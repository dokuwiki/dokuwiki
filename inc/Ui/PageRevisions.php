<?php

namespace dokuwiki\Ui;

use dokuwiki\ChangeLog\PageChangeLog;
use dokuwiki\ChangeLog\RevisionInfo;
use dokuwiki\Form\Form;

/**
 * DokuWiki PageRevisions Interface
 *
 * @package dokuwiki\Ui
 */
class PageRevisions extends Revisions
{
    /* @var PageChangeLog */
    protected $changelog;

    /**
     * PageRevisions Ui constructor
     *
     * @param string $id id of page
     */
    public function __construct($id = null)
    {
        global $INFO;
        if (!isset($id)) $id = $INFO['id'];
        parent::__construct($id);
    }

    /** @inheritdoc */
    protected function setChangeLog()
    {
        $this->changelog = new PageChangeLog($this->id);
    }

    /**
     * Display list of old revisions of the page
     *
     * @param int $first skip the first n changelog lines
     * @return void
     * @author Kate Arzamastseva <pshns@ukr.net>
     * @author Satoshi Sahara <sahara.satoshi@gmail.com>
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Ben Coburn <btcoburn@silicodon.net>
     */
    public function show($first = -1)
    {
        global $lang, $REV;
        $changelog =& $this->changelog;

        // get revisions, and set correct pagination parameters (first, hasNext)
        if ($first === null) $first = -1;
        $hasNext = false;
        $revisions = $this->getRevisions($first, $hasNext);

        // print intro
        echo p_locale_xhtml('revisions');

        // create the form
        $form = new Form([
            'id' => 'page__revisions',
            'class' => 'changes',
        ]);
        $form->addTagOpen('div')->addClass('no');

        // start listing
        $form->addTagOpen('ul');
        foreach ($revisions as $info) {
            $rev = $info['date'];

            $RevInfo = new RevisionInfo($info);
            $RevInfo->isCurrent($changelog->isCurrentRevision($rev));

            $class = ($RevInfo->val('type') === DOKU_CHANGE_TYPE_MINOR_EDIT) ? 'minor' : '';
            $form->addTagOpen('li')->addClass($class);
            $form->addTagOpen('div')->addClass('li');

            if ($RevInfo->isCurrent()) {
                $form->addCheckbox('rev2[]')->val($rev);
            } elseif ($rev == $REV) {
                $form->addCheckbox('rev2[]')->val($rev)->attr('checked', 'checked');
            } elseif (page_exists($this->id, $rev)) {
                $form->addCheckbox('rev2[]')->val($rev);
            } else {
                $form->addCheckbox('')->val($rev)->attr('disabled', 'disabled');
            }
            $form->addHTML(' ');

            $html = implode(' ', [
                $RevInfo->showEditDate(true),      // edit date and time
                $RevInfo->showIconCompareWithCurrent(),  // link to diff view icon
                $RevInfo->showFileName(),          // name of page or media
                $RevInfo->showEditSummary(),       // edit summary
                $RevInfo->showEditor(),            // editor info
                $RevInfo->showSizechange(),        // size change indicator
                $RevInfo->showCurrentIndicator(),  // current indicator (only when k=1)
            ]);
            $form->addHTML($html);
            $form->addTagClose('div');
            $form->addTagClose('li');
        }
        $form->addTagClose('ul');  // end of revision list

        // show button for diff view
        $form->addButton('do[diff]', $lang['diff2'])->attr('type', 'submit');

        $form->addTagClose('div'); // close div class=no

        echo $form->toHTML('Revisions');

        // provide navigation for paginated revision list (of pages and/or media files)
        echo $this->navigation($first, $hasNext, static fn($n) => ['do' => 'revisions', 'first' => $n]);
    }
}
