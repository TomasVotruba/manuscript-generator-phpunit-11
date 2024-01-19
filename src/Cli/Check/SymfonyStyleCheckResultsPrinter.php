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
    private readonly SymfonyStyle $symfonyStyle;

    private readonly ProgressBar $progressBar;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->symfonyStyle = new SymfonyStyle($input, $output);
        ProgressBar::setFormatDefinition('check', ' %current%/%max% -- %message% (%directory%)');

        $this->progressBar = $this->symfonyStyle->createProgressBar();
        $this->progressBar->setFormat('check');
        $this->progressBar->setMessage('Start');
        $this->progressBar->setMessage(getcwd() ?: 'cwd', 'directory');

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

        if (count($failedResults) > 0) {
            $this->symfonyStyle->error(sprintf('Failed checks: %d', count($failedResults)));
        } else {
            $this->symfonyStyle->success('All checks passed');
        }
    }

    public function setNumberOfDirectories(int $number): void
    {
        $this->progressBar->setMaxSteps($number);
    }

    public function advance(string $directory): void
    {
        $this->progressBar->setMessage($directory, 'directory');
        $this->progressBar->setMessage('Checking');

        $this->progressBar->advance();
    }
}
