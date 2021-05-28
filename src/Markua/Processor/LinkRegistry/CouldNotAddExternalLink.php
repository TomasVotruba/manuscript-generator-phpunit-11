<?php

declare(strict_types=1);

namespace BookTools\Markua\Processor\LinkRegistry;

use RuntimeException;

final class CouldNotAddExternalLink extends RuntimeException
{
    public static function becauseTheSlugIsAlreadyInUse(string $slug, string $url): self
    {
        return new self(
            sprintf('Could not add external link "%s" because the slug "%s" is already in use', $url, $slug)
        );
    }
}
