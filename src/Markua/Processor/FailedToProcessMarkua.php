<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Processor;

use Parsica\Parsica\ParserHasFailed;
use RuntimeException;

final class FailedToProcessMarkua extends RuntimeException
{
    public static function becauseItCouldNotBeParsed(
        string $filePathname,
        string $markua,
        ParserHasFailed $exception
    ): self {
        return new self(
            sprintf(
                'Could not parse Markua file %s. Parse error: %s. Full contents: %s',
                $filePathname,
                $exception->getMessage(),
                $markua
            ),
            0,
            $exception
        );
    }
}
