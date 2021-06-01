<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Parser\Visitor;

use ManuscriptGenerator\Markua\Parser\Node;
use ManuscriptGenerator\Markua\Parser\Node\Document;

final class NodeTraverser
{
    /**
     * @param array<NodeVisitor> $nodeVisitors
     */
    public function __construct(
        private array $nodeVisitors
    ) {
    }

    public function traverseDocument(Document $document): Document
    {
        foreach ($this->nodeVisitors as $nodeVisitor) {
            $nodeVisitor->beforeTraversing($document);
        }

        $document = $this->traverseNode($document);

        assert($document instanceof Document);

        foreach ($this->nodeVisitors as $nodeVisitor) {
            $nodeVisitor->afterTraversing($document);
        }

        return $document;
    }

    /**
     * @param array<Node> $nodes
     * @return array<Node>
     */
    private function traverseArray(array $nodes): array
    {
        foreach ($nodes as $index => $node) {
            $node = $this->enterNode($node);
            $node = $this->traverseNode($node);

            $nodes[$index] = $node;
        }

        return $nodes;
    }

    private function traverseNode(Node $node): Node
    {
        foreach ($node->subnodeNames() as $name) {
            $subNode = &$node->{$name};

            if (is_array($subNode)) {
                $subNode = $this->traverseArray($subNode);
            } elseif ($subNode instanceof Node) {
                $subNode = $this->enterNode($subNode);

                $subNode = $this->traverseNode($subNode);
            }
        }

        return $node;
    }

    private function enterNode(Node $node): Node
    {
        foreach ($this->nodeVisitors as $nodeVisitor) {
            $return = $nodeVisitor->enterNode($node);
            if ($return instanceof Node) {
                $node = $return;
            }
        }

        return $node;
    }
}
