<?php

declare(strict_types=1);

use ManuscriptGenerator\Configuration\BookProjectConfiguration;
use ManuscriptGenerator\Configuration\LinkRegistryConfiguration;

$configuration = BookProjectConfiguration::usingDefaults();
$configuration->setLinkRegistryConfiguration(
    new LinkRegistryConfiguration('links.txt', 'https://manuscriptGenerator.com')
);

return $configuration;
