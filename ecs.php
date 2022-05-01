<?php

declare(strict_types=1);

use PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\AssignmentInConditionSniff;
use PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitStrictFixer;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([__DIR__ . '/src', __DIR__ . '/tests', __DIR__ . '/ecs.php']);

    $ecsConfig->sets([SetList::CONTROL_STRUCTURES, SetList::PSR_12, SetList::COMMON, SetList::SYMPLIFY]);

    $ecsConfig->ruleWithConfiguration(LineLengthFixer::class, [
        // to keep the code snippets visible on the book page without line-breaking
        LineLengthFixer::LINE_LENGTH => 120,
    ]);

    $ecsConfig->skip([
        PhpUnitStrictFixer::class,
        // Because it makes no sense ;) (well, I just need assertEquals())
        // fixture files
        '*/tests/EndToEnd/*/*',

        // some WTF in new php_code_sniffer
        AssignmentInConditionSniff::class . '.FoundInWhileCondition',

        // allow @throws
        GeneralPhpdocAnnotationRemoveFixer::class,
    ]);
};
