<?php

namespace Phpactor\LanguageServer\Tests\Integration\Middleware\Handlers;

use Amp\Promise;
use Generator;
use Phpactor\LanguageServer\Adapter\Psr\AggregateEventDispatcher;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Rpc\RawMessage;
use Phpactor\LanguageServer\Core\Server\Parser\LspMessageReader;
use Phpactor\LanguageServer\Core\Server\Stream\DuplexStream;
use Phpactor\LanguageServer\Core\Server\Transmitter\LspMessageFormatter;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\LanguageServer\Handler\TextDocument\FormattingHandler;
use Phpactor\LanguageServer\Handler\TextDocument\TextDocumentHandler;
use Phpactor\LanguageServer\Listener\WorkspaceListener;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\LanguageServer\Tests\Unit\Handler\TextDocument\TestFormatter;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use PHPUnit\Framework\Assert;

class FormattingHandlersTest extends HandlersTestCase
{
    /**
     * @return Generator<Promise<string>>
     */
    public function testDispatchesFormattingRequest(): Generator
    {
        [$server, $stream] = yield $this->startServer($this->createHandlers());

        $formatter = new LspMessageFormatter();
        $reader = new LspMessageReader($stream);

        $response = yield $this->dispatchRequest(
            $stream,
            $formatter,
            $reader,
            new RequestMessage(1, 'textDocument/didOpen', [
                'textDocument' => [
                    'uri' => 'file://foobar',
                    'languageId' => 'php',
                    'version' => 1,
                    'text' => 'barfoo',
                ],
            ]),
        );

        Assert::assertArrayNotHasKey('error', $response->body());

        $response = yield $this->dispatchRequest(
            $stream,
            $formatter,
            $reader,
            new RequestMessage(1, 'textDocument/formatting', [
                'textDocument' => [
                    'uri' => 'file://foobar',
                ],
                'options' => [
                    'tabSize' => 4,
                    'insertSpaces' => true,
                ],
            ]),
        );

        Assert::assertArrayNotHasKey('error', $response->body());

        $server->shutdown();
    }

    /**
     * @return Promise<RawMessage>
     */
    protected function dispatchRequest(
        DuplexStream $stream,
        LspMessageFormatter $formatter,
        LspMessageReader $reader,
        RequestMessage $request,
    ): Promise {
        return \Amp\call(function () use ($request, $stream, $formatter, $reader) {
            $message = $formatter->format($request);
            yield $stream->write($message);
            return yield $reader->wait();
        });
    }

    private function createHandlers(): Handlers
    {
        $workspace = new Workspace();
        $formatter = new TestFormatter(
            ProtocolFactory::textEdit(0, 0, 0, 0, 'Hello'),
        );

        $dispatcher = new AggregateEventDispatcher(
            new WorkspaceListener($workspace),
        );

        return new Handlers(
            new TextDocumentHandler($dispatcher),
            new FormattingHandler($workspace, $formatter),
        );
    }
}
