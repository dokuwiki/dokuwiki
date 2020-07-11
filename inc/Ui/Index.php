<?php

namespace dokuwiki\Ui;

use dokuwiki\Extension\Event;
use dokuwiki\Form\Form;

/**
 * DokuWiki Index Insterface
 *
 * @package dokuwiki\Ui
 */
class Index extends Ui
{
    /**
     * Display page index
     *
     * @author   Andreas Gohr <andi@splitbrain.org>
     *
     * @param string $ns
     * @return void
     */
        public function show($ns = '')
        {
        global $conf;
        global $ID;

        $ns  = cleanID($ns);
        if (empty($ns)){
            $ns = getNS($ID);
            if ($ns === false) $ns = '';
        }
        $ns  = utf8_encodeFN(str_replace(':', '/', $ns));

        // print intro
        print p_locale_xhtml('index');

        print '<div id="index__tree" class="index__tree">';

        $data = array();
        search($data, $conf['datadir'], 'search_index', array('ns' => $ns));
        print html_buildlist($data, 'idx', [$this,'formatListItem'], [$this,'taglListItem']);

        print '</div>'.DOKU_LF;
    }

    /**
     * Index item formatter
     *
     * User function for html_buildlist()
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     * @param array $item
     * @return string
     */
    public function formatListItem($item)    // RENAMED from html_list_index()
    {
        global $ID, $conf;

        // prevent searchbots needlessly following links
        $nofollow = ($ID != $conf['start'] || $conf['sitemap']) ? 'rel="nofollow"' : '';

        $html = '';
        $base = ':'.$item['id'];
        $base = substr($base, strrpos($base,':') +1);
        if ($item['type'] == 'd') {
            // FS#2766, no need for search bots to follow namespace links in the index
            $link = wl($ID, 'idx='. rawurlencode($item['id']));
            $html .= '<a href="'. $link .'" title="'. $item['id'] .'" class="idx_dir"' . $nofollow .'><strong>';
            $html .= $base;
            $html .= '</strong></a>';
        } else {
            // default is noNSorNS($id), but we want noNS($id) when useheading is off FS#2605
            $html .= html_wikilink(':'.$item['id'], useHeading('navigation') ? null : noNS($item['id']));
        }
        return $html;
    }

    /**
     * Index List item
     *
     * This user function is used in html_buildlist to build the
     * <li> tags for namespaces when displaying the page index
     * it gives different classes to opened or closed "folders"
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     * @param array $item
     * @return string html
     */
    public function taglListItem($item)    // RENAMED from html_li_index()
    {
        global $INFO;
        global $ACT;

        $class = '';
        $id = '';

        if ($item['type'] == 'f') {
            // scroll to the current item
            if (isset($INFO) && $item['id'] == $INFO['id'] && $ACT == 'index') {
                $id = ' id="scroll__here"';
                $class = ' bounce';
            }
            return '<li class="level'.$item['level'].$class.'" '.$id.'>';
        } elseif ($item['open']) {
            return '<li class="open">';
        } else {
            return '<li class="closed">';
        }
    }

}
