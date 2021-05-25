<?php

declare(strict_types=1);

namespace BookTools\Test\Markua\Printer;

use BookTools\Markua\Parser\SimpleMarkuaParser;
use BookTools\Markua\Printer\MarkuaPrinter;
use Generator;
use PHPUnit\Framework\TestCase;

final class MarkuaPrinterTest extends TestCase
{
    /**
     * @dataProvider markuaProvider
     */
    public function testParseThenPrintBack(string $correctlyFormattedMarkua): void
    {
        $parser = new SimpleMarkuaParser();
        $document = $parser->parseDocument($correctlyFormattedMarkua);

        $printer = new MarkuaPrinter();
        $printedBack = $printer->printDocument($document);

        self::assertEquals($correctlyFormattedMarkua, $printedBack);
    }

    /**
     * @return Generator<array<string>>
     */
    public function markuaProvider(): Generator
    {
        yield [<<<CODE_SAMPLE
# Heading 1

Paragraph 1

# Heading 2

Paragraph 2
Second line of paragraph 2

{crop-start: 6}
![Caption](source.php)

CODE_SAMPLE
        ];
    }
}
