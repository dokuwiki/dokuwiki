<?php

namespace dokuwiki\ChangeLog;

/**
 * Class RevisionInfo
 *
 * Provides methods to show Revision Information in DokuWiki Ui compoments:
 *  - Ui\Recent
 *  - Ui\PageRevisions
 *  - Ui\MediaRevisions
 */
class RevisionInfo
{
    protected $info;

    /**
     * Constructor
     *
     * @param array $info Revision Infomation structure with entries:
     *      - date:  unix timestamp
     *      - ip:    IPv4 or IPv6 address
     *      - type:  change type (log line type)
     *      - id:    page id
     *      - user:  user name
     *      - sum:   edit summary (or action reason)
     *      - extra: extra data (varies by line type)
     *      - sizechange: change of filesize
     *      additionally,
     *      - current:   (optional) whether current revison or not
     *      - timestamp: (optional) set only when external edits occurred
     */
    public function __construct(array $info)
    {
        $info['item'] = strrpos($info['id'], '.') ? 'media' : 'page';
        // current is always true for irems shown in Ui\Recents
        $info['current'] = $info['current'] ?? true;
        // revision info may have timestamp key when external edits occurred
        $info['timestamp'] = $info['timestamp'] ?? true;

        $this->info = $info;
    }

    /**
     * fileicon of the page or media file
     * used in [Ui\recent]
     *
     * @return string
     */
    public function showItemIcon()
    {
        $id = $this->info['id'];
        switch ($this->info['item']) {
            case 'media': // media file revision
                $html = media_printicon($id);
                break;
            case 'page': // page revision
                $html = '<img class="icon" src="'.DOKU_BASE.'lib/images/fileicons/file.png" alt="'.$id.'" />';
        }
        return $html;
    }

    /**
     * edit date and time of the page or media file
     * used in [Ui\recent, Ui\Revisions]
     *
     * @param bool $checkTimestamp  enable timestamp check, alter formatted string when timestamp is false
     * @return string
     */
    public function showEditDate($checkTimestamp = false)
    {
        global $lang;
        $formatted = dformat($this->info['date']);
        if ($checkTimestamp && $this->info['timestamp'] === false) {
            // exact date is unknown for item has externally deleted or older file restored
            // when unknown, alter formatted string "YYYY-mm-DD HH:MM" to "____-__-__ __:__"
            $formatted = preg_replace('/[0-9a-zA-Z]/','_', $formatted);
        }
        return '<span class="date">'. $formatted .'</span>';
    }

    /**
     * edit summary
     * used in [Ui\recent, Ui\Revisions]
     *
     * @return string
     */
    public function showEditSummary()
    {
        return '<span class="sum">'.' – '. hsc($this->info['sum']).'</span>';
    }

    /**
     * editor of the page or media file
     * used in [Ui\recent, Ui\Revisions]
     *
     * @return string
     */
    public function showEditor()
    {
        global $lang;
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

    /**
     * name of the page or media file
     * used in [Ui\recent, Ui\Revisions]
     *
     * @return string
     */
    public function showItemName()
    {
        $id = $this->info['id'];
        $rev = ($this->info['current']) ? '' : $this->info['date'];

        switch ($this->info['item']) {
            case 'media': // media file revision
                $params = ['tab_details'=> 'view', 'ns'=> getNS($id), 'image'=> $id];
                if ($rev) $params += ['rev'=> $rev];
                $href = media_managerURL($params, '&');
                $class = file_exists(mediaFN($id, $rev)) ? 'wikilink1' : 'wikilink2';
                return '<a href="'.$href.'" class="'.$class.'">'.$id.'</a>';
            case 'page': // page revision
                $params = ($rev)  ? '' : "rev=$rev";
                $href = wl($id, $params, false, '&');
                $display_name = useHeading('navigation') ? hsc(p_get_first_heading($id)) : $id;
                if (!$display_name) $display_name = $id;
                $class = page_exists($id, $rev) ? 'wikilink1' : 'wikilink2';
                if ($this->info['type'] == DOKU_CHANGE_TYPE_DELETE) {
                    $class = 'wikilink2';
                }
                return '<a href="'.$href.'" class="'.$class.'">'.$display_name.'</a>';
        }
    }

    /**
     * difflink icon in recents list
     * all items in the recents are "current" revision of the page or media
     *
     * @return string
     */
    public function showDifflinkRecent()
    {
        global $lang;
        $id = $this->info['id'];

        $href = '';
        switch ($this->info['item']) {
            case 'media': // media file revision
                $revs = (new MediaChangeLog($id))->getRevisions(0, 1);
                $showLink = (count($revs) && file_exists(mediaFN($id)));
                if ($showLink) {
                    $href = media_managerURL(
                        ['tab_details'=>'history', 'mediado'=>'diff', 'image'=> $id, 'ns'=> getNS($id)], '&'
                    );
                }
                break;
            case 'page': // page revision
                if($this->info['type'] !== DOKU_CHANGE_TYPE_CREATE) {
                    $href = wl($id, "do=diff", false, '&');
                }
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

    /**
     * difflink icon in revsions list
     * the icon does not displayed for the current revision
     *
     * @return string
     */
    public function showDifflinkRevision()
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

    /**
     * icon revision link
     * used in [Ui\recent]
     *
     * @return string
     */
    public function showRevisionlink()
    {
        global $lang, $conf;

        if (!actionOK('revisions')) {
            return '';  //FIXME check page, media 
        }

        $id = $this->info['id'];
        switch ($this->info['item']) {
            case 'media': // media file revision
                $href = media_managerURL(['tab_details'=>'history', 'image'=> $id, 'ns'=> getNS($id)], '&');
                break;
            case 'page': // page revision
                $href = wl($id, "do=revisions", false, '&');
        }
        return '<a href="'.$href.'" class="revisions_link">'
              . '<img src="'.DOKU_BASE.'lib/images/history.png" width="12" height="14"'
              . ' title="'.$lang['btn_revs'].'" alt="'.$lang['btn_revs'].'" />'
              . '</a>';
    }

    /**
     * size change
     * used in [Ui\recent, Ui\Revisions]
     *
     * @return string
     */
    public function showSizeChange()
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

    /**
     * current indicator, used in revison list
     * not used in Ui\Recents because recent items are always current one
     *
     * @return string
     */
    public function showCurrentIndicator()
    {
        global $lang;
        return ($this->info['current']) ? '('.$lang['current'].')' : '';
    }


}
