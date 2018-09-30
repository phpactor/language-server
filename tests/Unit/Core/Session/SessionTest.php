<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Session;

use DateInterval;
use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Session\Session;
use Phpactor\LanguageServer\Core\Session\Workspace;

class SessionTest extends TestCase
{
    public function testReturnsUptime()
    {
        $session = $this->create(__FILE__);
        $this->assertInstanceOf(DateInterval::class, $session->uptime());
    }

    public function testProvidesWorkspace()
    {
        $session = $this->create(__FILE__);
        $workspace = $session->workspace();
        $this->assertInstanceOf(Workspace::class, $workspace);
        $this->assertSame($workspace, $session->workspace(), 'Same instanceo returned each time');
    }

    public function create(string $rootUri, int $processId = null)
    {
        return new Session($rootUri, $processId);
    }
}
