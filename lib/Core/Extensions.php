<?php

namespace Phpactor\LanguageServer\Core;

use LanguageServerProtocol\ServerCapabilities;

class Extensions implements Extension
{
    /**
     * @var array<Extension>
     */
    private $extensions = [];

    public function __construct(array $extensions)
    {
        foreach ($extensions as $extension) {
            $this->add($extension);
        }
    }

    public function handlers(): Handlers
    {
        $handlers = new Handlers();
        foreach ($this->extensions as $extension) {
            $handlers->merge($extension->handlers());
        }

        return $handlers;
    }

    public function configureCapabilities(ServerCapabilities $capabilities): void
    {
        foreach ($this->extensions as $extension) {
            $extension->configureCapabilities($capabilities);
        }
    }

    private function add(Extension $extension)
    {
        $this->extensions[] = $extension;
    }
}
