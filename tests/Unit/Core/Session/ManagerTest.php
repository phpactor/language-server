<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Session;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Session\Manager;
use RuntimeException;

class ManagerTest extends TestCase
{
    /**
     * @var Manager
     */
    private $manager;

    public function setUp()
    {
        $this->manager = new Manager();
    }

    public function testThrowsExceptionWhenNotInitialized()
    {
        $this->expectException(RuntimeException::class);
        $this->manager->current();
    }

    public function testInitializesSession()
    {
        $this->manager->initialize(__FILE__, 1234);
        $session = $this->manager->current();

        $this->assertEquals(__FILE__, $session->rootUri());
        $this->assertEquals(1234, $session->processId());

    }
}
