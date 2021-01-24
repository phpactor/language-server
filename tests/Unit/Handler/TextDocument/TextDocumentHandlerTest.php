<?php

namespace Phpactor\LanguageServer\Tests\Unit\Handler\TextDocument;

use Phpactor\LanguageServerProtocol\TextDocumentIdentifier;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Event\TextDocumentClosed;
use Phpactor\LanguageServer\Event\TextDocumentOpened;
use Phpactor\LanguageServer\Event\TextDocumentSaved;
use Phpactor\LanguageServer\Event\TextDocumentUpdated;
use Phpactor\LanguageServer\Handler\TextDocument\TextDocumentHandler;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\LanguageServer\Tests\Unit\Handler\HandlerTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\EventDispatcher\EventDispatcherInterface;

class TextDocumentHandlerTest extends HandlerTestCase
{
    /**
     * @var Workspace
     */
    private $workspace;

    /**
     * @var ObjectProphecy
     */
    private $dispatcher;

    protected function setUp(): void
    {
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
    }

    public function handler(): Handler
    {
        return new TextDocumentHandler(
            $this->dispatcher->reveal()
        );
    }

    public function testOpensDocument(): void
    {
        $textDocument = ProtocolFactory::textDocumentItem('foobar', 'foo');
        $this->dispatch('textDocument/didOpen', [
            'textDocument' => $textDocument
        ]);

        $this->dispatcher->dispatch(new TextDocumentOpened($textDocument))->shouldHaveBeenCalled();
    }

    public function testUpdatesDocument(): void
    {
        $textDocument = ProtocolFactory::textDocumentItem('foobar', 'foo');
        $identifier = ProtocolFactory::versionedTextDocumentIdentifier('foobar');

        $this->dispatch('textDocument/didChange', [
            'textDocument' => $identifier,
            'contentChanges' => [
                [
                    'text' => 'asd',
                ]
            ],
        ]);

        $this->dispatcher->dispatch(new TextDocumentUpdated($identifier, 'asd'))->shouldHaveBeenCalled();
    }

    public function testWillSave(): void
    {
        $response = $this->dispatch('textDocument/willSave', [
            'textDocument' => ProtocolFactory::textDocumentIdentifier('foobar'),
            'reason' => 1
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
                ]
            ],
        ]);

        $this->dispatcher->dispatch(new TextDocumentClosed($identifier))->shouldHaveBeenCalled();
    }

    public function testSavesDocument(): void
    {
        $identifier = ProtocolFactory::versionedTextDocumentIdentifier('foobar');
        $this->dispatch('textDocument/didSave', [
            'textDocument' => $identifier,
            'text' => 'hello',
        ]);

        $this->dispatcher->dispatch(new TextDocumentSaved($identifier, 'hello'))->shouldHaveBeenCalled();
    }
}
