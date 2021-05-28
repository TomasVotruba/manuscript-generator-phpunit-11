<?php

declare(strict_types=1);

namespace BookTools\Test\EndToEnd;

use BookTools\Cli\GenerateManuscriptCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class GenerateManuscriptTest extends TestCase
{
    private CommandTester $tester;

    private string $generatedManuscriptDir;

    protected function setUp(): void
    {
        $this->tester = new CommandTester(new GenerateManuscriptCommand());
        $this->generatedManuscriptDir = sys_get_temp_dir() . '/' . uniqid('manuscript');
    }

    protected function tearDown(): void
    {
        $filesystem = new Filesystem();

        $generatedFiles = [
            __DIR__ . '/Project/manuscript-src/resources/rector/rector-output.diff',
            __DIR__ . '/Project/manuscript-src/resources/tests/phpunit-output.txt',
            __DIR__ . '/Project/manuscript-src/resources/vendor',
        ];

        $filesystem->remove($generatedFiles);

        // Reset files for generated resources test
        $filesystem->remove(
            [
                __DIR__ . '/GeneratedResources/manuscript-src/resources/tests/phpunit-output.txt',
                __DIR__ . '/GeneratedResources/manuscript-src/resources/tokens/hello_world.table_of_tokens.md',
                __DIR__ . '/GeneratedResources/manuscript-src/resources/php_script/script.php_script_output.txt',
                __DIR__ . '/GeneratedResources/manuscript-src/resources/example.buffered-output.txt',
            ]
        );
        // Remove the entire generated manuscript dir
        $filesystem->remove($this->generatedManuscriptDir);
    }

    public function testItGeneratesTheManuscriptFolderBasedOnFilesReferencedInBookMdAndSubsetMd(): void
    {
        $this->tester->execute(
            [
                '--manuscript-dir' => $this->generatedManuscriptDir,
                '--manuscript-src-dir' => __DIR__ . '/Project/manuscript-src',
                '--config' => __DIR__ . '/Project/book.php',
            ]
        );

        self::assertDirectoryContentsEquals(__DIR__ . '/Project/manuscript-expected', $this->generatedManuscriptDir);

        self::assertSame(0, $this->tester->getStatusCode());
    }

    public function testItGeneratesResources(): void
    {
        $this->tester->execute(
            [
                '--manuscript-dir' => $this->generatedManuscriptDir,
                '--manuscript-src-dir' => __DIR__ . '/GeneratedResources/manuscript-src',
                '--config' => __DIR__ . '/GeneratedResources/manuscript-src/book.php',
            ]
        );

        self::assertDirectoryContentsEquals(
            __DIR__ . '/GeneratedResources/manuscript-expected',
            $this->generatedManuscriptDir
        );
    }

    public function testItGeneratesResourcesOnlyIfTheyNeedToBeRefreshed(): void
    {
        $this->tester->execute(
            [
                '--manuscript-dir' => $this->generatedManuscriptDir,
                '--manuscript-src-dir' => __DIR__ . '/GeneratedResources/manuscript-src',
                '--config' => __DIR__ . '/GeneratedResources/manuscript-src/book.php',
            ]
        );

        // First time: phpunit-output.txt will be generated
        self::assertStringContainsString('generated tests/phpunit-output.txt', $this->tester->getDisplay());

        $this->tester->execute(
            [
                '--manuscript-dir' => $this->generatedManuscriptDir,
                '--manuscript-src-dir' => __DIR__ . '/GeneratedResources/manuscript-src',
            ]
        );

        // Second time it won't
        self::assertStringNotContainsString('generated tests/phpunit-output.txt', $this->tester->getDisplay());

        // Third time: the generated output is older than the folder it's in, so the test output will be generated again
        $generatedPathFile = __DIR__ . '/GeneratedResources/manuscript-src/resources/tests/phpunit-output.txt';
        touch($generatedPathFile, (int) filemtime($generatedPathFile) - 1000);

        $this->tester->execute(
            [
                '--manuscript-dir' => $this->generatedManuscriptDir,
                '--manuscript-src-dir' => __DIR__ . '/GeneratedResources/manuscript-src',
            ]
        );

        self::assertStringContainsString('generated tests/phpunit-output.txt', $this->tester->getDisplay());
    }

    public function testItFailsWhenUsingDryRunAndFilesWereModified(): void
    {
        $this->tester->execute(
            [
                '--manuscript-dir' => $this->generatedManuscriptDir,
                '--manuscript-src-dir' => __DIR__ . '/Project/manuscript-src',
                '--dry-run' => true,
            ]
        );

        self::assertSame(1, $this->tester->getStatusCode());
    }

    public function testItReplacesExternalLinksWithLinksToTheLinkRegistry(): void
    {
        $this->tester->execute(
            [
                '--manuscript-dir' => $this->generatedManuscriptDir,
                '--manuscript-src-dir' => __DIR__ . '/LinkRegistry/manuscript-src',
                '--config' => __DIR__ . '/LinkRegistry/book.php',
            ]
        );

        self::assertDirectoryContentsEquals(
            __DIR__ . '/LinkRegistry/manuscript-expected',
            $this->generatedManuscriptDir
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
            array_map(fn (SplFileInfo $fileInfo) => $fileInfo->getRelativePathname(), $expectedFiles),
            array_map(fn (SplFileInfo $fileInfo) => $fileInfo->getRelativePathname(), $actualFiles),
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
