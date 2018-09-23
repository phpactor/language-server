<?php

namespace Phpactor\LanguageServer\Core\Connection;

use Phpactor\LanguageServer\Core\Connection;
use Phpactor\LanguageServer\Core\IO;

class SimpleConnection implements Connection
{
    /**
     * @var IO
     */
    private $io;

    public function __construct(IO $io)
    {
        $this->io = $io;
    }

    public function io(): IO
    {
        return $this->io;
    }
}
