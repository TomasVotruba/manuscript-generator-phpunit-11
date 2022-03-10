<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Cli;

use Assert\Assertion;
use ManuscriptGenerator\Checker\CombinedChecker;
use ManuscriptGenerator\Checker\PhpStanChecker;
use ManuscriptGenerator\Checker\PhpUnitChecker;
use ManuscriptGenerator\Checker\RectorChecker;
use ManuscriptGenerator\Dependencies\ComposerDependenciesInstaller;
use ManuscriptGenerator\FileOperations\ExistingDirectory;
use ManuscriptGenerator\Process\Result;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class CheckSubprojectsCommand extends Command
{
    private const PROJECT_ARGUMENT = 'project';

    protected function configure(): void
    {
        $this->setName('check')
            ->addArgument(
                self::PROJECT_ARGUMENT,
                InputArgument::OPTIONAL,
                'Path to the specific subproject you want to check'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $checker = new CombinedChecker(
            [new PhpStanChecker(), new PhpUnitChecker(), new RectorChecker()],
            new ComposerDependenciesInstaller(new ConsoleLogger($output)),
            new ConsoleLogger($output),
        );

        $directories = $this->collectDirectories($input);

        $symfonyStyle = new SymfonyStyle($input, $output);

        $progress = new SymfonyStyleCheckProgress($symfonyStyle, count($directories));

        $allResults = [];

        foreach ($directories as $directory) {
            $dirResults = $checker->check($directory, $progress);
            $allResults = array_merge($allResults, $dirResults);
        }

        $progress->finish();

        $symfonyStyle->definitionList();

        /** @var array<Result> $failedResults */
        $failedResults = array_filter($allResults, fn (Result $result): bool => ! $result->isSuccessful());

        if ($failedResults === []) {
            $symfonyStyle->success('All checks passed');
            return self::SUCCESS;
        }

        foreach ($failedResults as $failedResult) {
            $symfonyStyle->error('Failed check for subproject ' . $failedResult->workingDir()->pathname());
            $symfonyStyle->definitionList(
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

        $symfonyStyle->error(sprintf('Failed checks: %d', count($failedResults)));

        return self::FAILURE;
    }

    /**
     * @return array<ExistingDirectory>
     */
    private function collectDirectories(InputInterface $input): array
    {
        $specificProjectDir = $input->getArgument(self::PROJECT_ARGUMENT);
        if ($specificProjectDir !== null) {
            return [ExistingDirectory::fromPathname($specificProjectDir)];
        }

        $dir = getcwd();
        Assertion::string($dir);

        $subprojectMarkerFiles = Finder::create()
            ->in($dir)
            ->depth('> 1')
            ->files()
            ->name('composer.json')
            ->notPath('vendor')
            ->sortByName();

        return array_map(
            fn (SplFileInfo $subprojectMarkerFile): ExistingDirectory => ExistingDirectory::fromPathname(
                $subprojectMarkerFile->getRelativePath()
            ),
            iterator_to_array($subprojectMarkerFiles)
        );
    }
}
