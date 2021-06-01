<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader;

use Exception;
use RuntimeException;

final class CouldNotLoadFile extends RuntimeException
{
    public static function createFromPrevious(Exception $exception, ?Exception $previous = null): self
    {
        return new self($exception->getMessage(), 0, $previous);
    }

    public static function becauseResourceIsNotSupported(): self
    {
        return new self('The given resource is not supported by this loader');
    }
}
