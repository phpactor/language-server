<?php

namespace Phpactor\LanguageServer\Handler\System;

use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Server\RpcClient;
use Phpactor\LanguageServer\Core\Service\ServiceManager;

class ServiceHandler implements Handler
{
    /**
     * @var ServiceManager
     */
    private $manager;

    /**
     * @var ClientApi
     */
    private $client;

    public function __construct(ServiceManager $manager, ClientApi $client)
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

    public function runningServices(): void
    {
        $this->client->window()->showMessage()->info(sprintf(
            'Running services: "%s"',
            implode('", "', $this->manager->runningServices())
        ));
    }
}
