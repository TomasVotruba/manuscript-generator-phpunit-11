<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\FileOperations\Directory;
use ManuscriptGenerator\FileOperations\ExistingDirectory;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\Process\Process;
use RuntimeException;

final class TitlePageResourceGenerator implements ResourceGenerator
{
    private const GIMP_SOURCE_FILE_NAME = 'title_page.xcf';

    public function __construct(
        private Directory $tmpDir
    ) {
    }

    public function name(): string
    {
        return 'title_page';
    }

    public function sourcePathForResource(IncludedResource $resource): string
    {
        return dirname($resource->expectedFilePathname()) . '/images/cover/' . self::GIMP_SOURCE_FILE_NAME;
    }

    public function generateResource(IncludedResource $resource, Source $source): string
    {
        $tmpFilePathname = $this->tmpDir->create()
            ->toString() . '/' . uniqid('title_page') . '.png';

        // Convert xcf to png
        $process = new Process([
            'xcf2png',
            $this->sourcePathForResource($resource),
            '-o',
            $tmpFilePathname,
        ], ExistingDirectory::currentWorkingDirectory());
        $result = $process->run();
        if (! $result->isSuccessful()) {
            throw new RuntimeException(
                sprintf(
                    "Process was not successful\nCommand line: %s\n\nOutput: \n\n%s",
                    $result->command(),
                    $result->standardAndErrorOutputCombined()
                )
            );
        }

        // Resize png
        $process = new Process([
            'magick',
            'convert',
            $tmpFilePathname,
            '-resize',
            '1050x',
            $tmpFilePathname,
        ], ExistingDirectory::currentWorkingDirectory());
        $result = $process->run();
        if (! $result->isSuccessful()) {
            throw new RuntimeException(
                sprintf(
                    "Process was not successful\nCommand line: %s\n\nOutput: \n\n%s",
                    $result->command(),
                    $result->standardAndErrorOutputCombined()
                )
            );
        }

        $output = (string) file_get_contents($tmpFilePathname);
        unlink($tmpFilePathname);

        return $output;
    }

    public function sourceLastModified(
        IncludedResource $resource,
        Source $source,
        DetermineLastModifiedTimestamp $determineLastModifiedTimestamp
    ): int {
        return $determineLastModifiedTimestamp->ofFile($this->sourcePathForResource($resource));
    }
}
