<?php

namespace Phpactor\LanguageServer\Test;

use LanguageServerProtocol\TextDocumentItem;
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

    public function dispatch(string $method, array $params = []): array
    {
        static $id = 0;
        $request = new RequestMessage((int) ++$id, $method, $params);
        $results = iterator_to_array($this->container->dispatch($request));

        return $results;
    }

    public function initialize(): array
    {
        $responses = $this->dispatch('initialize', [
            'rootUri' => __DIR__,
        ]);
        $this->assertSuccess($responses);

        return $responses;
    }

    /**
     * @return array<ResponseMessage>
     */
    public function openDocument(TextDocumentItem $item): array
    {
        $responses = $this->dispatch('textDocument/didOpen', [
            'textDocument' => $item
        ]);
        $this->assertSuccess($responses);

        return $responses;
    }

    public function assertSuccess(array $responses): bool
    {
        $responses = (array) $responses;

        foreach ($responses as $response) {
            if (!$response instanceof ResponseMessage) {
                continue;
            }

            if ($response->responseError) {
                throw new RuntimeException(sprintf(
                    'Response contains error: %s',
                    json_encode($response->responseError, JSON_PRETTY_PRINT)
                ));
            }
        }

        return true;
    }
}
