<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Cli\Check;

use Assert\Assertion;
use ManuscriptGenerator\Checker\CombinedChecker;
use ManuscriptGenerator\Checker\PhpStanChecker;
use ManuscriptGenerator\Checker\PhpUnitChecker;
use ManuscriptGenerator\Cli\AbstractCommand;
use ManuscriptGenerator\Configuration\BookProjectConfiguration;
use ManuscriptGenerator\Dependencies\ComposerDependenciesInstaller;
use ManuscriptGenerator\FileOperations\ExistingDirectory;
use ManuscriptGenerator\Process\Result;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Process;

final class CheckSubprojectsCommand extends AbstractCommand
{
    private const PROJECT_DIRS_ARGUMENT = 'project';

    protected function configure(): void
    {
        parent::configure();

        $this->setName('check')
            ->addArgument(
                self::PROJECT_DIRS_ARGUMENT,
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
            ->addOption('json', null, InputOption::VALUE_NONE, 'Show the results as JSON');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $failFast = $input->getOption('fail-fast');
        if ($input->getOption('json')) {
            $printer = new JsonProjectCheckResultsPrinter($output);
        } else {
            $printer = new SymfonyStyleCheckResultsPrinter($input, $output);
        }

        $projectDirs = $input->getArgument(self::PROJECT_DIRS_ARGUMENT);

        if (count($projectDirs) > 0) {
            $allResults = $this->checkSpecificProjects($projectDirs, $output, $failFast, $printer);
        } else {
            $allResults = $this->checkAllProjects(
                $this->loadBookProjectConfiguration($input),
                $printer,
                (int) $input->getOption('parallel') ?: 1,
                $failFast
            );
        }

        $printer->finish($allResults);

        if (Result::hasFailingResult($allResults)) {
            return self::FAILURE;
        }

        return self::SUCCESS;
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
            fn (SplFileInfo $subprojectMarkerFile): string => $subprojectMarkerFile->getPath(),
            iterator_to_array($subprojectMarkerFiles)
        );
    }

    /**
     * @return array<Result>
     */
    private function checkAllProjects(
        BookProjectConfiguration $bookProjectConfiguration,
        ProjectCheckResultsPrinter $resultPrinter,
        int $parallelJobs,
        bool $failFast,
    ): array {
        $allDirectories = $this->allProjectDirectories($bookProjectConfiguration);
        $resultPrinter->setNumberOfDirectories(count($allDirectories));

        $runningCommands = [];

        $runMoreCommandsUntilMaximumIsReached = function () use (
            $parallelJobs,
            &$runningCommands,
            &$allDirectories
        ): void {
            while (count($runningCommands) < $parallelJobs) {
                $directory = array_shift($allDirectories);
                if (! $directory) {
                    break;
                }

                $checkCommand = new Process(array_merge($_SERVER['argv'], [$directory], ['--json']));
                $runningCommands[$directory] = $checkCommand;
                $checkCommand->start();
            }
        };

        $runMoreCommandsUntilMaximumIsReached();

        $allResults = [];

        while (count($runningCommands) > 0) {
            foreach ($runningCommands as $directory => $checkCommand) {
                /** @var Process $checkCommand */
                if ($checkCommand->isRunning()) {
                    continue;
                }

                $json = $checkCommand->getOutput();
                Assertion::isJsonString($json, $checkCommand->getErrorOutput());

                $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
                Assertion::isArray($decoded);

                $results = array_map(fn (array $data): Result => Result::fromArray($data), $decoded);
                $allResults = array_merge($allResults, $results);

                if ($failFast && Result::hasFailingResult($allResults)) {
                    return $allResults;
                }

                unset($runningCommands[$directory]);

                $resultPrinter->advance($directory);

                $runMoreCommandsUntilMaximumIsReached();
            }
        }

        return $allResults;
    }

    /**
     * @param array<string> $directories
     * @return array<Result>
     */
    private function checkSpecificProjects(
        array $directories,
        OutputInterface $output,
        bool $failFast,
        ProjectCheckResultsPrinter $resultPrinter,
    ): array {
        if ($output instanceof ConsoleOutputInterface) {
            $output = $output->getErrorOutput();
        }

        $checker = new CombinedChecker(
            [new PhpStanChecker(), new PhpUnitChecker()],
            new ComposerDependenciesInstaller(new ConsoleLogger($output)),
            new ConsoleLogger($output),
        );

        $resultPrinter->setNumberOfDirectories(count($directories));

        $allResults = [];

        foreach ($directories as $directory) {
            $resultPrinter->advance($directory);

            $dirResults = $checker->check(ExistingDirectory::fromPathname($directory));
            $allResults = array_merge($allResults, $dirResults);

            if ($failFast && Result::hasFailingResult($dirResults)) {
                return $allResults;
            }
        }

        return $allResults;
    }
}
