<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Parser\Visitor;

use ManuscriptGenerator\Markua\Parser\Node;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AddFileAttributeNodeVisitor extends AbstractNodeVisitor
{
    public function __construct(
        private SmartFileInfo $file
    ) {
    }

    public function enterNode(Node $node): ?Node
    {
        $node->setAttribute('file', $this->file);

        return $node;
    }
}
