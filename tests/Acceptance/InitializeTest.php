<?php

namespace Phpactor\LanguageServer\Tests\Acceptance;

use Closure;
use Generator;
use Phpactor\LanguageServer\Core\Serializer\JsonSerializer;

class InitializeTest extends AcceptanceTestCase
{
    public function testInitialize()
    {
        $responses = $this->playback('initialize.script');

        $this->assertResponse(function ($data) {
            $this->assertArrayHasKey('capabilities', $data['result']);
        }, $responses);
    }

    protected function assertResponse(Closure $assertion, Generator $generator)
    {
        $deserializer = new JsonSerializer();
        $response = $generator->current();
        $assertion($deserializer->deserialize($response->body()));
        $generator->next();
    }
}
