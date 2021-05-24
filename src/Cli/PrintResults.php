<?php

declare(strict_types=1);

namespace BookTools\Cli;

use BookTools\FileOperations\FileWasCreated;
use BookTools\FileOperations\FileWasModified;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PrintResults implements EventSubscriberInterface
{
    private bool $filesWereModified = false;

    public function __construct(
        private OutputInterface $output
    ) {
    }

    /**
     * @return array<class-string,array<string>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FileWasCreated::class => ['whenFileWasCreated'],
            FileWasModified::class => ['whenFileWasModified'],
        ];
    }

    public function whenFileWasCreated(FileWasCreated $event): void
    {
        $this->output->writeln(sprintf('<comment>created</comment> %s', $this->relativePathname($event->filepath())));
        // @TODO show diff
        $this->output->writeln($event->contents());
        $this->filesWereModified = true;
    }

    public function whenFileWasModified(FileWasModified $event): void
    {
        $this->output->writeln(sprintf('<comment>updated</comment> %s', $this->relativePathname($event->filepath())));
        // @TODO show diff
        $this->output->writeln($event->newContents());
        $this->filesWereModified = true;
    }

    public function filesWereModified(): bool
    {
        return $this->filesWereModified;
    }

    private function relativePathname(string $filepath): string
    {
        $cwd = getcwd();
        assert(is_string($cwd));

        if (str_starts_with($filepath, $cwd)) {
            return substr($filepath, strlen($cwd));
        }

        return $filepath;
    }
}
