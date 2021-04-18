<?php

namespace Phpactor\LanguageServer\Tests\Unit\Handler\Workspace;

use Amp\Success;
use Phly\EventDispatcher\ListenerProvider\ListenerProviderAggregate;
use Phpactor\LanguageServerProtocol\DidChangeWatchedFilesParams;
use Phpactor\LanguageServerProtocol\FileChangeType;
use Phpactor\LanguageServerProtocol\FileEvent;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServer\Core\Command\Command;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Server\RpcClient\TestRpcClient;
use Phpactor\LanguageServer\Event\FileCreated;
use Phpactor\LanguageServer\Event\FilesChanged;
use Phpactor\LanguageServer\Event\Initialized;
use Phpactor\LanguageServer\Handler\Workspace\CommandHandler;
use Phpactor\LanguageServer\Handler\Workspace\DidChangeWatchedFilesHandler;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;
use Phpactor\LanguageServer\Test\ListenerProvider\RecordingListenerProvider;
use Phpactor\LanguageServer\Tests\Unit\Handler\HandlerTestCase;
use Phpactor\LanguageServer\Core\Command\CommandDispatcher;
use Phpactor\TestUtils\PHPUnit\TestCase;
use Psr\EventDispatcher\ListenerProviderInterface;
use function Amp\Promise\wait;
use function Amp\delay;

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
