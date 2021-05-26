<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TrueTest extends TestCase
{
    public function test(): void
    {
        self::assertTrue(true);
    }
}
