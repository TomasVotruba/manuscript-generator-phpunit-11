<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Processor;

use ManuscriptGenerator\Markua\Parser\Node;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\Markua\Parser\Visitor\AbstractNodeVisitor;

final class UseFilenameAsCaptionNodeVisitor extends AbstractNodeVisitor
{
    public const SHOW_FILENAME_ATTRIBUTE = 'showFilename';

    public function enterNode(Node $node): ?Node
    {
        if (! $node instanceof IncludedResource) {
            return null;
        }

        if (! $node->attributes->has(self::SHOW_FILENAME_ATTRIBUTE)) {
            return null;
        }

        $node->attributes->set('caption', '`' . $this->determineCaption($node) . '`');
        $node->attributes->remove(self::SHOW_FILENAME_ATTRIBUTE);

        return $node;
    }

    private function determineCaption(IncludedResource $node): string
    {
        $fullPath = $node->expectedFile()
            ->pathname();

        $path = $fullPath;
        while (! in_array(($path = dirname($path)), ['.', '/'], true)) {
            if (is_file($path . '/composer.json')) {
                return str_replace($path . '/', '', $fullPath);
            }
        }

        return $node->link;
    }
}
