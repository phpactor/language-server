<?php

namespace Phpactor\LanguageServer\Core\Server;

use Phpactor\LanguageServer\Core\Server\Client\WindowClient;
use Phpactor\LanguageServer\Core\Server\Client\WorkspaceClient;

class ClientApi
{
    /**
     * @var RpcClient
     */
    private $client;

    public function __construct(RpcClient $client)
    {
        $this->client = $client;
    }

    public function window(): WindowClient
    {
        return new WindowClient($this->client);
    }

    public function workspace(): WorkspaceClient
    {
        return new WorkspaceClient($this->client);
    }
}
