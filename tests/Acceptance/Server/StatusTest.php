<?php

namespace Phpactor\LanguageServer\Tests\Acceptance\Server;

use Phpactor\LanguageServer\Tests\Acceptance\AcceptanceTestCase;

class StatusTest extends AcceptanceTestCase
{
    public function testStatus()
    {
        $responses = $this->playback('autozimzu/server_status.script');

        $responses->next();
        $responses->next();
        $responses->next();

        $this->assertResponse(function ($data) {
            $this->assertContains('up:', $data['params']['message']);
        }, $responses);
    }
}
