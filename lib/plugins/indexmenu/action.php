<?php

/**
 * Indexmenu Action Plugin:   Indexmenu Component.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Samuele Tognini <samuele@samuele.netsons.org>
 */

use dokuwiki\Extension\ActionPlugin;
use dokuwiki\Extension\Event;
use dokuwiki\Extension\EventHandler;
use dokuwiki\plugin\indexmenu\Search;
use dokuwiki\Ui\Index;

/**
 * Class action_plugin_indexmenu
 */
class action_plugin_indexmenu extends ActionPlugin
{
    /**
     * plugin should use this method to register its handlers with the dokuwiki's event controller
     *
     * @param EventHandler $controller DokuWiki's event controller object.
     */
    public function register(EventHandler $controller)
    {
        if ($this->getConf('only_admins')) {
            $controller->register_hook('IO_WIKIPAGE_WRITE', 'BEFORE', $this, 'removeSyntaxIfNotAdmin');
        }
        if ($this->getConf('page_index') != '') {
            $controller->register_hook('TPL_ACT_RENDER', 'BEFORE', $this, 'loadOwnIndexPage');
        }
        $controller->register_hook('DOKUWIKI_STARTED', 'AFTER', $this, 'extendJSINFO');
        $controller->register_hook('PARSER_CACHE_USE', 'BEFORE', $this, 'purgeCache');
        if ($this->getConf('show_sort')) {
            $controller->register_hook('TPL_CONTENT_DISPLAY', 'BEFORE', $this, 'showSortNumberAtTopOfPage');
        }
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'ajaxCalls');
        $controller->register_hook('TPL_METAHEADER_OUTPUT', 'BEFORE', $this, 'addStylesForSkins');
    }

    /**
     * Check if user has permission to insert indexmenu
     *
     * @param Event $event
     *
     * @author Samuele Tognini <samuele@samuele.netsons.org>
     */
    public function removeSyntaxIfNotAdmin(Event $event)
    {
        global $INFO;
        if (!$INFO['ismanager']) {
            $event->data[0][1] = preg_replace("/{{indexmenu(|_n)>.+?}}/", "", $event->data[0][1]);
        }
    }

    /**
     * Add additional info to $JSINFO
     *
     * @param Event $event
     *
     * @author Gerrit Uitslag <klapinklapin@gmail.com>
     * @author Samuele Tognini <samuele@samuele.netsons.org>
     */
    public function extendJSINFO(Event $event)
    {
        global $INFO, $JSINFO;

        $JSINFO['isadmin'] = (int)$INFO['isadmin'];
        $JSINFO['isauth'] = isset($INFO['userinfo']) ? (int) $INFO['userinfo'] : 0;
    }

    /**
     * Check for pages changes and eventually purge cache.
     *
     * @param Event $event
     *
     * @author Samuele Tognini <samuele@samuele.netsons.org>
     */
    public function purgeCache(Event $event)
    {
        global $ID;
        global $conf;
        global $INPUT;
        global $INFO;

        /** @var cache_parser $cache */
        $cache = &$event->data;

        if (!isset($cache->page)) return;
        //purge only xhtml cache
        if ($cache->mode != "xhtml") return;
        //Check if it is an indexmenu page
        if (!p_get_metadata($ID, 'indexmenu hasindexmenu')) return;

        $aclcache = $this->getConf('aclcache');
        if ($conf['useacl']) {
            $newkey = false;
            if ($aclcache == 'user') {
                //Cache per user
                if ($INPUT->server->str('REMOTE_USER')) {
                    $newkey = $INPUT->server->str('REMOTE_USER');
                }
            } elseif ($aclcache == 'groups') {
                //Cache per groups
                if (isset($INFO['userinfo']['grps'])) {
                    $newkey = implode('#', $INFO['userinfo']['grps']);
                }
            }
            if ($newkey) {
                $cache->key .= "#" . $newkey;
                $cache->cache = getCacheName($cache->key, $cache->ext);
            }
        }
        //Check if a page is more recent than purgefile.
        if (@filemtime($cache->cache) < @filemtime($conf['cachedir'] . '/purgefile')) {
            $event->preventDefault();
            $event->stopPropagation();
            $event->result = false;
        }
    }

    /**
     * Render a defined page as index.
     *
     * @param Event $event
     *
     * @author Samuele Tognini <samuele@samuele.netsons.org>
     */
    public function loadOwnIndexPage(Event $event)
    {
        if ('index' != $event->data) return;
        if (!file_exists(wikiFN($this->getConf('page_index')))) return;

        global $lang;

        echo '<h1><a id="index">' . $lang['btn_index'] . "</a></h1>\n";
        echo p_wiki_xhtml($this->getConf('page_index'));
        $event->preventDefault();
        $event->stopPropagation();
    }

    /**
     * Display the indexmenu sort number.
     *
     * @param Event $event
     *
     * @author Samuele Tognini <samuele@samuele.netsons.org>
     */
    public function showSortNumberAtTopOfPage(Event $event)
    {
        global $ID, $ACT, $INFO;
        if ($INFO['isadmin'] && $ACT == 'show') {
            if ($n = p_get_metadata($ID, 'indexmenu_n')) {
                echo '<div class="info">';
                echo $this->getLang('showsort') . $n;
                echo '</div>';
            }
        }
    }

    /**
     * Handles ajax requests for indexmenu
     *
     * @param Event $event
     */
    public function ajaxCalls(Event $event)
    {
        if ($event->data !== 'indexmenu') {
            return;
        }
        //no other ajax call handlers needed
        $event->stopPropagation();
        $event->preventDefault();

        global $INPUT;
        switch ($INPUT->str('req')) {
            case 'local':
                //list themes
                $this->getlocalThemes();
                break;

            case 'toc':
                //print toc preview
                if ($INPUT->has('id')) {
                    echo $this->printToc($INPUT->str('id'));
                }
                break;

            case 'index':
                //for dTree
                //retrieval of data of the extra nodes for the indexmenu (if ajax loading set with max#m(#n)
                if ($INPUT->has('idx')) {
                    echo $this->printIndex($INPUT->str('idx'));
                }
                break;

            case 'fancytree':
                //data for new index build with Fancytree
                $this->getDataFancyTree();
                break;
        }
    }

    /**
     * Handles ajax requests for FancyTree
     *
     * @return void
     */
    private function getDataFancyTree()
    {
        global $INPUT;

        $ns = $INPUT->str('ns', '');
        $ns = rtrim($ns, ':');
        //key of directory has extra : on the end
        $level = -1; //opened levels. -1=all levels open
        $max = 1; //levels to load by lazyloading. Before the default was 0. CHANGED to 1.
        $skipFileCombined = [];
        $skipNsCombined = [];

        if ($INPUT->int('max') > 0) {
            $max = $INPUT->int('max'); // max#n#m, if init: #n, otherwise #m
            $level = $max;
        }
        if ($INPUT->int('level', -10) >= -1) {
            $level = $INPUT->int('level');
        }
        $isInit = $INPUT->bool('init');

        $currentPage = $INPUT->str('currentpage');
        if ($isInit) {
            $subnss = $INPUT->arr('subnss');
            // if 'navbar' is enabled add current ns to list
            if ($INPUT->bool('navbar')) {
                $currentNs = getNS($currentPage);
                if ($currentNs !== false) {
                    $subnss[] = [$currentNs, 1];
                }
            }
            // alternative, via javascript.. https://wwwendt.de/tech/fancytree/doc/jsdoc/Fancytree.html#loadKeyPath
        } else {
            //not set via javascript at the moment.. ajax opens per level, so subnss has no use here
            $subnss = $INPUT->str('subnss');
            if ($subnss !== '') {
                $subnss = [[cleanID($subnss), 1]];
            }
        }

        $skipf = $INPUT->str('skipfile');
        $skipFileCombined[] = $this->getConf('skip_file');
        if (!empty($skipf)) {
            $index = 0;
            //prefix is '=' or '+'
            if ($skipf[0] == '+') {
                $index = 1;
            }
            $skipFileCombined[$index] = substr($skipf, 1);
        }
        $skipn = $INPUT->str('skipns');
        $skipNsCombined[] = $this->getConf('skip_index');
        if (!empty($skipn)) {
            $index = 0;
            //prefix is '=' or '+'
            if ($skipn[0] == '+') {
                $index = 1;
            }
            $skipNsCombined[$index] = substr($skipn, 1);
        }

        $opts = [
            //only set for init, lazy requests equal to max
            'level' => $level,
            //nons only needed for init as it has no nested nodes
            'nons' => $INPUT->bool('nons'),
            'nopg' => $INPUT->bool('nopg'),
            //init with complex array, empty if lazy loading
            'subnss' => $subnss,
            'max' => $max,
            'skipnscombined' => $skipNsCombined,
            'skipfilecombined' => $skipFileCombined,
            'headpage' => $this->getConf('headpage'),
            'hide_headpage' => $this->getConf('hide_headpage'),
        ];

        $sort = [
            'sort' => $INPUT->str('sort'),
            'msort' => $INPUT->str('msort'),
            'rsort' => $INPUT->bool('rsort'),
            'nsort' => $INPUT->bool('nsort'),
            'group' => $INPUT->bool('group'),
            'hsort' => $INPUT->bool('hsort')
        ];

        $opts['tempNew'] = true; //TODO temporary for recognizing treenew in the search function

        $search = new Search($sort);
        $data = $search->search($ns, $opts);
        $fancytreeData = $search->buildFancytreeData($data, $isInit, $currentPage, $opts['nopg']);

        //add eventually debug info
        if ($isInit) {
            //for lazy loading are other items than children not supported.
//            $fancytreeData['opts'] = $opts;
//            $fancytreeData['sort'] = $sort;
//            $fancytreeData['debug'] = $data;
        } else {
            //returns only children, therefore, add debug info to first child
//            $fancytreeData[0]['opts'] = $opts;
//            $fancytreeData[0]['sort'] = $sort;
//            $fancytreeData[0]['debug'] = $data;
        }

        header('Content-Type: application/json');
        echo json_encode($fancytreeData);
    }

    /**
     * Print a list of local themes
     *
     * @author Samuele Tognini <samuele@samuele.netsons.org>
     * @author Gerrit Uitslag <klapinklapin@gmail.com>
     */
    private function getlocalThemes()
    {
        header('Content-Type: application/json');

        $themebase = 'lib/plugins/indexmenu/images';

        $handle = @opendir(DOKU_INC . $themebase);
        $themes = [];
        while (false !== ($file = readdir($handle))) {
            if (
                is_dir(DOKU_INC . $themebase . '/' . $file)
                && $file != "."
                && $file != ".."
                && $file != "repository"
                && $file != "tmp"
                && $file != ".svn"
            ) {
                $themes[] = $file;
            }
        }
        closedir($handle);
        sort($themes);

        echo json_encode([
            'themebase' => $themebase,
            'themes' => $themes
        ]);
    }

    /**
     * Print a toc preview
     *
     * @param string $id
     * @return string
     *
     * @author Samuele Tognini <samuele@samuele.netsons.org>
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    private function printToc($id)
    {
        $id = cleanID($id);
        if (auth_quickaclcheck($id) < AUTH_READ) return '';

        $meta = p_get_metadata($id);
        $toc = $meta['description']['tableofcontents'] ?? [];

        if (count($toc) > 1) {
            //display ToC of two or more headings
            $out = $this->renderToc($toc);
        } else {
            //display page abstract
            $out = $this->renderAbstract($id, $meta);
        }
        return $out;
    }

    /**
     * Return the TOC rendered to XHTML
     *
     * @param $toc
     * @return string
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Gerrit Uitslag <klapinklapin@gmail.com>
     */
    private function renderToc($toc)
    {
        global $lang;
        $out = '<div class="tocheader">';
        $out .= $lang['toc'];
        $out .= '</div>';
        $out .= '<div class="indexmenu_toc_inside">';
        $out .= html_buildlist($toc, 'toc', [$this, 'formatIndexmenuListTocItem'], null, true);
        $out .= '</div>';
        return $out;
    }

    /**
     * Return the page abstract rendered to XHTML
     *
     * @param $id
     * @param array $meta by reference
     * @return string
     */
    private function renderAbstract($id, $meta)
    {
        $out = '<div class="tocheader">';
        $out .= '<a href="' . wl($id) . '">';
        $out .= $meta['title'] ? hsc($meta['title']) : hsc(noNS($id));
        $out .= '</a>';
        $out .= '</div>';
        if ($meta['description']['abstract']) {
            $out .= '<div class="indexmenu_toc_inside">';
            $out .= p_render('xhtml', p_get_instructions($meta['description']['abstract']), $info);
            $out .= '</div></div>';
        }
        return $out;
    }

    /**
     * Callback for html_buildlist
     *
     * @param $item
     * @return string
     */
    public function formatIndexmenuListTocItem($item)
    {
        global $INPUT;

        $id = cleanID($INPUT->str('id'));

        if (isset($item['hid'])) {
            $link = '#' . $item['hid'];
        } else {
            $link = $item['link'];
        }

        //prefix anchers with page id
        if ($link[0] == '#') {
            $link = wl($id, $link, false, '');
        }
        return '<a href="' . $link . '">' . hsc($item['title']) . '</a>';
    }

    /**
     * Print index nodes
     *
     * @param $ns
     * @return string
     *
     * @author Rene Hadler <rene.hadler@iteas.at>
     * @author Samuele Tognini <samuele@samuele.netsons.org>
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    private function printIndex($ns)
    {
        global $conf, $INPUT;
        $idxm = new syntax_plugin_indexmenu_indexmenu();
        $ns = $idxm->parseNs(rawurldecode($ns));
        $level = -1;
        $max = 0;
        $data = [];
        $skipfilecombined = [];
        $skipnscombined = [];

        if ($INPUT->int('max') > 0) {
            $max = $INPUT->int('max');
            $level = $max;
        }
        $nss = $INPUT->str('nss', '', true);
        $sort['sort'] = $INPUT->str('sort', '', true);
        $sort['msort'] = $INPUT->str('msort', '', true);
        $sort['rsort'] = $INPUT->bool('rsort', false, true);
        $sort['nsort'] = $INPUT->bool('nsort', false, true);
        $sort['group'] = $INPUT->bool('group', false, true);
        $sort['hsort'] = $INPUT->bool('hsort', false, true);
        $search = new Search($sort);
        $fsdir = "/" . utf8_encodeFN(str_replace(':', '/', $ns));

        $skipf = utf8_decodeFN($INPUT->str('skipfile'));
        $skipfilecombined[] = $this->getConf('skip_file');
        if (!empty($skipf)) {
            $index = 0;
            if ($skipf[0] == '+') {
                $index = 1;
            }
            $skipfilecombined[$index] = substr($skipf, 1);
        }
        $skipn = utf8_decodeFN($INPUT->str('skipns'));
        $skipnscombined[] = $this->getConf('skip_index');
        if (!empty($skipn)) {
            $index = 0;
            if ($skipn[0] == '+') {
                $index = 1;
            }
            $skipnscombined[$index] = substr($skipn, 1);
        }

        $opts = [
            'level' => $level,
            'nons' => $INPUT->bool('nons', false, true),
            'nss' => [[$nss, 1]],
            'max' => $max,
            'js' => false,
            'nopg' => $INPUT->bool('nopg', false, true),
            'skipnscombined' => $skipnscombined,
            'skipfilecombined' => $skipfilecombined,
            'headpage' => $idxm->getConf('headpage'),
            'hide_headpage' => $idxm->getConf('hide_headpage')
        ];
        if ($sort['sort'] || $sort['msort'] || $sort['rsort'] || $sort['hsort']) {
            $search->customSearch($data, $conf['datadir'], [$search, 'searchIndexmenuItems'], $opts, $fsdir);
        } else {
            search($data, $conf['datadir'], [$search, 'searchIndexmenuItems'], $opts, $fsdir);
        }

        $out = '';
        if ($INPUT->int('nojs') === 1) {
            $idx = new Index();
            $out_tmp = html_buildlist($data, 'idx', [$idxm, 'formatIndexmenuItem'], [$idx, 'tagListItem']);
            $out .= preg_replace('/<ul class="idx">(.*)<\/ul>/s', "$1", $out_tmp);
        } else {
            $nodes = $idxm->builddTreeNodes($data, '', false);
            $out = "ajxnodes = [";
            $out .= rtrim($nodes[0], ",");
            $out .= "];";
        }
        return $out;
    }

    /**
     * Add Js & Css after template is displayed
     *
     * @param Event $event
     */
    public function addStylesForSkins(Event $event)
    {

//        $event->data["link"][] = [
//            "type" => "text/css",
//            "rel" => "stylesheet",
//            "href" => DOKU_BASE . "lib/plugins/indexmenu/scripts/fancytree/... etc etc"
//        ];

//        $event->data["link"][] = [
//            "type" => "text/css",
//            "rel" => "stylesheet",
//            "href" => "//fonts.googleapis.com/icon?family=Material+Icons"
//        ];

//        $event->data["link"][] = [
//            "type" => "text/css",
//            "rel" => "stylesheet",
//            "href" => "//code.getmdl.io/1.3.0/material.indigo-pink.min.css"
//        ];
    }
}
