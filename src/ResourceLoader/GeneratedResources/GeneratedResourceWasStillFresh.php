<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

final class GeneratedResourceWasStillFresh
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
