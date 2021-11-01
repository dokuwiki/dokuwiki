<?php

namespace dokuwiki\Ui;

use dokuwiki\ChangeLog\ChangeLog;

/**
 * DokuWiki Revisions Interface
 * parent class of PageRevisions and MediaRevisions
 *
 * @package dokuwiki\Ui
 */
abstract class Revisions extends Ui
{
    /* @var string */
    protected $id;   // page id or media id

    /* @var ChangeLog */
    protected $changelog; // PageChangeLog or MediaChangeLog object

    /**
     * Revisions Ui constructor
     *
     * @param string $id  page id or media id
     */
    public function __construct($id)
    {
        $this->id = $id;
        $this->setChangeLog();
    }

    /**
     * set class property changelog
     */
    abstract protected function setChangeLog();

    /**
     * Get revisions, and set correct pagination parameters (first, hasNext)
     *
     * @param int  $first
     * @param bool $hasNext
     * @return array  revisions to be shown in a pagenated list
     * @see also https://www.dokuwiki.org/devel:changelog
     */
    protected function getRevisions(&$first, &$hasNext)
    {
        global $conf;

        $changelog =& $this->changelog;
        $revisions = [];

        $currentRevInfo = $changelog->getCurrentRevisionInfo();
        if (!$currentRevInfo) return $revisions;

        $num = $conf['recent'];
        if ($first == 0) {
            $revisions[] = $currentRevInfo;
            $first += (int)($currentRevInfo['date'] == $changelog->lastRevision());
            $num = $num - 1;
        }
        /* we need to get one additional log entry to be able to
         * decide if this is the last page or is there another one.
         * see also Ui\Recent::getRecents()
         */
        $revlist = $changelog->getRevisions($first, $num + 1);
        if (count($revlist) == 0 && $first > 0) {
            // resets to zero if $first requested a too high number
            $first = 0;
            return $this->getRevisions($first, $hasNext);
        }

        // decide if this is the last page or is there another one
        $hasNext = false;
        if (count($revlist) > $num) {
            $hasNext = true;
            array_pop($revlist); // remove one additional log entry
        }

        // append each revison info array to the revisions
        foreach ($revlist as $rev) {
            $revisions[] = $changelog->getRevisionInfo($rev);
        }
        return $revisions;
    }

    /**
     * Navigation buttons for Pagenation (prev/next)
     *
     * @param int  $first
     * @param bool $hasNext
     * @param callable $callback returns array of hidden fields for the form button
     * @return string html
     */
    protected function navigation($first, $hasNext, $callback)
    {
        global $conf;

        $html = '<div class="pagenav">';
        $last = $first + $conf['recent'];
        if ($first > 0) {
            $first = max($first - $conf['recent'], 0);
            $html.= '<div class="pagenav-prev">';
            $html.= html_btn('newer', $this->id, "p", $callback($first));
            $html.= '</div>';
        }
        if ($hasNext) {
            $html.= '<div class="pagenav-next">';
            $html.= html_btn('older', $this->id, "n", $callback($last));
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
    public function getObjRevInfo(array $info)
    {
        return new class ($info) // anonymous class (objRevInfo)
        {
            protected $info;

            public function __construct(array $info)
            {
                $info['item'] = strrpos($info['id'], '.') ? 'media' : 'page';
                $info['current'] = $info['current'] ?? false;
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
                global $lang;
                $date = dformat($this->info['date']);
                if (($this->info['timestamp'] ?? '') == 'unknown') {
                    // externally deleted or older file restored
                    $date = preg_replace('/[0-9a-zA-Z]/','_', $date);
                }
                return '<span class="date">'. $date .'</span>';
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

                switch ($this->info['item']) {
                    case 'media': // media file revision
                        if ($this->info['current']) {
                            $href = media_managerURL(['image'=> $id, 'tab_details'=> 'view'], '&');
                            $html = '<a href="'.$href.'" class="wikilink1">'.$id.'</a>';
                        } elseif (file_exists(mediaFN($id, $rev))) {
                            $href = media_managerURL(['image'=> $id, 'tab_details'=> 'view', 'rev'=> $rev], '&');
                            $html = '<a href="'.$href.'" class="wikilink1">'.$id.'</a>';
                        } else {
                            $html = $id;
                        }
                        return $html;
                    case 'page': // page revision
                        $display_name = useHeading('navigation') ? hsc(p_get_first_heading($id)) : $id;
                        if (!$display_name) $display_name = $id;
                        if ($this->info['type'] == DOKU_CHANGE_TYPE_DELETE) {
                            // exteranlly deleted or older file restored
                            $href = wl($id, "", false, '&');
                            $html = '<a href="'.$href.'" class="wikilink2">'.$display_name.'</a>';
                        } elseif ($this->info['current'] || page_exists($id, $rev)) {
                            $href = wl($id, "rev=$rev", false, '&');
                            $html = '<a href="'.$href.'" class="wikilink1">'.$display_name.'</a>';
                        } else {
                            $html = $display_name;
                        }
                        return $html;
                }
                return '';
            }

            // icon difflink
            public function difflink()
            {
                global $lang;
                $id = $this->info['id'];
                $rev = $this->info['date'];

                switch ($this->info['item']) {
                    case 'media': // media file revision
                        if ($this->info['current'] || !file_exists(mediaFN($id, $rev))) {
                            $html = '<img src="'.DOKU_BASE.'lib/images/blank.gif" width="15" height="11" alt="" />';
                        } else {
                            $href = media_managerURL(['image'=> $id, 'rev'=> $rev, 'mediado'=>'diff'], '&');
                            $html = '<a href="'.$href.'" class="diff_link">'
                                  . '<img src="'.DOKU_BASE.'lib/images/diff.png" width="15" height="11"'
                                  . ' title="'. $lang['diff'] .'" alt="'.$lang['diff'] .'" />'
                                  . '</a> ';
                        }
                        return $html;
                    case 'page': // page revision
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
                return '';
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
