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
    public function __construct(
        private Directory $tmpDir
    ) {
    }

    public function name(): string
    {
        return 'title_page';
    }

    public function generateResource(IncludedResource $resource, Source $source): string
    {
        $tmpFile = $this->tmpDir->tmpFile('title_page', '.png');

        // Convert xcf to png
        $process = new Process([
            'xcf2png',
            $source->existingFile()
                ->pathname(),
            '-o',
            $tmpFile->pathname(),
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
            $tmpFile->pathname(),
            '-resize',
            '1050x',
            $tmpFile->pathname(),
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

        $output = $tmpFile->getContents();
        $tmpFile->unlink();

        return $output;
    }

    public function sourceLastModified(
        IncludedResource $resource,
        Source $source,
        DetermineLastModifiedTimestamp $determineLastModifiedTimestamp
    ): int {
        if (! $source->file()->exists()) {
            return 0;
        }

        return $source->file()
            ->existing()
            ->lastModifiedTime();
    }
}
