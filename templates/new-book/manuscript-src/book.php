<?php

declare(strict_types=1);

use ManuscriptGenerator\Configuration\BookProjectConfiguration;
use ManuscriptGenerator\Configuration\TitlePageConfiguration;

$configuration = BookProjectConfiguration::usingDefaults();
$configuration->setTitlePageConfiguration(TitlePageConfiguration::generate());

return $configuration;
