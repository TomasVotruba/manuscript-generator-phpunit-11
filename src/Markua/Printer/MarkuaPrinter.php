<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Printer;

use LogicException;
use ManuscriptGenerator\Markua\Parser\Node;
use ManuscriptGenerator\Markua\Parser\Node\Aside;
use ManuscriptGenerator\Markua\Parser\Node\Attribute;
use ManuscriptGenerator\Markua\Parser\Node\AttributeList;
use ManuscriptGenerator\Markua\Parser\Node\Blurb;
use ManuscriptGenerator\Markua\Parser\Node\Directive;
use ManuscriptGenerator\Markua\Parser\Node\Document;
use ManuscriptGenerator\Markua\Parser\Node\Heading;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\Markua\Parser\Node\InlineResource;
use ManuscriptGenerator\Markua\Parser\Node\Link;
use ManuscriptGenerator\Markua\Parser\Node\Paragraph;
use ManuscriptGenerator\Markua\Parser\Node\Span;

final class MarkuaPrinter
{
    public function printDocument(Document $document): string
    {
        $result = new Result();

        $this->printNode($document, $result);

        return rtrim($result->asString()) . "\n";
    }

    /**
     * @param array<Node> $nodes
     */
    public function printNodes(array $nodes): string
    {
        $result = new Result();

        foreach ($nodes as $node) {
            $this->printNode($node, $result);
        }

        return $result->asString();
    }

    private function printNode(Node $node, Result $result): void
    {
        if ($node instanceof Document) {
            foreach ($node->nodes as $subnode) {
                $this->printNode($subnode, $result);
            }
        } elseif ($node instanceof Heading) {
            $result->startBlock();
            $this->printAttributeList($node->attributes, $result, true);
            $result->appendToCurrentBlock(str_repeat('#', $node->level) . ' ' . $node->title);
        } elseif ($node instanceof Directive) {
            $result->addBlock('{' . $node->name . '}');
        } elseif ($node instanceof Aside) {
            $result->startBlock();
            $result->appendLineToBlock('{aside}');
            $result->appendLineToBlock(rtrim($this->printNodes($node->subnodes)));
            $result->appendToCurrentBlock('{/aside}');
        } elseif ($node instanceof Blurb) {
            $result->startBlock();
            $result->appendToCurrentBlock('{blurb');
            if (! $node->attributes->isEmpty()) {
                $result->appendToCurrentBlock(', ');
            }
            $this->printAttributes($node->attributes, $result);
            $result->appendLineToBlock('}');
            $result->appendLineToBlock(rtrim($this->printNodes($node->subnodes)));
            $result->appendToCurrentBlock('{/blurb}');
        } elseif ($node instanceof Paragraph) {
            $result->startBlock();
            foreach ($node->parts as $part) {
                $this->printNode($part, $result);
            }
        } elseif ($node instanceof Span) {
            $result->appendToCurrentBlock($node->text);
        } elseif ($node instanceof Link) {
            $result->appendToCurrentBlock('[' . $node->linkText . '](' . $node->target . ')');
            $this->printAttributeList($node->attributes, $result, false);
        } elseif ($node instanceof IncludedResource) {
            $result->startBlock();
            $this->printAttributeList($node->attributes, $result, true);
            $result->appendToCurrentBlock('![' . $node->caption . '](' . $node->link . ')');
        } elseif ($node instanceof InlineResource) {
            $result->startBlock();
            $this->printAttributeList($node->attributes, $result, true);
            $contents = $node->contents;
            if (! str_ends_with($contents, "\n")) {
                $contents .= "\n";
            }
            $result->appendToCurrentBlock('```' . $node->format . "\n" . $contents . '```');
        } else {
            throw new LogicException('Unknown node type: ' . $node::class);
        }
    }

    private function printAttributeValue(string $value): string
    {
        if (preg_match('/^[\w\-]+$/', $value) === 1) {
            // no need to quote the value if it contains no spaces or special characters
            return $value;
        }

        return '"' . addslashes($value) . '"';
    }

    private function printAttributeList(AttributeList $node, Result $result, bool $addNewline): void
    {
        /*
         * The addNewline argument really represents whether whether the parent node is a block-level element.
         * To be refactored later...
         */
        if (count($node->attributes) === 0) {
            return;
        }
        $result->appendToCurrentBlock('{');
        $this->printAttributes($node, $result);
        $result->appendToCurrentBlock('}');

        if ($addNewline) {
            $result->newLine();
        }
    }

    private function printAttributes(AttributeList $attributes, Result $result): void
    {
        $result->appendToCurrentBlock(
            implode(
                ', ',
                array_map(
                    fn (Attribute $attribute) => $attribute->key . ': ' . $this->printAttributeValue(
                        $attribute->value
                    ),
                    $attributes->attributes
                )
            )
        );
    }
}
