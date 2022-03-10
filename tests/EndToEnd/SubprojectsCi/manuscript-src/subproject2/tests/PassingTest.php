<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PassingTest extends TestCase
{
    public function testFails(): void
    {
        $this->addToAssertionCount(1);
    }
}
