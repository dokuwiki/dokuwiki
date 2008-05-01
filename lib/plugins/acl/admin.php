<?php
/**
 * ACL administration functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Anika Henke <a.c.henke@arcor.de> (concepts)
 * @author     Frank Schubert <frank@schokilade.de> (old version)
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'admin.php');

/**
 * All DokuWiki plugins to extend the admin function
 * need to inherit from this class
 */
class admin_plugin_acl extends DokuWiki_Admin_Plugin {
    var $acl = null;
    var $ns  = null;
    var $who = '';
    var $usersgroups = array();


    /**
     * return some info
     */
    function getInfo(){
        return array(
            'author' => 'Andreas Gohr',
            'email'  => 'andi@splitbrain.org',
            'date'   => '2008-03-15',
            'name'   => 'ACL',
            'desc'   => 'Manage Page Access Control Lists',
            'url'    => 'http://wiki.splitbrain.org/wiki:acl',
        );
    }

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

        // namespace given?
        if($_REQUEST['ns'] == '*'){
            $this->ns = '*';
        }else{
            $this->ns = cleanID($_REQUEST['ns']);
        }

        // user or group choosen?
        $who = trim($_REQUEST['acl_w']);
        if($_REQUEST['acl_t'] == '__g__' && $who){
            $this->who = '@'.ltrim($who,'@');
        }elseif($_REQUEST['acl_t'] == '__u__' && $who){
            $this->who = ltrim($who,'@');
        }elseif($_REQUEST['acl_t'] &&
                $_REQUEST['acl_t'] != '__u__' &&
                $_REQUEST['acl_t'] != '__g__'){
            $this->who = $_REQUEST['acl_t'];
        }elseif($who){
            $this->who = $who;
        }

        // handle modifications
        if(isset($_REQUEST['cmd'])){
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

            if(isset($_REQUEST['cmd']['save']) && $scope && $this->who && isset($_REQUEST['acl'])){
                // handle additions or single modifications
                $this->_acl_del($scope, $this->who);
                $this->_acl_add($scope, $this->who, (int) $_REQUEST['acl']);
            }elseif(isset($_REQUEST['cmd']['del']) && $scope && $this->who){
                // handle single deletions
                $this->_acl_del($scope, $this->who);
            }elseif(isset($_REQUEST['cmd']['update'])){
                // handle update of the whole file
                foreach((array) $_REQUEST['del'] as $where => $who){
                    // remove all rules marked for deletion
                    unset($_REQUEST['acl'][$where][$who]);
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
                foreach((array) $_REQUEST['acl'] as $where => $opt){
                    foreach($opt as $who => $perm){
                        $who = auth_nameencode($who,true);
                        $lines[] = "$where\t$who\t$perm\n";
                    }
                }
                // save it
                io_saveFile(DOKU_CONF.'acl.auth.php', join('',$lines));
            }

            // reload ACL config
            $AUTH_ACL = file(DOKU_CONF.'acl.auth.php');
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
        global $ID;

        echo '<div id="acl_manager">'.NL;
        echo '<h1>'.$this->getLang('admin_acl').'</h1>'.NL;
        echo '<div class="level1">'.NL;

        echo '<div id="acl__tree">'.NL;
        $this->_html_explorer($_REQUEST['ns']);
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

        echo '</div>'.NL;
    }

    /**
     * returns array with set options for building links
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _get_opts($addopts=null){
        global $ID;
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
        require_once(DOKU_INC.'inc/search.php');
        global $conf;
        global $ID;
        global $lang;

        $dir = $conf['datadir'];
        $ns  = $this->ns;
        if(empty($ns)){
            $ns = dirname(str_replace(':','/',$ID));
            if($ns == '.') $ns ='';
        }elseif($ns == '*'){
            $ns ='';
        }
        $ns  = utf8_encodeFN(str_replace(':','/',$ns));


        $data = array();
        search($data,$conf['datadir'],'search_index',array('ns' => $ns));


        // wrap a list with the root level around the other namespaces
        $item = array( 'level' => 0, 'id' => '*', 'type' => 'd',
                   'open' =>'true', 'label' => '['.$lang['mediaroot'].']');

        echo '<ul class="acltree">';
        echo $this->_html_li_acl($item);
        echo '<div class="li">';
        echo $this->_html_list_acl($item);
        echo '</div>';
        echo html_buildlist($data,'acl',
                            array($this,'_html_list_acl'),
                            array($this,'_html_li_acl'));
        echo '</li>';
        echo '</ul>';

    }

    /**
     * Display the current ACL for selected where/who combination with
     * selectors and modification form
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _html_detail(){
        global $conf;
        global $ID;

        echo '<form action="'.wl().'" method="post" accept-charset="utf-8"><div class="no">'.NL;

        echo '<div id="acl__user">';
        echo $this->getLang('acl_perms').' ';
        $inl =  $this->_html_select();
        echo '<input type="text" name="acl_w" class="edit" value="'.(($inl)?'':hsc(ltrim($this->who,'@'))).'" />'.NL;
        echo '<input type="submit" value="'.$this->getLang('btn_select').'" class="button" />'.NL;
        echo '</div>'.NL;

        echo '<div id="acl__info">';
        $this->_html_info();
        echo '</div>';

        echo '<input type="hidden" name="ns" value="'.hsc($this->ns).'" />'.NL;
        echo '<input type="hidden" name="id" value="'.hsc($ID).'" />'.NL;
        echo '<input type="hidden" name="do" value="admin" />'.NL;
        echo '<input type="hidden" name="page" value="acl" />'.NL;
        echo '</div></form>'.NL;
    }

    /**
     * Print infos and editor
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
            echo '<input type="submit" name="cmd[save]" class="button" value="'.$lang['btn_save'].'" />'.NL;
        }else{
            echo '<input type="submit" name="cmd[save]" class="button" value="'.$lang['btn_update'].'" />'.NL;
            echo '<input type="submit" name="cmd[del]" class="button" value="'.$lang['btn_delete'].'" />'.NL;
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
            $user = auth_nameencode($who);
            $info = $auth->getUserData($user);
            if($info === false){
                $groups = array();
            }else{
                $groups = $info['groups'];
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
        global $ID;
        $ret = '';
        // what to display
        if($item['label']){
            $base = $item['label'];
        }else{
            $base = ':'.$item['id'];
            $base = substr($base,strrpos($base,':')+1);
        }

        // highlight?
        if(($item['type']=='d' &&
            $item['id'] == $this->ns) ||
            $item['id'] == $ID) $cl = ' cur';

        // namespace or page?
        if($item['type']=='d'){
            if($item['open']){
                $img   = DOKU_BASE.'lib/images/minus.gif';
                $alt   = '&minus;';
            }else{
                $img   = DOKU_BASE.'lib/images/plus.gif';
                $alt   = '+';
            }
            $ret .= '<img src="'.$img.'" alt="'.$alt.'" />';
            $ret .= '<a href="'.wl('',$this->_get_opts(array('ns'=>$item['id']))).'" class="idx_dir'.$cl.'">';
            $ret .= $base;
            $ret .= '</a>';
        }else{
            $ret .= '<a href="'.wl('',$this->_get_opts(array('id'=>$item['id'],'ns'=>''))).'" class="wikilink1'.$cl.'">';
            $ret .= noNS($item['id']);
            $ret .= '</a>';
        }
        return $ret;
    }


    function _html_li_acl($item){
            return '<li class="level'.$item['level'].'">';
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

        foreach($AUTH_ACL as $line){
            $line = trim(preg_replace('/#.*$/','',$line)); //ignore comments
            if(!$line) continue;

            $acl = preg_split('/\s+/',$line);
            //0 is pagename, 1 is user, 2 is acl

            $acl[1] = rawurldecode($acl[1]);
            $acl_config[$acl[0]][$acl[1]] = $acl[2];

            // store non-special users and groups for later selection dialog
            $ug = $acl[1];
            if($ug == '@ALL') continue;
            if($ug == $conf['superuser']) continue;
            if($ug == $conf['manager']) continue;
            $usersgroups[] = $ug;
        }

        $usersgroups = array_unique($usersgroups);
        sort($usersgroups);
        uksort($acl_config,array($this,'_sort_names'));

        $this->acl = $acl_config;
        $this->usersgroups = $usersgroups;
    }

    /**
     * Custom function to sort the ACLs by namespace names
     *
     * @todo This maybe could be improved to resemble the real tree structure?
     */
    function _sort_names($a,$b){
        $ca = substr_count($a,':');
        $cb = substr_count($b,':');
        if($ca < $cb){
            return -1;
        }elseif($ca > $cb){
            return 1;
        }else{
            return strcmp($a,$b);
        }
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
        echo '<table class="inline">';
        echo '<tr>';
        echo '<th>'.$this->getLang('where').'</th>';
        echo '<th>'.$this->getLang('who').'</th>';
        echo '<th>'.$this->getLang('perm').'</th>';
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
                echo $this->_html_checkboxes($perm,$ispage,'acl['.hsc($where).']['.hsc($who).']');
                echo '</td>';

                echo '<td align="center">';
                echo '<input type="checkbox" name="del['.hsc($where).']" value="'.hsc($who).'" />';
                echo '</td>';
                echo '</tr>';
            }
        }

        echo '<tr>';
        echo '<th align="right" colspan="4">';
        echo '<input type="submit" value="'.$lang['btn_update'].'" name="cmd[update]" class="button" />';
        echo '</th>';
        echo '</tr>';
        echo '</table>';
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

        if(isset($this->acl[$check][auth_nameencode($this->who,true)])){
            return $this->acl[$check][auth_nameencode($this->who,true)];
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
        $acl_config = file_get_contents(DOKU_CONF.'acl.auth.php');
        $acl_user = auth_nameencode($acl_user,true);

        // max level for pagenames is edit
        if(strpos($acl_scope,'*') === false) {
            if($acl_level > AUTH_EDIT) $acl_level = AUTH_EDIT;
        }


        $new_acl = "$acl_scope\t$acl_user\t$acl_level\n";

        $new_config = $acl_config.$new_acl;

        return io_saveFile(DOKU_CONF.'acl.auth.php', $new_config);
    }

    /**
     * remove acl-entry from conf/acl.auth.php
     *
     * @author  Frank Schubert <frank@schokilade.de>
     */
    function _acl_del($acl_scope, $acl_user){
        $acl_config = file(DOKU_CONF.'acl.auth.php');
        $acl_user = auth_nameencode($acl_user,true);

        $acl_pattern = '^'.preg_quote($acl_scope,'/').'\s+'.$acl_user.'\s+[0-8].*$';

        // save all non!-matching
        $new_config = preg_grep("/$acl_pattern/", $acl_config, PREG_GREP_INVERT);

        return io_saveFile(DOKU_CONF.'acl.auth.php', join('',$new_config));
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

        if($ispage && $setperm > AUTH_EDIT) $perm = AUTH_EDIT;

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
            $ret .= '<label for="pbox'.$label.'" title="'.$this->getLang('acl_perm'.$perm).'"'.$class.'>';
            $ret .= '<input '.html_attbuild($atts).' />&nbsp;';
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
        global $conf;
        $inlist = false;

        $specials = array('@ALL','@'.$conf['defaultgroup']);
        if($conf['manager'] && $conf['manager'] != '!!not set!!') $specials[] = $conf['manager'];


        if($this->who &&
           !in_array($this->who,$this->usersgroups) &&
           !in_array($this->who,$specials)){

            if($this->who{0} == '@'){
                $gsel = ' selected="selected"';
            }else{
                $usel   = ' selected="selected"';
            }
        }else{
            $usel = '';
            $gsel = '';
            $inlist = true;
        }


        echo '<select name="acl_t" class="edit">'.NL;
        echo '  <option value="__g__" class="aclgroup"'.$gsel.'>'.$this->getLang('acl_group').':</option>'.NL;
        echo '  <option value="__u__"  class="acluser"'.$usel.'>'.$this->getLang('acl_user').':</option>'.NL;
        echo '  <optgroup label="&nbsp;">'.NL;
        foreach($specials as $ug){
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
        echo '  <optgroup label="&nbsp;">'.NL;
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
        echo '</select>'.NL;
        return $inlist;
    }
}
