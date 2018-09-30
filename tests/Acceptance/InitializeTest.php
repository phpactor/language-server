<?php

namespace Phpactor\LanguageServer\Tests\Acceptance;

class InitializeTest extends AcceptanceTestCase
{
    /**
     * @dataProvider provideScripts
     */
    public function testInitialize(string $scriptPath)
    {
        $responses = $this->playback($scriptPath);

        $this->assertResponse(function ($data) {
            $this->assertArrayHasKey('capabilities', $data['result']);
        }, $responses);
    }

    public function provideScripts()
    {
        yield 'autozimzu-LanguageClient' => [ 'autozimzu/initialize.script' ];
        yield 'ale' => [ 'ale/initialize.script' ];
    }
}
