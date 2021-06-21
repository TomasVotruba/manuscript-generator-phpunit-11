<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Process;

final class Result
{
    public function __construct(
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
        return $this->standardOutput;
    }

    public function errorOutput(): string
    {
        return $this->errorOutput;
    }

    public function standardAndErrorOutputCombined(): string
    {
        return $this->combinedOutput;
    }

    public function isSuccessful(): bool
    {
        return $this->exitCode === 0;
    }
}
