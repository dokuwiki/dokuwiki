<?php

namespace dokuwiki\Ui;

/**
 * DokuWiki Index Interface
 *
 * @package dokuwiki\Ui
 */
class Index extends Ui
{
    protected $ns;

    /**
     * Index Ui constructor
     *
     * @param string $ns namespace
     */
    public function __construct($ns = '')
    {
        $this->ns = $ns;
    }


    /**
     * Display page index
     *
     * @return void
     * @author   Andreas Gohr <andi@splitbrain.org>
     *
     */
    public function show()
    {
        // print intro
        echo p_locale_xhtml('index');

        echo $this->sitemap();
    }

    /**
     * Build html of sitemap, unordered list of pages under the namespace
     *
     * @return string
     */
    public function sitemap()
    {
        global $conf;
        global $ID;

        $ns = cleanID($this->ns);
        if (empty($ns)) {
            $ns = getNS($ID);
            if ($ns === false) $ns = '';
        }
        $ns = utf8_encodeFN(str_replace(':', '/', $ns));
        $data = [];
        search($data, $conf['datadir'], 'search_index', ['ns' => $ns]);

        return '<div id="index__tree" class="index__tree">'
            . html_buildlist($data, 'idx', [$this, 'formatListItem'], [$this, 'tagListItem'])
            . '</div>';
    }

    /**
     * Index item formatter
     *
     * User function for html_buildlist()
     *
     * @param array $item
     * @return string
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     */
    public function formatListItem($item)    // RENAMED from html_list_index()
    {
        global $ID, $conf;

        // prevent searchbots needlessly following links
        $nofollow = ($ID != $conf['start'] || $conf['sitemap']) ? 'rel="nofollow"' : '';

        $html = '';
        $base = ':' . $item['id'];
        $base = substr($base, strrpos($base, ':') + 1);
        if ($item['type'] == 'd') {
            // FS#2766, no need for search bots to follow namespace links in the index
            $link = wl($ID, 'idx=' . rawurlencode($item['id']));
            $html .= '<a href="' . $link . '" title="' . $item['id'] . '" class="idx_dir"' . $nofollow . '><strong>';
            $html .= $base;
            $html .= '</strong></a>';
        } else {
            // default is noNSorNS($id), but we want noNS($id) when useheading is off FS#2605
            $html .= html_wikilink(':' . $item['id'], useHeading('navigation') ? null : noNS($item['id']));
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
     * @param array $item
     * @return string html
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     */
    public function tagListItem($item)    // RENAMED from html_li_index()
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
            return '<li class="level' . $item['level'] . $class . '" ' . $id . '>';
        } elseif ($item['open']) {
            return '<li class="open">';
        } else {
            return '<li class="closed">';
        }
    }
}
