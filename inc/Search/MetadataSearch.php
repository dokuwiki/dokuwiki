<?php

namespace dokuwiki\Search;

use dokuwiki\Extension\Event;
use dokuwiki\Search\Collection\CollectionSearch;
use dokuwiki\Search\Collection\PageMetaCollection;
use dokuwiki\Search\Collection\PageTitleCollection;
use dokuwiki\Search\Exception\IndexUsageException;
use dokuwiki\Search\Query\QueryParser;
use dokuwiki\Utf8;

/**
 * Class DokuWiki Metadata Search
 *
 * Provides search operations on metadata indexes using the Collection/Index architecture.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */
class MetadataSearch
{
    /**
     * Quicksearch for pagenames
     *
     * By default it only matches the pagename and ignores the namespace.
     * This can be changed with the second parameter.
     * The third parameter allows to search in titles as well.
     *
     * The function always returns titles as well
     *
     * @triggers SEARCH_QUERY_PAGELOOKUP
     * @param string     $id       page id
     * @param bool $in_ns    match against namespace as well?
     * @param bool $in_title search in title?
     * @param int|string|null $after    only show results with mtime after this date,
     *                             accepts timestap or strtotime arguments
     * @param int|string|null $before   only show results with mtime before this date,
     *                             accepts timestap or strtotime arguments
     *
     * @return string[]
     * @author   Andreas Gohr <andi@splitbrain.org>
     * @author   Adrian Lang <lang@cosmocode.de>
     *
     */
    public function pageLookup(
        string     $id,
        bool       $in_ns = false,
        bool       $in_title = false,
        int|string|null $after = null,
        int|string|null $before = null): array
    {
        $data = [
            'id' => $id,
            'in_ns' => $in_ns,
            'in_title' => $in_title,
            'after' => $after,
            'before' => $before
        ];
        $data['has_titles'] = true; // for plugin backward compatibility check
        return Event::createAndTrigger('SEARCH_QUERY_PAGELOOKUP', $data, $this->pageLookupCallBack(...));
    }

    /**
     * Returns list of pages as array(pageid => First Heading)
     *
     * @param array $data event data
     * @return string[]
     * @throws IndexUsageException
     */
    public function pageLookupCallBack(array &$data): array
    {
        $parsedQuery = (new QueryParser)->convert($data['id']);
        $ns = $parsedQuery['ns'] ? cleanID($parsedQuery['ns'][0]) . ':' : null;
        $notns = $parsedQuery['notns'] ? cleanID($parsedQuery['notns'][0]) . ':' : null;
        $query = ($ns || $notns) ? implode(' ', $parsedQuery['highlight']) : $data['id'];
        $cleaned = cleanID($query);

        if ($cleaned === '') return [];

        // find pages matching by page name
        $pages = [];
        foreach ($this->getPages() as $page) {
            if ($ns && !str_starts_with($page, $ns)) continue;
            if ($notns && str_starts_with($page, $notns)) continue;

            $match = $data['in_ns'] ? $page : noNSorNS($page);
            if (str_contains($match, $cleaned)) {
                $pages[$page] = p_get_first_heading($page, METADATA_DONT_RENDER);
            }
        }

        // additionally find pages matching by title
        if ($data['in_title']) {
            foreach ($this->lookupKey('title', $query, static fn($search, $title) => stripos($title, $search) !== false) as $page) {
                if ($ns && !str_starts_with($page, $ns)) continue;
                if ($notns && str_starts_with($page, $notns)) continue;

                if (!isset($pages[$page])) {
                    $pages[$page] = p_get_first_heading($page, METADATA_DONT_RENDER);
                }
            }
        }

        $pages = static::filterPages($pages, false, $data['after'], $data['before']);
        uksort($pages, $this->pagesorter(...));
        return $pages;
    }

    /**
     * Return a list of all indexed pages, optionally limited to those that have a specific metadata key
     *
     * When a key is given, only pages that have any value stored for that metadata key are returned.
     * This does not filter by the metadata value itself.
     *
     * @param string|null $key metadata key name, or null for all pages
     * @return string[] list of page names
     */
    public function getPages(?string $key = null): array
    {
        if ($key === null) {
            return (new Indexer())->getAllPages();
        }

        if ($key === 'title') {
            return (new PageTitleCollection())->getEntitiesWithData();
        }

        return (new PageMetaCollection($key))->getEntitiesWithData();
    }

    /**
     * Find pages containing a metadata value
     *
     * The metadata values are compared as case-sensitive strings. Pass a
     * callback function that returns true or false to use a different
     * comparison function. The function will be called with the $value being
     * searched for as the first argument, and the word in the index as the
     * second argument. The function preg_match can be used directly if the
     * values are regexes.
     *
     * When $value is a string, the result is a flat list of matching page names.
     * When $value is an array, each value is searched independently and the result
     * is an associative array keyed by the search values, each containing a list
     * of matching page names.
     *
     * Without a callback, values support wildcard matching with * at the start
     * and/or end (e.g. '*foo', 'bar*', '*baz*').
     *
     * @param string $key name of the metadata key to look for
     * @param string|string[] $value search term or array of search terms
     * @param callable|null $func comparison function: fn($searchValue, $indexWord) => bool
     * @return array flat list of page names (scalar $value) or [value => [pageName, ...]] (array $value)
     *
     * @throws IndexUsageException
     * @author Michael Hamann <michael@content-space.de>
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    public function lookupKey(string $key, string|array &$value, ?callable $func = null): array
    {
        $isScalar = !is_array($value);
        $valueArray = $isScalar ? [$value] : $value;

        if ($key === 'title') {
            $collection = new PageTitleCollection();
        } else {
            $collection = new PageMetaCollection($key);
        }

        $result = (new CollectionSearch($collection))->lookup($valueArray, $func);

        return $isScalar ? $result[$value] : $result;
    }

    /**
     * Returns the backlinks for a given page
     *
     * @param string $id The id for which links shall be returned
     * @param bool $ignore_perms Ignore the fact that pages are hidden or read-protected
     * @return string[] The pages that contain links to the given page
     *
     * @throws IndexUsageException
     * @author     Andreas Gohr <andi@splitbrain.org>
     */
    public function backlinks(string $id, bool $ignore_perms = false): array
    {
        $result = $this->lookupKey('relation_references', $id);
        if (!count($result)) return $result;

        $result = array_flip($result);
        $result = static::filterPages($result, $ignore_perms);
        $result = array_keys($result);

        Utf8\Sort::sort($result);
        return $result;
    }

    /**
     * Returns the pages that use a given media file
     *
     * @param string $id           The media id to look for
     * @param bool   $ignore_perms Ignore hidden pages and acls (optional, default: false)
     * @return string[] A list of pages that use the given media file
     *
     * @author     Andreas Gohr <andi@splitbrain.org>
     */
    public function mediause(string $id, bool $ignore_perms = false): array
    {
        $result = $this->lookupKey('relation_media', $id);
        if (!count($result)) return $result;

        $result = array_flip($result);
        $result = static::filterPages($result, $ignore_perms);
        $result = array_keys($result);

        Utf8\Sort::sort($result);
        return $result;
    }

    /**
     * Filter a list of pages by visibility, existence, permissions, and time range
     *
     * @param array $pages pages to filter (keys are page IDs)
     * @param bool $ignorePerms skip visibility and ACL checks
     * @param int|string|null $after only keep pages modified after this date
     * @param int|string|null $before only keep pages modified before this date
     * @return array filtered pages
     */
    public static function filterPages(array $pages, bool $ignorePerms = false, $after = null, $before = null): array
    {
        if ($after) $after = is_int($after) ? $after : strtotime($after);
        if ($before) $before = is_int($before) ? $before : strtotime($before);

        return array_filter($pages, static function ($value, $id) use ($ignorePerms, $after, $before) {
            if (!$ignorePerms) {
                if (isHiddenPage($id) || auth_quickaclcheck($id) < AUTH_READ) {
                    return false;
                }
            }
            if (!page_exists($id, '', false)) {
                return false;
            }
            if ($after || $before) {
                $mTime = filemtime(wikiFN($id));
                if ($after && $after > $mTime) return false;
                if ($before && $before < $mTime) return false;
            }
            return true;
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Sort pages based on their namespace level first, then on their string
     * values. This makes higher hierarchy pages rank higher than lower hierarchy
     * pages.
     *
     * @param string $a
     * @param string $b
     * @return int Returns < 0 if $a is less than $b; > 0 if $a is greater than $b,
     *             and 0 if they are equal.
     */
    protected function pagesorter(string $a, string $b): int
    {
        $diff = substr_count($a, ':') - substr_count($b, ':');
        return $diff ?: Utf8\Sort::strcmp($a, $b);
    }
}
