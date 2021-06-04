<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceProcessor;

use Symfony\Component\Finder\Finder;

final class Psr4SrcNamespaceCollector
{
    public function __construct(
        private string $srcDirectory
    ) {
    }

    /**
     * @return string[]
     */
    public function collect(): array
    {
        if (! is_dir($this->srcDirectory)) {
            return [];
        }

        $directories = Finder::create()
            ->directories()
            ->sortByName(true)
            ->depth('< 1')
            ->in($this->srcDirectory);

        $result = [];

        $versionDirectories = Finder::create()
            ->directories()
            ->sortByName(true)
            ->depth('== 2')
            ->in($this->srcDirectory);

        foreach ($versionDirectories as $versionDirectory) {
            if (! str_contains($versionDirectory->getBasename(), 'Version')
            ) {
                continue;
            }

            $result[] = $this->toNamespacePrefix($versionDirectory->getRelativePathname());
        }

        foreach ($directories as $chapterDirectory) {
            $result[] = $this->toNamespacePrefix($chapterDirectory->getRelativePathname());
        }

        return $result;
    }

    private function toNamespacePrefix(string $pathname): string
    {
        return str_replace('/', '\\', $pathname) . '\\';
    }
}
