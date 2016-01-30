<?php
/**
 * DokuWiki Plugin authpdo (Auth Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <andi@splitbrain.org>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class auth_plugin_authpdo extends DokuWiki_Auth_Plugin {

    /** @var PDO */
    protected $pdo;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct(); // for compatibility

        if(!class_exists('PDO')) {
            $this->_debug('PDO extension for PHP not found.', -1, __LINE__);
            $this->success = false;
            return;
        }

        if(!$this->getConf('dsn')) {
            $this->_debug('No DSN specified', -1, __LINE__);
            $this->success = false;
            return;
        }

        try {
            $this->pdo = new PDO(
                $this->getConf('dsn'),
                $this->getConf('user'),
                $this->getConf('pass'),
                array(
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // always fetch as array
                    PDO::ATTR_EMULATE_PREPARES => true, // emulating prepares allows us to reuse param names
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // we want exceptions, not error codes
                )
            );
        } catch(PDOException $e) {
            $this->_debug($e);
            $this->success = false;
            return;
        }

        // FIXME set capabilities accordingly
        //$this->cando['addUser']     = false; // can Users be created?
        //$this->cando['delUser']     = false; // can Users be deleted?
        //$this->cando['modLogin']    = false; // can login names be changed?
        //$this->cando['modPass']     = false; // can passwords be changed?
        //$this->cando['modName']     = false; // can real names be changed?
        //$this->cando['modMail']     = false; // can emails be changed?
        //$this->cando['modGroups']   = false; // can groups be changed?
        //$this->cando['getUsers']    = false; // can a (filtered) list of users be retrieved?
        //$this->cando['getUserCount']= false; // can the number of users be retrieved?
        //$this->cando['getGroups']   = false; // can a list of available groups be retrieved?
        //$this->cando['external']    = false; // does the module do external auth checking?
        //$this->cando['logout']      = true; // can the user logout again? (eg. not possible with HTTP auth)

        // FIXME intialize your auth system and set success to true, if successful
        $this->success = true;
    }

    /**
     * Check user+password
     *
     * May be ommited if trustExternal is used.
     *
     * @param   string $user the user name
     * @param   string $pass the clear text password
     * @return  bool
     */
    public function checkPass($user, $pass) {

        $data = $this->_selectUser($user);
        if($data == false) return false;

        if(isset($data['hash'])) {
            // hashed password
            $passhash = new PassHash();
            return $passhash->verify_hash($pass, $data['hash']);
        } else {
            // clear text password in the database O_o
            return ($pass == $data['clear']);
        }
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
     * @param   string $user the user name
     * @param   bool $requireGroups whether or not the returned data must include groups
     * @return array containing user data or false
     */
    public function getUserData($user, $requireGroups = true) {
        $data = $this->_selectUser($user);
        if($data == false) return false;

        if(isset($data['hash'])) unset($data['hash']);
        if(isset($data['clean'])) unset($data['clean']);

        if($requireGroups) {
            $data['grps'] = $this->_selectUserGroups($data);
            if($data['grps'] === false) return false;
        }

        return $data;
    }

    /**
     * Create a new User [implement only where required/possible]
     *
     * Returns false if the user already exists, null when an error
     * occurred and true if everything went well.
     *
     * The new user HAS TO be added to the default group by this
     * function!
     *
     * Set addUser capability when implemented
     *
     * @param  string $user
     * @param  string $clear
     * @param  string $name
     * @param  string $mail
     * @param  null|array $grps
     * @return bool|null
     */
    public function createUser($user, $clear, $name, $mail, $grps = null) {
        global $conf;

        if(($info = $this->getUserData($user, false)) !== false) {
            msg($this->getLang('userexists'), -1);
            return false; // user already exists
        }

        // prepare data
        if($grps == null) $grps = array();
        $grps[] = $conf['defaultgroup'];
        $grps = array_unique($grps);
        $hash = auth_cryptPassword($clear);
        $userdata = compact('user', 'clear', 'hash', 'name', 'mail');

        // action protected by transaction
        $this->pdo->beginTransaction();
        {
            // insert the user
            $ok = $this->_query($this->getConf('insert-user'), $userdata);
            if($ok === false) goto FAIL;
            $userdata = $this->getUserData($user, false);
            if($userdata === false) goto FAIL;

            // create all groups that do not exist, the refetch the groups
            $allgroups = $this->_selectGroups();
            foreach($grps as $group) {
                if(!isset($allgroups[$group])) {
                    $ok = $this->_insertGroup($group);
                    if($ok === false) goto FAIL;
                }
            }
            $allgroups = $this->_selectGroups();

            // add user to the groups
            foreach($grps as $group) {
                $ok = $this->_joinGroup($userdata, $allgroups[$group]);
                if($ok === false) goto FAIL;
            }
        }
        $this->pdo->commit();
        return true;

        // something went wrong, rollback
        FAIL:
        $this->pdo->rollBack();
        $this->_debug('Transaction rolled back', 0, __LINE__);
        return null; // return error
    }

    /**
     * Modify user data [implement only where required/possible]
     *
     * Set the mod* capabilities according to the implemented features
     *
     * @param   string $user nick of the user to be changed
     * @param   array $changes array of field/value pairs to be changed (password will be clear text)
     * @return  bool
     */
    //public function modifyUser($user, $changes) {
    // FIXME implement
    //    return false;
    //}

    /**
     * Delete one or more users [implement only where required/possible]
     *
     * Set delUser capability when implemented
     *
     * @param   array $users
     * @return  int    number of users deleted
     */
    //public function deleteUsers($users) {
    // FIXME implement
    //    return false;
    //}

    /**
     * Bulk retrieval of user data [implement only where required/possible]
     *
     * Set getUsers capability when implemented
     *
     * @param   int $start index of first user to be returned
     * @param   int $limit max number of users to be returned
     * @param   array $filter array of field/pattern pairs, null for no filter
     * @return  array list of userinfo (refer getUserData for internal userinfo details)
     */
    //public function retrieveUsers($start = 0, $limit = -1, $filter = null) {
    // FIXME implement
    //    return array();
    //}

    /**
     * Return a count of the number of user which meet $filter criteria
     * [should be implemented whenever retrieveUsers is implemented]
     *
     * Set getUserCount capability when implemented
     *
     * @param  array $filter array of field/pattern pairs, empty array for no filter
     * @return int
     */
    //public function getUserCount($filter = array()) {
    // FIXME implement
    //    return 0;
    //}

    /**
     * Define a group [implement only where required/possible]
     *
     * Set addGroup capability when implemented
     *
     * @param   string $group
     * @return  bool
     */
    //public function addGroup($group) {
    // FIXME implement
    //    return false;
    //}

    /**
     * Retrieve groups
     *
     * Set getGroups capability when implemented
     *
     * @param   int $start
     * @param   int $limit
     * @return  array
     */
    public function retrieveGroups($start = 0, $limit = 0) {
        $groups = array_keys($this->_selectGroups());
        if($groups === false) return array();

        if(!$limit) {
            return array_splice($groups, $start);
        } else {
            return array_splice($groups, $start, $limit);
        }
    }

    /**
     * Select data of a specified user
     *
     * @param string $user the user name
     * @return bool|array user data, false on error
     */
    protected function _selectUser($user) {
        $sql = $this->getConf('select-user');

        $result = $this->_query($sql, array(':user' => $user));
        if(!$result) return false;

        if(count($result) > 1) {
            $this->_debug('Found more than one matching user', -1, __LINE__);
            return false;
        }

        $data = array_shift($result);
        $dataok = true;

        if(!isset($data['user'])) {
            $this->_debug("Statement did not return 'user' attribute", -1, __LINE__);
            $dataok = false;
        }
        if(!isset($data['hash']) && !isset($data['clear'])) {
            $this->_debug("Statement did not return 'clear' or 'hash' attribute", -1, __LINE__);
            $dataok = false;
        }
        if(!isset($data['name'])) {
            $this->_debug("Statement did not return 'name' attribute", -1, __LINE__);
            $dataok = false;
        }
        if(!isset($data['mail'])) {
            $this->_debug("Statement did not return 'mail' attribute", -1, __LINE__);
            $dataok = false;
        }

        if(!$dataok) return false;
        return $data;
    }

    /**
     * Select all groups of a user
     *
     * @param array $userdata The userdata as returned by _selectUser()
     * @return array|bool list of group names, false on error
     */
    protected function _selectUserGroups($userdata) {
        global $conf;
        $sql = $this->getConf('select-user-groups');
        $result = $this->_query($sql, $userdata);
        if($result === false) return false;

        $groups = array($conf['defaultgroup']); // always add default config
        foreach($result as $row) {
            if(!isset($row['group'])) {
                $this->_debug("No 'group' field returned in select-user-groups statement");
                return false;
            }
            $groups[] = $row['group'];
        }

        $groups = array_unique($groups);
        sort($groups);
        return $groups;
    }

    /**
     * Select all available groups
     *
     * @todo this should be cached
     * @return array|bool list of all available groups and their properties
     */
    protected function _selectGroups() {
        $sql = $this->getConf('select-groups');
        $result = $this->_query($sql);
        if($result === false) return false;

        $groups = array();
        foreach($result as $row) {
            if(!isset($row['group'])) {
                $this->_debug("No 'group' field returned from select-groups statement", -1, __LINE__);
                return false;
            }

            // relayout result with group name as key
            $group = $row['group'];
            $groups[$group] = $row;
        }

        ksort($groups);
        return $groups;
    }

    /**
     * Create a new group with the given name
     *
     * @param string $group
     * @return bool
     */
    protected function _insertGroup($group) {
        $sql = $this->getConf('insert-group');

        $result = $this->_query($sql, array(':group' => $group));
        if($result === false) return false;
        return true;
    }

    /**
     * Enters the user to the group
     *
     * @param array $userdata all the user data
     * @param array $groupdata all the group data
     * @return bool
     */
    protected function _joinGroup($userdata, $groupdata) {
        $data = array_merge($userdata, $groupdata);
        $sql = $this->getConf('join-group');
        $result = $this->_query($sql, $data);
        if($result === false) return false;
        return true;
    }

    /**
     * Executes a query
     *
     * @param string $sql The SQL statement to execute
     * @param array $arguments Named parameters to be used in the statement
     * @return array|bool The result as associative array, false on error
     */
    protected function _query($sql, $arguments = array()) {
        if(empty($sql)) {
            $this->_debug('No SQL query given', -1, __LINE__);
            return false;
        }

        // prepare parameters - we only use those that exist in the SQL
        $params = array();
        foreach($arguments as $key => $value) {
            if(is_array($value)) continue;
            if(is_object($value)) continue;
            if($key[0] != ':') $key = ":$key"; // prefix with colon if needed
            if(strpos($sql, $key) !== false) $params[$key] = $value;
        }

        // execute
        $sth = $this->pdo->prepare($sql);
        try {
            $sth->execute($params);
            $result = $sth->fetchAll();
        } catch(Exception $e) {
            $dsql = $this->_debugSQL($sql, $params, !defined('DOKU_UNITTEST'));
            $this->_debug($e);
            $this->_debug("SQL: <pre>$dsql</pre>", -1, $e->getLine());
            $result = false;
        } finally {
            $sth->closeCursor();
            $sth = null;
        }

        return $result;
    }

    /**
     * Wrapper around msg() but outputs only when debug is enabled
     *
     * @param string|Exception $message
     * @param int $err
     * @param int $line
     */
    protected function _debug($message, $err = 0, $line = 0) {
        if(!$this->getConf('debug')) return;
        if(is_a($message, 'Exception')) {
            $err = -1;
            $line = $message->getLine();
            $msg = $message->getMessage();
        } else {
            $msg = $message;
        }

        if(defined('DOKU_UNITTEST')) {
            printf("\n%s, %s:%d\n", $msg, __FILE__, $line);
        } else {
            msg('authpdo: ' . $msg, $err, $line, __FILE__);
        }
    }

    /**
     * Check if the given config strings are set
     *
     * @author  Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     *
     * @param   string[] $keys
     * @return  bool
     */
    protected function _chkcnf($keys) {
        foreach($keys as $key) {
            if(!$this->getConf($key)) return false;
        }

        return true;
    }

    /**
     * create an approximation of the SQL string with parameters replaced
     *
     * @param string $sql
     * @param array $params
     * @param bool $htmlescape Should the result be escaped for output in HTML?
     * @return string
     */
    protected function _debugSQL($sql, $params, $htmlescape=true) {
        foreach($params as $key => $val) {
            if(is_int($val)) {
                $val = $this->pdo->quote($val, PDO::PARAM_INT);
            } elseif(is_bool($val)) {
                $val = $this->pdo->quote($val, PDO::PARAM_BOOL);
            } elseif(is_null($val)) {
                $val = 'NULL';
            } else {
                $val = $this->pdo->quote($val);
            }
            $sql = str_replace($key, $val, $sql);
        }
        if($htmlescape) $sql = hsc($sql);
        return $sql;
    }
}

// vim:ts=4:sw=4:et:
