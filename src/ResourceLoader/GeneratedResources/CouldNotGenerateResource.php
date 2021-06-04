<?php
declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use RuntimeException;
use Symfony\Component\Process\Process;

final class CouldNotGenerateResource extends RuntimeException
{
    public static function becauseAnExternalProcessWasUnsuccessful(Process $process): self
    {
        return new self(
            sprintf(
                'Could not generate resource because an external process was unsuccessful: %s. Error output: %s',
                $process->getCommandLine(),
                $process->getErrorOutput()
            )
        );
    }
}
