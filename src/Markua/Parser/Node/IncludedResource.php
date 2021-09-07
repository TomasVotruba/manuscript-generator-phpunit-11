<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Parser\Node;

use Assert\Assertion;
use ManuscriptGenerator\FileOperations\Directory;
use ManuscriptGenerator\FileOperations\ExistingFile;
use ManuscriptGenerator\FileOperations\File;
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

    public function expectedFile(): File
    {
        return $this->includedFromFile()
            ->containingDirectory()
            ->appendPath($this->link)
            ->file();
    }

    public function directoryOfExpectedFile(): Directory
    {
        return $this->expectedFile()
            ->containingDirectory();
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
            Assertion::string($formatAttribute, '"format" attribute only supports strings');

            return $formatAttribute;
        }

        $extension = pathinfo($this->link, PATHINFO_EXTENSION);
        if ($extension !== '') {
            return $extension;
        }

        return 'guess';
    }

    public function debugInfo(): string
    {
        return sprintf('resource %s included from file %s', $this->link, $this->includedFromFile() ->pathname());
    }
}
