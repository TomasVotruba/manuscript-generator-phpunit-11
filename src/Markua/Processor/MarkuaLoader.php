<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Processor;

use ManuscriptGenerator\FileOperations\ExistingFile;
use ManuscriptGenerator\Markua\Parser\Node\Document;
use ManuscriptGenerator\Markua\Parser\SimpleMarkuaParser;
use ManuscriptGenerator\Markua\Parser\Visitor\NodeTraverser;
use ManuscriptGenerator\Markua\Processor\Meta\AddFileAttributeNodeVisitor;
use Parsica\Parsica\ParserHasFailed;

final class MarkuaLoader
{
    public function __construct(
        private SimpleMarkuaParser $parser
    ) {
    }

    /**
     * @throws FailedToLoadMarkuaFile
     */
    public function load(string $markua, ExistingFile $markuaFile): Document
    {
        try {
            $document = $this->parser->parseDocument($markua);
        } catch (ParserHasFailed $exception) {
            throw FailedToLoadMarkuaFile::becauseItCouldNotBeParsed($markuaFile, $exception);
        }

        $nodeTraverser = new NodeTraverser([new AddFileAttributeNodeVisitor($markuaFile)]);

        return $nodeTraverser->traverseDocument($document);
    }
}
