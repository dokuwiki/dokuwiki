<?php

namespace dokuwiki\Ui;

use dokuwiki\ChangeLog\PageChangeLog;
use dokuwiki\ChangeLog\MediaChangeLog;

/**
 * DokuWiki Diff Interface
 * parent class of PageDiff and MediaDiff
 *
 * @package dokuwiki\Ui
 */
abstract class Diff extends Ui
{
    /* @var string */
    protected $id;  // page id or media id

    /* @var int */
    protected $old_rev;  // older revision, timestamp of left side
    protected $new_rev;  // newer revision, timestamp of right side
    protected $last_rev; // current revision, or last revision when it had removed

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
     * Set a pair of revisions to be compared
     *
     * @param int $old_rev
     * @param int $new_rev
     * @return $this
     */
    public function compare($old_rev, $new_rev)
    {
        $this->old_rev = $old_rev;
        $this->new_rev = $new_rev;
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
     * Retrieve requested revision(s) and difftype from Ui\Revisions
     *
     * @return void
     */
    protected function preProcess()
    {
        global $INPUT;

        // difflink icon click, eg. ?rev=123456789&do=diff
        if ($INPUT->has('rev')) {
            $this->old_rev = $INPUT->int('rev');
            $this->new_rev = ''; // current revision
        }

        // submit button with two checked boxes
        $rev2 = $INPUT->arr('rev2', []);
        if (count($rev2) > 1) {
            if ($rev2[0] == 'current') {
                [$this->old_rev, $this->new_rev] = [$rev2[1], ''];
            } elseif ($rev2[1] == 'current') {
                [$this->old_rev, $this->new_rev] = [$rev2[0], ''];
            } elseif ($rev2[0] < $rev2[1]) {
                [$this->old_rev, $this->new_rev] = [$rev2[0], $rev2[1]];
            } else {
                [$this->old_rev, $this->new_rev] = [$rev2[1], $rev2[0]];
            }
        }

        // diff view type
        if ($INPUT->has('difftype')) {
            // retrieve requested $difftype
            $this->preference['difftype'] = $INPUT->str('difftype');
        } else {
            // read preference from DokuWiki cookie. PageDiff only
            get_doku_pref('difftype', $mode);
            if (isset($mode)) $this->preference['difftype'] = $mode;
        }
    }



    /**
     * Build header of diff HTML
     *
     * @param string $l_rev   Left revisions
     * @param string $r_rev   Right revision
     * @return string[] HTML snippets for diff header
     */
    public function buildDiffHead($l_rev, $r_rev)
    {
        global $lang;

        // detect PageDiff or MediaDiff
        switch (get_class($this->changelog)) {
            case PageChangeLog::class :
                $isMedia = false;
                $ui = new PageRevisions($this->id);
                break;
            case MediaChangeLog::class :
                $isMedia = true;
                $ui = new MediaRevisions($this->id);
                break;
        }

        $head_separator = ($this->preference['difftype'] === 'inline') ? ' ' : '<br />';

        // assign minor edit checker to the variable
        $minor = function ($info) {
            return ($info['type'] === DOKU_CHANGE_TYPE_MINOR_EDIT) ? 'class="minor"' : '';
        };

        // assign link builder to the variable
        $idToUrl = function ($id, $rev = '') use ($isMedia) {
            return ($isMedia) ? ml($id, $rev) : wl($id, $rev);
        };

        // assign title builder to the variable
        $idToTitle = function ($id, $rev = '') use ($isMedia) {
            return ($isMedia) ? dformat($rev) : $id.' ['.dformat($rev).']';
        };

        // left side
        if (!$l_rev) {
            $l_minor = '';
            $l_head = '&mdash;';
        } else {
            $info = $this->changelog->getRevisionInfo($l_rev);
            $objRevInfo = $ui->getObjRevInfo($info);
            $l_minor = $minor($info);
            $l_head = '<bdi><a class="wikilink1" href="'.$idToUrl($this->id, "rev=$l_rev").'">'
                    .$idToTitle($this->id, $l_rev).'</a></bdi>'.$head_separator
                    .$objRevInfo->editor().' '.$objRevInfo->editSummary();

        }

        // right side
        if ($r_rev) {
            $info  = $this->changelog->getRevisionInfo($r_rev);
            $objRevInfo = $ui->getObjRevInfo($info);
            $r_minor = $minor($info);
            $r_head = '<bdi><a class="wikilink1" href="'.$idToUrl($this->id, "rev=$r_rev").'">'
                    .$idToTitle($this->id, $r_rev).'</a></bdi>'.$head_separator
                    .$objRevInfo->editor().' '.$objRevInfo->editSummary();
        } elseif ($this->last_rev) {
            $_rev = $this->last_rev;
            $info = $this->changelog->getRevisionInfo($_rev);
            $objRevInfo = $ui->getObjRevInfo($info);
            $r_minor = $minor($info);
            $r_head  = '<bdi><a class="wikilink1" href="'.$idToUrl($this->id).'">'
                     .$idToTitle($this->id, $_rev).'</a></bdi> '.'('.$lang['current'].')'.$head_separator
                     .$objRevInfo->editor().' '.$objRevInfo->editSummary();
        } else {
            $r_minor = '';
            $r_head = '&mdash; ('.$lang['current'].')';
        }

        return array($l_head, $r_head, $l_minor, $r_minor);
    }

}
