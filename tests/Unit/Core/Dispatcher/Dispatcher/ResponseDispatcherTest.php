<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Dispatcher\Dispatcher;

use Amp\Success;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\ResponseDispatcher;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher;
use Phpactor\TestUtils\PHPUnit\TestCase;

class ResponseDispatcherTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $innerDispatcher;

    /**
     * @var ResponseDispatcher
     */
    private $dispatcher;

    /**
     * @var ObjectProphecy
     */
    private $watcher;

    protected function setUp(): void
    {
        $this->innerDispatcher = $this->prophesize(Dispatcher::class);
        $this->watcher = $this->prophesize(ResponseWatcher::class);
        $this->dispatcher = new ResponseDispatcher($this->innerDispatcher->reveal(), $this->watcher->reveal());
        $this->handlers = new Handlers();
    }

    public function testDispatchesResponseToWatcher(): void
    {
        $response = new ResponseMessage(1, 'foo');
        $this->dispatcher->dispatch($this->handlers, $response, []);
        $this->watcher->handle($response)->shouldHaveBeenCalled();
    }

    public function testNotificationRequestIsPassedOn(): void
    {
        $message = new NotificationMessage('foo');
        $this->innerDispatcher->dispatch($this->handlers, $message, [])->willReturn(new Success());
        $this->dispatcher->dispatch($this->handlers, $message, []);

        $this->watcher->handle($message)->shouldNotHaveBeenCalled();
    }
}
