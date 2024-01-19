<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Parser\Node;

final class InlineResource extends AbstractNode
{
    public AttributeList $attributes;

    public function __construct(
        public string $contents,
        public ?string $format = null,
        ?AttributeList $attributes = null
    ) {
        $this->attributes = $attributes ?? new AttributeList();
    }

    public function subnodeNames(): array
    {
        return ['attributes'];
    }

    public function format(): string
    {
        return $this->attributes->getStringOrNull('format') ?? 'guess';
    }
}
