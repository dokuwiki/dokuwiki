<?php

namespace dokuwiki\Ui;

use dokuwiki\ChangeLog\RevisionInfo;
use dokuwiki\Ui\PageDiff;
use dokuwiki\Ui\MediaDiff;

/**
 * DokuWiki Generic Diff Viewer
 *
 * @package dokuwiki\Ui
 */
class DiffView extends Ui
{
    /* @var string */
    protected $id;   // page id or media id

    /* @var string */
    protected $mode;   // page or media

    /* @var DiffViewInterface */
    protected $diff;

    /**
     * DiffView constructor
     *
     * @param string $id  page id or media id
     */
    public function __construct($id)
    {
      //$this->id = $id;
        if (empty($id)) {
            $this->mode = 'media';
        } else {
            $this->mode = strrpos($id, '.') ? 'media' : 'page';
        }

        // diff viewer switching strategy
        $this->diff = $this->getDiffViewInterface($id);
    }

    /**
     * Get instance of diff view interface
     *
     * @param string $id  page id or media id
     */
    protected function getDiffViewInterface($id)
    {
        switch ($this->mode) {
            case 'page' : return new PageDiff($id);
            case 'media': return new MediaDiff($id);
        }
    }


    /**
     * Set a pair of revisions to be compared
     *
     * @param int $rev1 older revision
     * @param int $rev2 newer revision
     * @return $this
     */
    public function compare($rev1, $rev2)
    {
        $diff =& $this->diff;

        if ($rev2 < $rev1) [$rev1, $rev2] = [$rev2, $rev1];
        // set correct newer revision when it is non-actual revision
        $diff->setRevisions([$rev1, $rev2]);
        return $this;
    }

    /**
     * Set text to be compared with most current version
     * The method is called from class Ui\PageConflict and Ui\PageDraft
     *
     * @param string $text
     * @return $this
     */
    public function compareWith($text = null)
    {
        $diff =& $this->diff;
        if ($diff instanceof PageDiff) {
            $diff->compareWith($text);
        }
        return $this;
    }

    /**
     * Gets or Sets preference of the Ui\Diff object
     *
     * @param string|array $prefs  a key name or key-value pair(s)
     * @param mixed $value         value used when the first args is string
     * @return $this
     */
    public function preference($prefs = null, $value = null)
    {
        $diff =& $this->diff;
        $diff->setPreference($prefs, $value);
        return $this;
    }


    /**
     * Handle requested revision(s) and set diff property revisions
     *
     * @return void
     */
    protected function handle()
    {
        global $INPUT;
        $diff =& $this->diff;

        // difflink icon click, url parameter: &do=diff&rev=#
        if ($INPUT->has('rev')) {
            // compare given revision with current
            $rev1 = $INPUT->int('rev');
        }

        // submit button with two checked boxes, url parameter: &do=diff&rev2[0]=#&rev2[1]=#
        $revs = $INPUT->arr('rev2', []);
        if (count($revs) > 1) {
            list($rev1, $rev2) = $revs;
            if ($rev2 < $rev1) [$rev1, $rev2] = [$rev2, $rev1];
        }

        $revs = [];
        if (isset($rev1)) $revs[] = $rev1;
        if (isset($rev2)) $revs[] = $rev2;

        $diff->setRevisions($revs);
    }


    /**
     * Display diff view
     *
     */
    public function show()
    {
        if (!isset($this->diff->Revision1, $this->diff->Revision2)) {
            // call handle, process url parameters: rev, rev2
            $this->handle();
        }
        $this->diff->show();
    }

}
