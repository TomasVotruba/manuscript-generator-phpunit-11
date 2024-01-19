<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\FileOperations\Directory;
use ManuscriptGenerator\FileOperations\ExistingDirectory;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\Process\Process;

final readonly class DrawioResourceGenerator implements ResourceGenerator
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
        $tmpFile = $this->tmpDir->tmpFile('drawio', '.png');

        $process = new Process(
            [
                'drawio',
                '--export',
                '--format=png',
                '--scale=2',
                '--output',
                $tmpFile->pathname(),
                $source->existingFile()
                    ->pathname(),
            ],
            ExistingDirectory::currentWorkingDirectory()
        );
        $result = $process->run();

        if (! $result->isSuccessful()) {
            throw CouldNotGenerateResource::becauseAnExternalProcessWasUnsuccessful($result);
        }

        $generatedContents = $tmpFile->getContents();
        $tmpFile->unlink();

        return $generatedContents;
    }

    public function sourceLastModified(
        IncludedResource $resource,
        Source $source,
        DetermineLastModifiedTimestamp $determineLastModifiedTimestamp
    ): int {
        return $source->existingFile()
            ->lastModifiedTime();
    }
}
