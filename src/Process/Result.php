<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Process;

use ManuscriptGenerator\FileOperations\ExistingDirectory;

final readonly class Result
{
    public function __construct(
        private ExistingDirectory $workingDir,
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

    public function workingDir(): ExistingDirectory
    {
        return $this->workingDir;
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

    /**
     * @return array{workingDir: string, command: string, standardOutput: string, errorOutput: string, combinedOutput: string, exitCode: int}
     */
    public function toArray(): array
    {
        return [
            'workingDir' => $this->workingDir->pathname(),
            'command' => $this->command,
            'standardOutput' => $this->standardOutput,
            'errorOutput' => $this->errorOutput,
            'combinedOutput' => $this->combinedOutput,
            'exitCode' => $this->exitCode,
        ];
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            ExistingDirectory::fromPathname($data['workingDir']),
            $data['command'],
            $data['standardOutput'],
            $data['errorOutput'],
            $data['combinedOutput'],
            $data['exitCode'],
        );
    }

    /**
     * @param array<Result> $allResults
     */
    public static function hasFailingResult(array $allResults): bool
    {
        foreach ($allResults as $result) {
            if (! $result->isSuccessful()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<Result> $allResults
     * @return array<Result>
     */
    public static function failedResults(array $allResults): array
    {
        return array_filter($allResults, fn (Result $result): bool => ! $result->isSuccessful());
    }

    private function stripFilesystemContextFromOutput(string $output): string
    {
        return str_replace($this->workingDir->absolute()->pathname() . '/', '', $output);
    }
}
