<?php

namespace Phpactor\LanguageServer\Test;

use Amp\Promise;
use Phpactor\LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServerProtocol\InitializeResult;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\DispatcherFactory;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\Transmitter\LspMessageSerializer;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageSerializer;
use Phpactor\LanguageServer\Core\Server\Transmitter\TestMessageTransmitter;
use Phpactor\LanguageServer\Test\LanguageServerTester\ServicesTester;
use Phpactor\LanguageServer\Test\LanguageServerTester\TextDocumentTester;
use Phpactor\LanguageServer\Test\LanguageServerTester\WorkspaceTester;
use RuntimeException;
use function Amp\Promise\wait;

final class LanguageServerTester
{
    /**
     * @var TestMessageTransmitter
     */
    private $transmitter;

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var MessageSerializer
     */
    private $messageSerializer;

    /**
     * @var InitializeParams
     */
    private $initializeParams;

    public function __construct(DispatcherFactory $factory, InitializeParams $params, ?TestMessageTransmitter $transmitter = null)
    {
        $this->initializeParams = $params;
        $this->transmitter = $transmitter ?: new TestMessageTransmitter();
        $this->dispatcher = $factory->create($this->transmitter, $params);
        $this->messageSerializer = new LspMessageSerializer();
    }

    /**
     * @return Promise<ResponseMessage|null>
     */
    public function dispatch(Message $message): Promise
    {
        return $this->dispatcher->dispatch($message);
    }

    public function dispatchAndWait(Message $message): ?ResponseMessage
    {
        return wait($this->dispatcher->dispatch($message));
    }

    /**
     * @param array|object $params
     * @param int|string $id
     * @return Promise<ResponseMessage|null>
     */
    public function request(string $method, $params, $id = null): Promise
    {
        $requestMessage = new RequestMessage($id ?: uniqid(), $method, $this->normalizeParams($params));

        return $this->dispatch($requestMessage);
    }

    /**
     * @param array|object $params
     * @param int|string $id
     */
    public function requestAndWait(string $method, $params, $id = null): ?ResponseMessage
    {
        return wait($this->request($method, $this->normalizeParams($params), $id));
    }

    /**
     * @param array|object $params
     */
    public function mustRequestAndWait(string $method, mixed $params, int|string $id = null): ResponseMessage
    {
        $response = $this->requestAndWait($method, $params);

        if (null === $response) {
            throw new RuntimeException(sprintf(
                'Expected request to method "%s" to return a response, but it did not!',
                $method
            ));
        }

        return $response;
    }

    /**
     * @param mixed $value
     * @param string|int $id
     * @return Promise<mixed>
     */
    public function respond($id, $value): Promise
    {
        $requestMessage = new ResponseMessage($id, $value);

        return $this->dispatch($requestMessage);
    }

    /**
     * @param mixed $value
     * @param string|int $id
     * @return mixed
     */
    public function respondAndWait($id, $value)
    {
        return wait($this->respond($id, $value));
    }


    /**
     * @param array|object $params
     * @return Promise<ResponseMessage|null>
     */
    public function notify(string $method, $params): Promise
    {
        $notifyMessage = new NotificationMessage($method, $this->normalizeParams($params));

        return $this->dispatch($notifyMessage);
    }

    /**
     * @param array|object $params
     */
    public function notifyAndWait(string $method, $params): void
    {
        wait($this->notify($method, $this->normalizeParams($params)));
    }

    public function transmitter(): TestMessageTransmitter
    {
        return $this->transmitter;
    }

    /**
     * Initialize the server using the initialization parameters provided when
     * this class was instantiated and return the processed ServerCapabilties.
     */
    public function initialize(): InitializeResult
    {
        $response = $this->mustRequestAndWait('initialize', $this->initializeParams);
        $this->assertSuccess($response);
        $this->notifyAndWait('initialized', []);

        $result = $response->result;
        if (!$result instanceof InitializeResult) {
            throw new RuntimeException(sprintf(
                'Initialize did not return an InitializeResult, got "%s"',
                is_object($result) ? get_class($result) : gettype($result)
            ));
        }

        return $result;
    }

    public function services(): ServicesTester
    {
        return new ServicesTester($this);
    }

    public function textDocument(): TextDocumentTester
    {
        return new TextDocumentTester($this);
    }

    /**
     * Assert the the response is successful.
     *
     * @throws RuntimeException if not successful.
     */
    public function assertSuccess(ResponseMessage $response): void
    {
        if ($response->error) {
            throw new RuntimeException(sprintf(
                'Response has error: %s'."\n".'%s',
                $response->error->message,
                is_string($response->error->data) ? $response->error->data : json_encode($response->error->data, JSON_PRETTY_PRINT)
            ));
        }
    }

    public function cancel(int $requestId): void
    {
        $this->dispatchAndWait(new NotificationMessage('$/cancelRequest', ['id' => $requestId]));
    }

    public function workspace(): WorkspaceTester
    {
        return new WorkspaceTester($this);
    }

    /**
     * @param array|object $params
     * @return array<string,mixed>
     */
    private function normalizeParams($params): array
    {
        if (is_array($params)) {
            return $params;
        }

        $params =  $this->messageSerializer->normalize($params);
        if (!is_array($params)) {
            throw new RuntimeException(sprintf(
                'Could not normalize params, returned as type %s',
                gettype($params)
            ));
        }

        return $params;
    }
}
