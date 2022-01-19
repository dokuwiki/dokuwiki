<?php

namespace dokuwiki\Ui;

use dokuwiki\ChangeLog\PageChangeLog;
use dokuwiki\ChangeLog\RevisionInfo;
use dokuwiki\Form\Form;
use InlineDiffFormatter;
use TableDiffFormatter;

/**
 * DokuWiki PageDiff
 *
 * Display the differences in the two revisions of the page text
 * by compareing line by line.
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
    protected $Revision1;
    /* @var RevisionInfo newer revision */
    protected $Revision2;

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

            // revision info object of older file (left side)
            $this->Revision1 = new RevisionInfo($changelog->getCurrentRevisionInfo());
            $this->Revision1->append([
                'current' => true,
                'text' => rawWiki($this->id),
            ]);

            // revision info object of newer file (right side)
            $this->Revision2 = new RevisionInfo();
            $this->Revision2->append([
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

        // requested rev or rev2
        if (!isset($this->revisions)) {
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
                $REV = $this->revisions[0]; // store revision back in $REV
        }
    }

    /**
     * Prepare revision info of comparison pair
     */
    protected function preProcess()
    {
        global $lang;

        $changelog =& $this->changelog;
        list($rev1, $rev2) = $this->revisions;

        // create revision info object for older and newer sides
        // Revision1 : older, left side
        // Revision2 : newer, right side
        $this->Revision1 = new RevisionInfo($changelog->getRevisionInfo($rev1));
        $this->Revision2 = new RevisionInfo($changelog->getRevisionInfo($rev2));

        foreach ([$this->Revision1, $this->Revision2] as $Revision) {
            $isCurrent = $changelog->isCurrentRevision((int)$Revision->val('date'));
            $Revision->isCurrent($isCurrent);

            if ($Revision->val('type') == DOKU_CHANGE_TYPE_DELETE || empty($Revision->val('type'))) {
                $text = '';
            } else {
                $rev = $isCurrent ? '' : $Revision->val('date');
                $text = rawWiki($this->id, $rev);
            }
            $Revision->append(['text' => $text]);
        }

        if ($rev2 === false) {
            msg(sprintf($lang['page_nonexist_rev'],
                $this->id,
                wl($this->id, ['do'=>'edit']),
                $this->id), -1);
        } elseif (!$rev1 || $rev1 == $rev2) {
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

        if (!isset($this->Revision1, $this->Revision2)) {
            // retrieve form parameters: rev, rev2, difftype
            $this->handle();
            // prepare revision info of comparison pair, except PageConfrict or PageDraft
            $this->preProcess();
        }

        // revision information object
        [$Revision1, $Revision2] = [$this->Revision1, $this->Revision2];

        // revision title
        $rev1Title = trim($Revision1->showRevisionTitle() .' '. $Revision1->showCurrentIndicator());
        $rev1Supple = ($Revision1->val('date'))
            ? $Revision1->showEditSummary() .' '. $Revision1->showEditor()
            : '';

        if ($Revision2->val('extra') == 'compareWith') {
            $rev2Title = $lang['yours'];
            $rev2Supple = '';
        } else {
            $rev2Title = trim($Revision2->showRevisionTitle() .' '. $Revision2->showCurrentIndicator());
            $rev2Supple = ($Revision2->val('date'))
                ? $Revision2->showEditSummary() .' '. $Revision2->showEditor()
                : '';
        }

        // create difference engine object
        $Difference = new \Diff(
                explode("\n", $Revision1->val('text')),
                explode("\n", $Revision2->val('text'))
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
        echo '<table class="diff diff_'.$this->preference['difftype'] .'">';

        //navigation and header
        switch ($this->preference['difftype']) {
            case 'inline':
                $title1 = $rev1Title . ($rev1Supple ? '<br />'.$rev1Supple : '');
                $title2 = $rev2Title . ($rev2Supple ? '<br />'.$rev2Supple : '');
                if ($Revision2->val('extra') !== 'compareWith') {
                    echo '<tr>'
                        .'<td class="diff-lineheader">-</td>'
                        .'<td class="diffnav">'. $rev1Navi .'</td>'
                        .'</tr>';
                    echo '<tr>'
                        .'<th class="diff-lineheader">-</th>'
                        .'<th'.$classEditType($Revision1->val('type')).'>'. $title1 .'</th>'
                        .'</tr>';
                }
                echo '<tr>'
                    .'<td class="diff-lineheader">+</td>'
                    .'<td class="diffnav">'. $rev2Navi .'</td>'
                    .'</tr>';
                echo '<tr>'
                    .'<th class="diff-lineheader">+</th>'
                    .'<th'.$classEditType($Revision2->val('type')).'>'. $title2 .'</th>'
                    .'</tr>';
                // create formatter object
                $DiffFormatter = new InlineDiffFormatter();
                break;

            case 'sidebyside':
            default:
                $title1 = $rev1Title . ($rev1Supple ? ' '.$rev1Supple : '');
                $title2 = $rev2Title . ($rev2Supple ? ' '.$rev2Supple : '');
                if ($Revision2->val('extra') !== 'compareWith') {
                    // no revision navigation
                    echo '<tr>'
                        .'<td colspan="2" class="diffnav">'. $rev1Navi .'</td>'
                        .'<td colspan="2" class="diffnav">'. $rev2Navi .'</td>'
                        .'</tr>';
                }
                echo '<tr>'
                    .'<th colspan="2"'.$classEditType($Revision1->val('type')).'>'.$title1.'</th>'
                    .'<th colspan="2"'.$classEditType($Revision2->val('type')).'>'.$title2.'</th>'
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

        // revision information object
        [$Revision1, $Revision2] = [$this->Revision1, $this->Revision2];

        // no revisions selector for PageConflict or PageDraft
        if ($Revision2->val('extra') == 'compareWith') return;

        echo '<div class="diffoptions group">';

        // create the form to select difftype
        $form = new Form(['action' => wl($this->id)]);
        $form->setHiddenField('id', $this->id);
        $form->setHiddenField('rev2[0]', (int)$Revision1->val('date'));
        $form->setHiddenField('rev2[1]', (int)$Revision2->val('date'));
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
        if ($Revision1->val('date') && $Revision2->val('date')) {
            // link to exactly this view FS#2835
            $viewUrl = $this->diffViewlink('difflink', $Revision1->val('date'), $Revision2->val('date'));
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
        // revision information object
        [$Revision1, $Revision2] = [$this->Revision1, $this->Revision2];

        $changelog =& $this->changelog;

        if ($Revision2->val('extra') == 'compareWith') {
            // no revisions selector for PageConflict or PageDraft
            return array('', '');
        }

        // use timestamp for current revision, date may be false when revisions < 2
        [$rev1, $rev2] = [(int)$Revision1->val('date'), (int)$Revision2->val('date')];

        // retrieve revisions used in dropdown selectors
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
        // revision information object
        [$Revision1, $Revision2] = [$this->Revision1, $this->Revision2];

        [$rev1, $rev2] = [(int)$Revision1->val('date'), (int)$Revision2->val('date')];

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
            if (($side == 'older' && ($rev >= $rev2))
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
        // revision information object
        [$Revision1, $Revision2] = [$this->Revision1, $this->Revision2];

        [$rev1, $rev2] = [(int)$Revision1->val('date'), (int)$Revision2->val('date')];

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
