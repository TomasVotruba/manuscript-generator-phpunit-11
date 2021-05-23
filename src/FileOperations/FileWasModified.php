<?php

declare(strict_types=1);

namespace BookTools\FileOperations;

final class FileWasModified
{
    public function __construct(
        private string $filepath,
        private string $oldContents,
        private string $newContents
    ) {
    }
}
