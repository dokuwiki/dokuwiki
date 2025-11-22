<?php

declare(strict_types=1);

define('WIKI_INC', __DIR__ . '/../');

use easywiki\test\rector\EasyWikiPtlnRector;
use easywiki\test\rector\EasyWikiRenamePrintToEcho;
use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\CodeQuality\Rector\ClassMethod\ExplicitReturnNullRector;
use Rector\CodeQuality\Rector\Concat\JoinStringConcatRector;
use Rector\CodeQuality\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector;
use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Rector\CodeQuality\Rector\If_\CombineIfRector;
use Rector\CodeQuality\Rector\If_\CompleteMissingIfElseBracketRector;
use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector;
use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\CodingStyle\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector;
use Rector\CodingStyle\Rector\FuncCall\StrictArraySearchRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\CodingStyle\Rector\String_\SymplifyQuoteEscapeRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedConstructorParamRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector;
use Rector\DeadCode\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector;
use Rector\DeadCode\Rector\Property\RemoveUselessVarTagRector;
use Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;
use Rector\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector;
use Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;
use Rector\Php80\Rector\Identical\StrEndsWithRector;
use Rector\Php80\Rector\Identical\StrStartsWithRector;
use Rector\Php80\Rector\NotIdentical\StrContainsRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector;
use Rector\DeadCode\Rector\If_\ReduceAlwaysFalseIfOrRector;

return static function (RectorConfig $rectorConfig): void {
    // FIXME we may want to autoload these later
    require_once __DIR__ . '/rector/EasyWikiPtlnRector.php';
    require_once __DIR__ . '/rector/EasyWikiRenamePrintToEcho.php';

    // tune parallel task settings (see rectorphp/rector#8396)
    $rectorConfig->parallel(120, 16, 10);

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

    // supported minimum PHP version can be overridden by environment variable
    [$major, $minor] = explode('.', $_SERVER['RECTOR_MIN_PHP'] ?? '' ?: '7.4');
    $phpset = LevelSetList::class . '::UP_TO_PHP_' . $major . $minor;
    fwrite(STDERR, "Using PHP set $phpset\n");

    // define sets of rules
    $rectorConfig->sets([
        constant($phpset),
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::CODING_STYLE,
    ]);

    // future rules for which we have polyfills
    $rectorConfig->rule(StrContainsRector::class);
    $rectorConfig->rule(StrEndsWithRector::class);
    $rectorConfig->rule(StrStartsWithRector::class);

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
        CompleteMissingIfElseBracketRector::class, // keep one-line guardians
        SimplifyIfElseToTernaryRector::class,
        NewlineAfterStatementRector::class,
        CombineIfRector::class,
        ExplicitBoolCompareRector::class,
        IssetOnPropertyObjectToPropertyExistsRector::class, // maybe?
        SymplifyQuoteEscapeRector::class,
        CatchExceptionNameMatchingTypeRector::class,
        EncapsedStringsToSprintfRector::class,
        SimplifyUselessVariableRector::class, // seems to strip constructor property initializations
        DisallowedEmptyRuleFixerRector::class,
        RemoveParentCallWithoutParentRector::class,
        WrapEncapsedVariableInCurlyBracesRector::class,
        SimplifyIfReturnBoolRector::class,
        StrictArraySearchRector::class, // we cannot assume strict search is always wanted
        JoinStringConcatRector::class, // this does not count variables, so it creates overlong lines
        RemoveExtraParametersRector::class, // this actually broke code
        RemoveUnusedConstructorParamRector::class, // see rectorphp/rector#8580
        RemoveUnusedNonEmptyArrayBeforeForeachRector::class, // seems unreliable when checking on array keys
        RemoveAlwaysTrueIfConditionRector::class, // fails with if(defined(...)) constructs
        RemoveUnreachableStatementRector::class, // fails GOTO in authpdo -> should be rewritten with exceptions
        ReturnNeverTypeRector::class,
        RemoveUselessParamTagRector::class, // keep doc blocks
        RemoveUselessVarTagRector::class, // keep doc blocks
        RemoveUselessReturnTagRector::class, // keep doc blocks
        ExplicitReturnNullRector::class, // we sometimes return void or string intentionally
        UseIdenticalOverEqualWithSameTypeRector::class, // probably a good idea, maybe later
        ReduceAlwaysFalseIfOrRector::class, // see rectorphp/rector#8916

    ]);

    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // see inc/deprecated.php
        'RemoteAccessDeniedException' => 'easywiki\Remote\AccessDeniedException',
        'RemoteException' => 'easywiki\Remote\RemoteException',
        'setting' => 'easywiki\plugin\config\core\Setting\Setting',
        'setting_authtype' => 'easywiki\plugin\config\core\Setting\SettingAuthtype',
        'setting_string' => 'easywiki\plugin\config\core\Setting\SettingString',
        'PageChangelog' => 'easywiki\ChangeLog\PageChangeLog',
        'MediaChangelog' => 'easywiki\ChangeLog\MediaChangeLog',
        'Input' => 'easywiki\Input\Input',
        'PostInput' => 'easywiki\Input\Post',
        'GetInput' => 'easywiki\Input\Get',
        'ServerInput' => 'easywiki\Input\Server',
        'PassHash' => 'easywiki\PassHash',
        'HTTPClientException' => 'easywiki\HTTP\HTTPClientException',
        'HTTPClient' => 'easywiki\HTTP\HTTPClient',
        'DokuHTTPClient' => 'easywiki\HTTP\DokuHTTPClient',
        'Doku_Plugin_Controller' => 'easywiki\Extension\PluginController',
        'Doku_Indexer' => 'easywiki\Search\Indexer',
        'IXR_Client' => 'easywiki\Remote\IXR\Client',
        'IXR_ClientMulticall' => 'IXR\Client\ClientMulticall',
        'IXR_Server' => 'IXR\Server\Server',
        'IXR_IntrospectionServer' => 'IXR\Server\IntrospectionServer',
        'IXR_Request' => 'IXR\Request\Request',
        'IXR_Message' => 'R\Message\Message',
        'IXR_Error' => 'XR\Message\Error',
        'IXR_Date' => 'IXR\DataType\Date',
        'IXR_Base64' => 'IXR\DataType\Base64',
        'IXR_Value' => 'IXR\DataType\Value',

        // see inc/legacy.php
        'Doku_Event_Handler' => 'easywiki\Extension\EventHandler',
        'Doku_Event' => 'easywiki\Extension\Event',
        'EasyWiki_Action_Plugin' => 'easywiki\Extension\ActionPlugin',
        'EasyWiki_Admin_Plugin' => 'easywiki\Extension\AdminPlugin',
        'EasyWiki_Auth_Plugin' => 'easywiki\Extension\AuthPlugin',
        'EasyWiki_CLI_Plugin' => 'easywiki\Extension\CLIPlugin',
        'EasyWiki_Plugin' => 'easywiki\Extension\Plugin',
        'EasyWiki_Remote_Plugin' => 'easywiki\Extension\RemotePlugin',
        'EasyWiki_Syntax_Plugin' => 'easywiki\Extension\SyntaxPlugin',
    ]);

    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        // see inc/deprecated.php
        'Doku_Lexer_Escape' => 'easywiki\Parsing\Lexer\Lexer::escape',

        // see inc/utf8.php
        'utf8_isASCII' => 'easywiki\Utf8\Clean::isASCII',
        'utf8_strip' => 'easywiki\Utf8\Clean::strip',
        'utf8_check' => 'easywiki\Utf8\Clean::isUtf8',
        'utf8_basename' => 'easywiki\Utf8\PhpString::basename',
        'utf8_strlen' => 'easywiki\Utf8\PhpString::strlen',
        'utf8_substr' => 'easywiki\Utf8\PhpString::substr',
        'utf8_substr_replace' => 'easywiki\Utf8\PhpString::substr_replace',
        'utf8_ltrim' => 'easywiki\Utf8\PhpString::ltrim',
        'utf8_rtrim' => 'easywiki\Utf8\PhpString::rtrim',
        'utf8_trim' => 'easywiki\Utf8\PhpString::trim',
        'utf8_strtolower' => 'easywiki\Utf8\PhpString::strtolower',
        'utf8_strtoupper' => 'easywiki\Utf8\PhpString::strtoupper',
        'utf8_ucfirst' => 'easywiki\Utf8\PhpString::ucfirst',
        'utf8_ucwords' => 'easywiki\Utf8\PhpString::ucwords',
        'utf8_deaccent' => 'easywiki\Utf8\Clean::deaccent',
        'utf8_romanize' => 'easywiki\Utf8\Clean::romanize',
        'utf8_stripspecials' => 'easywiki\Utf8\Clean::stripspecials',
        'utf8_strpos' => 'easywiki\Utf8\PhpString::strpos',
        'utf8_tohtml' => 'easywiki\Utf8\Conversion::toHtml',
        'utf8_unhtml' => 'easywiki\Utf8\Conversion::fromHtml',
        'utf8_to_unicode' => 'easywiki\Utf8\Conversion::fromUtf8',
        'unicode_to_utf8' => 'easywiki\Utf8\Conversion::toUtf8',
        'utf8_to_utf16be' => 'easywiki\Utf8\Conversion::toUtf16be',
        'utf16be_to_utf8' => 'easywiki\Utf8\Conversion::fromUtf16be',
        'utf8_bad_replace' => 'easywiki\Utf8\Clean::replaceBadBytes',
        'utf8_correctIdx' => 'easywiki\Utf8\Clean::correctIdx',
    ]);

    $rectorConfig->rule(EasyWikiPtlnRector::class);
    $rectorConfig->rule(EasyWikiRenamePrintToEcho::class);
};
