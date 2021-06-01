<?php
declare(strict_types=1);

use ManuscriptGenerator\Cli\BookCliApplication;
use ManuscriptGenerator\Cli\GenerateManuscriptCommand;

require __DIR__ . '/../vendor/autoload.php';

$application = new BookCliApplication(
    [
        new GenerateManuscriptCommand()
    ]
);
exit($application->run());
