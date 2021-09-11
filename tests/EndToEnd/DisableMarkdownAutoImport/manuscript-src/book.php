<?php

declare(strict_types=1);

use ManuscriptGenerator\Configuration\BookProjectConfiguration;

$configuration = BookProjectConfiguration::usingDefaults();

$configuration->setAutoImportMarkdownFiles(false);

return $configuration;
