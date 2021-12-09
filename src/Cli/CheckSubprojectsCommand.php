<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Cli;

use Assert\Assertion;
use ManuscriptGenerator\Checker\CombinedChecker;
use ManuscriptGenerator\Checker\PhpStanChecker;
use ManuscriptGenerator\Checker\PhpUnitChecker;
use ManuscriptGenerator\Checker\RectorChecker;
use ManuscriptGenerator\Dependencies\ComposerDependenciesInstaller;
use ManuscriptGenerator\FileOperations\ExistingDirectory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class CheckSubprojectsCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('check');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $checker = new CombinedChecker(
            [new PhpStanChecker(), new PhpUnitChecker(), new RectorChecker()],
            new ComposerDependenciesInstaller(new ConsoleLogger($output))
        );

        $dir = getcwd();
        Assertion::string($dir);

        $subprojectMarkerFiles = Finder::create()
            ->in($dir)
            ->depth('> 1')
            ->files()
            ->name('composer.json')
            ->notPath('vendor')
            ->sortByName();

        $directories = array_map(
            fn (SplFileInfo $subprojectMarkerFile): ExistingDirectory => ExistingDirectory::fromPathname(
                $subprojectMarkerFile->getRelativePath()
            ),
            iterator_to_array($subprojectMarkerFiles)
        );

        $results = $checker->checkAll($directories, $output);

        $exitCode = self::SUCCESS;
        foreach ($results as $result) {
            if (! $result->isSuccessful()) {
                $exitCode = self::FAILURE;
            }
        }

        return $exitCode;
    }
}
