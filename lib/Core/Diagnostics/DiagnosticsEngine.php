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
use Exception;

class DiagnosticsEngine
{
    /**
     * @var Deferred<null>
     */
    private Deferred $deferred;

    private ?TextDocumentItem $waiting = null;

    /**
     * @var array<int|string,list<Diagnostic>>
     */
    private array $diagnostics = [];

    /**
     * @var array<string,Deferred<bool>>
     */
    private array $locks = [];

    private float $lastUpdatedAt;

    /**
     * @param DiagnosticsProvider[] $providers
     */
    public function __construct(private ClientApi $clientApi, private LoggerInterface $logger, private array $providers, private int $sleepTime = 1000)
    {
        $this->deferred = new Deferred();
        $this->providers = $providers;
        $this->clientApi = $clientApi;
        $this->sleepTime = $sleepTime;
        $this->lastUpdatedAt = 0.0;
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

                yield $this->awaitNextDocument();

                $beforeDocument = $this->waiting;
                $gracePeriod = abs($this->sleepTime - ((microtime(true) - $this->lastUpdatedAt) * 1000));
                yield delay(intval($gracePeriod));

                if ($beforeDocument !== $this->waiting) {
                    continue;
                }

                $textDocument = $this->waiting;
                $this->waiting = null;
                // allow the next document update to resolve
                $this->deferred = new Deferred();

                // should never happen
                if ($textDocument === null) {
                    continue;
                }

                // reset diagnostics for this document
                $this->clientApi->diagnostics()->publishDiagnostics(
                    $textDocument->uri,
                    $textDocument->version,
                    [],
                );
                $this->diagnostics[$textDocument->uri] = [];

                $crashedProviders = [];

                foreach ($this->providers as $providerId => $provider) {
                    if (in_array($providerId, $crashedProviders)) {
                        continue;
                    }

                    asyncCall(function () use ($providerId, $provider, $token, $textDocument, &$crashedProviders) {
                        $start = microtime(true);

                        yield $this->awaitProviderLock($providerId);

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
                                'stack' => (new Exception())->getTraceAsString()
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

                        if (!$this->isDocumentCurrent($textDocument)) {
                            return;
                        }

                        $this->diagnostics[$textDocument->uri] = array_merge(
                            $this->diagnostics[$textDocument->uri] ?? [],
                            $diagnostics
                        );

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
        // set the last updated at timestamp - this will be used as the basis of
        // the grace period before linting
        $this->lastUpdatedAt = microtime(true);

        $waiting = $this->waiting;

        // set the next document
        $this->waiting = $textDocument;

        // if we already had a waiting document then do nothing
        // it will get resolved next time
        if ($waiting !== null) {
            return;
        }

        // otherwise trigger the lint process
        $this->deferred->resolve();
    }

    private function isDocumentCurrent(TextDocumentItem $textDocument): bool
    {
        return $this->waiting === null;
    }

    /**
     * @param string|int $providerId
     * @return Promise<bool>
     */
    private function awaitProviderLock($providerId): Promise
    {
        if (!array_key_exists($providerId, $this->locks)) {
            return new Success(true);
        }

        return $this->locks[$providerId]->promise();

    }

    /**
     * @return Promise<null>
     */
    private function awaitNextDocument(): Promise
    {
        if ($this->waiting) {
            return new Success();
        }

        return $this->deferred->promise();
    }
}
