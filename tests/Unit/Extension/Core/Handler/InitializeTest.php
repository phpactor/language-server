<?php

namespace Phpactor\LanguageServer\Tests\Unit\Extension\Core\Handler;

use LanguageServerProtocol\InitializeResult;
use LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServer\Core\Extension;
use Phpactor\LanguageServer\Core\Handler;
use Phpactor\LanguageServer\Extension\Core\Initialize;
use Phpactor\LanguageServer\Core\Session\Session;
use Phpactor\LanguageServer\Core\Session\Manager;
use RuntimeException;
use Phpactor\LanguageServer\Tests\Unit\Extension\Core\Handler\HandlerTestCase;

class InitializeTest extends HandlerTestCase
{
    /**
     * @var ObjectProphecy
     */
    private $session;

    public function setUp()
    {
        $this->sessionManager = $this->prophesize(Manager::class);
        $this->session = $this->prophesize(Session::class);
        $this->extensions = $this->prophesize(Extension::class);
    }

    public function testInitialize()
    {
        $messages = $this->dispatch('initialize', [
            'capabilities' => [],
            'initializationOptions' => [],
            'processId' => '1234',
            'rootUri' => '/home/daniel/foobar',
        ]);

        $this->assertInstanceOf(InitializeResult::class, $messages[0]->result);
        $this->assertInstanceOf(ServerCapabilities::class, $messages[0]->result->capabilities);
    }

    public function testAcceptsDeprecatedRootPath()
    {
        $messages = $this->dispatch('initialize', [
            'capabilities' => [],
            'initializationOptions' => [],
            'processId' => 1234,
            'rootPath' => '/home/daniel/foobar',
        ]);

        $this->sessionManager->initialize('/home/daniel/foobar', 1234)->shouldBeCalled();
        $this->assertInstanceOf(InitializeResult::class, $messages[0]->result);
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
        return new Initialize($this->extensions->reveal(), $this->sessionManager->reveal());
    }
}
