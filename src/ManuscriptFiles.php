<?php
declare(strict_types=1);

namespace ManuscriptGenerator;

use Symfony\Component\Filesystem\Filesystem;

final class ManuscriptFiles
{
    /**
     * @var array<string,string>
     */
    private array $files = [];

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
            $filePath = $targetDir . dirname($relativePathname);
            if (!is_dir($filePath)) {
                $filesystem->mkdir($filePath);
            }

            file_put_contents($targetDir . '/' . $relativePathname, $contents);
        }
    }
}
