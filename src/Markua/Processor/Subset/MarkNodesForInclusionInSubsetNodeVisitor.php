<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Processor\Subset;

use ManuscriptGenerator\Markua\Parser\Node;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\Markua\Parser\Visitor\AbstractNodeVisitor;

final class MarkNodesForInclusionInSubsetNodeVisitor extends AbstractNodeVisitor
{
    public function enterNode(Node $node): ?Node
    {
        if (! $node instanceof IncludedResource) {
            return null;
        }

        $subsetAttribute = $node->attributes->get('subset');
        if ($subsetAttribute === null) {
            return null;
        }

        if ($subsetAttribute) {
            // Save the subset attribute value of the included resources as an internal node attribute
            $node->setAttribute('subset', true);
        }

        // Remove the "subset" attribute from the included resource
        $node->attributes->remove('subset');

        return $node;
    }
}
