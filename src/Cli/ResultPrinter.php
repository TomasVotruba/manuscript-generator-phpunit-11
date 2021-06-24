<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Cli;

use ManuscriptGenerator\FileOperations\FileWasCreated;
use ManuscriptGenerator\FileOperations\FileWasModified;
use ManuscriptGenerator\ManuscriptWasGenerated;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\GeneratedResourceWasStillFresh;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\ResourceWasGenerated;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symplify\ConsoleColorDiff\Console\Output\ConsoleDiffer;

final class ResultPrinter implements EventSubscriberInterface
{
    private int $countResourcesGenerated = 0;

    private int $countResourcesStillFresh = 0;

    private int $countFilesCreated = 0;

    private int $countFilesUpdated = 0;

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
            ManuscriptWasGenerated::class => ['whenManuscriptWasGenerated'],
        ];
    }

    public function whenResourceWasGenerated(ResourceWasGenerated $event): void
    {
        $this->countResourcesGenerated++;

        $this->output->writeln(sprintf('<comment>generated</comment> %s', $event->link()));
    }

    public function whenResourceWasStillFresh(GeneratedResourceWasStillFresh $event): void
    {
        $this->countResourcesStillFresh++;

        $this->output->writeln(sprintf('<comment>fresh</comment> %s', $event->link()));
    }

    public function whenFileWasCreated(FileWasCreated $event): void
    {
        $this->countFilesCreated++;

        $this->output->writeln(sprintf('<comment>created</comment> %s', $this->relativePathname($event->filepath())));
        $this->printDiff($event->filepath(), '', $event->contents());
    }

    public function whenFileWasModified(FileWasModified $event): void
    {
        $this->countFilesUpdated++;

        $this->output->writeln(sprintf('<comment>updated</comment> %s', $this->relativePathname($event->filepath())));

        $this->printDiff($event->filepath(), $event->oldContents(), $event->newContents());
    }

    public function whenManuscriptWasGenerated(ManuscriptWasGenerated $event): void
    {
        $this->output->writeln(
            sprintf(
                'Resources generated: %d, Resources still fresh: %d, Files created: %d, Files updated: %d',
                $this->countResourcesGenerated,
                $this->countResourcesStillFresh,
                $this->countFilesCreated,
                $this->countFilesUpdated
            )
        );

        $this->resetCounters();
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

    private function printDiff(string $filepath, string $old, string $new): void
    {
        $extension = pathinfo($filepath, PATHINFO_EXTENSION);
        assert(is_string($extension));

        if (in_array($extension, ['jpg', 'png'], true)) {
            // Don't show diff for binary content
            return;
        }

        $this->output->writeln($this->consoleDiffer->diff($old, $new));
    }

    private function resetCounters(): void
    {
        $this->countFilesCreated = 0;
        $this->countFilesUpdated = 0;
        $this->countResourcesGenerated = 0;
    }
}
