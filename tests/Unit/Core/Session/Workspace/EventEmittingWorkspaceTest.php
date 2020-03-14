<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Session\Workspace;

use LanguageServerProtocol\TextDocumentIdentifier;
use LanguageServerProtocol\TextDocumentItem;
use LanguageServerProtocol\VersionedTextDocumentIdentifier;
use League\Event\EmitterInterface;
use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Event\DocumentOpened;
use Phpactor\LanguageServer\Core\Event\DocumentRemoved;
use Phpactor\LanguageServer\Core\Event\DocumentUpdated;
use Phpactor\LanguageServer\Core\Session\Workspace;
use Phpactor\LanguageServer\Core\Session\Workspace\EventEmittingWorkspace;
use Prophecy\Prophecy\ObjectProphecy;

class EventEmittingWorkspaceTest extends TestCase
{
    const EXAMPLE_TEXT = 'abcd';

    /**
     * @var ObjectProphecy
     */
    private $innerWorkspace;

    /**
     * @var ObjectProphecy
     */
    private $emitter;

    /**
     * @var EventEmittingWorkspace
     */
    private $workspace;

    protected function setUp(): void
    {
        $this->innerWorkspace = $this->prophesize(Workspace::class);
        $this->emitter = $this->prophesize(EmitterInterface::class);
        $this->workspace = new EventEmittingWorkspace($this->innerWorkspace->reveal(), $this->emitter->reveal());
    }

    public function testDocumentOpened()
    {
        $textDocument = new TextDocumentItem('file://abc', 'php', 1, self::EXAMPLE_TEXT);
        $this->workspace->open($textDocument);

        $this->innerWorkspace->open($textDocument)->shouldBeCalled();
        $this->emitter->emit(
            new DocumentOpened($textDocument)
        )->shouldHaveBeenCalled();
    }

    public function testDocumentUpdated()
    {
        $identifier = new VersionedTextDocumentIdentifier('file://abc', 1);
        $this->workspace->update($identifier, self::EXAMPLE_TEXT);

        $this->innerWorkspace->update($identifier, self::EXAMPLE_TEXT)->shouldBeCalled();
        $this->emitter->emit(
            new DocumentUpdated($identifier, self::EXAMPLE_TEXT)
        )->shouldHaveBeenCalled();
    }

    public function testDocumentRemoved()
    {
        $identifier = new TextDocumentIdentifier('file://abc');
        $this->workspace->remove($identifier);

        $this->innerWorkspace->remove($identifier)->shouldBeCalled();
        $this->emitter->emit(
            new DocumentRemoved($identifier)
        )->shouldHaveBeenCalled();
    }
}
