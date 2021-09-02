<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Processor;

use ManuscriptGenerator\FileOperations\ExistingFile;
use ManuscriptGenerator\ManuscriptFiles\ManuscriptFiles;
use ManuscriptGenerator\Markua\Parser\SimpleMarkuaParser;
use ManuscriptGenerator\Markua\Parser\Visitor\NodeTraverser;
use ManuscriptGenerator\Markua\Parser\Visitor\NodeVisitor;
use ManuscriptGenerator\Markua\Printer\MarkuaPrinter;
use ManuscriptGenerator\Markua\Processor\Meta\AddFileAttributeNodeVisitor;
use ManuscriptGenerator\Markua\Processor\Meta\AddManuscriptFilesNodeVisitor;
use Parsica\Parsica\ParserHasFailed;

final class AstBasedMarkuaProcessor implements MarkuaProcessor
{
    /**
     * @param array<NodeVisitor> $nodeVisitors
     */
    public function __construct(
        private array $nodeVisitors,
        private SimpleMarkuaParser $parser,
        private MarkuaPrinter $markuaPrinter
    ) {
    }

    public function process(ExistingFile $markuaFile, string $markua, ManuscriptFiles $manuscriptFiles): string
    {
        try {
            $document = $this->parser->parseDocument($markua);
        } catch (ParserHasFailed $exception) {
            throw FailedToProcessMarkua::becauseItCouldNotBeParsed($markuaFile->pathname(), $markua, $exception);
        }

        $nodeTraverser = new NodeTraverser(
            array_merge([
                new AddFileAttributeNodeVisitor($markuaFile),
                new AddManuscriptFilesNodeVisitor($manuscriptFiles),
            ], $this->nodeVisitors)
        );

        $result = $nodeTraverser->traverseDocument($document);

        return $this->markuaPrinter->printDocument($result);
    }
}
