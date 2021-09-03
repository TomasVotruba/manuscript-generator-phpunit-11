<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ManuscriptFiles;

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
            fn (string $fileName): NewFile => new NewFile($fileName, $this->files[$fileName]),
            array_diff($fileNamesLeft, $fileNamesRight)
        );

        $modifiedFiles = array_map(
            fn (string $fileName): ModifiedFile => new ModifiedFile(
                $fileName,
                $other->files[$fileName],
                $this->files[$fileName]
            ),
            array_filter(
                array_intersect($fileNamesLeft, $fileNamesRight),
                fn (string $fileName): bool => $this->files[$fileName] !== $other->files[$fileName]
            )
        );

        $unusedFiles = array_map(
            fn (string $fileName): UnusedFile => new UnusedFile($fileName, $other->files[$fileName]),
            array_diff($fileNamesRight, $fileNamesLeft)
        );

        return new ManuscriptDiff(array_values($newFiles), array_values($modifiedFiles), array_values($unusedFiles));
    }
}
