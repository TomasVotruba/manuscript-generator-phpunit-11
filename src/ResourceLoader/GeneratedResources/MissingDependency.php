<?php

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use RuntimeException;

final class MissingDependency extends RuntimeException
{
    public static function process(string $name): self
    {
        return new self(sprintf('Dependency not found, process "%s"', $name));
    }
}
