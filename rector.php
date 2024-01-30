<?php

declare(strict_types=1);
use Rector\Config\RectorConfig;
use Rector\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector;
use Rector\Php81\Rector\ClassMethod\NewInInitializerRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_82,
        SetList::DEAD_CODE,
        SetList::TYPE_DECLARATION,
        SetList::EARLY_RETURN,
    ]);

    $rectorConfig->paths([
        __DIR__ . '/bin',
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/ecs.php',
        __DIR__ . '/rector.php',
    ]);

    $rectorConfig->importNames();

    $rectorConfig->skip([
        ChangeAndIfToEarlyReturnRector::class,
        '*/tests/EndToEnd/*/*',
        NewInInitializerRector::class => [__DIR__ . '/src/Markua/Parser/Node'],
    ]);
};
