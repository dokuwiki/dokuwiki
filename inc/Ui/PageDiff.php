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
    protected $id;

    /* @var string */
    protected $text = '';

    /**
     * PageDiff Ui constructor
     *
     * @param string $id  page id
     * @param string $text  when non-empty: compare with this text with most current version
     */
    public function __construct($id = null, $text = null)
    {
        global $INFO;
        $this->id = isset($id) ? $id : $INFO['id'];
        if (isset($text)) $this->text = $text;

        $this->preference['showIntro'] = true;
        $this->preference['difftype']  = null; // inline or sidebyside
    }

    /**
     * Determine requested diff view type
     *
     * @param string $mode  diff view type (inline or sidebyside)
     */
    public function getDiffType($mode = null)
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
     * Determine requested revision(s)
     */
    protected function getRevisions()
    {
        global $REV;
        global $INPUT;

        // we're trying to be clever here, revisions to compare can be either
        // given as rev and rev2 parameters, with rev2 being optional. Or in an
        // array in rev2.
        $rev1 = $REV;

        $rev2 = $INPUT->ref('rev2');
        if (is_array($rev2)) {
            $rev1 = (int) $rev2[0];
            $rev2 = (int) $rev2[1];

            if (!$rev1) {
                $rev1 = $rev2;
                $rev2 = null;
            }
        } else {
            $rev2 = $INPUT->int('rev2');
        }
        return array($rev1, $rev2);
    }

    /**
     * Determine left and right revision, its texts and the header
     *
     * @return array
     *       $l_rev,   $r_rev,    // int     left and right revisions
     *       $l_minor, $r_minor,  // string  class attributes
     *       $l_head,  $r_head,   // string  html snippet
     *       $l_text,  $r_text,   // string  raw wiki text
     *       $l_nav,   $r_nav,    // string  html snippet
     */
    protected function getHtmlParts()
    {
        global $REV;
        global $lang;

        // determine requested revision(s)
        list($rev1, $rev2) = $this->getRevisions();
        if ($rev2 === null) unset($rev2);

        $pagelog = new PageChangeLog($this->id);

        if ($this->text) { // compare text to the most current revision
            $l_rev = '';
            $r_minor = '';
            $l_text = rawWiki($this->id, '');
            $l_head = '<a class="wikilink1" href="'. wl($this->id) .'">'
                . $this->id .' '. dformat((int) @filemtime(wikiFN($this->id))) .'</a> '
                . $lang['current'];

            $r_rev = '';
            $l_minor = '';
            $r_text = cleanText($this->text);
            $r_head = $lang['yours'];
        } else {
            if ($rev1 && isset($rev2) && $rev2) { // two specific revisions wanted
                // make sure order is correct (older on the left)
                if ($rev1 < $rev2) {
                    $l_rev = $rev1;
                    $r_rev = $rev2;
                } else {
                    $l_rev = $rev2;
                    $r_rev = $rev1;
                }
            } elseif ($rev1) { // single revision given, compare to current
                $r_rev = '';
                $l_rev = $rev1;
            } else { // no revision was given, compare previous to current
                $r_rev = '';
                $revs = $pagelog->getRevisions(0, 1);
                $l_rev = $revs[0];
                $REV = $l_rev; // store revision back in $REV
            }

            // when both revisions are empty then the page was created just now
            if (!$l_rev && !$r_rev) {
                $l_text = '';
            } else {
                $l_text = rawWiki($this->id, $l_rev);
            }
            $r_text = rawWiki($this->id, $r_rev);

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
            $l_rev,   $r_rev,
            $l_minor, $r_minor,
            $l_head,  $r_head,
            $l_text,  $r_text,
            $l_nav,   $r_nav,
        );
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
        global $lang;
        global $INFO;

        $difftype = $this->getDiffType($difftype);

        // determine left and right revision, its texts and the header
        list(
            $l_rev,   $r_rev,
            $l_minor, $r_minor,
            $l_head,  $r_head,
            $l_text,  $r_text,
            $l_nav,   $r_nav,
        ) = $this->getHtmlParts();

        // Create diff object and the formatter
        $diff = new \Diff(explode("\n", $l_text), explode("\n", $r_text));

        if ($difftype == 'inline') {
            $diffformatter = new \InlineDiffFormatter();
        } else {
            $diffformatter = new \TableDiffFormatter();
        }

        // Display intro
        if ($this->preference['showIntro']) print p_locale_xhtml('diff');

        // Display type and exact reference
        if (!$this->text) {
            print '<div class="diffoptions group">';

            // create the form to select difftype
            $form = new Form(['action' => wl()]);
            $form->setHiddenField('id', $this->id);
            $form->setHiddenField('rev2[0]', $l_rev);
            $form->setHiddenField('rev2[1]', $r_rev);
            $form->setHiddenField('do', 'diff');
            $options = array(
                         'sidebyside' => $lang['diff_side'],
                         'inline' => $lang['diff_inline']
            );
            $input = $form->addDropdown('difftype', $options, $lang['diff_type'])
                ->val($difftype)->addClass('quickselect');
            $input->useInput(false); // inhibit prefillInput() during toHTML() process
            $form->addButton('do[diff]', 'Go')->attr('type','submit');
            print $form->toHTML();

            print '<p>';
            // link to exactly this view FS#2835
            print $this->diffViewlink('difflink', $l_rev, ($r_rev ?: $INFO['currentrev']));
            print '</p>';

            print '</div>'; // .diffoptions
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

        //diff view
        print $this->insertSoftbreaks($diffformatter->format($diff));

        print '</table>';
        print '</div>';
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
        $form->setHiddenField('rev2[1]', $r_rev);
        $form->setHiddenField('do', 'diff');
        $input = $form->addDropdown('rev2[0]', $l_revisions)->val($l_rev)->addClass('quickselect');
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
        $form->setHiddenField('rev2[0]', $l_rev);
        $form->setHiddenField('difftype', $this->difftype);
        $form->setHiddenField('do', 'diff');
        $input = $form->addDropdown('rev2[1]', $r_revisions)->val($r_rev)->addClass('quickselect');
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
        } else {
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
