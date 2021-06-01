<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Processor\LinkRegistry;

use ManuscriptGenerator\Markua\Parser\Node\Link;
use RuntimeException;

final class CouldNotProcessExternalLink extends RuntimeException
{
    public static function becauseItHasNoSlugAttribute(Link $link): self
    {
        return new self(
            sprintf('Could not process external link to URL "%s" because it has no "slug" attribute', $link->target)
        );
    }
}
