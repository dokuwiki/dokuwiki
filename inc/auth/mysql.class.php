<?php
/**
 * MySQLP authentication backend
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Chris Smith <chris@jalakai.co.uk>
 * @author     Matthias Grimm <matthias.grimmm@sourceforge.net>
*/

class auth_mysql extends auth_basic {

    var $dbcon        = 0;
    var $dbver        = 0;    // database version
    var $dbrev        = 0;    // database revision
    var $dbsub        = 0;    // database subrevision
    var $cnf          = null;
    var $defaultgroup = "";

    /**
     * Constructor
     *
     * checks if the mysql interface is available, otherwise it will
     * set the variable $success of the basis class to false
     *
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function auth_mysql() {
      global $conf;
      $this->cnf          = $conf['auth']['mysql'];

      if (method_exists($this, 'auth_basic'))
        parent::auth_basic();

      if(!function_exists('mysql_connect')) {
        if ($this->cnf['debug'])
          msg("MySQL err: PHP MySQL extension not found.",-1,__LINE__,__FILE__);
        $this->success = false;
        return;
      }

      // default to UTF-8, you rarely want something else
      if(!isset($this->cnf['charset'])) $this->cnf['charset'] = 'utf8';

      $this->defaultgroup = $conf['defaultgroup'];

      // set capabilities based upon config strings set
      if (empty($this->cnf['server']) || empty($this->cnf['user']) ||
          !isset($this->cnf['password']) || empty($this->cnf['database'])){
        if ($this->cnf['debug'])
          msg("MySQL err: insufficient configuration.",-1,__LINE__,__FILE__);
        $this->success = false;
        return;
      }

      $this->cando['addUser']      = $this->_chkcnf(array('getUserInfo',
                                                          'getGroups',
                                                          'addUser',
                                                          'getUserID',
                                                          'getGroupID',
                                                          'addGroup',
                                                          'addUserGroup'),true);
      $this->cando['delUser']      = $this->_chkcnf(array('getUserID',
                                                          'delUser',
                                                          'delUserRefs'),true);
      $this->cando['modLogin']     = $this->_chkcnf(array('getUserID',
                                                          'updateUser',
                                                          'UpdateTarget'),true);
      $this->cando['modPass']      = $this->cando['modLogin'];
      $this->cando['modName']      = $this->cando['modLogin'];
      $this->cando['modMail']      = $this->cando['modLogin'];
      $this->cando['modGroups']    = $this->_chkcnf(array('getUserID',
                                                          'getGroups',
                                                          'getGroupID',
                                                          'addGroup',
                                                          'addUserGroup',
                                                          'delGroup',
                                                          'getGroupID',
                                                          'delUserGroup'),true);
      /* getGroups is not yet supported
      $this->cando['getGroups']    = $this->_chkcnf(array('getGroups',
                                                          'getGroupID'),false); */
      $this->cando['getUsers']     = $this->_chkcnf(array('getUsers',
                                                          'getUserInfo',
                                                          'getGroups'),false);
      $this->cando['getUserCount'] = $this->_chkcnf(array('getUsers'),false);
    }

    /**
     * Check if the given config strings are set
     *
     * @author  Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     * @return  bool
     */
    function _chkcnf($keys, $wop=false){
      foreach ($keys as $key){
        if (empty($this->cnf[$key])) return false;
      }

      /* write operation and lock array filled with tables names? */
      if ($wop && (!is_array($this->cnf['TablesToLock']) ||
                   !count($this->cnf['TablesToLock']))){
        return false;
      }

      return true;
    }

    /**
     * Checks if the given user exists and the given plaintext password
     * is correct. Furtheron it might be checked wether the user is
     * member of the right group
     *
     * Depending on which SQL string is defined in the config, password
     * checking is done here (getpass) or by the database (passcheck)
     *
     * @param  $user  user who would like access
     * @param  $pass  user's clear text password to check
     * @return bool
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @author  Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function checkPass($user,$pass){
      $rc  = false;

      if($this->_openDB()) {
        $sql    = str_replace('%{user}',$this->_escape($user),$this->cnf['checkPass']);
        $sql    = str_replace('%{pass}',$this->_escape($pass),$sql);
        $sql    = str_replace('%{dgroup}',$this->_escape($this->defaultgroup),$sql);
        $result = $this->_queryDB($sql);

        if($result !== false && count($result) == 1) {
          if($this->cnf['forwardClearPass'] == 1)
            $rc = true;
          else
            $rc = auth_verifyPassword($pass,$result[0]['pass']);
        }
        $this->_closeDB();
      }
      return $rc;
    }

    /**
     * [public function]
     *
     * Returns info about the given user needs to contain
     * at least these fields:
     *   name  string  full name of the user
     *   mail  string  email addres of the user
     *   grps  array   list of groups the user is in
     *
     * @param $user   user's nick to get data for
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @author  Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function getUserData($user){
      if($this->_openDB()) {
        $this->_lockTables("READ");
        $info = $this->_getUserInfo($user);
        $this->_unlockTables();
        $this->_closeDB();
      } else
        $info = false;
      return $info;
    }

    /**
     * [public function]
     *
     * Create a new User. Returns false if the user already exists,
     * null when an error occurred and true if everything went well.
     *
     * The new user will be added to the default group by this
     * function if grps are not specified (default behaviour).
     *
     * @param $user  nick of the user
     * @param $pwd   clear text password
     * @param $name  full name of the user
     * @param $mail  email address
     * @param $grps  array of groups the user should become member of
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @author  Chris Smith <chris@jalakai.co.uk>
     * @author  Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function createUser($user,$pwd,$name,$mail,$grps=null){
      if($this->_openDB()) {
        if (($info = $this->_getUserInfo($user)) !== false)
          return false;  // user already exists

        // set defaultgroup if no groups were given
        if ($grps == null)
          $grps = array($this->defaultgroup);

        $this->_lockTables("WRITE");
        $pwd = $this->cnf['forwardClearPass'] ? $pwd : auth_cryptPassword($pwd);
        $rc = $this->_addUser($user,$pwd,$name,$mail,$grps);
        $this->_unlockTables();
        $this->_closeDB();
        if ($rc) return true;
      }
      return null;  // return error
    }

    /**
     * Modify user data [public function]
     *
     * An existing user dataset will be modified. Changes are given in an array.
     *
     * The dataset update will be rejected if the user name should be changed
     * to an already existing one.
     *
     * The password must be provides unencrypted. Pasword cryption is done
     * automatically if configured.
     *
     * If one or more groups could't be updated, an error would be set. In
     * this case the dataset might already be changed and we can't rollback
     * the changes. Transactions would be really usefull here.
     *
     * modifyUser() may be called without SQL statements defined that are
     * needed to change group membership (for example if only the user profile
     * should be modified). In this case we asure that we don't touch groups
     * even $changes['grps'] is set by mistake.
     *
     * @param   $user     nick of the user to be changed
     * @param   $changes  array of field/value pairs to be changed (password
     *                    will be clear text)
     * @return  bool      true on success, false on error
     *
     * @author  Chris Smith <chris@jalakai.co.uk>
     * @author  Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function modifyUser($user, $changes) {
      $rc = false;

      if (!is_array($changes) || !count($changes))
        return true;  // nothing to change

      if($this->_openDB()) {
        $this->_lockTables("WRITE");

        if (($uid = $this->_getUserID($user))) {
          $rc = $this->_updateUserInfo($changes, $uid);

          if ($rc && isset($changes['grps']) && $this->cando['modGroups']) {
            $groups = $this->_getGroups($user);
            $grpadd = array_diff($changes['grps'], $groups);
            $grpdel = array_diff($groups, $changes['grps']);

            foreach($grpadd as $group)
              if (($this->_addUserToGroup($user, $group, 1)) == false)
                $rc = false;

            foreach($grpdel as $group)
              if (($this->_delUserFromGroup($user, $group)) == false)
                $rc = false;
          }
        }

        $this->_unlockTables();
        $this->_closeDB();
      }
      return $rc;
    }

    /**
     * [public function]
     *
     * Remove one or more users from the list of registered users
     *
     * @param   array  $users   array of users to be deleted
     * @return  int             the number of users deleted
     *
     * @author  Christopher Smith <chris@jalakai.co.uk>
     * @author  Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function deleteUsers($users) {
      $count = 0;

      if($this->_openDB()) {
        if (is_array($users) && count($users)) {
          $this->_lockTables("WRITE");
          foreach ($users as $user) {
            if ($this->_delUser($user))
              $count++;
          }
          $this->_unlockTables();
        }
        $this->_closeDB();
      }
      return $count;
    }

    /**
     * [public function]
     *
     * Counts users which meet certain $filter criteria.
     *
     * @param  array  $filter  filter criteria in item/pattern pairs
     * @return count of found users.
     *
     * @author  Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function getUserCount($filter=array()) {
      $rc = 0;

      if($this->_openDB()) {
        $sql = $this->_createSQLFilter($this->cnf['getUsers'], $filter);

        if ($this->dbver >= 4) {
          $sql = substr($sql, 6);  /* remove 'SELECT' or 'select' */
          $sql = "SELECT SQL_CALC_FOUND_ROWS".$sql." LIMIT 1";
          $this->_queryDB($sql);
          $result = $this->_queryDB("SELECT FOUND_ROWS()");
          $rc = $result[0]['FOUND_ROWS()'];
        } else if (($result = $this->_queryDB($sql)))
          $rc = count($result);

        $this->_closeDB();
      }
      return $rc;
    }

    /**
     * Bulk retrieval of user data. [public function]
     *
     * @param   first     index of first user to be returned
     * @param   limit     max number of users to be returned
     * @param   filter    array of field/pattern pairs
     * @return  array of userinfo (refer getUserData for internal userinfo details)
     *
     * @author  Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function retrieveUsers($first=0,$limit=10,$filter=array()) {
      $out   = array();

      if($this->_openDB()) {
        $this->_lockTables("READ");
        $sql  = $this->_createSQLFilter($this->cnf['getUsers'], $filter);
        $sql .= " ".$this->cnf['SortOrder']." LIMIT $first, $limit";
        $result = $this->_queryDB($sql);

        if (!empty($result)) {
          foreach ($result as $user)
            if (($info = $this->_getUserInfo($user['user'])))
              $out[$user['user']] = $info;
        }

        $this->_unlockTables();
        $this->_closeDB();
      }
      return $out;
    }

    /**
     * Give user membership of a group [public function]
     *
     * @param   $user
     * @param   $group
     * @return  bool    true on success, false on error
     *
     * @author  Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function joinGroup($user, $group) {
      $rc = false;

      if ($this->_openDB()) {
        $this->_lockTables("WRITE");
        $rc  = $this->_addUserToGroup($user, $group);
        $this->_unlockTables();
        $this->_closeDB();
      }
      return $rc;
    }

    /**
     * Remove user from a group [public function]
     *
     * @param   $user    user that leaves a group
     * @param   $group   group to leave
     * @return  bool
     *
     * @author  Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function leaveGroup($user, $group) {
      $rc = false;

      if ($this->_openDB()) {
        $this->_lockTables("WRITE");
        $uid = $this->_getUserID($user);
        $rc  = $this->_delUserFromGroup($user, $group);
        $this->_unlockTables();
        $this->_closeDB();
      }
      return $rc;
    }

    /**
     * MySQL is case-insensitive
     */
    function isCaseSensitive(){
        return false;
    }

    /**
     * Adds a user to a group.
     *
     * If $force is set to '1' non existing groups would be created.
     *
     * The database connection must already be established. Otherwise
     * this function does nothing and returns 'false'. It is strongly
     * recommended to call this function only after all participating
     * tables (group and usergroup) have been locked.
     *
     * @param   $user    user to add to a group
     * @param   $group   name of the group
     * @param   $force   '1' create missing groups
     * @return  bool     'true' on success, 'false' on error
     *
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function _addUserToGroup($user, $group, $force=0) {
      $newgroup = 0;

      if (($this->dbcon) && ($user)) {
        $gid = $this->_getGroupID($group);
        if (!$gid) {
          if ($force) {  // create missing groups
            $sql = str_replace('%{group}',$this->_escape($group),$this->cnf['addGroup']);
            $gid = $this->_modifyDB($sql);
            $newgroup = 1;  // group newly created
          }
          if (!$gid) return false; // group didn't exist and can't be created
        }

        $sql = $this->cnf['addUserGroup'];
        if(strpos($sql,'%{uid}') !== false){
            $uid = $this->_getUserID($user);
            $sql = str_replace('%{uid}',  $this->_escape($uid),$sql);
        }
        $sql = str_replace('%{user}', $this->_escape($user),$sql);
        $sql = str_replace('%{gid}',  $this->_escape($gid),$sql);
        $sql = str_replace('%{group}',$this->_escape($group),$sql);
        if ($this->_modifyDB($sql) !== false) return true;

        if ($newgroup) { // remove previously created group on error
          $sql = str_replace('%{gid}',  $this->_escape($gid),$this->cnf['delGroup']);
          $sql = str_replace('%{group}',$this->_escape($group),$sql);
          $this->_modifyDB($sql);
        }
      }
      return false;
    }

    /**
     * Remove user from a group
     *
     * @param   $user    user that leaves a group
     * @param   $group   group to leave
     * @return  bool     true on success, false on error
     *
     * @author  Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function _delUserFromGroup($user, $group) {
      $rc = false;


      if (($this->dbcon) && ($user)) {
        $sql = $this->cnf['delUserGroup'];
        if(strpos($sql,'%{uid}') !== false){
            $uid = $this->_getUserID($user);
            $sql = str_replace('%{uid}',  $this->_escape($uid),$sql);
        }
        $gid = $this->_getGroupID($group);
        if ($gid) {
          $sql = str_replace('%{user}', $this->_escape($user),$sql);
          $sql = str_replace('%{gid}',  $this->_escape($gid),$sql);
          $sql = str_replace('%{group}',$this->_escape($group),$sql);
          $rc  = $this->_modifyDB($sql) == 0 ? true : false;
        }
      }
      return $rc;
    }

    /**
     * Retrieves a list of groups the user is a member off.
     *
     * The database connection must already be established
     * for this function to work. Otherwise it will return
     * 'false'.
     *
     * @param  $user  user whose groups should be listed
     * @return bool   false on error
     * @return array  array containing all groups on success
     *
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function _getGroups($user) {
      $groups = array();

      if($this->dbcon) {
        $sql = str_replace('%{user}',$this->_escape($user),$this->cnf['getGroups']);
        $result = $this->_queryDB($sql);

        if($result !== false && count($result)) {
          foreach($result as $row)
            $groups[] = $row['group'];
        }
        return $groups;
      }
      return false;
    }

    /**
     * Retrieves the user id of a given user name
     *
     * The database connection must already be established
     * for this function to work. Otherwise it will return
     * 'false'.
     *
     * @param  $user   user whose id is desired
     * @return user id
     *
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function _getUserID($user) {
      if($this->dbcon) {
        $sql = str_replace('%{user}',$this->_escape($user),$this->cnf['getUserID']);
        $result = $this->_queryDB($sql);
        return $result === false ? false : $result[0]['id'];
      }
      return false;
    }

    /**
     * Adds a new User to the database.
     *
     * The database connection must already be established
     * for this function to work. Otherwise it will return
     * 'false'.
     *
     * @param  $user  login of the user
     * @param  $pwd   encrypted password
     * @param  $name  full name of the user
     * @param  $mail  email address
     * @param  $grps  array of groups the user should become member of
     * @return bool
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @author  Chris Smith <chris@jalakai.co.uk>
     * @author  Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function _addUser($user,$pwd,$name,$mail,$grps){
      if($this->dbcon && is_array($grps)) {
        $sql = str_replace('%{user}', $this->_escape($user),$this->cnf['addUser']);
        $sql = str_replace('%{pass}', $this->_escape($pwd),$sql);
        $sql = str_replace('%{name}', $this->_escape($name),$sql);
        $sql = str_replace('%{email}',$this->_escape($mail),$sql);
        $uid = $this->_modifyDB($sql);

        if ($uid) {
          foreach($grps as $group) {
            $gid = $this->_addUserToGroup($user, $group, 1);
            if ($gid === false) break;
          }

          if ($gid) return true;
          else {
            /* remove the new user and all group relations if a group can't
             * be assigned. Newly created groups will remain in the database
             * and won't be removed. This might create orphaned groups but
             * is not a big issue so we ignore this problem here.
             */
            $this->_delUser($user);
            if ($this->cnf['debug'])
              msg ("MySQL err: Adding user '$user' to group '$group' failed.",-1,__LINE__,__FILE__);
          }
        }
      }
      return false;
    }

    /**
     * Deletes a given user and all his group references.
     *
     * The database connection must already be established
     * for this function to work. Otherwise it will return
     * 'false'.
     *
     * @param  $user   user whose id is desired
     * @return bool
     *
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function _delUser($user) {
      if($this->dbcon) {
        $uid = $this->_getUserID($user);
        if ($uid) {
          $sql = str_replace('%{uid}',$this->_escape($uid),$this->cnf['delUserRefs']);
          $this->_modifyDB($sql);
          $sql = str_replace('%{uid}',$this->_escape($uid),$this->cnf['delUser']);
          $sql = str_replace('%{user}',  $this->_escape($user),$sql);
          $this->_modifyDB($sql);
          return true;
        }
      }
      return false;
    }

    /**
     * getUserInfo
     *
     * Gets the data for a specific user The database connection
     * must already be established for this function to work.
     * Otherwise it will return 'false'.
     *
     * @param  $user  user's nick to get data for
     * @return bool   false on error
     * @return array  user info on success
     *
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function _getUserInfo($user){
      $sql = str_replace('%{user}',$this->_escape($user),$this->cnf['getUserInfo']);
      $result = $this->_queryDB($sql);
      if($result !== false && count($result)) {
        $info = $result[0];
        $info['grps'] = $this->_getGroups($user);
        return $info;
      }
      return false;
    }

    /**
     * Updates the user info in the database
     *
     * Update a user data structure in the database according changes
     * given in an array. The user name can only be changes if it didn't
     * exists already. If the new user name exists the update procedure
     * will be aborted. The database keeps unchanged.
     *
     * The database connection has already to be established for this
     * function to work. Otherwise it will return 'false'.
     *
     * The password will be crypted if necessary.
     *
     * @param  $changes  array of items to change as pairs of item and value
     * @param  $uid      user id of dataset to change, must be unique in DB
     * @return true on success or false on error
     *
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function _updateUserInfo($changes, $uid) {
      $sql  = $this->cnf['updateUser']." ";
      $cnt = 0;
      $err = 0;

      if($this->dbcon) {
        foreach ($changes as $item => $value) {
          if ($item == 'user') {
            if (($this->_getUserID($changes['user']))) {
              $err = 1; /* new username already exists */
              break;    /* abort update */
            }
            if ($cnt++ > 0) $sql .= ", ";
            $sql .= str_replace('%{user}',$value,$this->cnf['UpdateLogin']);
          } else if ($item == 'name') {
            if ($cnt++ > 0) $sql .= ", ";
            $sql .= str_replace('%{name}',$value,$this->cnf['UpdateName']);
          } else if ($item == 'pass') {
            if (!$this->cnf['forwardClearPass'])
              $value = auth_cryptPassword($value);
            if ($cnt++ > 0) $sql .= ", ";
            $sql .= str_replace('%{pass}',$value,$this->cnf['UpdatePass']);
          } else if ($item == 'mail') {
            if ($cnt++ > 0) $sql .= ", ";
            $sql .= str_replace('%{email}',$value,$this->cnf['UpdateEmail']);
          }
        }

        if ($err == 0) {
          if ($cnt > 0) {
            $sql .= " ".str_replace('%{uid}', $uid, $this->cnf['UpdateTarget']);
            if(get_class($this) == 'auth_mysql') $sql .= " LIMIT 1"; //some PgSQL inheritance comp.
            $this->_modifyDB($sql);
          }
          return true;
        }
      }
      return false;
    }

    /**
     * Retrieves the group id of a given group name
     *
     * The database connection must already be established
     * for this function to work. Otherwise it will return
     * 'false'.
     *
     * @param  $group   group name which id is desired
     * @return group id
     *
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function _getGroupID($group) {
      if($this->dbcon) {
        $sql = str_replace('%{group}',$this->_escape($group),$this->cnf['getGroupID']);
        $result = $this->_queryDB($sql);
        return $result === false ? false : $result[0]['id'];
      }
      return false;
    }

    /**
     * Opens a connection to a database and saves the handle for further
     * usage in the object. The successful call to this functions is
     * essential for most functions in this object.
     *
     * @return bool
     *
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function _openDB() {
      if (!$this->dbcon) {
        $con = @mysql_connect ($this->cnf['server'], $this->cnf['user'], $this->cnf['password']);
        if ($con) {
          if ((mysql_select_db($this->cnf['database'], $con))) {
            if ((preg_match("/^(\d+)\.(\d+)\.(\d+).*/", mysql_get_server_info ($con), $result)) == 1) {
              $this->dbver = $result[1];
              $this->dbrev = $result[2];
              $this->dbsub = $result[3];
            }
            $this->dbcon = $con;
            if(!empty($this->cnf['charset'])){
                 mysql_query('SET CHARACTER SET "' . $this->cnf['charset'] . '"', $con);
            }
            return true;   // connection and database successfully opened
          } else {
            mysql_close ($con);
            if ($this->cnf['debug'])
              msg("MySQL err: No access to database {$this->cnf['database']}.",-1,__LINE__,__FILE__);
          }
        } else if ($this->cnf['debug'])
          msg ("MySQL err: Connection to {$this->cnf['user']}@{$this->cnf['server']} not possible.",
               -1,__LINE__,__FILE__);

        return false;  // connection failed
      }
      return true;  // connection already open
    }

    /**
     * Closes a database connection.
     *
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function _closeDB() {
      if ($this->dbcon) {
        mysql_close ($this->dbcon);
        $this->dbcon = 0;
      }
    }

    /**
     * Sends a SQL query to the database and transforms the result into
     * an associative array.
     *
     * This function is only able to handle queries that returns a
     * table such as SELECT.
     *
     * @param $query  SQL string that contains the query
     * @return array with the result table
     *
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function _queryDB($query) {
      if($this->cnf['debug'] >= 2){
        msg('MySQL query: '.hsc($query),0,__LINE__,__FILE__);
      }

      $resultarray = array();
      if ($this->dbcon) {
        $result = @mysql_query($query,$this->dbcon);
        if ($result) {
          while (($t = mysql_fetch_assoc($result)) !== false)
            $resultarray[]=$t;
          mysql_free_result ($result);
          return $resultarray;
        }
        if ($this->cnf['debug'])
          msg('MySQL err: '.mysql_error($this->dbcon),-1,__LINE__,__FILE__);
      }
      return false;
    }

    /**
     * Sends a SQL query to the database
     *
     * This function is only able to handle queries that returns
     * either nothing or an id value such as INPUT, DELETE, UPDATE, etc.
     *
     * @param $query  SQL string that contains the query
     * @return insert id or 0, false on error
     *
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function _modifyDB($query) {
      if ($this->dbcon) {
        $result = @mysql_query($query,$this->dbcon);
        if ($result) {
          $rc = mysql_insert_id($this->dbcon); //give back ID on insert
          if ($rc !== false) return $rc;
        }
        if ($this->cnf['debug'])
          msg('MySQL err: '.mysql_error($this->dbcon),-1,__LINE__,__FILE__);
      }
      return false;
    }

    /**
     * Locked a list of tables for exclusive access so that modifications
     * to the database can't be disturbed by other threads. The list
     * could be set with $conf['auth']['mysql']['TablesToLock'] = array()
     *
     * If aliases for tables are used in SQL statements, also this aliases
     * must be locked. For eg. you use a table 'user' and the alias 'u' in
     * some sql queries, the array must looks like this (order is important):
     *   array("user", "user AS u");
     *
     * MySQL V3 is not able to handle transactions with COMMIT/ROLLBACK
     * so that this functionality is simulated by this function. Nevertheless
     * it is not as powerful as transactions, it is a good compromise in safty.
     *
     * @param $mode  could be 'READ' or 'WRITE'
     *
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function _lockTables($mode) {
      if ($this->dbcon) {
        if (is_array($this->cnf['TablesToLock']) && !empty($this->cnf['TablesToLock'])) {
          if ($mode == "READ" || $mode == "WRITE") {
            $sql = "LOCK TABLES ";
            $cnt = 0;
            foreach ($this->cnf['TablesToLock'] as $table) {
              if ($cnt++ != 0) $sql .= ", ";
              $sql .= "$table $mode";
            }
            $this->_modifyDB($sql);
            return true;
          }
        }
      }
      return false;
    }

    /**
     * Unlock locked tables. All existing locks of this thread will be
     * abrogated.
     *
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function _unlockTables() {
      if ($this->dbcon) {
        $this->_modifyDB("UNLOCK TABLES");
        return true;
      }
      return false;
    }

    /**
     * Transforms the filter settings in an filter string for a SQL database
     * The database connection must already be established, otherwise the
     * original SQL string without filter criteria will be returned.
     *
     * @param  $sql     SQL string to which the $filter criteria should be added
     * @param  $filter  array of filter criteria as pairs of item and pattern
     * @return SQL string with attached $filter criteria on success
     * @return the original SQL string on error.
     *
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function _createSQLFilter($sql, $filter) {
      $SQLfilter = "";
      $cnt = 0;

      if ($this->dbcon) {
        foreach ($filter as $item => $pattern) {
          $tmp = '%'.$this->_escape($pattern).'%';
          if ($item == 'user') {
            if ($cnt++ > 0) $SQLfilter .= " AND ";
            $SQLfilter .= str_replace('%{user}',$tmp,$this->cnf['FilterLogin']);
          } else if ($item == 'name') {
            if ($cnt++ > 0) $SQLfilter .= " AND ";
            $SQLfilter .= str_replace('%{name}',$tmp,$this->cnf['FilterName']);
          } else if ($item == 'mail') {
            if ($cnt++ > 0) $SQLfilter .= " AND ";
            $SQLfilter .= str_replace('%{email}',$tmp,$this->cnf['FilterEmail']);
          } else if ($item == 'grps') {
            if ($cnt++ > 0) $SQLfilter .= " AND ";
            $SQLfilter .= str_replace('%{group}',$tmp,$this->cnf['FilterGroup']);
          }
        }

        // we have to check SQLfilter here and must not use $cnt because if
        // any of cnf['Filter????'] is not defined, a malformed SQL string
        // would be generated.

        if (strlen($SQLfilter)) {
          $glue = strpos(strtolower($sql),"where") ? " AND " : " WHERE ";
          $sql = $sql.$glue.$SQLfilter;
        }
      }

      return $sql;
    }

    /**
     * Escape a string for insertion into the database
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @param  string  $string The string to escape
     * @param  boolean $like   Escape wildcard chars as well?
     */
    function _escape($string,$like=false){
      if($this->dbcon){
        $string = mysql_real_escape_string($string, $this->dbcon);
      }else{
        $string = addslashes($string);
      }
      if($like){
        $string = addcslashes($string,'%_');
      }
      return $string;
    }
}

//Setup VIM: ex: et ts=2 :
