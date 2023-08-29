<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector;
use Rector\CodeQuality\Rector\For_\ForRepeatedCountToOwnVariableRector;
use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Rector\CodeQuality\Rector\If_\CombineIfRector;
use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector;
use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\CodingStyle\Rector\Closure\StaticClosureRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\CodingStyle\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector;
use Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\CodingStyle\Rector\String_\SymplifyQuoteEscapeRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;
use Rector\Php71\Rector\ClassConst\PublicConstantVisibilityRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector;

return static function (RectorConfig $rectorConfig): void {
    define('DOKU_INC', __DIR__ . '/../');

    $rectorConfig->paths([
        __DIR__ . '/../inc',
        __DIR__ . '/../lib',
    ]);

    $rectorConfig->bootstrapFiles([
        __DIR__ . '/../inc/load.php',
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
        __DIR__ . '/../lib/plugins/*/vendor/*',
        __DIR__ . '/../lib/tpl/*/vendor/*',
        __DIR__ . '/../lib/plugins/*/skel/*', // dev plugin

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
        PublicConstantVisibilityRector::class, // open for discussion
        EncapsedStringsToSprintfRector::class,
        CallableThisArrayToAnonymousFunctionRector::class,
        StaticClosureRector::class,
        SimplifyUselessVariableRector::class, // seems to strip constructor property initializations
        PostIncDecToPreIncDecRector::class,
        RemoveUselessParamTagRector::class,
        DisallowedEmptyRuleFixerRector::class,
        ForRepeatedCountToOwnVariableRector::class, // adds unwanted is_countable checks?
        RemoveParentCallWithoutParentRector::class,
        WrapEncapsedVariableInCurlyBracesRector::class,
        SimplifyIfReturnBoolRector::class,
    ]);
};
