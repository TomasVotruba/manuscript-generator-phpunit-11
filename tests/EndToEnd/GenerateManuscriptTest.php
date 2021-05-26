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
        $commandOutput = $this->tester->getDisplay();

        $filesystem = new Filesystem();

        // @TODO fragile solution, maybe register an optional listener or something
        $result = preg_match_all("/created (.+)\n/", $commandOutput, $matches);
        assert($result !== false);
        $filesystem->remove(array_map(fn (string $file) => getcwd() . $file, $matches[1]));
        $filesystem->remove($this->generatedManuscriptDir);
    }

    public function testItGeneratesTheManuscriptFolderBasedOnFilesReferencedInBookMdAndSubsetMd(): void
    {
        $this->tester->execute(
            [
                '--manuscript-dir' => $this->generatedManuscriptDir,
                '--manuscript-src-dir' => __DIR__ . '/Project/manuscript-src',
                '--capitalize-headlines' => true,
            ]
        );

        self::assertDirectoryContentsEquals(__DIR__ . '/Project/manuscript-expected', $this->generatedManuscriptDir);

        self::assertSame(0, $this->tester->getStatusCode());
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
