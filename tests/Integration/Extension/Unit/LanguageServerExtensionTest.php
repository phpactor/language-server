<?php

namespace Phpactor\LanguageServer\Tests\Integration\Extension\Unit;

class LanguageServerExtensionTest extends LanguageServerTestCase
{
    public function testInitializesLanguageServer()
    {
        $serverTester = $this->createTester();
        $serverTester->initialize();
    }

    public function testLoadsTextDocuments()
    {
        $serverTester = $this->createTester();
        $responses = $serverTester->initialize();
        $serverTester->assertSuccess($responses);
    }

    public function testLoadsHandlers()
    {
        $serverTester = $this->createTester();
        $serverTester->initialize();
        $responses = $serverTester->dispatch('test', []);
        $this->assertCount(2, $responses);

        $this->assertTrue($serverTester->assertSuccess($responses));
    }
}
