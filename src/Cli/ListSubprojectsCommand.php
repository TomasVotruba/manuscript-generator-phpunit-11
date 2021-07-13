<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

final class ListSubprojectsCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('list-subprojects')
            ->addArgument('dir', InputArgument::OPTIONAL, 'Where to look for composer.json files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dir = $input->getArgument('dir');
        if ($dir === null) {
            $dir = getcwd();
        }
        assert(is_string($dir));
        assert(is_dir($dir));

        $subprojectMarkerFiles = Finder::create()
            ->in($dir)
            ->depth('> 1')
            ->files()
            ->name('composer.json')
            ->notPath('vendor');

        $subprojects = [];

        foreach ($subprojectMarkerFiles as $subprojectMarkerFile) {
            $directory = $subprojectMarkerFile->getRelativePath();
            $subprojects[] = [
                'directory' => $directory,
                'runPhpUnit' => is_file($subprojectMarkerFile->getPath() . '/phpunit.ci.xml'),
                'runRector' => is_file($subprojectMarkerFile->getPath() . '/rector.ci.php'),
            ];
        }

        $jsonEncoded = json_encode($subprojects);
        assert(is_string($jsonEncoded));
        $output->write($jsonEncoded);

        return 0;
    }
}
