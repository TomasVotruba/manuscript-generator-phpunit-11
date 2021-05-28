<?php

declare(strict_types=1);

namespace BookTools\Test\Markua;

use BookTools\Markua\Parser\Node;
use BookTools\Markua\Parser\Node\Attribute;
use BookTools\Markua\Parser\Node\AttributeList;
use BookTools\Markua\Parser\Node\Document;
use BookTools\Markua\Parser\Node\Heading;
use BookTools\Markua\Parser\Node\Paragraph;
use BookTools\Markua\Parser\Node\Span;
use BookTools\Markua\Parser\Visitor\AbstractNodeVisitor;
use BookTools\Markua\Parser\Visitor\NodeTraverser;
use PHPUnit\Framework\TestCase;

final class NodeTraverserTest extends TestCase
{
    public function testTraverseDepthFirst(): void
    {
        $document = new Document(
            [
                new Heading(1, 'Chapter 1', new AttributeList([new Attribute('id', 'chapter-1')])),
                new Paragraph([new Span('Paragraph')]),
            ]
        );

        $spy = new NodeVisitorSpy();
        $traverser = new NodeTraverser([$spy]);

        $traverser->traverseDocument($document);

        self::assertEquals(
            [
                'beforeTraversing',
                'enterNode: Heading',
                'enterNode: AttributeList',
                'enterNode: Attribute',
                'enterNode: Paragraph',
                'enterNode: Span',
                'afterTraversing',
            ],
            $spy->calledMethods()
        );
    }

    public function testModifyExistingNodes(): void
    {
        $traverser = new NodeTraverser(
            [new class() extends AbstractNodeVisitor {
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
            [new class() extends AbstractNodeVisitor {
                public function enterNode(Node $node): Node
                {
                    if ($node instanceof Heading) {
                        return new Paragraph([new Span($node->title)]);
                    }

                    return $node;
                }
            }]
        );

        $result = $traverser->traverseDocument(new Document([new Heading(1, 'Chapter 1')]));

        self::assertEquals(new Document([new Paragraph([new Span('Chapter 1')])]), $result);
    }

    public function testReplaceExistingSubnodes(): void
    {
        $traverser = new NodeTraverser(
            [new class() extends AbstractNodeVisitor {
                public function enterNode(Node $node): ?Node
                {
                    if (! $node instanceof Attribute) {
                        return null;
                    }
                    if ($node->value !== 'foo') {
                        return null;
                    }
                    return new Attribute('id', 'bar');
                }
            }]
        );

        $result = $traverser->traverseDocument(
            new Document([new Heading(1, 'Chapter 1', new AttributeList([new Attribute('id', 'foo')]))])
        );

        self::assertEquals(
            new Document([new Heading(1, 'Chapter 1', new AttributeList([new Attribute('id', 'bar')]))]),
            $result
        );
    }
}
