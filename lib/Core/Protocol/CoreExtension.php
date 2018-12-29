<?php

namespace Phpactor\LanguageServer\Core\Protocol;

use LanguageServerProtocol\ServerCapabilities;
use LanguageServerProtocol\TextDocumentSyncKind;
use Phpactor\LanguageServer\Core\Extension;
use Phpactor\LanguageServer\Core\Extensions;
use Phpactor\LanguageServer\Core\Dispatcher\Handlers;
use Phpactor\LanguageServer\Core\Session\Manager;
use Phpactor\LanguageServer\Core\Protocol\Session\Status;
use Phpactor\LanguageServer\Core\Protocol\TextDocument\DidChange;
use Phpactor\LanguageServer\Core\Protocol\TextDocument\DidClose;
use Phpactor\LanguageServer\Core\Protocol\TextDocument\DidOpen;
use Phpactor\LanguageServer\Core\Protocol\TextDocument\DidSave;
use Phpactor\LanguageServer\Core\Protocol\TextDocument\WillSave;
use Phpactor\LanguageServer\Core\Protocol\TextDocument\WillSaveWaitUntil;
use Phpactor\LanguageServer\Core\Protocol\InitializeParams;
use Phpactor\LanguageServer\Core\Protocol\InitializedParams;

class CoreExtension
{
    /**
     * @var Manager
     */
    private $sessionManager;

    public function __construct(Manager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    public function handlers(): Handlers
    {
        return new Handlers([
            new InitializeParams($this->sessionManager),
            new InitializedParams(),
            new ExitServer(),
            new Shutdown(),
            new DidOpen($this->sessionManager),
            new DidChange($this->sessionManager),
            new DidClose($this->sessionManager),
            new DidSave(),
            new WillSave(),
            new WillSaveWaitUntil(),
            new Status($this->sessionManager),
        ]);
    }

    public function configureCapabilities(ServerCapabilities $capabilities): void
    {
        $capabilities->textDocumentSync = TextDocumentSyncKind::FULL;
    }
}
