<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Dispatcher;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\ArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\MethodDispatcher;
use Phpactor\LanguageServer\Core\Handler;
use Phpactor\LanguageServer\Core\Handlers;
use Phpactor\LanguageServer\Core\Transport\RequestMessage;
use Phpactor\LanguageServer\Core\Transport\ResponseMessage;
use SebastianBergmann\CodeCoverage\Report\Xml\Method;
use stdClass;

class MethodDispatcherTest extends TestCase
{
    const EXPECTED_RESULT = 'Hello';

    private $argumentResolver;

    public function setUp()
    {
        $this->argumentResolver = $this->prophesize(ArgumentResolver::class);
        $this->handler = $this->prophesize(Handler::class);
    }

    public function testDispatchesRequest()
    {
        $this->handler->name()->willReturn('foobar');
        $dispatcher = $this->create([
            $this->handler->reveal()
        ]);
        $this->argumentResolver->resolveArguments(get_class($this->handler->reveal()), '__invoke', [
            'one',
            'two'
        ])->willReturn([ 'one', 'two' ]);

        $expectedResult = new stdClass();
        $this->handler->__invoke('one', 'two')->willReturn($expectedResult);

        $response = $dispatcher->dispatch(new RequestMessage(5, 'foobar', [ 'one', 'two' ]));

        $this->assertInstanceOf(ResponseMessage::class, $response);
        $this->assertEquals($expectedResult, $response->result);
        $this->assertEquals(5, $response->id);
    }

    private function create(array $array): MethodDispatcher
    {
        $handlers = new Handlers($array);
        return new MethodDispatcher($this->argumentResolver->reveal(), $handlers);
    }
}
