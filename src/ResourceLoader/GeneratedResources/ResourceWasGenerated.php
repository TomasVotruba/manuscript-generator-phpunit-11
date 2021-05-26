<?php

declare(strict_types=1);

namespace BookTools\ResourceLoader\GeneratedResources;

final class ResourceWasGenerated
{
    public function __construct(
        private string $link
    ) {
    }

    public function link(): string
    {
        return $this->link;
    }
}
