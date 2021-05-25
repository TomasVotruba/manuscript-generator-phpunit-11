<?php

declare(strict_types=1);

namespace BookTools\Markua\Printer;

use BookTools\Markua\Parser\Node;
use BookTools\Markua\Parser\Node\Attribute;
use BookTools\Markua\Parser\Node\Attributes;
use BookTools\Markua\Parser\Node\Document;
use BookTools\Markua\Parser\Node\Heading;
use BookTools\Markua\Parser\Node\IncludedResource;
use BookTools\Markua\Parser\Node\InlineResource;
use BookTools\Markua\Parser\Node\Paragraph;
use LogicException;

final class MarkuaPrinter
{
    public function printDocument(Document $document): string
    {
        $result = new Result();

        $this->printNode($document, $result);

        return $result->asString();
    }

    private function printNode(Node $node, Result $result): void
    {
        if ($node instanceof Document) {
            foreach ($node->nodes as $subnode) {
                $this->printNode($subnode, $result);
            }
        } elseif ($node instanceof Attributes) {
            if (count($node->attributes) === 0) {
                return;
            }
            $result->appendLineToBlock(
                '{' .
                implode(
                    ', ',
                    array_map(
                        fn (Attribute $attribute) => $attribute->key . ': ' . $this->printAttributeValue(
                            $attribute->value
                        ),
                        $node->attributes
                    )
                )
                . '}'
            );
        } elseif ($node instanceof Heading) {
            $result->startBlock();
            $this->printNode($node->attributes, $result);
            $result->appendToBlock(str_repeat('#', $node->level) . ' ' . $node->title);
        } elseif ($node instanceof Paragraph) {
            $result->addBlock($node->text);
        } elseif ($node instanceof IncludedResource) {
            $result->startBlock();
            $this->printNode($node->attributes, $result);
            $result->appendToBlock('![' . $node->caption . '](' . $node->link . ')');
        } elseif ($node instanceof InlineResource) {
            $result->startBlock();
            $this->printNode($node->attributes, $result);
            $result->appendToBlock('```' . $node->format . "\n" . $node->contents . '```');
        } else {
            throw new LogicException('Unknown node type: ' . get_class($node));
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
}
