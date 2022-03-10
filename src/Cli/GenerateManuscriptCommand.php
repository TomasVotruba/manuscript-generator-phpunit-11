<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Cli;

use ManuscriptGenerator\Configuration\BookProjectConfiguration;
use ManuscriptGenerator\Configuration\RuntimeConfiguration;
use ManuscriptGenerator\ServiceContainer;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class GenerateManuscriptCommand extends Command
{
    public const DEFAULT_CONFIG_FILE = 'book.php';

    public const COMMAND_NAME = 'generate-manuscript';

    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME)
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Add this option to make no changes to the current version of the generated manuscript'
            )
            ->addOption(
                'force',
                null,
                InputOption::VALUE_NONE,
                'Add this option to force generated resources to be generated again'
            )
            ->addOption(
                'update-dependencies',
                null,
                InputOption::VALUE_NONE,
                'Add this option to update Composer dependencies for all subprojects in the manuscript source directory'
            )
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_REQUIRED,
                'Provide the path to the book.php configuration file'
            )
            ->addOption(
                'manuscript-dir',
                null,
                InputOption::VALUE_REQUIRED,
                'The directory where the manuscript is stored (default for Leanpub: manuscript/)'
            )
            ->addOption(
                'manuscript-src-dir',
                null,
                InputOption::VALUE_REQUIRED,
                'The directory where the manuscript source files are stored (default: manuscript-src)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dryRun = $input->getOption('dry-run');
        assert(is_bool($dryRun));

        $force = $input->getOption('force');
        assert(is_bool($force));

        $updateDependencies = $input->getOption('update-dependencies');
        assert(is_bool($updateDependencies));

        $configuration = new RuntimeConfiguration($dryRun, $force, $updateDependencies);
        $container = new ServiceContainer($configuration, $this->loadBookProjectConfiguration($input), $output);

        $manuscriptGenerator = $container->manuscriptGenerator();

        $manuscriptFiles = $manuscriptGenerator->generateManuscript();

        $diff = $manuscriptGenerator->diffWithExistingManuscriptDir($manuscriptFiles);

        $manuscriptGenerator->printDiff($diff, $output);

        if ($dryRun && $diff->hasDifferences()) {
            // --dry-run will fail CI if the filesystem was touched
            return self::FAILURE;
        }

        $manuscriptGenerator->dumpManuscriptFiles($manuscriptFiles);

        return self::SUCCESS;
    }

    private function loadBookProjectConfiguration(InputInterface $input): BookProjectConfiguration
    {
        $config = $input->getOption('config');
        if (is_string($config) && ! is_file($config)) {
            throw new RuntimeException('Configuration file not found: ' . $config);
        }
        if (is_file(self::DEFAULT_CONFIG_FILE)) {
            $config = self::DEFAULT_CONFIG_FILE;
        }

        if (is_string($config)) {
            $bookProjectConfiguration = require $config;
            if (! $bookProjectConfiguration instanceof BookProjectConfiguration) {
                throw new RuntimeException(
                    sprintf('Expected file "%s" to return an instance of BookProjectConfiguration', $config)
                );
            }
        } else {
            $bookProjectConfiguration = BookProjectConfiguration::usingDefaults();
        }

        $manuscriptSrcDir = $input->getOption('manuscript-src-dir');
        if (is_string($manuscriptSrcDir)) {
            $bookProjectConfiguration->setManuscriptSrcDir($manuscriptSrcDir);
        }

        $manuscriptTargetDir = $input->getOption('manuscript-dir');
        if (is_string($manuscriptTargetDir)) {
            $bookProjectConfiguration->setManuscriptTargetDir($manuscriptTargetDir);
        }

        return $bookProjectConfiguration;
    }
}
