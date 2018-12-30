<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Server;

use Generator;
use LanguageServerProtocol\InitializeResult;
use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\LanguageServerBuilder;

class LanguageServerBuilderTest extends TestCase
{
    public function testBuildDispatcher()
    {
        $dispatcher = LanguageServerBuilder::create()
            ->catchExceptions(false)
            ->useDefaultHandlers()
            ->buildDispatcher();

        $results = $dispatcher->dispatch(new RequestMessage(1, 'initialize', [
            'rootUri' => __DIR__,
        ]));
        $this->assertInstanceOf(Generator::class, $results);
        $results = \iterator_to_array($results);
        $result = $results[0];
        $this->assertInstanceOf(ResponseMessage::class, $result);
        $this->assertInstanceOf(InitializeResult::class, $result->result);
    }
}
