<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @group shouldFail
 */
final class NotTrueTest extends TestCase
{
    public function test(): void
    {
        self::assertTrue(false);
    }
}
