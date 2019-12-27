<?php
namespace dokuwiki\Search;

use dokuwiki\Search\Indexer;

/**
 * Class DokuWiki Metadata Search
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */
class MetaSearch
{
    /**
     *  Metadata Search constructor. prevent direct object creation
     */
    protected function __construct() {}

    /**
     * Returns the backlinks for a given page
     *
     * Uses the metadata index.
     *
     * @param string $id           The id for which links shall be returned
     * @param bool   $ignore_perms Ignore the fact that pages are hidden or read-protected
     * @return array The pages that contain links to the given page
     */
    public static function backlinks($id, $ignore_perms = false)
    {
        $Indexer = Indexer::getInstance();
        $result = $Indexer->lookupKey('relation_references', $id);

        if (!count($result)) return $result;

        // check ACL permissions
        foreach (array_keys($result) as $idx) {
            if (($ignore_perms !== true
                && (isHiddenPage($result[$idx]) || auth_quickaclcheck($result[$idx]) < AUTH_READ)
                ) || !page_exists($result[$idx], '', false)
            ) {
                unset($result[$idx]);
            }
        }

        sort($result);
        return $result;
    }

    /**
     * Returns the pages that use a given media file
     *
     * Uses the relation media metadata property and the metadata index.
     *
     * Note that before 2013-07-31 the second parameter was the maximum number
     * of results and permissions were ignored. That's why the parameter is now
     * checked to be explicitely set to true (with type bool) in order to be
     * compatible with older uses of the function.
     *
     * @param string $id           The media id to look for
     * @param bool   $ignore_perms Ignore hidden pages and acls (optional, default: false)
     * @return array A list of pages that use the given media file
     */
    public static function mediause($id, $ignore_perms = false)
    {
        $Indexer = Indexer::getInstance();
        $result = $Indexer->lookupKey('relation_media', $id);

        if (!count($result)) return $result;

        // check ACL permissions
        foreach (array_keys($result) as $idx) {
            if (($ignore_perms !== true
                && (isHiddenPage($result[$idx]) || auth_quickaclcheck($result[$idx]) < AUTH_READ)
                ) || !page_exists($result[$idx], '', false)
            ) {
                unset($result[$idx]);
            }
        }

        sort($result);
        return $result;
    }
}
