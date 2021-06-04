<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Processor;

use ManuscriptGenerator\Markua\Parser\SimpleMarkuaParser;
use ManuscriptGenerator\Markua\Parser\Visitor\AddFileAttributeNodeVisitor;
use ManuscriptGenerator\Markua\Parser\Visitor\NodeTraverser;
use ManuscriptGenerator\Markua\Parser\Visitor\NodeVisitor;
use ManuscriptGenerator\Markua\Printer\MarkuaPrinter;
use Parsica\Parsica\ParserHasFailed;
use Symplify\SmartFileSystem\SmartFileInfo;

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

    public function process(SmartFileInfo $markuaFileInfo, string $markua): string
    {
        try {
            $document = $this->parser->parseDocument($markua);
        } catch (ParserHasFailed $exception) {
            throw FailedToProcessMarkua::becauseItCouldNotBeParsed((string) $markuaFileInfo, $markua, $exception);
        }

        $nodeTraverser = new NodeTraverser(
            array_merge([new AddFileAttributeNodeVisitor($markuaFileInfo)], $this->nodeVisitors)
        );

        $result = $nodeTraverser->traverseDocument($document);

        return $this->markuaPrinter->printDocument($result);
    }
}
