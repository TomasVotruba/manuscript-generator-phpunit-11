<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Dependencies;

interface DependenciesInstaller
{
    public function install(string $directory): void;

    public function updateAll(): void;
}
