<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Dispatcher\ArgumentResolver;

use Amp\CancellationToken;
use Amp\CancellationTokenSource;
use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\LanguageSeverProtocolParamsResolver;
use Phpactor\LanguageServer\Core\Dispatcher\Exception\CouldNotResolveArguments;

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
        $resolvedArgs = $resolver->resolveArguments($handler, 'initialize', $args);

        self::assertEquals([
            InitializeParams::fromArray($args),
        ], $resolvedArgs);
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

        $resolvedArgs = $resolver->resolveArguments($handler, 'initializeWrongOrder', $args);

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
}
