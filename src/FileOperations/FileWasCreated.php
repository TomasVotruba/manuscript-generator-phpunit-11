<?php

declare(strict_types=1);

namespace BookTools\FileOperations;

final class FileWasCreated
{
    public function __construct(
        private string $filepath,
        private string $contents
    ) {
    }
}
