<?php
declare(strict_types=1);

namespace ManuscriptGenerator\Cli;

use ManuscriptGenerator\FileOperations\ExistingDirectory;
use ManuscriptGenerator\Process\Result;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class SymfonyStyleCheckResultsPrinter implements ProjectCheckResultsPrinter, CheckProgress
{
    private SymfonyStyle $symfonyStyle;
    private ProgressBar $progressBar;

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

        $failedResults = array_filter($allResults, fn(Result $result): bool => !$result->isSuccessful());

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

    public function setNumberOfDirectories(int $number)
    {
        $this->progressBar->setMaxSteps($number);
    }

    public function startChecking(ExistingDirectory $directory): void
    {
        $this->progressBar->setMessage($directory->pathname(), 'directory');
        $this->progressBar->setMessage('Checking');

        $this->progressBar->advance();
    }
}
