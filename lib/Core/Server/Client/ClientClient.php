<?php

namespace Phpactor\LanguageServer\Core\Server\Client;

use Phpactor\LanguageServerProtocol\Registration;
use Phpactor\LanguageServerProtocol\Unregistration;
use Phpactor\LanguageServer\Core\Server\RpcClient;

final class ClientClient
{
    /**
     * @var RpcClient
     */
    private $client;

    public function __construct(RpcClient $client)
    {
        $this->client = $client;
    }

    public function registerCapability(Registration ...$registrations): void
    {
        $this->client->notification('client/registerCapability', [
            'registrations' => $registrations
        ]);
    }

    public function unregisterCapability(Unregistration ...$unregistrations): void
    {
        $this->client->notification('client/unregisterCapability', [
            'unregistrations' => $unregistrations
        ]);
    }
}
