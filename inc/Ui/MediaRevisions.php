<?php

namespace dokuwiki\Ui;

use dokuwiki\ChangeLog\MediaChangeLog;
use dokuwiki\ChangeLog\RevisionInfo;
use dokuwiki\Form\Form;
use InvalidArgumentException;

/**
 * DokuWiki MediaRevisions Interface
 *
 * @package dokuwiki\Ui
 */
class MediaRevisions extends Revisions
{
    /* @var MediaChangeLog */
    protected $changelog;

    /**
     * MediaRevisions Ui constructor
     *
     * @param string $id id of media
     */
    public function __construct($id)
    {
        if (!$id) {
            throw new InvalidArgumentException('media id should not be empty!');
        }
        parent::__construct($id);
    }

    /** @inheritdoc */
    protected function setChangeLog()
    {
        $this->changelog = new MediaChangeLog($this->id);
    }

    /**
     * Display a list of Media Revisions in the MediaManager
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
        global $lang;
        $changelog =& $this->changelog;

        // get revisions, and set correct pagination parameters (first, hasNext)
        if ($first === null) $first = -1;
        $hasNext = false;
        $revisions = $this->getRevisions($first, $hasNext);

        // create the form
        $form = new Form([
            'id' => 'page__revisions', // must not be "media__revisions"
            'action' => media_managerURL(['image' => $this->id], '&'),
            'class' => 'changes',
        ]);
        $form->setHiddenField('mediado', 'diff'); // required for media revisions
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
            } elseif (file_exists(mediaFN($this->id, $rev))) {
                $form->addCheckbox('rev2[]')->val($rev);
            } else {
                $form->addCheckbox('')->val($rev)->attr('disabled', 'disabled');
            }
            $form->addHTML(' ');

            $html = implode(' ', [
                $RevInfo->showEditDate(),          // edit date and time
                $RevInfo->showIconCompareWithCurrent(),  // link to diff view icon
                $RevInfo->showFileName(),          // name of page or media
                '<div>',
                $RevInfo->showEditSummary(),       // edit summary
                $RevInfo->showEditor(),            // editor info
                $RevInfo->showSizechange(),        // size change indicator
                $RevInfo->showCurrentIndicator(),  // current indicator (only when k=1)
                '</div>',
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
        echo $this->navigation(
            $first,
            $hasNext,
            static fn($n) => media_managerURL(['first' => $n], '&', false, true)
        );
    }
}
