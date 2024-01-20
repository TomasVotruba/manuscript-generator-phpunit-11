<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Processor\Subset;

use Assert\Assertion;
use ManuscriptGenerator\ManuscriptFiles\ManuscriptFiles;
use ManuscriptGenerator\Markua\Parser\Node;
use ManuscriptGenerator\Markua\Parser\Node\Document;
use ManuscriptGenerator\Markua\Parser\Visitor\AbstractNodeVisitor;
use ManuscriptGenerator\Markua\Printer\MarkuaPrinter;
use ManuscriptGenerator\Markua\Processor\Meta\MetaAttributes;

final class CreateSubsetNodeVisitor extends AbstractNodeVisitor
{
    /**
     * @var array<Node>
     */
    private array $nodes = [];

    public function __construct(
        private readonly MarkuaPrinter $markuaPrinter
    ) {
    }

    public function beforeTraversing(Document $document): void
    {
        $this->nodes = [];
    }

    public function enterNode(Node $node): ?Node
    {
        if ($node->getAttribute('subset') !== true) {
            return null;
        }

        $this->nodes[] = $node;

        return null;
    }

    public function afterTraversing(Document $document): void
    {
        if ($this->nodes === []) {
            return;
        }

        $subset = new Document($this->nodes);

        /** @var ManuscriptFiles $manuscriptFiles */
        $manuscriptFiles = $document->getAttribute(MetaAttributes::MANUSCRIPT_FILES);
        Assertion::isInstanceOf($manuscriptFiles, ManuscriptFiles::class);

        $manuscriptFiles->addFile('subset.md', $this->markuaPrinter->printDocument($subset));
        $manuscriptFiles->addFile('Subset.txt', "subset.md\n");
    }
}
