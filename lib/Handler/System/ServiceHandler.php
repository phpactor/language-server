<?php

namespace Phpactor\LanguageServer\Handler\System;

use Phpactor\LanguageServer\Core\Handler\Handler;
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
        ];
    }

    public function startService(ServiceManager $manager, string $name)
    {
        $manager->start($name);
    }

    public function stopService(ServiceManager $manager, string $name)
    {
        $manager->stop($name);
    }
}
