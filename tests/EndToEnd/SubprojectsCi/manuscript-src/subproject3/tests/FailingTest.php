<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class FailingTest extends TestCase
{
    public function testFails(): void
    {
        $this->fail('PHPUnit test failed');
    }
}
