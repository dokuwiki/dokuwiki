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
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
if(!defined('DOKU_PLUGIN_IMAGES')) define('DOKU_PLUGIN_IMAGES',DOKU_BASE.'lib/plugins/usermanager/images/');
require_once(DOKU_PLUGIN.'admin.php');

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
    var $_user_edit = null;   // set to user selected for editing

    /**
     * Constructor
     */
    function admin_plugin_usermanager(){
        global $auth;

        $this->setupLocale();
        if (isset($auth)) $this->_auth = & $auth;
    }

    /**
     * return some info
     */
    function getInfo(){
        $disabled = is_null($this->_auth) ? '(disabled)' : '';

        return array(
            'author' => 'Chris Smith',
            'email'  => 'chris@jalakai.co.uk',
            'date'   => '2005-11-24',
            'name'   => 'User Manager',
            'desc'   => 'Manage users '.$disabled,
            'url'    => 'http://wiki.splitbrain.org/plugin:user_manager',
        );
    }
     /**
     * return prompt for admin menu
     */
    function getMenuText($language) {

        if (!is_null($this->_auth)) 
          return parent::getMenuText($language);

        return $this->getLang["menu"]." (objectified auth only)";
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
          $this->_start = $_REQUEST['start'];
          $this->_filter = $this->_retrieveFilter();
        }

        switch($cmd){
          case "add"    : $this->_addUser(); break;
          case "delete" : $this->_deleteUser(); break;
          case "modify" : $this->_modifyUser(); break;
          case "edit"   : $this->_edit_user = $param; break;     // no extra handling required - only html
          case "search" : $this->_setFilter($param);
                          $this->_start = 0;
                          break;
        }
          
        $this->_user_total = $this->_auth->getUserCount($this->_filter);
  
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
        $edit_disable = $this->_auth->canDo('modifyUser') ? '' : 'disabled="disabled"';
        $delete_disable = $this->_auth->canDo('deleteUsers') ? '' : 'disabled="disabled"';

        print $this->locale_xhtml('intro');
        print $this->locale_xhtml('list');

        ptln("<div class=\"level2\" style=\"margin-bottom: 2em;\">");
        
        if ($this->_user_total) {
          ptln("<p>".sprintf($this->lang['summary'],$this->_start+1,$this->_last,$this->_user_total,$this->_auth->getUserCount())."</p>");
        } else {
          ptln("<p>".sprintf($this->lang['nonefound'],$this->_auth->getUserCount())."</p>");
        }
        ptln("<form action=\"".wl($ID)."\" method=\"post\">");
        ptln("  <table class=\"inline\">");
        ptln("    <thead>");
        ptln("      <tr>");
        ptln("        <th colspan=\"2\">&nbsp;</th><th>".$this->lang["user_id"]."</th><th>".$this->lang["user_name"]."</th><th>".$this->lang["user_mail"]."</th><th>".$this->lang["user_groups"]."</th>");
        ptln("      </tr>");

        ptln("      <tr>");
//        ptln("        <td colspan=\"2\"><input type=\"submit\" name=\"fn[search][new]\" value=\"".$this->lang['search']."\" /></td>");
		ptln("        <td colspan=\"2\" style=\"vertical-align:middle; text-align:right;\"><input type=\"image\" src=\"".DOKU_PLUGIN_IMAGES."search.png\" name=\"fn[search][new]\" title=\"".$this->lang['search_prompt']."\" alt=\"".$this->lang['search']."\" /></td>");
        ptln("        <td><input type=\"text\" name=\"userid\" value=\"".$this->_htmlFilter('user')."\" /></td>");
        ptln("        <td><input type=\"text\" name=\"username\" value=\"".$this->_htmlFilter('name')."\" /></td>");
        ptln("        <td><input type=\"text\" name=\"usermail\" value=\"".$this->_htmlFilter('mail')."\" /></td>");
        ptln("        <td><input type=\"text\" name=\"usergroups\" value=\"".$this->_htmlFilter('grps')."\" /></td>");
        ptln("      </tr>");
        ptln("    </thead>");

        if ($this->_user_total) {
          ptln("    <tbody>");
          foreach ($user_list as $user => $userinfo) {
            extract($userinfo);
            $groups = join(', ',$grps);
            ptln("    <tr valign=\"top\" align=\"left\">");
            ptln("      <td class=\"centeralign\"><input type=\"checkbox\" name=\"delete[".$user."]\" ".$delete_disable." /></td>");
//            ptln("      <td class=\"centeralign\"><input type=\"submit\" name=\"fn[edit][".$user."]\" ".$edit_disable." value=\"".$this->lang['edit']."\"/></td>");
            ptln("      <td class=\"centeralign\"><input type=\"image\" name=\"fn[edit][".$user."]\" ".$edit_disable." src=\"".DOKU_PLUGIN_IMAGES."user_edit.png\" title=\"".$this->lang['edit_prompt']."\" alt=\"".$this->lang['edit']."\"/></td>");
            ptln("      <td>".hsc($user)."</td><td>".hsc($name)."</td><td>".hsc($mail)."</td><td>".hsc($groups)."</td>");
            ptln("    </tr>");
          }
          ptln("    </tbody>");
        }

        ptln("    <tbody>");
        ptln("      <tr><td colspan=\"6\" style=\"text-align:center\">");
        ptln("        <span style=\"float:left\">");
        ptln("          <input type=\"submit\" name=\"fn[delete]\" ".$delete_disable." value=\"".$this->lang['delete_selected']."\"/>");
        ptln("        </span>");
        ptln("        <span style=\"float:right\">");
        ptln("          <input type=\"submit\" name=\"fn[start]\" ".$page_buttons['start']." value=\"".$this->lang['start']."\" />");
        ptln("          <input type=\"submit\" name=\"fn[prev]\" ".$page_buttons['prev']." value=\"".$this->lang['prev']."\" />");
        ptln("          <input type=\"submit\" name=\"fn[next]\" ".$page_buttons['next']." value=\"".$this->lang['next']."\" />");
        ptln("          <input type=\"submit\" name=\"fn[last]\" ".$page_buttons['last']." value=\"".$this->lang['last']."\" />");
        ptln("        </span>");
        ptln("        <input type=\"submit\" name=\"fn[search][clear]\" value=\"".$this->lang['clear']."\" />");
        ptln("      </td></tr>");
        ptln("    </tbody>");
        ptln("  </table>");
        ptln("  <input type=\"hidden\" name=\"do\"    value=\"admin\" />");
        ptln("  <input type=\"hidden\" name=\"page\"  value=\"usermanager\" />");

        $this->_htmlFilterSettings(2);

        ptln("</form>");
        ptln("</div>");

        $style = $this->_edit_user ? " style=\"width: 46%; float: left;\"" : "";

        if ($this->_auth->canDo('createUser')) {
          ptln("<div".$style.">");
          print $this->locale_xhtml('add');
          ptln("  <div class=\"level2\">");

          $this->_htmlUserForm('add',null,4);

          ptln("  </div>");
          ptln("</div>");
        }

        if($this->_edit_user  && $this->_auth->canDo('modifyUser')){
          ptln("<div".$style.">");
          print $this->locale_xhtml('edit');
          ptln("  <div class=\"level2\">");

          $this->_htmlUserForm('modify',$this->_edit_user,4);

          ptln("  </div>");
          ptln("</div>");
        }
    }

    function _htmlUserForm($cmd,$user=null,$indent=0) {

        if ($user) {
          extract($this->_auth->getUserData($user));
          $groups = join(',',$grps);
        } else {
          $user = $name = $mail = $groups = '';
        }

        ptln("<form action=\"".wl($ID)."\" method=\"post\">",$indent);
        ptln("  <table class=\"inline\">",$indent);
        ptln("    <thead>",$indent);
        ptln("      <tr><th>".$this->lang["field"]."</th><th>".$this->lang["value"]."</th></tr>",$indent);
        ptln("    </thead>",$indent);
        ptln("    <tbody>",$indent);
        ptln("      <tr><td><label for=\"".$cmd."_userid\" >".$this->lang["user_id"]." : </label></td><td><input type=\"text\" id=\"".$cmd."_userid\" name=\"userid\" value=\"".$user."\" /></td></tr>",$indent);
        ptln("      <tr><td><label for=\"".$cmd."_userpass\" >".$this->lang["user_pass"]." : </label></td><td><input type=\"text\" id=\"".$cmd."_userpass\" name=\"userpass\" value=\"\" /></td></tr>",$indent);
        ptln("      <tr><td><label for=\"".$cmd."_username\" >".$this->lang["user_name"]." : </label></td><td><input type=\"text\" id=\"".$cmd."_username\" name=\"username\" value=\"".$name."\" /></td></tr>",$indent);
        ptln("      <tr><td><label for=\"".$cmd."_usermail\" >".$this->lang["user_mail"]." : </label></td><td><input type=\"text\" id=\"".$cmd."_usermail\" name=\"usermail\" value=\"".$mail."\" /></td></tr>",$indent);
        ptln("      <tr><td><label for=\"".$cmd."_usergroups\" >".$this->lang["user_groups"]." : </label></td><td><input type=\"text\" id=\"".$cmd."_usergroups\" name=\"usergroups\" value=\"".$groups."\" /></td></tr>",$indent);
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

        ptln("          <input type=\"submit\" name=\"fn[".$cmd."]\" value=\"".$this->lang[$cmd]."\" />",$indent);
        ptln("        </td>",$indent);
        ptln("      </tr>",$indent);
        ptln("    </tbody>",$indent);
        ptln("  </table>",$indent);
        ptln("</form>",$indent);
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
    
        if (!$this->_auth->canDo('createUser')) return false;

        list($user,$pass,$name,$mail,$grps) = $this->_retrieveUser();
        if (empty($user)) return false;

        return $this->_auth->createUser($user,$pass,$name,$mail,$grps);
    }

    /**
     * Delete user
     */
    function _deleteUser(){

        if (!$this->_auth->canDo('deleteUsers')) return false;

        $selected = $_REQUEST['delete'];
        if (!is_array($selected) || empty($selected)) return false;
        $selected = array_keys($selected);

        $count = $this->_auth->deleteUsers($selected);
        if ($count == count($selected)) {
          $text = str_replace('%d', $count, $this->lang['delete_ok']);
          msg("$text.", 1);
        } else {
          $part1 = str_replace('%d', $count, $this->lang['delete_ok']);
          $part2 = str_replace('%d', (count($selected)-$count), $this->lang['delete_fail']);
          msg("$part1, $part2",-1);
        }
    }

    /**
     * Modify user
     */
    function _modifyUser(){
        if (!$this->_auth->canDo('modifyUser')) return false;

        list($user,$pass,$name,$mail,$grps) = $this->_retrieveUser();
        if (empty($user)) return false;

        $changes = array();
        $user_old = cleanID(preg_replace('/.*:/','',$_REQUEST['userid_old']));
        if ($user != $user_old) {
		  // check $user doesn't already exist
		  if ($this->_auth->getUserData($user)) {
		    msg(sprintf($this->lang['update_exists'],$user),-1);
			$this->_edit_user = $user = $user_old;
		  } else {
            $changes['user'] = $user;
            $user = $user_old;
		  }
        }

        if (!empty($pass)) $changes['pass'] = $pass;
        if (!empty($name)) $changes['name'] = $name;
        if (!empty($mail)) $changes['mail'] = $mail;
        if (!empty($grps)) $changes['grps'] = $grps;

        if ($this->_auth->modifyUser($user, $changes)) {
          msg($this->lang['update_ok'],1);
        } else {
          msg($this->lang['update_fail'],-1);
        }
    }

    /*
     * retrieve & clean user data from the form
     * return an array(user, password, full name, email, array(groups))
     */
    function _retrieveUser($clean=true) {
  
        $user[0] = ($clean) ? cleanID(preg_replace('/.*:/','',$_REQUEST['userid'])) : $_REQUEST['userid'];
        $user[1] = $_REQUEST['userpass'];
        $user[2] = $_REQUEST['username'];
        $user[3] = $_REQUEST['usermail'];
        $user[4] = preg_split('/\s*,\s*/',$_REQUEST['usergroups'],-1,PREG_SPLIT_NO_EMPTY);

        if (is_array($user[4]) && (count($user[4]) == 1) && (trim($user[4][0]) == '')) {
            $user[4] = null;
        }

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

        $buttons['start'] = $buttons['prev'] = ($this->_start == 0) ? 'disabled="disabled"' : '';
        $buttons['last'] = $buttons['next'] = (($this->_start + $this->_pagesize) >= $this->_user_total) ? 'disabled="disabled"' : '';

        return $buttons;
    }
}
