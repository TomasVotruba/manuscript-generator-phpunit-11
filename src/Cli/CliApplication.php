<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Cli;

use Symfony\Component\Console\Application;

final class CliApplication extends Application
{
    public function __construct()
    {
        parent::__construct('Manuscript Generator', '1.0');

        $command = new GenerateManuscriptCommand();
        $this->add($command);
        $this->setDefaultCommand(GenerateManuscriptCommand::COMMAND_NAME, true);
    }
}