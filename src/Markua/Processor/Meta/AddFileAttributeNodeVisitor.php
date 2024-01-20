<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Processor\Meta;

use ManuscriptGenerator\FileOperations\ExistingFile;
use ManuscriptGenerator\Markua\Parser\Node;
use ManuscriptGenerator\Markua\Parser\Node\Document;
use ManuscriptGenerator\Markua\Parser\Visitor\AbstractNodeVisitor;

final class AddFileAttributeNodeVisitor extends AbstractNodeVisitor
{
    public function __construct(
        private readonly ExistingFile $file
    ) {
    }

    public function beforeTraversing(Document $document): void
    {
        $document->setAttribute(MetaAttributes::FILE, $this->file);
    }

    public function enterNode(Node $node): ?Node
    {
        $node->setAttribute(MetaAttributes::FILE, $this->file);

        return $node;
    }
}
