<?php

declare(strict_types=1);

use BookTools\Configuration\BookProjectConfiguration;
use BookTools\Configuration\LinkRegistryConfiguration;

$configuration = BookProjectConfiguration::usingDefaults();
$configuration->setLinkRegistryConfiguration(new LinkRegistryConfiguration('links.txt', 'https://booktools.com'));

return $configuration;
