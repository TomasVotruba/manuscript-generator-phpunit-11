<?php

declare(strict_types=1);

namespace BookTools\Cli;

use BookTools\BookProjectConfiguration;
use BookTools\DevelopmentServiceContainer;
use BookTools\FileOperations\FileWasCreated;
use BookTools\FileOperations\FileWasModified;
use BookTools\RuntimeConfiguration;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class GenerateManuscriptCommand extends Command implements EventSubscriberInterface
{
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
        $this->setName('generate-manuscript')
            ->addOption('dry-run', null, InputOption::VALUE_NONE)
            ->addOption('config', 'c', InputOption::VALUE_REQUIRED, '', 'book.php')
            ->addOption('manuscript-dir', null, InputOption::VALUE_REQUIRED)
            ->addOption('manuscript-src-dir', null, InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dryRun = $input->getOption('dry-run');
        assert(is_bool($dryRun));

        $container = new DevelopmentServiceContainer(
            new RuntimeConfiguration($this->loadBookProjectConfiguration($input), $dryRun)
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
        assert(is_string($config));

        if (is_file($config)) {
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
