<?php

namespace dokuwiki\Ui;

use dokuwiki\ChangeLog\ChangeLog;

/**
 * DokuWiki Diff
 * parent class of PageDiff and MediaDiff
 *
 * @package dokuwiki\Ui
 */
abstract class Diff extends Ui
{
    /* @var string */
    protected $id;   // page id or media id

    /* @var int[] timestamps of older [0] and newer [1] revisions */
    protected $revisions;

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
     * @param int $rev1 older revision
     * @param int $rev2 newer revision
     * @return $this
     */
    public function compare($rev1, $rev2)
    {
        if ($rev2 < $rev1) [$rev1, $rev2] = [$rev2, $rev1];
        // set correct newer revision when it is non-actual revision
        $this->revisions = array(
            0 => $rev1,
            1 => $this->changelog->traceExternalRevision($rev2)
        );
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
        $changelog =& $this->changelog;

        // difflink icon click, eg. ?rev=123456789&do=diff
        if ($INPUT->has('rev')) {
            // compare given revision with current
            $rev1 = $INPUT->int('rev');
            $rev2 = $changelog->currentRevision();
            if ($rev1 < $rev2) {
                $this->revisions = array(0 => $rev1, 1 => $rev2);
            } else {
                // fallback to compare previous with current revision
                unset($rev1, $rev2);
            }
        }

        // submit button with two checked boxes
        $revs = $INPUT->arr('rev2', []);
        if (count($revs) > 1) {
            list($rev1, $rev2) = $revs;
            if ($rev2 < $rev1) [$rev1, $rev2] = [$rev2, $rev1];
            // set correct newer revision when it is non-actual revision
            $this->revisions = array(
                0 => $rev1,
                1 => $changelog->traceExternalRevision($rev2)
            );
        }

        // no revision was given, compare previous with current revision
        if (!isset($this->revisions)) {
            // rev2 and rev1 may become false when the page had never existed.
            // rev1 may become false when page is just created anyway.
            $rev2 = $changelog->currentRevision();
            if ($rev2 > $changelog->lastRevision()) {
                $rev1 = $changelog->lastRevision();
            } else {
                $revs = $changelog->getRevisions(0, 1);
                $rev1 = count($revs) ? $revs[0] : false;
            }
        }
        $this->revisions = array(0 => $rev1, 1 => $rev2);
    }
}
