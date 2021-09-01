<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Processor;

use Assert\Assertion;
use ManuscriptGenerator\ManuscriptFiles;
use ManuscriptGenerator\Markua\Parser\Node;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\Markua\Parser\Visitor\AbstractNodeVisitor;
use ManuscriptGenerator\Markua\Processor\Meta\MetaAttributes;
use ManuscriptGenerator\ResourceLoader\ResourceLoader;

final class CopyIncludedResourceNodeVisitor extends AbstractNodeVisitor
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

        $loadedResource = $this->resourceLoader->load($node);
        $manuscriptFiles = $node->getAttribute(MetaAttributes::MANUSCRIPT_FILES);
        Assertion::isInstanceOf($manuscriptFiles, ManuscriptFiles::class);

        $manuscriptFiles->addFile('resources/' . $node->link, $loadedResource->contents());

        return null;
    }
}
