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

    /* @var RevisionInfo older revision */
    protected $RevInfo1;
    /* @var RevisionInfo newer revision */
    protected $RevInfo2;

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
     * when it has been externally edited
     * exclusively use of the compare($old, $new) method
     *
     * @param string $text
     * @return $this
     */
    public function compareWith($text = null)
    {
        if (isset($text)) {
            $this->text = $text;
            $changelog =& $this->changelog;

            // revision info object of older file (left side)
            $this->RevInfo1 = new RevisionInfo($changelog->getCurrentRevisionInfo());
            $this->RevInfo1->append([
                'current' => true,
                'text' => rawWiki($this->id),
            ]);

            // revision info object of newer file (right side)
            $this->RevInfo2 = new RevisionInfo();
            $this->RevInfo2->append([
                'date' => false,
              //'ip'   => '127.0.0.1',
              //'type' => DOKU_CHANGE_TYPE_CREATE,
                'id'   => $this->id,
              //'user' => '',
              //'sum'  => '',
                'extra' => 'compareWith',
              //'sizechange' => strlen($this->text) - io_getSizeFile(wikiFN($this->id)),
                'current' => false,
                'text' => cleanText($this->text),
            ]);
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

        // retrieve requested rev or rev2
        if (!isset($this->RevInfo1, $this->RevInfo2)) {
            parent::handle();
        }

        // requested diff view type
        $mode = '';
        if ($INPUT->has('difftype')) {
            $mode = $INPUT->str('difftype');
        } else {
            // read preference from DokuWiki cookie. PageDiff only
            $mode = get_doku_pref('difftype', null);
        }
        if(in_array($mode, ['inline','sidebyside'])) $this->preference['difftype'] = $mode;

        if (!$INPUT->has('rev') && !$INPUT->has('rev2')) {
            global $INFO, $REV;
            if ($this->id == $INFO['id'])
                $REV = $this->rev1; // store revision back in $REV
        }
    }

    /**
     * Prepare revision info of comparison pair
     */
    protected function preProcess()
    {
        global $lang;

        $changelog =& $this->changelog;

        // create revision info object for older and newer sides
        // RevInfo1 : older, left side
        // RevInfo2 : newer, right side
        $this->RevInfo1 = new RevisionInfo($changelog->getRevisionInfo($this->rev1));
        $this->RevInfo2 = new RevisionInfo($changelog->getRevisionInfo($this->rev2));

        foreach ([$this->RevInfo1, $this->RevInfo2] as $RevInfo) {
            $isCurrent = $changelog->isCurrentRevision($RevInfo->val('date'));
            $RevInfo->isCurrent($isCurrent);

            if ($RevInfo->val('type') == DOKU_CHANGE_TYPE_DELETE || empty($RevInfo->val('type'))) {
                $text = '';
            } else {
                $rev = $isCurrent ? '' : $RevInfo->val('date');
                $text = rawWiki($this->id, $rev);
            }
            $RevInfo->append(['text' => $text]);
        }

        // msg could displayed only when wrong url typed in browser address bar
        if ($this->rev2 === false) {
            msg(sprintf($lang['page_nonexist_rev'],
                $this->id,
                wl($this->id, ['do'=>'edit']),
                $this->id), -1);
        } elseif (!$this->rev1 || $this->rev1 == $this->rev2) {
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
        global $lang;

        if (!isset($this->RevInfo1, $this->RevInfo2)) {
            // retrieve form parameters: rev, rev2, difftype
            $this->handle();
            // prepare revision info of comparison pair, except PageConfrict or PageDraft
            $this->preProcess();
        }

        // revision title
        $rev1Title = trim($this->RevInfo1->showRevisionTitle() .' '. $this->RevInfo1->showCurrentIndicator());
        $rev1Summary = ($this->RevInfo1->val('date'))
            ? $this->RevInfo1->showEditSummary() .' '. $this->RevInfo1->showEditor()
            : '';

        if ($this->RevInfo2->val('extra') == 'compareWith') {
            $rev2Title = $lang['yours'];
            $rev2Summary = '';
        } else {
            $rev2Title = trim($this->RevInfo2->showRevisionTitle() .' '. $this->RevInfo2->showCurrentIndicator());
            $rev2Summary = ($this->RevInfo2->val('date'))
                ? $this->RevInfo2->showEditSummary() .' '. $this->RevInfo2->showEditor()
                : '';
        }

        // create difference engine object
        $Difference = new \Diff(
                explode("\n", $this->RevInfo1->val('text')),
                explode("\n", $this->RevInfo2->val('text'))
        );

        // build paired navigation
        [$rev1Navi, $rev2Navi] = $this->buildRevisionsNavigation();

        // display intro
        if ($this->preference['showIntro']) echo p_locale_xhtml('diff');

        // print form to choose diff view type, and exact url reference to the view
        $this->showDiffViewSelector();

        // assign minor edit checker to the variable
        $classEditType = function ($changeType) {
            return ($changeType === DOKU_CHANGE_TYPE_MINOR_EDIT) ? ' class="minor"' : '';
        };

        // display diff view table
        echo '<div class="table">';
        echo '<table class="diff diff_'.hsc($this->preference['difftype']) .'">';

        //navigation and header
        switch ($this->preference['difftype']) {
            case 'inline':
                $title1 = $rev1Title . ($rev1Summary ? '<br />'.$rev1Summary : '');
                $title2 = $rev2Title . ($rev2Summary ? '<br />'.$rev2Summary : '');
                // no navigation for PageConflict or PageDraft
                if ($this->RevInfo2->val('extra') !== 'compareWith') {
                    echo '<tr>'
                        .'<td class="diff-lineheader">-</td>'
                        .'<td class="diffnav">'. $rev1Navi .'</td>'
                        .'</tr>';
                    echo '<tr>'
                        .'<th class="diff-lineheader">-</th>'
                        .'<th'.$classEditType($this->RevInfo1->val('type')).'>'. $title1 .'</th>'
                        .'</tr>';
                }
                echo '<tr>'
                    .'<td class="diff-lineheader">+</td>'
                    .'<td class="diffnav">'. $rev2Navi .'</td>'
                    .'</tr>';
                echo '<tr>'
                    .'<th class="diff-lineheader">+</th>'
                    .'<th'.$classEditType($this->RevInfo2->val('type')).'>'. $title2 .'</th>'
                    .'</tr>';
                // create formatter object
                $DiffFormatter = new InlineDiffFormatter();
                break;

            case 'sidebyside':
            default:
                $title1 = $rev1Title . ($rev1Summary ? ' '.$rev1Summary : '');
                $title2 = $rev2Title . ($rev2Summary ? ' '.$rev2Summary : '');
                // no navigation for PageConflict or PageDraft
                if ($this->RevInfo2->val('extra') !== 'compareWith') {
                    echo '<tr>'
                        .'<td colspan="2" class="diffnav">'. $rev1Navi .'</td>'
                        .'<td colspan="2" class="diffnav">'. $rev2Navi .'</td>'
                        .'</tr>';
                }
                echo '<tr>'
                    .'<th colspan="2"'.$classEditType($this->RevInfo1->val('type')).'>'.$title1.'</th>'
                    .'<th colspan="2"'.$classEditType($this->RevInfo2->val('type')).'>'.$title2.'</th>'
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
     * Print form to choose diff view type, and exact url reference to the view
     */
    protected function showDiffViewSelector()
    {
        global $lang;

        // no revisions selector for PageConflict or PageDraft
        if ($this->RevInfo2->val('extra') == 'compareWith') return;

        // use timestamp for current revision, date may be false when revisions < 2
        [$rev1, $rev2] = [(int)$this->RevInfo1->val('date'), (int)$this->RevInfo2->val('date')];

        echo '<div class="diffoptions group">';

        // create the form to select difftype
        $form = new Form(['action' => wl()]);
        $form->setHiddenField('id', $this->id);
        $form->setHiddenField('rev2[0]', $rev1);
        $form->setHiddenField('rev2[1]', $rev2);
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
        if ($rev1 && $rev2) {
            // link to exactly this view FS#2835
            $viewUrl = $this->diffViewlink('difflink', $rev1, $rev2);
        }
        echo $viewUrl ?? '<br />';
        echo '</p>';

        echo '</div>';
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

        if ($this->RevInfo2->val('extra') == 'compareWith') {
            // no revisions selector for PageConflict or PageDraft
            return array('', '');
        }

        // use timestamp for current revision, date may be false when revisions < 2
        [$rev1, $rev2] = [(int)$this->RevInfo1->val('date'), (int)$this->RevInfo2->val('date')];

        // retrieve revisions used in dropdown selectors, even when rev1 or rev2 is false
        [$revs1, $revs2] = $changelog->getRevisionsAround(
            ($rev1 ?: $changelog->currentRevision()),
            ($rev2 ?: $changelog->currentRevision())
        );

        // build options for dropdown selector
        $rev1Options = $this->buildRevisionOptions('older', $revs1);
        $rev2Options = $this->buildRevisionOptions('newer', $revs2);

        // determine previous/next revisions (older/left side)
        $rev1Prev = $rev1Next = false;
        if (($index = array_search($rev1, $revs1)) !== false) {
            $rev1Prev = ($index +1 < count($revs1)) ? $revs1[$index +1] : false;
            $rev1Next = ($index > 0)                ? $revs1[$index -1] : false;
        }
        // determine previous/next revisions (newer/right side)
        $rev2Prev = $rev2Next = false;
        if (($index = array_search($rev2, $revs2)) !== false) {
            $rev2Prev = ($index +1 < count($revs2)) ? $revs2[$index +1] : false;
            $rev2Next = ($index > 0)                ? $revs2[$index -1] : false;
        }

        /*
         * navigation UI for older revisions / Left side:
         */
        $rev1Navi = '';
        // move backward both side: ◀◀
        if ($rev1Prev && $rev2Prev)
            $rev1Navi .= $this->diffViewlink('diffbothprevrev', $rev1Prev, $rev2Prev);
        // move backward left side: ◀
        if ($rev1Prev)
            $rev1Navi .= $this->diffViewlink('diffprevrev', $rev1Prev, $rev2);
        // dropdown
        $rev1Navi .= $this->buildDropdownSelector('older', $rev1Options);
        // move forward left side: ▶
        if ($rev1Next && ($rev1Next < $rev2))
            $rev1Navi .= $this->diffViewlink('diffnextrev', $rev1Next, $rev2);

        /*
         * navigation UI for newer revisions / Right side:
         */
        $rev2Navi = '';
        // move backward right side: ◀
        if ($rev2Prev && ($rev1 < $rev2Prev))
            $rev2Navi .= $this->diffViewlink('diffprevrev', $rev1, $rev2Prev);
        // dropdown
        $rev2Navi .= $this->buildDropdownSelector('newer', $rev2Options);
        // move forward right side: ▶
        if ($rev2Next) {
            if ($changelog->isCurrentRevision($rev2Next)) {
                $rev2Navi .= $this->diffViewlink('difflastrev', $rev1, $rev2Next);
            } else {
                $rev2Navi .= $this->diffViewlink('diffnextrev', $rev1, $rev2Next);
            }
        }
        // move forward both side: ▶▶
        if ($rev1Next && $rev2Next)
            $rev2Navi .= $this->diffViewlink('diffbothnextrev', $rev1Next, $rev2Next);

        return array($rev1Navi, $rev2Navi);
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
        // use timestamp for current revision, date may be false when revisions < 2
        [$rev1, $rev2] = [(int)$this->RevInfo1->val('date'), (int)$this->RevInfo2->val('date')];

        $changelog =& $this->changelog;
        $options = [];

        foreach ($revs as $rev) {
            $info = $changelog->getRevisionInfo($rev);
            // revision info may have timestamp key when external edits occurred
            $info['timestamp'] = $info['timestamp'] ?? true;
            $date = dformat($info['date']);
            if ($info['timestamp'] === false) {
                // exteranlly deleted or older file restored
                $date = preg_replace('/[0-9a-zA-Z]/','_', $date);
            }
            $options[$rev] = array(
                'label' => implode(' ', [
                            $date,
                            editorinfo($info['user'], true),
                            $info['sum'],
                           ]),
                'attrs' => ['title' => $rev],
            );
            if (($side == 'older' && ($rev2 && $rev >= $rev2))
              ||($side == 'newer' && ($rev <= $rev1))
            ) {
                $options[$rev]['attrs']['disabled'] = 'disabled';
            }
        }
        return $options;
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
        // use timestamp for current revision, date may be false when revisions < 2
        [$rev1, $rev2] = [(int)$this->RevInfo1->val('date'), (int)$this->RevInfo2->val('date')];

        $form = new Form(['action' => wl($this->id)]);
        $form->setHiddenField('id', $this->id);
        $form->setHiddenField('do', 'diff');
        $form->setHiddenField('difftype', $this->preference['difftype']);

        switch ($side) {
            case 'older': // left side
                $form->setHiddenField('rev2[1]', $rev2);
                $input = $form->addDropdown('rev2[0]', $options)
                    ->val($rev1)->addClass('quickselect');
                $input->useInput(false); // inhibit prefillInput() during toHTML() process
                break;
            case 'newer': // right side
                $form->setHiddenField('rev2[0]', $rev1);
                $input = $form->addDropdown('rev2[1]', $options)
                    ->val($rev2)->addClass('quickselect');
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
     * @param int $rev1 older revision
     * @param int $rev2 newer revision or null for diff with current revision
     * @return string html of link to a diff view
     */
    protected function diffViewlink($linktype, $rev1, $rev2 = null)
    {
        global $lang;
        if ($rev1 === false) return '';

        if ($rev2 === null) {
            $urlparam = array(
                'do' => 'diff',
                'rev' => $rev1,
                'difftype' => $this->preference['difftype'],
            );
        } else {
            $urlparam = array(
                'do' => 'diff',
                'rev2[0]' => $rev1,
                'rev2[1]' => $rev2,
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
