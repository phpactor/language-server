<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Dispatcher\ArgumentResolver;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\ChainArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\Exception\CouldNotResolveArguments;
use Prophecy\PhpUnit\ProphecyTrait;
use stdClass;

class ChainArgumentResolverTest extends TestCase
{
    use ProphecyTrait;

    public function testExceptionIfNoResolvers(): void
    {
        $this->expectException(CouldNotResolveArguments::class);
        (new ChainArgumentResolver())->resolveArguments(new stdClass(), 'foo', [], []);
    }

    public function testResolvesFirstThatReturns(): void
    {
        $resolver1 = $this->prophesize(ArgumentResolver::class);
        $resolver2 = $this->prophesize(ArgumentResolver::class);
        $resolver1->resolveArguments(new stdClass(), 'foo', [], [])->willThrow(new CouldNotResolveArguments('foo'));
        $resolver1->resolveArguments(new stdClass(), 'foo', [], [])->willReturn(['foo' => 'bar']);
        $this->expectException(CouldNotResolveArguments::class);
        self::assertEquals(['foo' => 'bar'], (new ChainArgumentResolver())->resolveArguments(new stdClass(), 'foo', [], []));
    }
}
