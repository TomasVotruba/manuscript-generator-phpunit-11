<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Processor;

use ManuscriptGenerator\Markua\Parser\Node;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\Markua\Parser\Visitor\AbstractNodeVisitor;
use ManuscriptGenerator\Markua\Processor\Meta\MetaAttributes;
use ManuscriptGenerator\ResourceLoader\ResourceLoader;

final class ImportIncludedMarkdownFilesNodeVisitor extends AbstractNodeVisitor
{
    public function __construct(
        private ResourceLoader $resourceLoader,
        private MarkuaLoader $markuaLoader,
        private bool $autoImportMarkdownFiles
    ) {
    }

    public function enterNode(Node $node): ?Node
    {
        if (! $node instanceof IncludedResource) {
            return null;
        }
        $import = $node->attributes->getBoolOrNull('import');
        if ($import === null) {
            $import = $this->autoImportMarkdownFiles;
        }

        // Erase the attribute anyway, so it won't end up in the generated manuscript itself
        $node->attributes->remove('import');

        if (! $import) {
            return null;
        }

        if (str_ends_with($node->link, 'md') || str_ends_with($node->link, 'markdown')) {
            if ($node->expectedFile()->exists()) {
                $markuaFile = $node->expectedFile()
                    ->existing();
                $document = $this->markuaLoader->load($markuaFile->getContents(), $markuaFile);
            } else {
                // @TODO is still needed? I think the file will always exist so we only need the branch above
                // The included file inherits the file attribute of the current node
                $document = $this->markuaLoader->load(
                    $this->resourceLoader->load($node)
                        ->contents(),
                    $node->getAttribute(MetaAttributes::FILE)
                );
            }

            // Copy "subset" attribute
            $document->setAttribute('subset', $node->getAttribute('subset'));
            // @TODO should we copy all other attributes too?

            return $document;
        }

        return null;
    }
}
