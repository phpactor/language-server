<?php

namespace Phpactor\LanguageServer\Extension;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\LanguageServer\Extension\Handler\PhpactorHandlerLoader;
use Phpactor\LanguageServer\Extension\Handler\SessionHandler;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\LanguageServer\Extension\Command\StartCommand;
use Phpactor\LanguageServer\Core\Session\Workspace;
use Phpactor\LanguageServer\Handler\TextDocument\TextDocumentHandler;
use Phpactor\LanguageServer\LanguageServerBuilder;
use Phpactor\MapResolver\Resolver;

class LanguageServerExtension implements Extension
{
    const SERVICE_LANGUAGE_SERVER_BUILDER = 'language_server.builder';
    const SERVICE_EVENT_EMITTER = 'language_server.event_emitter';

    const TAG_SESSION_HANDLER = 'language_server.session_handler';

    const PARAM_WELCOME_MESSAGE = 'language_server.welcome_message';
    const SERVICE_SESSION_WORKSPACE = 'language_server.session.workspace';

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
        $schema->setDefaults([
            self::PARAM_WELCOME_MESSAGE => 'Welcome to a Phpactor Language Server'
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $this->registerServer($container);
        $this->registerCommand($container);
        $this->registerSession($container);
    }

    private function registerServer(ContainerBuilder $container)
    {
        $container->register(self::SERVICE_LANGUAGE_SERVER_BUILDER, function (Container $container) {
            $builder = LanguageServerBuilder::create(
                $container->get(LoggingExtension::SERVICE_LOGGER)
            );
            $builder->addHandlerLoader(
                $container->get('language_server.handler_loader.phpactor')
            );
        
            return $builder;
        });

        $container->register('language_server.handler_loader.phpactor', function (Container $container) {
            return new PhpactorHandlerLoader($container);
        });
    }

    private function registerCommand(ContainerBuilder $container)
    {
        $container->register('language_server.command.lsp_start', function (Container $container) {
            return new StartCommand($container->get(self::SERVICE_LANGUAGE_SERVER_BUILDER));
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => StartCommand::NAME ]]);
    }

    private function registerSession(ContainerBuilder $container)
    {
        $container->register(self::SERVICE_SESSION_WORKSPACE, function (Container $container) {
            return new Workspace();
        });

        $container->register('language_server.session.handler.text_document', function (Container $container) {
            return new TextDocumentHandler($container->get(self::SERVICE_SESSION_WORKSPACE));
        }, [ self::TAG_SESSION_HANDLER => []]);

        $container->register('language_server.session.handler.session', function (Container $container) {
            return new SessionHandler($container);
        }, [ self::TAG_SESSION_HANDLER => []]);
    }
}
