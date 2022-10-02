<?php

namespace Phpactor\LanguageServer\Handler\TextDocument;

use Amp\Promise;
use Phpactor\LanguageServerProtocol\FormattingOptions;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServerProtocol\TextDocumentIdentifier;
use Phpactor\LanguageServerProtocol\TextEdit;
use Phpactor\LanguageServer\Core\Formatting\Formatter;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use function Amp\call;

class FormattingHandler implements Handler, CanRegisterCapabilities
{
    private Workspace $workspace;

    private Formatter $formatter;


    public function __construct(Workspace $workspace, Formatter $formatter)
    {
        $this->workspace = $workspace;
        $this->formatter = $formatter;
    }

    public function methods(): array
    {
        return ['textDocument/formatting' => 'formatting'];
    }

    /**
     * @return Promise<array<int,TextEdit[]>|null>
     */
    public function formatting(TextDocumentIdentifier $textDocument, FormattingOptions $options): Promise
    {
        return call(function () use ($textDocument) {
            $document = $this->workspace->get($textDocument->uri);
            $formatted = yield $this->formatter->format($document);

            return $formatted;
        });
    }

    public function registerCapabiltiies(ServerCapabilities $capabilities): void
    {
        $capabilities->documentFormattingProvider = true;
    }
}
