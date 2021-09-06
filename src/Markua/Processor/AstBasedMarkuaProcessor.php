<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Processor;

use ManuscriptGenerator\FileOperations\ExistingFile;
use ManuscriptGenerator\ManuscriptFiles\ManuscriptFiles;
use ManuscriptGenerator\Markua\Parser\Visitor\NodeTraverser;
use ManuscriptGenerator\Markua\Parser\Visitor\NodeVisitor;
use ManuscriptGenerator\Markua\Printer\MarkuaPrinter;
use ManuscriptGenerator\Markua\Processor\Meta\AddManuscriptFilesNodeVisitor;
use ManuscriptGenerator\Markua\Processor\Meta\AddTitlePageResourceNodeVisitor;

final class AstBasedMarkuaProcessor implements MarkuaProcessor
{
    /**
     * @param array<NodeVisitor> $nodeVisitors
     */
    public function __construct(
        private array $nodeVisitors,
        private MarkuaLoader $markuaLoader,
        private MarkuaPrinter $markuaPrinter
    ) {
    }

    public function process(ExistingFile $markuaFile, ManuscriptFiles $manuscriptFiles): string
    {
        $document = $this->markuaLoader->load($markuaFile->getContents(), $markuaFile);

        $nodeTraverser = new NodeTraverser(
            array_merge([
                new AddManuscriptFilesNodeVisitor($manuscriptFiles),
                new AddTitlePageResourceNodeVisitor(),
            ], $this->nodeVisitors)
        );
        $result = $nodeTraverser->traverseDocument($document);

        return $this->markuaPrinter->printDocument($result);
    }
}
