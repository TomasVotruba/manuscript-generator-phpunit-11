<?php

declare(strict_types=1);

namespace BookTools\Markua\Processor;

use BookTools\Markua\Parser\Node;
use BookTools\Markua\Parser\Node\IncludedResource;
use BookTools\Markua\Parser\Node\InlineResource;
use BookTools\Markua\Parser\Visitor\NodeVisitor;
use BookTools\ResourceLoader\ResourceLoader;
use Symplify\SmartFileSystem\SmartFileInfo;

final class InlineIncludedResourcesNodeVisitor implements NodeVisitor
{
    public function __construct(
        private ResourceLoader $resourceLoader
    ) {
    }

    public function enterNode(Node $node): ?Node
    {
        if (! $node instanceof IncludedResource) {
            return null;
        }

        $includedFromFile = $node->getAttribute('file');
        assert($includedFromFile instanceof SmartFileInfo);

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
