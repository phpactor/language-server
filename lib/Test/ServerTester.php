<?php

namespace Phpactor\LanguageServer\Test;

use Amp\Promise;
use LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\ApplicationContainer;
use RuntimeException;

class ServerTester
{
    /**
     * @var ApplicationContainer
     */
    private $container;

    public function __construct(ApplicationContainer $container)
    {
        $this->container = $container;
    }

    /**
     * @param array<mixed> $params
     */
    public function dispatchAndWait(int $id, string $method, array $params = [], array $extraParams = []): ?Message
    {
        $request = new RequestMessage((int) ++$id, $method, $params);
        return \Amp\Promise\wait($this->container->dispatch($request, $extraParams));
    }

    /**
     * @return Promise<Message|null>
     */
    public function dispatch(int $id, string $method, array $params = [], array $extraParams = []): Promise
    {
        static $id = 0;
        $request = new RequestMessage((int) ++$id, $method, $params);
        return $this->container->dispatch($request, $extraParams);
    }

    public function initialize(): Message
    {
        $response = $this->dispatchAndWait(1, 'initialize', [
            'rootUri' => __DIR__,
        ]);
        $this->assertSuccess($response);
        return $response;
    }

    public function openDocument(TextDocumentItem $item): void
    {
        $this->dispatchAndWait(1, 'textDocument/didOpen', [
            'textDocument' => $item
        ]);
    }

    public function assertSuccess(?Message $response): bool
    {
        if (!$response) {
            return true;
        }

        if ($response instanceof ResponseMessage && $response->responseError) {
            throw new RuntimeException(sprintf(
                'Response contains error: %s',
                json_encode($response->responseError, JSON_PRETTY_PRINT)
            ));
        }

        return true;
    }
}
