<?php
/**
 * ACL administration functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Frank Schubert <frank@schokilade.de>
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


        function admin_plugin_acl(){
            $this->setupLocale();
        }



    /**
     * return some info
     */
    function getInfo(){
      return array(
        'author' => 'Frank Schubert',
        'email'  => 'frank@schokilade.de',
        'date'   => '2005-08-08',
        'name'   => 'ACL',
        'desc'   => 'Manage Page Access Control Lists',
        'url'    => 'http://wiki.splitbrain.org/wiki:acl',
      );
    }

    /**
     * return prompt for admin menu
     */
    function getMenuText($language) {
        return $this->lang['admin_acl'];
    }

    /**
     * return sort order for position in admin menu
     */
    function getMenuSort() {
      return 1;
    }

    /**
     * handle user request
     */
    function handle() {
      global $AUTH_ACL;

      $cmd   = $_REQUEST['acl_cmd'];
      $scope = $_REQUEST['acl_scope'];
      $type  = $_REQUEST['acl_type'];
      $user  = $_REQUEST['acl_user'];
      $perm  = $_REQUEST['acl_perm'];

      if(is_array($perm)){
        //use the maximum
        sort($perm);
        $perm = array_pop($perm);
      }else{
        $perm = 0;
      }

      //sanitize
      $user  = auth_nameencode($user);
      if($type == '@') $user = '@'.$user;
      if($user == '@all') $user = '@ALL'; //special group! (now case insensitive)
      $perm  = (int) $perm;
      if($perm > AUTH_DELETE) $perm = AUTH_DELETE;
      //FIXME sanitize scope!!!

      //nothing to do?
      if(empty($cmd) || empty($scope) || empty($user)) return;


      if($cmd == 'save'){
        $this->admin_acl_del($scope, $user);
        $this->admin_acl_add($scope, $user, $perm);
      }elseif($cmd == 'delete'){
        $this->admin_acl_del($scope, $user);
      }

      // reload ACL config
      $AUTH_ACL = file(DOKU_CONF.'acl.auth.php');
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

      print $this->locale_xhtml('intro');

      ptln('<div id="acl__manager">');
      ptln('<table class="inline">');

      //new
      $this->admin_acl_html_new();

      //current config
      $acls = $this->get_acl_config($ID);
      foreach ($acls as $id => $acl){
        $this->admin_acl_html_current($id,$acl);
      }

      ptln('</table>');
      ptln('</div>');
    }


    /**
     * Get matching ACL lines for a page
     *
     * $ID is pagename, reads matching lines from $AUTH_ACL,
     * also reads acls from namespace
     * returns multi-array with key=pagename and value=array(user, acl)
     *
     * @todo    Fix comment to make sense
     * @todo    should this moved to auth.php?
     * @todo    can this be combined with auth_aclcheck to avoid duplicate code?
     * @author  Frank Schubert <frank@schokilade.de>
     */
    function get_acl_config($id){
      global $AUTH_ACL;

      $acl_config=array();

      // match exact name
      $matches = preg_grep('/^'.$id.'\s+.*/',$AUTH_ACL);
      if(count($matches)){
        foreach($matches as $match){
          $match = preg_replace('/#.*$/','',$match); //ignore comments
          $acl   = preg_split('/\s+/',$match);
          //0 is pagename, 1 is user, 2 is acl
          $acl_config[$acl[0]][] = array( 'name' => $acl[1], 'perm' => $acl[2]);
        }
      }

      $specific_found=array();
      // match ns
      while(($id=getNS($id)) !== false){
        $matches = preg_grep('/^'.$id.':\*\s+.*/',$AUTH_ACL);
        if(count($matches)){
          foreach($matches as $match){
            $match = preg_replace('/#.*$/','',$match); //ignore comments
            $acl   = preg_split('/\s+/',$match);
            //0 is pagename, 1 is user, 2 is acl
            $acl_config[$acl[0]][] = array( 'name' => $acl[1], 'perm' => $acl[2]);
            $specific_found[]=$acl[1];
          }
        }
      }

      //include *-config
      $matches = preg_grep('/^\*\s+.*/',$AUTH_ACL);
      if(count($matches)){
        foreach($matches as $match){
          $match = preg_replace('/#.*$/','',$match); //ignore comments
          $acl   = preg_split('/\s+/',$match);
          // only include * for this user if not already found in ns
          if(!in_array($acl[1], $specific_found)){
            //0 is pagename, 1 is user, 2 is acl
            $acl_config[$acl[0]][] = array( 'name' => $acl[1], 'perm' => $acl[2]);
          }
        }
      }

      //sort
      //FIXME: better sort algo: first sort by key, then sort by first value
      krsort($acl_config, SORT_STRING);

      return($acl_config);
    }


    /**
     * adds new acl-entry to conf/acl.auth.php
     *
     * @author  Frank Schubert <frank@schokilade.de>
     */
    function admin_acl_add($acl_scope, $acl_user, $acl_level){
      $acl_config = join("",file(DOKU_CONF.'acl.auth.php'));

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
    function admin_acl_del($acl_scope, $acl_user){
      $acl_config = file(DOKU_CONF.'acl.auth.php');

      $acl_pattern = '^'.preg_quote($acl_scope,'/').'\s+'.$acl_user.'\s+[0-8].*$';

      // save all non!-matching #FIXME invert is available from 4.2.0 only!
      $new_config = preg_grep("/$acl_pattern/", $acl_config, PREG_GREP_INVERT);

      return io_saveFile(DOKU_CONF.'acl.auth.php', join('',$new_config));
    }

    // --- HTML OUTPUT FUNCTIONS BELOW --- //

    /**
     * print tablerows with the current permissions for one id
     *
     * @author  Frank Schubert <frank@schokilade.de>
     * @author  Andreas Gohr <andi@splitbrain.org>
     */
    function admin_acl_html_dropdown($id){
      $cur = $id;
      $ret = '';
      $opt = array();

      //prepare all options

      // current page
      $opt[] = array('key'=> $id, 'val'=> $id.' ('.$this->lang['page'].')');

      // additional namespaces
      while(($id=getNS($id)) !== false){
        $opt[] = array('key'=> $id.':*', 'val'=> $id.':* ('.$this->lang['namespace'].')');
      }

      // the top namespace
      $opt[] = array('key'=> '*', 'val'=> '* ('.$this->lang['namespace'].')');

      // set sel on second entry (current namespace)
      $opt[1]['sel'] = ' selected="selected"';

      // flip options
      $opt = array_reverse($opt);

      // create HTML
      $att = array( 'name'  => 'acl_scope',
                    'class' => 'edit',
                    'title' => $this->lang['page'].'/'.$this->lang['namespace']);
      $ret .= '<select '.html_attbuild($att).'>';
      foreach($opt as $o){
        $ret .= '<option value="'.$o['key'].'"'.$o['sel'].'>'.$o['val'].'</option>';
      }
      $ret .= '</select>';

      return $ret;
    }

    /**
     * print tablerows with the current permissions for one id
     *
     * @author  Frank Schubert <frank@schokilade.de>
     * @author  Andreas Gohr <andi@splitbrain.org>
     */
    function admin_acl_html_new(){
      global $ID;
            global $lang;

      // table headers
      ptln('<tr>',2);
      ptln('  <th class="leftalign" colspan="3">'.$this->lang['acl_new'].'</th>',2);
      ptln('</tr>',2);

      ptln('<tr>',2);

      ptln('<td class="centeralign" colspan="3">',4);

      ptln('  <form method="post" action="'.wl($ID).'"><div class="no">',4);
      ptln('    <input type="hidden" name="do"   value="admin" />',4);
      ptln('    <input type="hidden" name="page" value="acl" />',4);
      ptln('    <input type="hidden" name="acl_cmd" value="save" />',4);

      //scope select
      ptln($this->lang['acl_perms'],4);
      ptln($this->admin_acl_html_dropdown($ID),4);

      $att = array( 'name'  => 'acl_type',
                    'class' => 'edit',
                    'title' => $this->lang['acl_user'].'/'.$this->lang['acl_group']);
      ptln('    <select '.html_attbuild($att).'>',4);
      ptln('      <option value="@">'.$this->lang['acl_group'].'</option>',4);
      ptln('      <option value="">'.$this->lang['acl_user'].'</option>',4);
      ptln('    </select>',4);

      $att = array( 'name'  => 'acl_user',
                    'type'  => 'text',
                    'class' => 'edit',
                    'title' => $this->lang['acl_user'].'/'.$this->lang['acl_group']);
      ptln('    <input '.html_attbuild($att).' />',4);
      ptln('    <br />');

      ptln(     $this->admin_acl_html_checkboxes(0,false),8);

      ptln('    <input type="submit" class="button" value="'.$lang['btn_save'].'" />',4);
      ptln('  </div></form>');
      ptln('</td>',4);
      ptln('</tr>',2);
    }

    /**
     * print tablerows with the current permissions for one id
     *
     * @author  Frank Schubert <frank@schokilade.de>
     * @author  Andreas Gohr <andi@splitbrain.org>
     */
    function admin_acl_html_current($id,$permissions){
      global $lang;
      global $ID;

      //is it a page?
      if(substr($id,-1) == '*'){
        $ispage = false;
      }else{
        $ispage = true;
      }

      // table headers
      ptln('  <tr>');
      ptln('    <th class="leftalign" colspan="3">');
      ptln($this->lang['acl_perms'],6);
      if($ispage){
        ptln($this->lang['page'],6);
      }else{
        ptln($this->lang['namespace'],6);
      }
      ptln('<em>'.$id.'</em>',6);
      ptln('    </th>');
      ptln('  </tr>');

      sort($permissions);

      foreach ($permissions as $conf){
        //userfriendly group/user display
        $conf['name'] = rawurldecode($conf['name']);
        if(substr($conf['name'],0,1)=="@"){
          $group = $this->lang['acl_group'];
          $name  = substr($conf['name'],1);
          $type  = '@';
        }else{
          $group = $this->lang['acl_user'];
          $name  = $conf['name'];
          $type  = '';
        }

        ptln('<tr>',2);
        ptln('<td class="leftalign">'.htmlspecialchars($group.' '.$name).'</td>',4);

        // update form
        ptln('<td class="centeralign">',4);
        ptln('  <form method="post" action="'.wl($ID).'"><div class="no">',4);
        ptln('    <input type="hidden" name="do"   value="admin" />',4);
        ptln('    <input type="hidden" name="page" value="acl" />',4);
        ptln('    <input type="hidden" name="acl_cmd"   value="save" />',4);
        ptln('    <input type="hidden" name="acl_scope" value="'.formtext($id).'" />',4);
        ptln('    <input type="hidden" name="acl_type" value="'.$type.'" />',4);
        ptln('    <input type="hidden" name="acl_user"  value="'.formtext($name).'" />',4);
        ptln(     $this->admin_acl_html_checkboxes($conf['perm'],$ispage),8);
        ptln('    <input type="submit" class="button" value="'.$lang['btn_update'].'" />',4);
        ptln('  </div></form>');
        ptln('</td>',4);


        // deletion form

        $ask  = $lang['del_confirm'].'\\n';
        $ask .= $id.'  '.$conf['name'].'  '.$conf['perm'];
        ptln('<td class="centeralign">',4);
        ptln('  <form method="post" action="'.wl($ID).'" onsubmit="return confirm(\''.str_replace('\\\\n','\\n',addslashes($ask)).'\')"><div class="no">',4);
        ptln('    <input type="hidden" name="do"        value="admin" />',4);
        ptln('    <input type="hidden" name="page"      value="acl" />',4);
        ptln('    <input type="hidden" name="acl_cmd"   value="delete" />',4);
        ptln('    <input type="hidden" name="acl_scope" value="'.formtext($id).'" />',4);
        ptln('    <input type="hidden" name="acl_type" value="'.$type.'" />',4);
        ptln('    <input type="hidden" name="acl_user"  value="'.formtext($name).'" />',4);
        ptln('    <input type="submit" class="button" value="'.$lang['btn_delete'].'" />',4);
        ptln('  </div></form>',4);
        ptln('</td>',4);

        ptln('</tr>',2);
      }

    }


    /**
     * print the permission checkboxes
     *
     * @author  Frank Schubert <frank@schokilade.de>
     * @author  Andreas Gohr <andi@splitbrain.org>
     */
    function admin_acl_html_checkboxes($setperm,$ispage){
      global $lang;

      static $label = 0; //number labels
      $ret = '';

      foreach(array(AUTH_READ,AUTH_EDIT,AUTH_CREATE,AUTH_UPLOAD,AUTH_DELETE) as $perm){
        $label += 1;

        //general checkbox attributes
        $atts = array( 'type'  => 'checkbox',
                       'id'    => 'pbox'.$label,
                       'name'  => 'acl_perm[]',
                       'value' => $perm );
        //dynamic attributes
        if($setperm >= $perm) $atts['checked']  = 'checked';
    #        if($perm > AUTH_READ) $atts['onchange'] = #FIXME JS to autoadd lower perms
        if($ispage && $perm > AUTH_EDIT) $atts['disabled'] = 'disabled';

        //build code
        $ret .= '<label for="pbox'.$label.'" title="'.$this->lang['acl_perm'.$perm].'">';
        $ret .= '<input '.html_attbuild($atts).' />';
        $ret .= $this->lang['acl_perm'.$perm];
        $ret .= "</label>\n";
      }
      return $ret;
    }

}
