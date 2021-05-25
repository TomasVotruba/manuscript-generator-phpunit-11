<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\PhpUnit\PhpUnitStrictFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests', __DIR__ . '/ecs.php']);

    $parameters->set(Option::CACHE_DIRECTORY, getcwd() . '/cache/ecs');

    $containerConfigurator->import(SetList::CONTROL_STRUCTURES);
    $containerConfigurator->import(SetList::PSR_12);
    $containerConfigurator->import(SetList::COMMON);
    $containerConfigurator->import(SetList::SYMPLIFY);

    $services->set(LineLengthFixer::class)
        ->call(
            'configure',
            [
                [
                    // to keep the code snippets visible on the book page without line-breaking
                    LineLengthFixer::LINE_LENGTH => 120,
                ],
            ]
        );

    $parameters->set(
        Option::SKIP,
        [
            // Because it makes no sense ;) (well, I just need assertEquals())
            PhpUnitStrictFixer::class,
        ]
    );
};
