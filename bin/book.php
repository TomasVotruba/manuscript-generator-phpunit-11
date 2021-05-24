<?php
declare(strict_types=1);

use BookTools\Cli\BookCliApplication;
use BookTools\Cli\GenerateManuscriptCommand;

require __DIR__ . '/../vendor/autoload.php';

$application = new BookCliApplication(
    [
        new GenerateManuscriptCommand()
    ]
);
exit($application->run());
