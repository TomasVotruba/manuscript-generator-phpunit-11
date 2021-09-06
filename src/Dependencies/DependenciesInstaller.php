<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Dependencies;

use ManuscriptGenerator\FileOperations\ExistingDirectory;

interface DependenciesInstaller
{
    public function install(ExistingDirectory $directory): void;

    public function updateAll(): void;
}
