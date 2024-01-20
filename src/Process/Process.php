<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Process;

use ManuscriptGenerator\FileOperations\ExistingDirectory;
use RuntimeException;
use Symfony\Component\Process\Process as SymfonyProcess;

final readonly class Process
{
    /**
     * @param array<string> $command
     */
    public function __construct(
        private array $command,
        private ExistingDirectory $workingDir
    ) {
    }

    public function run(): Result
    {
        $process = new SymfonyProcess($this->command, $this->workingDir->pathname());

        $combinedOutput = '';

        $process->run(
            function (string $type, string $output) use (&$combinedOutput): void {
                $combinedOutput .= $output;
            }
        );

        $exitCode = $process->getExitCode();
        assert(is_int($exitCode));

        return new Result(
            $this->workingDir,
            $process->getCommandLine(),
            $process->getOutput(),
            $process->getErrorOutput(),
            $combinedOutput,
            $exitCode
        );
    }

    public function runSuccessfully(): Result
    {
        $result = $this->run();

        if (! $result->isSuccessful()) {
            throw new RuntimeException(
                sprintf(
                    "Process was not successful\nCommand line: %s\n\nOutput: \n\n%s",
                    $result->command(),
                    $result->standardAndErrorOutputCombined()
                )
            );
        }

        return $result;
    }
}
