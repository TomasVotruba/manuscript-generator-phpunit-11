<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Test\EndToEnd;

use Iterator;
use ManuscriptGenerator\Cli\GenerateManuscriptCommand;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Process;

final class GenerateManuscriptTest extends AbstractEndToEndTestCase
{
    private CommandTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester = new CommandTester(new GenerateManuscriptCommand());
    }

    public function testItUpdatesComposerDependenciesIfRequested(): void
    {
        $this->filesystem->mirror(__DIR__ . '/ComposerDependencies/manuscript-src', $this->manuscriptSrcDir);

        $this->tester->execute(
            [
                '--manuscript-dir' => $this->manuscriptDir,
                '--manuscript-src-dir' => $this->manuscriptSrcDir,
                '--update-dependencies' => true,
            ],
            [
                'verbosity' => OutputInterface::VERBOSITY_DEBUG,
            ]
        );

        self::assertStringContainsString('Running composer update', $this->tester->getDisplay());
    }

    public function testItDoesNotInstallComposerDependenciesIfComposerLockHasNotChanged(): void
    {
        $this->filesystem->mirror(__DIR__ . '/ComposerDependencies/manuscript-src', $this->manuscriptSrcDir);

        $this->tester->execute(
            [
                '--manuscript-dir' => $this->manuscriptDir,
                '--manuscript-src-dir' => $this->manuscriptSrcDir,
            ],
            [
                'verbosity' => OutputInterface::VERBOSITY_DEBUG,
            ]
        );

        // The first time it runs composer install
        self::assertStringContainsString('Running composer install', $this->tester->getDisplay());

        $this->tester->execute(
            [
                '--manuscript-dir' => $this->manuscriptDir,
                '--manuscript-src-dir' => $this->manuscriptSrcDir,
            ],
            [
                'verbosity' => OutputInterface::VERBOSITY_DEBUG,
            ]
        );

        // The second time it doesn't need to do it, since the vendor/ directory already exists
        self::assertStringNotContainsString('Running composer install', $this->tester->getDisplay());
        $this->tester->execute(
            [
                '--manuscript-dir' => $this->manuscriptDir,
                '--manuscript-src-dir' => $this->manuscriptSrcDir,
            ],
            [
                'verbosity' => OutputInterface::VERBOSITY_DEBUG,
            ]
        );

        // Fake a change in composer.json:
        touch($this->manuscriptSrcDir . '/tests/composer.json', time() + 10);

        $this->tester->execute(
            [
                '--manuscript-dir' => $this->manuscriptDir,
                '--manuscript-src-dir' => $this->manuscriptSrcDir,
            ],
            [
                'verbosity' => OutputInterface::VERBOSITY_DEBUG,
            ]
        );

        // composer.json has changed, so now it will run composer update
        self::assertStringContainsString('Running composer update', $this->tester->getDisplay());
    }

    public function testItRemovesNoLongerUsedImages(): void
    {
        // The first version of the src dir has a reference to image.png
        $this->filesystem->mirror(__DIR__ . '/CleanUpUnusedFiles/manuscript-src-1', $this->manuscriptSrcDir);

        $this->tester->execute(
            [
                '--manuscript-dir' => $this->manuscriptDir,
                '--manuscript-src-dir' => $this->manuscriptSrcDir,
            ],
            [
                'verbosity' => OutputInterface::VERBOSITY_DEBUG,
            ]
        );
        self::assertFileExists($this->manuscriptDir . '/resources/image.png');

        // The second version of the src dir has no reference to image.png anymore
        $this->filesystem->remove($this->manuscriptSrcDir);
        $this->filesystem->mirror(__DIR__ . '/CleanUpUnusedFiles/manuscript-src-2', $this->manuscriptSrcDir);
        $this->tester->execute(
            [
                '--manuscript-dir' => $this->manuscriptDir,
                '--manuscript-src-dir' => $this->manuscriptSrcDir,
            ],
            [
                'verbosity' => OutputInterface::VERBOSITY_DEBUG,
            ]
        );
        self::assertFileDoesNotExist($this->manuscriptDir . '/resources/image.png');

        self::assertDirectoryContentsEquals(
            __DIR__ . '/CleanUpUnusedFiles/manuscript-expected',
            $this->manuscriptDir
        );
    }

    #[DataProvider('manuscriptDirProvider')]
    public function testItGeneratesTheExpectedManuscript(string $manuscriptSrcDir, string $manuscriptExpectedDir): void
    {
        $this->filesystem->mirror($manuscriptSrcDir, $this->manuscriptSrcDir);

        $command = [
            '--manuscript-dir' => $this->manuscriptDir,
            '--manuscript-src-dir' => $this->manuscriptSrcDir,
        ];

        $configFile = $this->manuscriptSrcDir . '/book.php';
        if (is_file($configFile)) {
            $command['--config'] = $configFile;
        }

        $this->tester->execute($command);

        self::assertDirectoryContentsEquals($manuscriptExpectedDir, $this->manuscriptDir);
        self::assertSame(0, $this->tester->getStatusCode());
    }

    public function testGenerateTitlePage(): void
    {
        $process = new Process(['which', 'xcf2png']);
        $process->run();
        if (! $process->isSuccessful()) {
            $this->markTestSkipped('xcf2png is needed for generating the title page');
        }

        $this->filesystem->mirror(__DIR__ . '/GenerateTitlePage/manuscript-src', $this->manuscriptSrcDir);

        $this->tester->execute(
            [
                '--manuscript-dir' => $this->manuscriptDir,
                '--manuscript-src-dir' => $this->manuscriptSrcDir,
                '--config' => $this->manuscriptSrcDir . '/book.php',
            ]
        );

        self::assertFileExists($this->manuscriptDir . '/resources/title_page.png');
    }

    public static function manuscriptDirProvider(): Iterator
    {
        yield 'GuessFormat' => [
            __DIR__ . '/GuessFormat/manuscript-src',
            __DIR__ . '/GuessFormat/manuscript-expected',
        ];
        yield 'DisableMarkdownAutoImport' => [
            __DIR__ . '/DisableMarkdownAutoImport/manuscript-src',
            __DIR__ . '/DisableMarkdownAutoImport/manuscript-expected',
        ];
        yield 'Project' => [__DIR__ . '/Project/manuscript-src', __DIR__ . '/Project/manuscript-expected'];
        yield 'GeneratedResources' => [
            __DIR__ . '/GeneratedResources/manuscript-src',
            __DIR__ . '/GeneratedResources/manuscript-expected',
        ];
        yield 'CustomResourceProcessor' => [
            __DIR__ . '/CustomResourceProcessor/manuscript-src',
            __DIR__ . '/CustomResourceProcessor/manuscript-expected',
        ];
        yield 'LinkRegistry' => [
            __DIR__ . '/LinkRegistry/manuscript-src',
            __DIR__ . '/LinkRegistry/manuscript-expected',
        ];
        yield 'LinkRegistryWithExistingLinks' => [
            __DIR__ . '/LinkRegistryWithExistingLinks/manuscript-src',
            __DIR__ . '/LinkRegistryWithExistingLinks/manuscript-expected',
        ];
        yield 'CopyTitlePage' => [
            __DIR__ . '/CopyTitlePage/manuscript-src',
            __DIR__ . '/CopyTitlePage/manuscript-expected',
        ];
        yield 'Comments' => [__DIR__ . '/Comments/manuscript-src', __DIR__ . '/Comments/manuscript-expected'];
        yield 'Subset' => [__DIR__ . '/Subset/manuscript-src', __DIR__ . '/Subset/manuscript-expected'];
        yield 'IncludeRelativePaths' => [
            __DIR__ . '/IncludeRelativePaths/manuscript-src',
            __DIR__ . '/IncludeRelativePaths/manuscript-expected',
        ];
        yield 'AutomaticCaptions' => [
            __DIR__ . '/AutomaticCaptions/manuscript-src',
            __DIR__ . '/AutomaticCaptions/manuscript-expected',
        ];
        yield 'ComposerDependencies' => [
            __DIR__ . '/ComposerDependencies/manuscript-src',
            __DIR__ . '/ComposerDependencies/manuscript-expected',
        ];
        yield 'LongLines' => [__DIR__ . '/LongLines/manuscript-src', __DIR__ . '/LongLines/manuscript-expected'];
        yield 'CroppingAndSkipping' => [
            __DIR__ . '/CroppingAndSkipping/manuscript-src',
            __DIR__ . '/CroppingAndSkipping/manuscript-expected',
        ];
    }

    public function testItGeneratesResourcesOnlyIfTheyNeedToBeRefreshed(): void
    {
        $this->filesystem->mirror(__DIR__ . '/GeneratedResources/manuscript-src', $this->manuscriptSrcDir);

        $this->tester->execute(
            [
                '--manuscript-dir' => $this->manuscriptDir,
                '--manuscript-src-dir' => $this->manuscriptSrcDir,
                '--config' => $this->manuscriptSrcDir . '/book.php',
            ],
            [
                'verbosity' => OutputInterface::VERBOSITY_DEBUG,
            ]
        );

        // First time: phpunit-output.txt will be generated
        self::assertStringContainsString('Generated resource tests/phpunit-output.txt', $this->tester->getDisplay());

        $this->tester->execute(
            [
                '--manuscript-dir' => $this->manuscriptDir,
                '--manuscript-src-dir' => $this->manuscriptSrcDir,
                '--config' => $this->manuscriptSrcDir . '/book.php',
            ]
        );

        // Second time it won't
        self::assertStringNotContainsString('Generated resource tests/phpunit-output.txt', $this->tester->getDisplay());

        clearstatcache();
        // Third time: the generated output is older than one of the files of the folder it's in, so the test output
        // will be generated again
        $fileInSubDirectory = $this->manuscriptSrcDir . '/tests/test/NotTrueTest.php';
        touch($fileInSubDirectory, (int) filemtime($fileInSubDirectory) + 1000);

        $this->tester->execute(
            [
                '--manuscript-dir' => $this->manuscriptDir,
                '--manuscript-src-dir' => $this->manuscriptSrcDir,
                '--config' => $this->manuscriptSrcDir . '/book.php',
            ],
            [
                'verbosity' => OutputInterface::VERBOSITY_DEBUG,
            ]
        );

        self::assertStringContainsString('Generated resource tests/phpunit-output.txt', $this->tester->getDisplay());
    }

    public function testYouCanForceTheGeneratorToRegenerateAllGeneratedResources(): void
    {
        $this->filesystem->mirror(__DIR__ . '/GeneratedResources/manuscript-src', $this->manuscriptSrcDir);

        $this->tester->execute(
            [
                '--manuscript-dir' => $this->manuscriptDir,
                '--manuscript-src-dir' => $this->manuscriptSrcDir,
                '--config' => $this->manuscriptSrcDir . '/book.php',
            ],
            [
                'verbosity' => OutputInterface::VERBOSITY_DEBUG,
            ]
        );

        // First time: phpunit-output.txt will be generated
        self::assertStringContainsString('Generated resource tests/phpunit-output.txt', $this->tester->getDisplay());

        $this->tester->execute(
            [
                '--manuscript-dir' => $this->manuscriptDir,
                '--manuscript-src-dir' => $this->manuscriptSrcDir,
                '--config' => $this->manuscriptSrcDir . '/book.php',
            ]
        );

        // Second time it won't
        self::assertStringNotContainsString('Generated resource tests/phpunit-output.txt', $this->tester->getDisplay());

        // Third time we add the --force option
        $this->tester->execute(
            [
                '--manuscript-dir' => $this->manuscriptDir,
                '--manuscript-src-dir' => $this->manuscriptSrcDir,
                '--config' => $this->manuscriptSrcDir . '/book.php',
                '--force' => true,
            ],
            [
                'verbosity' => OutputInterface::VERBOSITY_DEBUG,
            ]
        );

        // The output will be regenerated
        self::assertStringContainsString('Generated resource tests/phpunit-output.txt', $this->tester->getDisplay());
    }

    public function testItFailsWhenUsingDryRunAndFilesWereModified(): void
    {
        $this->filesystem->mirror(__DIR__ . '/Project/manuscript-src', $this->manuscriptSrcDir);

        $this->tester->execute(
            [
                '--manuscript-dir' => $this->manuscriptDir,
                '--manuscript-src-dir' => $this->manuscriptSrcDir,
                '--dry-run' => true,
            ]
        );

        self::assertSame(1, $this->tester->getStatusCode());
    }

    public function testItSucceedsWhenUsingDryRunAndNothingChanged(): void
    {
        $this->filesystem->mirror(__DIR__ . '/Project/manuscript-src', $this->manuscriptSrcDir);

        $this->tester->execute(
            [
                '--manuscript-dir' => $this->manuscriptDir,
                '--manuscript-src-dir' => $this->manuscriptSrcDir,
            ]
        );

        // run again
        $this->tester->execute(
            [
                '--manuscript-dir' => $this->manuscriptDir,
                '--manuscript-src-dir' => $this->manuscriptSrcDir,
                '--dry-run' => true,
            ]
        );

        self::assertSame(0, $this->tester->getStatusCode());
    }

    private static function assertDirectoryContentsEquals(string $expectedDir, string $actualDir): void
    {
        /** @var SplFileInfo[] $expectedFiles */
        $expectedFiles = iterator_to_array(Finder::create()->files()->sortByName(true)->in($expectedDir), false);

        /** @var SplFileInfo[] $actualFiles */
        $actualFiles = iterator_to_array(Finder::create()->files()->sortByName(true)->in($actualDir), false);

        // The files are the same in both places
        self::assertSame(
            array_map(fn (SplFileInfo $fileInfo): string => $fileInfo->getRelativePathname(), $expectedFiles),
            array_map(fn (SplFileInfo $fileInfo): string => $fileInfo->getRelativePathname(), $actualFiles),
        );

        foreach ($expectedFiles as $expectedFile) {
            self::assertFileEquals(
                $expectedFile->getPathname(),
                $actualDir . '/' . $expectedFile->getRelativePathname(),
                sprintf('File "%s" does not contain the expected contents.', $expectedFile->getRelativePathname())
            );
        }
    }
}
