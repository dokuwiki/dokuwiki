<?php

namespace dokuwiki\plugin\indexmenu;

use dokuwiki\Utf8\Sort;

class Search
{
    /**
     * @var bool|string sort by t=title, d=date of creation, 0 if not set i.e. default page sort (old dTree..)
     */
    private $sort;
    /**
     * @var string 'indexmenu_n' or other key from the metadata structure
     */
    private $msort;
    /**
     * @var bool Reverse the sorting of pages, combined with $nsort also the namespaces
     */
    private $rsort;
    /**
     * @var bool also sorts the namespaces
     */
    private $nsort;
    /**
     * @var bool Group the namespaces and page and sort separately, or mix them and sort together
     */
    private $group;
    /**
     * @var bool Sort the headpages as defined by global config setting startpage to the top
     */
    private $hsort;

    /**
     * Search constructor.
     *
     * @param array $sort
     *   $sort['sort']
     *   $sort['msort']
     *   $sort['rsort']
     *   $sort['nsort']
     *   $sort['group']
     *   $sort['hsort'];
     */
    public function __construct($sort)
    {
        $this->sort = $sort['sort'];
        $this->msort = $sort['msort'];
        $this->rsort = $sort['rsort'];
        $this->nsort = $sort['nsort'];
        $this->group = $sort['group'];
        $this->hsort = $sort['hsort'];
    }

    /**
     * Build the data array for fancytree from search results
     *
     * @param array $data results from search
     * @param bool $isInit true if first level of nodes from tree, false if next levels
     * @param bool $currentPage current wikipage id
     * @param bool $isNopg if nopg is set
     * @return array
     */
    public function buildFancytreeData($data, $isInit, $currentPage, $isNopg)
    {
        if (empty($data)) return [];

        $children = [];
        $opts = [
            'currentPage' => $currentPage,
            'isParentLazy' => false,
            'nopg' => $isNopg
        ];
        $hasActiveNode = false;
        $this->makeNodes($data, -1, 0, $children, $hasActiveNode, $opts);

        if ($isInit) {
            $nodes['children'] = $children;
            return $nodes;
        } else {
            return $children;
        }
    }

    /**
     * Collects the children at the same level since last parsed item
     *
     * @param array $data results from search
     * @param int $indexLatestParsedItem
     * @param int $previousLevel level of parent
     * @param array $nodes by reference, here the child nodes are stored
     * @param bool $hasActiveNode active node must be unique, needs tracking
     * @param array $opts <ul>
     *      <li>$opts['currentPage'] string id of main article</li>
     *      <li>$opts['isParentLazy'] bool Used for recognizing the extra level below lazy nodes</li>
     *      <li>$opts['nopg'] bool needed for currentpage handling</li>
     * </ul>
     * @return int latest parsed item from data array
     */
    private function makeNodes(&$data, $indexLatestParsedItem, $previousLevel, &$nodes, &$hasActiveNode, $opts)
    {
        $i = 0;
        $counter = 0;
        foreach ($data as $i => $item) {
            //skip parsed items
            if ($i <= $indexLatestParsedItem) {
                continue;
            }

            if ($item['level'] < $previousLevel || $counter === 0 && $item['level'] == $previousLevel) {
                return $i - 1;
            }
            $node = [
                'title' => $item['title'],
                'key' => $item['id'] . ($item['type'] === 'f' ? '' : ':'), //ensure ns is unique
                'hns' => $item['hns'] //false if not available
            ];

            // f=file, d=directory, l=directory which is lazy loaded later
            if ($item['type'] == 'f') {
                // let php create url (considering rewriting etc)
                $node['url'] = wl($item['id']);

                //set current page to active
                if ($opts['currentPage'] == $item['id']) {
                    if (!$hasActiveNode) {
                        $node['active'] = true;
                        $hasActiveNode = true;
                    }
                }
            } else {
                // type: d/l
                $node['folder'] = true;
                // let php create url (considering rewriting etc)
                $node['url'] = $item['hns'] === false ? false : wl($item['hns']);
                if (!$item['hnsExists']) {
                    //change link color
                    $node['hnsNotExisting'] = true;
                }

                if ($item['open'] === true) {
                    $node['expanded'] = true;
                }

                $node['children'] = [];
                $indexLatestParsedItem = $this->makeNodes(
                    $data,
                    $i,
                    $item['level'],
                    $node['children'],
                    $hasActiveNode,
                    [
                        'currentPage' => $opts['currentPage'],
                        'isParentLazy' => $item['type'] === 'l',
                        'nopg' => $opts['nopg']
                    ]
                );

                // a lazy node, but because we have sometime no pages or nodes (due e.g. acl/hidden/nopg), it could be
                // empty. Therefore we did extra work by walking a level deeper and check here whether it has children
                if ($item['type'] === 'l') {
                    if (empty($node['children'])) {
                        //an empty lazy node, is not marked lazy
                        if ($opts['isParentLazy']) {
                            //a lazy node with a lazy parent has no children loaded, so stays always empty
                            //(these nodes are not really used, but only counted)
                            $node['lazy'] = true;
                            unset($node['children']);
                        }
                    } else {
                        //has children, so mark lazy
                        $node['lazy'] = true;
                        unset($node['children']); //do not keep, because these nodes do not know yet their child folders
                    }
                }

                //might be duplicated if hide_headpage is disabled, or with nopg and a :same: headpage
                //mark active after processing children, such that deepest level is activated
                if (
                    $item['hns'] === $opts['currentPage']
                    || $opts['nopg'] && getNS($opts['currentPage']) === $item['id']
                ) {
                    //with hide_headpage enabled, the parent node must be actived
                    //special: nopg has no pages, therefore, mark its parent node active
                    if (!$hasActiveNode) {
                        $node['active'] = true;
                        $hasActiveNode = true;
                    }
                }
            }

            if ($item['type'] === 'f' || !empty($node['children']) || isset($node['lazy']) || $item['hns'] !== false) {
                // add only files, non-empty folders, lazy-loaded or folder with only a headpage
                $nodes[] = $node;
            }

            $previousLevel = $item['level'];
            $counter++;
        }
        return $i;
    }


    /**
     * Search pages/folders depending on the given options $opts
     *
     * @param string $ns
     * @param array $opts<ul>
     *  <li>$opts['skipns'] string regexp matching namespaceids to skip (ignored)</li>
     *  <li>$opts['skipfile']  string regexp matching pageids to skip (ignored)</li>
     *  <li>$opts['skipnscombined'] array regexp matching namespaceids to skip</li>
     *  <li>$opts['skipfilecombined']  array regexp matching pageids to skip</li>
     *  <li>$opts['headpage']   string headpages options or pageids</li>
     *  <li>$opts['level']      int    desired depth of main namespace, -1 = all levels</li>
     *  <li>$opts['subnss']     array with entries: array(namespaceid,level) specifying namespaces with their own
     *                          number of opened levels</li>
     *  <li>$opts['nons']       bool   exclude namespace nodes</li>
     *  <li>$opts['max']        int    If initially closed, the node at max level will retrieve all its child nodes
     *                          through the AJAX mechanism</li>
     *  <li>$opts['nopg']       bool   exclude page nodes</li>
     *  <li>$opts['hide_headpage'] int don't hide (0) or hide (1)</li>
     *  <li>$opts['js']         bool   use js-render (only used for old 'searchIndexmenuItems')</li>
     * </ul>
     * @return array The results of the search
     */
    public function search($ns, $opts): array
    {
        global $conf;

        if (!empty($opts['tempNew'])) {
            //a specific callback for Fancytree
            $callback = [$this, 'searchIndexmenuItemsNew'];
        } else {
            $callback = [$this, 'searchIndexmenuItems'];
        }
        $dataDir = $conf['datadir'];
        $data = [];
        $fsDir = "/" . utf8_encodeFN(str_replace(':', '/', $ns));
        if ($this->sort || $this->msort || $this->rsort || $this->hsort) {
            $this->customSearch($data, $dataDir, $callback, $opts, $fsDir);
        } else {
            search($data, $dataDir, $callback, $opts, $fsDir);
        }
        return $data;
    }

    /**
     * Callback that adds an item of namespace/page to the browsable index, if it fits in the specified options
     *
     * @param array $data Already collected nodes
     * @param string $base Where to start the search, usually this is $conf['datadir']
     * @param string $file Current file or directory relative to $base
     * @param string $type Type either 'd' for directory or 'f' for file
     * @param int $lvl Current recursion depth
     * @param array $opts Option array as given to search():<ul>
     *   <li>$opts['skipns'] string regexp matching namespaceids to skip (ignored),</li>
     *   <li>$opts['skipfile'] string regexp matching pageids to skip (ignored),</li>
     *   <li>$opts['skipnscombined'] array regexp matching namespaceids to skip,</li>
     *   <li>$opts['skipfilecombined'] array regexp matching pageids to skip,</li>
     *   <li>$opts['headpage'] string headpages options or pageids,</li>
     *   <li>$opts['level'] int desired depth of main namespace, -1 = all levels,</li>
     *   <li>$opts['subnss'] array with entries: array(namespaceid,level) specifying namespaces with their own number
     *                       of opened levels,</li>
     *   <li>$opts['nons'] bool Exclude namespace nodes,</li>
     *   <li>$opts['max'] int If initially closed, the node at max level will retrieve all its child nodes through
     *                    the AJAX mechanism,</li>
     *   <li>$opts['nopg'] bool Exclude page nodes,</li>
     *   <li>$opts['hide_headpage'] int don't hide (0) or hide (1),</li>
     *   <li>$opts['js'] bool use js-render</li>
     * </ul>
     * @return bool if this directory should be traversed (true) or not (false)
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * modified by Samuele Tognini <samuele@samuele.netsons.org>
     */
    public function searchIndexmenuItems(&$data, $base, $file, $type, $lvl, $opts)
    {
        global $conf;

        $hns = false;
        $isOpen = false;
        $title = null;
        $skipns = $opts['skipnscombined'];
        $skipfile = $opts['skipfilecombined'];
        $headpage = $opts['headpage'];
        $id = pathID($file);

        if ($type == 'd') {
            // Skip folders in plugin conf
            foreach ($skipns as $skipn) {
                if (!empty($skipn) && preg_match($skipn, $id)) {
                    return false;
                }
            }
            //check ACL (for sneaky_index namespaces too).
            if ($conf['sneaky_index'] && auth_quickaclcheck($id . ':') < AUTH_READ) return false;

            //Open requested level
            if ($opts['level'] > $lvl || $opts['level'] == -1) {
                $isOpen = true;
            }
            //Search optional subnamespaces with
            if (!empty($opts['subnss'])) {
                $subnss = $opts['subnss'];
                $counter = count($subnss);
                for ($a = 0; $a < $counter; $a++) {
                    if (preg_match("/^" . $id . "($|:.+)/i", $subnss[$a][0], $match)) {
                        //It contains a subnamespace
                        $isOpen = true;
                    } elseif (preg_match("/^" . $subnss[$a][0] . "(:.*)/i", $id, $match)) {
                        //It's inside a subnamespace, check level
                        // -1 is open all, otherwise count number of levels in the remainer of the pageid
                        // (match[0] is always prefixed with :)
                        if ($subnss[$a][1] == -1 || substr_count($match[1], ":") < $subnss[$a][1]) {
                            $isOpen = true;
                        } else {
                            $isOpen = false;
                        }
                    }
                }
            }

            //decide if it should be traversed
            if ($opts['nons']) {
                return $isOpen; // in nons, level is only way to show/hide nodes (in nons nodes are not expandable)
            } elseif ($opts['max'] > 0 && !$isOpen && $lvl >= $opts['max']) {
                //Stop recursive searching
                $shouldBeTraversed = false;
                //change type
                $type = "l";
            } elseif ($opts['js']) {
                $shouldBeTraversed = true; //TODO if js tree, then traverse deeper???
            } else {
                $shouldBeTraversed = $isOpen;
            }
            //Set title and headpage
            $title = static::getNamespaceTitle($id, $headpage, $hns);
            // when excluding page nodes: guess a headpage based on the headpage setting
            if ($opts['nopg'] && $hns === false) {
                $hns = $this->guessHeadpage($headpage, $id);
            }
        } else {
            //Nopg. Dont show pages
            if ($opts['nopg']) return false;

            $shouldBeTraversed = true;
            //Nons.Set all pages at first level
            if ($opts['nons']) {
                $lvl = 1;
            }
            //don't add
            if (substr($file, -4) != '.txt') return false;
            //check hiddens and acl
            if (isHiddenPage($id) || auth_quickaclcheck($id) < AUTH_READ) return false;
            //Skip files in plugin conf
            foreach ($skipfile as $skipf) {
                if (!empty($skipf) && preg_match($skipf, $id)) {
                    return false;
                }
            }
            //Skip headpages to hide (nons has no namespace nodes, therefore, no duplicated links to headpage)
            if (!$opts['nons'] && !empty($headpage) && $opts['hide_headpage']) {
                //start page is in root
                if ($id == $conf['start']) return false;

                $ahp = explode(",", $headpage);
                foreach ($ahp as $hp) {
                    switch ($hp) {
                        case ":inside:":
                            if (noNS($id) == noNS(getNS($id))) return false;
                            break;
                        case ":same:":
                            if (@is_dir(dirname(wikiFN($id)) . "/" . utf8_encodeFN(noNS($id)))) return false;
                            break;
                        //it' s an inside start
                        case ":start:":
                            if (noNS($id) == $conf['start']) return false;
                            break;
                        default:
                            if (noNS($id) == cleanID($hp)) return false;
                    }
                }
            }
            //Set title
            if ($conf['useheading'] == 1 || $conf['useheading'] === 'navigation') {
                $title = p_get_first_heading($id, false);
            }
            if (is_null($title)) {
                $title = noNS($id);
            }
            $title = hsc($title);
        }

        $item = [
            'id' => $id,
            'type' => $type,
            'level' => $lvl,
            'open' => $isOpen,
            'title' => $title,
            'hns' => $hns,
            'file' => $file,
            'shouldBeTraversed' => $shouldBeTraversed
        ];
        $item['sort'] = $this->getSortValue($item);
        $data[] = $item;

        return $shouldBeTraversed;
    }

    /**
     * Callback that adds an item of namespace/page to the browsable index, if it fits in the specified options
     *
     * TODO Version as used for Fancytree js tree
     *
     * @param array $data indexed array of collected nodes, each item has:<ul>
     *   <li>$item['id'] string namespace or page id</li>
     *   <li>$item['type'] string f/d/l</li>
     *   <li>$item['level'] string current recursion depth (start count at 1)</li>
     *   <li>$item['open'] bool if a node is open</li>
     *   <li>$item['title'] string </li>
     *   <li>$item['hns'] string|false page id or false</li>
     *   <li>$item['hnsExists'] bool only false if hns is guessed(not-existing) for nopg</li>
     *   <li>$item['file'] string path to file or directory</li>
     *   <li>$item['shouldBeTraversed'] bool directory should be searched</li>
     *   <li>$item['sort'] mixed sort value</li>
     * </ul>
     * @param string $base Where to start the search, usually this is $conf['datadir']
     * @param string $file Current file or directory relative to $base
     * @param string $type Type either 'd' for directory or 'f' for file
     * @param int $lvl Current recursion depth
     * @param array $opts Option array as given to search()<ul>
     *   <li>$opts['skipns'] string regexp matching namespaceids to skip (ignored)</li>
     *   <li>$opts['skipfile']  string regexp matching pageids to skip (ignored)</li>
     *   <li>$opts['skipnscombined'] array regexp matching namespaceids to skip</li>
     *   <li>$opts['skipfilecombined'] array regexp matching pageids to skip</li>
     *   <li>$opts['headpage']   string headpages options or pageids</li>
     *   <li>$opts['level']      int    desired depth of main namespace, -1 = all levels</li>
     *   <li>$opts['subnss']     array with entries: array(namespaceid,level) specifying namespaces with their
     *                           own level</li>
     *   <li>$opts['nons']       bool   exclude namespace nodes</li>
     *   <li>$opts['max']        int    If initially closed, the node at max level will retrieve all its child nodes
     *                              through the AJAX mechanism</li>
     *   <li>$opts['nopg']       bool   exclude page nodes</li>
     *   <li>$opts['hide_headpage'] int don't hide (0) or hide (1)</li>
     * </ul>
     * @return bool if this directory should be traversed (true) or not (false)
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * modified by Samuele Tognini <samuele@samuele.netsons.org>
     */
    public function searchIndexmenuItemsNew(&$data, $base, $file, $type, $lvl, $opts)
    {
        global $conf;

        $hns = false;
        $isOpen = false;
        $title = null;
        $skipns = $opts['skipnscombined'];
        $skipfile = $opts['skipfilecombined'];
        $headpage = $opts['headpage'];
        $hnsExists = true; //nopg guesses pages
        $id = pathID($file);

        if ($type == 'd') {
            // Skip folders in plugin conf
            foreach ($skipns as $skipn) {
                if (!empty($skipn) && preg_match($skipn, $id)) {
                    return false;
                }
            }
            //check ACL (for sneaky_index namespaces too).
            if ($conf['sneaky_index'] && auth_quickaclcheck($id . ':') < AUTH_READ) return false;

            //Open requested level
            if ($opts['level'] > $lvl || $opts['level'] == -1) {
                $isOpen = true;
            }

            //Search optional subnamespaces with
            $isFolderAdjacentToSubNss = false;
            if (!empty($opts['subnss'])) {
                $subnss = $opts['subnss'];
                $counter = count($subnss);

                for ($a = 0; $a < $counter; $a++) {
                    if (preg_match("/^" . $id . "($|:.+)/i", $subnss[$a][0], $match)) {
                        //this folder contains a subnamespace
                        $isOpen = true;
                    } elseif (preg_match("/^" . $subnss[$a][0] . "(:.*)/i", $id, $match)) {
                        //this folder is inside a subnamespace, check level
                        if ($subnss[$a][1] == -1 || substr_count($match[1], ":") < $subnss[$a][1]) {
                            $isOpen = true;
                        } else {
                            $isOpen = false;
                        }
                    } elseif (
                        preg_match(
                            "/^" . (($ns = getNS($id)) === false ? '' : $ns) . "($|:.+)/i",
                            $subnss[$a][0],
                            $match
                        )
                    ) {
                        // parent folder contains a subnamespace, if level deeper it does not match anymore
                        // that is handled with normal >max handling
                        $isOpen = false;
                        if ($opts['max'] > 0) {
                            $isFolderAdjacentToSubNss = true;
                        }
                    }
                }
            }

            //decide if it should be traversed
            if ($opts['nons']) {
                return $isOpen; // in nons, level is only way to show/hide nodes (in nons nodes are not expandable)
            } elseif ($opts['max'] > 0 && !$isOpen) { // note: for Fancytree >=1 is used
                // limited levels per request, node is closed
                if ($lvl == $opts['max'] || $isFolderAdjacentToSubNss) {
                    // change type, more nodes should be loaded by ajax, but for nopg we need extra level to determine
                    // if folder is empty
                    // and folders adjacent to subns must be traversed as well
                    $type = "l";
                    $shouldBeTraversed = true;
                } elseif ($lvl > $opts['max']) { // deeper lvls only used temporary for checking existance children
                    //change type, more nodes should be loaded by ajax
                    $type = "l"; // use lazy loading
                    $shouldBeTraversed = false;
                } else {
                    //node is closed, but still more levels requested with max
                    $shouldBeTraversed = true;
                }
            } else {
                $shouldBeTraversed = $isOpen;
            }

            //Set title and headpage
            $title = static::getNamespaceTitle($id, $headpage, $hns);

            // when excluding page nodes: guess a headpage based on the headpage setting
            if ($opts['nopg'] && $hns === false) {
                $hns = $this->guessHeadpage($headpage, $id);
                $hnsExists = false;
            }
        } else {
            //Nopg.Dont show pages
            if ($opts['nopg']) return false;

            $shouldBeTraversed = true;
            //Nons.Set all pages at first level
            if ($opts['nons']) {
                $lvl = 1;
            }
            //don't add
            if (substr($file, -4) != '.txt') return false;
            //check hiddens and acl
            if (isHiddenPage($id) || auth_quickaclcheck($id) < AUTH_READ) return false;
            //Skip files in plugin conf
            foreach ($skipfile as $skipf) {
                if (!empty($skipf) && preg_match($skipf, $id)) {
                    return false;
                }
            }
            //Skip headpages to hide
            if (!$opts['nons'] && !empty($headpage) && $opts['hide_headpage']) {
                //start page is in root
                if ($id == $conf['start']) return false;

                $hpOptions = explode(",", $headpage);
                foreach ($hpOptions as $hp) {
                    switch ($hp) {
                        case ":inside:":
                            if (noNS($id) == noNS(getNS($id))) return false;
                            break;
                        case ":same:":
                            if (@is_dir(dirname(wikiFN($id)) . "/" . utf8_encodeFN(noNS($id)))) return false;
                            break;
                        //it' s an inside start
                        case ":start:":
                            if (noNS($id) == $conf['start']) return false;
                            break;
                        default:
                            if (noNS($id) == cleanID($hp)) return false;
                    }
                }
            }

            //Set title
            if ($conf['useheading'] == 1 || $conf['useheading'] === 'navigation') {
                $title = p_get_first_heading($id, false);
            }
            if (is_null($title)) {
                $title = noNS($id);
            }
            $title = hsc($title);
        }

        $item = [
            'id' => $id,
            'type' => $type,
            'level' => $lvl,
            'open' => $isOpen,
            'title' => $title,
            'hns' => $hns,
            'hnsExists' => $hnsExists,
            'file' => $file,
            'shouldBeTraversed' => $shouldBeTraversed
        ];
        $item['sort'] = $this->getSortValue($item);
        $data[] = $item;

        return $shouldBeTraversed;
    }

    /**
     * callback that recurse directory
     *
     * This function recurses into a given base directory
     * and calls the supplied function for each file and directory
     *
     * Similar to search() of inc/search.php, but has extended sorting options
     *
     * @param array $data The results of the search are stored here
     * @param string $base Where to start the search
     * @param callback $func Callback (function name or array with object,method)
     * @param array $opts List of indexmenu options
     * @param string $dir Current directory beyond $base
     * @param int $lvl Recursion Level
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @author  modified by Samuele Tognini <samuele@samuele.netsons.org>
     */
    public function customSearch(&$data, $base, $func, $opts, $dir = '', $lvl = 1)
    {
        $dirs = [];
        $files = [];
        $files_tmp = [];
        $dirs_tmp = [];
        $count = count($data);

        //read in directories and files
        $dh = @opendir($base . '/' . $dir);
        if (!$dh) return;
        while (($file = readdir($dh)) !== false) {
            //skip hidden files and upper dirs
            if (preg_match('/^[._]/', $file)) continue;
            if (is_dir($base . '/' . $dir . '/' . $file)) {
                $dirs[] = $dir . '/' . $file;
                continue;
            }
            $files[] = $dir . '/' . $file;
        }
        closedir($dh);

        //Collect and sort files
        foreach ($files as $file) {
            call_user_func_array($func, [&$files_tmp, $base, $file, 'f', $lvl, $opts]);
        }
        usort($files_tmp, [$this, "compareNodes"]);

        //Collect and sort dirs
        if ($this->nsort) {
            //collect the wanted directories in dirs_tmp
            foreach ($dirs as $dir) {
                call_user_func_array($func, [&$dirs_tmp, $base, $dir, 'd', $lvl, $opts]);
            }
            if($this->group) {
                //group directories and pages, and sort separately
                $dirsAndFiles = $dirs_tmp;
            } else {
                // no grouping
                //mix directories and pages and sort together
                $dirsAndFiles = array_merge($dirs_tmp, $files_tmp);
            }

            usort($dirsAndFiles, [$this, "compareNodes"]);

            //add and search each directory
            foreach ($dirsAndFiles as $dirOrFile) {
                $data[] = $dirOrFile;
                if ($dirOrFile['type'] != 'f' && $dirOrFile['shouldBeTraversed']) {
                    $this->customSearch($data, $base, $func, $opts, $dirOrFile['file'], $lvl + 1);
                }
            }
        } else {
            //sort by directory name
            Sort::sort($dirs);
            //collect directories
            foreach ($dirs as $dir) {
                if (call_user_func_array($func, [&$data, $base, $dir, 'd', $lvl, $opts])) {
                    $this->customSearch($data, $base, $func, $opts, $dir, $lvl + 1);
                }
            }
        }

        //count added items
        $added = count($data) - $count;

        if ($added === 0 && $files_tmp === []) {
            //remove empty directory again, only if it has not a headpage associated
            $lastItem = end($data);
            if (blank($lastItem['hns'])) {
                array_pop($data);
            }
        } elseif (!($this->nsort && !$this->group)) {
            //add files to index
            $data = array_merge($data, $files_tmp);
        }
    }


    /**
     * Get namespace title, checking for headpages
     *
     * @param string $ns namespace
     * @param string $headpage comma-separated headpages options and headpages
     * @param string|false $hns reference pageid of headpage, false when not existing
     * @return string when headpage & heading on: title of headpage, otherwise: namespace name
     *
     * @author  Samuele Tognini <samuele@samuele.netsons.org>
     */
    public static function getNamespaceTitle($ns, $headpage, &$hns)
    {
        global $conf;
        $hns = false;
        $title = noNS($ns);
        if (empty($headpage)) {
            return $title;
        }
        $hpOptions = explode(",", $headpage);
        foreach ($hpOptions as $hp) {
            switch ($hp) {
                case ":inside:":
                    $page = $ns . ":" . noNS($ns);
                    break;
                case ":same:":
                    $page = $ns;
                    break;
                //it's an inside start
                case ":start:":
                    $page = ltrim($ns . ":" . $conf['start'], ":");
                    break;
                //inside pages
                default:
                    if (!blank($hp)) { //empty setting results in empty string here
                        $page = $ns . ":" . $hp;
                    }
            }
            //check headpage
            if (@file_exists(wikiFN($page)) && auth_quickaclcheck($page) >= AUTH_READ) {
                if ($conf['useheading'] == 1 || $conf['useheading'] === 'navigation') {
                    $title_tmp = p_get_first_heading($page, false);
                    if (!is_null($title_tmp)) {
                        $title = $title_tmp;
                    }
                }
                $title = hsc($title);
                $hns = $page;
                //headpage found, exit for
                break;
            }
        }
        return $title;
    }


    /**
     * callback that sorts nodes
     *
     * @param array $a first node as array with 'sort' entry
     * @param array $b second node as array with 'sort' entry
     * @return int if less than zero 1st node is less than 2nd, otherwise equal respectively larger
     */
    private function compareNodes($a, $b)
    {
        if ($this->rsort) {
            return Sort::strcmp($b['sort'], $a['sort']);
        } else {
            return Sort::strcmp($a['sort'], $b['sort']);
        }
    }

    /**
     * Add sort information to item.
     *
     * @param array $item
     * @return bool|int|mixed|string
     *
     * @author  Samuele Tognini <samuele@samuele.netsons.org>
     */
    private function getSortValue($item)
    {
        global $conf;

        $sort = false;
        $page = false;
        if ($item['type'] == 'd' || $item['type'] == 'l') {
            //Fake order info when nsort is not requested
            if ($this->nsort) {
                $page = $item['hns'];
            } else {
                $sort = 0;
            }
        }
        if ($item['type'] == 'f') {
            $page = $item['id'];
        }
        if ($page) {
            if ($this->hsort && noNS($item['id']) == $conf['start']) {
                $sort = 1;
            }
            if ($this->msort) {
                $sort = p_get_metadata($page, $this->msort);
            }
            if (!$sort && $this->sort) {
                switch ($this->sort) {
                    case 't':
                        $sort = $item['title'];
                        break;
                    case 'd':
                        $sort = @filectime(wikiFN($page));
                        break;
                }
            }
        }
        if ($sort === false) {
            $sort = noNS($item['id']);
        }
        return $sort;
    }

    /**
     * Guess based on first option of the headpage config setting (default :start: if enabled) the headpage of the node
     *
     * @param string $headpage config setting
     * @param string $ns namespace
     * @return string guessed headpage
     */
    private function guessHeadpage(string $headpage, string $ns): string
    {
        global $conf;
        $hns = false;

        $hpOptions = explode(",", $headpage);
        foreach ($hpOptions as $hp) {
            switch ($hp) {
                case ":inside:":
                    $hns = $ns . ":" . noNS($ns);
                    break 2;
                case ":same:":
                    $hns = $ns;
                    break 2;
                //it's an inside start
                case ":start:":
                    $hns = ltrim($ns . ":" . $conf['start'], ":");
                    break 2;
                //inside pages
                default:
                    if (!blank($hp)) {
                        $hns = $ns . ":" . $hp;
                        break 2;
                    }
            }
        }

        if ($hns === false) {
            //fallback to start if headpage setting was empty
            $hns = ltrim($ns . ":" . $conf['start'], ":");
        }
        return $hns;
    }
}
