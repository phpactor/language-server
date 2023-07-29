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
     * @var DiagnosticsProvider[]
     */
    private array $providers;

    private ClientApi $clientApi;

    private int $sleepTime;

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
    public function __construct(ClientApi $clientApi, array $providers, int $sleepTime = 1000)
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

                /** @var TextDocumentItem $textDocument */
                $textDocument = yield $this->deferred->promise();
                $this->versions[$textDocument->uri] = $textDocument->version;

                // clear diagnostics for document
                $this->diagnostics[$textDocument->uri] = [];
                $this->clientApi->diagnostics()->publishDiagnostics(
                    $textDocument->uri,
                    $textDocument->version,
                    $this->diagnostics[$textDocument->uri],
                );

                $this->deferred = new Deferred();

                // after we have reset deferred, we can safely set linting to
                // `false` and let another resolve happen
                $this->running = false;

                if ($this->next) {
                    $textDocument = $this->next;
                    $this->next = null;
                }

                foreach ($this->providers as $providerId => $provider) {
                    asyncCall(function () use ($providerId, $provider, $token, $textDocument) {
                        $start = microtime(true);

                        yield $this->await($providerId);

                        if (!$this->isDocumentCurrent($textDocument)) {
                            return;
                        }

                        $this->locks[$providerId] = new Deferred();

                        /** @var Diagnostic[] $diagnostics */
                        $diagnostics = yield $provider->provideDiagnostics($textDocument, $token) ;

                        if (isset($this->locks[$providerId])) {
                            $lock = $this->locks[$providerId];
                            unset($this->locks[$providerId]);
                            $lock->resolve(true);
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
}
