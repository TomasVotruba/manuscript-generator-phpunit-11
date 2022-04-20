<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Cli;

use ManuscriptGenerator\Cli\Check\CheckSubprojectsCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Filesystem\Filesystem;

final class CliApplication extends Application
{
    public function __construct()
    {
        parent::__construct('Manuscript Generator', '1.0');

        $filesystem = new Filesystem();
        $initCommand = new InitCommand($filesystem);

        $this->addCommands([new GenerateManuscriptCommand(), $initCommand, new CheckSubprojectsCommand()]);
        $this->setDefaultCommand(GenerateManuscriptCommand::COMMAND_NAME);
    }
}
