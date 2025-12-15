<?php

namespace Phpactor\LanguageServer\Listener;

use Phpactor\LanguageServerProtocol\ClientCapabilities;
use Phpactor\LanguageServerProtocol\DidChangeWatchedFilesRegistrationOptions;
use Phpactor\LanguageServerProtocol\FileSystemWatcher;
use Phpactor\LanguageServerProtocol\Registration;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Event\Initialized;
use Psr\EventDispatcher\ListenerProviderInterface;
use Ramsey\Uuid\Uuid;
use function Amp\asyncCall;

class DidChangeWatchedFilesListener implements ListenerProviderInterface
{
    public function __construct(private ClientApi $client, private array $globPatterns, private ClientCapabilities $clientCapabilities)
    {
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
        if (!($this->clientCapabilities?->workspace?->didChangeWatchedFiles?->dynamicRegistration ?? false)) {
            return;
        }

        asyncCall(function () {
            yield $this->client->client()->registerCapability(
                new Registration(
                    Uuid::uuid4()->__toString(),
                    'workspace/didChangeWatchedFiles',
                    new DidChangeWatchedFilesRegistrationOptions(array_map(function (string $glob) {
                        return new FileSystemWatcher($glob);
                    }, $this->globPatterns))
                )
            );
        });
    }
}
