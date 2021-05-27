<?php

declare(strict_types=1);

namespace BookTools\Markua\Parser\Node;

use Symplify\SmartFileSystem\SmartFileInfo;

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
            ->getPath() . '/resources/' . $this->link;
    }

    public function includedFromFile(): SmartFileInfo
    {
        $includedFromFile = $this->getAttribute('file');
        assert($includedFromFile instanceof SmartFileInfo);

        return $includedFromFile;
    }
}
