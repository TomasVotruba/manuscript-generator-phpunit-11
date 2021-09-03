<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Parser\Node;

use Assert\Assertion;
use ManuscriptGenerator\FileOperations\ExistingFile;
use ManuscriptGenerator\Markua\Processor\Meta\MetaAttributes;

final class IncludedResource extends AbstractNode
{
    public AttributeList $attributes;

    public function __construct(
        public string $link,
        public ?string $caption = null,
        ?AttributeList $attributes = null
    ) {
        $this->attributes = $attributes === null ? new AttributeList() : $attributes;
    }

    public function subnodeNames(): array
    {
        return ['attributes'];
    }

    public function expectedFilePathname(): string
    {
        // @TODO fix this: the included manuscript file can be anywhere; use the manuscript-src dir!
        return $this->includedFromFile()
            ->directory() . '/resources/' . $this->link;
    }

    public function includedFile(): ExistingFile
    {
        // @TODO for BC, remove later
        if (file_exists($this->expectedFilePathname())) {
            return ExistingFile::fromPathname($this->expectedFilePathname());
        }

        return ExistingFile::fromPathname($this->includedFromFile()->directory() . '/' . $this->link);
    }

    public function includedFromFile(): ExistingFile
    {
        /** @var ExistingFile $includedFromFile */
        $includedFromFile = $this->getAttribute(MetaAttributes::FILE);
        Assertion::isInstanceOf($includedFromFile, ExistingFile::class);

        return $includedFromFile;
    }

    public function format(): string
    {
        $formatAttribute = $this->attributes->get('format');
        if ($formatAttribute !== null) {
            return $formatAttribute;
        }

        $extension = pathinfo($this->link, PATHINFO_EXTENSION);
        if ($extension !== '') {
            return $extension;
        }

        return 'guess';
    }
}
