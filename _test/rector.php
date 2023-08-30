<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector;
use Rector\CodeQuality\Rector\Concat\JoinStringConcatRector;
use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Rector\CodeQuality\Rector\If_\CombineIfRector;
use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector;
use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\CodingStyle\Rector\Class_\AddArrayDefaultToArrayPropertyRector;
use Rector\CodingStyle\Rector\Closure\StaticClosureRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\CodingStyle\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector;
use Rector\CodingStyle\Rector\FuncCall\StrictArraySearchRector;
use Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\CodingStyle\Rector\String_\SymplifyQuoteEscapeRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\For_\RemoveDeadContinueRector;
use Rector\DeadCode\Rector\For_\RemoveDeadIfForeachForRector;
use Rector\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector;
use Rector\DeadCode\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector;
use Rector\DeadCode\Rector\Property\RemoveUselessVarTagRector;
use Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;
use Rector\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector;
use Rector\Php71\Rector\ClassConst\PublicConstantVisibilityRector;
use Rector\Php71\Rector\FuncCall\CountOnNullRector;
use Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/../inc/',
        __DIR__ . '/../lib/',
        __DIR__ . '/../bin/',
        __DIR__ . '/../*.php',
    ]);

    $rectorConfig->bootstrapFiles([
        __DIR__ . '/../inc/init.php',
    ]);

    $rectorConfig->importNames();
    $rectorConfig->importShortClasses(false);
    $rectorConfig->cacheClass(FileCacheStorage::class);
    $rectorConfig->cacheDirectory(__DIR__ . '/.rector-cache');

    // define sets of rules
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_74,
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::CODING_STYLE,
    ]);

    $rectorConfig->skip([
        // skip paths
        __DIR__ . '/../inc/lang/*',
        __DIR__ . '/../lib/plugins/*/_test/*',
        __DIR__ . '/../lib/tpl/*/_test/*',
        __DIR__ . '/../lib/plugins/*/lang/*',
        __DIR__ . '/../lib/tpl/*/lang/*',
        __DIR__ . '/../lib/plugins/*/conf/*', // maybe later
        __DIR__ . '/../lib/tpl/*/conf/*',  // maybe later
        __DIR__ . '/../lib/plugins/*/vendor/*',
        __DIR__ . '/../lib/tpl/*/vendor/*',
        __DIR__ . '/../lib/plugins/*/skel/*', // dev plugin
        __DIR__ . '/../inc/deprecated.php',
        __DIR__ . '/../inc/form.php',

        // third party libs, not yet moved to composer
        __DIR__ . '/../inc/DifferenceEngine.php',
        __DIR__ . '/../inc/JpegMeta.php',
        __DIR__ . '/../lib/plugins/authad/adLDAP',

        // skip rules
        SimplifyIfElseToTernaryRector::class,
        NewlineAfterStatementRector::class,
        CombineIfRector::class,
        ExplicitBoolCompareRector::class,
        IssetOnPropertyObjectToPropertyExistsRector::class, // maybe?
        SymplifyQuoteEscapeRector::class,
        CatchExceptionNameMatchingTypeRector::class,
        EncapsedStringsToSprintfRector::class,
        CallableThisArrayToAnonymousFunctionRector::class,
        StaticClosureRector::class,
        SimplifyUselessVariableRector::class, // seems to strip constructor property initializations
        PostIncDecToPreIncDecRector::class,
        RemoveUselessParamTagRector::class,
        DisallowedEmptyRuleFixerRector::class,
        CountOnNullRector::class, // adds unwanted is_countable checks?
        RemoveParentCallWithoutParentRector::class,
        WrapEncapsedVariableInCurlyBracesRector::class,
        SimplifyIfReturnBoolRector::class,
        StrictArraySearchRector::class, // we cannot assume strict search is always wanted
        AddArrayDefaultToArrayPropertyRector::class, // may break code differentiating between null and empty array
        RemoveUselessVarTagRector::class,
        TypedPropertyFromAssignsRector::class, // maybe?
        JoinStringConcatRector::class, // this does not count variables, so it creates overlong lines
        RemoveExtraParametersRector::class, // this actually broke code
        RemoveUnusedNonEmptyArrayBeforeForeachRector::class, // seems unreliable when checking on array keys
        RemoveAlwaysTrueIfConditionRector::class, // fails with if(defined(...)) constructs
        RemoveUnreachableStatementRector::class, // fails GOTO in authpdo -> should be rewritten with exceptions
    ]);
};
