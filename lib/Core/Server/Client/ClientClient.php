<?php

namespace Phpactor\LanguageServer\Core\Server\Client;

use Amp\Promise;
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

    public function registerCapability(Registration ...$registrations): Promise
    {
        return $this->client->request('client/registerCapability', [
            'registrations' => $registrations
        ]);
    }

    public function unregisterCapability(Unregistration ...$unregistrations): Promise
    {
        return $this->client->request('client/unregisterCapability', [
            'unregistrations' => $unregistrations
        ]);
    }
}
