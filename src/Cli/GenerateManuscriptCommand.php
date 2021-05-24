<?php

declare(strict_types=1);

namespace BookTools\Cli;

use BookTools\Configuration;
use BookTools\DevelopmentServiceContainer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class GenerateManuscriptCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('generate-manuscript')
            ->addOption('dry-run', null, InputOption::VALUE_NONE)
            ->addOption('manuscript-dir', null, InputOption::VALUE_REQUIRED, '', 'manuscript')
            ->addOption('manuscript-src-dir', null, InputOption::VALUE_REQUIRED, 'manuscript-src');
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

        $container = new DevelopmentServiceContainer(
            new Configuration(
                $manuscriptSrcDir,
                $manuscriptTargetDir,
                true, // @TODO make this configurable,
                $dryRun
            )
        );
        $subscriber = new PrintResults($output);
        $container->eventDispatcher()
            ->addSubscriber($subscriber);

        $container->application()
            ->generateManuscript();

        if ($dryRun && $subscriber->filesWereModified()) {
            // --dry-run will fail CI if something would have changed
            return 1;
        }

        return 0;
    }
}
