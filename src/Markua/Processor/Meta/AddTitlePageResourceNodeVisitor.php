<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Processor\Meta;

use Assert\Assertion;
use ManuscriptGenerator\Configuration\TitlePageConfiguration;
use ManuscriptGenerator\FileOperations\ExistingFile;
use ManuscriptGenerator\Markua\Parser\Node\Document;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\Markua\Parser\Visitor\AbstractNodeVisitor;

final class AddTitlePageResourceNodeVisitor extends AbstractNodeVisitor
{
    public function __construct(
        private TitlePageConfiguration $titlePage
    ) {
    }

    public function beforeTraversing(Document $document): void
    {
        if (! $this->titlePage->shouldTitlePageBeIncludedInManuscript()) {
            return;
        }

        $includedResource = new IncludedResource('title_page.png');
        $includedFromFile = $document->getAttribute(MetaAttributes::FILE);
        Assertion::isInstanceOf($includedFromFile, ExistingFile::class);
        $includedResource->setAttribute(MetaAttributes::FILE, $includedFromFile);

        if ($this->titlePage->shouldTitlePageBeGenerated()) {
            $includedResource->attributes->set('generator', $this->titlePage->generatorName());
            $includedResource->attributes->set('source', $this->titlePage->sourceFile());
        }

        $document->virtualNodes[] = $includedResource;
    }
}
