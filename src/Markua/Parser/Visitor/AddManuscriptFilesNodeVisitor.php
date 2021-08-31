<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Parser\Visitor;

use ManuscriptGenerator\ManuscriptFiles;
use ManuscriptGenerator\Markua\Parser\Node;

final class AddManuscriptFilesNodeVisitor extends AbstractNodeVisitor
{
    public function __construct(
        private ManuscriptFiles $manuscriptFiles
    ) {
    }

    public function enterNode(Node $node): ?Node
    {
        $node->setAttribute('manuscript_files', $this->manuscriptFiles);

        return $node;
    }
}
