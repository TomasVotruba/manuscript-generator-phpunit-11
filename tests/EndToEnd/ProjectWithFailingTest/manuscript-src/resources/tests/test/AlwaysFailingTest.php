<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Test\EndToEnd\ProjectWithFailingTest\resources\tests\test;

use PHPUnit\Framework\TestCase;

/**
 * @group shouldFail
 */
final class AlwaysFailingTest extends TestCase
{
    public function testAlwaysFails(): void
    {
        self::assertTrue(false);
    }
}
