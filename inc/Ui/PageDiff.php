<?php

namespace dokuwiki\Ui;

use dokuwiki\ChangeLog\PageChangeLog;
use dokuwiki\ChangeLog\RevisionInfo;
use dokuwiki\Form\Form;
use InlineDiffFormatter;
use TableDiffFormatter;

/**
 * DokuWiki PageDiff Interface
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Satoshi Sahara <sahara.satoshi@gmail.com>
 * @package dokuwiki\Ui
 */
class PageDiff extends Diff
{
    /* @var PageChangeLog */
    protected $changelog;

    /* @var array */
    protected $oldRevInfo;
    protected $newRevInfo;

    /* @var string */
    protected $text;

    /**
     * PageDiff Ui constructor
     *
     * @param string $id  page id
     */
    public function __construct($id = null)
    {
        global $INFO;
        if (!isset($id)) $id = $INFO['id'];

        // init preference
        $this->preference['showIntro'] = true;
        $this->preference['difftype'] = 'sidebyside'; // diff view type: inline or sidebyside

        parent::__construct($id);
    }

    /** @inheritdoc */
    protected function setChangeLog()
    {
        $this->changelog = new PageChangeLog($this->id);
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
        global $lang;

        if (isset($text)) {
            $this->text = $text;
            $changelog =& $this->changelog;

            // revision info of older file (left side)
            $this->oldRevInfo = $changelog->getCurrentRevisionInfo() + [
                'current' => true,
                'rev'  => '',
                'navTitle' => $this->revisionTitle($changelog->getCurrentRevisionInfo()),
                'text' => rawWiki($this->id),
            ];

            // revision info of newer file (right side)
            $this->newRevInfo = [
                'date' => null,
              //'ip'   => '127.0.0.1',
              //'type' => DOKU_CHANGE_TYPE_CREATE,
                'id'   => $this->id,
              //'user' => '',
              //'sum'  => '',
                'extra' => 'compareWith',
                'sizechange' => strlen($this->text) - io_getSizeFile(wikiFN($this->id)),
                'timestamp' => false,
                'current' => false,
                'rev'  => false,
                'navTitle' => $lang['yours'],
                'text' => cleanText($this->text),
            ];
        }
        return $this;
    }

    /**
     * Handle requested revision(s) and diff view preferences
     *
     * @return void
     */
    protected function handle()
    {
        global $INPUT;

        // requested rev or rev2
        if (!isset($this->oldRevInfo, $this->newRevInfo)) {
            parent::handle();
        }

        // requested diff view type
        if ($INPUT->has('difftype')) {
            $this->preference['difftype'] = $INPUT->str('difftype');
        } else {
            // read preference from DokuWiki cookie. PageDiff only
            $mode = get_doku_pref('difftype', null);
            if (isset($mode)) $this->preference['difftype'] = $mode;
        }

        if (!$INPUT->has('rev') && !$INPUT->has('rev2')) {
            global $INFO, $REV;
            if ($this->id == $INFO['id'])
                $REV = $this->oldRev; // store revision back in $REV
        }
    }

    /**
     * Prepare revision info of comparison pair
     */
    protected function preProcess()
    {
        global $lang;

        $changelog =& $this->changelog;

        // get revision info array for older and newer sides
        foreach (['oldRev','newRev'] as $rev) {
            $revInfo = $rev.'Info';
            if ($this->$rev !== false) {
                $this->$revInfo = $changelog->getRevisionInfo($this->$rev);
            } else {
                // invalid revision number, set exceptional revInfo array
                $this->$revInfo = array(
                    'date' => false,
                    'type' => '',
                    'timestamp' => false,
                    'rev'  => false,
                    'text' => '',
                    'navTitle' => '&mdash;',
                );
            }
        }

        foreach ([&$this->oldRevInfo, &$this->newRevInfo] as &$revInfo) {
            // use timestamp and '' properly as $rev for the current file
            $isCurrent = $changelog->isCurrentRevision($revInfo['date']);
            $revInfo += [
                'current' => $isCurrent,
                'rev'     => $isCurrent ? '' : $revInfo['date'],
            ];

            // headline in the Diff view navigation
            if (!isset($revInfo['navTitle'])) {
                $revInfo['navTitle'] = $this->revisionTitle($revInfo);
            }

            if ($revInfo['type'] == DOKU_CHANGE_TYPE_DELETE) {
                //attic stores complete last page version for a deleted page
                $revInfo['text'] = '';
            } else {
                $revInfo['text'] = rawWiki($this->id, $revInfo['rev']);
            }
        }

        if ($this->newRev === false) {
            msg(sprintf($lang['page_nonexist_rev'],
                $this->id,
                wl($this->id, ['do'=>'edit']),
                $this->id), -1);
        } elseif (!$this->oldRev || $this->oldRev == $this->newRev) {
            msg('no way to compare when less than two revisions', -1);
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
    public function show()
    {
        if (!isset($this->oldRevInfo, $this->newRevInfo)) {
            // retrieve form parameters: rev, rev2, difftype
            $this->handle();
            // prepare revision info of comparison pair, except PageConfrict or PageDraft
            $this->preProcess();
        }

        // create difference engine object
        $Difference = new \Diff(
                explode("\n", $this->oldRevInfo['text']),
                explode("\n", $this->newRevInfo['text'])
        );

        // build paired navigation
        [$navOlderRevisions, $navNewerRevisions] = $this->buildRevisionsNavigation();

        // display intro
        if ($this->preference['showIntro']) echo p_locale_xhtml('diff');

        // print form to choose diff view type, and exact url reference to the view
        $this->showDiffViewSelector();

        // assign minor edit checker to the variable
        $classEditType = function ($info) {
            return ($info['type'] === DOKU_CHANGE_TYPE_MINOR_EDIT) ? ' class="minor"' : '';
        };

        // display diff view table
        echo '<div class="table">';
        echo '<table class="diff diff_'.$this->preference['difftype'] .'">';

        //navigation and header
        switch ($this->preference['difftype']) {
            case 'inline':
                if ($this->newRevInfo['rev'] !== false) {
                    echo '<tr>'
                        .'<td class="diff-lineheader">-</td>'
                        .'<td class="diffnav">'. $navOlderRevisions .'</td>'
                        .'</tr>';
                    echo '<tr>'
                        .'<th class="diff-lineheader">-</th>'
                        .'<th'.$classEditType($this->oldRevInfo).'>'.$this->oldRevInfo['navTitle'].'</th>'
                        .'</tr>';
                }
                echo '<tr>'
                    .'<td class="diff-lineheader">+</td>'
                    .'<td class="diffnav">'. $navNewerRevisions .'</td>'
                    .'</tr>';
                echo '<tr>'
                    .'<th class="diff-lineheader">+</th>'
                    .'<th'.$classEditType($this->newRevInfo).'>'.$this->newRevInfo['navTitle'].'</th>'
                    .'</tr>';
                // create formatter object
                $DiffFormatter = new InlineDiffFormatter();
                break;

            case 'sidebyside':
            default:
                if ($this->newRevInfo['rev'] !== false) {
                    echo '<tr>'
                        .'<td colspan="2" class="diffnav">'. $navOlderRevisions .'</td>'
                        .'<td colspan="2" class="diffnav">'. $navNewerRevisions .'</td>'
                        .'</tr>';
                }
                echo '<tr>'
                    .'<th colspan="2"'.$classEditType($this->oldRevInfo).'>'.$this->oldRevInfo['navTitle'].'</th>'
                    .'<th colspan="2"'.$classEditType($this->newRevInfo).'>'.$this->newRevInfo['navTitle'].'</th>'
                    .'</tr>';
                // create formatter object
                $DiffFormatter = new TableDiffFormatter();
                break;
        }

        // output formatted difference
        echo $this->insertSoftbreaks($DiffFormatter->format($Difference));

        echo '</table>';
        echo '</div>';
    }

    /**
     * Revision Title for PageDiff table headline
     *
     * @param array $info  Revision info structure of a page
     * @return string
     */
    protected function revisionTitle(array $info)
    {
        global $lang;

        // use designated title when compare current page source with given text
        if ($info['extra'] == 'compareWith') {
            return $lang['yours'];
        }

        // revision info may have timestamp key when external edits occurred
        $info['timestamp'] = $info['timestamp'] ?? true;

        if (isset($info['date'])) {
            $rev = $info['date'];
            if ($info['timestamp'] === false) {
                // exteranlly deleted or older file restored
                $title = '<bdi><a class="wikilink2" href="'.wl($this->id).'">'
                   . $this->id .' ['. $lang['unknowndate'] .']'.'</a></bdi>';
            } else {
                $title = '<bdi><a class="wikilink1" href="'.wl($this->id, ['rev' => $rev]).'">'
                   . $this->id .' ['. dformat($rev) .']'.'</a></bdi>';
            }
        } else {
            $title = '&mdash;';
        }
        if ($info['current']) {
            $title .= '&nbsp;('.$lang['current'].')';
        }

        // append separator
        $title .= ($this->preference['difftype'] === 'inline') ? ' ' : '<br />';

        // supplement
        if (isset($info['date'])) {
            $RevInfo = new RevisionInfo($info);
            $title .= $RevInfo->showEditSummary().' '.$RevInfo->showEditor();
        }
        return $title;
    }

    /**
     * Print form to choose diff view type, and exact url reference to the view
     */
    protected function showDiffViewSelector()
    {
        global $lang;

        // no revisions selector for PageConflict or PageDraft
        if ($this->newRevInfo['extra'] == 'compareWith') return;

        // use timestamp for current revision
        [$oldRev, $newRev] = [(int)$this->oldRevInfo['date'], (int)$this->newRevInfo['date']];

        echo '<div class="diffoptions group">';

        // create the form to select difftype
        $form = new Form(['action' => wl($this->id)]);
        $form->setHiddenField('id', $this->id);
        $form->setHiddenField('rev2[0]', $oldRev);
        $form->setHiddenField('rev2[1]', $newRev);
        $form->setHiddenField('do', 'diff');
        $options = array(
                     'sidebyside' => $lang['diff_side'],
                     'inline' => $lang['diff_inline'],
        );
        $input = $form->addDropdown('difftype', $options, $lang['diff_type'])
            ->val($this->preference['difftype'])
            ->addClass('quickselect');
        $input->useInput(false); // inhibit prefillInput() during toHTML() process
        $form->addButton('do[diff]', 'Go')->attr('type','submit');
        echo $form->toHTML();

        // show exact url reference to the view when it is meaningful
        echo '<p>';
        if ($oldRev && $newRev) {
            // link to exactly this view FS#2835
            $viewUrl = $this->diffViewlink('difflink', $oldRev, $newRev);
        }
        echo $viewUrl ?? '<br />';
        echo '</p>';

        echo '</div>'; // .diffoptions
    }

    /**
     * Create html for revision navigation
     *
     * The navigation consists of older and newer revisions selectors, each
     * state mutually depends on the selected revision of opposite side.
     *
     * @return string[] html of navigation for both older and newer sides
     */
    protected function buildRevisionsNavigation()
    {
        $changelog =& $this->changelog;

        if ($this->newRevInfo['extra'] == 'compareWith') {
            // no revisions selector for PageConflict or PageDraft
            return array('', '');
        }

        // use timestamp for current revision, date may be false when revisions < 2
        [$oldRev, $newRev] = [(int)$this->oldRevInfo['date'], (int)$this->newRevInfo['date']];

        // retrieve revisions used in dropdown selectors
        [$oldRevs, $newRevs] = $changelog->getRevisionsAround(
            ($oldRev ?: $changelog->currentRevision()),
            ($newRev ?: $changelog->currentRevision())
        );

        // build options for dropdown selector
        $olderRevisions = $this->buildRevisionOptions('older', $oldRevs);
        $newerRevisions = $this->buildRevisionOptions('newer', $newRevs);

        // determine previous/next revisions (older/left side)
        $oldPrevRev = $oldNextRev = false;
        if (($index = array_search($oldRev, $oldRevs)) !== false) {
            $oldPrevRev = ($index +1 < count($oldRevs)) ? $oldRevs[$index +1] : false;
            $oldNextRev = ($index > 0)                  ? $oldRevs[$index -1] : false;
        }
        // determine previous/next revisions (newer/right side)
        $newPrevRev = $newNextRev = false;
        if (($index = array_search($newRev, $newRevs)) !== false) {
            $newPrevRev = ($index +1 < count($newRevs)) ? $newRevs[$index +1] : false;
            $newNextRev = ($index > 0)                  ? $newRevs[$index -1] : false;
        }

        /*
         * navigation UI for older revisions / Left side:
         */
        $navOlderRevs = '';
        // move backward both side: ◀◀
        if ($oldPrevRev && $newPrevRev)
            $navOlderRevs .= $this->diffViewlink('diffbothprevrev', $oldPrevRev, $newPrevRev);
        // move backward left side: ◀
        if ($oldPrevRev)
            $navOlderRevs .= $this->diffViewlink('diffprevrev', $oldPrevRev, $newRev);
        // dropdown
        $navOlderRevs .= $this->buildDropdownSelector('older', $olderRevisions);
        // move forward left side: ▶
        if ($oldNextRev && ($oldNextRev < $newRev))
            $navOlderRevs .= $this->diffViewlink('diffnextrev', $oldNextRev, $newRev);

        /*
         * navigation UI for newer revisions / Right side:
         */
        $navNewerRevs = '';
        // move backward right side: ◀
        if ($newPrevRev && ($oldRev < $newPrevRev))
            $navNewerRevs .= $this->diffViewlink('diffprevrev', $oldRev, $newPrevRev);
        // dropdown
        $navNewerRevs .= $this->buildDropdownSelector('newer', $newerRevisions);
        // move forward right side: ▶
        if ($newNextRev) {
            if ($changelog->isCurrentRevision($newNextRev)) {
                $navNewerRevs .= $this->diffViewlink('difflastrev', $oldRev, $newNextRev);
            } else {
                $navNewerRevs .= $this->diffViewlink('diffnextrev', $oldRev, $newNextRev);
            }
        }
        // move forward both side: ▶▶
        if ($oldNextRev && $newNextRev)
            $navNewerRevs .= $this->diffViewlink('diffbothnextrev', $oldNextRev, $newNextRev);

        return array($navOlderRevs, $navNewerRevs);
    }

    /**
     * prepare options for dropdwon selector
     *
     * @params string $side  "older" or "newer"
     * @params array $revs  list of revsion
     * @return array
     */
    protected function buildRevisionOptions($side, $revs)
    {
        $changelog =& $this->changelog;
        $revisions = array();

        // use timestamp for current revision, date may be false when revisions < 2
        [$oldRev, $newRev] = [(int)$this->oldRevInfo['date'], (int)$this->newRevInfo['date']];

        foreach ($revs as $rev) {
            $info = $changelog->getRevisionInfo($rev);
            // revision info may have timestamp key when external edits occurred
            $info['timestamp'] = $info['timestamp'] ?? true;
            $date = dformat($info['date']);
            if ($info['timestamp'] === false) {
                // exteranlly deleted or older file restored
                $date = preg_replace('/[0-9a-zA-Z]/','_', $date);
            }
            $revisions[$rev] = array(
                'label' => implode(' ', [
                            $date,
                            editorinfo($info['user'], true),
                            $info['sum'],
                           ]),
                'attrs' => ['title' => $rev],
            );
            if (($side == 'older' && ($newRev && $rev >= $newRev))
              ||($side == 'newer' && ($rev <= $oldRev))
            ) {
                $revisions[$rev]['attrs']['disabled'] = 'disabled';
            }
        }
        return $revisions;
    }

    /**
     * build Dropdown form for revisions navigation
     *
     * @params string $side  "older" or "newer"
     * @params array $options  dropdown options
     * @return string
     */
    protected function buildDropdownSelector($side, $options)
    {
        $form = new Form(['action' => wl($this->id)]);
        $form->setHiddenField('id', $this->id);
        $form->setHiddenField('do', 'diff');
        $form->setHiddenField('difftype', $this->preference['difftype']);

        // use timestamp for current revision, date may be false when revisions < 2
        [$oldRev, $newRev] = [(int)$this->oldRevInfo['date'], (int)$this->newRevInfo['date']];

        switch ($side) {
            case 'older': // left side
                $form->setHiddenField('rev2[1]', $newRev);
                $input = $form->addDropdown('rev2[0]', $options)
                    ->val($oldRev)->addClass('quickselect');
                $input->useInput(false); // inhibit prefillInput() during toHTML() process
                break;
            case 'newer': // right side
                $form->setHiddenField('rev2[0]', $oldRev);
                $input = $form->addDropdown('rev2[1]', $options)
                    ->val($newRev)->addClass('quickselect');
                $input->useInput(false); // inhibit prefillInput() during toHTML() process
                break;
        }
        $form->addButton('do[diff]', 'Go')->attr('type','submit');
        return $form->toHTML();
    }

    /**
     * Create html link to a diff view defined by two revisions
     *
     * @param string $linktype
     * @param int $oldRev older revision
     * @param int $newRev newer revision or null for diff with current revision
     * @return string html of link to a diff view
     */
    protected function diffViewlink($linktype, $oldRev, $newRev = null)
    {
        global $lang;
        if ($oldRev === false) return '';

        if ($newRev === null) {
            $urlparam = array(
                'do' => 'diff',
                'rev' => $oldRev,
                'difftype' => $this->preference['difftype'],
            );
        } else {
            $urlparam = array(
                'do' => 'diff',
                'rev2[0]' => $oldRev,
                'rev2[1]' => $newRev,
                'difftype' => $this->preference['difftype'],
            );
        }
        $attr = array(
            'class' => $linktype,
            'href'  => wl($this->id, $urlparam, true, '&'),
            'title' => $lang[$linktype],
        );
        return '<a '. buildAttributes($attr) .'><span>'. $lang[$linktype] .'</span></a>';
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
