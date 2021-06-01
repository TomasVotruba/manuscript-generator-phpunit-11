<?php
declare(strict_types=1);

use ManuscriptGenerator\Cli\BookCliApplication;
use ManuscriptGenerator\Cli\GenerateManuscriptCommand;

$potentialAutoloadFiles = [
    __DIR__ . '/../vendor/autoload.php', // in this project
    __DIR__ . '/../autoload.php', // in a project where this library is installed
];
$autoloaderLoaded = false;

foreach ($potentialAutoloadFiles as $file) {
    if (file_exists($file)) {
        require $file;
        $autoloaderLoaded = true;
    }
}

if (!$autoloaderLoaded) {
    throw new RuntimeException('Could not find autoload.php');
}

$application = new BookCliApplication(
    [
        new GenerateManuscriptCommand()
    ]
);
exit($application->run());
