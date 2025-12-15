<?php

namespace Phpactor\LanguageServer\Core\Server\Client;

use Amp\Promise;
use Phpactor\LanguageServerProtocol\Registration;
use Phpactor\LanguageServerProtocol\Unregistration;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\RpcClient;

final class ClientClient
{
    public function __construct(private RpcClient $client)
    {
    }

    /**
     * @return Promise<ResponseMessage>
     */
    public function registerCapability(Registration ...$registrations): Promise
    {
        return $this->client->request('client/registerCapability', [
            'registrations' => $registrations
        ]);
    }

    /**
     * @return Promise<ResponseMessage>
     */
    public function unregisterCapability(Unregistration ...$unregistrations): Promise
    {
        return $this->client->request('client/unregisterCapability', [
            'unregistrations' => $unregistrations
        ]);
    }
}
