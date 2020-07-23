<?php

namespace Phpactor\LanguageServer\Handler\System;

use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Server\RpcClient;
use Phpactor\LanguageServer\Core\Service\ServiceManager;

class ServiceHandler implements Handler
{
    /**
     * @var ServiceManager
     */
    private $manager;

    /**
     * @var RpcClient
     */
    private $client;

    public function __construct(ServiceManager $manager, RpcClient $client)
    {
        $this->manager = $manager;
        $this->client = $client;
    }

    /**
     * {@inheritDoc}
     */
    public function methods(): array
    {
        return [
            'service/start' => 'startService',
            'service/stop' => 'stopService',
            'service/running' => 'runningServices',
        ];
    }

    public function startService(string $name): void
    {
        $this->manager->start($name);
    }

    public function stopService(string $name): void
    {
        $this->manager->stop($name);
    }

    public function runningServices(ServiceManager $manager): void
    {
        $this->client->notification('window/showMessage', [
            'type' => 'info',
            'message' => sprintf(
                'Running services: "%s"',
                implode('", "', $manager->runningServices())
            )
        ]);
    }
}
