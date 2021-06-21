<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\Process\Result;
use RuntimeException;

final class CouldNotGenerateResource extends RuntimeException
{
    public static function becauseAnExternalProcessWasUnsuccessful(Result $result): self
    {
        return new self(
            sprintf(
                'Could not generate resource because an external process was unsuccessful: %s. Error output: %s',
                $result->command(),
                $result->standardAndErrorOutputCombined()
            )
        );
    }

    public static function becauseSourceFileNotFound(string $sourceFilePathname): self
    {
        return new self(
            sprintf('Could not generate resource because source file not found: %s', $sourceFilePathname)
        );
    }
}
