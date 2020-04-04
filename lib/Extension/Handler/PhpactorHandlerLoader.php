<?php

namespace Phpactor\LanguageServer\Extension\Handler;

use LanguageServerProtocol\InitializeParams;
use Phpactor\Container\Container;
use Phpactor\Container\PhpactorContainer;
use Phpactor\LanguageServer\Extension\LanguageServerExtension;
use Phpactor\FilePathResolverExtension\FilePathResolverExtension;
use Phpactor\LanguageServer\Core\Handler\HandlerLoader;
use Phpactor\LanguageServer\Core\Handler\Handlers;

class PhpactorHandlerLoader implements HandlerLoader
{
    /**
     * @var Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function load(InitializeParams $params): Handlers
    {
        $container = $this->createContainer($params->rootUri);
        $handlers = [];

        foreach (array_keys(
            $container->getServiceIdsForTag(LanguageServerExtension::TAG_SESSION_HANDLER)
        ) as $serviceId) {
            $handlers[] = $container->get($serviceId);
        }

        return new Handlers($handlers);
    }

    protected function createContainer(string $rootUri): Container
    {
        $container = $this->container;
        $parameters = $container->getParameters();
        $parameters[FilePathResolverExtension::PARAM_PROJECT_ROOT] = $rootUri;

        $container = PhpactorContainer::fromExtensions(
            $container->getParameter(
                PhpactorContainer::PARAM_EXTENSION_CLASSES
            ),
            $parameters
        );

        return $container;
    }
}
