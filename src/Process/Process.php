<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Process;

use Symfony\Component\Process\Process as SymfonyProcess;

final class Process
{
    private string $workingDir;

    /**
     * @param array<string> $command
     * @param string $workingDir
     */
    public function __construct(
        private array $command,
        ?string $workingDir = null
    ) {
        if ($workingDir === null) {
            $workingDir = getcwd();
        }

        assert(is_string($workingDir));
        $this->workingDir = $workingDir;
    }

    public function run(): Result
    {
        $process = new SymfonyProcess($this->command, $this->workingDir);

        $combinedOutput = '';

        $process->run(
            function (string $type, string $output) use (&$combinedOutput): void {
                $combinedOutput .= $output;
            }
        );

        $exitCode = $process->getExitCode();
        assert(is_int($exitCode));

        return new Result(
            $process->getCommandLine(),
            $process->getOutput(),
            $process->getErrorOutput(),
            $combinedOutput,
            $exitCode
        );
    }
}
