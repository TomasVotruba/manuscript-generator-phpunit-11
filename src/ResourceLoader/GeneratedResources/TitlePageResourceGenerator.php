<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\FileOperations\Directory;
use ManuscriptGenerator\FileOperations\ExistingDirectory;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\Process\Process;
use RuntimeException;
use Symfony\Component\Process\Process as SymfonyProcess;

final readonly class TitlePageResourceGenerator implements ResourceGenerator
{
    public const MAGICK_CONVERT_COMMAND = 'convert';

    public const XCF_2_PNG_COMMAND = 'xcf2png';

    public function __construct(
        private Directory $tmpDir
    ) {
    }

    public function name(): string
    {
        return 'title_page';
    }

    public static function checkDependencies(): void
    {
        foreach ([self::MAGICK_CONVERT_COMMAND, self::XCF_2_PNG_COMMAND] as $processName) {
            $process = new SymfonyProcess(['which', $processName]);
            $process->run();

            if (! $process->isSuccessful()) {
                throw MissingDependency::process($processName);
            }
        }
    }

    public function generateResource(IncludedResource $resource, Source $source): string
    {
        $tmpFile = $this->tmpDir->tmpFile('title_page', '.png');

        // Convert xcf to png
        $process = new Process([
            self::XCF_2_PNG_COMMAND,
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
            self::MAGICK_CONVERT_COMMAND,
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
