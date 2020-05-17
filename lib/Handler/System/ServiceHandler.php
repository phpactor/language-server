<?php

namespace Phpactor\LanguageServer\Handler\System;

use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Server\RpcClient;
use Phpactor\LanguageServer\Core\Server\RpcClient\JsonRpcClient;
use Phpactor\LanguageServer\Core\Service\ServiceManager;

class ServiceHandler implements Handler
{
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

    public function startService(ServiceManager $manager, string $name): void
    {
        $manager->start($name);
    }

    public function stopService(ServiceManager $manager, string $name): void
    {
        $manager->stop($name);
    }

    public function runningServices(ServiceManager $manager, RpcClient $client): void
    {
        $client->notification('window/showMessage', [
            'type' => 'info',
            'message' => sprintf(
                'Running services: "%s"',
                implode('", "', $manager->runningServices())
            )
        ]);
    }
}
