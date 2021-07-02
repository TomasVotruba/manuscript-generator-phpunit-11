<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Test\Markua\Printer;

use Generator;
use Iterator;
use ManuscriptGenerator\Markua\Parser\SimpleMarkuaParser;
use ManuscriptGenerator\Markua\Printer\MarkuaPrinter;
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
    public function markuaProvider(): Iterator
    {
        yield [<<<CODE_SAMPLE
{frontmatter}

# Heading 1

Paragraph 1

{mainmatter}

# Heading 2

Paragraph 2
Second line of paragraph 2

{crop-start: 6, caption: "Caption with spaces"}
![Included source with attributes](source1.php)

![Included source without attributes](source2.php)

{caption: Caption, format: php}
```
// inline source
```

```php
// inline source without attributes
```

See also: [Blog](https://matthiasnoback.nl){slug: blog}

{aside}
This chapter covers:
{/aside}

{blurb, class: tip}
Test
{/blurb}

{blockquote}
A quote
{/blockquote}

CODE_SAMPLE
        ];
    }
}
