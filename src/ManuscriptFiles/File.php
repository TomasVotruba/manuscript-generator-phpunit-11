<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ManuscriptFiles;

interface File
{
    public function filePathname(): string;

    public function oldContents(): string;

    public function newContents(): string;
}
