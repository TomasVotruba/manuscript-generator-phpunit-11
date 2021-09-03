<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Processor;

use ManuscriptGenerator\FileOperations\ExistingFile;
use ManuscriptGenerator\Markua\Parser\Node;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\Markua\Parser\Visitor\AbstractNodeVisitor;
use ManuscriptGenerator\Markua\Processor\Meta\MetaAttributes;
use ManuscriptGenerator\ResourceLoader\ResourceLoader;

final class InlineIncludedMarkdownFilesNodeVisitor extends AbstractNodeVisitor
{
    public function __construct(
        private ResourceLoader $resourceLoader,
        private MarkuaLoader $markuaLoader
    ) {
    }

    public function enterNode(Node $node): ?Node
    {
        if (! $node instanceof IncludedResource) {
            return null;
        }

        if (count($node->attributes->attributes) !== 0) {
            // we'll take the existence of attributes as a hint that the writer want to show the contents of the file as it is
            return null;
        }

        if (str_ends_with($node->link, 'md') || str_ends_with($node->link, 'markdown')) {
            if (is_file($node->expectedFilePathname())) {
                return $this->markuaLoader->load(ExistingFile::fromPathname($node->expectedFilePathname()));
            }

            // The included file inherits the file attribute of the current node
            return $this->markuaLoader->loadString(
                $this->resourceLoader->load($node)
                    ->contents(),
                $node->getAttribute(MetaAttributes::FILE)
            );
        }

        return null;
    }
}
