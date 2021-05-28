<?php

declare(strict_types=1);

namespace BookTools\Markua\Processor\LinkRegistry;

use BookTools\Configuration\LinkRegistryConfiguration;
use BookTools\Configuration\RuntimeConfiguration;
use BookTools\FileOperations\FileOperations;
use BookTools\Markua\Parser\Node;
use BookTools\Markua\Parser\Node\Document;
use BookTools\Markua\Parser\Node\Link;
use BookTools\Markua\Parser\Visitor\AbstractNodeVisitor;

final class GenerateLinksForLinkRegistryNodeVisitor extends AbstractNodeVisitor
{
    /**
     * @var array<string,string>
     */
    private array $links = [];

    public function __construct(
        private FileOperations $fileOperations,
        private LinkRegistryConfiguration $linkRegistryConfiguration,
        private RuntimeConfiguration $configuration
    ) {
    }

    public function beforeTraversing(Document $document): void
    {
        $this->links = [];
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

        if (! str_starts_with($slug, '/')) {
            $slug = '/' . $slug;
        }

        $this->links[$slug] = $node->target;

        $node->target = $this->linkRegistryConfiguration->linkRegistryBaseUrl() . $slug;

        $node->attributes->remove('slug');

        return $node;
    }

    public function afterTraversing(Document $document): void
    {
        $lines = [];
        foreach ($this->links as $slug => $url) {
            $lines[] = $slug . ' ' . $url;
        }

        $this->fileOperations->putContents(
            $this->configuration->manuscriptTargetDir() . '/' . $this->linkRegistryConfiguration->linksFile(),
            implode("\n", $lines) . "\n"
        );
    }
}
