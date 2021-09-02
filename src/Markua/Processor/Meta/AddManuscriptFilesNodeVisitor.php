<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Processor\Meta;

use ManuscriptGenerator\ManuscriptFiles\ManuscriptFiles;
use ManuscriptGenerator\Markua\Parser\Node;
use ManuscriptGenerator\Markua\Parser\Node\Document;
use ManuscriptGenerator\Markua\Parser\Visitor\AbstractNodeVisitor;

final class AddManuscriptFilesNodeVisitor extends AbstractNodeVisitor
{
    public function __construct(
        private ManuscriptFiles $manuscriptFiles
    ) {
    }

    public function beforeTraversing(Document $document): void
    {
        $document->setAttribute(MetaAttributes::MANUSCRIPT_FILES, $this->manuscriptFiles);
    }

    public function enterNode(Node $node): ?Node
    {
        $node->setAttribute(MetaAttributes::MANUSCRIPT_FILES, $this->manuscriptFiles);

        return $node;
    }
}
