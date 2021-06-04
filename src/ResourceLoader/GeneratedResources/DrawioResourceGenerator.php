<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use Symfony\Component\Process\Process;

final class DrawioResourceGenerator implements ResourceGenerator
{
    private const DRAWIO_PNG_SUFFIX = '.drawio.png';

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
        $tmpFilePathname = tempnam(sys_get_temp_dir(), 'drawio');
        assert(is_string($tmpFilePathname));

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
        $process->run();

        if (! $process->isSuccessful()) {
            throw CouldNotGenerateResource::becauseAnExternalProcessWasUnsuccessful($process);
        }

        $generatedContents = (string) file_get_contents($tmpFilePathname);
        unlink($tmpFilePathname);

        return $generatedContents;
    }
}
