<?php

declare(strict_types=1);

namespace ManuscriptGenerator;

final class ManuscriptDiff
{
    /**
     * @param array<File> $newFiles
     * @param array<ModifiedFile> $modifiedFiles
     * @param array<File> $unusedFiles
     */
    public function __construct(
        private array $newFiles,
        private array $modifiedFiles,
        private array $unusedFiles
    ) {
    }

    /**
     * @return array<File>
     */
    public function newFiles(): array
    {
        return $this->newFiles;
    }

    /**
     * @return array<ModifiedFile>
     */
    public function modifiedFiles(): array
    {
        return $this->modifiedFiles;
    }

    /**
     * @return array<File>
     */
    public function unusedFiles(): array
    {
        return $this->unusedFiles;
    }

    public function hasDifferences(): bool
    {
        return $this->newFiles !== [] || $this->modifiedFiles !== [] || $this->unusedFiles !== [];
    }
}
