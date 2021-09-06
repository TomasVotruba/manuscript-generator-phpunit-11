<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\FileOperations\Directory;
use ManuscriptGenerator\FileOperations\ExistingDirectory;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\Process\Process;

final class DrawioResourceGenerator implements ResourceGenerator
{
    public function __construct(
        private Directory $tmpDir
    ) {
    }

    public function name(): string
    {
        return 'drawio';
    }

    public function generateResource(IncludedResource $resource, Source $source): string
    {
        $tmpFilePathname = $this->tmpDir->create()
            ->toString() . '/' . uniqid('drawio') . '.drawio.png';

        $process = new Process(
            [
                'drawio',
                '--export',
                '--format=png',
                '--scale=2',
                '--output',
                $tmpFilePathname,
                $source->file()
                    ->pathname(),
            ],
            ExistingDirectory::currentWorkingDirectory()
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
        Source $source,
        DetermineLastModifiedTimestamp $determineLastModifiedTimestamp
    ): int {
        return $determineLastModifiedTimestamp->ofFile($source->file()->pathname());
    }
}
