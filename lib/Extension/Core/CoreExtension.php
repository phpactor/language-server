<?php

namespace Phpactor\LanguageServer\Extension\Core;

use LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServer\Core\Extension;
use Phpactor\LanguageServer\Core\Extensions;
use Phpactor\LanguageServer\Core\Handlers;
use Phpactor\LanguageServer\Core\Session\Manager;
use Phpactor\LanguageServer\Extension\Core\Session\Status;
use Phpactor\LanguageServer\Extension\Core\TextDocument\DidChange;
use Phpactor\LanguageServer\Extension\Core\TextDocument\DidClose;
use Phpactor\LanguageServer\Extension\Core\TextDocument\DidOpen;
use Phpactor\LanguageServer\Extension\Core\TextDocument\DidSave;
use Phpactor\LanguageServer\Extension\Core\TextDocument\WillSave;
use Phpactor\LanguageServer\Extension\Core\TextDocument\WillSaveWaitUntil;

class CoreExtension implements Extension
{
    /**
     * @var Extensions
     */
    private $extensions;

    /**
     * @var Manager
     */
    private $sessionManager;

    public function __construct(Extensions $extensions, Manager $sessionManager)
    {
        $this->extensions = $extensions;
        $this->sessionManager = $sessionManager;
    }

    public function handlers(): Handlers
    {
        return new Handlers([
            new Initialize($this->extensions, $this->sessionManager),
            new Initialized(),
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
    }
}
