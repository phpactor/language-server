<?php

namespace Phpactor\LanguageServer\Tests\Unit\Handler\Workspace;

use Phpactor\LanguageServerProtocol\DidChangeWatchedFilesParams;
use Phpactor\LanguageServerProtocol\FileChangeType;
use Phpactor\LanguageServerProtocol\FileEvent;
use Phpactor\LanguageServer\Event\FilesChanged;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;
use Phpactor\LanguageServer\Test\ListenerProvider\RecordingListenerProvider;
use Phpactor\TestUtils\PHPUnit\TestCase;

class DidChangeWatchedFilesHandlerTest extends TestCase
{
    public function testRegisterCapability(): void
    {
        $tester = LanguageServerTesterBuilder::createBare()
            ->enableFileEvents()
            ->build();
        $tester->initialize();

        // capability registration happens after the intiialization request has been returned
        $this->addToAssertionCount(1);
    }

    public function testEmitsFileChangedEvents(): void
    {
        $events = new RecordingListenerProvider();
        $tester = LanguageServerTesterBuilder::create()
            ->enableFileEvents()
            ->addListenerProvider($events)
            ->build();

        $tester->notifyAndWait('workspace/didChangeWatchedFiles', new DidChangeWatchedFilesParams([
            new FileEvent('file://foobar', FileChangeType::CREATED)
        ]));
        $event = $events->shift(FilesChanged::class);
        self::assertEquals(new FilesChanged(new FileEvent('file://foobar', FileChangeType::CREATED)), $event);
    }
}
