<?php

namespace Phpactor\LanguageServer\Core\Server;

use Phpactor\LanguageServer\Core\Server\Client\ClientClient;
use Phpactor\LanguageServer\Core\Server\Client\DiagnosticsClient;
use Phpactor\LanguageServer\Core\Server\Client\WindowClient;
use Phpactor\LanguageServer\Core\Server\Client\WorkDoneProgressClient;
use Phpactor\LanguageServer\Core\Server\Client\WorkspaceClient;

final class ClientApi
{
    /**
     * @var RpcClient
     */
    private $client;

    public function __construct(RpcClient $client)
    {
        $this->client = $client;
    }

    public function client(): ClientClient
    {
        return new ClientClient($this->client);
    }

    public function window(): WindowClient
    {
        return new WindowClient($this->client);
    }

    public function workspace(): WorkspaceClient
    {
        return new WorkspaceClient($this->client);
    }

    public function diagnostics(): DiagnosticsClient
    {
        return new DiagnosticsClient($this->client);
    }

    public function workDoneProgress(): WorkDoneProgressClient
    {
        return new WorkDoneProgressClient($this->client);
    }
}
