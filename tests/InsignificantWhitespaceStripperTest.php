<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Test;

use Generator;
use Iterator;
use ManuscriptGenerator\ResourceProcessor\InsignificantWhitespaceStripper;
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
     * @return Generator<string,array{string,string}>
     */
    public function stringsProvider(): Iterator
    {
        yield 'remove spaces before the newline' => ["test    \n", "test\n"];
        yield 'remove one space before the newline' => ["test \n", "test\n"];
        yield 'keep the newline' => ["test\n", "test\n"];
        yield 'keep only one newline at the end' => ["test\n\n", "test\n"];
        yield 'add a newline at the end' => ['test', "test\n"];
        yield 'remove spaces from an otherwise empty line' => ["test\n \ntest\n", "test\n\ntest\n"];
        yield 'remove newlines from the start' => ["\n\ntest\n", "test\n"];
        yield 'remove empty newlines from the start' => ["\n \n \ntest\n", "test\n"];
        yield 'remove one newline from the start' => ["\ntest\n", "test\n"];
    }
}
