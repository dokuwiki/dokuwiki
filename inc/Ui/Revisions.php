<?php

namespace dokuwiki\Ui;

use dokuwiki\ChangeLog\ChangeLog;

/**
 * DokuWiki Revisions Interface
 * parent class of PageRevisions and MediaRevisions
 *
 * Note: navigation starts from -1, not 0. This is because our Revision management starts old revisions at 0 and
 * will return the current revision only if the revisions starting at -1 are requested.
 *
 * @package dokuwiki\Ui
 */
abstract class Revisions extends Ui
{
    /* @var string */
    protected $id;   // page id or media id

    /* @var ChangeLog */
    protected $changelog; // PageChangeLog or MediaChangeLog object

    /**
     * Revisions Ui constructor
     *
     * @param string $id page id or media id
     */
    public function __construct($id)
    {
        $this->id = $id;
        $this->setChangeLog();
    }

    /**
     * set class property changelog
     */
    abstract protected function setChangeLog();

    /**
     * Get revisions, and set correct pagination parameters (first, hasNext)
     *
     * @param int $first
     * @param bool $hasNext
     * @return array  revisions to be shown in a paginated list
     * @see also https://www.dokuwiki.org/devel:changelog
     */
    protected function getRevisions(&$first, &$hasNext)
    {
        global $conf;

        $changelog =& $this->changelog;
        $revisions = [];

        $currentRevInfo = $changelog->getCurrentRevisionInfo();
        if (!$currentRevInfo) return $revisions;

        $num = $conf['recent'];

        /* we need to get one additional log entry to be able to
         * decide if this is the last page or is there another one.
         * see also Ui\Recent::getRecents()
         */
        $revlist = $changelog->getRevisions($first, $num + 1);
        if (count($revlist) == 0 && $first > -1) {
            // resets to zero if $first requested a too high number
            $first = -1;
            return $this->getRevisions($first, $hasNext);
        }

        // decide if this is the last page or is there another one
        $hasNext = false;
        if (count($revlist) > $num) {
            $hasNext = true;
            array_pop($revlist); // remove one additional log entry
        }

        // append each revision info array to the revisions
        foreach ($revlist as $rev) {
            $revisions[] = $changelog->getRevisionInfo($rev);
        }
        return $revisions;
    }

    /**
     * Navigation buttons for Pagination (prev/next)
     *
     * @param int $first
     * @param bool $hasNext
     * @param callable $callback returns array of hidden fields for the form button
     * @return string html
     */
    protected function navigation($first, $hasNext, $callback)
    {
        global $conf;

        $html = '<div class="pagenav">';
        $last = $first + $conf['recent'];
        if ($first > -1) {
            $first = max($first - $conf['recent'], -1);
            $html .= '<div class="pagenav-prev">';
            $html .= html_btn('newer', $this->id, "p", $callback($first));
            $html .= '</div>';
        }
        if ($hasNext) {
            $html .= '<div class="pagenav-next">';
            $html .= html_btn('older', $this->id, "n", $callback($last));
            $html .= '</div>';
        }
        $html .= '</div>';
        return $html;
    }
}
