<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Processor\Meta;

use Assert\Assertion;
use ManuscriptGenerator\FileOperations\ExistingFile;
use ManuscriptGenerator\Markua\Parser\Node\Document;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\Markua\Parser\Visitor\AbstractNodeVisitor;

final class AddTitlePageResourceNodeVisitor extends AbstractNodeVisitor
{
    public function beforeTraversing(Document $document): void
    {
        $includedResource = new IncludedResource('title_page.png');
        $includedFromFile = $document->getAttribute(MetaAttributes::FILE);
        Assertion::isInstanceOf($includedFromFile, ExistingFile::class);

        $includedResource->setAttribute(MetaAttributes::FILE, $includedFromFile);
        $includedResource->attributes->set('generator', 'title_page');
        $includedResource->attributes->set('source', 'title_page.xcf');

        $document->virtualNodes[] = $includedResource;
    }
}
