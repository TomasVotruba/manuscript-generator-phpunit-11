<?php

declare(strict_types=1);

namespace BookTools;

final class ManuscriptWasGenerated
{
    public function __construct(
        private string $manuscriptDir
    ) {
    }

    public function manuscriptDir(): string
    {
        return $this->manuscriptDir;
    }
}
