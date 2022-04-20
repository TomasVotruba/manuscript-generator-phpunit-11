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

final class CheckSubprojectsCommand extends AbstractCommand
{
    private const PROJECT_ARGUMENT = 'project';

    protected function configure(): void
    {
        parent::configure();

        $this->setName('check')
            ->addArgument(
                self::PROJECT_ARGUMENT,
                InputArgument::OPTIONAL,
                'Path to the specific subproject you want to check'
            )
            ->addOption(
                'fail-fast',
                'f',
                InputOption::VALUE_NONE,
                'Fail the command on the first subproject that has a failed check'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $bookProjectConfiguration = $this->loadBookProjectConfiguration($input);
        $failFast = $input->getOption('fail-fast');

        $checker = new CombinedChecker(
            [new PhpStanChecker(), new PhpUnitChecker(), new RectorChecker()],
            new ComposerDependenciesInstaller(new ConsoleLogger($output)),
            new ConsoleLogger($output),
        );

        $directories = $this->collectDirectories($bookProjectConfiguration, $input);

        $symfonyStyle = new SymfonyStyle($input, $output);

        $progress = new SymfonyStyleCheckProgress($symfonyStyle, count($directories));

        $allResults = [];

        foreach ($directories as $directory) {
            $progress->startChecking($directory);

            $dirResults = $checker->check($directory);
            $allResults = array_merge($allResults, $dirResults);

            if ($failFast) {
                foreach ($dirResults as $result) {
                    if (! $result->isSuccessful()) {
                        break 2;
                    }
                }
            }
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
    private function collectDirectories(
        BookProjectConfiguration $bookProjectConfiguration,
        InputInterface $input
    ): array {
        $specificProjectDir = $input->getArgument(self::PROJECT_ARGUMENT);
        if ($specificProjectDir !== null) {
            return [ExistingDirectory::fromPathname($specificProjectDir)];
        }

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
            fn (SplFileInfo $subprojectMarkerFile): ExistingDirectory => ExistingDirectory::fromPathname(
                $subprojectMarkerFile->getPath()
            ),
            iterator_to_array($subprojectMarkerFiles)
        );
    }
}
