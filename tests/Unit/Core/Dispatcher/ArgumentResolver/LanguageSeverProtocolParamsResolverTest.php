<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Dispatcher\ArgumentResolver;

use Amp\CancellationToken;
use Amp\CancellationTokenSource;
use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\LanguageSeverProtocolParamsResolver;
use Phpactor\LanguageServer\Core\Dispatcher\Exception\CouldNotResolveArguments;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Test\ProtocolFactory;

class LanguageSeverProtocolParamsResolverTest extends TestCase
{
    public function testResolvesLspParams(): void
    {
        $handler = new LspHandler();
        $resolver = new LanguageSeverProtocolParamsResolver();
        $args = [
            'capabilities' => [
            ],
            'rootUri' => 'file://tmp/foo',
        ];
        $resolvedArgs = $resolver->resolveArguments($handler, 'initialize', ProtocolFactory::requestMessage('foo', $args));

        self::assertEquals([
            InitializeParams::fromArray($args),
        ], $resolvedArgs);
    }

    public function testResolvesRawRequestMessage(): void
    {
        $handler = new LspHandler();
        $resolver = new LanguageSeverProtocolParamsResolver();
        $args = [
            'foo' => 'bar',
        ];
        $message = ProtocolFactory::requestMessage('foo', $args);
        $resolvedArgs = $resolver->resolveArguments($handler, 'rawRequest', $message);

        self::assertEquals([$message], $resolvedArgs);
    }

    public function testResolvesRawNotification(): void
    {
        $handler = new LspHandler();
        $resolver = new LanguageSeverProtocolParamsResolver();
        $args = [
            'foo' => 'bar',
        ];
        $message = ProtocolFactory::notificationMessage('foo', $args);
        $resolvedArgs = $resolver->resolveArguments($handler, 'rawNotification', $message);

        self::assertEquals([$message], $resolvedArgs);
    }

    public function testNotResolvableWhenFirstParamNotProtocolParams(): void
    {
        $this->expectException(CouldNotResolveArguments::class);
        $this->expectExceptionMessage('First argument');
        $handler = new LspHandler();
        $resolver = new LanguageSeverProtocolParamsResolver();

        $args = [
            'capabilities' => [
            ],
            'rootUri' => 'file://tmp/foo',
        ];
        $cancellationToken = (new CancellationTokenSource())->getToken();
        $extraArgs = [
            'cancel' => $cancellationToken
        ];

        $resolvedArgs = $resolver->resolveArguments($handler, 'initializeWrongOrder', ProtocolFactory::requestMessage('foo', $args));

        self::assertEquals([
            InitializeParams::fromArray($args),
            $cancellationToken
        ], $resolvedArgs);
    }
}

class LspHandler
{
    public function initialize(InitializeParams $params, CancellationToken $c): void
    {
    }

    public function initializeWrongOrder(CancellationToken $c, InitializeParams $params): void
    {
    }

    public function rawRequest(RequestMessage $request): void
    {
    }

    public function rawNotification(NotificationMessage $notification): void
    {
    }
}
