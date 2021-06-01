<?php

declare(strict_types=1);

use ManuscriptGenerator\Configuration\BookProjectConfiguration;

$configuration = BookProjectConfiguration::usingDefaults();
$configuration->setCapitalizeHeadings(true);

return $configuration;
