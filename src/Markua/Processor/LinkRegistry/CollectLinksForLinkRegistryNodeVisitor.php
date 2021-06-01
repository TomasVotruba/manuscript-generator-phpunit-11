<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Processor\LinkRegistry;

use ManuscriptGenerator\Configuration\LinkRegistryConfiguration;
use ManuscriptGenerator\Configuration\RuntimeConfiguration;
use ManuscriptGenerator\FileOperations\FileOperations;
use ManuscriptGenerator\Markua\Parser\Node;
use ManuscriptGenerator\Markua\Parser\Node\Document;
use ManuscriptGenerator\Markua\Parser\Node\Link;
use ManuscriptGenerator\Markua\Parser\Visitor\AbstractNodeVisitor;

final class CollectLinksForLinkRegistryNodeVisitor extends AbstractNodeVisitor
{
    private ExternalLinkCollector $linkCollector;

    public function __construct(
        private FileOperations $fileOperations,
        private LinkRegistryConfiguration $linkRegistryConfiguration,
        private RuntimeConfiguration $configuration
    ) {
    }

    public function beforeTraversing(Document $document): void
    {
        if (is_file($this->linkFilePathname())) {
            $this->linkCollector = ExternalLinkCollector::loadFromString(
                (string) file_get_contents($this->linkFilePathname())
            );
        } else {
            $this->linkCollector = new ExternalLinkCollector();
        }
    }

    public function enterNode(Node $node): ?Node
    {
        if (! $node instanceof Link) {
            return null;
        }

        if (! str_contains($node->target, '://')) {
            return null;
        }

        $slug = $node->attributes->get('slug');
        if ($slug === null) {
            throw CouldNotProcessExternalLink::becauseItHasNoSlugAttribute($node);
        }

        $this->linkCollector->add($slug, $node->target);

        $node->target = $this->linkRegistryConfiguration->linkRegistryBaseUrl() . $slug;

        $node->attributes->remove('slug');

        return $node;
    }

    public function afterTraversing(Document $document): void
    {
        $this->fileOperations->putContents($this->linkFilePathname(), $this->linkCollector->asString());
    }

    private function linkFilePathname(): string
    {
        return $this->configuration->manuscriptTargetDir() . '/' . $this->linkRegistryConfiguration->linksFile();
    }
}
