<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Processor\LinkRegistry;

use Assert\Assertion;
use ManuscriptGenerator\Configuration\BookProjectConfiguration;
use ManuscriptGenerator\FileOperations\File;
use ManuscriptGenerator\ManuscriptFiles\ManuscriptFiles;
use ManuscriptGenerator\Markua\Parser\Node;
use ManuscriptGenerator\Markua\Parser\Node\Document;
use ManuscriptGenerator\Markua\Parser\Node\Link;
use ManuscriptGenerator\Markua\Parser\Visitor\AbstractNodeVisitor;
use ManuscriptGenerator\Markua\Processor\Meta\MetaAttributes;

final class CollectLinksForLinkRegistryNodeVisitor extends AbstractNodeVisitor
{
    private ExternalLinkCollector $linkCollector;

    public function __construct(
        private BookProjectConfiguration $bookProjectConfiguration
    ) {
    }

    public function beforeTraversing(Document $document): void
    {
        if ($this->linksFilePathnameInSrc()->exists()) {
            $this->linkCollector = ExternalLinkCollector::loadFromString(
                $this->linksFilePathnameInSrc()
                    ->getContents()
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

        $slug = $node->attributes->getStringOrNull('slug');
        if ($slug === null) {
            throw CouldNotProcessExternalLink::becauseItHasNoSlugAttribute($node);
        }

        $this->linkCollector->add($slug, $node->target);

        $node->target = $this->bookProjectConfiguration->linkRegistryConfiguration()
            ->linkRegistryBaseUrl() . $slug;

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
        $this->linksFilePathnameInSrc()
            ->putContents($linksFileContents);

        // Copy the file to manuscript, because it's a file that needs to be published in some way
        $manuscriptFiles->addFile(
            $this->bookProjectConfiguration->linkRegistryConfiguration()
                ->linksFile(),
            $linksFileContents
        );
    }

    private function linksFilePathnameInSrc(): File
    {
        return $this->bookProjectConfiguration->manuscriptSrcDir()
            ->appendPath($this->bookProjectConfiguration->linkRegistryConfiguration()->linksFile())
            ->file();
    }
}
