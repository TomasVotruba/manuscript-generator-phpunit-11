<?php

declare(strict_types=1);

namespace BookTools\Test\Markua;

use BookTools\Markua\Parser\Attribute;
use BookTools\Markua\Parser\Attributes;
use BookTools\Markua\Parser\Document;
use BookTools\Markua\Parser\Heading;
use BookTools\Markua\Parser\Paragraph;
use BookTools\Markua\Parser\Visitor\NodeTraverser;
use PHPUnit\Framework\TestCase;

final class NodeTraverserTest extends TestCase
{
    public function testTraverseDepthFirst(): void
    {
        $document = new Document(
            [
                new Heading(1, 'Chapter 1', new Attributes([new Attribute('id', 'chapter-1')])),
                new Paragraph('Paragraph'),
            ]
        );

        $spy = new NodeVisitorSpy();
        $traverser = new NodeTraverser([$spy]);

        $traverser->traverse($document);

        self::assertEquals(
            [
                'enterNode: Document',
                'enterNode: Heading',
                'enterNode: Attributes',
                'enterNode: Attribute',
                'enterNode: Paragraph',
            ],
            $spy->calledMethods()
        );
    }
}
