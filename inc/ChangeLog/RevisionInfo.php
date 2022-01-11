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
                return media_printicon($id);
            case 'page': // page revision
                return '<img class="icon" src="'.DOKU_BASE.'lib/images/fileicons/file.png" alt="'.$id.'" />';
        }
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
        $html = '';
        if ($this->info['user']) {
            $html.= '<bdi>'. editorinfo($this->info['user']) .'</bdi>';
            if (auth_ismanager()) $html .= ' <bdo dir="ltr">('. $this->info['ip'] .')</bdo>';
        } else {
            $html.= '<bdo dir="ltr">'. $this->info['ip'] .'</bdo>';
        }
        return '<span class="user">'. $html. '</span>';
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
                $display_name = $id;
                $class = file_exists(mediaFN($id, $rev)) ? 'wikilink1' : 'wikilink2';
                break;
            case 'page': // page revision
                $params = ($rev)  ? [] : ['rev'=> $rev];
                $href = wl($id, $params, false, '&');
                $display_name = useHeading('navigation') ? hsc(p_get_first_heading($id)) : $id;
                if (!$display_name) $display_name = $id;
                $class = page_exists($id, $rev) ? 'wikilink1' : 'wikilink2';
                if ($this->info['type'] == DOKU_CHANGE_TYPE_DELETE) {
                    $class = 'wikilink2';
                }
        }
        return '<a href="'.$href.'" class="'.$class.'">'.$display_name.'</a>';
    }

    /**
     * difflink icon in recents list, to compare (this) current revision with previous one
     * all items in "recent changes" are current revision of the page or media
     *
     * @return string
     */
    public function showIconCompareWithPrevious()
    {
        global $lang;
        $id = $this->info['id'];

        $href = '';
        switch ($this->info['item']) {
            case 'media': // media file revision
                // unlile page, media file does not copyed to attic when uploaded.
                $revs = (new MediaChangeLog($id))->getRevisions(0, 1);
                $showLink = (count($revs) && file_exists(mediaFN($id,$revs[0])) && file_exists(mediaFN($id)));
                if ($showLink) {
                    $param = ['tab_details'=>'history', 'mediado'=>'diff', 'ns'=> getNS($id), 'image'=> $id];
                    $href = media_managerURL($param, '&');
                }
                break;
            case 'page': // page revision
                // when a page just created anyway, it is natural to expect no older revisions
                // even if it had once existed but deleted before. Simply ignore to check changelog.
                if($this->info['type'] !== DOKU_CHANGE_TYPE_CREATE) {
                    $href = wl($id, ['do'=>'diff'], false, '&');
                }
        }

        if ($href) {
            return '<a href="'.$href.'" class="diff_link">'
                  .'<img src="'.DOKU_BASE.'lib/images/diff.png" width="15" height="11"'
                  .' title="'. $lang['diff'] .'" alt="'.$lang['diff'] .'" />'
                  .'</a>';
        } else {
            return '<img src="'.DOKU_BASE.'lib/images/blank.gif" width="15" height="11" alt="" />';
        }
    }

    /**
     * difflink icon in revsions list, compare this revision with current one
     * the icon does not displayed for the current revision
     *
     * @return string
     */
    public function showIconCompareWithCurrent()
    {
        global $lang;
        $id = $this->info['id'];
        $rev = $this->info['date'];

        $href = '';
        switch ($this->info['item']) {
            case 'media': // media file revision
                if (!$this->info['current'] && file_exists(mediaFN($id, $rev))) {
                    $param = ['mediado'=>'diff', 'image'=> $id, 'rev'=> $rev];
                    $href = media_managerURL($param, '&');
                }
                break;
            case 'page': // page revision
                if (!$this->info['current']) {
                    $href = wl($id, ['rev'=> $rev, 'do'=>'diff'], false, '&');
                }
        }

        if ($href) {
            return '<a href="'.$href.'" class="diff_link">'
                  .'<img src="'.DOKU_BASE.'lib/images/diff.png" width="15" height="11"'
                  .' title="'. $lang['diff'] .'" alt="'.$lang['diff'] .'" />'
                  .'</a>';
        } else {
            return '<img src="'.DOKU_BASE.'lib/images/blank.gif" width="15" height="11" alt="" />';
        }
    }

    /**
     * icon for revision action
     * used in [Ui\recent]
     *
     * @return string
     */
    public function showIconRevisions()
    {
        global $lang, $conf;

        if (!actionOK('revisions')) {
            return '';  //FIXME check page, media 
        }

        $id = $this->info['id'];
        switch ($this->info['item']) {
            case 'media': // media file revision
                $param  = ['tab_details'=>'history', 'ns'=> getNS($id), 'image'=> $id];
                $href = media_managerURL($param, '&');
                break;
            case 'page': // page revision
                $href = wl($id, ['do'=>'revisions'], false, '&');
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
