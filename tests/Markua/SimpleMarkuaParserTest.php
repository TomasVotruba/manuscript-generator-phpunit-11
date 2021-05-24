<?php

declare(strict_types=1);

namespace BookTools\Test\Markua;

use BookTools\Markua\Attribute;
use BookTools\Markua\Attributes;
use BookTools\Markua\Resource_;
use BookTools\Markua\SimpleMarkuaParser;
use PHPUnit\Framework\TestCase;

final class SimpleMarkuaParserTest extends TestCase
{
    private SimpleMarkuaParser $parser;

    protected function setUp(): void
    {
        $this->parser = new SimpleMarkuaParser();
    }

    public function testIncludedResource(): void
    {
        self::assertEquals(
            new Resource_('source.php', 'Label'),
            $this->parser->parseIncludedResource(<<<CODE_SAMPLE
![Label](source.php)
CODE_SAMPLE
            )
        );
    }

    public function testIncludedResourceWithoutLabel(): void
    {
        self::assertEquals(
            new Resource_('source.php', ''),
            $this->parser->parseIncludedResource(<<<CODE_SAMPLE
![](source.php)
CODE_SAMPLE
            )
        );
    }

    public function testAttributes(): void
    {
        self::assertEquals(
            new Attributes([new Attribute('crop-start', '6')]),
            $this->parser->parseAttributes(<<<CODE_SAMPLE
{crop-start: 6}
CODE_SAMPLE
            )
        );
    }

    public function testAttributesOptionalWhitespace(): void
    {
        self::assertEquals(
            new Attributes([new Attribute('crop-start', '6'), new Attribute('crop-end', '7')]),
            $this->parser->parseAttributes(<<<CODE_SAMPLE
{crop-start: 6,crop-end: 7}
CODE_SAMPLE
            )
        );
    }

    public function testAttributesWithAndWithoutQuotes(): void
    {
        self::assertEquals(
            new Attributes([new Attribute('caption', 'Caption'), new Attribute('crop-start', '6')]),
            $this->parser->parseAttributes(<<<CODE_SAMPLE
{caption: "Caption", crop-start: 6}
CODE_SAMPLE
            )
        );
    }

    public function testEmptyAttributes(): void
    {
        self::assertEquals(new Attributes([]), $this->parser->parseAttributes(<<<CODE_SAMPLE
{}
CODE_SAMPLE
            ));
    }
}
