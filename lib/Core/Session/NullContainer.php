<?php

namespace Phpactor\LanguageServer\Core\Session;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

class NullContainer implements ContainerInterface
{
    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        throw new class extends RuntimeException implements NotFoundExceptionInterface {
            public function __construct()
            {
                parent::__construct('This is a NULL container, it cannot be used to retreive services');
            }
        };
    }

    /**
     * {@inheritDoc}
     */
    public function has($id)
    {
        return false;
    }
}
