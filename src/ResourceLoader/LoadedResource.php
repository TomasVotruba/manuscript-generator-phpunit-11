<?php

declare(strict_types=1);

namespace BookTools\ResourceLoader;

use BookTools\Markua\Parser\Node\InlineResource;

final class LoadedResource
{
    public function __construct(
        private string $format,
        private string $contents
    ) {
    }

    public static function createFromInlineResource(InlineResource $node): self {
        return new self($node->attributes->get('format') ?? 'guess', $node->contents);
    }

    public function format(): string
    {
        return $this->format;
    }

    public function contents(): string
    {
        return $this->contents;
    }

    public function withContents(string $newContents): self
    {
        return new self($this->format, $newContents);
    }

    public static function createFromPathAndContents(string $pathname, string $contents): self
    {
        return new self(pathinfo($pathname, PATHINFO_EXTENSION), $contents);
    }
}
