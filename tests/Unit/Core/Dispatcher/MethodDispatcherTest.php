<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Dispatcher;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\MethodDispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Dispatcher\Handlers;
use Phpactor\LanguageServer\Core\Transport\RequestMessage;
use Phpactor\LanguageServer\Core\Transport\ResponseMessage;
use stdClass;

class MethodDispatcherTest extends TestCase
{
    const EXPECTED_RESULT = 'Hello';

    private $argumentResolver;

    /**
     * @var ObjectProphecy
     */
    private $handler;

    /**
     * @var Handlers
     */
    private $handlers;

    public function setUp()
    {
        $this->argumentResolver = $this->prophesize(ArgumentResolver::class);
        $this->handler = $this->prophesize(Handler::class);
        $this->handler->name()->willReturn('foobar');
        $this->handlers = new Handlers([ $this->handler->reveal() ]);
    }

    public function testDispatchesRequest()
    {
        $dispatcher = $this->create([
            $this->handler->reveal()
        ]);
        $this->argumentResolver->resolveArguments($this->handler->reveal(), '__invoke', [
            'one',
            'two'
        ])->willReturn([ 'one', 'two' ]);

        $expectedResult = new stdClass();
        $this->handler->__invoke('one', 'two')->will(function () use ($expectedResult) {
            yield $expectedResult;
        });

        $messages = $dispatcher->dispatch(new RequestMessage(5, 'foobar', [ 'one', 'two' ]));

        $this->assertInstanceOf(Generator::class, $messages);
        $response = $messages->current();
        $this->assertInstanceOf(ResponseMessage::class, $response);
        $this->assertEquals($expectedResult, $response->result);
        $this->assertEquals(5, $response->id);
    }

    private function create(array $array): MethodDispatcher
    {
        return new MethodDispatcher($this->argumentResolver->reveal(), $this->handlers);
    }
}
