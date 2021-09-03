<?php

declare(strict_types=1);

use Foo\Bar;
use PHPUnit\Framework\TestCase;

/**
 * @group shouldFail
 */
final class BarTest extends TestCase
{
    public function test(): void
    {
        self::assertInstanceOf(Bar::class, new Bar());
    }
}
