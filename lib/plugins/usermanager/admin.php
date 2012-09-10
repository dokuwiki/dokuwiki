<?php
/*
 *  User Manager
 *
 *  Dokuwiki Admin Plugin
 *
 *  This version of the user manager has been modified to only work with
 *  objectified version of auth system
 *
 *  @author  neolao <neolao@neolao.com>
 *  @author  Chris Smith <chris@jalakai.co.uk>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN_IMAGES')) define('DOKU_PLUGIN_IMAGES',DOKU_BASE.'lib/plugins/usermanager/images/');

/**
 * All DokuWiki plugins to extend the admin function
 * need to inherit from this class
 */
class admin_plugin_usermanager extends DokuWiki_Admin_Plugin {

    var $_auth = null;        // auth object
    var $_user_total = 0;     // number of registered users
    var $_filter = array();   // user selection filter(s)
    var $_start = 0;          // index of first user to be displayed
    var $_last = 0;           // index of the last user to be displayed
    var $_pagesize = 20;      // number of users to list on one page
    var $_edit_user = '';     // set to user selected for editing
    var $_edit_userdata = array();
    var $_disabled = '';      // if disabled set to explanatory string

    /**
     * Constructor
     */
    function admin_plugin_usermanager(){
        global $auth;

        $this->setupLocale();

        if (!isset($auth)) {
          $this->disabled = $this->lang['noauth'];
        } else if (!$auth->canDo('getUsers')) {
          $this->disabled = $this->lang['nosupport'];
        } else {

          // we're good to go
          $this->_auth = & $auth;

        }
    }

     /**
     * return prompt for admin menu
     */
    function getMenuText($language) {

        if (!is_null($this->_auth))
          return parent::getMenuText($language);

        return $this->getLang('menu').' '.$this->disabled;
    }

    /**
     * return sort order for position in admin menu
     */
    function getMenuSort() {
        return 2;
    }

    /**
     * handle user request
     */
    function handle() {
        global $ID;

        if (is_null($this->_auth)) return false;

        // extract the command and any specific parameters
        // submit button name is of the form - fn[cmd][param(s)]
        $fn   = $_REQUEST['fn'];

        if (is_array($fn)) {
            $cmd = key($fn);
            $param = is_array($fn[$cmd]) ? key($fn[$cmd]) : null;
        } else {
            $cmd = $fn;
            $param = null;
        }

        if ($cmd != "search") {
          if (!empty($_REQUEST['start']))
            $this->_start = $_REQUEST['start'];
          $this->_filter = $this->_retrieveFilter();
        }

        switch($cmd){
          case "add"    : $this->_addUser(); break;
          case "delete" : $this->_deleteUser(); break;
          case "modify" : $this->_modifyUser(); break;
          case "edit"   : $this->_editUser($param); break;
          case "search" : $this->_setFilter($param);
                          $this->_start = 0;
                          break;
        }

        $this->_user_total = $this->_auth->canDo('getUserCount') ? $this->_auth->getUserCount($this->_filter) : -1;

        // page handling
        switch($cmd){
          case 'start' : $this->_start = 0; break;
          case 'prev'  : $this->_start -= $this->_pagesize; break;
          case 'next'  : $this->_start += $this->_pagesize; break;
          case 'last'  : $this->_start = $this->_user_total; break;
        }
        $this->_validatePagination();
    }

    /**
     * output appropriate html
     */
    function html() {
        global $ID;

        if(is_null($this->_auth)) {
            print $this->lang['badauth'];
            return false;
        }

        $user_list = $this->_auth->retrieveUsers($this->_start, $this->_pagesize, $this->_filter);
        $users = array_keys($user_list);

        $page_buttons = $this->_pagination();
        $delete_disable = $this->_auth->canDo('delUser') ? '' : 'disabled="disabled"';

        $editable = $this->_auth->canDo('UserMod');

        print $this->locale_xhtml('intro');
        print $this->locale_xhtml('list');

        ptln("<div id=\"user__manager\">");
        ptln("<div class=\"level2\">");

        if ($this->_user_total > 0) {
          ptln("<p>".sprintf($this->lang['summary'],$this->_start+1,$this->_last,$this->_user_total,$this->_auth->getUserCount())."</p>");
        } else {
          ptln("<p>".sprintf($this->lang['nonefound'],$this->_auth->getUserCount())."</p>");
        }
        ptln("<form action=\"".wl($ID)."\" method=\"post\">");
        formSecurityToken();
        ptln("  <table class=\"inline\">");
        ptln("    <thead>");
        ptln("      <tr>");
        ptln("        <th>&nbsp;</th><th>".$this->lang["user_id"]."</th><th>".$this->lang["user_name"]."</th><th>".$this->lang["user_mail"]."</th><th>".$this->lang["user_groups"]."</th>");
        ptln("      </tr>");

        ptln("      <tr>");
        ptln("        <td class=\"rightalign\"><input type=\"image\" src=\"".DOKU_PLUGIN_IMAGES."search.png\" name=\"fn[search][new]\" title=\"".$this->lang['search_prompt']."\" alt=\"".$this->lang['search']."\" class=\"button\" /></td>");
        ptln("        <td><input type=\"text\" name=\"userid\" class=\"edit\" value=\"".$this->_htmlFilter('user')."\" /></td>");
        ptln("        <td><input type=\"text\" name=\"username\" class=\"edit\" value=\"".$this->_htmlFilter('name')."\" /></td>");
        ptln("        <td><input type=\"text\" name=\"usermail\" class=\"edit\" value=\"".$this->_htmlFilter('mail')."\" /></td>");
        ptln("        <td><input type=\"text\" name=\"usergroups\" class=\"edit\" value=\"".$this->_htmlFilter('grps')."\" /></td>");
        ptln("      </tr>");
        ptln("    </thead>");

        if ($this->_user_total) {
          ptln("    <tbody>");
          foreach ($user_list as $user => $userinfo) {
            extract($userinfo);
            $groups = join(', ',$grps);
            ptln("    <tr class=\"user_info\">");
            ptln("      <td class=\"centeralign\"><input type=\"checkbox\" name=\"delete[".$user."]\" ".$delete_disable." /></td>");
            if ($editable) {
              ptln("    <td><a href=\"".wl($ID,array('fn[edit]['.hsc($user).']' => 1,
                                                     'do' => 'admin',
                                                     'page' => 'usermanager',
                                                     'sectok' => getSecurityToken())).
                   "\" title=\"".$this->lang['edit_prompt']."\">".hsc($user)."</a></td>");
            } else {
              ptln("    <td>".hsc($user)."</td>");
            }
            ptln("      <td>".hsc($name)."</td><td>".hsc($mail)."</td><td>".hsc($groups)."</td>");
            ptln("    </tr>");
          }
          ptln("    </tbody>");
        }

        ptln("    <tbody>");
        ptln("      <tr><td colspan=\"5\" class=\"centeralign\">");
        ptln("        <span class=\"medialeft\">");
        ptln("          <input type=\"submit\" name=\"fn[delete]\" ".$delete_disable." class=\"button\" value=\"".$this->lang['delete_selected']."\" id=\"usrmgr__del\" />");
        ptln("        </span>");
        ptln("        <span class=\"mediaright\">");
        ptln("          <input type=\"submit\" name=\"fn[start]\" ".$page_buttons['start']." class=\"button\" value=\"".$this->lang['start']."\" />");
        ptln("          <input type=\"submit\" name=\"fn[prev]\" ".$page_buttons['prev']." class=\"button\" value=\"".$this->lang['prev']."\" />");
        ptln("          <input type=\"submit\" name=\"fn[next]\" ".$page_buttons['next']." class=\"button\" value=\"".$this->lang['next']."\" />");
        ptln("          <input type=\"submit\" name=\"fn[last]\" ".$page_buttons['last']." class=\"button\" value=\"".$this->lang['last']."\" />");
        ptln("        </span>");
        ptln("        <input type=\"submit\" name=\"fn[search][clear]\" class=\"button\" value=\"".$this->lang['clear']."\" />");
        ptln("        <input type=\"hidden\" name=\"do\"    value=\"admin\" />");
        ptln("        <input type=\"hidden\" name=\"page\"  value=\"usermanager\" />");

        $this->_htmlFilterSettings(2);

        ptln("      </td></tr>");
        ptln("    </tbody>");
        ptln("  </table>");

        ptln("</form>");
        ptln("</div>");

        $style = $this->_edit_user ? " class=\"edit_user\"" : "";

        if ($this->_auth->canDo('addUser')) {
          ptln("<div".$style.">");
          print $this->locale_xhtml('add');
          ptln("  <div class=\"level2\">");

          $this->_htmlUserForm('add',null,array(),4);

          ptln("  </div>");
          ptln("</div>");
        }

        if($this->_edit_user  && $this->_auth->canDo('UserMod')){
          ptln("<div".$style." id=\"scroll__here\">");
          print $this->locale_xhtml('edit');
          ptln("  <div class=\"level2\">");

          $this->_htmlUserForm('modify',$this->_edit_user,$this->_edit_userdata,4);

          ptln("  </div>");
          ptln("</div>");
        }
        ptln("</div>");
    }


    /**
     * @todo disable fields which the backend can't change
     */
    function _htmlUserForm($cmd,$user='',$userdata=array(),$indent=0) {
        global $conf;
        global $ID;

        $name = $mail = $groups = '';
        $notes = array();

        if ($user) {
          extract($userdata);
          if (!empty($grps)) $groups = join(',',$grps);
        } else {
          $notes[] = sprintf($this->lang['note_group'],$conf['defaultgroup']);
        }

        ptln("<form action=\"".wl($ID)."\" method=\"post\">",$indent);
        formSecurityToken();
        ptln("  <table class=\"inline\">",$indent);
        ptln("    <thead>",$indent);
        ptln("      <tr><th>".$this->lang["field"]."</th><th>".$this->lang["value"]."</th></tr>",$indent);
        ptln("    </thead>",$indent);
        ptln("    <tbody>",$indent);

        $this->_htmlInputField($cmd."_userid",    "userid",    $this->lang["user_id"],    $user,  $this->_auth->canDo("modLogin"), $indent+6);
        $this->_htmlInputField($cmd."_userpass",  "userpass",  $this->lang["user_pass"],  "",     $this->_auth->canDo("modPass"),  $indent+6);
        $this->_htmlInputField($cmd."_username",  "username",  $this->lang["user_name"],  $name,  $this->_auth->canDo("modName"),  $indent+6);
        $this->_htmlInputField($cmd."_usermail",  "usermail",  $this->lang["user_mail"],  $mail,  $this->_auth->canDo("modMail"),  $indent+6);
        $this->_htmlInputField($cmd."_usergroups","usergroups",$this->lang["user_groups"],$groups,$this->_auth->canDo("modGroups"),$indent+6);

        if ($this->_auth->canDo("modPass")) {
          $notes[] = $this->lang['note_pass'];
          if ($user) {
            $notes[] = $this->lang['note_notify'];
          }

          ptln("<tr><td><label for=\"".$cmd."_usernotify\" >".$this->lang["user_notify"].": </label></td><td><input type=\"checkbox\" id=\"".$cmd."_usernotify\" name=\"usernotify\" value=\"1\" /></td></tr>", $indent);
        }

        ptln("    </tbody>",$indent);
        ptln("    <tbody>",$indent);
        ptln("      <tr>",$indent);
        ptln("        <td colspan=\"2\">",$indent);
        ptln("          <input type=\"hidden\" name=\"do\"    value=\"admin\" />",$indent);
        ptln("          <input type=\"hidden\" name=\"page\"  value=\"usermanager\" />",$indent);

        // save current $user, we need this to access details if the name is changed
        if ($user)
          ptln("          <input type=\"hidden\" name=\"userid_old\"  value=\"".$user."\" />",$indent);

        $this->_htmlFilterSettings($indent+10);

        ptln("          <input type=\"submit\" name=\"fn[".$cmd."]\" class=\"button\" value=\"".$this->lang[$cmd]."\" />",$indent);
        ptln("        </td>",$indent);
        ptln("      </tr>",$indent);
        ptln("    </tbody>",$indent);
        ptln("  </table>",$indent);

        foreach ($notes as $note)
          ptln("<div class=\"fn\">".$note."</div>",$indent);

        ptln("</form>",$indent);
    }

    function _htmlInputField($id, $name, $label, $value, $cando, $indent=0) {
        $class = $cando ? '' : ' class="disabled"';
        $disabled = $cando ? '' : ' disabled="disabled"';
        echo str_pad('',$indent);

        if($name == 'userpass'){
            $fieldtype = 'password';
            $autocomp  = 'autocomplete="off"';
        }else{
            $fieldtype = 'text';
            $autocomp  = '';
        }


        echo "<tr $class>";
        echo "<td><label for=\"$id\" >$label: </label></td>";
        echo "<td>";
        if($cando){
            echo "<input type=\"$fieldtype\" id=\"$id\" name=\"$name\" value=\"$value\" class=\"edit\" $autocomp />";
        }else{
            echo "<input type=\"hidden\" name=\"$name\" value=\"$value\" />";
            echo "<input type=\"$fieldtype\" id=\"$id\" name=\"$name\" value=\"$value\" class=\"edit disabled\" disabled=\"disabled\" />";
        }
        echo "</td>";
        echo "</tr>";
    }

    function _htmlFilter($key) {
        if (empty($this->_filter)) return '';
        return (isset($this->_filter[$key]) ? hsc($this->_filter[$key]) : '');
    }

    function _htmlFilterSettings($indent=0) {

        ptln("<input type=\"hidden\" name=\"start\" value=\"".$this->_start."\" />",$indent);

        foreach ($this->_filter as $key => $filter) {
          ptln("<input type=\"hidden\" name=\"filter[".$key."]\" value=\"".hsc($filter)."\" />",$indent);
        }
    }

    function _addUser(){
        if (!checkSecurityToken()) return false;
        if (!$this->_auth->canDo('addUser')) return false;

        list($user,$pass,$name,$mail,$grps) = $this->_retrieveUser();
        if (empty($user)) return false;

        if ($this->_auth->canDo('modPass')){
          if (empty($pass)){
            if(!empty($_REQUEST['usernotify'])){
              $pass = auth_pwgen();
            } else {
              msg($this->lang['add_fail'], -1);
              return false;
            }
          }
        } else {
          if (!empty($pass)){
            msg($this->lang['add_fail'], -1);
            return false;
          }
        }

        if ($this->_auth->canDo('modName')){
          if (empty($name)){
            msg($this->lang['add_fail'], -1);
            return false;
          }
        } else {
          if (!empty($name)){
            return false;
          }
        }

        if ($this->_auth->canDo('modMail')){
          if (empty($mail)){
            msg($this->lang['add_fail'], -1);
            return false;
          }
        } else {
          if (!empty($mail)){
            return false;
          }
        }

        if ($ok = $this->_auth->triggerUserMod('create', array($user,$pass,$name,$mail,$grps))) {

          msg($this->lang['add_ok'], 1);

          if (!empty($_REQUEST['usernotify']) && $pass) {
            $this->_notifyUser($user,$pass);
          }
        } else {
          msg($this->lang['add_fail'], -1);
        }

        return $ok;
    }

    /**
     * Delete user
     */
    function _deleteUser(){
        global $conf;

        if (!checkSecurityToken()) return false;
        if (!$this->_auth->canDo('delUser')) return false;

        $selected = $_REQUEST['delete'];
        if (!is_array($selected) || empty($selected)) return false;
        $selected = array_keys($selected);

        if(in_array($_SERVER['REMOTE_USER'], $selected)) {
            msg("You can't delete yourself!", -1);
            return false;
        }

        $count = $this->_auth->triggerUserMod('delete', array($selected));
        if ($count == count($selected)) {
          $text = str_replace('%d', $count, $this->lang['delete_ok']);
          msg("$text.", 1);
        } else {
          $part1 = str_replace('%d', $count, $this->lang['delete_ok']);
          $part2 = str_replace('%d', (count($selected)-$count), $this->lang['delete_fail']);
          msg("$part1, $part2",-1);
        }

        // invalidate all sessions
        io_saveFile($conf['cachedir'].'/sessionpurge',time());

        return true;
    }

    /**
     * Edit user (a user has been selected for editing)
     */
    function _editUser($param) {
        if (!checkSecurityToken()) return false;
        if (!$this->_auth->canDo('UserMod')) return false;

        $user = cleanID(preg_replace('/.*:/','',$param));
        $userdata = $this->_auth->getUserData($user);

        // no user found?
        if (!$userdata) {
          msg($this->lang['edit_usermissing'],-1);
          return false;
        }

        $this->_edit_user = $user;
        $this->_edit_userdata = $userdata;

        return true;
    }

    /**
     * Modify user (modified user data has been recieved)
     */
    function _modifyUser(){
        global $conf;

        if (!checkSecurityToken()) return false;
        if (!$this->_auth->canDo('UserMod')) return false;

        // get currently valid  user data
        $olduser = cleanID(preg_replace('/.*:/','',$_REQUEST['userid_old']));
        $oldinfo = $this->_auth->getUserData($olduser);

        // get new user data subject to change
        list($newuser,$newpass,$newname,$newmail,$newgrps) = $this->_retrieveUser();
        if (empty($newuser)) return false;

        $changes = array();
        if ($newuser != $olduser) {

          if (!$this->_auth->canDo('modLogin')) {        // sanity check, shouldn't be possible
            msg($this->lang['update_fail'],-1);
            return false;
          }

          // check if $newuser already exists
          if ($this->_auth->getUserData($newuser)) {
            msg(sprintf($this->lang['update_exists'],$newuser),-1);
            $re_edit = true;
          } else {
            $changes['user'] = $newuser;
          }
        }

        // generate password if left empty and notification is on
        if(!empty($_REQUEST['usernotify']) && empty($newpass)){
            $newpass = auth_pwgen();
        }

        if (!empty($newpass) && $this->_auth->canDo('modPass'))
          $changes['pass'] = $newpass;
        if (!empty($newname) && $this->_auth->canDo('modName') && $newname != $oldinfo['name'])
          $changes['name'] = $newname;
        if (!empty($newmail) && $this->_auth->canDo('modMail') && $newmail != $oldinfo['mail'])
          $changes['mail'] = $newmail;
        if (!empty($newgrps) && $this->_auth->canDo('modGroups') && $newgrps != $oldinfo['grps'])
          $changes['grps'] = $newgrps;

        if ($ok = $this->_auth->triggerUserMod('modify', array($olduser, $changes))) {
          msg($this->lang['update_ok'],1);

          if (!empty($_REQUEST['usernotify']) && $newpass) {
            $notify = empty($changes['user']) ? $olduser : $newuser;
            $this->_notifyUser($notify,$newpass);
          }

          // invalidate all sessions
          io_saveFile($conf['cachedir'].'/sessionpurge',time());

        } else {
          msg($this->lang['update_fail'],-1);
        }

        if (!empty($re_edit)) {
            $this->_editUser($olduser);
        }

        return $ok;
    }

    /**
     * send password change notification email
     */
    function _notifyUser($user, $password) {

        if ($sent = auth_sendPassword($user,$password)) {
          msg($this->lang['notify_ok'], 1);
        } else {
          msg($this->lang['notify_fail'], -1);
        }

        return $sent;
    }

    /**
     * retrieve & clean user data from the form
     *
     * @return  array(user, password, full name, email, array(groups))
     */
    function _retrieveUser($clean=true) {
        global $auth;

        $user[0] = ($clean) ? $auth->cleanUser($_REQUEST['userid']) : $_REQUEST['userid'];
        $user[1] = $_REQUEST['userpass'];
        $user[2] = $_REQUEST['username'];
        $user[3] = $_REQUEST['usermail'];
        $user[4] = explode(',',$_REQUEST['usergroups']);

        $user[4] = array_map('trim',$user[4]);
        if($clean) $user[4] = array_map(array($auth,'cleanGroup'),$user[4]);
        $user[4] = array_filter($user[4]);
        $user[4] = array_unique($user[4]);
        if(!count($user[4])) $user[4] = null;

        return $user;
    }

    function _setFilter($op) {

        $this->_filter = array();

        if ($op == 'new') {
          list($user,$pass,$name,$mail,$grps) = $this->_retrieveUser(false);

          if (!empty($user)) $this->_filter['user'] = $user;
          if (!empty($name)) $this->_filter['name'] = $name;
          if (!empty($mail)) $this->_filter['mail'] = $mail;
          if (!empty($grps)) $this->_filter['grps'] = join('|',$grps);
        }
    }

    function _retrieveFilter() {

        $t_filter = $_REQUEST['filter'];
        if (!is_array($t_filter)) return array();

        // messy, but this way we ensure we aren't getting any additional crap from malicious users
        $filter = array();

        if (isset($t_filter['user'])) $filter['user'] = $t_filter['user'];
        if (isset($t_filter['name'])) $filter['name'] = $t_filter['name'];
        if (isset($t_filter['mail'])) $filter['mail'] = $t_filter['mail'];
        if (isset($t_filter['grps'])) $filter['grps'] = $t_filter['grps'];

        return $filter;
    }

    function _validatePagination() {

        if ($this->_start >= $this->_user_total) {
          $this->_start = $this->_user_total - $this->_pagesize;
        }
        if ($this->_start < 0) $this->_start = 0;

        $this->_last = min($this->_user_total, $this->_start + $this->_pagesize);
    }

    /*
     *  return an array of strings to enable/disable pagination buttons
     */
    function _pagination() {

        $disabled = 'disabled="disabled"';

        $buttons['start'] = $buttons['prev'] = ($this->_start == 0) ? $disabled : '';

        if ($this->_user_total == -1) {
          $buttons['last'] = $disabled;
          $buttons['next'] = '';
        } else {
          $buttons['last'] = $buttons['next'] = (($this->_start + $this->_pagesize) >= $this->_user_total) ? $disabled : '';
        }

        return $buttons;
    }
}
