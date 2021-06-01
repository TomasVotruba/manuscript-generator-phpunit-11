<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Processor;

use ManuscriptGenerator\Markua\Parser\Node;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\Markua\Parser\SimpleMarkuaParser;
use ManuscriptGenerator\Markua\Parser\Visitor\AbstractNodeVisitor;
use ManuscriptGenerator\ResourceLoader\ResourceLoader;
use Symplify\SmartFileSystem\SmartFileInfo;

final class InlineIncludedMarkdownFilesNodeVisitor extends AbstractNodeVisitor
{
    public function __construct(
        private ResourceLoader $resourceLoader,
        private SimpleMarkuaParser $markuaParser
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

        if (! str_ends_with($node->link, 'md') && ! str_ends_with($node->link, 'markdown')) {
            return null;
        }

        $includedFromFile = $node->getAttribute('file');
        assert($includedFromFile instanceof SmartFileInfo);

        // @TODO allow returning an array of nodes, the way it works now it causes an additional newline
        return $this->markuaParser->parseDocument($this->resourceLoader->load($node) ->contents());
    }
}
