<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Processor;

use ManuscriptGenerator\FileOperations\ExistingFile;
use Parsica\Parsica\ParserHasFailed;
use RuntimeException;

final class FailedToLoadMarkuaFile extends RuntimeException
{
    public static function becauseItCouldNotBeParsed(ExistingFile $markuaFile, ParserHasFailed $exception): self
    {
        return new self(
            sprintf(
                'Could not parse Markua file %s. Parse error: %s. Full contents: %s',
                $markuaFile->pathname(),
                $exception->getMessage(),
                $markuaFile->getContents()
            ),
            0,
            $exception
        );
    }
}
