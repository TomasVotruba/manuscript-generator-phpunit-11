<?php

declare(strict_types=1);

namespace BookTools\Test\Markua;

use BookTools\Markua\Parser\Attribute;
use BookTools\Markua\Parser\Attributes;
use BookTools\Markua\Parser\Document;
use BookTools\Markua\Parser\Heading;
use BookTools\Markua\Parser\Resource_;
use BookTools\Markua\Parser\SimpleMarkuaParser;
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
            new Document([new Resource_('source.php', 'Label')]),
            $this->parser->parseDocument(<<<CODE_SAMPLE
![Label](source.php)
CODE_SAMPLE
            )
        );
    }

    public function testIncludedResourceWithoutLabel(): void
    {
        self::assertEquals(
            new Document([new Resource_('source.php', '')]),
            $this->parser->parseDocument(<<<CODE_SAMPLE
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

    public function testIncludedResourceWithAttributes(): void
    {
        self::assertEquals(
            new Document(
                [new Resource_('source.php', 'Caption', new Attributes([new Attribute('crop-start', '6')]))]
            ),
            $this->parser->parseDocument(<<<CODE_SAMPLE
{crop-start: 6}
![Caption](source.php)
CODE_SAMPLE
            )
        );
    }

    public function testHeading1(): void
    {
        self::assertEquals(
            new Document([new Heading(1, 'Title')]),
            $this->parser->parseDocument(<<<CODE_SAMPLE
# Title
CODE_SAMPLE
            )
        );
    }

    public function testHeadingWithSpace(): void
    {
        self::assertEquals(
            new Document([new Heading(1, 'The title')]),
            $this->parser->parseDocument(<<<CODE_SAMPLE
# The title
CODE_SAMPLE
            )
        );
    }

    public function testHeading2(): void
    {
        self::assertEquals(
            new Document([new Heading(2, 'Title')]),
            $this->parser->parseDocument(<<<CODE_SAMPLE
## Title
CODE_SAMPLE
            )
        );
    }

    public function testHeadingWithAttributes(): void
    {
        self::assertEquals(
            new Document([new Heading(2, 'Title', new Attributes([new Attribute('id', 'title')]))]),
            $this->parser->parseDocument(<<<CODE_SAMPLE
{id:title}
## Title
CODE_SAMPLE
            )
        );
    }

    public function testDocument(): void
    {
        self::assertEquals(
            new Document([new Heading(1, 'Title'), new Resource_('source.php', 'Caption')]),
            $this->parser->parseDocument(<<<CODE_SAMPLE
# Title

![Caption](source.php)
CODE_SAMPLE
            )
        );
    }
}
