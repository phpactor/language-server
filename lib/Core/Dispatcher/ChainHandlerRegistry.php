<?php

namespace Phpactor\LanguageServer\Core\Dispatcher;

class ChainHandlerRegistry implements HandlerRegistry
{
    /**
     * @var HandlerRegistry[]
     */
    private $registries;

    /**
     * @param HandlerRegistry[] $registries
     */
    public function __construct(array $registries)
    {
        foreach ($registries as $registry) {
            $this->add($registry);
        }
    }

    public function get(string $name): Handler
    {
        $exceptions = [];
        foreach ($this->registries as $registry) {
            try {
                return $registry->get($name);
            } catch (HandlerNotFound $e) {
                $exceptions[] = $e->getMessage();
            }
        }

        throw new HandlerNotFound(sprintf(
            'Chain handler failure: "%s"',
            implode('", "', $exceptions)
        ));
    }

    private function add(HandlerRegistry $registry)
    {
        $this->registries[] = $registry;
    }
}
