<?php

namespace Phpactor\LanguageServer\Tests\Integration\Extension\Example;

use LanguageServerProtocol\MessageType;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\LanguageServer\Extension\LanguageServerExtension;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\MapResolver\Resolver;

class TestExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register('test.handler', function (Container $container) {
            return new class implements Handler {
                public function methods(): array
                {
                    return ['test' => 'test'];
                }

                public function test()
                {
                    yield null;
                    yield new NotificationMessage('window/showMessage', [
                        'type' => MessageType::INFO,
                        'message' => 'Hallo',
                    ]);
                }
            };
        }, [ LanguageServerExtension::TAG_SESSION_HANDLER => []]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
