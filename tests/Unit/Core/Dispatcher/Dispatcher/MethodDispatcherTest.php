<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Dispatcher\Dispatcher;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\MethodDispatcher;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use stdClass;

class MethodDispatcherTest extends TestCase
{
    const EXPECTED_RESULT = 'Hello';

    private $argumentResolver;

    /**
     * @var Handler
     */
    private $handler;

    /**
     * @var Handlers
     */
    private $handlers;

    public function setUp()
    {
        $this->argumentResolver = $this->prophesize(ArgumentResolver::class);
        $this->handler = new class implements Handler {
            public function methods(): array
            {
                return [
                    'foobar' => 'foobar',
                ];
            }

            public function foobar(string $one, string $two)
            {
                yield new stdClass();
            }
        };
    }

    public function testDispatchesRequest()
    {
        $dispatcher = $this->create();
        $handlers = new Handlers([
            $this->handler
        ]);
        $this->argumentResolver->resolveArguments($this->handler, 'foobar', [
            'one',
            'two'
        ])->willReturn([ 'one', 'two' ]);

        $expectedResult = new stdClass();

        $messages = $dispatcher->dispatch($handlers, new RequestMessage(5, 'foobar', [ 'one', 'two' ]));

        $this->assertInstanceOf(Generator::class, $messages);
        $response = $messages->current();
        $this->assertInstanceOf(ResponseMessage::class, $response);
        $this->assertEquals($expectedResult, $response->result);
        $this->assertEquals(5, $response->id);
    }

    private function create(): MethodDispatcher
    {
        return new MethodDispatcher($this->argumentResolver->reveal());
    }
}
