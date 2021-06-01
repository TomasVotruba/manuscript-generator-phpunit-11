<?php

declare(strict_types=1);

namespace ManuscriptGenerator\FileOperations;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class FileOperations
{
    public function __construct(
        private Filesystem $filesystem,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function putContents(string $pathname, string $contents): void
    {
        if ($this->filesystem->fileExists($pathname)) {
            $oldContents = $this->filesystem->getContents($pathname);
            $this->filesystem->putContents($pathname, $contents);

            $this->eventDispatcher->dispatch(new FileWasModified($pathname, $oldContents, $contents));
        } else {
            $this->filesystem->putContents($pathname, $contents);

            $this->eventDispatcher->dispatch(new FileWasCreated($pathname, $contents));
        }
    }
}
