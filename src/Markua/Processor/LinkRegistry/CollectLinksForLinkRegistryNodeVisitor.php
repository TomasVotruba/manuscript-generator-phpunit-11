<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Processor\LinkRegistry;

use Assert\Assertion;
use ManuscriptGenerator\Configuration\LinkRegistryConfiguration;
use ManuscriptGenerator\Configuration\RuntimeConfiguration;
use ManuscriptGenerator\FileOperations\FileOperations;
use ManuscriptGenerator\ManuscriptFiles;
use ManuscriptGenerator\Markua\Parser\Node;
use ManuscriptGenerator\Markua\Parser\Node\Document;
use ManuscriptGenerator\Markua\Parser\Node\Link;
use ManuscriptGenerator\Markua\Parser\Visitor\AbstractNodeVisitor;
use ManuscriptGenerator\Markua\Processor\Meta\MetaAttributes;

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
        if (is_file($this->linksFilePathnameInSrc())) {
            $this->linkCollector = ExternalLinkCollector::loadFromString(
                (string) file_get_contents($this->linksFilePathnameInSrc())
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
        /** @var ManuscriptFiles $manuscriptFiles */
        $manuscriptFiles = $document->getAttribute(MetaAttributes::MANUSCRIPT_FILES);
        Assertion::isInstanceOf($manuscriptFiles, ManuscriptFiles::class);

        $linksFileContents = $this->linkCollector->asString();

        // Save a copy in manuscript-src, so we can load it the next time
        $this->fileOperations->putContents($this->linksFilePathnameInSrc(), $linksFileContents);

        // Copy the file to manuscript, because it's a file that needs to be published in some way
        $manuscriptFiles->addFile($this->linkRegistryConfiguration->linksFile(), $linksFileContents);
    }

    private function linksFilePathnameInSrc(): string
    {
        return $this->configuration->manuscriptSrcDir() . '/' . $this->linkRegistryConfiguration->linksFile();
    }
}
