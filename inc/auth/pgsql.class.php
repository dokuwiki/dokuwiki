<?php
/**
 * PgSQL authentication backend
 *
 * This class inherits much functionality from the MySQL class
 * and just reimplements the Postgres specific parts.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Chris Smith <chris@jalakai.co.uk>
 * @author     Matthias Grimm <matthias.grimmm@sourceforge.net>
*/

require_once(DOKU_INC.'inc/auth/mysql.class.php');

class auth_pgsql extends auth_mysql {

    /**
     * Constructor
     *
     * checks if the pgsql interface is available, otherwise it will
     * set the variable $success of the basis class to false
     *
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function auth_pgsql() {
      global $conf;
      $this->cnf          = $conf['auth']['pgsql'];
      if(!$this->cnf['port']) $this->cnf['port'] = 5432;

      if (method_exists($this, 'auth_basic'))
        parent::auth_basic();

      if(!function_exists('pg_connect')) {
        if ($this->cnf['debug'])
          msg("PgSQL err: PHP Postgres extension not found.",-1);
        $this->success = false;
        return;
      }

      $this->defaultgroup = $conf['defaultgroup'];

      // set capabilities based upon config strings set
      if (empty($this->cnf['user']) ||
          empty($this->cnf['password']) || empty($this->cnf['database'])){
        if ($this->cnf['debug'])
          msg("PgSQL err: insufficient configuration.",-1,__LINE__,__FILE__);
        $this->success = false;
        return;
      }

      $this->cando['addUser']      = $this->_chkcnf(array('getUserInfo',
                                                          'getGroups',
                                                          'addUser',
                                                          'getUserID',
                                                          'getGroupID',
                                                          'addGroup',
                                                          'addUserGroup'));
      $this->cando['delUser']      = $this->_chkcnf(array('getUserID',
                                                          'delUser',
                                                          'delUserRefs'));
      $this->cando['modLogin']     = $this->_chkcnf(array('getUserID',
                                                          'updateUser',
                                                          'UpdateTarget'));
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
                                                          'delUserGroup'));
      /* getGroups is not yet supported
      $this->cando['getGroups']    = $this->_chkcnf(array('getGroups',
                                                          'getGroupID')); */
      $this->cando['getUsers']     = $this->_chkcnf(array('getUsers',
                                                          'getUserInfo',
                                                          'getGroups'));
      $this->cando['getUserCount'] = $this->_chkcnf(array('getUsers'));
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
      return true;
    }

    // @inherit function checkPass($user,$pass)
    // @inherit function getUserData($user)
    // @inherit function createUser($user,$pwd,$name,$mail,$grps=null)
    // @inherit function modifyUser($user, $changes)
    // @inherit function deleteUsers($users)


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

        // no equivalent of SQL_CALC_FOUND_ROWS in pgsql?
        if (($result = $this->_queryDB($sql))){
          $rc = count($result);
        }
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
        $sql .= " ".$this->cnf['SortOrder']." LIMIT $limit OFFSET $first";
        $result = $this->_queryDB($sql);

        foreach ($result as $user)
          if (($info = $this->_getUserInfo($user['user'])))
            $out[$user['user']] = $info;

        $this->_unlockTables();
        $this->_closeDB();
      }
      return $out;
    }

    // @inherit function joinGroup($user, $group)
    // @inherit function leaveGroup($user, $group) {

    /**
     * Adds a user to a group.
     *
     * If $force is set to '1' non existing groups would be created.
     *
     * The database connection must already be established. Otherwise
     * this function does nothing and returns 'false'.
     *
     * @param   $user    user to add to a group
     * @param   $group   name of the group
     * @param   $force   '1' create missing groups
     * @return  bool     'true' on success, 'false' on error
     *
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     * @author Andreas Gohr   <andi@splitbrain.org>
     */
    function _addUserToGroup($user, $group, $force=0) {
      $newgroup = 0;

      if (($this->dbcon) && ($user)) {
        $gid = $this->_getGroupID($group);
        if (!$gid) {
          if ($force) {  // create missing groups
            $sql = str_replace('%{group}',addslashes($group),$this->cnf['addGroup']);
            $this->_modifyDB($sql);
            //group should now exists try again to fetch it
            $gid = $this->_getGroupID($group);
            $newgroup = 1;  // group newly created
          }
        }
        if (!$gid) return false; // group didn't exist and can't be created

        $sql = $this->cnf['addUserGroup'];
        if(strpos($sql,'%{uid}') !== false){
            $uid = $this->_getUserID($user);
            $sql = str_replace('%{uid}', addslashes($uid), $sql);
        }
        $sql = str_replace('%{user}', addslashes($user),$sql);
        $sql = str_replace('%{gid}',  addslashes($gid),$sql);
        $sql = str_replace('%{group}',addslashes($group),$sql);
        if ($this->_modifyDB($sql) !== false) return true;

        if ($newgroup) { // remove previously created group on error
          $sql = str_replace('%{gid}',  addslashes($gid),$this->cnf['delGroup']);
          $sql = str_replace('%{group}',addslashes($group),$sql);
          $this->_modifyDB($sql);
        }
      }
      return false;
    }

    // @inherit function _delUserFromGroup($user $group)
    // @inherit function _getGroups($user)
    // @inherit function _getUserID($user)

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
        $sql = str_replace('%{user}', addslashes($user),$this->cnf['addUser']);
        $sql = str_replace('%{pass}', addslashes($pwd),$sql);
        $sql = str_replace('%{name}', addslashes($name),$sql);
        $sql = str_replace('%{email}',addslashes($mail),$sql);
        if($this->_modifyDB($sql)){
          $uid = $this->_getUserID($user);
        }else{
          return false;
        }

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
              msg("PgSQL err: Adding user '$user' to group '$group' failed.",-1,__LINE__,__FILE__);
          }
        }
      }
      return false;
    }

    // @inherit function _delUser($user)
    // @inherit function _getUserInfo($user)
    // @inherit function _updateUserInfo($changes, $uid)
    // @inherit function _getGroupID($group)

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
        $dsn  = $this->cnf['server'] ? 'host='.$this->cnf['server'] : '';
        $dsn .= ' port='.$this->cnf['port'];
        $dsn .= ' dbname='.$this->cnf['database'];
        $dsn .= ' user='.$this->cnf['user'];
        $dsn .= ' password='.$this->cnf['password'];

        $con = @pg_connect($dsn);
        if ($con) {
            $this->dbcon = $con;
            return true;   // connection and database successfully opened
        } else if ($this->cnf['debug']){
            msg ("PgSQL err: Connection to {$this->cnf['user']}@{$this->cnf['server']} not possible.",
                  -1,__LINE__,__FILE__);
        }
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
        pg_close ($this->dbcon);
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
      if ($this->dbcon) {
        $result = @pg_query($this->dbcon,$query);
        if ($result) {
          while (($t = pg_fetch_assoc($result)) !== false)
            $resultarray[]=$t;
          pg_free_result ($result);
          return $resultarray;
        }elseif ($this->cnf['debug'])
          msg('PgSQL err: '.pg_last_error($this->dbcon),-1,__LINE__,__FILE__);
      }
      return false;
    }

    /**
     * Executes an update or insert query. This differs from the
     * MySQL one because it does NOT return the last insertID
     *
     * @author Andreas Gohr
     */
    function _modifyDB($query) {
      if ($this->dbcon) {
        $result = @pg_query($this->dbcon,$query);
        if ($result) {
          pg_free_result ($result);
          return true;
        }
        if ($this->cnf['debug']){
          msg('PgSQL err: '.pg_last_error($this->dbcon),-1,__LINE__,__FILE__);
        }
      }
      return false;
    }

    /**
     * Start a transaction
     *
     * @param $mode  could be 'READ' or 'WRITE'
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function _lockTables($mode) {
      if ($this->dbcon) {
        $this->_modifyDB('BEGIN');
        return true;
      }
      return false;
    }

    /**
     * Commit a transaction
     *
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function _unlockTables() {
      if ($this->dbcon) {
        $this->_modifyDB('COMMIT');
        return true;
      }
      return false;
    }

    // @inherit function _createSQLFilter($sql, $filter)


    /**
     * Escape a string for insertion into the database
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @param  string  $string The string to escape
     * @param  boolean $like   Escape wildcard chars as well?
     */
    function _escape($string,$like=false){
      $string = pg_escape_string($string);
      if($like){
        $string = addcslashes($string,'%_');
      }
      return $string;
    }

}

//Setup VIM: ex: et ts=2 :
