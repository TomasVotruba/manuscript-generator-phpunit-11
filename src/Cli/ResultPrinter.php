<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Cli;

use ManuscriptGenerator\FileOperations\FileWasCreated;
use ManuscriptGenerator\FileOperations\FileWasModified;
use ManuscriptGenerator\ManuscriptDiff;
use ManuscriptGenerator\ManuscriptWasGenerated;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\GeneratedResourceWasStillFresh;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\ResourceWasGenerated;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\ConsoleColorDiff\Console\Output\ConsoleDiffer;

/**
 * @TODO show summary of the diff 'Resources generated: %d, Resources still fresh: %d, Files created: %d, Files updated: %d'
 * @TODO log $output->writeln(sprintf('<comment>generated</comment> %s', $event->link())); instead
 * @TODO log $output->writeln(sprintf('<comment>fresh</comment> %s', $event->link()));
 */
final class ResultPrinter
{
    public function __construct(
        private ConsoleDiffer $consoleDiffer
    ) {
    }

    public function printManuscriptDiff(ManuscriptDiff $diff, OutputInterface $output): void
    {
        foreach ($diff->newFiles() as $newFile) {
            $output->writeln(sprintf('<comment>new file</comment> %s', $this->relativePathname($newFile->filePathname())));
            $this->printDiff($output, $newFile->filePathname(), '', $newFile->contents());
        }

        foreach ($diff->modifiedFiles() as $modifiedFile) {
            $output->writeln(sprintf('<comment>modified</comment> %s', $this->relativePathname($modifiedFile->filePathname())));
            $this->printDiff($output, $modifiedFile->filePathname(), $modifiedFile->oldContents(), $modifiedFile->newContents());
        }
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

    private function printDiff(OutputInterface $output, string $filepath, string $old, string $new): void
    {
        $extension = pathinfo($filepath, PATHINFO_EXTENSION);
        assert(is_string($extension));

        if (in_array($extension, ['jpg', 'png'], true)) {
            // Don't show diff for binary content
            return;
        }

        $output->writeln($this->consoleDiffer->diff($old, $new));
    }
}
