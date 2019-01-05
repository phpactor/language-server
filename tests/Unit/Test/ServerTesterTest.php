<?php

namespace Phpactor\LanguageServer\Tests\Unit\Test;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\LanguageServerBuilder;
use Phpactor\LanguageServer\Test\ServerTester;

class ServerTesterTest extends TestCase
{
    public function testServerTester()
    {
        $builder = LanguageServerBuilder::create();
        $tester = new ServerTester($builder);
        $tester->initialize();
    }
}
