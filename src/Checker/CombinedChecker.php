<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Checker;

use ManuscriptGenerator\Dependencies\DependenciesInstaller;
use ManuscriptGenerator\FileOperations\ExistingDirectory;
use ManuscriptGenerator\Process\Result;
use Symfony\Component\Console\Style\OutputStyle;

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
    public function checkAll(array $directories, OutputStyle $outputStyle): array
    {
        $results = [];

        foreach ($directories as $directory) {
            $title = sprintf('Checking "%s" directory', $directory->pathname());
            $outputStyle->title($title);

            $outputStyle->note('Installing dependencies');
            $this->dependenciesInstaller->install($directory);

            foreach ($this->checkers as $key => $checker) {
                $checkerMessage = sprintf('%d) Checker %s', $key + 1, $checker->name());
                $outputStyle->section($checkerMessage);

                $result = $checker->check($directory);
                if ($result !== null) {
                    if (! $result->isSuccessful()) {
                        $outputStyle->error('Failed');
                        $outputStyle->writeln($result->standardAndErrorOutputCombined());
                    } else {
                        $outputStyle->success('Success');
                    }

                    $results[] = $result;
                } else {
                    $outputStyle->warning('Skipped');
                }
            }
            $outputStyle->newLine(2);
        }

        return $results;
    }
}
