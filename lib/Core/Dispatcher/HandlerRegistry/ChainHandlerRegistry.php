<?php

namespace Phpactor\LanguageServer\Core\Dispatcher\HandlerRegistry;

use Generator;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Dispatcher\HandlerCollection;
use Phpactor\LanguageServer\Core\Dispatcher\HandlerNotFound;

class ChainHandlerRegistry implements HandlerCollection
{
    /**
     * @var HandlerCollection[]
     */
    private $handlerCollections;

    /**
     * @param HandlerCollection[] $handlerCollections
     */
    public function __construct(array $handlerCollections)
    {
        foreach ($handlerCollections as $collection) {
            $this->add($collection);
        }
    }

    public function get(string $name): Handler
    {
        $exceptions = [];
        foreach ($this->handlerCollections as $collection) {
            try {
                return $collection->get($name);
            } catch (HandlerNotFound $e) {
                $exceptions[] = $e->getMessage();
            }
        }

        throw new HandlerNotFound(sprintf(
            'Chain handler failure: "%s"',
            implode('", "', $exceptions)
        ));
    }

    private function add(HandlerCollection $collection)
    {
        $this->handlerCollections[] = $collection;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): Generator
    {
        foreach ($this->handlerCollections as $collection) {
            yield from $collection;
        }
    }
}
