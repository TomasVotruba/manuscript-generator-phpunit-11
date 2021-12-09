<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Checker;

use ManuscriptGenerator\Dependencies\DependenciesInstaller;
use ManuscriptGenerator\FileOperations\ExistingDirectory;
use ManuscriptGenerator\Process\Result;
use Symfony\Component\Console\Output\OutputInterface;

final class CombinedChecker
{
    /**
     * @param array<Checker> $checkers
     */
    public function __construct(
        private array $checkers,
        private DependenciesInstaller $dependenciesInstaller,
    ) {
    }

    /**
     * @param array<ExistingDirectory> $directories
     * @return array<Result>
     */
    public function checkAll(array $directories, OutputInterface $output): array
    {
        $results = [];

        foreach ($directories as $directory) {
            $output->writeln('Checking directory ' . $directory->pathname());

            $output->writeln('Installing dependencies');
            $this->dependenciesInstaller->install($directory);

            foreach ($this->checkers as $checker) {
                $output->writeln('Checker ' . $checker->name() . ': ');
                $result = $checker->check($directory);
                if ($result !== null) {
                    if (! $result->isSuccessful()) {
                        $output->writeln('<error>failed</error>');

                        $output->writeln($result->standardAndErrorOutputCombined());
                    }
                    {
                        $output->writeln('<info>success</info>');
                    }

                    $results[] = $result;
                } else {
                    $output->writeln('<comment>skipped</comment>');
                }
            }
            $output->writeln(PHP_EOL . PHP_EOL);
        }

        return $results;
    }
}
