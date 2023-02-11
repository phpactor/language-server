<?php

namespace Phpactor\LanguageServer\Middleware;

use Amp\Promise;
use Amp\Success;
use Phpactor\LanguageServerProtocol\InitializeResult;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Middleware\Middleware;
use Phpactor\LanguageServer\Core\Middleware\RequestHandler;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Event\Initialized;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;

class InitializeMiddleware implements Middleware
{
    private const METHOD_INITIALIZED = 'initialized';
    private const METHOD_INITIALIZE = 'initialize';

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @param array{name?:string,version?:string} $serverInfo
     */
    public function __construct(private Handlers $handlers, private EventDispatcherInterface $dispatcher, private array $serverInfo = [])
    {
        $this->handlers = $handlers;
        $this->dispatcher = $dispatcher;
        $this->serverInfo = $serverInfo;
    }

    /**
     * {@inheritDoc}
     */
    public function process(Message $request, RequestHandler $handler): Promise
    {
        if ($request instanceof NotificationMessage && $request->method === self::METHOD_INITIALIZED) {
            $this->dispatcher->dispatch(new Initialized());
            return new Success(null);
        }

        if (!$request instanceof RequestMessage) {
            return $handler->handle($request);
        }

        if ($request->method !== self::METHOD_INITIALIZE) {
            return $handler->handle($request);
        }

        if (true === $this->initialized) {
            throw new RuntimeException(sprintf(
                'Second initialize request (id: %s) has been recieved. Can only initialize a session once',
                $request->id
            ));
        }

        $serverCapabilities = new ServerCapabilities();

        foreach ($this->handlers as $handler) {
            if ($handler instanceof CanRegisterCapabilities) {
                $handler->registerCapabiltiies($serverCapabilities);
            }
        }

        $this->initialized = true;

        return new Success(
            new ResponseMessage(
                $request->id,
                new InitializeResult($serverCapabilities, array_merge([
                    'name' => 'unspecified',
                    'version' => 'unspecified',
                ], $this->serverInfo))
            )
        );
    }
}
