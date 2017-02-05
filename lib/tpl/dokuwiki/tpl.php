<?php

namespace dokuwiki\template\dokuwiki;

/**
 * Class tpl
 *
 * Provides additional template functions for the dokuwiki template
 * @package dokuwiki\tpl\dokuwiki
 */
class tpl {

    static $icons = array(
        'default' => '00-default_checkbox-blank-circle-outline.svg',
        'edit' => '01-edit_pencil.svg',
        'create' => '02-create_pencil.svg',
        'draft' => '03-draft_android-studio.svg',
        'show' => '04-show_file-document.svg',
        'source' => '05-source_file-xml.svg',
        'revert' => '06-revert_replay.svg',
        'revs' => '07-revisions_history.svg',
        'backlink' => '08-backlink_link-variant.svg',
        'subscribe' => '09-subscribe_email-outline.svg',
        'top' => '10-top_arrow-up.svg',
        'mediaManager' => '11-mediamanager_folder-image.svg',
        'img_backto' => '12-back_arrow-left.svg',

    );

    /**
     * Return the HTML for one of the default actions
     *
     * Reimplements parts of tpl_actionlink
     *
     * @param string $action
     * @return string
     */
    static public function pageToolAction($action) {
        $data = tpl_get_action($action);
        if(!is_array($data)) return '';
        global $lang;

        if($data['id'][0] == '#') {
            $linktarget = $data['id'];
        } else {
            $linktarget = wl($data['id'], $data['params']);
        }
        $caption = $lang['btn_' . $data['type']];
        if(strpos($caption, '%s')) {
            $caption = sprintf($caption, $data['replacement']);
        }

        $svg = __DIR__ . '/images/tools/' . self::$icons[$data['type']];

        return self::pageToolItem(
            $linktarget,
            $caption,
            $svg,
            $data['accesskey']
        );
    }

    /**
     * Return the HTML for a page action
     *
     * Plugins could use this
     *
     * @param string $link The link
     * @param string $caption The label of the action
     * @param string $svg The icon to show
     * @param string $key Accesskey
     * @return string
     */
    static public function pageToolItem($link, $caption, $svg, $key = '') {
        $title = $caption;
        if($key) {
            $title .= ' [' . strtoupper($key) . ']';
            $key = 'accesskey="' . $key . '"';
        }

        $svg = inlinSVG($svg);
        if(!$svg) $svg = inlinSVG(__DIR__ . '/images/tools/' . self::$icons['default']);

        $out = '<li>';
        $out .= '<a href="' . $link . '" title="' . hsc($title) . '" rel="nofollow" ' . $key . '>';
        $out .= '<span>' . hsc($caption) . '</span>';
        $out .= $svg;
        $out .= '</li>';
        return $out;
    }
}
