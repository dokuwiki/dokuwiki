<?php

namespace dokuwiki\Ui;

use dokuwiki\ChangeLog\PageChangeLog;
use dokuwiki\ChangeLog\MediaChangeLog;
use dokuwiki\Form\Form;

/**
 * DokuWiki Revisions Interface
 *
 * @package dokuwiki\Ui
 */
class Revisions extends Ui
{
    protected $first;
    protected $media_id;

    /**
     * Revisions Ui constructor
     *
     * @param int $first  skip the first n changelog lines
     * @param bool|string $media_id  id of media, or false for current page
     */
    public function __construct($first = 0, $media_id = false)
    {
        $this->first    = $first;
        $this->media_id = $media_id;
    }

    /**
     * Display list of old revisions
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Ben Coburn <btcoburn@silicodon.net>
     * @author Kate Arzamastseva <pshns@ukr.net>
     * @author Satoshi Sahara <sahara.satoshi@gmail.com>
     *
     * @return void
     */
    public function show()
    {
        global $ID;

        if ($this->media_id) {
            return $this->showMediaRevisions($this->media_id);
        } else {
            return $this->showPageRevisions($ID);
        }
    }

    /**
     * Display a list of Media Revisions in the MediaManager
     *
     * @param string $id  media id
     * @return void
     */
    protected function showMediaRevisions($id)
    {
        global $lang;

        // get revisions, and set correct pagenation parameters (first, hasNext)
        $first   = $this->first;
        $hasNext = false;
        $revisions = $this->getRevisions($first, $hasNext);

        // create the form
        $form = new Form([
                'id' => 'page__revisions', // must not be "media__revisions"
                'action' => media_managerURL(['image' => $id], '&'),
                'class'  => 'changes',
        ]);
        $form->setHiddenField('mediado', 'diff'); // required for media revisions
        $form->addTagOpen('div')->addClass('no');

        // start listing
        $form->addTagOpen('ul');
        foreach ($revisions as $info) {
            $rev = $info['date'];
            $class = ($info['type'] === DOKU_CHANGE_TYPE_MINOR_EDIT) ? 'minor' : '';
            $form->addTagOpen('li')->addClass($class);
            $form->addTagOpen('div')->addClass('li');

            if (isset($info['current'])) {
               $form->addCheckbox('rev2[]')->val('current');
            } elseif (file_exists(mediaFN($id, $rev))) {
                $form->addCheckbox('rev2[]')->val($rev);
            } else {
                $form->addCheckbox('')->val($rev)->attr('disabled','disabled');
            }
            $form->addHTML(' ');

            $objRevInfo = $this->getObjRevInfo($info);
            $html = implode(' ', [
                $objRevInfo->editDate(),          // edit date and time
                $objRevInfo->difflink(),          // link to diffview icon
                $objRevInfo->itemName(),          // name of page or media
                '<div>',
                $objRevInfo->editSummary(),       // edit summary
                $objRevInfo->editor(),            // editor info
                html_sizechange($info['sizechange']), // size change indicator
                $objRevInfo->currentIndicator(),  // current indicator (only when k=1)
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

        print $form->toHTML('Revisions');

        // provide navigation for pagenated revision list (of pages and/or media files)
        print $this->htmlNavigation($id, $first, $hasNext);
    }

    /**
     * Display a list of Page Revisions
     *
     * @return void
     */
    protected function showPageRevisions($id)
    {
        global $lang;

        // get revisions, and set correct pagenation parameters (first, hasNext)
        $first   = $this->first;
        $hasNext = false;
        $revisions = $this->getRevisions($first, $hasNext);

        // print intro
        print p_locale_xhtml('revisions');

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
            $class = ($info['type'] === DOKU_CHANGE_TYPE_MINOR_EDIT) ? 'minor' : '';
            $form->addTagOpen('li')->addClass($class);
            $form->addTagOpen('div')->addClass('li');

            if (page_exists($id, $rev)) {
                $form->addCheckbox('rev2[]')->val($rev);
            } else {
                $form->addCheckbox('')->val($rev)->attr('disabled','disabled');
            }
            $form->addHTML(' ');

            $objRevInfo = $this->getObjRevInfo($info);
            $html = implode(' ', [
                $objRevInfo->editDate(),          // edit date and time
                $objRevInfo->difflink(),          // link to diffview icon
                $objRevInfo->itemName(),          // name of page or media
                $objRevInfo->editSummary(),       // edit summary
                $objRevInfo->editor(),            // editor info
                $objRevInfo->sizechange(),        // size change indicator
                $objRevInfo->currentIndicator(),  // current indicator (only when k=1)
            ]);
            $form->addHTML($html);
            $form->addTagClose('div');
            $form->addTagClose('li');
        }
        $form->addTagClose('ul');  // end of revision list

        // show button for diff view
        $form->addButton('do[diff]', $lang['diff2'])->attr('type', 'submit');

        $form->addTagClose('div'); // close div class=no

        print $form->toHTML('Revisions');

        // provide navigation for pagenated revision list (of pages and/or media files)
        print $this->htmlNavigation($id, $first, $hasNext);
    }


    /**
     * Get revisions, and set correct pagenation parameters (first, hasNext)
     *
     * @param int  $first
     * @param bool $hasNext
     * @return array  revisions to be shown in a pagenated list
     * @see also https://www.dokuwiki.org/devel:changelog
     */
    protected function getRevisions(&$first, &$hasNext)
    {
        global $INFO, $conf;

        if ($this->media_id) {
            $changelog = new MediaChangeLog($this->media_id);
        } else {
            $changelog = new PageChangeLog($INFO['id']);
        }

        $revisions = [];

        /* we need to get one additional log entry to be able to
         * decide if this is the last page or is there another one.
         * see also Ui\Recent::getRecents()
         */
        $revlist = $changelog->getRevisions($first, $conf['recent'] +1);
        if (count($revlist) == 0 && $first != 0) {
            $first = 0;
            $revlist = $changelog->getRevisions($first, $conf['recent'] +1);
        }
        $exists = ($this->media_id) ? file_exists(mediaFN($this->media_id)) : $INFO['exists'];
        if ($first === 0 && $exists) {
            // add current page or media as revision[0]
            if ($this->media_id) {
                $rev = filemtime(fullpath(mediaFN($this->media_id)));
                $changelog->setChunkSize(1024);
                $revinfo = $changelog->getRevisionInfo($rev) ?: array(
                        'date' => $rev,
                        'ip'   => null,
                        'type' => null,
                        'id'   => $this->media_id,
                        'user' => null,
                        'sum'  => null,
                        'extra' => null,
                        'sizechange' => null,
                );
                $revisions[] = $revinfo + array(
                        'media' => true,
                        'current' => true,
                );
            } else {
                if (isset($INFO['meta']['last_change'])) {
                    $type = $INFO['meta']['last_change']['type'];
                    $sizechange = $INFO['meta']['last_change']['sizechange'];
                } else {
                    $type = $sizechange = null;
                }

                $revisions[] = array(
                        'date' => $INFO['lastmod'],
                        'ip'   => null,
                        'type' => $type,
                        'id'   => $INFO['id'],
                        'user' => $INFO['editor'],
                        'sum'  => $INFO['sum'],
                        'extra' => null,
                        'sizechange' => $sizechange,
                        'current' => true,
                );
            }
        }

        // decide if this is the last page or is there another one
        $hasNext = false;
        if (count($revlist) > $conf['recent']) {
            $hasNext = true;
            array_pop($revlist); // remove one additional log entry
        }

        // append each revison info array to the revisions
        foreach ($revlist as $rev) {
            if ($this->media_id) {
                $revisions[] = $changelog->getRevisionInfo($rev) + array('media' => true);
            } else {
                $revisions[] = $changelog->getRevisionInfo($rev);
            }
        }
        return $revisions;
    }

    /**
     * Navigation buttons for Pagenation (prev/next)
     *
     * @param string $id  page id or media id
     * @param int  $first
     * @param bool $hasNext
     * @return array  html
     */
    protected function htmlNavigation($id, $first, $hasNext)
    {
        global $conf;

        $html = '<div class="pagenav">';
        $last = $first + $conf['recent'];
        if ($first > 0) {
            $first = max($first - $conf['recent'], 0);
            $html.= '<div class="pagenav-prev">';
            if ($this->media_id) {
                $html.= html_btn('newer', $id, "p", media_managerURL(['first' => $first], '&', false, true));
            } else {
                $html.= html_btn('newer', $id, "p" ,['do' => 'revisions', 'first' => $first]);
            }
            $html.= '</div>';
        }
        if ($hasNext) {
            $html.= '<div class="pagenav-next">';
            if ($this->media_id) {
                $html.= html_btn('older', $id, "n", media_managerURL(['first' => $last], '&', false, true));
            } else {
                $html.= html_btn('older', $id, "n", ['do' => 'revisions', 'first' => $last]);
            }
            $html.= '</div>';
        }
        $html.= '</div>';
        return $html;
    }

    /**
     * Returns instance of objRevInfo
     *
     * @param array $info  Revision info structure of a page or media file
     * @return objRevInfo object (anonymous class)
     */
    protected function getObjRevInfo(array $info)
    {
        return new class ($info) // anonymous class (objRevInfo)
        {
            protected $info;

            public function __construct(array $info)
            {
                if (!isset($info['current'])) {
                    $info['current'] = false;
                }
                $this->info = $info;
            }

            // current indicator
            public function currentIndicator()
            {
                global $lang;
                return ($this->info['current']) ? '('.$lang['current'].')' : '';
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
                // slightly different with display of Ui\Recent, i.e. external edit
                global $lang;
                $html = '<span class="user">';
                if (!$this->info['user'] && !$this->info['ip']) {
                    $html.= '('.$lang['external_edit'].')';
                } elseif ($this->info['user']) {
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
                // slightly different with display of Ui\Recent, i.e. revison may not exists
                $id = $this->info['id'];
                $rev = $this->info['date'];

                if (isset($this->info['media'])) {
                    // media file revision
                    if (isset($this->info['current'])) {
                        $href = media_managerURL(['image'=> $id, 'tab_details'=> 'view'], '&');
                        $html = '<a href="'.$href.'" class="wikilink1">'.$id.'</a>';
                    } elseif (file_exists(mediaFN($id, $rev))) {
                        $href = media_managerURL(['image'=> $id, 'tab_details'=> 'view', 'rev'=> $rev], '&');
                        $html = '<a href="'.$href.'" class="wikilink1">'.$id.'</a>';
                    } else {
                        $html = $id;
                    }
                    return $html;
                } else {
                    // page revision
                    $display_name = useHeading('navigation') ? hsc(p_get_first_heading($id)) : $id;
                    if (!$display_name) $display_name = $id;
                    if ($this->info['current'] || page_exists($id, $rev)) {
                        $href = wl($id, "rev=$rev", false, '&');
                        $html = '<a href="'.$href.'" class="wikilink1">'.$display_name.'</a>';
                    } else {
                        $html = $display_name;
                    }
                    return $html;
                }
            }

            // icon difflink
            public function difflink()
            {
                global $lang;
                $id = $this->info['id'];
                $rev = $this->info['date'];

                if (isset($this->info['media'])) {
                    // media file revision
                    if (isset($this->info['current']) || !file_exists(mediaFN($id, $rev))) {
                        $html = '<img src="'.DOKU_BASE.'lib/images/blank.gif" width="15" height="11" alt="" />';
                    } else {
                        $href = media_managerURL(['image'=> $id, 'rev'=> $rev, 'mediado'=>'diff'], '&');
                        $html = '<a href="'.$href.'" class="diff_link">'
                              . '<img src="'.DOKU_BASE.'lib/images/diff.png" width="15" height="11"'
                              . ' title="'. $lang['diff'] .'" alt="'.$lang['diff'] .'" />'
                              . '</a> ';
                    }
                    return $html;
                } else {
                    // page revision
                    if ($this->info['current'] || !page_exists($id, $rev)) {
                        $html = '<img src="'.DOKU_BASE.'lib/images/blank.gif" width="15" height="11" alt="" />';
                    } else {
                        $href = wl($id, "rev=$rev,do=diff", false, '&');
                        $html = '<a href="'.$href.'" class="diff_link">'
                              . '<img src="'.DOKU_BASE.'lib/images/diff.png" width="15" height="11"'
                              . ' title="'.$lang['diff'].'" alt="'.$lang['diff'].'" />'
                              . '</a>';
                    }
                    return $html;
                }
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
