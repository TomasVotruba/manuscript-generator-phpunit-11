<?php

declare(strict_types=1);

namespace BookTools\Cli;

use BookTools\Configuration;
use BookTools\DevelopmentServiceContainer;
use BookTools\FileOperations\FileWasCreated;
use BookTools\FileOperations\FileWasModified;
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
            ->addOption('manuscript-dir', null, InputOption::VALUE_REQUIRED, '', 'manuscript')
            ->addOption('manuscript-src-dir', null, InputOption::VALUE_REQUIRED, 'manuscript-src')
            ->addOption('capitalize-headlines', null, InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dryRun = $input->getOption('dry-run');
        assert(is_bool($dryRun));

        // @TODO use a factory
        $manuscriptSrcDir = $input->getOption('manuscript-src-dir');
        assert(is_string($manuscriptSrcDir));

        $manuscriptTargetDir = $input->getOption('manuscript-dir');
        assert(is_string($manuscriptTargetDir));

        $capitalizeHeadlines = $input->getOption('capitalize-headlines');
        assert(is_bool($capitalizeHeadlines));

        $container = new DevelopmentServiceContainer(
            new Configuration($manuscriptSrcDir, $manuscriptTargetDir, $capitalizeHeadlines, $dryRun)
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
}
