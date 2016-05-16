<?php
/**
 * ACL administration functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Anika Henke <anika@selfthinker.org> (concepts)
 * @author     Frank Schubert <frank@schokilade.de> (old version)
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * All DokuWiki plugins to extend the admin function
 * need to inherit from this class
 */
class admin_plugin_acl extends DokuWiki_Admin_Plugin {
    var $acl = null;
    var $ns  = null;
    /**
     * The currently selected item, associative array with id and type.
     * Populated from (in this order):
     * $_REQUEST['current_ns']
     * $_REQUEST['current_id']
     * $ns
     * $ID
     */
    var $current_item = null;
    var $who = '';
    var $usersgroups = array();
    var $specials = array();

    /**
     * return prompt for admin menu
     */
    function getMenuText($language) {
        return $this->getLang('admin_acl');
    }

    /**
     * return sort order for position in admin menu
     */
    function getMenuSort() {
        return 1;
    }

    /**
     * handle user request
     *
     * Initializes internal vars and handles modifications
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function handle() {
        global $AUTH_ACL;
        global $ID;
        global $auth;
        global $config_cascade;
        global $INPUT;

        // fresh 1:1 copy without replacements
        $AUTH_ACL = file($config_cascade['acl']['default']);

        // namespace given?
        if($INPUT->str('ns') == '*'){
            $this->ns = '*';
        }else{
            $this->ns = cleanID($INPUT->str('ns'));
        }

        if ($INPUT->str('current_ns')) {
            $this->current_item = array('id' => cleanID($INPUT->str('current_ns')), 'type' => 'd');
        } elseif ($INPUT->str('current_id')) {
            $this->current_item = array('id' => cleanID($INPUT->str('current_id')), 'type' => 'f');
        } elseif ($this->ns) {
            $this->current_item = array('id' => $this->ns, 'type' => 'd');
        } else {
            $this->current_item = array('id' => $ID, 'type' => 'f');
        }

        // user or group choosen?
        $who = trim($INPUT->str('acl_w'));
        if($INPUT->str('acl_t') == '__g__' && $who){
            $this->who = '@'.ltrim($auth->cleanGroup($who),'@');
        }elseif($INPUT->str('acl_t') == '__u__' && $who){
            $this->who = ltrim($who,'@');
            if($this->who != '%USER%' && $this->who != '%GROUP%'){ #keep wildcard as is
                $this->who = $auth->cleanUser($this->who);
            }
        }elseif($INPUT->str('acl_t') &&
                $INPUT->str('acl_t') != '__u__' &&
                $INPUT->str('acl_t') != '__g__'){
            $this->who = $INPUT->str('acl_t');
        }elseif($who){
            $this->who = $who;
        }

        // handle modifications
        if($INPUT->has('cmd') && checkSecurityToken()){
            $cmd = $INPUT->extract('cmd')->str('cmd');

            // scope for modifications
            if($this->ns){
                if($this->ns == '*'){
                    $scope = '*';
                }else{
                    $scope = $this->ns.':*';
                }
            }else{
                $scope = $ID;
            }

            if($cmd == 'save' && $scope && $this->who && $INPUT->has('acl')){
                // handle additions or single modifications
                $this->_acl_del($scope, $this->who);
                $this->_acl_add($scope, $this->who, $INPUT->int('acl'));
            }elseif($cmd == 'del' && $scope && $this->who){
                // handle single deletions
                $this->_acl_del($scope, $this->who);
            }elseif($cmd == 'update'){
                $acl = $INPUT->arr('acl');

                // handle update of the whole file
                foreach($INPUT->arr('del') as $where => $names){
                    // remove all rules marked for deletion
                    foreach($names as $who)
                        unset($acl[$where][$who]);
                }
                // prepare lines
                $lines = array();
                // keep header
                foreach($AUTH_ACL as $line){
                    if($line{0} == '#'){
                        $lines[] = $line;
                    }else{
                        break;
                    }
                }
                // re-add all rules
                foreach($acl as $where => $opt){
                    foreach($opt as $who => $perm){
                        if ($who[0]=='@') {
                            if ($who!='@ALL') {
                                $who = '@'.ltrim($auth->cleanGroup($who),'@');
                            }
                        } elseif ($who != '%USER%' && $who != '%GROUP%'){ #keep wildcard as is
                            $who = $auth->cleanUser($who);
                        }
                        $who = auth_nameencode($who,true);
                        $lines[] = "$where\t$who\t$perm\n";
                    }
                }
                // save it
                io_saveFile($config_cascade['acl']['default'], join('',$lines));
            }

            // reload ACL config
            $AUTH_ACL = file($config_cascade['acl']['default']);
        }

        // initialize ACL array
        $this->_init_acl_config();
    }

    /**
     * ACL Output function
     *
     * print a table with all significant permissions for the
     * current id
     *
     * @author  Frank Schubert <frank@schokilade.de>
     * @author  Andreas Gohr <andi@splitbrain.org>
     */
    function html() {
        echo '<div id="acl_manager">'.NL;
        echo '<h1>'.$this->getLang('admin_acl').'</h1>'.NL;
        echo '<div class="level1">'.NL;

        echo '<div id="acl__tree">'.NL;
        $this->_html_explorer();
        echo '</div>'.NL;

        echo '<div id="acl__detail">'.NL;
        $this->_html_detail();
        echo '</div>'.NL;
        echo '</div>'.NL;

        echo '<div class="clearer"></div>';
        echo '<h2>'.$this->getLang('current').'</h2>'.NL;
        echo '<div class="level2">'.NL;
        $this->_html_table();
        echo '</div>'.NL;

        echo '<div class="footnotes"><div class="fn">'.NL;
        echo '<sup><a id="fn__1" class="fn_bot" href="#fnt__1">1)</a></sup>'.NL;
        echo $this->getLang('p_include');
        echo '</div></div>';

        echo '</div>'.NL;
    }

    /**
     * returns array with set options for building links
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _get_opts($addopts=null){
        $opts = array(
                    'do'=>'admin',
                    'page'=>'acl',
                );
        if($this->ns) $opts['ns'] = $this->ns;
        if($this->who) $opts['acl_w'] = $this->who;

        if(is_null($addopts)) return $opts;
        return array_merge($opts, $addopts);
    }

    /**
     * Display a tree menu to select a page or namespace
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _html_explorer(){
        global $conf;
        global $ID;
        global $lang;

        $ns  = $this->ns;
        if(empty($ns)){
            $ns = dirname(str_replace(':','/',$ID));
            if($ns == '.') $ns ='';
        }elseif($ns == '*'){
            $ns ='';
        }
        $ns  = utf8_encodeFN(str_replace(':','/',$ns));

        $data = $this->_get_tree($ns);

        // wrap a list with the root level around the other namespaces
        array_unshift($data, array( 'level' => 0, 'id' => '*', 'type' => 'd',
                   'open' =>'true', 'label' => '['.$lang['mediaroot'].']'));

        echo html_buildlist($data,'acl',
                            array($this,'_html_list_acl'),
                            array($this,'_html_li_acl'));

    }

    /**
     * get a combined list of media and page files
     *
     * @param string $folder an already converted filesystem folder of the current namespace
     * @param string $limit  limit the search to this folder
     */
    function _get_tree($folder,$limit=''){
        global $conf;

        // read tree structure from pages and media
        $data = array();
        search($data,$conf['datadir'],'search_index',array('ns' => $folder),$limit);
        $media = array();
        search($media,$conf['mediadir'],'search_index',array('ns' => $folder, 'nofiles' => true),$limit);
        $data = array_merge($data,$media);
        unset($media);

        // combine by sorting and removing duplicates
        usort($data,array($this,'_tree_sort'));
        $count = count($data);
        if($count>0) for($i=1; $i<$count; $i++){
            if($data[$i-1]['id'] == $data[$i]['id'] && $data[$i-1]['type'] == $data[$i]['type']) {
                unset($data[$i]);
                $i++;  // duplicate found, next $i can't be a duplicate, so skip forward one
            }
        }
        return $data;
    }

    /**
     * usort callback
     *
     * Sorts the combined trees of media and page files
     */
    function _tree_sort($a,$b){
        // handle the trivial cases first
        if ($a['id'] == '') return -1;
        if ($b['id'] == '') return 1;
        // split up the id into parts
        $a_ids = explode(':', $a['id']);
        $b_ids = explode(':', $b['id']);
        // now loop through the parts
        while (count($a_ids) && count($b_ids)) {
            // compare each level from upper to lower
            // until a non-equal component is found
            $cur_result = strcmp(array_shift($a_ids), array_shift($b_ids));
            if ($cur_result) {
                // if one of the components is the last component and is a file
                // and the other one is either of a deeper level or a directory,
                // the file has to come after the deeper level or directory
                if (empty($a_ids) && $a['type'] == 'f' && (count($b_ids) || $b['type'] == 'd')) return 1;
                if (empty($b_ids) && $b['type'] == 'f' && (count($a_ids) || $a['type'] == 'd')) return -1;
                return $cur_result;
            }
        }
        // The two ids seem to be equal. One of them might however refer
        // to a page, one to a namespace, the namespace needs to be first.
        if (empty($a_ids) && empty($b_ids)) {
            if ($a['type'] == $b['type']) return 0;
            if ($a['type'] == 'f') return 1;
            return -1;
        }
        // Now the empty part is either a page in the parent namespace
        // that obviously needs to be after the namespace
        // Or it is the namespace that contains the other part and should be
        // before that other part.
        if (empty($a_ids)) return ($a['type'] == 'd') ? -1 : 1;
        if (empty($b_ids)) return ($b['type'] == 'd') ? 1 : -1;
    }

    /**
     * Display the current ACL for selected where/who combination with
     * selectors and modification form
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _html_detail(){
        global $ID;

        echo '<form action="'.wl().'" method="post" accept-charset="utf-8"><div class="no">'.NL;

        echo '<div id="acl__user">';
        echo $this->getLang('acl_perms').' ';
        $inl =  $this->_html_select();
        echo '<input type="text" name="acl_w" class="edit" value="'.(($inl)?'':hsc(ltrim($this->who,'@'))).'" />'.NL;
        echo '<button type="submit">'.$this->getLang('btn_select').'</button>'.NL;
        echo '</div>'.NL;

        echo '<div id="acl__info">';
        $this->_html_info();
        echo '</div>';

        echo '<input type="hidden" name="ns" value="'.hsc($this->ns).'" />'.NL;
        echo '<input type="hidden" name="id" value="'.hsc($ID).'" />'.NL;
        echo '<input type="hidden" name="do" value="admin" />'.NL;
        echo '<input type="hidden" name="page" value="acl" />'.NL;
        echo '<input type="hidden" name="sectok" value="'.getSecurityToken().'" />'.NL;
        echo '</div></form>'.NL;
    }

    /**
     * Print info and editor
     */
    function _html_info(){
        global $ID;

        if($this->who){
            $current = $this->_get_exact_perm();

            // explain current permissions
            $this->_html_explain($current);
            // load editor
            $this->_html_acleditor($current);
        }else{
            echo '<p>';
            if($this->ns){
                printf($this->getLang('p_choose_ns'),hsc($this->ns));
            }else{
                printf($this->getLang('p_choose_id'),hsc($ID));
            }
            echo '</p>';

            echo $this->locale_xhtml('help');
        }
    }

    /**
     * Display the ACL editor
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _html_acleditor($current){
        global $lang;

        echo '<fieldset>';
        if(is_null($current)){
            echo '<legend>'.$this->getLang('acl_new').'</legend>';
        }else{
            echo '<legend>'.$this->getLang('acl_mod').'</legend>';
        }

        echo $this->_html_checkboxes($current,empty($this->ns),'acl');

        if(is_null($current)){
            echo '<button type="submit" name="cmd[save]">'.$lang['btn_save'].'</button>'.NL;
        }else{
            echo '<button type="submit" name="cmd[save]">'.$lang['btn_update'].'</button>'.NL;
            echo '<button type="submit" name="cmd[del]">'.$lang['btn_delete'].'</button>'.NL;
        }

        echo '</fieldset>';
    }

    /**
     * Explain the currently set permissions in plain english/$lang
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _html_explain($current){
        global $ID;
        global $auth;

        $who = $this->who;
        $ns  = $this->ns;

        // prepare where to check
        if($ns){
            if($ns == '*'){
                $check='*';
            }else{
                $check=$ns.':*';
            }
        }else{
            $check = $ID;
        }

        // prepare who to check
        if($who{0} == '@'){
            $user   = '';
            $groups = array(ltrim($who,'@'));
        }else{
            $user = $who;
            $info = $auth->getUserData($user);
            if($info === false){
                $groups = array();
            }else{
                $groups = $info['grps'];
            }
        }

        // check the permissions
        $perm = auth_aclcheck($check,$user,$groups);

        // build array of named permissions
        $names = array();
        if($perm){
            if($ns){
                if($perm >= AUTH_DELETE) $names[] = $this->getLang('acl_perm16');
                if($perm >= AUTH_UPLOAD) $names[] = $this->getLang('acl_perm8');
                if($perm >= AUTH_CREATE) $names[] = $this->getLang('acl_perm4');
            }
            if($perm >= AUTH_EDIT) $names[] = $this->getLang('acl_perm2');
            if($perm >= AUTH_READ) $names[] = $this->getLang('acl_perm1');
            $names = array_reverse($names);
        }else{
            $names[] = $this->getLang('acl_perm0');
        }

        // print permission explanation
        echo '<p>';
        if($user){
            if($ns){
                printf($this->getLang('p_user_ns'),hsc($who),hsc($ns),join(', ',$names));
            }else{
                printf($this->getLang('p_user_id'),hsc($who),hsc($ID),join(', ',$names));
            }
        }else{
            if($ns){
                printf($this->getLang('p_group_ns'),hsc(ltrim($who,'@')),hsc($ns),join(', ',$names));
            }else{
                printf($this->getLang('p_group_id'),hsc(ltrim($who,'@')),hsc($ID),join(', ',$names));
            }
        }
        echo '</p>';

        // add note if admin
        if($perm == AUTH_ADMIN){
            echo '<p>'.$this->getLang('p_isadmin').'</p>';
        }elseif(is_null($current)){
            echo '<p>'.$this->getLang('p_inherited').'</p>';
        }
    }


    /**
     * Item formatter for the tree view
     *
     * User function for html_buildlist()
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _html_list_acl($item){
        $ret = '';
        // what to display
        if(!empty($item['label'])){
            $base = $item['label'];
        }else{
            $base = ':'.$item['id'];
            $base = substr($base,strrpos($base,':')+1);
        }

        // highlight?
        if( ($item['type']== $this->current_item['type'] && $item['id'] == $this->current_item['id'])) {
            $cl = ' cur';
        } else {
            $cl = '';
        }

        // namespace or page?
        if($item['type']=='d'){
            if($item['open']){
                $img   = DOKU_BASE.'lib/images/minus.gif';
                $alt   = '−';
            }else{
                $img   = DOKU_BASE.'lib/images/plus.gif';
                $alt   = '+';
            }
            $ret .= '<img src="'.$img.'" alt="'.$alt.'" />';
            $ret .= '<a href="'.wl('',$this->_get_opts(array('ns'=>$item['id'],'sectok'=>getSecurityToken()))).'" class="idx_dir'.$cl.'">';
            $ret .= $base;
            $ret .= '</a>';
        }else{
            $ret .= '<a href="'.wl('',$this->_get_opts(array('id'=>$item['id'],'ns'=>'','sectok'=>getSecurityToken()))).'" class="wikilink1'.$cl.'">';
            $ret .= noNS($item['id']);
            $ret .= '</a>';
        }
        return $ret;
    }


    function _html_li_acl($item){
        return '<li class="level' . $item['level'] . ' ' .
               ($item['open'] ? 'open' : 'closed') . '">';
    }


    /**
     * Get current ACL settings as multidim array
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _init_acl_config(){
        global $AUTH_ACL;
        global $conf;
        $acl_config=array();
        $usersgroups = array();

        // get special users and groups
        $this->specials[] = '@ALL';
        $this->specials[] = '@'.$conf['defaultgroup'];
        if($conf['manager'] != '!!not set!!'){
            $this->specials = array_merge($this->specials,
                                          array_map('trim',
                                                    explode(',',$conf['manager'])));
        }
        $this->specials = array_filter($this->specials);
        $this->specials = array_unique($this->specials);
        sort($this->specials);

        foreach($AUTH_ACL as $line){
            $line = trim(preg_replace('/#.*$/','',$line)); //ignore comments
            if(!$line) continue;

            $acl = preg_split('/[ \t]+/',$line);
            //0 is pagename, 1 is user, 2 is acl

            $acl[1] = rawurldecode($acl[1]);
            $acl_config[$acl[0]][$acl[1]] = $acl[2];

            // store non-special users and groups for later selection dialog
            $ug = $acl[1];
            if(in_array($ug,$this->specials)) continue;
            $usersgroups[] = $ug;
        }

        $usersgroups = array_unique($usersgroups);
        sort($usersgroups);
        ksort($acl_config);

        $this->acl = $acl_config;
        $this->usersgroups = $usersgroups;
    }

    /**
     * Display all currently set permissions in a table
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _html_table(){
        global $lang;
        global $ID;

        echo '<form action="'.wl().'" method="post" accept-charset="utf-8"><div class="no">'.NL;
        if($this->ns){
            echo '<input type="hidden" name="ns" value="'.hsc($this->ns).'" />'.NL;
        }else{
            echo '<input type="hidden" name="id" value="'.hsc($ID).'" />'.NL;
        }
        echo '<input type="hidden" name="acl_w" value="'.hsc($this->who).'" />'.NL;
        echo '<input type="hidden" name="do" value="admin" />'.NL;
        echo '<input type="hidden" name="page" value="acl" />'.NL;
        echo '<input type="hidden" name="sectok" value="'.getSecurityToken().'" />'.NL;
        echo '<div class="table">';
        echo '<table class="inline">';
        echo '<tr>';
        echo '<th>'.$this->getLang('where').'</th>';
        echo '<th>'.$this->getLang('who').'</th>';
        echo '<th>'.$this->getLang('perm').'<sup><a id="fnt__1" class="fn_top" href="#fn__1">1)</a></sup></th>';
        echo '<th>'.$lang['btn_delete'].'</th>';
        echo '</tr>';
        foreach($this->acl as $where => $set){
            foreach($set as $who => $perm){
                echo '<tr>';
                echo '<td>';
                if(substr($where,-1) == '*'){
                    echo '<span class="aclns">'.hsc($where).'</span>';
                    $ispage = false;
                }else{
                    echo '<span class="aclpage">'.hsc($where).'</span>';
                    $ispage = true;
                }
                echo '</td>';

                echo '<td>';
                if($who{0} == '@'){
                    echo '<span class="aclgroup">'.hsc($who).'</span>';
                }else{
                    echo '<span class="acluser">'.hsc($who).'</span>';
                }
                echo '</td>';

                echo '<td>';
                echo $this->_html_checkboxes($perm,$ispage,'acl['.$where.']['.$who.']');
                echo '</td>';

                echo '<td class="check">';
                echo '<input type="checkbox" name="del['.hsc($where).'][]" value="'.hsc($who).'" />';
                echo '</td>';
                echo '</tr>';
            }
        }

        echo '<tr>';
        echo '<th class="action" colspan="4">';
        echo '<button type="submit" name="cmd[update]">'.$lang['btn_update'].'</button>';
        echo '</th>';
        echo '</tr>';
        echo '</table>';
        echo '</div>';
        echo '</div></form>'.NL;
    }

    /**
     * Returns the permission which were set for exactly the given user/group
     * and page/namespace. Returns null if no exact match is available
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _get_exact_perm(){
        global $ID;
        if($this->ns){
            if($this->ns == '*'){
                $check = '*';
            }else{
                $check = $this->ns.':*';
            }
        }else{
            $check = $ID;
        }

        if(isset($this->acl[$check][$this->who])){
            return $this->acl[$check][$this->who];
        }else{
            return null;
        }
    }

    /**
     * adds new acl-entry to conf/acl.auth.php
     *
     * @author  Frank Schubert <frank@schokilade.de>
     */
    function _acl_add($acl_scope, $acl_user, $acl_level){
        global $config_cascade;
        $acl_user = auth_nameencode($acl_user,true);

        // max level for pagenames is edit
        if(strpos($acl_scope,'*') === false) {
            if($acl_level > AUTH_EDIT) $acl_level = AUTH_EDIT;
        }

        $new_acl = "$acl_scope\t$acl_user\t$acl_level\n";

        return io_saveFile($config_cascade['acl']['default'], $new_acl, true);
    }

    /**
     * remove acl-entry from conf/acl.auth.php
     *
     * @author  Frank Schubert <frank@schokilade.de>
     */
    function _acl_del($acl_scope, $acl_user){
        global $config_cascade;
        $acl_user = auth_nameencode($acl_user,true);

        $acl_pattern = '^'.preg_quote($acl_scope,'/').'[ \t]+'.$acl_user.'[ \t]+[0-8].*$';

        return io_deleteFromFile($config_cascade['acl']['default'], "/$acl_pattern/", true);
    }

    /**
     * print the permission radio boxes
     *
     * @author  Frank Schubert <frank@schokilade.de>
     * @author  Andreas Gohr <andi@splitbrain.org>
     */
    function _html_checkboxes($setperm,$ispage,$name){
        global $lang;

        static $label = 0; //number labels
        $ret = '';

        if($ispage && $setperm > AUTH_EDIT) $setperm = AUTH_EDIT;

        foreach(array(AUTH_NONE,AUTH_READ,AUTH_EDIT,AUTH_CREATE,AUTH_UPLOAD,AUTH_DELETE) as $perm){
            $label += 1;

            //general checkbox attributes
            $atts = array( 'type'  => 'radio',
                           'id'    => 'pbox'.$label,
                           'name'  => $name,
                           'value' => $perm );
            //dynamic attributes
            if(!is_null($setperm) && $setperm == $perm) $atts['checked']  = 'checked';
            if($ispage && $perm > AUTH_EDIT){
                $atts['disabled'] = 'disabled';
                $class = ' class="disabled"';
            }else{
                $class = '';
            }

            //build code
            $ret .= '<label for="pbox'.$label.'"'.$class.'>';
            $ret .= '<input '.buildAttributes($atts).' />&#160;';
            $ret .= $this->getLang('acl_perm'.$perm);
            $ret .= '</label>'.NL;
        }
        return $ret;
    }

    /**
     * Print a user/group selector (reusing already used users and groups)
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     */
    function _html_select(){
        $inlist = false;
        $usel = '';
        $gsel = '';

        if($this->who &&
           !in_array($this->who,$this->usersgroups) &&
           !in_array($this->who,$this->specials)){

            if($this->who{0} == '@'){
                $gsel = ' selected="selected"';
            }else{
                $usel = ' selected="selected"';
            }
        }else{
            $inlist = true;
        }

        echo '<select name="acl_t" class="edit">'.NL;
        echo '  <option value="__g__" class="aclgroup"'.$gsel.'>'.$this->getLang('acl_group').'</option>'.NL;
        echo '  <option value="__u__"  class="acluser"'.$usel.'>'.$this->getLang('acl_user').'</option>'.NL;
        if (!empty($this->specials)) {
            echo '  <optgroup label="&#160;">'.NL;
            foreach($this->specials as $ug){
                if($ug == $this->who){
                    $sel    = ' selected="selected"';
                    $inlist = true;
                }else{
                    $sel = '';
                }

                if($ug{0} == '@'){
                        echo '  <option value="'.hsc($ug).'" class="aclgroup"'.$sel.'>'.hsc($ug).'</option>'.NL;
                }else{
                        echo '  <option value="'.hsc($ug).'" class="acluser"'.$sel.'>'.hsc($ug).'</option>'.NL;
                }
            }
            echo '  </optgroup>'.NL;
        }
        if (!empty($this->usersgroups)) {
            echo '  <optgroup label="&#160;">'.NL;
            foreach($this->usersgroups as $ug){
                if($ug == $this->who){
                    $sel    = ' selected="selected"';
                    $inlist = true;
                }else{
                    $sel = '';
                }

                if($ug{0} == '@'){
                        echo '  <option value="'.hsc($ug).'" class="aclgroup"'.$sel.'>'.hsc($ug).'</option>'.NL;
                }else{
                        echo '  <option value="'.hsc($ug).'" class="acluser"'.$sel.'>'.hsc($ug).'</option>'.NL;
                }
            }
            echo '  </optgroup>'.NL;
        }
        echo '</select>'.NL;
        return $inlist;
    }
}
