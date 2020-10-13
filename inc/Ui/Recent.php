<?php

namespace dokuwiki\Ui;

use dokuwiki\ChangeLog\MediaChangeLog;
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
     * @param int $first  skip the first n changelog lines
     * @param string $show_changes  type of changes to show; pages, mediafiles, or both
     */
    public function __construct($first = 0, $show_changes = 'both')
    {
        $this->first        = $first;
        $this->show_changes = $show_changes;
    }

    /**
     * Display recent changes
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     * @author Ben Coburn <btcoburn@silicodon.net>
     * @author Kate Arzamastseva <pshns@ukr.net>
     * @author Satoshi Sahara <sahara.satoshi@gmail.com>
     *
     * @return void
     */
    public function show()
    {
        global $conf, $lang;
        global $ID;

        // get recent items, and set correct pagenation parameters (first, hasNext)
        $first = $this->first;
        $hasNext = false;
        $recents = $this->getRecents($first, $hasNext);

        // print intro
        print p_locale_xhtml('recent');

        if (getNS($ID) != '') {
            print '<div class="level1"><p>'
                . sprintf($lang['recent_global'], getNS($ID), wl('', 'do=recent'))
                .'</p></div>';
        }

        // create the form
        $form = new Form(['id'=>'dw__recent', 'method'=>'GET', 'action'=> wl($ID), 'class'=>'changes']);
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
            $objRevInfo = $this->getObjRevInfo($recent);
            $class = ($recent['type'] === DOKU_CHANGE_TYPE_MINOR_EDIT) ? 'minor': '';
            $form->addTagOpen('li')->addClass($class);
            $form->addTagOpen('div')->addClass('li');
            $html = implode(' ', [
                $objRevInfo->itemIcon(),          // filetype icon
                $objRevInfo->editDate(),          // edit date and time
                $objRevInfo->difflink(),          // link to diffview icon
                $objRevInfo->revisionlink(),      // linkto revisions icon
                $objRevInfo->itemName(),          // name of page or media
                $objRevInfo->editSummary(),       // edit summary
                $objRevInfo->editor(),            // editor info
                $objRevInfo->sizechange(),        // size change indicator
            ]);
            $form->addHTML($html);
            $form->addTagClose('div');
            $form->addTagClose('li');
        }
        $form->addTagClose('ul');

        $form->addTagClose('div'); // close div class=no

        // provide navigation for pagenated recent list (of pages and/or media files)
        $form->addHTML($this->htmlNavigation($first, $hasNext));

        print $form->toHTML('Recent');
    }

    /**
     * Get recent items, and set correct pagenation parameters (first, hasNext)
     *
     * @param int  $first
     * @param bool $hasNext
     * @return array  recent items to be shown in a pagenated list
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
     * Navigation buttons for Pagenation (prev/next)
     *
     * @param int  $first
     * @param bool $hasNext
     * @return array  html
     */
    protected function htmlNavigation($first, $hasNext)
    {
        global $conf, $lang;

        $last = $first + $conf['recent'];
        $html = '<div class="pagenav">';
        if ($first > 0) {
            $first = max($first - $conf['recent'], 0);
            $html.= '<div class="pagenav-prev">';
            $html.= '<button type="submit" name="first['.$first.']" accesskey="n"'
                  . ' title="'.$lang['btn_newer'].' [N]" class="button show">'
                  . $lang['btn_newer']
                  . '</button>';
            $html.= '</div>';
        }
        if ($hasNext) {
            $html.= '<div class="pagenav-next">';
            $html.= '<button type="submit" name="first['.$last.']" accesskey="p"'
                  . ' title="'.$lang['btn_older'].' [P]" class="button show">'
                  . $lang['btn_older']
                  . '</button>';
            $html.= '</div>';
        }
        $html.= '</div>';
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
        $options = array(
                    'pages'      => $lang['pages_changes'],
                    'mediafiles' => $lang['media_changes'],
                    'both'       => $lang['both_changes'],
        );
        $form->addDropdown('show_changes', $options, $lang['changes_type'])
                ->val($this->show_changes)->addClass('quickselect');
        $form->addButton('do[recent]', $lang['btn_apply'])->attr('type','submit');
        $form->addTagClose('div');
    }

    /**
     * Returns instance of objRevInfo
     * @param array $info  Revision info structure of page or media
     * @return objRevInfo object (anonymous class)
     */
    protected function getObjRevInfo(array $info)
    {
        return new class ($info) // anonymous class (objRevInfo)
        {
            protected $info;

            public function __construct(array $info)
            {
                $this->info = $info;
            }

            // fileicon of the page or media file
            public function itemIcon()
            {
                $id = $this->info['id'];
                if (isset($this->info['media'])) {
                    $html = media_printicon($id);
                } else {
                    $html = '<img class="icon" src="'.DOKU_BASE.'lib/images/fileicons/file.png" alt="'.$id.'" />';
                }
                return $html;
            }

            // edit date and time of the page or media file
            public function editDate()
            {
                return '<span class="date">'. dformat($this->info['date']) .'</span>';
            }

            // edit summary
            public function editSummary()
            {
                return '<span class="sum">'.' – '. hsc($this->info['sum']).'</span>';
            }

            // editor of the page or media file
            public function editor()
            {
                $html = '<span class="user">';
                if ($this->info['user']) {
                    $html.= '<bdi>'. editorinfo($this->info['user']) .'</bdi>';
                    if (auth_ismanager()) $html.= ' <bdo dir="ltr">('. $this->info['ip'] .')</bdo>';
                } else {
                    $html.= '<bdo dir="ltr">'. $this->info['ip'] .'</bdo>';
                }
                $html.= '</span>';
                return $html;
            }

            // name of the page or media file
            public function itemName()
            {
                $id = $this->info['id'];
                if (isset($this->info['media'])) {
                    $href = media_managerURL(['tab_details'=>'view', 'image'=> $id, 'ns'=> getNS($id)], '&');
                    $class = file_exists(mediaFN($id)) ? 'wikilink1' : 'wikilink2';
                    $html = '<a href="'.$href.'" class="'.$class.'">'.$id.'</a>';
                } else {
                    $html = html_wikilink(':'.$id, (useHeading('navigation') ? null : $id));
                }
                return $html;
            }

            // icon difflink
            public function difflink()
            {
                global $lang;
                $id = $this->info['id'];

                if (isset($this->info['media'])) {
                    $revs = (new MediaChangeLog($id))->getRevisions(0, 1);
                    $diff = (count($revs) && file_exists(mediaFN($id)));
                    if ($diff) {
                        $href = media_managerURL(
                            ['tab_details'=>'history', 'mediado'=>'diff', 'image'=> $id, 'ns'=> getNS($id)], '&'
                        );
                    } else {
                        $href = '';
                    }
                } else {
                    $href = wl($id, "do=diff", false, '&');
                }

                if ($href) {
                    $html = '<a href="'.$href.'" class="diff_link">'
                          . '<img src="'.DOKU_BASE.'lib/images/diff.png" width="15" height="11"'
                          . ' title="'.$lang['diff'].'" alt="'.$lang['diff'].'" />'
                          . '</a>';
                } else {
                    $html = '<img src="'.DOKU_BASE.'lib/images/blank.gif" width="15" height="11" alt="" />';
                }
                return $html;
            }

            // icon revision link
            public function revisionlink()
            {
                global $lang;
                $id = $this->info['id'];
                if (isset($this->info['media'])) {
                    $href = media_managerURL(['tab_details'=>'history', 'image'=> $id, 'ns'=> getNS($id)], '&');
                } else {
                    $href = wl($id, "do=revisions", false, '&');
                }
                $html = '<a href="'.$href.'" class="revisions_link">'
                      . '<img src="'.DOKU_BASE.'lib/images/history.png" width="12" height="14"'
                      . ' title="'.$lang['btn_revs'].'" alt="'.$lang['btn_revs'].'" />'
                      . '</a>';
                return $html;
            }

            // size change
            public function sizeChange()
            {
                $class = 'sizechange';
                $value = filesize_h(abs($this->info['sizechange']));
                if ($this->info['sizechange'] > 0) {
                    $class .= ' positive';
                    $value = '+' . $value;
                } elseif ($this->info['sizechange'] < 0) {
                    $class .= ' negative';
                    $value = '-' . $value;
                } else {
                    $value = '±' . $value;
                }
                return '<span class="'.$class.'">'.$value.'</span>';
            }
        }; // end of anonymous class (objRevInfo)
    }

}
