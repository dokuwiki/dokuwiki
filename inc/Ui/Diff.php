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
        if ($newRev < $oldRev) [$oldRev, $newRev] = [$newRev, $oldRev];
        // set correct newRev when it is non-actual revision
        $newRev = $this->changelog->traceExternalRevision($newRev);
        [$this->oldRev, $this->newRev] = [$oldRev2, $newRev];
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
            $rev2[1] = $changelog->currentRevision();
            $rev2[0] = $INPUT->int('rev');
            if ($rev2[0] < $rev2[1]) {
                [$this->oldRev, $this->newRev] = [$rev2[0], $rev2[1]];
            } else {
                // fallback to compare previous with current revision
                unset($rev2);
            }
        }

        // submit button with two checked boxes
        $rev2 = $INPUT->arr('rev2', []);
        if (count($rev2) > 1) {
            if ($rev2[1] < $rev2[0]) [$rev2[0], $rev2[1]] = [$rev2[1], $rev2[0]];
            // set correct rev2[1] when it is non-actual revision
            $rev2[1] = $changelog->traceExternalRevision($rev2[1]);
            [$this->oldRev, $this->newRev] = [$rev2[0], $rev2[1]];
        }

        // no revision was given, compare previous with current revision
        if (!isset($this->oldRev, $this->newRev)) {
            // newRev and oldRev may become false when page had never existed.
            // oldRev may become false when page is just created anyway
            $rev2[1] = $changelog->currentRevision();
            if ($rev2[1] > $changelog->lastRevision()) {
                $rev2[0] = $changelog->lastRevision() ?: false;
            } else {
                $revs = $changelog->getRevisions(0, 1);
                $rev2[0] = count($revs) ? $revs[0] : false;
            }
            [$this->oldRev, $this->newRev] = [$rev2[0], $rev2[1]];
        }
    }
}
