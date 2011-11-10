<?php
/**
 * Plaintext authentication backend
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Chris Smith <chris@jalakai.co.uk>
 */

class auth_plain extends auth_basic {

    var $users = null;
    var $_pattern = array();

    /**
     * Constructor
     *
     * Carry out sanity checks to ensure the object is
     * able to operate. Set capabilities.
     *
     * @author  Christopher Smith <chris@jalakai.co.uk>
     */
    function auth_plain() {
      global $config_cascade;

      if (!@is_readable($config_cascade['plainauth.users']['default'])){
        $this->success = false;
      }else{
        if(@is_writable($config_cascade['plainauth.users']['default'])){
          $this->cando['addUser']      = true;
          $this->cando['delUser']      = true;
          $this->cando['modLogin']     = true;
          $this->cando['modPass']      = true;
          $this->cando['modName']      = true;
          $this->cando['modMail']      = true;
          $this->cando['modGroups']    = true;
        }
        $this->cando['getUsers']     = true;
        $this->cando['getUserCount'] = true;
      }
    }

    /**
     * Check user+password [required auth function]
     *
     * Checks if the given user exists and the given
     * plaintext password is correct
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @return  bool
     */
    function checkPass($user,$pass){

      $userinfo = $this->getUserData($user);
      if ($userinfo === false) return false;

      return auth_verifyPassword($pass,$this->users[$user]['pass']);
    }

    /**
     * Return user info
     *
     * Returns info about the given user needs to contain
     * at least these fields:
     *
     * name string  full name of the user
     * mail string  email addres of the user
     * grps array   list of groups the user is in
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     */
    function getUserData($user){

      if($this->users === null) $this->_loadUserData();
      return isset($this->users[$user]) ? $this->users[$user] : false;
    }

    /**
     * Create a new User
     *
     * Returns false if the user already exists, null when an error
     * occurred and true if everything went well.
     *
     * The new user will be added to the default group by this
     * function if grps are not specified (default behaviour).
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @author  Chris Smith <chris@jalakai.co.uk>
     */
    function createUser($user,$pwd,$name,$mail,$grps=null){
      global $conf;
      global $config_cascade;

      // user mustn't already exist
      if ($this->getUserData($user) !== false) return false;

      $pass = auth_cryptPassword($pwd);

      // set default group if no groups specified
      if (!is_array($grps)) $grps = array($conf['defaultgroup']);

      // prepare user line
      $groups = join(',',$grps);
      $userline = join(':',array($user,$pass,$name,$mail,$groups))."\n";

      if (io_saveFile($config_cascade['plainauth.users']['default'],$userline,true)) {
        $this->users[$user] = compact('pass','name','mail','grps');
        return $pwd;
      }

      msg('The '.$config_cascade['plainauth.users']['default'].
          ' file is not writable. Please inform the Wiki-Admin',-1);
      return null;
    }

    /**
     * Modify user data
     *
     * @author  Chris Smith <chris@jalakai.co.uk>
     * @param   $user      nick of the user to be changed
     * @param   $changes   array of field/value pairs to be changed (password will be clear text)
     * @return  bool
     */
    function modifyUser($user, $changes) {
      global $conf;
      global $ACT;
      global $INFO;
      global $config_cascade;

      // sanity checks, user must already exist and there must be something to change
      if (($userinfo = $this->getUserData($user)) === false) return false;
      if (!is_array($changes) || !count($changes)) return true;

      // update userinfo with new data, remembering to encrypt any password
      $newuser = $user;
      foreach ($changes as $field => $value) {
        if ($field == 'user') {
          $newuser = $value;
          continue;
        }
        if ($field == 'pass') $value = auth_cryptPassword($value);
        $userinfo[$field] = $value;
      }

      $groups = join(',',$userinfo['grps']);
      $userline = join(':',array($newuser, $userinfo['pass'], $userinfo['name'], $userinfo['mail'], $groups))."\n";

      if (!$this->deleteUsers(array($user))) {
        msg('Unable to modify user data. Please inform the Wiki-Admin',-1);
        return false;
      }

      if (!io_saveFile($config_cascade['plainauth.users']['default'],$userline,true)) {
        msg('There was an error modifying your user data. You should register again.',-1);
        // FIXME, user has been deleted but not recreated, should force a logout and redirect to login page
        $ACT == 'register';
        return false;
      }

      $this->users[$newuser] = $userinfo;
      return true;
    }

    /**
     *  Remove one or more users from the list of registered users
     *
     *  @author  Christopher Smith <chris@jalakai.co.uk>
     *  @param   array  $users   array of users to be deleted
     *  @return  int             the number of users deleted
     */
    function deleteUsers($users) {
      global $config_cascade;

      if (!is_array($users) || empty($users)) return 0;

      if ($this->users === null) $this->_loadUserData();

      $deleted = array();
      foreach ($users as $user) {
        if (isset($this->users[$user])) $deleted[] = preg_quote($user,'/');
      }

      if (empty($deleted)) return 0;

      $pattern = '/^('.join('|',$deleted).'):/';

      if (io_deleteFromFile($config_cascade['plainauth.users']['default'],$pattern,true)) {
        foreach ($deleted as $user) unset($this->users[$user]);
        return count($deleted);
      }

      // problem deleting, reload the user list and count the difference
      $count = count($this->users);
      $this->_loadUserData();
      $count -= count($this->users);
      return $count;
    }

    /**
     * Return a count of the number of user which meet $filter criteria
     *
     * @author  Chris Smith <chris@jalakai.co.uk>
     */
    function getUserCount($filter=array()) {

      if($this->users === null) $this->_loadUserData();

      if (!count($filter)) return count($this->users);

      $count = 0;
      $this->_constructPattern($filter);

      foreach ($this->users as $user => $info) {
          $count += $this->_filter($user, $info);
      }

      return $count;
    }

    /**
     * Bulk retrieval of user data
     *
     * @author  Chris Smith <chris@jalakai.co.uk>
     * @param   start     index of first user to be returned
     * @param   limit     max number of users to be returned
     * @param   filter    array of field/pattern pairs
     * @return  array of userinfo (refer getUserData for internal userinfo details)
     */
    function retrieveUsers($start=0,$limit=0,$filter=array()) {

      if ($this->users === null) $this->_loadUserData();

      ksort($this->users);

      $i = 0;
      $count = 0;
      $out = array();
      $this->_constructPattern($filter);

      foreach ($this->users as $user => $info) {
        if ($this->_filter($user, $info)) {
          if ($i >= $start) {
            $out[$user] = $info;
            $count++;
            if (($limit > 0) && ($count >= $limit)) break;
          }
          $i++;
        }
      }

      return $out;
    }

    /**
     * Only valid pageid's (no namespaces) for usernames
     */
    function cleanUser($user){
        global $conf;
        return cleanID(str_replace(':',$conf['sepchar'],$user));
    }

    /**
     * Only valid pageid's (no namespaces) for groupnames
     */
    function cleanGroup($group){
        global $conf;
        return cleanID(str_replace(':',$conf['sepchar'],$group));
    }

    /**
     * Load all user data
     *
     * loads the user file into a datastructure
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     */
    function _loadUserData(){
      global $config_cascade;

      $this->users = array();

      if(!@file_exists($config_cascade['plainauth.users']['default'])) return;

      $lines = file($config_cascade['plainauth.users']['default']);
      foreach($lines as $line){
        $line = preg_replace('/#.*$/','',$line); //ignore comments
        $line = trim($line);
        if(empty($line)) continue;

        $row    = explode(":",$line,5);
        $groups = array_values(array_filter(explode(",",$row[4])));

        $this->users[$row[0]]['pass'] = $row[1];
        $this->users[$row[0]]['name'] = urldecode($row[2]);
        $this->users[$row[0]]['mail'] = $row[3];
        $this->users[$row[0]]['grps'] = $groups;
      }
    }

    /**
     * return 1 if $user + $info match $filter criteria, 0 otherwise
     *
     * @author   Chris Smith <chris@jalakai.co.uk>
     */
    function _filter($user, $info) {
        // FIXME
        foreach ($this->_pattern as $item => $pattern) {
            if ($item == 'user') {
                if (!preg_match($pattern, $user)) return 0;
            } else if ($item == 'grps') {
                if (!count(preg_grep($pattern, $info['grps']))) return 0;
            } else {
                if (!preg_match($pattern, $info[$item])) return 0;
            }
        }
        return 1;
    }

    function _constructPattern($filter) {
      $this->_pattern = array();
      foreach ($filter as $item => $pattern) {
//        $this->_pattern[$item] = '/'.preg_quote($pattern,"/").'/i';          // don't allow regex characters
        $this->_pattern[$item] = '/'.str_replace('/','\/',$pattern).'/i';    // allow regex characters
      }
    }
}

//Setup VIM: ex: et ts=2 :
