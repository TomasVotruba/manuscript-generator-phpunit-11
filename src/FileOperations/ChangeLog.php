<?php

declare(strict_types=1);

namespace BookTools\FileOperations;

final class ChangeLog
{
    /**
     * @var array<object>
     */
    private array $events = [];

    public function fileWasCreated(string $pathname, string $contents): void
    {
        $this->events[] = new FileWasCreated($pathname, $contents);
    }

    public function fileWasModified(string $pathname, string $oldContents, string $newContents): void
    {
        $this->events[] = new FileWasModified($pathname, $oldContents, $newContents);
    }
}
