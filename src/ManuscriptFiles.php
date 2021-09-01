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

    public function hasChangesComparedTo(self $other): bool
    {
        ksort($this->files);
        ksort($other->files);
        if (array_keys($this->files) !== array_keys($other->files)) {
            return true;
        }

        foreach ($this->files as $file => $contents) {
            if ($contents !== $other->files[$file]) {
                return true;
            }
        }

        return false;
    }
}
