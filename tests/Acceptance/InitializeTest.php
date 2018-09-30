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
}
