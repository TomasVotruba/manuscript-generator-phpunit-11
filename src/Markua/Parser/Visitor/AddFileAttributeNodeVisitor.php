<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Parser\Visitor;

use ManuscriptGenerator\FileOperations\ExistingFile;
use ManuscriptGenerator\Markua\Parser\Node;

final class AddFileAttributeNodeVisitor extends AbstractNodeVisitor
{
    public function __construct(
        private ExistingFile $file
    ) {
    }

    public function enterNode(Node $node): ?Node
    {
        $node->setAttribute('file', $this->file);

        return $node;
    }
}
