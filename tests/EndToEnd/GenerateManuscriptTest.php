<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Test\EndToEnd;

use ManuscriptGenerator\Cli\GenerateManuscriptCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Process;

final class GenerateManuscriptTest extends TestCase
{
    private Filesystem $filesystem;

    private CommandTester $tester;

    private string $manuscriptSrcDir;

    private string $manuscriptDir;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();

        $this->tester = new CommandTester(new GenerateManuscriptCommand());

        // Create temporary directories
        $this->manuscriptDir = sys_get_temp_dir() . '/' . uniqid('manuscript');
        $this->filesystem->mkdir($this->manuscriptDir);

        $this->manuscriptSrcDir = sys_get_temp_dir() . '/' . uniqid('manuscript-src');
        $this->filesystem->mkdir($this->manuscriptSrcDir);
    }

    protected function tearDown(): void
    {
        // Remove temporary directories
        $this->filesystem->remove($this->manuscriptDir);
        $this->filesystem->remove($this->manuscriptSrcDir);
    }

    public function testItGeneratesTheManuscriptFolderBasedOnFilesReferencedInBookMdAndSubsetMd(): void
    {
        $this->filesystem->mirror(__DIR__ . '/Project/manuscript-src', $this->manuscriptSrcDir);

        $this->tester->execute(
            [
                '--manuscript-dir' => $this->manuscriptDir,
                '--manuscript-src-dir' => $this->manuscriptSrcDir,
                '--config' => $this->manuscriptSrcDir . '/book.php',
            ]
        );

        self::assertDirectoryContentsEquals(__DIR__ . '/Project/manuscript-expected', $this->manuscriptDir);

        self::assertSame(0, $this->tester->getStatusCode());
    }

    public function testItGeneratesResources(): void
    {
        $this->filesystem->mirror(__DIR__ . '/GeneratedResources/manuscript-src', $this->manuscriptSrcDir);

        $this->tester->execute(
            [
                '--manuscript-dir' => $this->manuscriptDir,
                '--manuscript-src-dir' => $this->manuscriptSrcDir,
                '--config' => $this->manuscriptSrcDir . '/book.php',
            ]
        );

        self::assertDirectoryContentsEquals(
            __DIR__ . '/GeneratedResources/manuscript-expected',
            $this->manuscriptDir
        );
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

    /**
     * @dataProvider manuscriptDirProvider
     */
    public function testItGeneratesTheExpectedManuscript(string $manuscriptSrcDir, string $manuscriptExpectedDir): void
    {
        $this->filesystem->mirror($manuscriptSrcDir, $this->manuscriptSrcDir);

        $this->tester->execute(
            [
                '--manuscript-dir' => $this->manuscriptDir,
                '--manuscript-src-dir' => $this->manuscriptSrcDir,
            ]
        );

        self::assertDirectoryContentsEquals($manuscriptExpectedDir, $this->manuscriptDir);
    }

    /**
     * @return array<string, array{string,string}>
     */
    public function manuscriptDirProvider(): array
    {
        return [
            'IncludeRelativePaths' => [
                __DIR__ . '/IncludeRelativePaths/manuscript-src',
                __DIR__ . '/IncludeRelativePaths/manuscript-expected',
            ],
            'AutomaticCaptions' => [
                __DIR__ . '/AutomaticCaptions/manuscript-src',
                __DIR__ . '/AutomaticCaptions/manuscript-expected',
            ],
            'ComposerDependencies' => [
                __DIR__ . '/ComposerDependencies/manuscript-src',
                __DIR__ . '/ComposerDependencies/manuscript-expected',
            ],
            'LongLines' => [__DIR__ . '/LongLines/manuscript-src', __DIR__ . '/LongLines/manuscript-expected'],
            'CroppingAndSkipping' => [
                __DIR__ . '/CroppingAndSkipping/manuscript-src',
                __DIR__ . '/CroppingAndSkipping/manuscript-expected',
            ],
        ];
    }

    public function testItUsesAdditionalResourceProcessors(): void
    {
        $this->filesystem->mirror(__DIR__ . '/CustomResourceProcessor/manuscript-src', $this->manuscriptSrcDir);

        $this->tester->execute(
            [
                '--manuscript-dir' => $this->manuscriptDir,
                '--manuscript-src-dir' => $this->manuscriptSrcDir,
                '--config' => $this->manuscriptSrcDir . '/book.php',
            ]
        );

        self::assertDirectoryContentsEquals(
            __DIR__ . '/CustomResourceProcessor/manuscript-expected',
            $this->manuscriptDir
        );
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
        $process = new Process(['which', 'xcf2png']);
        $process->run();
        if (!$process->isSuccessful()) {
            $this->markTestSkipped('Using --force will also regenerate title_page.png, for which xcf2png is needed');
        }

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
                '--force' => true
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

    public function testItReplacesExternalLinksWithLinksToTheLinkRegistry(): void
    {
        $this->filesystem->mirror(__DIR__ . '/LinkRegistry/manuscript-src', $this->manuscriptSrcDir);

        $this->tester->execute(
            [
                '--manuscript-dir' => $this->manuscriptDir,
                '--manuscript-src-dir' => $this->manuscriptSrcDir,
                '--config' => $this->manuscriptSrcDir . '/book.php',
            ]
        );

        self::assertDirectoryContentsEquals(__DIR__ . '/LinkRegistry/manuscript-expected', $this->manuscriptDir);
    }

    public function testItKeepsExistingLinks(): void
    {
        $this->filesystem->mirror(__DIR__ . '/LinkRegistry/manuscript-src', $this->manuscriptSrcDir);

        $existingLinks = '/example https://example.com';

        file_put_contents($this->manuscriptSrcDir . '/links.txt', $existingLinks);

        $this->tester->execute(
            [
                '--manuscript-dir' => $this->manuscriptDir,
                '--manuscript-src-dir' => $this->manuscriptSrcDir,
                '--config' => $this->manuscriptSrcDir . '/book.php',
            ]
        );

        self::assertStringContainsString(
            $existingLinks,
            (string) file_get_contents($this->manuscriptDir . '/links.txt')
        );
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
