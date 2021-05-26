<?php

declare(strict_types=1);

namespace BookTools\Cli;

use BookTools\FileOperations\FileWasCreated;
use BookTools\FileOperations\FileWasModified;
use BookTools\ResourceLoader\GeneratedResources\ResourceWasGenerated;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symplify\ConsoleColorDiff\Console\Output\ConsoleDiffer;

final class ResultPrinter implements EventSubscriberInterface
{
    public function __construct(
        private OutputInterface $output,
        private ConsoleDiffer $consoleDiffer
    ) {
    }

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    /**
     * @return array<class-string,array<string>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FileWasCreated::class => ['whenFileWasCreated'],
            FileWasModified::class => ['whenFileWasModified'],
            ResourceWasGenerated::class => ['whenResourceWasGenerated'],
        ];
    }

    public function whenResourceWasGenerated(ResourceWasGenerated $event): void
    {
        $this->output->writeln(sprintf('<comment>generated</comment> %s', $event->link()));
    }

    public function whenFileWasCreated(FileWasCreated $event): void
    {
        $this->output->writeln(sprintf('<comment>created</comment> %s', $this->relativePathname($event->filepath())));
        $this->printDiff('', $event->contents());
    }

    public function whenFileWasModified(FileWasModified $event): void
    {
        $this->output->writeln(sprintf('<comment>updated</comment> %s', $this->relativePathname($event->filepath())));
        $this->printDiff($event->oldContents(), $event->newContents());
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

    private function printDiff(string $old, string $new): void
    {
        $this->output->writeln($this->consoleDiffer->diff($old, $new));
    }
}
