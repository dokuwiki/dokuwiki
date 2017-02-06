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
            array('accesskey' => $data['accesskey'])
        );
    }

    /**
     * Return the HTML for a page action
     *
     * Plugins may use this in TEMPLATE_PAGETOOLS_DISPLAY
     *
     * @param string $link The link
     * @param string $caption The label of the action
     * @param string $svg The icon to show
     * @param string[] $args HTML attributes for the item
     * @return string
     */
    static public function pageToolItem($link, $caption, $svg, $args = array()) {
        if(blank($args['title'])) {
            $args['title'] = $caption;
        }

        if(!blank($args['accesskey'])) {
            $args['title'] .= ' [' . strtoupper($args['accesskey']) . ']';
        }

        if(blank($args['rel'])) {
            $args['rel'] = 'nofollow';
        }

        $args['href'] = $link;

        $svg = inlineSVG($svg);
        if(!$svg) $svg = inlineSVG(__DIR__ . '/images/tools/' . self::$icons['default']);

        $attributes = buildAttributes($args, true);

        $out = "<a $attributes>";
        $out .= '<span>' . hsc($caption) . '</span>';
        $out .= $svg;
        $out .= '</a>';

        return $out;
    }
}
