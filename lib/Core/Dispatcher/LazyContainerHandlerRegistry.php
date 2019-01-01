<?php

namespace Phpactor\LanguageServer\Core\Dispatcher;

use Phpactor\LanguageServer\Core\Session\SessionManager;

class LazyContainerHandlerRegistry implements HandlerRegistry
{
    /**
     * @var SessionManager
     */
    private $manager;

    /**
     * @var array
     */
    private $factories;

    public function __construct(SessionManager $manager, array $factories)
    {
        $this->manager = $manager;
        $this->factories = $factories;
    }

    public function get(string $name): Handler
    {
        $container = $this->manager->current()->container();

        if (!isset($this->factories[$name])) {
            throw new HandlerNotFound(sprintf(
                'No factory available for "%s", known handlers: "%s"',
                $name,
                implode('", "', array_keys($this->factories))
            ));
        }

        $factory = $this->factories[$name];

        return $factory($container);
    }
}
