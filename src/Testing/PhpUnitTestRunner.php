<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Testing;

use ManuscriptGenerator\Configuration\RuntimeConfiguration;
use ManuscriptGenerator\Dependencies\DependenciesInstaller;
use ManuscriptGenerator\Process\Process;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;

final class PhpUnitTestRunner implements TestRunner
{
    public function __construct(
        private RuntimeConfiguration $configuration,
        private LoggerInterface $logger,
        private DependenciesInstaller $dependenciesInstaller
    ) {
    }

    public function run(): void
    {
        $this->logger->info('Running PHPUnit tests');

        $phpunitCiXmlFilename = 'phpunit.ci.xml'; // @TODO make configurable?

        $xmlFiles = Finder::create()->files()->in($this->configuration->manuscriptSrcDir())->name(
            $phpunitCiXmlFilename
        );

        foreach ($xmlFiles as $xmlFile) {
            $this->logger->debug('Running PHPUnit tests for {xmlFile}', [
                'xmlFile' => $xmlFile->getPathname(),
            ]);

            $this->dependenciesInstaller->install($xmlFile->getPath());

            $process = new Process([
                'vendor/bin/phpunit',
                '-c',
                $phpunitCiXmlFilename,
                '--do-not-cache-result',
            ], $xmlFile->getPath());
            $result = $process->run();

            if (! $result->isSuccessful()) {
                throw new TestFailed(
                    "PHPUnit test run not successful. Output: \n\n" . $result->standardAndErrorOutputCombined()
                );
            }
        }
    }
}
