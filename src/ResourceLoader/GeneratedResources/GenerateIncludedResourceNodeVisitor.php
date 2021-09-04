<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\Markua\Parser\Node;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\Markua\Parser\Visitor\AbstractNodeVisitor;

final class GenerateIncludedResourceNodeVisitor extends AbstractNodeVisitor
{
    public function __construct(
        private ResourceGenerator $resourceGenerator
    ) {
    }

    public function enterNode(Node $node): ?Node
    {
        if (! $node instanceof IncludedResource) {
            return null;
        }

        if (! is_string($node->attributes->get('generator'))) {
            // This included resources is not supposed to be generated
            return null;
        }

        $this->resourceGenerator->generateResource($node);

        $node->attributes->remove('generator');

        return null;
    }
}
