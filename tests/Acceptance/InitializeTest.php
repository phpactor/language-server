<?php

namespace Phpactor\LanguageServer\Tests\Acceptance;

class InitializeTest extends AcceptanceTestCase
{
    /**
     * @dataProvider provideScripts
     */
    public function testInitialize(string $scriptPath)
    {
        $script = file_get_contents(__DIR__ . '/scripts/' .$scriptPath);
        $client = $this->client();

        $response = $client->send($script);

        $this->assertArrayHasKey('result', $response->body());
        $this->assertArrayHasKey('capabilities', $response->body()['result']);
    }

    public function provideScripts()
    {
        yield 'autozimzu-LanguageClient' => [ 'autozimzu/initialize.script' ];
        yield 'ale' => [ 'ale/initialize.script' ];
    }
}
