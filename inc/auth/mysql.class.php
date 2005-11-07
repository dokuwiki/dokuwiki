<?php
/**
 * MySQLP authentication backend
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Chris Smith <chris@jalakai.co.uk>
 * @author     Matthias Grimm <matthias.grimmm@sourceforge.net>
*/
 
define('DOKU_AUTH', dirname(__FILE__)); 
require_once(DOKU_AUTH.'/basic.class.php');

class auth_mysql extends auth_basic {
   
    var $dbcon        = 0;
    var $cnf          = null;
    var $defaultgroup = "";
    
    /**
     * Constructor
     *
     * checks if the mysql interface is available, otherwise it will
     * set the variable $success of the basis class to FALSE
     *
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function auth_mysql() {
      global $conf;
      global $lang;
      
      if (method_exists($this, 'auth_basic'))
        parent::auth_basic();
        
      if(!function_exists('mysql_connect')) {
        msg($lang['noMySQL'],-1);
        $this->success = false;
      }
      
      $this->cnf          = $conf['auth']['mysql'];
      $this->defaultgroup = $conf['defaultgroup'];
    }
    
    /**
     * [public function]
     *
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
      
      if($this->openDB()) {
        $sql    = str_replace('%u',addslashes($user),$this->cnf['checkPass']);
        $sql    = str_replace('%p',addslashes($pass),$sql);
        $sql    = str_replace('%g',addslashes($this->defaultgroup),$sql);
        $result = $this->queryDB($sql);
      
        if($result !== false && count($result) == 1)
          $rc = $cnf['encryptPass'] ? true : auth_verifyPassword($pass,$result[0]['pass']);
		  
        $this->closeDB();
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
      if($this->openDB()) {
        $this->lockTables("READ");
        $info = $this->getUserInfo($user);
        $this->unlockTables();
        $this->closeDB();
      } else
        $info = false;
      return $info;
    }

    /**
     * [public function]
     *
     * Create a new User. Returns false if the user already exists,
     * null when an error occured and the cleartext password of the
     * new user if everything went well.
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
      if($this->openDB()) {
        if (($info = $this->getUserInfo($user)) !== false)
          return false;  // user already exists

        // set defaultgroup if no groups were given
        if ($grps == null)
          $grps = array($this->defaultgroup);
 
        $this->lockTables("WRITE");
        $rc = $this->addUser($user,$pwd,$name,$mail,$grps);
        $this->unlockTables();
        $this->closeDB();
        if ($rc) return $pwd;
      }
      return null;  // return error
    }
   
    /**
     * [public function]
     *
     * Modify user data
     *
     * @param   $user      nick of the user to be changed
     * @param   $changes   array of field/value pairs to be changed (password will be clear text)
     * @return  bool
     *
     * @todo  Modifications are done through deleting and recreating the user.
     *        This might be suboptimal and dangerous. Using UPDATE seems the
     *        better way.
     *
     * @author  Chris Smith <chris@jalakai.co.uk>
     * @author  Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function modifyUser($user, $changes) {
      $rc = false;
      
      if (!is_array($changes) || !count($changes))
        return true;  // nothing to change
        
      if($this->openDB()) {
        $this->lockTables("WRITE");
        if (($info = $this->getUserInfo($user)) !== false) {
          foreach ($changes as $field => $value)
            $info[$field] = $value;  // update user record

          $rc = $this->delUser($user);   // remove user from database
          if ($rc)
            $rc = $this->addUser($user,$info['pass'],$info['name'],$info['mail'],$info['grps']);
          if (!$rc)
            msg($lang['modUserFailed'], -1);
        }  
        $this->unlockTables();
        $this->closeDB();
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
	  
      if($this->openDB()) {
        if (is_array($users) && !empty($users)) {
          $this->lockTables("WRITE");
          foreach ($users as $user) {
            if ($this->delUser($user))
              $count++;
          }
          $this->unlockTables();
        }
        $this->closeDB();
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
      
      if($this->openDB()) {
        $sql = $this->createSQLFilter($this->cnf['getUsers'], $filter);
        $result = $this->queryDB($sql);
        if ($result)
            $rc = count($result);
        $this->closeDB();
      }
      return $rc;
    }
    
    /**
     * [public function]
     *
     * Bulk retrieval of user data.
     *
     * @param   start     index of first user to be returned
     * @param   limit     max number of users to be returned
     * @param   filter    array of field/pattern pairs
     * @return  array of userinfo (refer getUserData for internal userinfo details)
     *
     * @author  Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function retrieveUsers($start=0,$limit=0,$filter=array()) {
      $out   = array();
      $i     = 0;
      $count = 0;
      
      if($this->openDB()) {
        $this->lockTables("READ");
        $sql = $this->createSQLFilter($this->cnf['getUsers'], $filter)." ".$this->cnf['SortOrder'];
        $result = $this->queryDB($sql);
        if ($result) {
          foreach ($result as $user) {
            if ($i++ >= $start) {
              $info = $this->getUserInfo($user['user']);
              if ($info) {
                $out[$user['user']] = $info;
                if (($limit > 0) && (++$count >= $limit)) break;
              }
            }
          }
        }
        $this->unlockTables();
        $this->closeDB();
      }
      return $out;
    }

    /**
     * [public function]
     *
     * Give user membership of a group
     * 
     * @param   $user
     * @param   $group  
     * @return  bool
     *
     * @author  Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function joinGroup($user, $group) {
      $rc = false;
      
      if($this->openDB()) {
        $this->lockTables("WRITE");
        $rc = addUserToGroup($user, $group);
        $this->unlochTables();
        $this->closeDB();
      }
      return $rc;
    }

    /**
     * [public function]
     *
     * Remove user from a group
     *
     * @param   $user    user that leaves a group
     * @param   $group   group to leave
     * @return  bool
     *
     * @author  Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function leaveGroup($user, $group) {
      $rc = false;
      
      if($this->openDB()) {
        $this->lockTables("WRITE");
        
        $uid = $this->getUserID($user);
        if ($uid) {
          $gid = $this->getGroupID($group);
          if ($gid) {
            $sql = str_replace('%uid',addslashes($uid),$this->cnf['delUserGroup']);
            $sql = str_replace('%u'  ,addslashes($user),$sql);
            $sql = str_replace('%gid',addslashes($gid),$sql);
            $sql = str_replace('%g'  ,addslashes($group),$sql);
            $rc  = $this->modifyDB($sql) == 0 ? true : false;
          }
        }
        $this->unlochTables();
        $this->closeDB();
      }
      return $rc;
    }
 
    /**
     * Adds a user to a group. If $force is set to '1' the group will be
     * created if it not already existed.
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
    function addUserToGroup($user, $group, $force=0) {
      $newgroup = 0;
        
      if($this->dbcon) {
        $uid = $this->getUserID($user);
        if ($uid) {
          $gid = $this->getGroupID($group);
          if (!$gid) {
            if ($force) {  // create missing groups
              $sql = str_replace('%g',addslashes($group),$this->cnf['addGroup']);
              $gid = $this->modifyDB($sql);
              $newgroup = 1;  // group newly created
            }
            if (!$gid) return false; // group didm't exist and can't be created
          }
        
          $sql = str_replace('%uid',addslashes($uid),$this->cnf['addUserGroup']);
          $sql = str_replace('%u'  ,addslashes($user),$sql);
          $sql = str_replace('%gid',addslashes($gid),$sql);
          $sql = str_replace('%g'  ,addslashes($group),$sql);
          if ($this->modifyDB($sql) !== false) return true;

          if ($newgroup) { // remove previously created group on error
            $sql = str_replace('%gid',addslashes($gid),$this->cnf['delGroup']);
            $sql = str_replace('%g'  ,addslashes($group),$sql);
            $this->modifyDB($sql);
          }
        }
      }
      return false;
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
    function getGroups($user) {
      $groups = array();
      
      if($this->dbcon) {
        $sql = str_replace('%u',addslashes($user),$this->cnf['getGroups']);
        $result = $this->queryDB($sql);
        
        if(count($result)) {
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
    function getUserID($user) {
      if($this->dbcon) {
        $sql = str_replace('%u',addslashes($user),$this->cnf['getUserID']);
        $result = $this->queryDB($sql);
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
     * @param  $user  nick of the user
     * @param  $pwd   clear text password
     * @param  $name  full name of the user
     * @param  $mail  email address
     * @param  $grps  array of groups the user should become member of
     * @return bool
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @author  Chris Smith <chris@jalakai.co.uk>
     * @author  Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    function addUser($user,$pwd,$name,$mail,$grps){
      if($this->dbcon && is_array($grps)) {
        $_pwd = $this->cnf['encryptPass'] ? $pwd : auth_cryptPassword($pwd);
        $sql = str_replace('%u'  ,addslashes($user),$this->cnf['addUser']);
        $sql = str_replace('%p'  ,addslashes($_pwd),$sql);
        $sql = str_replace('%n'  ,addslashes($name),$sql);
        $sql = str_replace('%e'  ,addslashes($mail),$sql);  
        $uid = $this->modifyDB($sql);
      
        if ($uid) {
          foreach($grps as $group) {
            $gid = $this->addUserToGroup($user, $group, 1);
            if ($gid === false) break;
          }
          
          if ($gid) return true;
          else {
            /* remove the new user and all group relations if a group can't
             * be assigned. Newly created groups will remain in the database
             * and won't be removed. This might create orphaned groups but
             * is not a big issue so we ignore this problem here.
             */
            $this->delUser($user);
            $text = str_replace('%u' ,addslashes($user),$this->cnf['joinGroupFailed']);
            $text = str_replace('%g' ,addslashes($group),$text);
            msg($text, -1);
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
    function delUser($user) {
      if($this->dbcon) {
        $uid = $this->getUserID($user);
        if ($uid) {
          $sql = str_replace('%uid',addslashes($uid),$this->cnf['delUser']);
          $sql = str_replace('%u',  addslashes($user),$sql);
          $this->modifyDB($sql);
          $sql = str_replace('%uid',addslashes($uid),$this->cnf['delUserRefs']);
          $this->modifyDB($sql);
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
    function getUserInfo($user){
      $sql = str_replace('%u',addslashes($user),$this->cnf['getUserInfo']);
      $result = $this->queryDB($sql);
      if(count($result)) {
        $info = $result[0];
        $info['grps'] = $this->getGroups($user);
        return $info;
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
    function getGroupID($group) {
      if($this->dbcon) {
        $sql = str_replace('%g',addslashes($group),$this->cnf['getGroupID']);
        $result = $this->queryDB($sql);
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
    function openDB() {
      global $lang;
      
      if ($this->dbcon == 0) {
        $con = @mysql_connect ($this->cnf['server'], $this->cnf['user'], $this->cnf['password']);   

        if ($con) {
          if ((mysql_select_db($this->cnf['database'], $con))) {
            $this->dbcon = $con; 
            return true;   // connection and database sucessfully opened
          } else {
            $text = str_replace('%d',addslashes($this->cnf['database']),$lang['noDatabase']);
            msg($text, -1);
            mysql_close ($con);
          }   
        } else {
          $text = str_replace('%u',addslashes($this->cnf['user']),$lang['noConnect']);
          $text = str_replace('%s',addslashes($this->cnf['server']),$text);
          msg($text, -1);
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
    function closeDB() {
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
    function queryDB($query) {
      if ($this->dbcon) {
        $result = @mysql_query($query,$this->dbcon);
        if ($result) {
          while (($t = mysql_fetch_assoc($result)) !== false)
            $resultarray[]=$t;
          mysql_free_result ($result);
          return $resultarray;
        }
        msg('MySQL: '.mysql_error($this->dbcon), -1);
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
    function modifyDB($query) {
      if ($this->dbcon) {
        $result = @mysql_query($query,$this->dbcon);
        if ($result) {
          $rc = mysql_insert_id($this->dbcon); //give back ID on insert
          if ($rc !== false) return $rc;
        }
        msg('MySQL: '.mysql_error($this->dbcon), -1);
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
    function lockTables($mode) {
      if ($this->dbcon) {
        if (is_array($this->cnf['TablesToLock']) && !empty($this->cnf['TablesToLock'])) {
          if ($mode == "READ" || $mode == "WRITE") {
            $sql = "LOCK TABLES ";
            $cnt = 0;
            foreach ($this->cnf['TablesToLock'] as $table) {
              if ($cnt++ != 0) $sql .= ", ";
              $sql .= "$table $mode";
            }
            $this->modifyDB($sql);
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
    function unlockTables() {
      if ($this->dbcon) {
        $this->modifyDB("UNLOCK TABLES");
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
    function createSQLFilter($sql, $filter) {
      $SQLfilter = "";
      $cnt = 0;
        
      if ($this->dbcon) {
        foreach ($filter as $item => $pattern) {
          $tmp = addslashes('%'.mysql_real_escape_string($pattern, $this->dbcon).'%');
          if ($item == 'user') {
            if ($cnt++ > 0) $SQLfilter .= " AND ";
            $SQLfilter .= str_replace('%u',$tmp,$this->cnf['FilterLogin']);
          } else if ($item == 'name') {
            if ($cnt++ > 0) $SQLfilter .= " AND ";
            $SQLfilter .= str_replace('%n',$tmp,$this->cnf['FilterName']);
          } else if ($item == 'mail') {
            if ($cnt++ > 0) $SQLfilter .= " AND ";
            $SQLfilter .= str_replace('%e',$tmp,$this->cnf['FilterEmail']);
          } else if ($item == 'grps') {
            if ($cnt++ > 0) $SQLfilter .= " AND ";
            $SQLfilter .= str_replace('%g',$tmp,$this->cnf['FilterGroup']);
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
    
}

//Setup VIM: ex: et ts=2 enc=utf-8 :

