<?php
// phpcs:ignoreFile -- this file violates PSR-12 by definition
/**
 * These classes and functions are deprecated and will be removed in future releases
 *
 * Note: when adding to this file, please also add appropriate actions to _test/rector.php
 */

use dokuwiki\Debug\DebugHelper;

/**
 * @deprecated since 2021-11-11 use \dokuwiki\Remote\IXR\Client instead!
 */
class IXR_Client extends \dokuwiki\Remote\IXR\Client
{
    /**
     * @inheritdoc
     * @deprecated 2021-11-11
     */
    public function __construct($server, $path = false, $port = 80, $timeout = 15, $timeout_io = null)
    {
        DebugHelper::dbgDeprecatedFunction(dokuwiki\Remote\IXR\Client::class);
        parent::__construct($server, $path, $port, $timeout, $timeout_io);
    }
}
/**
 * @deprecated since 2021-11-11 use \IXR\Client\ClientMulticall instead!
 */
class IXR_ClientMulticall extends \IXR\Client\ClientMulticall
{
    /**
     * @inheritdoc
     * @deprecated 2021-11-11
     */
    public function __construct($server, $path = false, $port = 80)
    {
        DebugHelper::dbgDeprecatedFunction(IXR\Client\ClientMulticall::class);
        parent::__construct($server, $path, $port);
    }
}
/**
 * @deprecated since 2021-11-11 use \IXR\Server\Server instead!
 */
class IXR_Server extends \IXR\Server\Server
{
    /**
     * @inheritdoc
     * @deprecated 2021-11-11
     */
    public function __construct($callbacks = false, $data = false, $wait = false)
    {
        DebugHelper::dbgDeprecatedFunction(IXR\Server\Server::class);
        parent::__construct($callbacks, $data, $wait);
    }
}
/**
 * @deprecated since 2021-11-11 use \IXR\Server\IntrospectionServer instead!
 */
class IXR_IntrospectionServer extends \IXR\Server\IntrospectionServer
{
    /**
     * @inheritdoc
     * @deprecated 2021-11-11
     */
    public function __construct()
    {
        DebugHelper::dbgDeprecatedFunction(IXR\Server\IntrospectionServer::class);
        parent::__construct();
    }
}
/**
 * @deprecated since 2021-11-11 use \IXR\Request\Request instead!
 */
class IXR_Request extends \IXR\Request\Request
{
    /**
     * @inheritdoc
     * @deprecated 2021-11-11
     */
    public function __construct($method, $args)
    {
        DebugHelper::dbgDeprecatedFunction(IXR\Request\Request::class);
        parent::__construct($method, $args);
    }
}
/**
 * @deprecated since 2021-11-11 use \IXR\Message\Message instead!
 */
class IXR_Message extends IXR\Message\Message
{
    /**
     * @inheritdoc
     * @deprecated 2021-11-11
     */
    public function __construct($message)
    {
        DebugHelper::dbgDeprecatedFunction(IXR\Message\Message::class);
        parent::__construct($message);
    }
}
/**
 * @deprecated since 2021-11-11 use \IXR\Message\Error instead!
 */
class IXR_Error extends \IXR\Message\Error
{
    /**
     * @inheritdoc
     * @deprecated 2021-11-11
     */
    public function __construct($code, $message)
    {
        DebugHelper::dbgDeprecatedFunction(IXR\Message\Error::class);
        parent::__construct($code, $message);
    }
}
/**
 * @deprecated since 2021-11-11 use \IXR\DataType\Date instead!
 */
class IXR_Date extends \IXR\DataType\Date
{
    /**
     * @inheritdoc
     * @deprecated 2021-11-11
     */
    public function __construct($time)
    {
        DebugHelper::dbgDeprecatedFunction(IXR\DataType\Date::class);
        parent::__construct($time);
    }
}
/**
 * @deprecated since 2021-11-11 use \IXR\DataType\Base64 instead!
 */
class IXR_Base64 extends \IXR\DataType\Base64
{
    /**
     * @inheritdoc
     * @deprecated 2021-11-11
     */
    public function __construct($data)
    {
        DebugHelper::dbgDeprecatedFunction(IXR\DataType\Base64::class);
        parent::__construct($data);
    }
}
/**
 * @deprecated since 2021-11-11 use \IXR\DataType\Value instead!
 */
class IXR_Value extends \IXR\DataType\Value
{
    /**
     * @inheritdoc
     * @deprecated 2021-11-11
     */
    public function __construct($data, $type = null)
    {
        DebugHelper::dbgDeprecatedFunction(IXR\DataType\Value::class);
        parent::__construct($data, $type);
    }
}

/**
 * print a newline terminated string
 *
 * You can give an indention as optional parameter
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @param string $string  line of text
 * @param int    $indent  number of spaces indention
 * @deprecated 2023-08-31 use echo instead
 */
function ptln($string, $indent = 0)
{
    DebugHelper::dbgDeprecatedFunction('echo');
    echo str_repeat(' ', $indent) . "$string\n";
}

/**
 * Adds/updates the search index for the given page
 *
 * Locking is handled internally.
 *
 * @param string        $page   name of the page to index
 * @param boolean       $verbose    print status messages
 * @param boolean       $force  force reindexing even when the index is up to date
 * @return string|boolean  the function completed successfully
 *
 * @deprecated 2026-04-07 use Indexer class instead
 */
function idx_addPage($page, $verbose = false, $force = false)
{
    DebugHelper::dbgDeprecatedFunction('dokuwiki\Search\Indexer::addPage()');
    try {
        (new dokuwiki\Search\Indexer())->addPage($page, $force);
        return true;
    } catch (\dokuwiki\Search\Exception\SearchException $e) {
        return false;
    }
}

/**
 * Create an instance of the indexer.
 *
 * @return dokuwiki\Search\Indexer
 *
 * @deprecated 2026-04-07 use dokuwiki\Search\Indexer directly
 */
function idx_get_indexer()
{
    DebugHelper::dbgDeprecatedFunction(dokuwiki\Search\Indexer::class);
    return new dokuwiki\Search\Indexer();
}

/**
 * Read the list of words in an index (if it exists).
 *
 * @param string $idx
 * @param string $suffix
 * @return array
 *
 * @deprecated 2026-04-07 use Index classes directly
 */
function idx_getIndex($idx, $suffix)
{
    DebugHelper::dbgDeprecatedFunction('Index classes');
    global $conf;
    $fn = $conf['indexdir'] . '/' . $idx . $suffix . '.idx';
    if (!file_exists($fn)) return [];
    return file($fn);
}

/**
 * Find tokens in the fulltext index
 *
 * @param array $words list of words to search for
 * @return array list of pages found
 *
 * @deprecated 2026-04-07 use CollectionSearch on PageFulltextCollection instead
 */
function idx_lookup(&$words)
{
    DebugHelper::dbgDeprecatedFunction(dokuwiki\Search\Collection\CollectionSearch::class);
    return (new dokuwiki\Search\Indexer())->lookup($words);
}

/**
 * Get the list of lengths indexed in the wiki.
 *
 * @return array
 *
 * @deprecated 2026-04-07 use PageFulltextCollection::getTokenIndexMaximum() instead
 */
function idx_listIndexLengths()
{
    DebugHelper::dbgDeprecatedFunction('PageFulltextCollection::getTokenIndexMaximum()');
    global $conf;
    $idx = [];
    $files = glob($conf['indexdir'] . '/i*.idx');
    if ($files) {
        foreach ($files as $file) {
            if (preg_match('/i(\d+)\.idx$/', $file, $match)) {
                $idx[] = (int)$match[1];
            }
        }
        sort($idx);
    }
    return $idx;
}

/**
 * Get the word lengths that have been indexed.
 *
 * @param array|int $filter
 * @return array
 *
 * @deprecated 2026-04-07 use PageFulltextCollection::getTokenIndexMaximum() instead
 */
function idx_indexLengths($filter)
{
    DebugHelper::dbgDeprecatedFunction('PageFulltextCollection::getTokenIndexMaximum()');
    global $conf;
    $idx = [];
    if (is_array($filter)) {
        $path = $conf['indexdir'] . "/i";
        foreach (array_keys($filter) as $key) {
            if (file_exists($path . $key . '.idx'))
                $idx[] = $key;
        }
    } else {
        $lengths = idx_listIndexLengths();
        foreach ($lengths as $length) {
            if ((int)$length >= (int)$filter)
                $idx[] = $length;
        }
    }
    return $idx;
}

/**
 * Execute a fulltext search
 *
 * @param string $query search query
 * @param array $highlight words to highlight
 * @param string|null $sort sorting order
 * @param int|string|null $after only show results after this date
 * @param int|string|null $before only show results before this date
 * @return array
 *
 * @deprecated 2026-04-07 use dokuwiki\Search\FulltextSearch::pageSearch() instead
 */
function ft_pageSearch($query, &$highlight, $sort = null, $after = null, $before = null)
{
    DebugHelper::dbgDeprecatedFunction(dokuwiki\Search\FulltextSearch::class . '::pageSearch()');
    return (new dokuwiki\Search\FulltextSearch())->pageSearch($query, $highlight, $sort, $after, $before);
}

/**
 * Returns the backlinks for a given page
 *
 * @param string $id page id
 * @param bool $ignore_perms
 * @return string[]
 *
 * @deprecated 2026-04-07 use dokuwiki\Search\MetadataSearch::backlinks() instead
 */
function ft_backlinks($id, $ignore_perms = false)
{
    DebugHelper::dbgDeprecatedFunction(dokuwiki\Search\MetadataSearch::class . '::backlinks()');
    return (new dokuwiki\Search\MetadataSearch())->backlinks($id, $ignore_perms);
}

/**
 * Returns the pages that use a given media file
 *
 * @param string $id media id
 * @param bool $ignore_perms
 * @return string[]
 *
 * @deprecated 2026-04-07 use dokuwiki\Search\MetadataSearch::mediause() instead
 */
function ft_mediause($id, $ignore_perms = false)
{
    DebugHelper::dbgDeprecatedFunction(dokuwiki\Search\MetadataSearch::class . '::mediause()');
    return (new dokuwiki\Search\MetadataSearch())->mediause($id, $ignore_perms);
}

/**
 * Quicksearch for pagenames
 *
 * @param string $id page id
 * @param bool $in_ns match namespace
 * @param bool $in_title search in title
 * @param int|string|null $after
 * @param int|string|null $before
 * @return string[]
 *
 * @deprecated 2026-04-07 use dokuwiki\Search\MetadataSearch::pageLookup() instead
 */
function ft_pageLookup($id, $in_ns = false, $in_title = false, $after = null, $before = null)
{
    DebugHelper::dbgDeprecatedFunction(dokuwiki\Search\MetadataSearch::class . '::pageLookup()');
    return (new dokuwiki\Search\MetadataSearch())->pageLookup($id, $in_ns, $in_title, $after, $before);
}

/**
 * Creates a snippet extract
 *
 * @param string $id page id
 * @param array $highlight words to highlight
 * @return string
 *
 * @deprecated 2026-04-07 use dokuwiki\Search\FulltextSearch::snippet() instead
 */
function ft_snippet($id, $highlight)
{
    DebugHelper::dbgDeprecatedFunction(dokuwiki\Search\FulltextSearch::class . '::snippet()');
    return (new dokuwiki\Search\FulltextSearch())->snippet($id, $highlight);
}

/**
 * Sort pages based on their namespace level first, then alphabetically
 *
 * @param string $a
 * @param string $b
 * @return int
 *
 * @deprecated 2026-04-07 use Utf8\Sort functions directly
 */
function ft_pagesorter($a, $b)
{
    DebugHelper::dbgDeprecatedFunction('Utf8\\Sort');
    $diff = substr_count($a, ':') - substr_count($b, ':');
    return $diff ?: dokuwiki\Utf8\Sort::strcmp($a, $b);
}

/**
 * Wrap a search term in regex boundary checks
 *
 * @param string $term
 * @return string
 *
 * @deprecated 2026-04-07 use dokuwiki\Search\FulltextSearch::snippetRePreprocess() instead
 */
function ft_snippet_re_preprocess($term)
{
    DebugHelper::dbgDeprecatedFunction(dokuwiki\Search\FulltextSearch::class . '::snippetRePreprocess()');
    return (new dokuwiki\Search\FulltextSearch())->snippetRePreprocess($term);
}

/**
 * Parse a search query into its components
 *
 * @param mixed $Indexer ignored (legacy parameter)
 * @param string $query search query
 * @return array parsed query structure
 *
 * @deprecated 2026-04-07 use dokuwiki\Search\Query\QueryParser::convert() instead
 */
function ft_queryParser($Indexer, $query)
{
    DebugHelper::dbgDeprecatedFunction(dokuwiki\Search\Query\QueryParser::class . '::convert()');
    return (new dokuwiki\Search\Query\QueryParser())->convert($query);
}
