<?php

namespace Phpactor\LanguageServer\Tests\Unit\Listener;

use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServerProtocol\VersionedTextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\LanguageServer\Listener\WorkspaceListener;
use Phpactor\LanguageServer\Event\TextDocumentClosed;
use Phpactor\LanguageServer\Event\TextDocumentOpened;
use Phpactor\LanguageServer\Event\TextDocumentUpdated;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\TestUtils\PHPUnit\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class WorkspaceListenerTest extends TestCase
{
    /**
     * @var Workspace|ObjectProphecy
     */
    private $workspace;

    /**
     * @var WorkspaceListener
     */
    private $listener;

    protected function setUp(): void
    {
        $this->workspace = $this->prophesize(Workspace::class);
        $this->listener = new WorkspaceListener($this->workspace->reveal());
    }

    public function testClosed(): void
    {
        $this->workspace->remove(ProtocolFactory::textDocumentIdentifier('file://test'))->shouldBeCalled();

        $this->dispatch(
            new TextDocumentClosed(
                ProtocolFactory::textDocumentIdentifier('file://test')
            )
        );
    }

    public function testOpened(): void
    {
        $document = $this->createDocument('file://test');
        $this->workspace->open($document)->shouldBeCalled();

        $this->dispatch(
            new TextDocumentOpened($document)
        );
    }

    public function testUpdated(): void
    {
        $identifier = new VersionedTextDocumentIdentifier('file://test', 1);
        $this->workspace->update(
            $identifier,
            'new text'
        )->shouldBeCalled();

        $this->dispatch(
            new TextDocumentUpdated($identifier, 'new text')
        );
    }

    private function dispatch(object $event): void
    {
        foreach ($this->listener->getListenersForEvent($event) as $listener) {
            $listener($event);
        };
    }

    private function createDocument(string $uri): TextDocumentItem
    {
        return ProtocolFactory::textDocumentItem($uri, 'text');
    }
}
