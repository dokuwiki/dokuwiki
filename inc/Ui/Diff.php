<?php

namespace dokuwiki\Ui;

use dokuwiki\ChangeLog\ChangeLog;

/**
 * DokuWiki Diff Interface
 * parent class of PageDiff and MediaDiff
 *
 * @package dokuwiki\Ui
 */
abstract class Diff extends Ui
{
    /* @var string */
    protected $id;   // page id or media id

    /* @var int */
    protected $oldRev;  // timestamp of older revision
    protected $newRev;  // timestamp of newer revision

    /* @var array */
    protected $preference = [];

    /* @var ChangeLog */
    protected $changelog; // PageChangeLog or MediaChangeLog object

    /**
     * Diff Ui constructor
     *
     * @param string $id  page id or media id
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
     * Prepare revision info of comparison pair
     */
    abstract protected function preProcess();

    /**
     * Set a pair of revisions to be compared
     *
     * @param int $oldRev
     * @param int $newRev
     * @return $this
     */
    public function compare($oldRev, $newRev)
    {
        if ($oldRev < $newRev) {
            [$this->oldRev, $this->newRev] = [$oldRev, $newRev];
        } else {
            [$this->oldRev, $this->newRev] = [$newRev, $oldRev];
        }
        return $this;
    }

    /**
     * Gets or Sets preference of the Ui\Diff object
     *
     * @param string|array $prefs  a key name or key-value pair(s)
     * @param mixed $value         value used when the first args is string
     * @return array|$this
     */
    public function preference($prefs = null, $value = null)
    {
        // set
        if (is_string($prefs) && isset($value)) {
            $this->preference[$prefs] = $value;
            return $this;
        } elseif (is_array($prefs)) {
            foreach ($prefs as $name => $value) {
                $this->preference[$name] = $value;
            }
            return $this;
        }
        // get
        return $this->preference;
    }

    /**
     * Handle requested revision(s)
     *
     * @return void
     */
    protected function handle()
    {
        global $INPUT;

        // difflink icon click, eg. ?rev=123456789&do=diff
        if ($INPUT->has('rev')) {
            $this->oldRev = $INPUT->int('rev');
            $this->newRev = $this->changelog->currentRevision();
        }

        // submit button with two checked boxes
        $rev2 = $INPUT->arr('rev2', []);
        if (count($rev2) > 1) {
            if ($rev2[0] < $rev2[1]) {
                [$this->oldRev, $this->newRev] = [$rev2[0], $rev2[1]];
            } else {
                [$this->oldRev, $this->newRev] = [$rev2[1], $rev2[0]];
            }
        }

        if (!isset($this->oldRev, $this->newRev)) {
            // no revision was given, compare previous to current
            // Note: need to chack validity of each revision numbers later
            $this->newRev = $this->changelog->currentRevision();
            $revs = $this->changelog->getRevisions(-1, 2);
            if (count($revs) < 2) {
                // impossible compare revision pair, both false when page or media not exists
                $this->oldRev = $this->newRev;
            } else {
                $this->oldRev = ($revs[0] == $this->newRev) ? $revs[1] : $revs[0];
            }
        }
    }




    /**
     * Build header of diff HTML
     *
     * @param string $l_rev   Left revisions
     * @param string $r_rev   Right revision
     * @return string[] HTML snippets for diff header
     * @deprecated 2020-12-31
     */
    public function buildDiffHead($l_rev, $r_rev)
    {
        dbg_deprecated('not used see '. \dokuwiki\Ui\PageDiff::class .'::show()');
    }

}
