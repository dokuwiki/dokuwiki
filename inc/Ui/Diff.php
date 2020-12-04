<?php

namespace dokuwiki\Ui;

use dokuwiki\ChangeLog\PageChangeLog;
use dokuwiki\ChangeLog\MediaChangeLog;

/**
 * DokuWiki Diff Interface
 * parent class of PageDiff and MediaDiff
 *
 * @package dokuwiki\Ui
 */
abstract class Diff extends Ui
{
    /* @var string */
    protected $id;  // page id or media id

    /* @var array */
    protected $preference = [];

    /**
     * Diff Ui constructor
     *
     * @param string $id  page id or media id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Gets or Sets preference of the Ui\Diff object
     *
     * @param string|array $prefs  a key name or key-value pair(s)
     * @param mixed $value         value used when the first args is string
     * @return array|$this
     */
    public function preference($prefs = null, $value = null)
    {
        // set
        if (is_array($prefs)) {
            foreach ($prefs as $name => $value) {
                $this->preference[$name] = $value;
            }
            return $this;
        } elseif (is_string($prefs) && isset($value)) {
            $this->preference[$prefs] = $value;
            return $this;
        }
        // get
        return $this->preference;
    }


    /**
     * Get header of diff HTML
     *
     * @param ChangeLog $changelog  PageChangeLog or MediaChangeLog object
     * @param string $l_rev   Left revisions
     * @param string $r_rev   Right revision
     * @param string $id      Page id, if null $ID is used   // クラスプロパティを使用するべき
     * @param bool   $media   If it is for media files       // changelog object を渡せばよいのは？
     * @param bool   $inline  Return the header on a single line  // クラスプロパティを使用するべき
     * @return string[] HTML snippets for diff header
     */
    public function diffHead($changelog, $l_rev, $r_rev)
    {
        global $lang;

        // detect PageDiff or MediaDiff
        switch (get_class($changelog)) {
            case PageChangeLog::class :
                $media_or_wikiFN = 'wikiFN';
                $ml_or_wl = 'wl';
                $media = false;
                break;
            case MediaChangeLog::class :
                $media_or_wikiFN = 'mediaFN';
                $ml_or_wl = 'ml';
                $media = true;
                break;
        }

        $head_separator = ($this->preference['difftype'] === 'inline') ? ' ' : '<br />';
        $l_minor = $r_minor = '';

        // left side
        if (!$l_rev) {
            $l_head = '&mdash;';
        } else {
            $l_info   = $changelog->getRevisionInfo($l_rev);
            if ($l_info['user']) {
                $l_user = '<bdi>'.editorinfo($l_info['user']).'</bdi>';
                if (auth_ismanager()) $l_user .= ' <bdo dir="ltr">('.$l_info['ip'].')</bdo>';
            } else {
                $l_user = '<bdo dir="ltr">'.$l_info['ip'].'</bdo>';
            }
            $l_user  = '<span class="user">'.$l_user.'</span>';
            $l_sum   = ($l_info['sum']) ? '<span class="sum"><bdi>'.hsc($l_info['sum']).'</bdi></span>' : '';
            if ($l_info['type'] === DOKU_CHANGE_TYPE_MINOR_EDIT) $l_minor = 'class="minor"';

            $l_head_title = ($media) ? dformat($l_rev) : $this->id.' ['.dformat($l_rev).']';
            $l_head = '<bdi><a class="wikilink1" href="'.$ml_or_wl($this->id,"rev=$l_rev").'">'
                . $l_head_title.'</a></bdi>'.$head_separator.$l_user.' '.$l_sum;
        }

        // right side
        if ($r_rev) {
            $r_info   = $changelog->getRevisionInfo($r_rev);
            if ($r_info['user']) {
                $r_user = '<bdi>'.editorinfo($r_info['user']).'</bdi>';
                if (auth_ismanager()) $r_user .= ' <bdo dir="ltr">('.$r_info['ip'].')</bdo>';
            } else {
                $r_user = '<bdo dir="ltr">'.$r_info['ip'].'</bdo>';
            }
            $r_user = '<span class="user">'.$r_user.'</span>';
            $r_sum  = ($r_info['sum']) ? '<span class="sum"><bdi>'.hsc($r_info['sum']).'</bdi></span>' : '';
            if ($r_info['type'] === DOKU_CHANGE_TYPE_MINOR_EDIT) $r_minor = 'class="minor"';

            $r_head_title = ($media) ? dformat($r_rev) : $this->id.' ['.dformat($r_rev).']';
            $r_head = '<bdi><a class="wikilink1" href="'.$ml_or_wl($this->id,"rev=$r_rev").'">'
                . $r_head_title.'</a></bdi>'.$head_separator.$r_user.' '.$r_sum;
        } elseif ($_rev = @filemtime($media_or_wikiFN($this->id))) {
            $_info   = $changelog->getRevisionInfo($_rev);
            if ($_info['user']) {
                $_user = '<bdi>'.editorinfo($_info['user']).'</bdi>';
                if (auth_ismanager()) $_user .= ' <bdo dir="ltr">('.$_info['ip'].')</bdo>';
            } else {
                $_user = '<bdo dir="ltr">'.$_info['ip'].'</bdo>';
            }
            $_user = '<span class="user">'.$_user.'</span>';
            $_sum  = ($_info['sum']) ? '<span class="sum"><bdi>'.hsc($_info['sum']).'</span></bdi>' : '';
            if ($_info['type'] === DOKU_CHANGE_TYPE_MINOR_EDIT) $r_minor = 'class="minor"';

            $r_head_title = ($media) ? dformat($_rev) : $this->id.' ['.dformat($_rev).']';
            $r_head  = '<bdi><a class="wikilink1" href="'.$ml_or_wl($this->id).'">'
                . $r_head_title.'</a></bdi> '.'('.$lang['current'].')'.$head_separator.$_user.' '.$_sum;
        }else{
            $r_head = '&mdash; ('.$lang['current'].')';
        }

        return array($l_head, $r_head, $l_minor, $r_minor);
    }

}
