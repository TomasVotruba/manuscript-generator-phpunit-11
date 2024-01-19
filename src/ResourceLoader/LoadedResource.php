<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader;

use ManuscriptGenerator\Markua\Parser\Node\AttributeList;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\Markua\Parser\Node\InlineResource;

final class LoadedResource
{
    public function __construct(
        private readonly string $format,
        private string $contents,
        private readonly AttributeList $attributes
    ) {
    }

    public static function createFromInlineResource(InlineResource $inlineResource): self
    {
        return new self($inlineResource->format(), $inlineResource->contents, $inlineResource->attributes);
    }

    public static function createFromIncludedResource(IncludedResource $includedResource, string $contents): self
    {
        return new self($includedResource->format(), $contents, $includedResource->attributes);
    }

    public function format(): string
    {
        return $this->format;
    }

    public function contents(): string
    {
        return $this->contents;
    }

    public function setContents(string $newContents): void
    {
        $this->contents = $newContents;
    }

    public function attributes(): AttributeList
    {
        return $this->attributes;
    }
}
