<?php

declare(strict_types=1);

namespace BookTools\Test\Markua;

use BookTools\Markua\Parser\Node\Attribute;
use BookTools\Markua\Parser\Node\AttributeList;
use BookTools\Markua\Parser\Node\Directive;
use BookTools\Markua\Parser\Node\Document;
use BookTools\Markua\Parser\Node\Heading;
use BookTools\Markua\Parser\Node\IncludedResource;
use BookTools\Markua\Parser\Node\InlineResource;
use BookTools\Markua\Parser\Node\Paragraph;
use BookTools\Markua\Parser\SimpleMarkuaParser;
use Parsica\Parsica\ParserHasFailed;
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
            new Document([new IncludedResource('source.php', 'Label')]),
            $this->parser->parseDocument(<<<CODE_SAMPLE
![Label](source.php)
CODE_SAMPLE
            )
        );
    }

    public function testInlineResourceWithExtraBacktickFails(): void
    {
        $this->expectException(ParserHasFailed::class);

        $this->parser->parseDocument(<<<'CODE_SAMPLE'
````php
$code
```
CODE_SAMPLE
        );
    }

    public function testInlineResourceWithNoFormat(): void
    {
        self::assertEquals(
            new Document([new InlineResource("\$code\n", null)]),
            $this->parser->parseDocument(<<<'CODE_SAMPLE'
```
$code
```
CODE_SAMPLE
            )
        );
    }

    public function testInlineResourceWithAttributes(): void
    {
        self::assertEquals(
            new Document([new InlineResource("\$code\n", 'php', new AttributeList(
                [new Attribute('caption', 'Caption')]
            ))]),
            $this->parser->parseDocument(<<<'CODE_SAMPLE'
{caption: "Caption"}
```php
$code
```
CODE_SAMPLE
            )
        );
    }

    public function testIncludedResourceWithoutLabel(): void
    {
        self::assertEquals(
            new Document([new IncludedResource('source.php', '')]),
            $this->parser->parseDocument(<<<CODE_SAMPLE
![](source.php)
CODE_SAMPLE
            )
        );
    }

    public function testAttributes(): void
    {
        self::assertEquals(
            new Document([
                new IncludedResource('source.php', '', new AttributeList([new Attribute('crop-start', '6')])), ]),
            $this->parser->parseDocument(<<<CODE_SAMPLE
{crop-start: 6}
![](source.php)
CODE_SAMPLE
            )
        );
    }

    public function testAttributesOptionalWhitespace(): void
    {
        self::assertEquals(
            new Document([new IncludedResource(
                'source.php',
                '',
                new AttributeList([new Attribute('crop-start', '6'), new Attribute('crop-end', '7')])
            )]),
            $this->parser->parseDocument(<<<CODE_SAMPLE
{crop-start: 6,crop-end: 7}
![](source.php)
CODE_SAMPLE
            )
        );
    }

    public function testAttributesWithAndWithoutQuotes(): void
    {
        self::assertEquals(
            new Document([new IncludedResource(
                'source.php',
                '',
                new AttributeList([new Attribute('caption', 'Caption'), new Attribute('crop-start', '6')])
            )]),
            $this->parser->parseDocument(
                <<<CODE_SAMPLE
{caption: "Caption", crop-start: 6}
![](source.php)
CODE_SAMPLE
            )
        );
    }

    public function testIncludedResourceWithAttributes(): void
    {
        self::assertEquals(
            new Document(
                [new IncludedResource('source.php', 'Caption', new AttributeList([new Attribute('crop-start', '6')]))]
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
            new Document([new Heading(2, 'Title', new AttributeList([new Attribute('id', 'title')]))]),
            $this->parser->parseDocument(<<<CODE_SAMPLE
{id:title}
## Title
CODE_SAMPLE
            )
        );
    }

    public function testIdAttributeShortcut(): void
    {
        self::assertEquals(
            new Document([new Heading(1, 'Chapter 1', new AttributeList([new Attribute('id', 'chapter-1')]))]),
            $this->parser->parseDocument(<<<CODE_SAMPLE
{#chapter-1}
# Chapter 1
CODE_SAMPLE
            )
        );
    }

    public function testDocument(): void
    {
        self::assertEquals(
            new Document([new Heading(1, 'Title'), new IncludedResource('source.php', 'Caption')]),
            $this->parser->parseDocument(<<<CODE_SAMPLE
# Title

![Caption](source.php)
CODE_SAMPLE
            )
        );
    }

    public function testParagraph(): void
    {
        self::assertEquals(
            new Document([new Heading(1, 'Title'), new Paragraph('Paragraph 1')]),
            $this->parser->parseDocument(<<<CODE_SAMPLE
# Title

Paragraph 1
CODE_SAMPLE
            )
        );
    }

    public function testMultipleParagraphs(): void
    {
        self::assertEquals(
            new Document([new Heading(1, 'Title'), new Paragraph('Paragraph 1'), new Paragraph('Paragraph 2')]),
            $this->parser->parseDocument(<<<CODE_SAMPLE
# Title

Paragraph 1

Paragraph 2
CODE_SAMPLE
            )
        );
    }

    public function testMultilineParagraphs(): void
    {
        self::assertEquals(
            new Document([new Heading(1, 'Title'), new Paragraph("Paragraph 1\nLine 2 of the same paragraph")]),
            $this->parser->parseDocument(
                <<<CODE_SAMPLE
# Title

Paragraph 1
Line 2 of the same paragraph
CODE_SAMPLE
            )
        );
    }

    public function testDirective(): void
    {
        self::assertEquals(
            new Document([new Directive('frontmatter'), new Directive('mainmatter'), new Directive('backmatter')]),
            $this->parser->parseDocument(<<<CODE_SAMPLE
{frontmatter}
{mainmatter}
{backmatter}
CODE_SAMPLE
            )
        );
    }
}
