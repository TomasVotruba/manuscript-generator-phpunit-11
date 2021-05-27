<?php

declare(strict_types=1);

namespace BookTools\ResourceLoader;

use BookTools\Markua\Parser\Node\AttributeList;
use BookTools\Markua\Parser\Node\IncludedResource;
use BookTools\Markua\Parser\Node\InlineResource;

final class LoadedResource
{
    public function __construct(
        private string $format,
        private string $contents,
        private AttributeList $attributes
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

    public function getAttribute(string $key): ?string
    {
        return $this->attributes->get($key);
    }

    public function setAttribute(string $key, string $value): void
    {
        $this->attributes->set($key, $value);
    }

    public function removeAttribute(string $key): void
    {
        $this->attributes->remove($key);
    }
}
