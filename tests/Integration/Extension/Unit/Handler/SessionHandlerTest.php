<?php

namespace Phpactor\LanguageServer\Tests\Integration\Extension\Unit\Handler;

use Phpactor\LanguageServer\Tests\Integration\Extension\Unit\LanguageServerTestCase;

class SessionHandlerTest extends LanguageServerTestCase
{
    public function testSessionHandler()
    {
        $tester = $this->createTester();
        $tester->initialize();
        $responses = $tester->dispatch('session/dumpConfig', []);
        $tester->assertSuccess($responses);
        $this->assertCount(2, $responses);
    }
}
