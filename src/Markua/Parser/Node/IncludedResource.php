<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Parser\Node;

use ManuscriptGenerator\FileOperations\ExistingFile;

final class IncludedResource extends AbstractNode
{
    public AttributeList $attributes;

    public function __construct(
        public string $link,
        public ?string $caption,
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
        return $this->includedFromFile()
            ->directory() . '/resources/' . $this->link;
    }

    public function includedFromFile(): ExistingFile
    {
        $includedFromFile = $this->getAttribute('file');
        assert($includedFromFile instanceof ExistingFile);

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
