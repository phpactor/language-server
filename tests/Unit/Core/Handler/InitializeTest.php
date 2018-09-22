<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Handler;

use LanguageServerProtocol\InitializeResult;
use LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServer\Core\Handler;
use Phpactor\LanguageServer\Core\Handler\Initialize;
use Phpactor\LanguageServer\Core\LanguageServer;
use Phpactor\LanguageServer\Core\LanguageServerFactory;
use Phpactor\LanguageServer\Core\Session;
use RuntimeException;

class InitializeTest extends HandlerTestCase
{
    /**
     * @var ObjectProphecy
     */
    private $session;

    /**
     * @var ObjectProphecy
     */
    private $server;

    /**
     * @var ObjectProphecy
     */
    private $serverFactory;

    public function setUp()
    {
        $this->session = $this->prophesize(Session::class);
        $this->server = $this->prophesize(LanguageServer::class);
        $this->serverFactory = $this->prophesize(LanguageServerFactory::class);
        $this->serverFactory->server()->willReturn($this->server->reveal());
        $this->server->capabilities()->willReturn(new ServerCapabilities());
    }

    public function testInitialize()
    {
        $response = $this->dispatch('initialize', [
            'capabilities' => [],
            'initializationOptions' => [],
            'processId' => '1234',
            'rootUri' => '/home/daniel/foobar',
        ]);

        $this->assertInstanceOf(InitializeResult::class, $response->result);
        $this->assertInstanceOf(ServerCapabilities::class, $response->result->capabilities);
    }

    public function testAcceptsDeprecatedRootPath()
    {
        $response = $this->dispatch('initialize', [
            'capabilities' => [],
            'initializationOptions' => [],
            'processId' => '1234',
            'rootPath' => '/home/daniel/foobar',
        ]);

        $this->assertInstanceOf(InitializeResult::class, $response->result);
    }

    public function testThrowsExceptionIfNoRootUriOrPathGiven()
    {
        $this->expectException(RuntimeException::class);

        $this->dispatch('initialize', [
            'capabilities' => [],
            'initializationOptions' => [],
            'processId' => '1234',
        ]);
    }

    public function handler(): Handler
    {
        return new Initialize($this->session->reveal(), $this->serverFactory->reveal());
    }
}
