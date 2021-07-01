<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Processor\LinkRegistry;

use RuntimeException;

final class CouldNotAddExternalLink extends RuntimeException
{
    public static function becauseTheSlugIsAlreadyInUse(string $slug, string $url, string $existingUrl): self
    {
        return new self(
            sprintf(
                'Could not add external link "%s" because the slug "%s" is already in use for a different URL (%s)',
                $url,
                $slug,
                $existingUrl
            )
        );
    }
}
