<?php

namespace dokuwiki\Ui;

/**
 * DokuWiki Revisions Interface
 * parent class of PageRevisions and MediaRevisions
 *
 * @package dokuwiki\Ui
 */
abstract class Revisions extends Ui
{
    /* @var string */
    protected $id;

    /** 
     * Revisions Ui constructor
     *
     * @param string $id  id of page or media
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Get revisions, and set correct pagenation parameters (first, hasNext)
     *
     * @param int  $first
     * @param bool $hasNext
     * @return array  revisions to be shown in a pagenated list
     * @see also https://www.dokuwiki.org/devel:changelog
     */
    abstract protected function getRevisions(&$first, &$hasNext);

    /**
     * Navigation buttons for Pagenation (prev/next)
     *
     * @param int  $first
     * @param bool $hasNext
     * @param callable $callback returns array of hidden fields for the form button
     * @return array  html
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
