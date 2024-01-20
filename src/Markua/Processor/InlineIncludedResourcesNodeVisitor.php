<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Processor;

use ManuscriptGenerator\Markua\Parser\Node;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\Markua\Parser\Node\InlineResource;
use ManuscriptGenerator\Markua\Parser\Visitor\AbstractNodeVisitor;
use ManuscriptGenerator\ResourceLoader\ResourceLoader;

final class InlineIncludedResourcesNodeVisitor extends AbstractNodeVisitor
{
    public function __construct(
        private readonly ResourceLoader $resourceLoader
    ) {
    }

    public function enterNode(Node $node): ?Node
    {
        if (! $node instanceof IncludedResource) {
            return null;
        }

        $resource = $this->resourceLoader->load($node);

        if (in_array($resource->format(), ['gif', 'jpeg', 'jpg', 'png', 'svg'], true)) {
            // Don't try to inline images
            return null;
        }

        if ($node->caption !== null) {
            $node->attributes->set('caption', $node->caption);
        }
        if (! $node->attributes->has('format')) {
            $node->attributes->set('format', $resource->format());
        }

        return new InlineResource(
            $resource->contents(), // the contents will be further process later
            null, // the format has been moved to the attributes
            $node->attributes
        );
    }
}
