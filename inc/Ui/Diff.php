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

    /* @var int|false */
    protected $rev1;  // timestamp of older revision
    /* @var int|false */
    protected $rev2;  // timestamp of newer revision

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
        $this->rev1 = (int)$rev1;
        $this->rev2 = (int)$this->changelog->traceCurrentRevision($rev2);
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

        // diff link icon click, eg. &do=diff&rev=#
        if ($INPUT->has('rev')) {
            $this->rev1 = $INPUT->int('rev');
            $this->rev2 = $this->changelog->currentRevision();
            if ($this->rev2 <= $this->rev1) {
                // fallback to compare previous with current
                 unset($this->rev1, $this->rev2);
            }
        }

        // submit button with two checked boxes, eg. &do=diff&rev2[0]=#&rev2[1]=#
        $revs = $INPUT->arr('rev2', []);
        if (count($revs) > 1) {
            list($rev1, $rev2) = $revs;
            if ($rev2 < $rev1) [$rev1, $rev2] = [$rev2, $rev1];
            $this->rev1 = (int)$rev1;
            $this->rev2 = (int)$this->changelog->traceCurrentRevision($rev2);
        }

       // no revision was given, compare previous to current
        if (!isset($this->rev1, $this->rev2)) {
            $rev2 = $this->changelog->currentRevision();
            if ($rev2 > $this->changelog->lastRevision()) {
                $rev1 = $this->changelog->lastRevision();
            } else {
                $revs = $this->changelog->getRevisions(0, 1);
                $rev1 = count($revs) ? $revs[0] : false;
            }
            $this->rev1 = $rev1;
            $this->rev2 = $rev2;
        }
    }
}
