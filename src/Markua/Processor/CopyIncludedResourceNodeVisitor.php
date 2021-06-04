<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Processor;

use ManuscriptGenerator\Configuration\RuntimeConfiguration;
use ManuscriptGenerator\FileOperations\FileOperations;
use ManuscriptGenerator\Markua\Parser\Node;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\Markua\Parser\Visitor\AbstractNodeVisitor;
use ManuscriptGenerator\ResourceLoader\ResourceLoader;

final class CopyIncludedResourceNodeVisitor extends AbstractNodeVisitor
{
    public function __construct(
        private RuntimeConfiguration $configuration,
        private ResourceLoader $resourceLoader,
        private FileOperations $fileOperations
    ) {
    }

    public function enterNode(Node $node): ?Node
    {
        if (! $node instanceof IncludedResource) {
            return null;
        }

        $loadedResource = $this->resourceLoader->load($node);
        $this->fileOperations->putContents(
            $this->configuration->manuscriptTargetDir() . '/resources/' . $node->link,
            $loadedResource->contents()
        );

        return null;
    }
}
