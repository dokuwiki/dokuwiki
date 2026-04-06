<?php

namespace dokuwiki\Search\Collection;

use dokuwiki\Search\Index\AbstractIndex;
use dokuwiki\Utf8;

/**
 * Collection for arbitrary page metadata
 *
 * A lookup collection where each token appears at most once per page.
 * Initialized with a subject string (e.g. 'relation_references', 'relation_media')
 * to derive index file names dynamically.
 *
 * Replaces the separate ReferencesCollection and MediaCollection classes and
 * handles arbitrary plugin metadata keys.
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Andreas Gohr <andi@splitbrain.org>
 */
class PageMetaCollection extends LookupCollection
{
    /** @inheritdoc */
    public function __construct(string $subject, ?AbstractIndex $pageIndex = null)
    {
        $clean = self::cleanName($subject);
        parent::__construct(
            $pageIndex ?? 'page',
            $clean . '_w',
            $clean . '_i',
            $clean . '_p'
        );
    }

    /**
     * Clean a name for use as a file name
     *
     * Romanizes non-latin characters, then strips away anything that's
     * not a letter, number, or underscore.
     *
     * @param string $name
     * @return string
     */
    public static function cleanName(string $name): string
    {
        $name = Utf8\Clean::romanize(trim($name));
        $name = preg_replace('#[ \./\\:-]+#', '_', $name);
        $name = preg_replace('/[^A-Za-z0-9_]/', '', $name);
        return strtolower($name);
    }
}
