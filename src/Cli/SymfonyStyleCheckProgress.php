<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Cli;

use ManuscriptGenerator\FileOperations\ExistingDirectory;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;

final class SymfonyStyleCheckProgress implements CheckProgress
{
    private ProgressBar $progressBar;

    public function __construct(
        private SymfonyStyle $symfonyStyle,
        int $numberOfDirectories
    ) {
        ProgressBar::setFormatDefinition('check', ' %current%/%max% -- %message% (%directory%)');

        $this->progressBar = $this->symfonyStyle->createProgressBar($numberOfDirectories);
        $this->progressBar->setFormat('check');
        $this->progressBar->setMessage('Start');
        $this->progressBar->setMessage(getcwd() ?: 'cwd', 'directory');

        $this->progressBar->start();
    }

    public function startChecking(ExistingDirectory $directory): void
    {
        $this->progressBar->setMessage($directory->pathname(), 'directory');
        $this->progressBar->setMessage('Checking');

        $this->progressBar->advance();
    }

    public function finish(): void
    {
        $this->progressBar->finish();
        $this->symfonyStyle->newLine();
    }
}
