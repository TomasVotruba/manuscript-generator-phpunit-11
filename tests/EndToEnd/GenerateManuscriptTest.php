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
    public function testItGeneratesTheManuscriptFolderBasedOnFilesReferencedInBookMdAndSubsetMd(): void
    {
        $generatedManuscriptDir = $this->randomGeneratedManuscriptDir();
        $tester = $this->generateManuscript(
            __DIR__ . '/Project/manuscript-src',
            $generatedManuscriptDir,
            __DIR__ . '/Project/manuscript-expected'
        );

        $this->cleanUp($tester, $generatedManuscriptDir);

        self::assertSame(0, $tester->getStatusCode());
    }

    public function testItFailsWhenUsingDryRunAndFilesWereModified(): void
    {
        $tester = new CommandTester(new GenerateManuscriptCommand());
        $generatedManuscriptDir = $this->randomGeneratedManuscriptDir();

        $tester->execute(
            [
                '--manuscript-dir' => $generatedManuscriptDir,
                '--manuscript-src-dir' => __DIR__ . '/Project/manuscript-src',
                '--dry-run' => true,
            ]
        );

        $this->cleanUp($tester, $generatedManuscriptDir);

        self::assertSame(1, $tester->getStatusCode());
    }

    protected function randomGeneratedManuscriptDir(): string
    {
        return sys_get_temp_dir() . '/' . uniqid('manuscript');
    }

    private function generateManuscript(
        string $manuscriptSrcDir,
        string $manuscriptTargetDir,
        string $expectedManuscriptDir
    ): CommandTester
    {
        $tester = new CommandTester(new GenerateManuscriptCommand());
        $tester->execute(
            [
                '--manuscript-dir' => $manuscriptTargetDir,
                '--manuscript-src-dir' => $manuscriptSrcDir,
                '--capitalize-headlines' => true,
            ]
        );

        self::assertDirectoryContentsEquals($expectedManuscriptDir, $manuscriptTargetDir);

        return $tester;
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

    private function cleanUp(CommandTester $tester, string $generatedManuscriptDir): void
    {
        $commandOutput = $tester->getDisplay();

        $filesystem = new Filesystem();

        // @TODO fragile solution, maybe register an optional listener or something
        $result = preg_match_all("/created (.+)\n/", $commandOutput, $matches);
        assert($result !== false);
        $filesystem->remove(array_map(fn (string $file) => getcwd() . $file, $matches[1]));
        $filesystem->remove($generatedManuscriptDir);
    }
}
