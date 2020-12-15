<?php

namespace dokuwiki\Ui;

use dokuwiki\ChangeLog\PageChangeLog;
use dokuwiki\Form\Form;

/**
 * DokuWiki PageDiff Interface
 *
 * @package dokuwiki\Ui
 */
class PageDiff extends Diff
{
    /* @var string */
    protected $text = '';

    /**
     * PageDiff Ui constructor
     *
     * @param string $id  page id
     */
    public function __construct($id = null)
    {
        global $INFO;
        $this->id = isset($id) ? $id : $INFO['id'];

        $this->preference['showIntro'] = true;
        $this->preference['difftype']  = null; // inline or sidebyside
    }

    /**
     * Set text to be compared with most current version
     * exclusively use of the compare($old, $new) method
     *
     * @param string $text
     * @return $this
     */
    public function compareWith($text = null)
    {
        if (isset($text)) {
            $this->text = $text;
            $this->old_rev = '';
        }
        return $this;
    }

    /** @inheritdoc */
    protected function preProcess()
    {
        parent::preProcess();
        if (!isset($this->old_rev, $this->new_rev)) {
            // no revision was given, compare previous to current
            $changelog = new PageChangeLog($this->id);
            $revs = $changelog->getRevisions(0, 1);
            $this->old_rev = $revs[0];
            $this->new_rev = '';

            global $REV;
            $REV = $this->old_rev; // store revision back in $REV
        }
    }

    /**
     * Show diff
     * between current page version and provided $text
     * or between the revisions provided via GET or POST
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     * @return void
     */
    public function show($difftype = null)
    {
       // determine left and right revision
        $this->preProcess();
        [$l_rev, $r_rev] = [$this->old_rev, $this->new_rev];

       // determine html diff view components
        list(
            $l_minor, $r_minor,
            $l_head,  $r_head,
            $l_text,  $r_text,
            $l_nav,   $r_nav,
        ) = $this->getDiffViewComponents($l_rev, $r_rev);

        // determine requested diff view type
        $difftype = $this->getDiffType($difftype);
     // $difftype = $this->preference['difftype'] ?? get_doku_pref('difftype', $mode);;

        // display intro
        if ($this->preference['showIntro']) echo p_locale_xhtml('diff');

        // print form to choose diff view type, and exact url reference to the view
        if (!$this->text) {
            $this->showDiffViewSelector($l_rev, $r_rev, $difftype);
        }

        /*
         * Display diff view table
         */
        print '<div class="table">';
        print '<table class="diff diff_'. $difftype .'">';

        //navigation and header
        if ($difftype == 'inline') {
            if (!$this->text) {
                print '<tr>'
                    . '<td class="diff-lineheader">-</td>'
                    . '<td class="diffnav">'. $l_nav .'</td>'
                    . '</tr>';
                print '<tr>'
                    . '<th class="diff-lineheader">-</th>'
                    . '<th '. $l_minor .'>'. $l_head .'</th>'
                    .'</tr>';
            }
            print '<tr>'
                . '<td class="diff-lineheader">+</td>'
                . '<td class="diffnav">'. $r_nav .'</td>'
                .'</tr>';
            print '<tr>'
                . '<th class="diff-lineheader">+</th>'
                . '<th '. $r_minor .'>'. $r_head .'</th>'
                . '</tr>';
        } else {
            if (!$this->text) {
                print '<tr>'
                    . '<td colspan="2" class="diffnav">'. $l_nav .'</td>'
                    . '<td colspan="2" class="diffnav">'. $r_nav .'</td>'
                    . '</tr>';
            }
            print '<tr>'
                . '<th colspan="2" '. $l_minor .'>'. $l_head .'</th>'
                . '<th colspan="2" '. $r_minor .'>'. $r_head .'</th>'
                . '</tr>';
        }

        // create difference engine object and the formatter
        $Difference = new \Diff(explode("\n", $l_text), explode("\n", $r_text));

        if ($difftype == 'inline') {
            $DiffFormatter = new \InlineDiffFormatter();
        } else {
            $DiffFormatter = new \TableDiffFormatter();
        }

        //diff view
        print $this->insertSoftbreaks($DiffFormatter->format($Difference));

        print '</table>';
        print '</div>';
    }

    /**
     * Determine requested diff view type for page
     *
     * @param string $mode  diff view type (inline or sidebyside)
     * @return string
     */
    protected function getDiffType($mode = null)
    {
        global $INPUT;
        global $INFO;
        $difftype =& $this->preference['difftype'];

        if (!isset($mode)) {
            // retrieve requested $difftype or read preference from DokuWiki cookie
            $difftype = $INPUT->str('difftype') ?: get_doku_pref('difftype', $mode);
            if (empty($difftype)) {
                $difftype = $INFO['ismobile'] ? 'inline' : 'sidebyside';
            }
        } elseif (in_array($mode, ['inline', 'sidebyside'])) {
            $difftype = $mode;
        } else {
            $difftype = 'sidebyside';
        }
        return $this->preference['difftype'];
    }

    /**
     * Determine html diff view components
     *
     * @param int $l_rev  revision timestamp of left side
     * @param int $r_rev  revision timestamp of right side
     * @return array
     *       $l_minor, $r_minor,  // string  class attributes
     *       $l_head,  $r_head,   // string  html snippet
     *       $l_text,  $r_text,   // string  raw wiki text
     *       $l_nav,   $r_nav,    // string  html snippet
     */
    protected function getDiffViewComponents($l_rev, $r_rev)
    {
        global $lang;

        if ($this->text) { // compare text to the most current revision
            $r_minor = '';
            $l_text = rawWiki($this->id, '');
            $l_head = '<a class="wikilink1" href="'. wl($this->id) .'">'
                . $this->id .' '. dformat((int) @filemtime(wikiFN($this->id))) .'</a> '
                . $lang['current'];

            $l_minor = '';
            $r_text = cleanText($this->text);
            $r_head = $lang['yours'];

        } else {
            // when both revisions are empty then the page was created just now
            if (!$l_rev && !$r_rev) {
                $l_text = '';
            } else {
                $l_text = rawWiki($this->id, $l_rev);
            }
            $r_text = rawWiki($this->id, $r_rev);

            $pagelog = new PageChangeLog($this->id);

            // get header of diff HTML
            list($l_head, $r_head, $l_minor, $r_minor) = $this->diffHead($pagelog, $l_rev, $r_rev);
        }
        // build navigation
        $l_nav = '';
        $r_nav = '';
        if (!$this->text) {
            list($l_nav, $r_nav) = $this->diffNavigation($pagelog, $l_rev, $r_rev);
        }

        return array(
            $l_minor, $r_minor,
            $l_head,  $r_head,
            $l_text,  $r_text,
            $l_nav,   $r_nav,
        );
    }

    /**
     * Print form to choose diff view type, and exact url reference to the view
     *
     * @param int $l_rev  revision timestamp of left side
     * @param int $r_rev  revision timestamp of right side
     * @param string $difftype  diff view type for page (inline or sidebyside)
     */
    protected function showDiffViewSelector($l_rev, $r_rev, $difftype)
    {
        global $INFO, $lang;

        echo '<div class="diffoptions group">';

        // create the form to select difftype
        $form = new Form(['action' => wl()]);
        $form->setHiddenField('id', $this->id);
        $form->setHiddenField('rev2[0]', $l_rev ?: 'current');
        $form->setHiddenField('rev2[1]', $r_rev ?: 'current');
        $form->setHiddenField('do', 'diff');
        $options = array(
                     'sidebyside' => $lang['diff_side'],
                     'inline' => $lang['diff_inline']
        );
        $input = $form->addDropdown('difftype', $options, $lang['diff_type'])
            ->val($difftype)->addClass('quickselect');
        $input->useInput(false); // inhibit prefillInput() during toHTML() process
        $form->addButton('do[diff]', 'Go')->attr('type','submit');
        echo $form->toHTML();

        echo '<p>';
        // link to exactly this view FS#2835
        echo $this->diffViewlink('difflink', $l_rev, ($r_rev ?: $INFO['currentrev']));
        echo '</p>';

        echo '</div>'; // .diffoptions
    }


    /**
     * Create html for revision navigation
     *
     * @param PageChangeLog $pagelog changelog object of current page
     * @param int           $l_rev   left revision timestamp
     * @param int           $r_rev   right revision timestamp
     * @return string[] html of left and right navigation elements
     */
    protected function diffNavigation($pagelog, $l_rev, $r_rev)
    {
        global $INFO;

        // last timestamp is not in changelog, retrieve timestamp from metadata
        // note: when page is removed, the metadata timestamp is zero
        if (!$r_rev) {
            if (isset($INFO['meta']['last_change']['date'])) {
                $r_rev = $INFO['meta']['last_change']['date'];
            } else {
                $r_rev = 0;
            }
        }

        //retrieve revisions with additional info
        list($l_revs, $r_revs) = $pagelog->getRevisionsAround($l_rev, $r_rev);
        $l_revisions = array();
        if (!$l_rev) {
            //no left revision given, add dummy
            $l_revisions[0]= array('label' => '', 'attrs' => []);
        }
        foreach ($l_revs as $rev) {
            $info = $pagelog->getRevisionInfo($rev);
            $l_revisions[$rev] = array(
                'label' => dformat($info['date']) .' '. editorinfo($info['user'], true) .' '. $info['sum'],
                'attrs' => ['title' => $rev],
            );
            if ($r_rev ? $rev >= $r_rev : false) $l_revisions[$rev]['attrs']['disabled'] = 'disabled';
        }
        $r_revisions = array();
        if (!$r_rev) {
            //no right revision given, add dummy
            $r_revisions[0] = array('label' => '', 'attrs' => []);
        }
        foreach ($r_revs as $rev) {
            $info = $pagelog->getRevisionInfo($rev);
            $r_revisions[$rev] = array(
                'label' => dformat($info['date']) .' '. editorinfo($info['user'], true) .' '. $info['sum'],
                'attrs' => ['title' => $rev],
            );
            if ($rev <= $l_rev) $r_revisions[$rev]['attrs']['disabled'] = 'disabled';
        }

        //determine previous/next revisions
        $l_index = array_search($l_rev, $l_revs);
        $l_prev = $l_revs[$l_index + 1];
        $l_next = $l_revs[$l_index - 1];
        if ($r_rev) {
            $r_index = array_search($r_rev, $r_revs);
            $r_prev = $r_revs[$r_index + 1];
            $r_next = $r_revs[$r_index - 1];
        } else {
            //removed page
            if ($l_next) {
                $r_prev = $r_revs[0];
            } else {
                $r_prev = null;
            }
            $r_next = null;
        }

        /*
         * Left side:
         */
        $l_nav = '';
        //move back
        if ($l_prev) {
            $l_nav .= $this->diffViewlink('diffbothprevrev', $l_prev, $r_prev);
            $l_nav .= $this->diffViewlink('diffprevrev', $l_prev, $r_rev);
        }
        //dropdown
        $form = new Form(['action' => wl()]);
        $form->setHiddenField('id', $this->id);
        $form->setHiddenField('difftype', $this->difftype);
        $form->setHiddenField('rev2[1]', $r_rev ?: 'current');
        $form->setHiddenField('do', 'diff');
        $input = $form->addDropdown('rev2[0]', $l_revisions)->val($l_rev ?: 'current')->addClass('quickselect');
        $input->useInput(false); // inhibit prefillInput() during toHTML() process
        $form->addButton('do[diff]', 'Go')->attr('type','submit');
        $l_nav .= $form->toHTML();
        //move forward
        if ($l_next && ($l_next < $r_rev || !$r_rev)) {
            $l_nav .= $this->diffViewlink('diffnextrev', $l_next, $r_rev);
        }

        /*
         * Right side:
         */
        $r_nav = '';
        //move back
        if ($l_rev < $r_prev) {
            $r_nav .= $this->diffViewlink('diffprevrev', $l_rev, $r_prev);
        }
        //dropdown
        $form = new Form(['action' => wl()]);
        $form->setHiddenField('id', $this->id);
        $form->setHiddenField('rev2[0]', $l_rev ?: 'current');
        $form->setHiddenField('difftype', $this->difftype);
        $form->setHiddenField('do', 'diff');
        $input = $form->addDropdown('rev2[1]', $r_revisions)->val($r_rev ?: 'current')->addClass('quickselect');
        $input->useInput(false); // inhibit prefillInput() during toHTML() process
        $form->addButton('do[diff]', 'Go')->attr('type','submit');
        $r_nav .= $form->toHTML();
        //move forward
        if ($r_next) {
            if ($pagelog->isCurrentRevision($r_next)) {
                //last revision is diff with current page
                $r_nav .= $this->diffViewlink('difflastrev', $l_rev);
            } else {
                $r_nav .= $this->diffViewlink('diffnextrev', $l_rev, $r_next);
            }
            $r_nav .= $this->diffViewlink('diffbothnextrev', $l_next, $r_next);
        }
        return array($l_nav, $r_nav);
    }

    /**
     * Create html link to a diff view defined by two revisions
     *
     * @param string $linktype
     * @param int $lrev oldest revision
     * @param int $rrev newest revision or null for diff with current revision
     * @return string html of link to a diff view
     */
    protected function diffViewlink($linktype, $lrev, $rrev = null)
    {
        global $lang;
        if ($rrev === null) {
            $urlparam = array(
                'do' => 'diff',
                'rev' => $lrev,
                'difftype' => $this->preference['difftype'],
            );
        } else {
            $urlparam = array(
                'do' => 'diff',
                'rev2[0]' => $lrev,
                'rev2[1]' => $rrev,
                'difftype' => $this->preference['difftype'],
            );
        }
        return  '<a class="'. $linktype .'" href="'. wl($this->id, $urlparam) .'" title="'. $lang[$linktype] .'">'
              . '<span>'. $lang[$linktype] .'</span>'
              . '</a>';
    }


    /**
     * Insert soft breaks in diff html
     *
     * @param string $diffhtml
     * @return string
     */
    public function insertSoftbreaks($diffhtml)
    {
        // search the diff html string for both:
        // - html tags, so these can be ignored
        // - long strings of characters without breaking characters
        return preg_replace_callback('/<[^>]*>|[^<> ]{12,}/', function ($match) {
            // if match is an html tag, return it intact
            if ($match[0][0] == '<') return $match[0];
            // its a long string without a breaking character,
            // make certain characters into breaking characters by inserting a
            // word break opportunity (<wbr> tag) in front of them.
            $regex = <<< REGEX
(?(?=              # start a conditional expression with a positive look ahead ...
&\#?\\w{1,6};)     # ... for html entities - we don't want to split them (ok to catch some invalid combinations)
&\#?\\w{1,6};      # yes pattern - a quicker match for the html entity, since we know we have one
|
[?/,&\#;:]         # no pattern - any other group of 'special' characters to insert a breaking character after
)+                 # end conditional expression
REGEX;
            return preg_replace('<'.$regex.'>xu', '\0<wbr>', $match[0]);
        }, $diffhtml);
    }

}
