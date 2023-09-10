<?php

namespace Phpactor\LanguageServer\Core\Diagnostics;

use Amp\CancelledException;
use Amp\Success;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Amp\Promise;
use Amp\CancellationToken;
use Amp\Deferred;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Psr\Log\LoggerInterface;
use Throwable;
use function Amp\asyncCall;
use function Amp\delay;

class DiagnosticsEngine
{
    /**
     * @var Deferred<TextDocumentItem>
     */
    private Deferred $deferred;

    private bool $running = false;

    private ?TextDocumentItem $next = null;

    /**
     * @var array<int|string,list<Diagnostic>>
     */
    private array $diagnostics = [];

    /**
     * @var array<int|string,int|string>
     */
    private array $versions = [];

    /**
     * @var array<string,Deferred<bool>>
     */
    private array $locks = [];

    /**
     * @param DiagnosticsProvider[] $providers
     */
    public function __construct(private ClientApi $clientApi, private LoggerInterface $logger, private array $providers, private int $sleepTime = 1000)
    {
        $this->deferred = new Deferred();
        $this->providers = $providers;
        $this->clientApi = $clientApi;
        $this->sleepTime = $sleepTime;
    }

    public function clear(TextDocumentItem $textDocument): void
    {
        $this->clientApi->diagnostics()->publishDiagnostics(
            $textDocument->uri,
            $textDocument->version,
            []
        );
    }

    /**
     * @return Promise<bool>
     */
    public function run(CancellationToken $token): Promise
    {
        return \Amp\call(function () use ($token) {
            while (true) {
                try {
                    $token->throwIfRequested();
                } catch (CancelledException $cancelled) {
                    return;
                }

                $textDocument = yield $this->nextDocument();
                $this->versions[$textDocument->uri] = $textDocument->version;

                if (isset($this->diagnostics[$textDocument->uri])) {
                    $this->clientApi->diagnostics()->publishDiagnostics(
                        $textDocument->uri,
                        $textDocument->version,
                        [],
                    );
                }
                $this->diagnostics[$textDocument->uri] = [];

                $this->deferred = new Deferred();

                // after we have reset deferred, we can safely set linting to
                // `false` and let another resolve happen
                $this->running = false;

                $crashedProviders = [];

                foreach ($this->providers as $providerId => $provider) {
                    if (in_array($providerId, $crashedProviders)) {
                        continue;
                    }

                    asyncCall(function () use ($providerId, $provider, $token, $textDocument, &$crashedProviders) {
                        $start = microtime(true);

                        yield $this->await($providerId);

                        if (!$this->isDocumentCurrent($textDocument)) {
                            return;
                        }

                        $this->locks[$providerId] = new Deferred();

                        try {
                            /** @var Diagnostic[] $diagnostics */
                            $diagnostics = yield $provider->provideDiagnostics($textDocument, $token) ;
                        } catch (Throwable $e) {
                            $message = sprintf('Diagnostic provider "%s" errored with "%s", removing from pool', $providerId, $e->getMessage());
                            $this->clientApi->window()->showMessage()->warning($message);
                            $this->logger->error($message, [
                                'stack' => (new \Exception())->getTraceAsString()
                            ]);
                            $crashedProviders[$providerId] = true;
                            return;
                        }

                        if (isset($this->locks[$providerId])) {
                            $lock = $this->locks[$providerId];
                            unset($this->locks[$providerId]);
                            $lock->resolve(true);
                        }

                        if (!$diagnostics) {
                            return;
                        }

                        $elapsed = (int)round((microtime(true) - $start) / 1000);

                        $this->diagnostics[$textDocument->uri] = array_merge(
                            $this->diagnostics[$textDocument->uri] ?? [],
                            $diagnostics
                        );

                        $timeToSleep = $this->sleepTime - $elapsed;

                        if ($timeToSleep > 0) {
                            yield delay($timeToSleep);
                        }

                        if (!$this->isDocumentCurrent($textDocument)) {
                            return;
                        }

                        $this->clientApi->diagnostics()->publishDiagnostics(
                            $textDocument->uri,
                            $textDocument->version,
                            $this->diagnostics[$textDocument->uri]
                        );
                    });
                }
            }
        });
    }

    public function enqueue(TextDocumentItem $textDocument): void
    {
        // if we are already linting then store whatever comes afterwards in
        // next, overwriting the redundant update
        if ($this->running === true) {
            $this->next = $textDocument;
            return;
        }

        // resolving the promise will start PHPStan
        $this->running = true;
        $this->deferred->resolve($textDocument);
    }

    private function isDocumentCurrent(TextDocumentItem $textDocument): bool
    {
        return $textDocument->version === ($this->versions[$textDocument->uri] ?? -1);
    }
    /**
     * @param string|int $providerId
     * @return Promise<bool>
     */
    private function await($providerId): Promise
    {
        if (!array_key_exists($providerId, $this->locks)) {
            return new Success(true);
        }

        return $this->locks[$providerId]->promise();

    }

    /**
     * @return Promise<TextDocumentItem>
     */
    private function nextDocument(): Promise
    {
        if ($this->next) {
            $textDocument = $this->next;
            $this->next = null;
            return new Success($textDocument);
        }

        return $this->deferred->promise();
    }
}
