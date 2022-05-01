<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Cli;

use Assert\Assertion;
use ManuscriptGenerator\ManuscriptFiles\File;
use ManuscriptGenerator\ManuscriptFiles\ManuscriptDiff;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\Output\ConsoleDiffer;

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
            $this->printDiff($output, $newFile);
        }

        foreach ($diff->modifiedFiles() as $modifiedFile) {
            $output->writeln(sprintf('<comment>modified</comment> %s', $modifiedFile->filePathname()));
            $this->printDiff($output, $modifiedFile);
        }

        foreach ($diff->unusedFiles() as $unusedFile) {
            $output->writeln(sprintf('<comment>unused</comment> %s', $unusedFile->filePathname()));
            $this->printDiff($output, $unusedFile);
        }
    }

    private function printDiff(OutputInterface $output, File $file): void
    {
        $extension = pathinfo($file->filePathname(), PATHINFO_EXTENSION);
        Assertion::string($extension);

        if (in_array($extension, ['jpg', 'png'], true)) {
            // Don't show diff for binary content
            return;
        }

        $output->writeln($this->consoleDiffer->diff($file->oldContents(), $file->newContents()));
    }
}
