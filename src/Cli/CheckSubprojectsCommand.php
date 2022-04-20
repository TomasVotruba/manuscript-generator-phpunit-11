<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Cli;

use Assert\Assertion;
use ManuscriptGenerator\Checker\CombinedChecker;
use ManuscriptGenerator\Checker\PhpStanChecker;
use ManuscriptGenerator\Checker\PhpUnitChecker;
use ManuscriptGenerator\Checker\RectorChecker;
use ManuscriptGenerator\Configuration\BookProjectConfiguration;
use ManuscriptGenerator\Dependencies\ComposerDependenciesInstaller;
use ManuscriptGenerator\FileOperations\ExistingDirectory;
use ManuscriptGenerator\Process\Result;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Process;

final class CheckSubprojectsCommand extends AbstractCommand
{
    private const PROJECT_ARGUMENT = 'project';

    /**
     * @param array<Result> $results
     */
    private static function hasFailingResult(array $results): bool
    {
        foreach ($results as $result) {
            if (!$result->isSuccessful()) {
                return true;
            }
        }

        return false;
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setName('check')
            ->addArgument(
                self::PROJECT_ARGUMENT,
                InputArgument::IS_ARRAY,
                'Path(s) to the specific subproject(s) you want to check'
            )
            ->addOption(
                'fail-fast',
                null,
                InputOption::VALUE_NONE,
                'Fail the command on the first subproject that has a failed check'
            )
            ->addOption(
                'parallel',
                null,
                InputOption::VALUE_REQUIRED,
                'The number of checks that should be started in parallel',
            )
            ->addOption('json', 'j', InputOption::VALUE_NONE, 'Show the results as JSON');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $bookProjectConfiguration = $this->loadBookProjectConfiguration($input);
        $failFast = $input->getOption('fail-fast');
        $showResultsAsJson = $input->getOption('json');
        $parallelJobs = (int) $input->getOption('parallel') ?: 1;

        if ($showResultsAsJson) {
            $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        }

        $symfonyStyle = new SymfonyStyle($input, $output);

        $projectDirs = $input->getArgument(self::PROJECT_ARGUMENT);

        $progress = new SymfonyStyleCheckProgress($symfonyStyle);

        if (count($projectDirs) > 0) {
            $allResults = $this->checkSpecificProjects(
                array_map(
                    fn(string $projectDir): ExistingDirectory => ExistingDirectory::fromPathname($projectDir),
                    $projectDirs
                ),
                $symfonyStyle,
                $failFast,
                $progress,
            );
        } else {
            $allResults = $this->checkAllProjects($bookProjectConfiguration, $progress, $parallelJobs, $failFast);
        }

        $progress->finish();

        $symfonyStyle->definitionList();

        /** @var array<Result> $failedResults */
        $failedResults = array_filter($allResults, fn(Result $result): bool => !$result->isSuccessful());

        if ($failedResults === []) {
            $symfonyStyle->success('All checks passed');
            $exitCode = self::SUCCESS;
        } else {
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

            $exitCode = self::FAILURE;
        }

        if ($showResultsAsJson) {
            $output->setVerbosity(OutputInterface::VERBOSITY_NORMAL);
            $jsonEncodedResults = json_encode(
                array_map(fn(Result $result): array => $result->toArray(), $allResults),
                JSON_THROW_ON_ERROR
            );
            Assertion::string($jsonEncodedResults);

            $output->writeln($jsonEncodedResults);
        }

        return $exitCode;
    }

    /**
     * @return array<string>
     */
    private function allProjectDirectories(BookProjectConfiguration $bookProjectConfiguration): array
    {
        $dir = $bookProjectConfiguration->manuscriptSrcDir()
            ->pathname();
        Assertion::string($dir);

        $subprojectMarkerFiles = Finder::create()
            ->in($dir)
            ->files()
            ->name('composer.json')
            ->notPath('vendor')
            ->sortByName(true);

        return array_map(
            fn(SplFileInfo $subprojectMarkerFile): string => $subprojectMarkerFile->getPath(),
            iterator_to_array($subprojectMarkerFiles)
        );
    }

    /**
     * @return array<Result>
     */
    private function checkAllProjects(
        BookProjectConfiguration $bookProjectConfiguration,
        CheckProgress $progress,
        int $parallelJobs,
        bool $failFast,
    ): array {
        $allDirectories = $this->allProjectDirectories($bookProjectConfiguration);
        $progress->setNumberOfDirectories(count($allDirectories));

        // Here we can start dividing the work

        $directoriesPerJob = (int) ceil(count($allDirectories) / $parallelJobs);

        $chunks = array_chunk($allDirectories, $directoriesPerJob);

        $checkCommands = [];
        foreach ($chunks as $chunk) {
            $cleanedUpCommand = array_filter(
                $_SERVER['argv'],
                fn (string $value) => $value !== '--fail-fast',
            );

            $checkCommand = new Process(array_merge($cleanedUpCommand, $chunk, ['--json']));
            $checkCommands[] = $checkCommand;
            $checkCommand->start();
        }

        $allResults = [];

        while (count($checkCommands) > 0) {
            foreach ($checkCommands as $key => $checkCommand) {
                if ($checkCommand->isRunning()) {
                    continue;
                }

                $json = $checkCommand->getOutput();
                $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
                Assertion::isArray($decoded);

                $allResults = array_merge(
                    $allResults,
                    array_map(fn(array $data): Result => Result::fromArray($data), $decoded)
                );

                if ($failFast && self::hasFailingResult($allResults)) {
                    return $allResults;
                }

                unset($checkCommands[$key]);
                // TODO update progress when we're done
            }
        }

        return $allResults;
    }

    /**
     * @param array<ExistingDirectory> $directories
     * @return array<Result>
     */
    private function checkSpecificProjects(
        array $directories,
        SymfonyStyle $symfonyStyle,
        bool $failFast,
        CheckProgress $progress
    ): array {
        $checker = new CombinedChecker(
            [new PhpStanChecker(), new PhpUnitChecker(), new RectorChecker()],
            new ComposerDependenciesInstaller(new ConsoleLogger($symfonyStyle)),
            new ConsoleLogger($symfonyStyle),
        );

        $progress->setNumberOfDirectories(count($directories));

        $allResults = [];

        foreach ($directories as $directory) {
            $progress->startChecking($directory);

            $dirResults = $checker->check($directory);
            $allResults = array_merge($allResults, $dirResults);

            if ($failFast && self::hasFailingResult($dirResults)) {
                return $allResults;
            }
        }

        return $allResults;
    }
}
