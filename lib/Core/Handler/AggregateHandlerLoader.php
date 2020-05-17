<?php

namespace Phpactor\LanguageServer\Core\Handler;

use LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServer\Core\Server\SessionServices;

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

    public function load(InitializeParams $params, SessionServices $services): Handlers
    {
        $handlers = new Handlers([]);
        foreach ($this->loaders as $loader) {
            $handlers->merge($loader->load($params, $services));
        }

        return $handlers;
    }

    private function add(HandlerLoader $loader): void
    {
        $this->loaders[] = $loader;
    }
}
