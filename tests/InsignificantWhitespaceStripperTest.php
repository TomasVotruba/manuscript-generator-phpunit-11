<?php

declare(strict_types=1);

namespace BookTools\Test;

use BookTools\ResourcePreProcessor\InsignificantWhitespaceStripper;
use Generator;
use Iterator;
use PHPUnit\Framework\TestCase;

final class InsignificantWhitespaceStripperTest extends TestCase
{
    private InsignificantWhitespaceStripper $service;

    protected function setUp(): void
    {
        $this->service = new InsignificantWhitespaceStripper();
    }

    /**
     * @dataProvider stringsProvider
     */
    public function test(string $original, string $expected): void
    {
        self::assertEquals($expected, $this->service->strip($original));
    }

    /**
     * @return Generator<array{string,string}>
     */
    public function stringsProvider(): Iterator
    {
        yield ["test    \n", "test\n"];
        yield ["test \n", "test\n"];
        yield ["test\n", "test\n"];
        yield ["test\n\n", "test\n"];
        yield ["test\ntest\n", "test\ntest\n"];
    }
}
