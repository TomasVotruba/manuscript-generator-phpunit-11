<?php

declare(strict_types=1);

namespace BookTools\Test\Markua;

use BookTools\Markua\Parser\Node;
use BookTools\Markua\Parser\Node\Attribute;
use BookTools\Markua\Parser\Node\Attributes;
use BookTools\Markua\Parser\Node\Document;
use BookTools\Markua\Parser\Node\Heading;
use BookTools\Markua\Parser\Node\Paragraph;
use BookTools\Markua\Parser\Visitor\NodeTraverser;
use BookTools\Markua\Parser\Visitor\NodeVisitor;
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

        $traverser->traverseDocument($document);

        self::assertEquals(
            ['enterNode: Heading', 'enterNode: Attributes', 'enterNode: Attribute', 'enterNode: Paragraph'],
            $spy->calledMethods()
        );
    }

    public function testModifyExistingNodes(): void
    {
        $traverser = new NodeTraverser(
            [new class() implements NodeVisitor {
                public function enterNode(Node $node): Node
                {
                    if ($node instanceof Heading) {
                        $node->title = strtoupper($node->title);
                    }

                    return $node;
                }
            }]
        );

        $result = $traverser->traverseDocument(new Document([new Heading(1, 'Chapter 1')]));

        self::assertEquals(new Document([new Heading(1, 'CHAPTER 1')]), $result);
    }

    public function testReplaceExistingNodes(): void
    {
        $traverser = new NodeTraverser(
            [new class() implements NodeVisitor {
                public function enterNode(Node $node): Node
                {
                    if ($node instanceof Heading) {
                        return new Paragraph($node->title);
                    }

                    return $node;
                }
            }]
        );

        $result = $traverser->traverseDocument(new Document([new Heading(1, 'Chapter 1')]));

        self::assertEquals(new Document([new Paragraph('Chapter 1')]), $result);
    }

    public function testReplaceExistingSubnodes(): void
    {
        $traverser = new NodeTraverser(
            [new class() implements NodeVisitor {
                public function enterNode(Node $node): Node
                {
                    if ($node instanceof Attribute && $node->value === 'foo') {
                        return new Attribute('id', 'bar');
                    }

                    return $node;
                }
            }]
        );

        $result = $traverser->traverseDocument(
            new Document([new Heading(1, 'Chapter 1', new Attributes([new Attribute('id', 'foo')]))])
        );

        self::assertEquals(
            new Document([new Heading(1, 'Chapter 1', new Attributes([new Attribute('id', 'bar')]))]),
            $result
        );
    }
}
