<?php

declare(strict_types=1);

namespace BookTools\Markua\Processor;

use BookTools\Markua\Parser\SimpleMarkuaParser;
use BookTools\Markua\Parser\Visitor\AddFileAttributeNodeVisitor;
use BookTools\Markua\Parser\Visitor\NodeTraverser;
use BookTools\Markua\Parser\Visitor\NodeVisitor;
use BookTools\Markua\Printer\MarkuaPrinter;
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
        $document = $this->parser->parseDocument($markua);

        $nodeTraverser = new NodeTraverser(
            array_merge([new AddFileAttributeNodeVisitor($markuaFileInfo)], $this->nodeVisitors)
        );

        $result = $nodeTraverser->traverseDocument($document);

        return $this->markuaPrinter->printDocument($result);
    }
}
