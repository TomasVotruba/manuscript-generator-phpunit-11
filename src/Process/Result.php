<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Process;

final class Result
{
    public function __construct(
        private string $workingDir,
        private string $command,
        private string $standardOutput,
        private string $errorOutput,
        private string $combinedOutput,
        private int $exitCode
    ) {
    }

    public function command(): string
    {
        return $this->command;
    }

    public function standardOutput(): string
    {
        return $this->stripFilesystemContextFromOutput($this->standardOutput);
    }

    public function errorOutput(): string
    {
        return $this->stripFilesystemContextFromOutput($this->errorOutput);
    }

    public function standardAndErrorOutputCombined(): string
    {
        return $this->stripFilesystemContextFromOutput($this->combinedOutput);
    }

    public function isSuccessful(): bool
    {
        return $this->exitCode === 0;
    }

    private function stripFilesystemContextFromOutput(string $output): string
    {
        return str_replace($this->workingDir . '/', '', $output);
    }
}