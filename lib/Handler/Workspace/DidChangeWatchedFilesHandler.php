<?php

namespace Phpactor\LanguageServer\Handler\Workspace;

use Phpactor\LanguageServerProtocol\DidChangeTextDocumentNotification;
use Phpactor\LanguageServerProtocol\DidChangeWatchedFilesParams;
use Phpactor\LanguageServerProtocol\DidChangeWatchedFilesRegistrationOptions;
use Phpactor\LanguageServerProtocol\FileSystemWatcher;
use Phpactor\LanguageServerProtocol\Registration;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Event\FilesChanged;
use Phpactor\LanguageServer\Event\Initialized;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Ramsey\Uuid\Uuid;
use function Amp\asyncCall;

class DidChangeWatchedFilesHandler implements ListenerProviderInterface, Handler
{
    /**
     * @var ClientApi
     */
    private $client;

    /**
     * @var array
     */
    private $globPatterns;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(ClientApi $client, EventDispatcherInterface $dispatcher, array $globPatterns)
    {
        $this->client = $client;
        $this->globPatterns = $globPatterns;
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function getListenersForEvent(object $event): iterable
    {
        if ($event instanceof Initialized) {
            return [[$this, 'registerCapability']];
        }

        return [];
    }

    public function registerCapability(Initialized $initialized): void
    {
        asyncCall(function () {
            yield $this->client->client()->registerCapability(
            new Registration(
                Uuid::uuid4()->__toString(),
                'workspace/didChangeWatchedFiles',
                new DidChangeWatchedFilesRegistrationOptions(array_map(function (string $glob) {
                    return new FileSystemWatcher($glob);
                }, $this->globPatterns))
            ));
        });
    }

    /**
     * {@inheritDoc}
     */
    public function methods(): array
    {
        return [
            'workspace/didChangeWatchedFiles' => 'didChange'
        ];
    }

    public function didChange(DidChangeWatchedFilesParams $params)
    {
        $this->dispatcher->dispatch(new FilesChanged(...$params->changes));
    }
}
