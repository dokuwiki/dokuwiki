<?php
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * PostgreSQL authentication backend
 *
 * This class inherits much functionality from the MySQL class
 * and just reimplements the Postgres specific parts.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Chris Smith <chris@jalakai.co.uk>
 * @author     Matthias Grimm <matthias.grimmm@sourceforge.net>
 * @author     Jan Schumann <js@schumann-it.com>
 */
class auth_plugin_authpgsql extends auth_plugin_authmysql {

    /**
     * Constructor
     *
     * checks if the pgsql interface is available, otherwise it will
     * set the variable $success of the basis class to false
     *
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    public function __construct() {
        // we don't want the stuff the MySQL constructor does, but the grandparent might do something
        DokuWiki_Auth_Plugin::__construct();

        if(!function_exists('pg_connect')) {
            $this->_debug("PgSQL err: PHP Postgres extension not found.", -1, __LINE__, __FILE__);
            $this->success = false;
            return;
        }

        $this->loadConfig();

        // set capabilities based upon config strings set
        if(empty($this->conf['user']) ||
            empty($this->conf['password']) || empty($this->conf['database'])
        ) {
            $this->_debug("PgSQL err: insufficient configuration.", -1, __LINE__, __FILE__);
            $this->success = false;
            return;
        }

        $this->cando['addUser']   = $this->_chkcnf(
            array(
                 'getUserInfo',
                 'getGroups',
                 'addUser',
                 'getUserID',
                 'getGroupID',
                 'addGroup',
                 'addUserGroup'
            )
        );
        $this->cando['delUser']   = $this->_chkcnf(
            array(
                 'getUserID',
                 'delUser',
                 'delUserRefs'
            )
        );
        $this->cando['modLogin']  = $this->_chkcnf(
            array(
                 'getUserID',
                 'updateUser',
                 'UpdateTarget'
            )
        );
        $this->cando['modPass']   = $this->cando['modLogin'];
        $this->cando['modName']   = $this->cando['modLogin'];
        $this->cando['modMail']   = $this->cando['modLogin'];
        $this->cando['modGroups'] = $this->_chkcnf(
            array(
                 'getUserID',
                 'getGroups',
                 'getGroupID',
                 'addGroup',
                 'addUserGroup',
                 'delGroup',
                 'getGroupID',
                 'delUserGroup'
            )
        );
        /* getGroups is not yet supported
           $this->cando['getGroups']    = $this->_chkcnf(array('getGroups',
           'getGroupID')); */
        $this->cando['getUsers']     = $this->_chkcnf(
            array(
                 'getUsers',
                 'getUserInfo',
                 'getGroups'
            )
        );
        $this->cando['getUserCount'] = $this->_chkcnf(array('getUsers'));
    }

    /**
     * Check if the given config strings are set
     *
     * @author  Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     *
     * @param   array $keys
     * @param   bool  $wop
     * @return  bool
     */
    protected function _chkcnf($keys, $wop = false) {
        foreach($keys as $key) {
            if(empty($this->conf[$key])) return false;
        }
        return true;
    }

    /**
     * Counts users which meet certain $filter criteria.
     *
     * @author  Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     *
     * @param  array  $filter  filter criteria in item/pattern pairs
     * @return int count of found users.
     */
    public function getUserCount($filter = array()) {
        $rc = 0;

        if($this->_openDB()) {
            $sql = $this->_createSQLFilter($this->conf['getUsers'], $filter);

            // no equivalent of SQL_CALC_FOUND_ROWS in pgsql?
            if(($result = $this->_queryDB($sql))) {
                $rc = count($result);
            }
            $this->_closeDB();
        }
        return $rc;
    }

    /**
     * Bulk retrieval of user data
     *
     * @author  Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     *
     * @param   int   $first     index of first user to be returned
     * @param   int   $limit     max number of users to be returned
     * @param   array $filter    array of field/pattern pairs
     * @return  array userinfo (refer getUserData for internal userinfo details)
     */
    public function retrieveUsers($first = 0, $limit = 0, $filter = array()) {
        $out = array();

        if($this->_openDB()) {
            $this->_lockTables("READ");
            $sql = $this->_createSQLFilter($this->conf['getUsers'], $filter);
            $sql .= " ".$this->conf['SortOrder'];
            if($limit) $sql .= " LIMIT $limit";
            if($first) $sql .= " OFFSET $first";
            $result = $this->_queryDB($sql);

            foreach($result as $user) {
                if(($info = $this->_getUserInfo($user['user']))) {
                    $out[$user['user']] = $info;
                }
            }

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
     * If $force is set to true non existing groups would be created.
     *
     * The database connection must already be established. Otherwise
     * this function does nothing and returns 'false'.
     *
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     * @author Andreas Gohr   <andi@splitbrain.org>
     *
     * @param   string $user    user to add to a group
     * @param   string $group   name of the group
     * @param   bool   $force   create missing groups
     * @return  bool   true on success, false on error
     */
    protected function _addUserToGroup($user, $group, $force = false) {
        $newgroup = 0;

        if(($this->dbcon) && ($user)) {
            $gid = $this->_getGroupID($group);
            if(!$gid) {
                if($force) { // create missing groups
                    $sql = str_replace('%{group}', addslashes($group), $this->conf['addGroup']);
                    $this->_modifyDB($sql);
                    //group should now exists try again to fetch it
                    $gid      = $this->_getGroupID($group);
                    $newgroup = 1; // group newly created
                }
            }
            if(!$gid) return false; // group didn't exist and can't be created

            $sql = $this->conf['addUserGroup'];
            if(strpos($sql, '%{uid}') !== false) {
                $uid = $this->_getUserID($user);
                $sql = str_replace('%{uid}', addslashes($uid), $sql);
            }
            $sql = str_replace('%{user}', addslashes($user), $sql);
            $sql = str_replace('%{gid}', addslashes($gid), $sql);
            $sql = str_replace('%{group}', addslashes($group), $sql);
            if($this->_modifyDB($sql) !== false) {
                $this->_flushUserInfoCache($user);
                return true;
            }

            if($newgroup) { // remove previously created group on error
                $sql = str_replace('%{gid}', addslashes($gid), $this->conf['delGroup']);
                $sql = str_replace('%{group}', addslashes($group), $sql);
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
     * @param  string $user  login of the user
     * @param  string $pwd   encrypted password
     * @param  string $name  full name of the user
     * @param  string $mail  email address
     * @param  array  $grps  array of groups the user should become member of
     * @return bool
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @author  Chris Smith <chris@jalakai.co.uk>
     * @author  Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    protected function _addUser($user, $pwd, $name, $mail, $grps) {
        if($this->dbcon && is_array($grps)) {
            $sql = str_replace('%{user}', addslashes($user), $this->conf['addUser']);
            $sql = str_replace('%{pass}', addslashes($pwd), $sql);
            $sql = str_replace('%{name}', addslashes($name), $sql);
            $sql = str_replace('%{email}', addslashes($mail), $sql);
            if($this->_modifyDB($sql)) {
                $uid = $this->_getUserID($user);
            } else {
                return false;
            }

            $group = '';
            $gid = false;

            if($uid) {
                foreach($grps as $group) {
                    $gid = $this->_addUserToGroup($user, $group, 1);
                    if($gid === false) break;
                }

                if($gid !== false){
                    $this->_flushUserInfoCache($user);
                    return true;
                } else {
                    /* remove the new user and all group relations if a group can't
                     * be assigned. Newly created groups will remain in the database
                     * and won't be removed. This might create orphaned groups but
                     * is not a big issue so we ignore this problem here.
                     */
                    $this->_delUser($user);
                    $this->_debug("PgSQL err: Adding user '$user' to group '$group' failed.", -1, __LINE__, __FILE__);
                }
            }
        }
        return false;
    }

    /**
     * Opens a connection to a database and saves the handle for further
     * usage in the object. The successful call to this functions is
     * essential for most functions in this object.
     *
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     *
     * @return bool
     */
    protected function _openDB() {
        if(!$this->dbcon) {
            $dsn = $this->conf['server'] ? 'host='.$this->conf['server'] : '';
            $dsn .= ' port='.$this->conf['port'];
            $dsn .= ' dbname='.$this->conf['database'];
            $dsn .= ' user='.$this->conf['user'];
            $dsn .= ' password='.$this->conf['password'];

            $con = @pg_connect($dsn);
            if($con) {
                $this->dbcon = $con;
                return true; // connection and database successfully opened
            } else {
                $this->_debug(
                        "PgSQL err: Connection to {$this->conf['user']}@{$this->conf['server']} not possible.",
                        -1, __LINE__, __FILE__
                    );
            }
            return false; // connection failed
        }
        return true; // connection already open
    }

    /**
     * Closes a database connection.
     *
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    protected function _closeDB() {
        if($this->dbcon) {
            pg_close($this->dbcon);
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
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     *
     * @param  string $query  SQL string that contains the query
     * @return array the result table
     */
    protected function _queryDB($query) {
        $resultarray = array();
        if($this->dbcon) {
            $result = @pg_query($this->dbcon, $query);
            if($result) {
                while(($t = pg_fetch_assoc($result)) !== false)
                    $resultarray[] = $t;
                pg_free_result($result);
                return $resultarray;
            } else{
                $this->_debug('PgSQL err: '.pg_last_error($this->dbcon), -1, __LINE__, __FILE__);
            }
        }
        return false;
    }

    /**
     * Executes an update or insert query. This differs from the
     * MySQL one because it does NOT return the last insertID
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    protected function _modifyDB($query) {
        if($this->dbcon) {
            $result = @pg_query($this->dbcon, $query);
            if($result) {
                pg_free_result($result);
                return true;
            }
            $this->_debug('PgSQL err: '.pg_last_error($this->dbcon), -1, __LINE__, __FILE__);
        }
        return false;
    }

    /**
     * Start a transaction
     *
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     *
     * @param string $mode  could be 'READ' or 'WRITE'
     * @return bool
     */
    protected function _lockTables($mode) {
        if($this->dbcon) {
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
    protected function _unlockTables() {
        if($this->dbcon) {
            $this->_modifyDB('COMMIT');
            return true;
        }
        return false;
    }

    /**
     * Escape a string for insertion into the database
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     * @param  string  $string The string to escape
     * @param  bool    $like   Escape wildcard chars as well?
     * @return string
     */
    protected function _escape($string, $like = false) {
        $string = pg_escape_string($string);
        if($like) {
            $string = addcslashes($string, '%_');
        }
        return $string;
    }
}