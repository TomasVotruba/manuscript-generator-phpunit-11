<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Cli;

use ManuscriptGenerator\Configuration\RuntimeConfiguration;
use ManuscriptGenerator\ServiceContainer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class GenerateManuscriptCommand extends AbstractCommand
{
    public const COMMAND_NAME = 'generate-manuscript';

    protected function configure(): void
    {
        parent::configure();

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
}
