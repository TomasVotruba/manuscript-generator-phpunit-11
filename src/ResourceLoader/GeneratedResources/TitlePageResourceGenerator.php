<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\Process\Process;
use RuntimeException;

final class TitlePageResourceGenerator implements ResourceGenerator
{
    private const GIMP_SOURCE_FILE_NAME = 'title_page.xcf';

    private const PNG_TARGET_FILE = 'title_page.png';

    public function __construct(
        private string $tmpDir
    ) {
    }

    public function supportsResource(IncludedResource $resource): bool
    {
        return $resource->link === self::PNG_TARGET_FILE;
    }

    public function sourcePathForResource(IncludedResource $resource): string
    {
        return dirname($resource->expectedFilePathname()) . '/images/cover/' . self::GIMP_SOURCE_FILE_NAME;
    }

    public function generateResource(IncludedResource $resource): string
    {
        if (! is_dir($this->tmpDir)) {
            // @TODO introduce WritableDir "VO" for this
            mkdir($this->tmpDir, 0777, true);
        }

        $tmpFilePathname = $this->tmpDir . '/' . uniqid('title_page') . '.png';

        $workingDir = dirname($resource->expectedFilePathname());

        // Convert xcf to png
        $process = new Process([
            'xcf2png',
            $this->sourcePathForResource($resource),
            '-o',
            $tmpFilePathname,
        ], $workingDir);
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
        ], $workingDir);
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
}
