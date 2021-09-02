<?php

declare(strict_types=1);

namespace ManuscriptGenerator;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

final class ManuscriptFiles
{
    /**
     * @param array<string,string> $files
     */
    private function __construct(
        private array $files
    ) {
    }

    public static function createEmpty(): self
    {
        return new self([]);
    }

    public static function fromDir(string $directory): self
    {
        $finder = Finder::create()->files()->in($directory);
        $files = [];
        foreach ($finder as $file) {
            $files[$file->getRelativePathname()] = $file->getContents();
        }
        ksort($files);

        return new self($files);
    }

    public function addFile(string $relativePathname, string $contents): void
    {
        $this->files[$relativePathname] = $contents;
    }

    public function dumpTo(string $targetDir): void
    {
        // @TODO use our own Filesystem for this stuff?
        $filesystem = new Filesystem();
        $filesystem->remove($targetDir);
        $filesystem->mkdir($targetDir);

        foreach ($this->files as $relativePathname => $contents) {
            $filePath = $targetDir . '/' . dirname($relativePathname);
            if (! is_dir($filePath)) {
                $filesystem->mkdir($filePath);
            }

            file_put_contents($targetDir . '/' . $relativePathname, $contents);
        }
    }

    public function diff(self $other): ManuscriptDiff
    {
        ksort($this->files);
        ksort($other->files);

        $fileNamesLeft = array_keys($this->files);
        $fileNamesRight = array_keys($other->files);

        $newFiles = array_map(
            fn (string $fileName) => new File($fileName, $this->files[$fileName]),
            array_diff($fileNamesLeft, $fileNamesRight)
        );

        $modifiedFiles = array_map(
            fn (string $fileName) => new ModifiedFile($fileName, $this->files[$fileName], $other->files[$fileName]),
            array_intersect($fileNamesLeft, $fileNamesRight)
        );

        $unusedFiles = array_map(
            fn (string $fileName) => new File($fileName, $other->files[$fileName]),
            array_diff($fileNamesRight, $fileNamesLeft)
        );

        return new ManuscriptDiff($newFiles, $modifiedFiles, $unusedFiles);
    }
}
