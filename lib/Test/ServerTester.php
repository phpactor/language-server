<?php

namespace Phpactor\LanguageServer\Test;

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

    public function dispatch(string $method, array $params = [])
    {
        static $id = 0;
        $request = new RequestMessage((int) ++$id, $method, $params);
        return \Amp\Promise\wait($this->container->dispatch($request));
    }

    public function initialize()
    {
        $response = $this->dispatch('initialize', [
            'rootUri' => __DIR__,
        ]);
        $this->assertSuccess($response);
        return $response;
    }

    /**
     * @return array<ResponseMessage>
     */
    public function openDocument(TextDocumentItem $item)
    {
        $response = $this->dispatch('textDocument/didOpen', [
            'textDocument' => $item
        ]);
        $this->assertSuccess($response);

        return $response;
    }

    public function assertSuccess(?ResponseMessage $response): bool
    {
        if (!$response) {
            return true;
        }

        if ($response->responseError) {
            throw new RuntimeException(sprintf(
                'Response contains error: %s',
                json_encode($response->responseError, JSON_PRETTY_PRINT)
            ));
        }

        return true;
    }
}
