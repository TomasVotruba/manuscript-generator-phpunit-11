<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use RuntimeException;

final class DetermineLastModifiedTimestamp
{
    public function ofFile(string $filePathname): int
    {
        if (! is_file($filePathname)) {
            return 0;
        }

        return (int) filemtime($filePathname);
    }

    public function ofDirectory(string $directory): int
    {
        if (! is_dir($directory)) {
            return 0;
        }

        $files = $this->relevantFilesIn($directory);
        if ($files === []) {
            return 0;
        }

        $lastModifiedTimes = array_map(fn (string $pathname) => (int) filemtime($pathname), $files);
        arsort($lastModifiedTimes);

        return $lastModifiedTimes[array_key_first($lastModifiedTimes)];
    }

    /**
     * @return array<string>
     */
    private function relevantFilesIn(string $directory): array
    {
        $files = [];

        $dh = opendir($directory);
        if ($dh === false) {
            throw new RuntimeException('Could not open directory ' . $directory . ' for reading');
        }

        while (($filename = readdir($dh)) !== false) {
            if ($filename === '.') {
                continue;
            }
            if ($filename === '..') {
                continue;
            }
            // @TODO get from DependenciesInstaller
            $ignoreFileNames = ['vendor'];
            if (in_array($filename, $ignoreFileNames, true)) {
                continue;
            }

            $pathname = $directory . '/' . $filename;
            if (is_dir($pathname)) {
                $files = array_merge($files, $this->relevantFilesIn($pathname));
            } else {
                $files[] = $pathname;
            }
        }

        closedir($dh);

        return $files;
    }
}
