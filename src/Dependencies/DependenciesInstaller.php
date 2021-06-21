<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Dependencies;

interface DependenciesInstaller
{
    public function install(): void;

    public function update(): void;
}
