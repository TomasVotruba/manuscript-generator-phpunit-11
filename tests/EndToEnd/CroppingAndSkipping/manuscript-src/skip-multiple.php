<?php

declare(strict_types=1);

// crop-start
final class Foo
{
    public function bar(): void
    {
        // first line

        // skip-start

        // will be skipped

        // skip-end

        // second line

        // skip-start

        // will also be skipped

        // skip-end

        // last line
    }
}
