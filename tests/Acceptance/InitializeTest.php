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

        $responses = $client->send($script);
        $this->assertAllSuccess($responses);

        $response = $responses[0];
        $this->assertArrayHasKey('result', $response->body());
        $this->assertArrayHasKey('capabilities', $response->body()['result']);
    }

    public function provideScripts()
    {
        yield 'autozimzu-LanguageClient' => [ 'autozimzu/initialize.script' ];
        yield 'ale' => [ 'ale/initialize.script' ];
    }
}
