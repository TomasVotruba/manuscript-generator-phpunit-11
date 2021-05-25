<?php

declare(strict_types=1);

namespace BookTools\Test\EndToEnd;

use BookTools\Cli\GenerateManuscriptCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class GenerateManuscriptTest extends TestCase
{
    private string $manuscriptDir;

    private string $manuscriptSrcDir;

    protected function setUp(): void
    {
        $this->manuscriptSrcDir = __DIR__ . '/Project/manuscript-src';
        $this->manuscriptDir = sys_get_temp_dir() . '/' . uniqid('manuscript');
    }

    protected function tearDown(): void
    {
        // @TODO read the paths from the tester's output
        $generatedFiles = [
            $this->manuscriptSrcDir . '/resources/tests/phpunit-output.txt',
            $this->manuscriptSrcDir . '/resources/vendor/symfony/event-dispatcher-contracts/EventDispatcherInterface.php',
        ];
        foreach ($generatedFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        // @TODO remove manuscriptDir
    }

    public function testItGeneratesTheManuscriptFolderBasedOnFilesReferencedInBookMdAndSubsetMd(): void
    {
        $tester = new CommandTester(new GenerateManuscriptCommand());
        $tester->execute(
            [
                '--manuscript-dir' => $this->manuscriptDir,
                '--manuscript-src-dir' => $this->manuscriptSrcDir,
                '--capitalize-headlines' => true,
            ]
        );

        self::assertDirectoryContentsEquals(__DIR__ . '/Project/manuscript-expected', $this->manuscriptDir);

        self::assertSame(0, $tester->getStatusCode());
    }

    public function testItFailsWhenUsingDryRunAndFilesWereModified(): void
    {
        $tester = new CommandTester(new GenerateManuscriptCommand());
        $tester->execute(
            [
                '--manuscript-dir' => $this->manuscriptDir,
                '--manuscript-src-dir' => $this->manuscriptSrcDir,
                '--dry-run' => true,
            ]
        );

        self::assertSame(1, $tester->getStatusCode());
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
