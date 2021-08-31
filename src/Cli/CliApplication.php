<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Cli;

use Symfony\Component\Console\Application;
use Symfony\Component\Filesystem\Filesystem;

final class CliApplication extends Application
{
    public function __construct()
    {
        parent::__construct('Manuscript Generator', '1.0');

        $filesystem = new Filesystem();
        $initCommand = new InitCommand($filesystem);

        $this->addCommands([new GenerateManuscriptCommand(), new ListSubprojectsCommand(), $initCommand]);
        $this->setDefaultCommand(GenerateManuscriptCommand::COMMAND_NAME);
    }
}
