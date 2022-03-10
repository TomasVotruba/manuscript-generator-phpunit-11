<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Cli;

use ManuscriptGenerator\Configuration\BookProjectConfiguration;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

abstract class AbstractCommand extends Command
{
    public const DEFAULT_CONFIG_FILE = 'book.php';

    protected function configure(): void
    {
        $this->addOption(
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

    protected function loadBookProjectConfiguration(InputInterface $input): BookProjectConfiguration
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
