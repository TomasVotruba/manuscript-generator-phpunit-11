<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\Markua\Parser\Node;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\Markua\Parser\Visitor\AbstractNodeVisitor;

final class GenerateIncludedResourceNodeVisitor extends AbstractNodeVisitor
{
    public function __construct(
        private IncludedResourceGenerator $includedResourceGenerator
    ) {
    }

    public function enterNode(Node $node): ?Node
    {
        if (! $node instanceof IncludedResource) {
            return null;
        }

        if ($node->attributes->getStringOrNull('generator') === null) {
            // This included resources is not supposed to be generated
            return null;
        }

        $this->includedResourceGenerator->generateResource($node);

        $node->attributes->remove('generator');
        $node->attributes->remove('source');

        return null;
    }
}
