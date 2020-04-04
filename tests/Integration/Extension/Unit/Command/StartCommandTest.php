<?php

namespace Phpactor\LanguageServer\Tests\Integration\Extension\Unit\Command;

use Phpactor\LanguageServer\Tests\Integration\Extension\Unit\LanguageServerTestCase;
use RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;

class StartCommandTest extends LanguageServerTestCase
{
    /**
     * @var CommandTester
     */
    private $tester;

    public function setUp()
    {
        $container = $this->createContainer([]);
        $this->tester = new CommandTester($container->get('language_server.command.lsp_start'));
    }

    public function testRecordToNonExistingFile()
    {
        $this->expectException(RuntimeException::class);
        $this->tester->execute([
            '--record' => 'foobar/ads',
        ]);
    }
}
