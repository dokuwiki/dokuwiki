<?php

namespace dokuwiki\ChangeLog;

/**
 * Class RevisionInfo
 *
 * Provides methods to show Revision Information in DokuWiki Ui components:
 *  - Ui\Recent
 *  - Ui\PageRevisions
 *  - Ui\MediaRevisions
 *  - Ui\PageDiff
 *  - Ui\MediaDiff
 */
class RevisionInfo
{
    public const MODE_PAGE = 'page';
    public const MODE_MEDIA = 'media';

    /* @var array */
    protected $info;

    /**
     * Constructor
     *
     * @param array $info Revision Information structure with entries:
     *      - date:  unix timestamp
     *      - ip:    IPv4 or IPv6 address
     *      - type:  change type (log line type)
     *      - id:    page id
     *      - user:  user name
     *      - sum:   edit summary (or action reason)
     *      - extra: extra data (varies by line type)
     *      - sizechange: change of filesize
     *      additionally,
     *      - current:   (optional) whether current revision or not
     *      - timestamp: (optional) set only when external edits occurred
     *      - mode:  (internal use) ether "media" or "page"
     */
    public function __construct($info = null)
    {
        if (!is_array($info) || !isset($info['id'])) {
            $info = [
                'mode' => self::MODE_PAGE,
                'date' => false,
            ];
        }
        $this->info = $info;
    }

    /**
     * Set or return whether this revision is current page or media file
     *
     * This method does not check exactly whether the revision is current or not. Instead,
     * set value of associated "current" key for internal use. Some UI element like diff
     * link button depend on relation to current page or media file. A changelog line does
     * not indicate whether it corresponds to current page or media file.
     *
     * @param bool $value true if the revision is current, otherwise false
     * @return bool
     */
    public function isCurrent($value = null)
    {
        return (bool) $this->val('current', $value);
    }

    /**
     * Return or set a value of associated key of revision information
     * but does not allow to change values of existing keys
     *
     * @param string $key
     * @param mixed $value
     * @return string|null
     */
    public function val($key, $value = null)
    {
        if (isset($value) && !array_key_exists($key, $this->info)) {
            // setter, only for new keys
            $this->info[$key] = $value;
        }
        if (array_key_exists($key, $this->info)) {
            // getter
            return $this->info[$key];
        }
        return null;
    }

    /**
     * Set extra key-value to the revision information
     * but does not allow to change values of existing keys
     * @param array $info
     * @return void
     */
    public function append(array $info)
    {
        foreach ($info as $key => $value) {
            $this->val($key, $value);
        }
    }


    /**
     * file icon of the page or media file
     * used in [Ui\recent]
     *
     * @return string
     */
    public function showFileIcon()
    {
        $id = $this->val('id');
        if ($this->val('mode') == self::MODE_MEDIA) {
            // media file revision
            return media_printicon($id);
        } elseif ($this->val('mode') == self::MODE_PAGE) {
            // page revision
            return '<img class="icon" src="' . DOKU_BASE . 'lib/images/fileicons/file.png" alt="' . $id . '" />';
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
        $formatted = dformat($this->val('date'));
        if ($checkTimestamp && $this->val('timestamp') === false) {
            // exact date is unknown for externally deleted file
            // when unknown, alter formatted string "YYYY-mm-DD HH:MM" to "____-__-__ __:__"
            $formatted = preg_replace('/[0-9a-zA-Z]/', '_', $formatted);
        }
        return '<span class="date">' . $formatted . '</span>';
    }

    /**
     * edit summary
     * used in [Ui\recent, Ui\Revisions]
     *
     * @return string
     */
    public function showEditSummary()
    {
        return '<span class="sum">' . ' – ' . hsc($this->val('sum')) . '</span>';
    }

    /**
     * editor of the page or media file
     * used in [Ui\recent, Ui\Revisions]
     *
     * @return string
     */
    public function showEditor()
    {
        if ($this->val('user')) {
            $html = '<bdi>' . editorinfo($this->val('user')) . '</bdi>';
            if (auth_ismanager()) {
                $html .= ' <bdo dir="ltr">(' . $this->val('ip') . ')</bdo>';
            }
        } else {
            $html = '<bdo dir="ltr">' . $this->val('ip') . '</bdo>';
        }
        return '<span class="user">' . $html . '</span>';
    }

    /**
     * name of the page or media file
     * used in [Ui\recent, Ui\Revisions]
     *
     * @return string
     */
    public function showFileName()
    {
        $id = $this->val('id');
        $rev = $this->isCurrent() ? '' : $this->val('date');

        if ($this->val('mode') == self::MODE_MEDIA) {
            // media file revision
            $params = ['tab_details' => 'view', 'ns' => getNS($id), 'image' => $id];
            if ($rev) $params += ['rev' => $rev];
            $href = media_managerURL($params, '&');
            $display_name = $id;
            $exists = file_exists(mediaFN($id, $rev));
        } elseif ($this->val('mode') == self::MODE_PAGE) {
            // page revision
            $params = $rev ? ['rev' => $rev] : [];
            $href = wl($id, $params, false, '&');
            $display_name = useHeading('navigation') ? hsc(p_get_first_heading($id)) : $id;
            if (!$display_name) $display_name = $id;
            $exists = page_exists($id, $rev);
        }

        if ($exists) {
            $class = 'wikilink1';
        } elseif ($this->isCurrent()) {
            //show only not-existing link for current page, which allows for directly create a new page/upload
            $class = 'wikilink2';
        } else {
            //revision is not in attic
            return $display_name;
        }
        if ($this->val('type') == DOKU_CHANGE_TYPE_DELETE) {
            $class = 'wikilink2';
        }
        return '<a href="' . $href . '" class="' . $class . '">' . $display_name . '</a>';
    }

    /**
     * Revision Title for PageDiff table headline
     *
     * @return string
     */
    public function showRevisionTitle()
    {
        global $lang;

        if (!$this->val('date')) return '&mdash;';

        $id = $this->val('id');
        $rev = $this->isCurrent() ? '' : $this->val('date');
        $params = ($rev) ? ['rev' => $rev] : [];

        // revision info may have timestamp key when external edits occurred
        $date = ($this->val('timestamp') === false)
            ? $lang['unknowndate']
            : dformat($this->val('date'));


        if ($this->val('mode') == self::MODE_MEDIA) {
            // media file revision
            $href = ml($id, $params, false, '&');
            $exists = file_exists(mediaFN($id, $rev));
        } elseif ($this->val('mode') == self::MODE_PAGE) {
            // page revision
            $href = wl($id, $params, false, '&');
            $exists = page_exists($id, $rev);
        }
        if ($exists) {
            $class = 'wikilink1';
        } elseif ($this->isCurrent()) {
            //show only not-existing link for current page, which allows for directly create a new page/upload
            $class = 'wikilink2';
        } else {
            //revision is not in attic
            return $id . ' [' . $date . ']';
        }
        if ($this->val('type') == DOKU_CHANGE_TYPE_DELETE) {
            $class = 'wikilink2';
        }
        return '<bdi><a class="' . $class . '" href="' . $href . '">' . $id . ' [' . $date . ']' . '</a></bdi>';
    }

    /**
     * diff link icon in recent changes list, to compare (this) current revision with previous one
     * all items in "recent changes" are current revision of the page or media
     *
     * @return string
     */
    public function showIconCompareWithPrevious()
    {
        global $lang;
        $id = $this->val('id');

        $href = '';
        if ($this->val('mode') == self::MODE_MEDIA) {
            // media file revision
            // unlike page, media file does not copied to media_attic when uploaded.
            // diff icon will not be shown when external edit occurred
            // because no attic file to be compared with current.
            $revs = (new MediaChangeLog($id))->getRevisions(0, 1);
            $showLink = (count($revs) && file_exists(mediaFN($id, $revs[0])) && file_exists(mediaFN($id)));
            if ($showLink) {
                $param = ['tab_details' => 'history', 'mediado' => 'diff', 'ns' => getNS($id), 'image' => $id];
                $href = media_managerURL($param, '&');
            }
        } elseif ($this->val('mode') == self::MODE_PAGE) {
            // page revision
            // when a page just created anyway, it is natural to expect no older revisions
            // even if it had once existed but deleted before. Simply ignore to check changelog.
            if ($this->val('type') !== DOKU_CHANGE_TYPE_CREATE) {
                $href = wl($id, ['do' => 'diff'], false, '&');
            }
        }

        if ($href) {
            return '<a href="' . $href . '" class="diff_link">'
                  . '<img src="' . DOKU_BASE . 'lib/images/diff.png" width="15" height="11"'
                  . ' title="' . $lang['diff'] . '" alt="' . $lang['diff'] . '" />'
                  . '</a>';
        } else {
            return '<img src="' . DOKU_BASE . 'lib/images/blank.gif" width="15" height="11" alt="" />';
        }
    }

    /**
     * diff link icon in revisions list, compare this revision with current one
     * the icon does not displayed for the current revision
     *
     * @return string
     */
    public function showIconCompareWithCurrent()
    {
        global $lang;
        $id = $this->val('id');
        $rev = $this->isCurrent() ? '' : $this->val('date');

        $href = '';
        if ($this->val('mode') == self::MODE_MEDIA) {
            // media file revision
            if (!$this->isCurrent() && file_exists(mediaFN($id, $rev))) {
                $param = ['mediado' => 'diff', 'image' => $id, 'rev' => $rev];
                $href = media_managerURL($param, '&');
            }
        } elseif ($this->val('mode') == self::MODE_PAGE) {
            // page revision
            if (!$this->isCurrent()) {
                $href = wl($id, ['rev' => $rev, 'do' => 'diff'], false, '&');
            }
        }

        if ($href) {
            return '<a href="' . $href . '" class="diff_link">'
                  . '<img src="' . DOKU_BASE . 'lib/images/diff.png" width="15" height="11"'
                  . ' title="' . $lang['diff'] . '" alt="' . $lang['diff'] . '" />'
                  . '</a>';
        } else {
            return '<img src="' . DOKU_BASE . 'lib/images/blank.gif" width="15" height="11" alt="" />';
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
        global $lang;

        if (!actionOK('revisions')) {
            return '';
        }

        $id = $this->val('id');
        if ($this->val('mode') == self::MODE_MEDIA) {
            // media file revision
            $param  = ['tab_details' => 'history', 'ns' => getNS($id), 'image' => $id];
            $href = media_managerURL($param, '&');
        } elseif ($this->val('mode') == self::MODE_PAGE) {
            // page revision
            $href = wl($id, ['do' => 'revisions'], false, '&');
        }
        return '<a href="' . $href . '" class="revisions_link">'
              . '<img src="' . DOKU_BASE . 'lib/images/history.png" width="12" height="14"'
              . ' title="' . $lang['btn_revs'] . '" alt="' . $lang['btn_revs'] . '" />'
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
        $value = filesize_h(abs($this->val('sizechange')));
        if ($this->val('sizechange') > 0) {
            $class .= ' positive';
            $value = '+' . $value;
        } elseif ($this->val('sizechange') < 0) {
            $class .= ' negative';
            $value = '-' . $value;
        } else {
            $value = '±' . $value;
        }
        return '<span class="' . $class . '">' . $value . '</span>';
    }

    /**
     * current indicator, used in revision list
     * not used in Ui\Recent because recent files are always current one
     *
     * @return string
     */
    public function showCurrentIndicator()
    {
        global $lang;
        return $this->isCurrent() ? '(' . $lang['current'] . ')' : '';
    }
}
