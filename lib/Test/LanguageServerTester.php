<?php

namespace Phpactor\LanguageServer\Test;

use Amp\Promise;
use Phpactor\LanguageServerProtocol\DidOpenTextDocumentNotification;
use Phpactor\LanguageServerProtocol\DidOpenTextDocumentParams;
use Phpactor\LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\DispatcherFactory;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageSerializer;
use Phpactor\LanguageServer\Core\Server\Transmitter\TestMessageTransmitter;
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

    public function __construct(DispatcherFactory $factory, InitializeParams $params)
    {
        $this->initializeParams = $params;
        $this->transmitter = new TestMessageTransmitter();
        $this->dispatcher = $factory->create($this->transmitter, $params);
        $this->messageSerializer = new MessageSerializer();
    }

    /**
     * @return Promise<ResponseMessage|null>
     */
    public function dispatch(Message $message): Promise
    {
        return $this->dispatcher->dispatch($message);
    }

    public function dispatchAndWait(RequestMessage $message): ?ResponseMessage
    {
        return wait($this->dispatcher->dispatch($message));
    }

    /**
     * @param array|object $params
     * @return Promise<ResponseMessage|null>
     */
    public function request(string $method, $params): Promise
    {
        $requestMessage = new RequestMessage(uniqid(), $method, $this->normalizeParams($params));

        return $this->dispatch($requestMessage);
    }

    /**
     * @param array|object $params
     */
    public function requestAndWait(string $method, $params): ?ResponseMessage
    {
        return wait($this->request($method, $this->normalizeParams($params)));
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

    public function openTextDocument(string $url, string $content): void
    {
        $this->notifyAndWait(DidOpenTextDocumentNotification::METHOD, new DidOpenTextDocumentParams(
            ProtocolFactory::textDocumentItem($url, $content)
        ));
    }

    public function initialize(): void
    {
        $response = $this->requestAndWait('initialize', $this->initializeParams);
        $this->assertSuccess($response);
        $this->notifyAndWait('initialized', []);
    }

    public function servicesRunning(): array
    {
        $response = $this->requestAndWait('phpactor/service/running', []);
        return $response->result;
    }

    public function serviceStop(string $name): void
    {
        $this->notifyAndWait('phpactor/service/stop', ['name' => $name]);
    }

    public function serviceStart(string $name): void
    {
        $this->notifyAndWait('phpactor/service/start', ['name' => $name]);
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

        return $this->messageSerializer->normalize($params);
    }

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
}
