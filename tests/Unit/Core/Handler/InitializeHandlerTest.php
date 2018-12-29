<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Handler;

use LanguageServerProtocol\InitializeResult;
use LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServer\Adapter\Evenement\EvenementEmitter;
use Phpactor\LanguageServer\Core\Extension;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Handler\InitializeHandler;
use Phpactor\LanguageServer\Core\Session\Session;
use Phpactor\LanguageServer\Core\Session\SessionManager;
use RuntimeException;

class InitializeHandlerTest extends HandlerTestCase
{
    /**
     * @var ObjectProphecy
     */
    private $session;

    /**
     * @var ObjectProphecy
     */
    private $sessionManager;

    /**
     * @var EvenementEmitter
     */
    private $emitter;

    /**
     * @var ObjectProphecy
     */
    private $extensions;


    public function setUp()
    {
        $this->sessionManager = $this->prophesize(SessionManager::class);
        $this->session = $this->prophesize(Session::class);
        $this->emitter = new EvenementEmitter();
        $this->extensions = $this->prophesize(Extension::class);
    }

    public function handler(): Handler
    {
        return new InitializeHandler(
            $this->emitter,
            $this->sessionManager->reveal()
        );
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
}
