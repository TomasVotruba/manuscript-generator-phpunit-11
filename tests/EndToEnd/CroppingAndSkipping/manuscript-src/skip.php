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

        // last line
    }
}
