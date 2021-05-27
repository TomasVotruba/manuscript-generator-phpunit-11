<?php

declare(strict_types=1);

use BookTools\BookProjectConfiguration;

$configuration = BookProjectConfiguration::usingDefaults();
$configuration->setCapitalizeHeadings(true);

return $configuration;
