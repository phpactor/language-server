<?php

namespace Phpactor\LanguageServer\Core\Handler;

use LanguageServerProtocol\InitializeParams;

class AggregateHandlerLoader implements HandlerLoader
{
    /**
     * @var HandlerLoader[]
     */
    private $loaders = [];

    public function __construct(array $loaders)
    {
        foreach ($loaders as $loader) {
            $this->add($loader);
        }
    }

    private function add(HandlerLoader $loader)
    {
        $this->loaders[] = $loader;
    }

    public function load(InitializeParams $params): Handlers
    {
        $handlers = new Handlers([]);
        foreach ($this->loaders as $loader) {
            $handlers->merge($loader->load($params));
        }

        return $handlers;
    }
}
