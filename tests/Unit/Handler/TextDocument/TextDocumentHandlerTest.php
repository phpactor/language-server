<?php

namespace Phpactor\LanguageServer\Tests\Unit\Handler\TextDocument;

use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServerProtocol\TextDocumentContentChangeIncrementalEvent;
use Phpactor\LanguageServerProtocol\TextDocumentIdentifier;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServerProtocol\TextDocumentSyncKind;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Event\TextDocumentClosed;
use Phpactor\LanguageServer\Event\TextDocumentIncrementallyUpdated;
use Phpactor\LanguageServer\Event\TextDocumentOpened;
use Phpactor\LanguageServer\Event\TextDocumentSaved;
use Phpactor\LanguageServer\Event\TextDocumentUpdated;
use Phpactor\LanguageServer\Handler\TextDocument\TextDocumentHandler;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\LanguageServer\Tests\Unit\Handler\HandlerTestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\EventDispatcher\EventDispatcherInterface;

class TextDocumentHandlerTest extends HandlerTestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<EventDispatcherInterface>
     */
    private ObjectProphecy $dispatcher;

    /**
     * @var TextDocumentSyncKind::*
     */
    private int $syncKind = TextDocumentSyncKind::FULL;

    protected function setUp(): void
    {
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
    }

    public function handler(): Handler
    {
        return new TextDocumentHandler(
            $this->dispatcher->reveal(),
            $this->syncKind,
        );
    }

    public function testSyncKindFull(): void
    {
        $handler = $this->handler();
        self::assertInstanceOf(CanRegisterCapabilities::class, $handler);
        $capabiltiies = new ServerCapabilities();
        $handler->registerCapabiltiies($capabiltiies);
        self::assertEquals(TextDocumentSyncKind::FULL, $capabiltiies->textDocumentSync);
    }

    public function testSyncKindIncremental(): void
    {
        $this->syncKind = TextDocumentSyncKind::INCREMENTAL;
        $handler = $this->handler();
        self::assertInstanceOf(CanRegisterCapabilities::class, $handler);
        $capabiltiies = new ServerCapabilities();
        $handler->registerCapabiltiies($capabiltiies);
        self::assertEquals(TextDocumentSyncKind::INCREMENTAL, $capabiltiies->textDocumentSync);

    }

    public function testOpensDocument(): void
    {
        $textDocument = ProtocolFactory::textDocumentItem('foobar', 'foo');
        $this->dispatch('textDocument/didOpen', [
            'textDocument' => $textDocument,
        ]);

        $this->dispatcher->dispatch(new TextDocumentOpened($textDocument))->shouldHaveBeenCalled();
    }

    public function testUpdatesDocument(): void
    {
        $textDocument = ProtocolFactory::textDocumentItem('foobar', 'foo');
        $identifier = ProtocolFactory::versionedTextDocumentIdentifier('foobar', 1);

        $this->dispatch('textDocument/didChange', [
            'textDocument' => $identifier,
            'contentChanges' => [
                [
                    'text' => 'asd',
                ],
            ],
        ]);

        $this->dispatcher->dispatch(new TextDocumentUpdated($identifier, 'asd'))->shouldHaveBeenCalled();
    }

    public function testUpdatesDocumentIncrementally(): void
    {
        $textDocument = ProtocolFactory::textDocumentItem('foobar', 'foo');
        $identifier = ProtocolFactory::versionedTextDocumentIdentifier('foobar', 1);

        $this->dispatch('textDocument/didChange', [
            'textDocument' => $identifier,
            'contentChanges' => [
                [
                    'range' => [
                        'start' => [
                            'character' => 0,
                            'line' => 75,
                        ],
                        'end' => [
                            'character' => 0,
                            'line' => 75,
                        ],
                    ],
                    'rangeLength' => 1,
                    'text' => 'hello',
                ],
            ],
        ]);

        $this->dispatcher->dispatch(
            new TextDocumentIncrementallyUpdated(
                $identifier,
                [
                    new TextDocumentContentChangeIncrementalEvent(
                        new Range(
                            new Position(75, 0),
                            new Position(75, 0),
                        ),
                        rangeLength: 1,
                        text: 'hello',
                    ),
                ],
            )
        )->shouldHaveBeenCalled();
    }

    public function testWillSave(): void
    {
        $response = $this->dispatch('textDocument/willSave', [
            'textDocument' => ProtocolFactory::textDocumentIdentifier('foobar'),
            'reason' => 1,
        ]);
        self::assertInstanceOf(ResponseMessage::class, $response);
        self::assertNull($response->result);
    }

    public function testClosesDocument(): void
    {
        $document = new TextDocumentItem('foobar', 'php', 1, 'foo');
        $document->uri = 'foobar';
        $identifier = new TextDocumentIdentifier('foobar');
        $this->dispatch('textDocument/didClose', [
            'textDocument' => $identifier,
            'contentChanges' => [
                [
                    'text' => 'asd',
                ],
            ],
        ]);

        $this->dispatcher->dispatch(new TextDocumentClosed($identifier))->shouldHaveBeenCalled();
    }

    public function testSavesDocument(): void
    {
        $identifier = ProtocolFactory::versionedTextDocumentIdentifier('foobar', 1);
        $this->dispatch('textDocument/didSave', [
            'textDocument' => $identifier,
            'text' => 'hello',
        ]);

        $this->dispatcher->dispatch(new TextDocumentSaved($identifier, 'hello'))->shouldHaveBeenCalled();
    }
}
