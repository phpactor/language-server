<?php

namespace Phpactor\LanguageServer;

use Phpactor\LanguageServer\Adapter\DTL\DTLArgumentResolver;
use Phpactor\LanguageServer\Adapter\Evenement\EvenementEmitter;
use Phpactor\LanguageServer\Core\Dispatcher\ErrorCatchingDispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\Handlers;
use Phpactor\LanguageServer\Core\Dispatcher\MethodDispatcher;
use Phpactor\LanguageServer\Core\Event\EventEmitter;
use Phpactor\LanguageServer\Core\Handler\ExitHandler;
use Phpactor\LanguageServer\Core\Handler\InitializeHandler;
use Phpactor\LanguageServer\Core\Handler\TextDocumentHandler;
use Phpactor\LanguageServer\Core\Server\TcpServer;
use Phpactor\LanguageServer\Core\Protocol\CoreExtension;
use Phpactor\LanguageServer\Core\Server\Server;
use Phpactor\LanguageServer\Core\Session\Manager;
use Phpactor\LanguageServer\Core\Session\Session;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class LanguageServerBuilder
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    private function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function create(LoggerInterface $logger = null): self
    {
        return new self(
            $logger ?: new NullLogger()
        );
    }

    public function build(string $address = '127.0.0.1:8888'): Server
    {
        $manager = $this->sessionManager();
        $emitter = $this->emitter();
        $dispatcher = new ErrorCatchingDispatcher(
            new MethodDispatcher(
                new DTLArgumentResolver(),
                new Handlers([
                    new InitializeHandler(
                        $emitter,
                        $manager
                    ),
                    new TextDocumentHandler(
                        $emitter,
                        $manager
                    ),
                    new ExitHandler(),
                ])
            ),
            $this->logger
        );

        return new TcpServer($dispatcher, $this->logger, $address);
    }

    private function emitter(): EventEmitter
    {
        return new EvenementEmitter();
    }

    private function sessionManager(): Manager
    {
        return new Manager();
    }
}
