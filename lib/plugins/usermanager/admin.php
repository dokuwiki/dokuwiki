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

/**
 * All DokuWiki plugins to extend the admin function
 * need to inherit from this class
 */
class admin_plugin_usermanager extends DokuWiki_Admin_Plugin
{
    const IMAGE_DIR = DOKU_BASE.'lib/plugins/usermanager/images/';

    protected $auth = null;        // auth object
    protected $users_total = 0;     // number of registered users
    protected $filter = array();   // user selection filter(s)
    protected $start = 0;          // index of first user to be displayed
    protected $last = 0;           // index of the last user to be displayed
    protected $pagesize = 20;      // number of users to list on one page
    protected $edit_user = '';     // set to user selected for editing
    protected $edit_userdata = array();
    protected $disabled = '';      // if disabled set to explanatory string
    protected $import_failures = array();
    protected $lastdisabled = false; // set to true if last user is unknown and last button is hence buggy

    /**
     * Constructor
     */
    public function __construct()
    {
        /** @var DokuWiki_Auth_Plugin $auth */
        global $auth;

        $this->setupLocale();

        if (!isset($auth)) {
            $this->disabled = $this->lang['noauth'];
        } elseif (!$auth->canDo('getUsers')) {
            $this->disabled = $this->lang['nosupport'];
        } else {
            // we're good to go
            $this->auth = & $auth;
        }

        // attempt to retrieve any import failures from the session
        if (!empty($_SESSION['import_failures'])) {
            $this->import_failures = $_SESSION['import_failures'];
        }
    }

    /**
     * Return prompt for admin menu
     *
     * @param string $language
     * @return string
     */
    public function getMenuText($language)
    {

        if (!is_null($this->auth))
          return parent::getMenuText($language);

        return $this->getLang('menu').' '.$this->disabled;
    }

    /**
     * return sort order for position in admin menu
     *
     * @return int
     */
    public function getMenuSort()
    {
        return 2;
    }

    /**
     * @return int current start value for pageination
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @return int number of users per page
     */
    public function getPagesize()
    {
        return $this->pagesize;
    }

    /**
     * @param boolean $lastdisabled
     */
    public function setLastdisabled($lastdisabled)
    {
        $this->lastdisabled = $lastdisabled;
    }

    /**
     * Handle user request
     *
     * @return bool
     */
    public function handle()
    {
        global $INPUT;
        if (is_null($this->auth)) return false;

        // extract the command and any specific parameters
        // submit button name is of the form - fn[cmd][param(s)]
        $fn   = $INPUT->param('fn');

        if (is_array($fn)) {
            $cmd = key($fn);
            $param = is_array($fn[$cmd]) ? key($fn[$cmd]) : null;
        } else {
            $cmd = $fn;
            $param = null;
        }

        if ($cmd != "search") {
            $this->start = $INPUT->int('start', 0);
            $this->filter = $this->retrieveFilter();
        }

        switch ($cmd) {
            case "add":
                $this->addUser();
                break;
            case "delete":
                $this->deleteUser();
                break;
            case "modify":
                $this->modifyUser();
                break;
            case "edit":
                $this->editUser($param);
                break;
            case "search":
                $this->setFilter($param);
                            $this->start = 0;
                break;
            case "export":
                $this->exportCSV();
                break;
            case "import":
                $this->importCSV();
                break;
            case "importfails":
                $this->downloadImportFailures();
                break;
        }

        $this->users_total = $this->auth->canDo('getUserCount') ? $this->auth->getUserCount($this->filter) : -1;

        // page handling
        switch ($cmd) {
            case 'start':
                $this->start = 0;
                break;
            case 'prev':
                $this->start -= $this->pagesize;
                break;
            case 'next':
                $this->start += $this->pagesize;
                break;
            case 'last':
                $this->start = $this->users_total;
                break;
        }
        $this->validatePagination();
        return true;
    }

    /**
     * Output appropriate html
     *
     * @return bool
     */
    public function html()
    {
        global $ID;

        if (is_null($this->auth)) {
            print $this->lang['badauth'];
            return false;
        }

        $user_list = $this->auth->retrieveUsers($this->start, $this->pagesize, $this->filter);

        $page_buttons = $this->pagination();
        $delete_disable = $this->auth->canDo('delUser') ? '' : 'disabled="disabled"';

        $editable = $this->auth->canDo('UserMod');
        $export_label = empty($this->filter) ? $this->lang['export_all'] : $this->lang['export_filtered'];

        print $this->locale_xhtml('intro');
        print $this->locale_xhtml('list');

        ptln("<div id=\"user__manager\">");
        ptln("<div class=\"level2\">");

        if ($this->users_total > 0) {
            ptln(
                "<p>" . sprintf(
                    $this->lang['summary'],
                    $this->start + 1,
                    $this->last,
                    $this->users_total,
                    $this->auth->getUserCount()
                ) . "</p>"
            );
        } else {
            if ($this->users_total < 0) {
                $allUserTotal = 0;
            } else {
                $allUserTotal = $this->auth->getUserCount();
            }
            ptln("<p>".sprintf($this->lang['nonefound'], $allUserTotal)."</p>");
        }
        ptln("<form action=\"".wl($ID)."\" method=\"post\">");
        formSecurityToken();
        ptln("  <div class=\"table\">");
        ptln("  <table class=\"inline\">");
        ptln("    <thead>");
        ptln("      <tr>");
        ptln("        <th>&#160;</th>
            <th>".$this->lang["user_id"]."</th>
            <th>".$this->lang["user_name"]."</th>
            <th>".$this->lang["user_mail"]."</th>
            <th>".$this->lang["user_groups"]."</th>");
        ptln("      </tr>");

        ptln("      <tr>");
        ptln("        <td class=\"rightalign\"><input type=\"image\" src=\"".
             self::IMAGE_DIR."search.png\" name=\"fn[search][new]\" title=\"".
             $this->lang['search_prompt']."\" alt=\"".$this->lang['search']."\" class=\"button\" /></td>");
        ptln("        <td><input type=\"text\" name=\"userid\" class=\"edit\" value=\"".
             $this->htmlFilter('user')."\" /></td>");
        ptln("        <td><input type=\"text\" name=\"username\" class=\"edit\" value=\"".
             $this->htmlFilter('name')."\" /></td>");
        ptln("        <td><input type=\"text\" name=\"usermail\" class=\"edit\" value=\"".
             $this->htmlFilter('mail')."\" /></td>");
        ptln("        <td><input type=\"text\" name=\"usergroups\" class=\"edit\" value=\"".
             $this->htmlFilter('grps')."\" /></td>");
        ptln("      </tr>");
        ptln("    </thead>");

        if ($this->users_total) {
            ptln("    <tbody>");
            foreach ($user_list as $user => $userinfo) {
                extract($userinfo);
                /**
                 * @var string $name
                 * @var string $pass
                 * @var string $mail
                 * @var array  $grps
                 */
                $groups = join(', ', $grps);
                ptln("    <tr class=\"user_info\">");
                ptln("      <td class=\"centeralign\"><input type=\"checkbox\" name=\"delete[".hsc($user).
                     "]\" ".$delete_disable." /></td>");
                if ($editable) {
                    ptln("    <td><a href=\"".wl($ID, array('fn[edit]['.$user.']' => 1,
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
        ptln("          <button type=\"submit\" name=\"fn[delete]\" id=\"usrmgr__del\" ".$delete_disable.">".
             $this->lang['delete_selected']."</button>");
        ptln("        </span>");
        ptln("        <span class=\"mediaright\">");
        ptln("          <button type=\"submit\" name=\"fn[start]\" ".$page_buttons['start'].">".
             $this->lang['start']."</button>");
        ptln("          <button type=\"submit\" name=\"fn[prev]\" ".$page_buttons['prev'].">".
             $this->lang['prev']."</button>");
        ptln("          <button type=\"submit\" name=\"fn[next]\" ".$page_buttons['next'].">".
             $this->lang['next']."</button>");
        ptln("          <button type=\"submit\" name=\"fn[last]\" ".$page_buttons['last'].">".
             $this->lang['last']."</button>");
        ptln("        </span>");
        if (!empty($this->filter)) {
            ptln("    <button type=\"submit\" name=\"fn[search][clear]\">".$this->lang['clear']."</button>");
        }
        ptln("        <button type=\"submit\" name=\"fn[export]\">".$export_label."</button>");
        ptln("        <input type=\"hidden\" name=\"do\"    value=\"admin\" />");
        ptln("        <input type=\"hidden\" name=\"page\"  value=\"usermanager\" />");

        $this->htmlFilterSettings(2);

        ptln("      </td></tr>");
        ptln("    </tbody>");
        ptln("  </table>");
        ptln("  </div>");

        ptln("</form>");
        ptln("</div>");

        $style = $this->edit_user ? " class=\"edit_user\"" : "";

        if ($this->auth->canDo('addUser')) {
            ptln("<div".$style.">");
            print $this->locale_xhtml('add');
            ptln("  <div class=\"level2\">");

            $this->htmlUserForm('add', null, array(), 4);

            ptln("  </div>");
            ptln("</div>");
        }

        if ($this->edit_user  && $this->auth->canDo('UserMod')) {
            ptln("<div".$style." id=\"scroll__here\">");
            print $this->locale_xhtml('edit');
            ptln("  <div class=\"level2\">");

            $this->htmlUserForm('modify', $this->edit_user, $this->edit_userdata, 4);

            ptln("  </div>");
            ptln("</div>");
        }

        if ($this->auth->canDo('addUser')) {
            $this->htmlImportForm();
        }
        ptln("</div>");
        return true;
    }

    /**
     * User Manager is only available if the auth backend supports it
     *
     * @inheritdoc
     * @return bool
     */
    public function isAccessibleByCurrentUser()
    {
        /** @var DokuWiki_Auth_Plugin $auth */
        global $auth;
        if(!$auth || !$auth->canDo('getUsers') ) {
            return false;
        }

        return parent::isAccessibleByCurrentUser();
    }


    /**
     * Display form to add or modify a user
     *
     * @param string $cmd 'add' or 'modify'
     * @param string $user id of user
     * @param array  $userdata array with name, mail, pass and grps
     * @param int    $indent
     */
    protected function htmlUserForm($cmd, $user = '', $userdata = array(), $indent = 0)
    {
        global $conf;
        global $ID;
        global $lang;

        $name = $mail = $groups = '';
        $notes = array();

        if ($user) {
            extract($userdata);
            if (!empty($grps)) $groups = join(',', $grps);
        } else {
            $notes[] = sprintf($this->lang['note_group'], $conf['defaultgroup']);
        }

        ptln("<form action=\"".wl($ID)."\" method=\"post\">", $indent);
        formSecurityToken();
        ptln("  <div class=\"table\">", $indent);
        ptln("  <table class=\"inline\">", $indent);
        ptln("    <thead>", $indent);
        ptln("      <tr><th>".$this->lang["field"]."</th><th>".$this->lang["value"]."</th></tr>", $indent);
        ptln("    </thead>", $indent);
        ptln("    <tbody>", $indent);

        $this->htmlInputField(
            $cmd . "_userid",
            "userid",
            $this->lang["user_id"],
            $user,
            $this->auth->canDo("modLogin"),
            true,
            $indent + 6
        );
        $this->htmlInputField(
            $cmd . "_userpass",
            "userpass",
            $this->lang["user_pass"],
            "",
            $this->auth->canDo("modPass"),
            false,
            $indent + 6
        );
        $this->htmlInputField(
            $cmd . "_userpass2",
            "userpass2",
            $lang["passchk"],
            "",
            $this->auth->canDo("modPass"),
            false,
            $indent + 6
        );
        $this->htmlInputField(
            $cmd . "_username",
            "username",
            $this->lang["user_name"],
            $name,
            $this->auth->canDo("modName"),
            true,
            $indent + 6
        );
        $this->htmlInputField(
            $cmd . "_usermail",
            "usermail",
            $this->lang["user_mail"],
            $mail,
            $this->auth->canDo("modMail"),
            true,
            $indent + 6
        );
        $this->htmlInputField(
            $cmd . "_usergroups",
            "usergroups",
            $this->lang["user_groups"],
            $groups,
            $this->auth->canDo("modGroups"),
            false,
            $indent + 6
        );

        if ($this->auth->canDo("modPass")) {
            if ($cmd == 'add') {
                $notes[] = $this->lang['note_pass'];
            }
            if ($user) {
                $notes[] = $this->lang['note_notify'];
            }

            ptln("<tr><td><label for=\"".$cmd."_usernotify\" >".
                 $this->lang["user_notify"].": </label></td>
                 <td><input type=\"checkbox\" id=\"".$cmd."_usernotify\" name=\"usernotify\" value=\"1\" />
                 </td></tr>", $indent);
        }

        ptln("    </tbody>", $indent);
        ptln("    <tbody>", $indent);
        ptln("      <tr>", $indent);
        ptln("        <td colspan=\"2\">", $indent);
        ptln("          <input type=\"hidden\" name=\"do\"    value=\"admin\" />", $indent);
        ptln("          <input type=\"hidden\" name=\"page\"  value=\"usermanager\" />", $indent);

        // save current $user, we need this to access details if the name is changed
        if ($user)
          ptln("          <input type=\"hidden\" name=\"userid_old\"  value=\"".hsc($user)."\" />", $indent);

        $this->htmlFilterSettings($indent+10);

        ptln("          <button type=\"submit\" name=\"fn[".$cmd."]\">".$this->lang[$cmd]."</button>", $indent);
        ptln("        </td>", $indent);
        ptln("      </tr>", $indent);
        ptln("    </tbody>", $indent);
        ptln("  </table>", $indent);

        if ($notes) {
            ptln("    <ul class=\"notes\">");
            foreach ($notes as $note) {
                ptln("      <li><span class=\"li\">".$note."</li>", $indent);
            }
            ptln("    </ul>");
        }
        ptln("  </div>", $indent);
        ptln("</form>", $indent);
    }

    /**
     * Prints a inputfield
     *
     * @param string $id
     * @param string $name
     * @param string $label
     * @param string $value
     * @param bool   $cando whether auth backend is capable to do this action
     * @param bool   $required is this field required?
     * @param int $indent
     */
    protected function htmlInputField($id, $name, $label, $value, $cando, $required, $indent = 0)
    {
        $class = $cando ? '' : ' class="disabled"';
        echo str_pad('', $indent);

        if ($name == 'userpass' || $name == 'userpass2') {
            $fieldtype = 'password';
            $autocomp  = 'autocomplete="off"';
        } elseif ($name == 'usermail') {
            $fieldtype = 'email';
            $autocomp  = '';
        } else {
            $fieldtype = 'text';
            $autocomp  = '';
        }
        $value = hsc($value);

        echo "<tr $class>";
        echo "<td><label for=\"$id\" >$label: </label></td>";
        echo "<td>";
        if ($cando) {
            $req = '';
            if ($required) $req = 'required="required"';
            echo "<input type=\"$fieldtype\" id=\"$id\" name=\"$name\"
                  value=\"$value\" class=\"edit\" $autocomp $req />";
        } else {
            echo "<input type=\"hidden\" name=\"$name\" value=\"$value\" />";
            echo "<input type=\"$fieldtype\" id=\"$id\" name=\"$name\"
                  value=\"$value\" class=\"edit disabled\" disabled=\"disabled\" />";
        }
        echo "</td>";
        echo "</tr>";
    }

    /**
     * Returns htmlescaped filter value
     *
     * @param string $key name of search field
     * @return string html escaped value
     */
    protected function htmlFilter($key)
    {
        if (empty($this->filter)) return '';
        return (isset($this->filter[$key]) ? hsc($this->filter[$key]) : '');
    }

    /**
     * Print hidden inputs with the current filter values
     *
     * @param int $indent
     */
    protected function htmlFilterSettings($indent = 0)
    {

        ptln("<input type=\"hidden\" name=\"start\" value=\"".$this->start."\" />", $indent);

        foreach ($this->filter as $key => $filter) {
            ptln("<input type=\"hidden\" name=\"filter[".$key."]\" value=\"".hsc($filter)."\" />", $indent);
        }
    }

    /**
     * Print import form and summary of previous import
     *
     * @param int $indent
     */
    protected function htmlImportForm($indent = 0)
    {
        global $ID;

        $failure_download_link = wl($ID, array('do'=>'admin','page'=>'usermanager','fn[importfails]'=>1));

        ptln('<div class="level2 import_users">', $indent);
        print $this->locale_xhtml('import');
        ptln('  <form action="'.wl($ID).'" method="post" enctype="multipart/form-data">', $indent);
        formSecurityToken();
        ptln('    <label>'.$this->lang['import_userlistcsv'].'<input type="file" name="import" /></label>', $indent);
        ptln('    <button type="submit" name="fn[import]">'.$this->lang['import'].'</button>', $indent);
        ptln('    <input type="hidden" name="do"    value="admin" />', $indent);
        ptln('    <input type="hidden" name="page"  value="usermanager" />', $indent);

        $this->htmlFilterSettings($indent+4);
        ptln('  </form>', $indent);
        ptln('</div>');

        // list failures from the previous import
        if ($this->import_failures) {
            $digits = strlen(count($this->import_failures));
            ptln('<div class="level3 import_failures">', $indent);
            ptln('  <h3>'.$this->lang['import_header'].'</h3>');
            ptln('  <table class="import_failures">', $indent);
            ptln('    <thead>', $indent);
            ptln('      <tr>', $indent);
            ptln('        <th class="line">'.$this->lang['line'].'</th>', $indent);
            ptln('        <th class="error">'.$this->lang['error'].'</th>', $indent);
            ptln('        <th class="userid">'.$this->lang['user_id'].'</th>', $indent);
            ptln('        <th class="username">'.$this->lang['user_name'].'</th>', $indent);
            ptln('        <th class="usermail">'.$this->lang['user_mail'].'</th>', $indent);
            ptln('        <th class="usergroups">'.$this->lang['user_groups'].'</th>', $indent);
            ptln('      </tr>', $indent);
            ptln('    </thead>', $indent);
            ptln('    <tbody>', $indent);
            foreach ($this->import_failures as $line => $failure) {
                ptln('      <tr>', $indent);
                ptln('        <td class="lineno"> '.sprintf('%0'.$digits.'d', $line).' </td>', $indent);
                ptln('        <td class="error">' .$failure['error'].' </td>', $indent);
                ptln('        <td class="field userid"> '.hsc($failure['user'][0]).' </td>', $indent);
                ptln('        <td class="field username"> '.hsc($failure['user'][2]).' </td>', $indent);
                ptln('        <td class="field usermail"> '.hsc($failure['user'][3]).' </td>', $indent);
                ptln('        <td class="field usergroups"> '.hsc($failure['user'][4]).' </td>', $indent);
                ptln('      </tr>', $indent);
            }
            ptln('    </tbody>', $indent);
            ptln('  </table>', $indent);
            ptln('  <p><a href="'.$failure_download_link.'">'.$this->lang['import_downloadfailures'].'</a></p>');
            ptln('</div>');
        }
    }

    /**
     * Add an user to auth backend
     *
     * @return bool whether succesful
     */
    protected function addUser()
    {
        global $INPUT;
        if (!checkSecurityToken()) return false;
        if (!$this->auth->canDo('addUser')) return false;

        list($user,$pass,$name,$mail,$grps,$passconfirm) = $this->retrieveUser();
        if (empty($user)) return false;

        if ($this->auth->canDo('modPass')) {
            if (empty($pass)) {
                if ($INPUT->has('usernotify')) {
                    $pass = auth_pwgen($user);
                } else {
                    msg($this->lang['add_fail'], -1);
                    msg($this->lang['addUser_error_missing_pass'], -1);
                    return false;
                }
            } else {
                if (!$this->verifyPassword($pass, $passconfirm)) {
                    msg($this->lang['add_fail'], -1);
                    msg($this->lang['addUser_error_pass_not_identical'], -1);
                    return false;
                }
            }
        } else {
            if (!empty($pass)) {
                msg($this->lang['add_fail'], -1);
                msg($this->lang['addUser_error_modPass_disabled'], -1);
                return false;
            }
        }

        if ($this->auth->canDo('modName')) {
            if (empty($name)) {
                msg($this->lang['add_fail'], -1);
                msg($this->lang['addUser_error_name_missing'], -1);
                return false;
            }
        } else {
            if (!empty($name)) {
                msg($this->lang['add_fail'], -1);
                msg($this->lang['addUser_error_modName_disabled'], -1);
                return false;
            }
        }

        if ($this->auth->canDo('modMail')) {
            if (empty($mail)) {
                msg($this->lang['add_fail'], -1);
                msg($this->lang['addUser_error_mail_missing'], -1);
                return false;
            }
        } else {
            if (!empty($mail)) {
                msg($this->lang['add_fail'], -1);
                msg($this->lang['addUser_error_modMail_disabled'], -1);
                return false;
            }
        }

        if ($ok = $this->auth->triggerUserMod('create', array($user, $pass, $name, $mail, $grps))) {
            msg($this->lang['add_ok'], 1);

            if ($INPUT->has('usernotify') && $pass) {
                $this->notifyUser($user, $pass);
            }
        } else {
            msg($this->lang['add_fail'], -1);
            msg($this->lang['addUser_error_create_event_failed'], -1);
        }

        return $ok;
    }

    /**
     * Delete user from auth backend
     *
     * @return bool whether succesful
     */
    protected function deleteUser()
    {
        global $conf, $INPUT;

        if (!checkSecurityToken()) return false;
        if (!$this->auth->canDo('delUser')) return false;

        $selected = $INPUT->arr('delete');
        if (empty($selected)) return false;
        $selected = array_keys($selected);

        if (in_array($_SERVER['REMOTE_USER'], $selected)) {
            msg("You can't delete yourself!", -1);
            return false;
        }

        $count = $this->auth->triggerUserMod('delete', array($selected));
        if ($count == count($selected)) {
            $text = str_replace('%d', $count, $this->lang['delete_ok']);
            msg("$text.", 1);
        } else {
            $part1 = str_replace('%d', $count, $this->lang['delete_ok']);
            $part2 = str_replace('%d', (count($selected)-$count), $this->lang['delete_fail']);
            msg("$part1, $part2", -1);
        }

        // invalidate all sessions
        io_saveFile($conf['cachedir'].'/sessionpurge', time());

        return true;
    }

    /**
     * Edit user (a user has been selected for editing)
     *
     * @param string $param id of the user
     * @return bool whether succesful
     */
    protected function editUser($param)
    {
        if (!checkSecurityToken()) return false;
        if (!$this->auth->canDo('UserMod')) return false;
        $user = $this->auth->cleanUser(preg_replace('/.*[:\/]/', '', $param));
        $userdata = $this->auth->getUserData($user);

        // no user found?
        if (!$userdata) {
            msg($this->lang['edit_usermissing'], -1);
            return false;
        }

        $this->edit_user = $user;
        $this->edit_userdata = $userdata;

        return true;
    }

    /**
     * Modify user in the auth backend (modified user data has been recieved)
     *
     * @return bool whether succesful
     */
    protected function modifyUser()
    {
        global $conf, $INPUT;

        if (!checkSecurityToken()) return false;
        if (!$this->auth->canDo('UserMod')) return false;

        // get currently valid  user data
        $olduser = $this->auth->cleanUser(preg_replace('/.*[:\/]/', '', $INPUT->str('userid_old')));
        $oldinfo = $this->auth->getUserData($olduser);

        // get new user data subject to change
        list($newuser,$newpass,$newname,$newmail,$newgrps,$passconfirm) = $this->retrieveUser();
        if (empty($newuser)) return false;

        $changes = array();
        if ($newuser != $olduser) {
            if (!$this->auth->canDo('modLogin')) {        // sanity check, shouldn't be possible
                msg($this->lang['update_fail'], -1);
                return false;
            }

            // check if $newuser already exists
            if ($this->auth->getUserData($newuser)) {
                msg(sprintf($this->lang['update_exists'], $newuser), -1);
                $re_edit = true;
            } else {
                $changes['user'] = $newuser;
            }
        }
        if ($this->auth->canDo('modPass')) {
            if ($newpass || $passconfirm) {
                if ($this->verifyPassword($newpass, $passconfirm)) {
                    $changes['pass'] = $newpass;
                } else {
                    return false;
                }
            } else {
                // no new password supplied, check if we need to generate one (or it stays unchanged)
                if ($INPUT->has('usernotify')) {
                    $changes['pass'] = auth_pwgen($olduser);
                }
            }
        }

        if (!empty($newname) && $this->auth->canDo('modName') && $newname != $oldinfo['name']) {
            $changes['name'] = $newname;
        }
        if (!empty($newmail) && $this->auth->canDo('modMail') && $newmail != $oldinfo['mail']) {
            $changes['mail'] = $newmail;
        }
        if (!empty($newgrps) && $this->auth->canDo('modGroups') && $newgrps != $oldinfo['grps']) {
            $changes['grps'] = $newgrps;
        }

        if ($ok = $this->auth->triggerUserMod('modify', array($olduser, $changes))) {
            msg($this->lang['update_ok'], 1);

            if ($INPUT->has('usernotify') && !empty($changes['pass'])) {
                $notify = empty($changes['user']) ? $olduser : $newuser;
                $this->notifyUser($notify, $changes['pass']);
            }

            // invalidate all sessions
            io_saveFile($conf['cachedir'].'/sessionpurge', time());
        } else {
            msg($this->lang['update_fail'], -1);
        }

        if (!empty($re_edit)) {
            $this->editUser($olduser);
        }

        return $ok;
    }

    /**
     * Send password change notification email
     *
     * @param string $user         id of user
     * @param string $password     plain text
     * @param bool   $status_alert whether status alert should be shown
     * @return bool whether succesful
     */
    protected function notifyUser($user, $password, $status_alert = true)
    {

        if ($sent = auth_sendPassword($user, $password)) {
            if ($status_alert) {
                msg($this->lang['notify_ok'], 1);
            }
        } else {
            if ($status_alert) {
                msg($this->lang['notify_fail'], -1);
            }
        }

        return $sent;
    }

    /**
     * Verify password meets minimum requirements
     * :TODO: extend to support password strength
     *
     * @param string  $password   candidate string for new password
     * @param string  $confirm    repeated password for confirmation
     * @return bool   true if meets requirements, false otherwise
     */
    protected function verifyPassword($password, $confirm)
    {
        global $lang;

        if (empty($password) && empty($confirm)) {
            return false;
        }

        if ($password !== $confirm) {
            msg($lang['regbadpass'], -1);
            return false;
        }

        // :TODO: test password for required strength

        // if we make it this far the password is good
        return true;
    }

    /**
     * Retrieve & clean user data from the form
     *
     * @param bool $clean whether the cleanUser method of the authentication backend is applied
     * @return array (user, password, full name, email, array(groups))
     */
    protected function retrieveUser($clean = true)
    {
        /** @var DokuWiki_Auth_Plugin $auth */
        global $auth;
        global $INPUT;

        $user = array();
        $user[0] = ($clean) ? $auth->cleanUser($INPUT->str('userid')) : $INPUT->str('userid');
        $user[1] = $INPUT->str('userpass');
        $user[2] = $INPUT->str('username');
        $user[3] = $INPUT->str('usermail');
        $user[4] = explode(',', $INPUT->str('usergroups'));
        $user[5] = $INPUT->str('userpass2');                // repeated password for confirmation

        $user[4] = array_map('trim', $user[4]);
        if ($clean) $user[4] = array_map(array($auth,'cleanGroup'), $user[4]);
        $user[4] = array_filter($user[4]);
        $user[4] = array_unique($user[4]);
        if (!count($user[4])) $user[4] = null;

        return $user;
    }

    /**
     * Set the filter with the current search terms or clear the filter
     *
     * @param string $op 'new' or 'clear'
     */
    protected function setFilter($op)
    {

        $this->filter = array();

        if ($op == 'new') {
            list($user,/* $pass */,$name,$mail,$grps) = $this->retrieveUser(false);

            if (!empty($user)) $this->filter['user'] = $user;
            if (!empty($name)) $this->filter['name'] = $name;
            if (!empty($mail)) $this->filter['mail'] = $mail;
            if (!empty($grps)) $this->filter['grps'] = join('|', $grps);
        }
    }

    /**
     * Get the current search terms
     *
     * @return array
     */
    protected function retrieveFilter()
    {
        global $INPUT;

        $t_filter = $INPUT->arr('filter');

        // messy, but this way we ensure we aren't getting any additional crap from malicious users
        $filter = array();

        if (isset($t_filter['user'])) $filter['user'] = $t_filter['user'];
        if (isset($t_filter['name'])) $filter['name'] = $t_filter['name'];
        if (isset($t_filter['mail'])) $filter['mail'] = $t_filter['mail'];
        if (isset($t_filter['grps'])) $filter['grps'] = $t_filter['grps'];

        return $filter;
    }

    /**
     * Validate and improve the pagination values
     */
    protected function validatePagination()
    {

        if ($this->start >= $this->users_total) {
            $this->start = $this->users_total - $this->pagesize;
        }
        if ($this->start < 0) $this->start = 0;

        $this->last = min($this->users_total, $this->start + $this->pagesize);
    }

    /**
     * Return an array of strings to enable/disable pagination buttons
     *
     * @return array with enable/disable attributes
     */
    protected function pagination()
    {

        $disabled = 'disabled="disabled"';

        $buttons = array();
        $buttons['start'] = $buttons['prev'] = ($this->start == 0) ? $disabled : '';

        if ($this->users_total == -1) {
            $buttons['last'] = $disabled;
            $buttons['next'] = '';
        } else {
            $buttons['last'] = $buttons['next'] =
                (($this->start + $this->pagesize) >= $this->users_total) ? $disabled : '';
        }

        if ($this->lastdisabled) {
            $buttons['last'] = $disabled;
        }

        return $buttons;
    }

    /**
     * Export a list of users in csv format using the current filter criteria
     */
    protected function exportCSV()
    {
        // list of users for export - based on current filter criteria
        $user_list = $this->auth->retrieveUsers(0, 0, $this->filter);
        $column_headings = array(
            $this->lang["user_id"],
            $this->lang["user_name"],
            $this->lang["user_mail"],
            $this->lang["user_groups"]
        );

        // ==============================================================================================
        // GENERATE OUTPUT
        // normal headers for downloading...
        header('Content-type: text/csv;charset=utf-8');
        header('Content-Disposition: attachment; filename="wikiusers.csv"');
#       // for debugging assistance, send as text plain to the browser
#       header('Content-type: text/plain;charset=utf-8');

        // output the csv
        $fd = fopen('php://output', 'w');
        fputcsv($fd, $column_headings);
        foreach ($user_list as $user => $info) {
            $line = array($user, $info['name'], $info['mail'], join(',', $info['grps']));
            fputcsv($fd, $line);
        }
        fclose($fd);
        if (defined('DOKU_UNITTEST')) {
            return;
        }

        die;
    }

    /**
     * Import a file of users in csv format
     *
     * csv file should have 4 columns, user_id, full name, email, groups (comma separated)
     *
     * @return bool whether successful
     */
    protected function importCSV()
    {
        // check we are allowed to add users
        if (!checkSecurityToken()) return false;
        if (!$this->auth->canDo('addUser')) return false;

        // check file uploaded ok.
        if (empty($_FILES['import']['size']) ||
            !empty($_FILES['import']['error']) && $this->isUploadedFile($_FILES['import']['tmp_name'])
        ) {
            msg($this->lang['import_error_upload'], -1);
            return false;
        }
        // retrieve users from the file
        $this->import_failures = array();
        $import_success_count = 0;
        $import_fail_count = 0;
        $line = 0;
        $fd = fopen($_FILES['import']['tmp_name'], 'r');
        if ($fd) {
            while ($csv = fgets($fd)) {
                if (!\dokuwiki\Utf8\Clean::isUtf8($csv)) {
                    $csv = utf8_encode($csv);
                }
                $raw = str_getcsv($csv);
                $error = '';                        // clean out any errors from the previous line
                // data checks...
                if (1 == ++$line) {
                    if ($raw[0] == 'user_id' || $raw[0] == $this->lang['user_id']) continue;    // skip headers
                }
                if (count($raw) < 4) {                                        // need at least four fields
                    $import_fail_count++;
                    $error = sprintf($this->lang['import_error_fields'], count($raw));
                    $this->import_failures[$line] = array('error' => $error, 'user' => $raw, 'orig' => $csv);
                    continue;
                }
                array_splice($raw, 1, 0, auth_pwgen());                          // splice in a generated password
                $clean = $this->cleanImportUser($raw, $error);
                if ($clean && $this->importUser($clean, $error)) {
                    $sent = $this->notifyUser($clean[0], $clean[1], false);
                    if (!$sent) {
                        msg(sprintf($this->lang['import_notify_fail'], $clean[0], $clean[3]), -1);
                    }
                    $import_success_count++;
                } else {
                    $import_fail_count++;
                    array_splice($raw, 1, 1);                                  // remove the spliced in password
                    $this->import_failures[$line] = array('error' => $error, 'user' => $raw, 'orig' => $csv);
                }
            }
            msg(
                sprintf(
                    $this->lang['import_success_count'],
                    ($import_success_count + $import_fail_count),
                    $import_success_count
                ),
                ($import_success_count ? 1 : -1)
            );
            if ($import_fail_count) {
                msg(sprintf($this->lang['import_failure_count'], $import_fail_count), -1);
            }
        } else {
            msg($this->lang['import_error_readfail'], -1);
        }

        // save import failures into the session
        if (!headers_sent()) {
            session_start();
            $_SESSION['import_failures'] = $this->import_failures;
            session_write_close();
        }
        return true;
    }

    /**
     * Returns cleaned user data
     *
     * @param array $candidate raw values of line from input file
     * @param string $error
     * @return array|false cleaned data or false
     */
    protected function cleanImportUser($candidate, & $error)
    {
        global $INPUT;

        // FIXME kludgy ....
        $INPUT->set('userid', $candidate[0]);
        $INPUT->set('userpass', $candidate[1]);
        $INPUT->set('username', $candidate[2]);
        $INPUT->set('usermail', $candidate[3]);
        $INPUT->set('usergroups', $candidate[4]);

        $cleaned = $this->retrieveUser();
        list($user,/* $pass */,$name,$mail,/* $grps */) = $cleaned;
        if (empty($user)) {
            $error = $this->lang['import_error_baduserid'];
            return false;
        }

        // no need to check password, handled elsewhere

        if (!($this->auth->canDo('modName') xor empty($name))) {
            $error = $this->lang['import_error_badname'];
            return false;
        }

        if ($this->auth->canDo('modMail')) {
            if (empty($mail) || !mail_isvalid($mail)) {
                $error = $this->lang['import_error_badmail'];
                return false;
            }
        } else {
            if (!empty($mail)) {
                $error = $this->lang['import_error_badmail'];
                return false;
            }
        }

        return $cleaned;
    }

    /**
     * Adds imported user to auth backend
     *
     * Required a check of canDo('addUser') before
     *
     * @param array  $user   data of user
     * @param string &$error reference catched error message
     * @return bool whether successful
     */
    protected function importUser($user, &$error)
    {
        if (!$this->auth->triggerUserMod('create', $user)) {
            $error = $this->lang['import_error_create'];
            return false;
        }

        return true;
    }

    /**
     * Downloads failures as csv file
     */
    protected function downloadImportFailures()
    {

        // ==============================================================================================
        // GENERATE OUTPUT
        // normal headers for downloading...
        header('Content-type: text/csv;charset=utf-8');
        header('Content-Disposition: attachment; filename="importfails.csv"');
#       // for debugging assistance, send as text plain to the browser
#       header('Content-type: text/plain;charset=utf-8');

        // output the csv
        $fd = fopen('php://output', 'w');
        foreach ($this->import_failures as $fail) {
            fputs($fd, $fail['orig']);
        }
        fclose($fd);
        die;
    }

    /**
     * wrapper for is_uploaded_file to facilitate overriding by test suite
     *
     * @param string $file filename
     * @return bool
     */
    protected function isUploadedFile($file)
    {
        return is_uploaded_file($file);
    }
}
