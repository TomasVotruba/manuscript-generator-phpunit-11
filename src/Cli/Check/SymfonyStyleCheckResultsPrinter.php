<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Cli\Check;

use ManuscriptGenerator\Process\Result;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class SymfonyStyleCheckResultsPrinter implements ProjectCheckResultsPrinter
{
    private SymfonyStyle $symfonyStyle;

    private ProgressBar $progressBar;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->symfonyStyle = new SymfonyStyle($input, $output);
        ProgressBar::setFormatDefinition('check', ' %current%/%max%');

        $this->progressBar = $this->symfonyStyle->createProgressBar();
        $this->progressBar->setFormat('check');

        $this->progressBar->start();
    }

    public function finish(array $allResults): void
    {
        $this->progressBar->finish();
        $this->symfonyStyle->newLine();

        $failedResults = Result::failedResults($allResults);
        foreach ($failedResults as $failedResult) {
            $this->symfonyStyle->error('Failed check for subproject ' . $failedResult->workingDir()->pathname());
            $this->symfonyStyle->definitionList(
                [
                    'Working dir' => $failedResult->workingDir()
                        ->pathname(),
                ],
                [
                    'Failed command' => $failedResult->command(),
                ],
                [
                    'Output' => $failedResult->standardAndErrorOutputCombined(),
                ],
            );
        }

        $this->symfonyStyle->error(sprintf('Failed checks: %d', count($failedResults)));

        $this->symfonyStyle->success('All checks passed');
    }

    public function setNumberOfDirectories(int $number): void
    {
        $this->progressBar->setMaxSteps($number);
    }

    public function advance(int $numberOfDirs): void
    {
        $this->progressBar->advance($numberOfDirs);
    }
}
