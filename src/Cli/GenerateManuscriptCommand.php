<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Cli;

use ManuscriptGenerator\Configuration\BookProjectConfiguration;
use ManuscriptGenerator\Configuration\RuntimeConfiguration;
use ManuscriptGenerator\FileOperations\FileWasCreated;
use ManuscriptGenerator\FileOperations\FileWasModified;
use ManuscriptGenerator\ServiceContainer;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class GenerateManuscriptCommand extends Command implements EventSubscriberInterface
{
    public const DEFAULT_CONFIG_FILE = 'book.php';

    public const COMMAND_NAME = 'generate-manuscript';

    private bool $filesystemWasTouched = false;

    /**
     * @return array<class-string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FileWasCreated::class => 'filesystemWasTouched',
            FileWasModified::class => 'filesystemWasTouched',
        ];
    }

    public function filesystemWasTouched(): void
    {
        $this->filesystemWasTouched = true;
    }

    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME)
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Add this option to treat the filesystem as read-only'
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

        $updateDependencies = $input->getOption('update-dependencies');
        assert(is_bool($updateDependencies));

        $container = new ServiceContainer(
            new RuntimeConfiguration($this->loadBookProjectConfiguration($input), $dryRun, $updateDependencies)
        );

        // For showing results while generating the manuscript:
        $container->setOutput($output);
        $container->addEventSubscriber($this);

        $container->manuscriptGenerator()
            ->generateManuscript();
        if (! $dryRun) {
            // --dry-run will fail CI if the filesystem was touched
            return 0;
        }
        if (! $this->filesystemWasTouched) {
            // --dry-run will fail CI if the filesystem was touched
            return 0;
        }
        // --dry-run will fail CI if the filesystem was touched
        return 1;
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
