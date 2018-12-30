<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Handler;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Adapter\Evenement\EvenementEmitter;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Event\EventEmitter;
use Phpactor\LanguageServer\Core\Session\SessionManager;
use Phpactor\LanguageServer\Test\HandlerTester;

abstract class HandlerTestCase extends TestCase
{
    abstract public function handler(): Handler;

    protected function emitter(): EventEmitter
    {
        return new EvenementEmitter();
    }

    protected function sessionManager(): SessionManager
    {
        return new SessionManager();
    }

    public function dispatch(string $method, array $params)
    {
        $tester = new HandlerTester($this->handler());

        return $tester->dispatch($method, $params);
    }
}
