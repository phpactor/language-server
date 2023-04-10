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
use Phpactor\LanguageServer\WorkDoneProgress\ProgressNotifier;
use Phpactor\LanguageServer\WorkDoneProgress\SilentWorkDoneProgressNotifier;
use Phpactor\LanguageServer\WorkDoneProgress\WorkDoneToken;
use function Amp\call;

class FormattingHandler implements Handler, CanRegisterCapabilities
{
    private ProgressNotifier $notifier;

    public function __construct(private Workspace $workspace, private Formatter $formatter, ?ProgressNotifier $notifier = null)
    {
        $this->notifier = $notifier ?: new SilentWorkDoneProgressNotifier();
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
            $token = WorkDoneToken::generate();
            yield $this->notifier->create($token);
            $document = $this->workspace->get($textDocument->uri);
            $this->notifier->begin($token, 'Formatting document');
            try {
                $formatted = yield $this->formatter->format($document);
            } finally {
                $this->notifier->end($token);
            }

            return $formatted;
        });
    }

    public function registerCapabiltiies(ServerCapabilities $capabilities): void
    {
        $capabilities->documentFormattingProvider = true;
    }
}
