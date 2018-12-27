<?php

namespace Phpactor\LanguageServer\Tests\Acceptance;

class InitializeTest extends AcceptanceTestCase
{
    /**
     * @dataProvider provideScripts
     */
    public function testInitialize(string $scriptPath)
    {
        $response = $this->playback($scriptPath);
        var_dump($response);

    }

    public function provideScripts()
    {
        yield 'autozimzu-LanguageClient' => [ 'autozimzu/initialize.script' ];
        yield 'ale' => [ 'ale/initialize.script' ];
    }
}
