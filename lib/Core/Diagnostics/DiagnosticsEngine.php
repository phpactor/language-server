<?php

namespace Phpactor\LanguageServer\Core\Diagnostics;

use Amp\CancelledException;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Amp\Promise;
use Amp\CancellationToken;
use Amp\Deferred;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use function Amp\delay;

class DiagnosticsEngine
{
    /**
     * @var Deferred<TextDocumentItem>
     */
    private $deferred;

    /**
     * @var bool
     */
    private $running = false;

    /**
     * @var ?TextDocumentItem
     */
    private $next;

    /**
     * @var DiagnosticsProvider
     */
    private $provider;

    /**
     * @var ClientApi
     */
    private $clientApi;

    /**
     * @var int
     */
    private $sleepTime;

    public function __construct(ClientApi $clientApi, DiagnosticsProvider $provider, int $sleepTime = 1000)
    {
        $this->deferred = new Deferred();
        $this->provider = $provider;
        $this->clientApi = $clientApi;
        $this->sleepTime = $sleepTime;
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

                $textDocument = yield $this->deferred->promise();

                $this->deferred = new Deferred();

                // after we have reset deferred, we can safely set linting to
                // `false` and let another resolve happen
                $this->running = false;

                assert($textDocument instanceof TextDocumentItem);

                if ($this->sleepTime > 0) {
                    yield delay($this->sleepTime);
                }

                if ($this->next) {
                    $textDocument = $this->next;
                    $this->next = null;
                }

                $diagnostics = yield $this->provider->provideDiagnostics($textDocument);

                $this->clientApi->diagnostics()->publishDiagnostics(
                    $textDocument->uri,
                    $textDocument->version,
                    $diagnostics
                );
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
}
