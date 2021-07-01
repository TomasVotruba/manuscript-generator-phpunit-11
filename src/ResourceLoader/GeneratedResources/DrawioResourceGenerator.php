<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\Process\Process;

final class DrawioResourceGenerator implements ResourceGenerator
{
    private const DRAWIO_PNG_SUFFIX = '.drawio.png';

    public function __construct(
        private string $tmpDir
    ) {
    }

    public function supportsResource(IncludedResource $resource): bool
    {
        return str_ends_with($resource->expectedFilePathname(), self::DRAWIO_PNG_SUFFIX);
    }

    public function sourcePathForResource(IncludedResource $resource): string
    {
        return str_replace(self::DRAWIO_PNG_SUFFIX, '.drawio', $resource->expectedFilePathname());
    }

    public function generateResource(IncludedResource $resource): string
    {
        if (! is_dir($this->tmpDir)) {
            // @TODO introduce WritableDir "VO" for this
            mkdir($this->tmpDir, 0777, true);
        }

        $tmpFilePathname = $this->tmpDir . '/' . uniqid('drawio') . '.drawio.png';

        $process = new Process(
            [
                'drawio',
                '--export',
                '--format=png',
                '--scale=2',
                '--output',
                $tmpFilePathname,
                $this->sourcePathForResource($resource),
            ]
        );
        $result = $process->run();

        if (! $result->isSuccessful()) {
            throw CouldNotGenerateResource::becauseAnExternalProcessWasUnsuccessful($result);
        }

        $generatedContents = (string) file_get_contents($tmpFilePathname);
        unlink($tmpFilePathname);

        return $generatedContents;
    }

    public function sourceLastModified(
        IncludedResource $resource,
        DetermineLastModifiedTimestamp $determineLastModifiedTimestamp
    ): int {
        return $determineLastModifiedTimestamp->ofFile($this->sourcePathForResource($resource));
    }
}
