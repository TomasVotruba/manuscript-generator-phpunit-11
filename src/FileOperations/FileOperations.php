<?php

declare(strict_types=1);

namespace BookTools\FileOperations;

final class FileOperations
{
    private ChangeLog $changeLog;

    public function __construct(
        private Filesystem $filesystem
    ) {
        $this->changeLog = new ChangeLog();
    }

    public function putContents(string $pathname, string $contents): void
    {
        if ($this->filesystem->fileExists($pathname)) {
            $oldContents = $this->filesystem->getContents($pathname);
            $this->filesystem->putContents($pathname, $contents);

            $this->changeLog->fileWasModified($pathname, $oldContents, $contents);
        } else {
            $this->filesystem->putContents($pathname, $contents);

            $this->changeLog->fileWasCreated($pathname, $contents);
        }
    }
}
