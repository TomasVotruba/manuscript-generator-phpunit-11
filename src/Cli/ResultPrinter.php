<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Cli;

use Assert\Assertion;
use ManuscriptGenerator\ManuscriptDiff;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\ConsoleColorDiff\Console\Output\ConsoleDiffer;

final class ResultPrinter
{
    public function __construct(
        private ConsoleDiffer $consoleDiffer
    ) {
    }

    public function printManuscriptDiff(ManuscriptDiff $diff, OutputInterface $output): void
    {
        foreach ($diff->newFiles() as $newFile) {
            $output->writeln(sprintf('<comment>new file</comment> %s', $newFile->filePathname()));
            $this->printDiff($output, $newFile->filePathname(), '', $newFile->contents());
        }

        foreach ($diff->modifiedFiles() as $modifiedFile) {
            $output->writeln(sprintf('<comment>modified</comment> %s', $modifiedFile->filePathname()));
            $this->printDiff(
                $output,
                $modifiedFile->filePathname(),
                $modifiedFile->oldContents(),
                $modifiedFile->newContents()
            );
        }

        foreach ($diff->unusedFiles() as $unusedFile) {
            $output->writeln(sprintf('<comment>unused</comment> %s', $unusedFile->filePathname()));
            $this->printDiff($output, $unusedFile->filePathname(), $unusedFile->contents(), '');
        }
    }

    private function printDiff(OutputInterface $output, string $filepath, string $old, string $new): void
    {
        $extension = pathinfo($filepath, PATHINFO_EXTENSION);
        Assertion::string($extension);

        if (in_array($extension, ['jpg', 'png'], true)) {
            // Don't show diff for binary content
            return;
        }

        $output->writeln($this->consoleDiffer->diff($old, $new));
    }
}
