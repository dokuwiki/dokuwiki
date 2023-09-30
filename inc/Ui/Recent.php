<?php

namespace dokuwiki\Ui;

use dokuwiki\ChangeLog\MediaChangeLog;
use dokuwiki\ChangeLog\PageChangeLog;
use dokuwiki\ChangeLog\RevisionInfo;
use dokuwiki\Form\Form;

/**
 * DokuWiki Recent Interface
 *
 * @package dokuwiki\Ui
 */
class Recent extends Ui
{
    protected $first;
    protected $show_changes;

    /**
     * Recent Ui constructor
     *
     * @param int $first skip the first n changelog lines
     * @param string $show_changes type of changes to show; 'pages', 'mediafiles', or 'both'
     */
    public function __construct($first = 0, $show_changes = 'both')
    {
        $this->first = $first;
        $this->show_changes = $show_changes;
    }

    /**
     * Display recent changes
     *
     * @return void
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     * @author Ben Coburn <btcoburn@silicodon.net>
     * @author Kate Arzamastseva <pshns@ukr.net>
     * @author Satoshi Sahara <sahara.satoshi@gmail.com>
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    public function show()
    {
        global $conf, $lang;
        global $ID;

        // get recent items, and set correct pagination parameters (first, hasNext)
        $first = $this->first;
        $hasNext = false;
        $recents = $this->getRecents($first, $hasNext);

        // print intro
        echo p_locale_xhtml('recent');

        if (getNS($ID) != '') {
            echo '<div class="level1"><p>'
                . sprintf($lang['recent_global'], getNS($ID), wl('', 'do=recent'))
                . '</p></div>';
        }

        // create the form
        $form = new Form(['id' => 'dw__recent', 'method' => 'GET', 'action' => wl($ID), 'class' => 'changes']);
        $form->addTagOpen('div')->addClass('no');
        $form->setHiddenField('sectok', null);
        $form->setHiddenField('do', 'recent');
        $form->setHiddenField('id', $ID);

        // show dropdown selector, whether include not only recent pages but also recent media files?
        if ($conf['mediarevisions']) {
            $this->addRecentItemSelector($form);
        }

        // start listing of recent items
        $form->addTagOpen('ul');
        foreach ($recents as $recent) {
            // check possible external edition for current page or media
            $this->checkCurrentRevision($recent);

            $RevInfo = new RevisionInfo($recent);
            $RevInfo->isCurrent(true);
            $class = ($RevInfo->val('type') === DOKU_CHANGE_TYPE_MINOR_EDIT) ? 'minor' : '';
            $form->addTagOpen('li')->addClass($class);
            $form->addTagOpen('div')->addClass('li');
            $html = implode(' ', [
                $RevInfo->showFileIcon(),          // filetype icon
                $RevInfo->showEditDate(),          // edit date and time
                $RevInfo->showIconCompareWithPrevious(),    // link to diff view icon
                $RevInfo->showIconRevisions(),     // link to revisions icon
                $RevInfo->showFileName(),          // name of page or media
                $RevInfo->showEditSummary(),       // edit summary
                $RevInfo->showEditor(),            // editor info
                $RevInfo->showSizechange(),        // size change indicator
            ]);
            $form->addHTML($html);
            $form->addTagClose('div');
            $form->addTagClose('li');
        }
        $form->addTagClose('ul');

        $form->addTagClose('div'); // close div class=no

        // provide navigation for paginated recent list (of pages and/or media files)
        $form->addHTML($this->htmlNavigation($first, $hasNext));

        echo $form->toHTML('Recent');
    }

    /**
     * Get recent items, and set correct pagination parameters (first, hasNext)
     *
     * @param int $first
     * @param bool $hasNext
     * @return array  recent items to be shown in a paginated list
     *
     * @see also dokuwiki\Changelog::getRevisionInfo()
     */
    protected function getRecents(&$first, &$hasNext)
    {
        global $ID, $conf;

        $flags = 0;
        if ($this->show_changes == 'mediafiles' && $conf['mediarevisions']) {
            $flags = RECENTS_MEDIA_CHANGES;
        } elseif ($this->show_changes == 'pages') {
            $flags = 0;
        } elseif ($conf['mediarevisions']) {
            $flags = RECENTS_MEDIA_PAGES_MIXED;
        }

        /* we need to get one additionally log entry to be able to
         * decide if this is the last page or is there another one.
         * This is the cheapest solution to get this information.
         */
        $recents = getRecents($first, $conf['recent'] + 1, getNS($ID), $flags);
        if (count($recents) == 0 && $first != 0) {
            $first = 0;
            $recents = getRecents($first, $conf['recent'] + 1, getNS($ID), $flags);
        }

        $hasNext = false;
        if (count($recents) > $conf['recent']) {
            $hasNext = true;
            array_pop($recents); // remove extra log entry
        }
        return $recents;
    }

    /**
     * Check possible external deletion for current page or media
     *
     * To keep sort order in the recent list, we ignore externally modification.
     * It is not possible to know when external deletion had happened,
     * $info['date'] is to be incremented 1 second when such deletion detected.
     */
    protected function checkCurrentRevision(array &$info)
    {
        if ($info['mode'] == RevisionInfo::MODE_PAGE) {
            $changelog = new PageChangelog($info['id']);
        } else {
            $changelog = new MediaChangelog($info['id']);
        }
        if (!$changelog->isCurrentRevision($info['date'])) {
            $currentRevInfo = $changelog->getCurrentRevisionInfo();
            if ($currentRevInfo['type'] == DOKU_CHANGE_TYPE_DELETE) {
                // the page or media file was externally deleted, updated info because the link is already red
                // externally created and edited not updated because sorting by date is not worth so much changes
                $info = array_merge($info, $currentRevInfo);
            }
        }
        unset($changelog);
    }

    /**
     * Navigation buttons for Pagination (prev/next)
     *
     * @param int $first
     * @param bool $hasNext
     * @return string html
     */
    protected function htmlNavigation($first, $hasNext)
    {
        global $conf, $lang;

        $last = $first + $conf['recent'];
        $html = '<div class="pagenav">';
        if ($first > 0) {
            $first = max($first - $conf['recent'], 0);
            $html .= '<div class="pagenav-prev">';
            $html .= '<button type="submit" name="first[' . $first . ']" accesskey="n"'
                . ' title="' . $lang['btn_newer'] . ' [N]" class="button show">'
                . $lang['btn_newer']
                . '</button>';
            $html .= '</div>';
        }
        if ($hasNext) {
            $html .= '<div class="pagenav-next">';
            $html .= '<button type="submit" name="first[' . $last . ']" accesskey="p"'
                . ' title="' . $lang['btn_older'] . ' [P]" class="button show">'
                . $lang['btn_older']
                . '</button>';
            $html .= '</div>';
        }
        $html .= '</div>';
        return $html;
    }

    /**
     * Add dropdown selector of item types to the form instance
     *
     * @param Form $form
     * @return void
     */
    protected function addRecentItemSelector(Form $form)
    {
        global $lang;

        $form->addTagOpen('div')->addClass('changeType');
        $options = [
            'pages' => $lang['pages_changes'],
            'mediafiles' => $lang['media_changes'],
            'both' => $lang['both_changes']
        ];
        $form->addDropdown('show_changes', $options, $lang['changes_type'])
            ->val($this->show_changes)->addClass('quickselect');
        $form->addButton('do[recent]', $lang['btn_apply'])->attr('type', 'submit');
        $form->addTagClose('div');
    }
}
