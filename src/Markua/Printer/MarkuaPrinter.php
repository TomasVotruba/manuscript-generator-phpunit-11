<?php

declare(strict_types=1);

namespace BookTools\Markua\Printer;

use BookTools\Markua\Parser\Attribute;
use BookTools\Markua\Parser\Attributes;
use BookTools\Markua\Parser\Document;
use BookTools\Markua\Parser\Heading;
use BookTools\Markua\Parser\IncludedResource;
use BookTools\Markua\Parser\Node;
use BookTools\Markua\Parser\Paragraph;
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
                        fn (Attribute $attribute) => $attribute->key . ': ' . $attribute->value,
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
        } else {
            throw new LogicException('Unknown node type: ' . get_class($node));
        }
    }
}
