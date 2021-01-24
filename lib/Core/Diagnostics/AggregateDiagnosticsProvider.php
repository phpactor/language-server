<?php

namespace Phpactor\LanguageServer\Core\Diagnostics;

use Amp\Promise;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Psr\Log\LoggerInterface;
use function Amp\call;
use Throwable;

class AggregateDiagnosticsProvider implements DiagnosticsProvider
{
    /**
     * @var array<DiagnosticsProvider>
     */
    private $providers;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger, DiagnosticsProvider ...$providers)
    {
        $this->providers = $providers;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function provideDiagnostics(TextDocumentItem $textDocument): Promise
    {
        return call(function () use ($textDocument) {
            $diagnostics = [];
            foreach ($this->providers as $provider) {
                try {
                    $start = microtime(true);
                    $diagnostics = array_merge(
                        $diagnostics,
                        yield $provider->provideDiagnostics($textDocument)
                    );
                    $this->logger->debug(sprintf(
                        'Diagnostic finsihed in "%s" (%s)',
                        number_format(microtime(true) - $start, 2),
                        get_class($provider)
                    ));
                } catch (Throwable $throwable) {
                    $this->logger->error(sprintf(
                        'Diagnostic error from provider "%s": %s',
                        get_class($provider),
                        $throwable->getMessage()
                    ), [
                        'trace' => $throwable->getTraceAsString()
                    ]);
                }
            }

            return $diagnostics;
        });
    }
}
