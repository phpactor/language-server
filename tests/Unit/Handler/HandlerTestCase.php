<?php

namespace Phpactor\LanguageServer\Tests\Unit\Handler;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Test\HandlerTester;

abstract class HandlerTestCase extends TestCase
{
    abstract public function handler(): Handler;

    public function dispatch(string $method, array $params)
    {
        $tester = new HandlerTester($this->handler());

        return $tester->dispatch($method, $params);
    }
}
