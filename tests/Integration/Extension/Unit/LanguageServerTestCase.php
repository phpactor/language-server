<?php

namespace Phpactor\LanguageServer\Tests\Integration\Extension\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\Container\Container;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\LanguageServer\Extension\LanguageServerExtension;
use Phpactor\LanguageServer\Tests\Integration\Extension\Example\TestExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\FilePathResolverExtension\FilePathResolverExtension;
use Phpactor\LanguageServer\LanguageServerBuilder;
use Phpactor\LanguageServer\Test\ServerTester;

class LanguageServerTestCase extends TestCase
{
    protected function createContainer(array $params = []): Container
    {
        return PhpactorContainer::fromExtensions([
            TestExtension::class,
            ConsoleExtension::class,
            LanguageServerExtension::class,
            LoggingExtension::class,
            FilePathResolverExtension::class
        ], $params);
    }

    protected function createTester(): ServerTester
    {
        $builder = $this->createContainer()->get(
            LanguageServerExtension::SERVICE_LANGUAGE_SERVER_BUILDER
        );
        
        $this->assertInstanceOf(LanguageServerBuilder::class, $builder);
        
        return $builder->buildServerTester();
    }
}
