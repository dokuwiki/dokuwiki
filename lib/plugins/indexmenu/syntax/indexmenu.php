<?php

/**
 * Info Indexmenu: Show a customizable and sortable index for a namespace.
 *
 * @license     GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author      Samuele Tognini <samuele@samuele.netsons.org>
 *
 */

use dokuwiki\Extension\SyntaxPlugin;
use dokuwiki\File\PageResolver;
use dokuwiki\plugin\indexmenu\Search;
use dokuwiki\Ui\Index;

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_indexmenu_indexmenu extends SyntaxPlugin
{
    /**
     * What kind of syntax are we?
     */
    public function getType()
    {
        return 'substition';
    }

    /**
     * Behavior regarding the paragraph
     */
    public function getPType()
    {
        return 'block';
    }

    /**
     * Where to sort in?
     */
    public function getSort()
    {
        return 138;
    }

    /**
     * Connect pattern to lexer
     *
     * @param string $mode
     */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern('{{indexmenu>.+?}}', $mode, 'plugin_indexmenu_indexmenu');
    }

    /**
     * Handler to prepare matched data for the rendering process
     *
     * @param string $match The text matched by the patterns
     * @param int $state The lexer state for the match
     * @param int $pos The character position of the matched text
     * @param Doku_Handler $handler The Doku_Handler object
     * @return  array Return an array with all data you want to use in render
     *
     * @throws Exception
     */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        $theme = 'default'; // name of theme for images and additional css
        $level = -1; // requested depth of initial opened nodes, -1:all
        $max = 0; // number of levels loaded initially, rest should be loaded with ajax. (TODO actual default is 1)
        $maxAjax = 1; // number of levels loaded per ajax request
        $subNSs = [];
        $skipNsCombined = [];
        $skipFileCombined = [];
        $skipNs = '';
        $skipFile = '';
        /* @deprecated 2022-04-15 dTree only */
        $maxJs = 1;
        /* @deprecated 2022-04-15 dTree only. Fancytree always random id */
        $gen_id = 'random';
        /* @deprecated 2021-07-01 -- allow (temporary) switching between versions of the js treemenu */
        $jsVersion = 1; // 0:both, 1:dTree, 2:Fancytree
        /* @deprecated 2022-04-15 dTree only */
        $jsAjax = '';

        $defaultsStr = $this->getConf('defaultoptions');
        $defaults = explode(' ', $defaultsStr);

        $match = substr($match, 12, -2);
        //split namespace,level,theme
        [$nsStr, $optsStr] = array_pad(explode('|', $match, 2), 2, '');
        //split options
        $opts = explode(' ', $optsStr);

        //Context option
        $context = $this->hasOption($defaults, $opts, 'context');

        //split subnamespaces with their level of open/closed nodes
        // PREG_SPLIT_NO_EMPTY flag filters empty pieces e.g. due to multiple spaces
        $nsStrs = preg_split("/ /u", $nsStr, -1, PREG_SPLIT_NO_EMPTY);
        //skips i=0 because that becomes main $ns
        $counter = count($nsStrs);
        //skips i=0 because that becomes main $ns
        for ($i = 1; $i < $counter; $i++) {
            $subns_lvl = explode("#", $nsStrs[$i]);
            //context should parse this later in correct context
            if (!$context) {
                $subns_lvl[0] = $this->parseNs($subns_lvl[0]);
            }
            $subNSs[] = [
                $subns_lvl[0], //subns
                isset($subns_lvl[1]) && is_numeric($subns_lvl[1]) ? $subns_lvl[1] : -1 // level
            ];
        }
        //empty pieces were filtered
        if ($nsStrs === []) {
            $nsStrs[0] = '';
        }
        //split main requested namespace
        if (preg_match('/(.*)#(\S*)/u', $nsStrs[0], $matched_ns_lvl)) {
            //split level
            $ns = $matched_ns_lvl[1];
            if (is_numeric($matched_ns_lvl[2])) {
                $level = (int)$matched_ns_lvl[2];
            }
        } else {
            $ns = $nsStrs[0];
        }
        //context needs to be resolved later
        if (!$context) {
            $ns = $this->parseNs($ns);
        }

        //nocookie option (disable for uncached pages)
        /* @deprecated 2023-11 dTree only?, too complex */
        $nocookie = $context || $this->hasOption($defaults, $opts, 'nocookie');
        //noscroll option
        /** @deprecated 2023-11 dTree only and too complex */
        $noscroll = $this->hasOption($defaults, $opts, 'noscroll');
        //Open at current namespace option
        $navbar = $this->hasOption($defaults, $opts, 'navbar');
        //no namespaces  options
        $nons = $this->hasOption($defaults, $opts, 'nons');
        //no pages option
        $nopg = $this->hasOption($defaults, $opts, 'nopg');
        //disable toc preview
        $notoc = $this->hasOption($defaults, $opts, 'notoc');
        //disable the right context menu
        $nomenu = $this->hasOption($defaults, $opts, 'nomenu');
        //Main sort method
        $tsort = $this->hasOption($defaults, $opts, 'tsort');
        $dsort = $this->hasOption($defaults, $opts, 'dsort');
        if ($tsort) {
            $sort = 't';
        } elseif ($dsort) {
            $sort = 'd';
        } else {
            $sort = 0;
        }
        //sort directories in the same way as files
        $nsort = $this->hasOption($defaults, $opts, 'nsort');
        //group namespaces and pages both sorted separately with same sorting, or nogroup: mix them and sort together
        $group = !$this->hasOption($defaults, $opts, 'nogroup');

        //sort headpages up
        $hsort = $this->hasOption($defaults, $opts, 'hsort');
        //Metadata sort method
        if ($msort = $this->hasOption($defaults, $opts, 'msort')) {
            $msort = 'indexmenu_n';
        } elseif ($value = $this->getOption($defaultsStr, $optsStr, '/msort#(\S+)/u')) {
            $msort = str_replace(':', ' ', $value);
        }
        //reverse sort
        $rsort = $this->hasOption($defaults, $opts, 'rsort');

        if ($sort) $jsAjax .= "&sort=" . $sort;
        if ($msort) $jsAjax .= "&msort=" . $msort;
        if ($rsort) $jsAjax .= "&rsort=1";
        if ($nsort) $jsAjax .= "&nsort=1";
        if ($group) $jsAjax .= "&group=1";
        if ($hsort) $jsAjax .= "&hsort=1";
        if ($nopg) $jsAjax .= "&nopg=1";

        //javascript option
        $dir = '';
        //check defaults for js,js#theme, #theme
        if (!$js = in_array('js', $defaults)) {
            if (preg_match('/(?:^|\s)(js)?#(\S*)/u', $defaultsStr, $matched_js_theme) > 0) {
                if (!empty($matched_js_theme[1])) {
                    $js = true;
                }
                if (isset($matched_js_theme[2])) {
                    $dir = $matched_js_theme[2];
                }
            }
        }
        //check opts for nojs,#theme or js,js#theme
        if ($js) {
            if (in_array('nojs', $opts)) {
                $js = false;
            } elseif (preg_match('/(?:^|\s)(?:js)?#(\S*)/u', $optsStr, $matched_theme) > 0) {
                if (isset($matched_theme[1])) {
                    $dir = $matched_theme[1];
                }
            }
        } elseif ($js = in_array('js', $opts)) {
            //use theme from the defaults
        } elseif (preg_match('/(?:^|\s)js#(\S*)/u', $optsStr, $matched_theme) > 0) {
            $js = true;
            if (isset($matched_theme[1])) {
                $dir = $matched_theme[1];
            }
        }

        if ($js) {
            //exist theme?
            if (!empty($dir) && is_dir(DOKU_PLUGIN . "indexmenu/images/" . $dir)) {
                $theme = $dir;
            }

            //id generation method
            /* @deprecated 2023-11 not needed anymore */
            $gen_id = $this->getOption($defaultsStr, $optsStr, '/id#(\S+)/u');

            //max option: #n is no of lvls during initialization , #m levels retrieved per ajax request
            $matchPattern = '/max#(\d+)(?:$|\s+|#(\d+))/u';
            if ($matched_lvl_sublvl = $this->getOption($defaultsStr, $optsStr, $matchPattern, true)) {
                $max = $matched_lvl_sublvl[1];
                if (!empty($matched_lvl_sublvl[2])) {
                    $jsAjax .= "&max=" . $matched_lvl_sublvl[2];
                    $maxAjax = (int)$matched_lvl_sublvl[2];
                }
                //disable cookie to avoid javascript errors
                $nocookie = true;
            } else {
                $max = 0; //todo current default seems 1.
            }

            //max js option
            if ($maxjs_lvl = $this->getOption($defaultsStr, $optsStr, '/maxjs#(\d+)/u')) {
                $maxJs = $maxjs_lvl;
            }
            /* @deprecated 2021-07-01 -- allow (temporary) switching between versions of the js treemenu */
            $treeNew = $this->hasOption($defaults, $opts, 'treenew'); //overrides old and both
            /* @deprecated 2021-07-01 -- allow (temporary) switching between versions of the js treemenu */
            $treeOld = $this->hasOption($defaults, $opts, 'treeold'); //overrides both
            /* @deprecated 2021-07-01 -- allow (temporary) switching between versions of the js treemenu */
            $treeBoth = $this->hasOption($defaults, $opts, 'treeboth');
//            $jsVersion = $treeNew ? 2 : ($treeOld ? 1 : ($treeBoth ? 0 : $jsVersion));
            $jsVersion = $treeOld ? 1 : ($treeNew ? 2 : ($treeBoth ? 0 : $jsVersion));
//            error_log('$treeOld:'.$treeOld.'$treeNew:'.$treeNew.'$treeBoth:'.$treeBoth);

            if ($jsVersion !== 1) {
                //check for theme of fancytree (overrides old dTree theme eventually?)
                if (!empty($dir) && is_dir(DOKU_PLUGIN . 'indexmenu/scripts/fancytree/skin-' . $dir)) {
                    $theme = $dir;
                }
                // $theme='default' is later overwritten by 'win7'
            }
        }
        if (is_numeric($gen_id)) {
            /* @deprecated 2023-11 not needed anymore */
            $identifier = $gen_id;
        } elseif ($gen_id == 'ns') {
            $identifier = sprintf("%u", crc32($ns));
        } else {
            $identifier = uniqid(random_int(0, mt_getrandmax()));
        }

        //skip namespaces in index
        $skipNsCombined[] = $this->getConf('skip_index');
        if (preg_match('/skipns[+=](\S+)/u', $optsStr, $matched_skipns) > 0) {
            //first sign is: '+' (parallel to conf) or '=' (replace conf)
            $action = $matched_skipns[0][6];
            $index = 0;
            if ($action == '+') {
                $index = 1;
            }
            //directly used in search
            $skipNsCombined[$index] = $matched_skipns[1];
            //fancytree
            $skipNs = ($action == '+' ? '+' : '=') . $matched_skipns[1];
            //dTree
            $jsAjax .= "&skipns=" . utf8_encodeFN(($action == '+' ? '+' : '=') . $matched_skipns[1]);
        }
        //skip file
        $skipFileCombined[] = $this->getConf('skip_file');
        if (preg_match('/skipfile[+=](\S+)/u', $optsStr, $matched_skipfile) > 0) {
            //first sign is: '+' (parallel to conf) or '=' (replace conf)
            $action = $matched_skipfile[0][8];
            $index = 0;
            if ($action == '+') {
                $index = 1;
            }
            //directly used in search
            $skipFileCombined[$index] = $matched_skipfile[1];
            //fancytree
            $skipFile = ($action == '+' ? '+' : '=') . $matched_skipfile[1];
            //dTree
            $jsAjax .= "&skipfile=" . utf8_encodeFN(($action == '+' ? '+' : '=') . $matched_skipfile[1]);
        }

        //js options
        return [
            $ns, //0
            [ //1=js_dTreeOpts
                'theme' => $theme,
                'identifier' => $identifier, //deprecated
                'nocookie' => $nocookie, //deprecated
                'navbar' => $navbar,
                'noscroll' => $noscroll, //deprecated
                'maxJs' => $maxJs, //deprecated
                'notoc' => $notoc, //will be changed to default notoc
                'jsAjax' => $jsAjax, //deprecated
                'context' => $context, //only in handler()?
                'nomenu' => $nomenu //will be changed to default nomenu
            ],
            [ //2=sort
                'sort' => $sort,
                'msort' => $msort,
                'rsort' => $rsort,
                'nsort' => $nsort,
                'group' => $group,
                'hsort' => $hsort
            ],
            [ //3=opts
                'level' => $level, // requested depth of initial opened nodes, -1:all
                'nons' => $nons,
                'nopg' => $nopg,
                'subnss' => $subNSs, //only used for initial load
                'navbar' => $navbar, //add current ns to subNSs, for initial load
                'max' => $max, //number of levels loaded initially, rest should be loaded with ajax
                'maxajax' => $maxAjax, //number of levels loaded per ajax request
                'js' => $js,
                'skipnscombined' => $skipNsCombined,
                'skipfilecombined' => $skipFileCombined,
                'skipns' => $skipNs,
                'skipfile' => $skipFile,
                'headpage' => $this->getConf('headpage'),
                'hide_headpage' => $this->getConf('hide_headpage'),
                'theme' => $theme
            ],
            $jsVersion //4
        ];
    }

    /**
     * Looks if the default options and syntax options has the requested option
     *
     * @param array $defaultsOpts array of default options
     * @param array $opts array of options provided via syntax
     * @param string $optionName name of requested option
     * @return bool has $optionName?
     */
    private function hasOption($defaultsOpts, $opts, $optionName)
    {
        $name = $optionName;
        if (substr($optionName, 0, 2) == 'no') {
            $inverseName = substr($optionName, 2);
        } else {
            $inverseName = 'no' . $optionName;
        }

        if (in_array($name, $defaultsOpts)) {
            return !in_array($inverseName, $opts);
        } else {
            return in_array($name, $opts);
        }
    }

    /**
     * Looks for the value of the requested option in the default options and syntax options
     *
     * @param string $defaultsString default options string
     * @param string $optsString syntax options string
     * @param string $matchPattern pattern to search for
     * @param bool $multipleMatches if multiple returns array, otherwise the first match
     * @return string|array
     */
    private function getOption($defaultsString, $optsString, $matchPattern, $multipleMatches = false)
    {
        if (preg_match($matchPattern, $optsString, $match_o) > 0) {
            if ($multipleMatches) {
                return $match_o;
            } else {
                return $match_o[1];
            }
        } elseif (preg_match($matchPattern, $defaultsString, $match_d) > 0) {
            if ($multipleMatches) {
                return $match_d;
            } else {
                return $match_d[1];
            }
        }
        return false;
    }

    /**
     * Handles the actual output creation.
     *
     * @param string $format output format being rendered
     * @param Doku_Renderer $renderer the current renderer object
     * @param array $data data created by handler()
     * @return boolean rendered correctly?
     */
    public function render($format, Doku_Renderer $renderer, $data)
    {
        global $ACT;
        global $conf;
        global $INFO;

        $ns = $data[0];
        //theme, identifier, nocookie, navbar, noscroll, maxJs, notoc, jsAjax, context, nomenu
        $js_dTreeOpts = $data[1];
        //sort, msort, rsort, nsort, group, hsort
        $sort = $data[2];
        //opts for search(): level, nons, nopg, subnss, max, maxajax, js, skipns, skipfile, skipnscombined,
        //skipfilecombined, headpage, hide_headpage
        $opts = $data[3];
        /* @deprecated 2021-07-01 temporary */
        $jsVersion = $data[4];

        if ($format == 'xhtml') {
            if ($ACT == 'preview') {
                //Check user permission to display indexmenu in a preview page
                if (
                    $this->getConf('only_admins') &&
                    $conf['useacl'] &&
                    $INFO['perm'] < AUTH_ADMIN
                ) {
                    return false;
                }
                //disable cookies
                $js_dTreeOpts['nocookie'] = true;
            }
            if ($opts['js'] & $conf['defer_js']) {
                msg(
                    'Indexmenu Plugin: If you use the \'js\'-option of the indexmenu plugin, you have to '
                    . 'disable the <a href="https://www.dokuwiki.org/config:defer_js">\'defer_js\'</a>-setting. '
                    . 'This setting is temporary, in the future the indexmenu plugin will be improved.',
                    -1
                );
            }
            //Navbar with nojs
            if ($js_dTreeOpts['navbar'] && !$opts['js']) {
                if (!isset($ns)) {
                    $ns = ':';
                }
                //add ns of current page to let open these nodes (within the $ns), open only 1 level.
                $currentNS = getNS($INFO['id']);
                if ($currentNS !== false) {
                    $opts['subnss'][] = [$currentNS, 1];
                }
                $renderer->info['cache'] = false;
            }
            if ($js_dTreeOpts['context']) {
                //resolve ns and subns's relative to current wiki page (instead of sidebar)
                $ns = $this->parseNs($ns, $INFO['id']);
                foreach ($opts['subnss'] as $key => $value) {
                    $opts['subnss'][$key][0] = $this->parseNs($value[0], $INFO['id']);
                }
                $renderer->info['cache'] = false;
            }
            //build index
            $html = $this->buildHtmlIndexmenu($ns, $js_dTreeOpts, $sort, $opts, $jsVersion);
            //alternative if empty
            if (!@$html) {
                $html = $this->getConf('empty_msg');
                $html = str_replace('{{ns}}', cleanID($ns), $html);
                $html = p_render('xhtml', p_get_instructions($html), $info);
            }
            $renderer->doc .= $html;
            return true;
        } elseif ($format == 'metadata') {
            /** @var Doku_Renderer_metadata $renderer */
            if (!($js_dTreeOpts['navbar'] && !$opts['js']) && !$js_dTreeOpts['context']) {
                //this is an indexmenu page that needs the PARSER_CACHE_USE event trigger;
                $renderer->meta['indexmenu']['hasindexmenu'] = true;
            }
            //summary
            $renderer->doc .= (empty($ns) ? $conf['title'] : nons($ns)) . " index\n\n";
            unset($renderer->persistent['indexmenu']);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return the index
     *
     * @param string $ns
     * @param array $js_dTreeOpts entries: theme, identifier, nocookie, navbar, noscroll, maxJs, notoc, jsAjax, context,
     *                          nomenu
     * @param array $sort entries: sort, msort, rsort, nsort, group, hsort
     * @param array $opts entries of opts for search(): level, nons, nopg, nss, max, maxajax, js, skipns, skipfile,
     *                     skipnscombined, skipfilecombined, headpage, hide_headpage
     * @param int $jsVersion
     * @return bool|string return html for a nojs index and when enabled the js rendered index, otherwise false
     *
     * @author Samuele Tognini <samuele@samuele.netsons.org>
     */
    private function buildHtmlIndexmenu($ns, $js_dTreeOpts, $sort, $opts, $jsVersion)
    {
        $js_name = "indexmenu_" . $js_dTreeOpts['identifier'];
        //TODO temporary hack, to switch in Search between searchIndexmenuItemsNew() and searchIndexmenuItems()
        $opts['tempNew'] = false;
        $search = new Search($sort);
        $nodes = $search->search($ns, $opts);

        if (!$nodes) return false;

        // javascript index
        $output_js = '';
        if ($opts['js']) {
            $ns = str_replace('/', ':', $ns);

            // $jsversion: 0:both, 1:dTree, 2:Fancytree
            if ($jsVersion < 2) {
                $output_js .= $this->builddTree($nodes, $ns, $js_dTreeOpts, $js_name, $opts['max']);
            }
            if ($jsVersion !== 1) {
                $output_js .= $this->buildFancyTree($js_name, $ns, $opts, $sort);
            }

            //remove unwanted nodes from standard index
            $this->cleanNojsData($nodes);
        }
        $output = "\n";
        $output .= $this->buildNoJSTree($nodes, $js_name, $js_dTreeOpts['jsAjax']);
        $output .= $output_js;
        return $output;
    }

    private function buildNoJSTree($nodes, $js_name, $jsAjax)
    {
        // Nojs dokuwiki index
        //    extra div needed when index is first element in sidebar of dokuwiki template, template uses this to
        //    toggle sidebar the toggle interacts with hide needed for js option.
        $idx = new Index();
        return '<div>'
            . '<div id="nojs_' . $js_name . '" data-jsajax="' . utf8_encodeFN($jsAjax) . '" class="indexmenu_nojs">'
            . html_buildlist($nodes, 'idx', [$this, 'formatIndexmenuItem'], [$idx, 'tagListItem'])
            . '</div>'
            . '</div>';
    }

    private function buildFancyTree($js_name, $ns, $opts, $sort)
    {
        global $conf;
        //not needed, because directly retrieved from config
        unset($opts['headpage']);
        unset($opts['hide_headpage']);
        unset($opts['js']); //always true
        unset($opts['skipnscombined']);
        unset($opts['skipfilecombined']);

        /* @deprecated 2023-08-14 remove later */
        if ($opts['theme'] == 'default') {
            $opts['theme'] = 'win7';
        }
        $options = [
            'ns' => $ns,
            'opts' => $opts,
            'sort' => $sort,
            'contextmenu' => false,
            'startpage' => $conf['start'] //needed? or for contextmenu?
        ];
        return '<div id="tree2_' . $js_name . '" class="indexmenu_js2 skin-' . $opts['theme'] . '"'
            . 'data-options=\'' . json_encode($options) . '\'></div>';
    }

    /**
     * Build the browsable index of pages using javascript
     *
     * @param array $nodes array with items of the tree
     * @param string $ns requested namespace
     * @param array $js_dTreeOpts options for javascript renderer
     * @param string $js_name identifier for this index
     * @param int $max the node at $max level will retrieve all its child nodes through the AJAX mechanism
     * @return bool|string returns inline javascript or false
     *
     * @author  Samuele Tognini <samuele@samuele.netsons.org>
     * @author  Rene Hadler
     *
     * @deprecated 2023-11 will be replace by Fancytree
     */
    private function builddTree($nodes, $ns, $js_dTreeOpts, $js_name, $max)
    {
        global $conf;
        $hns = false;
        if (empty($nodes)) {
            return false;
        }

//TODO jsAjax is empty?? while max is set to 1
        // Render requested ns as root
        $headpage = $this->getConf('headpage');
        // if rootnamespace and headpage, then add startpage as headpage
        // TODO seems not logic, when desired use $conf[headpage]=:start: ??
        if (empty($ns) && !empty($headpage)) {
            $headpage .= ',' . $conf['start'];
        }
        $title = Search::getNamespaceTitle($ns, $headpage, $hns);
        if (empty($title)) {
            if (empty($ns)) {
                $title = hsc($conf['title']);
            } else {
                $title = $ns;
            }
        }
        // inline javascript
        $out = "<script type='text/javascript'>\n";
        $out .= "<!--//--><![CDATA[//><!--\n";
        $out .= "var $js_name = new dTree('" . $js_name . "','" . $js_dTreeOpts['theme'] . "');\n";
        //javascript config options
        $sepchar = idfilter(':', false);
        $out .= "$js_name.config.urlbase='" . substr(wl(":"), 0, -1) . "';\n";
        $out .= "$js_name.config.sepchar='" . $sepchar . "';\n";
        if ($js_dTreeOpts['notoc']) {
            $out .= "$js_name.config.toc=false;\n";
        }
        if ($js_dTreeOpts['nocookie']) {
            $out .= "$js_name.config.useCookies=false;\n";
        }
        if ($js_dTreeOpts['noscroll']) {
            $out .= "$js_name.config.scroll=false;\n";
        }
        //1 is default in dTree
        if ($js_dTreeOpts['maxJs'] > 1) {
            $out .= "$js_name.config.maxjs=" . $js_dTreeOpts['maxJs'] . ";\n";
        }
        if (!empty($js_dTreeOpts['jsAjax'])) {
            $out .= "$js_name.config.jsajax='" . utf8_encodeFN($js_dTreeOpts['jsAjax']) . "';\n";
        }

        //add root node
        $out .= $js_name . ".add('" . idfilter(cleanID($ns), false) . "',0,-1," . json_encode($title);
        if ($hns) {
            $out .= ",'" . idfilter(cleanID($hns), false) . "'";
        }
        $out .= ");\n";
        //add nodes
        [$nodesArray, $openNodes] = $this->builddTreeNodes($nodes, $js_name);
        $out .= $nodesArray;
        //write to document
        $out .= "document.write(" . $js_name . ");\n";
        //initialize index
        $out .= "jQuery(function(){" . $js_name . ".init(";
        $out .= (int)is_file(DOKU_PLUGIN . 'indexmenu/images/' . $js_dTreeOpts['theme'] . '/style.css') . ",";
        $out .= (int)$js_dTreeOpts['nocookie'] . ",";
        $out .= '"' . $openNodes . '",';
        $out .= (int)$js_dTreeOpts['navbar'] . ",";
        $out .= (int)$max;
        if ($js_dTreeOpts['nomenu']) {
            $out .= ",1";
        }
        $out .= ");});\n";

        $out .= "//--><!]]>\n";
        $out .= "</script>\n";
        return $out;
    }

    /**
     * Return array of javascript nodes and nodes to open.
     *
     * @param array $nodes array with items of the tree
     * @param string $js_name identifier for this index
     * @param boolean $noajax return as inline js (=true) or array for ajax response (=false)
     * @return array|bool returns array with
     *     - a string of the javascript nodes
     *     - and a string of space separated numbers of the opened nodes
     *    or false when no data provided
     *
     * @author  Samuele Tognini <samuele@samuele.netsons.org>
     *
     * @deprecated 2023-11 will be replace by Fancytree
     */
    public function builddTreeNodes($nodes, $js_name, $noajax = true)
    {
        if (empty($nodes)) {
            return false;
        }
        //Array of nodes to check
        $q = ['0'];
        //Current open node
        $currentOpenNode = 0;
        $out = '';
        $openNodes = '';
        if ($noajax) {
            $jscmd = $js_name . ".add";
            $separator = ";\n";
        } else {
            $jscmd = "new Array ";
            $separator = ",";
        }

        foreach ($nodes as $i => $node) {
            $i++;
            //Remove already processed nodes (greater level = lower level)
            while (isset($nodes[end($q) - 1]) && $node['level'] <= $nodes[end($q) - 1]['level']) {
                array_pop($q);
            }

            //till i found its father node
            if ($node['level'] == 1) {
                //root node
                $father = '0';
            } else {
                //Father node
                $father = end($q);
            }
            //add node and its options
            if ($node['type'] == 'd') {
                //Search the lowest open node of a tree branch in order to open it.
                if ($node['open']) {
                    if ($node['level'] < $nodes[$currentOpenNode]['level']) {
                        $currentOpenNode = $i;
                    } else {
                        $openNodes .= "$i ";
                    }
                }
                //insert node in last position
                $q[] = $i;
            }
            $out .= $jscmd . "('" . idfilter($node['id'], false) . "',$i," . $father
                . "," . json_encode($node['title']);
            //hns
            if ($node['hns']) {
                $out .= ",'" . idfilter($node['hns'], false) . "'";
            } else {
                $out .= ",0";
            }
            if ($node['type'] == 'd' || $node['type'] == 'l') {
                $out .= ",1";
            } else {
                $out .= ",0";
            }
            //MAX option
            if ($node['type'] == 'l') {
                $out .= ",1";
            } else {
                $out .= ",0";
            }
            $out .= ")" . $separator;
        }
        $openNodes = rtrim($openNodes, ' ');
        return [$out, $openNodes];
    }

    /**
     * Parse namespace request
     *
     * @param string $ns namespaceid
     * @param bool $id page id to resolve $ns relative to.
     * @return string id of namespace
     *
     * @author  Samuele Tognini <samuele@samuele.netsons.org>
     */
    public function parseNs($ns, $id = false)
    {
        if ($id === false) {
            global $ID;
            $id = $ID;
        }
        //Just for old releases compatibility, .. was an old version for : in the docs of indexmenu
        if ($ns == '..') {
            $ns = ":";
        }
        $ns = "$ns:arandompagehere";
        $resolver = new PageResolver($id);
        $ns = getNs($resolver->resolveId($ns));
        return $ns === false ? '' : $ns;
    }

    /**
     * Clean index data from unwanted nodes in nojs mode.
     *
     * @param array $nodes nodes of the tree
     * @return void
     *
     * @author  Samuele Tognini <samuele@samuele.netsons.org>
     */
    private function cleanNojsData(&$nodes)
    {
        $a = 0;
        foreach ($nodes as $i => $node) {
            //all entries before $a are unset
            if ($i < $a) {
                continue;
            }
            //closed node
            if ($node['type'] == "d" && !$node['open']) {
                $a = $i + 1;
                $level = $node['level'];
                //search and remove every lower and closed nodes
                while (isset($nodes[$a]) && $nodes[$a]['level'] > $level && !$nodes[$a]['open']) {
                    unset($nodes[$a]);
                    $a++;
                }
            }
        }
    }


    /**
     * Callback to print a Indexmenu item
     *
     * User function for @param array $item item described by array with at least the entries
     *          - id    page id/namespace id
     *          - type  'd', 'l'(directory which is not yet opened) or 'f'
     *          - open  is node open
     *          - title title of link
     *          - hns   page id of headpage of the namespace or false
     * @return string html of the content of a list item
     *
     * @author Samuele Tognini <samuele@samuele.netsons.org>
     * @author Rik Blok
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     * @see html_buildlist()
     */
    public function formatIndexmenuItem($item)
    {
        global $INFO;
        $ret = '';

        //namespace
        if ($item['type'] == 'd' || $item['type'] == 'l') {
            $markCurrentPage = false;

            $link = $item['id'];
            $more = 'idx=' . $item['id'];
            //namespace link
            if ($item['hns']) {
                $link = $item['hns'];
                $tagid = "indexmenu_idx_head";
                $more = '';
                //current page is shown?
                $markCurrentPage = $this->getConf('hide_headpage') && $item['hns'] == $INFO['id'];
            } else {
                //namespace without headpage
                $tagid = "indexmenu_idx";
                if ($item['open']) {
                    $tagid .= ' open';
                }
            }

            if ($markCurrentPage) {
                $ret .= '<span class="curid">';
            }
            $ret .= '<a href="' . wl($link, $more) . '" class="' . $tagid . '" data-wiki-id="'. $item['hns'] . '">'
                . $item['title']
                . '</a>';
            if ($markCurrentPage) {
                $ret .= '</span>';
            }
            return $ret;
        } else {
            //page link
            return html_wikilink(':' . $item['id']);
        }
    }
}
