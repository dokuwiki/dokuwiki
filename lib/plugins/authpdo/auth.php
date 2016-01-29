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
     * @param  string $pass
     * @param  string $name
     * @param  string $mail
     * @param  null|array $grps
     * @return bool|null
     */
    //public function createUser($user, $pass, $name, $mail, $grps = null) {
    // FIXME implement
    //    return null;
    //}

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
     * Retrieve groups [implement only where required/possible]
     *
     * Set getGroups capability when implemented
     *
     * @param   int $start
     * @param   int $limit
     * @return  array
     */
    //public function retrieveGroups($start = 0, $limit = 0) {
    // FIXME implement
    //    return array();
    //}

    /**
     * Return case sensitivity of the backend
     *
     * When your backend is caseinsensitive (eg. you can login with USER and
     * user) then you need to overwrite this method and return false
     *
     * @return bool
     */
    public function isCaseSensitive() {
        return true;
    }

    /**
     * Sanitize a given username
     *
     * This function is applied to any user name that is given to
     * the backend and should also be applied to any user name within
     * the backend before returning it somewhere.
     *
     * This should be used to enforce username restrictions.
     *
     * @param string $user username
     * @return string the cleaned username
     */
    public function cleanUser($user) {
        return $user;
    }

    /**
     * Sanitize a given groupname
     *
     * This function is applied to any groupname that is given to
     * the backend and should also be applied to any groupname within
     * the backend before returning it somewhere.
     *
     * This should be used to enforce groupname restrictions.
     *
     * Groupnames are to be passed without a leading '@' here.
     *
     * @param  string $group groupname
     * @return string the cleaned groupname
     */
    public function cleanGroup($group) {
        return $group;
    }

    /**
     * Check Session Cache validity [implement only where required/possible]
     *
     * DokuWiki caches user info in the user's session for the timespan defined
     * in $conf['auth_security_timeout'].
     *
     * This makes sure slow authentication backends do not slow down DokuWiki.
     * This also means that changes to the user database will not be reflected
     * on currently logged in users.
     *
     * To accommodate for this, the user manager plugin will touch a reference
     * file whenever a change is submitted. This function compares the filetime
     * of this reference file with the time stored in the session.
     *
     * This reference file mechanism does not reflect changes done directly in
     * the backend's database through other means than the user manager plugin.
     *
     * Fast backends might want to return always false, to force rechecks on
     * each page load. Others might want to use their own checking here. If
     * unsure, do not override.
     *
     * @param  string $user - The username
     * @return bool
     */
    //public function useSessionCache($user) {
    // FIXME implement
    //}

    /**
     * Select data of a specified user
     *
     * @param $user
     * @return bool|array
     */
    protected function _selectUser($user) {
        $sql = $this->getConf('select-user');

        $result = $this->query($sql, array(':user' => $user));
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
     * @return array
     */
    protected function _selectUserGroups($userdata) {
        global $conf;
        $sql = $this->getConf('select-user-groups');

        $result = $this->query($sql, $userdata);

        $groups = array($conf['defaultgroup']); // always add default config
        if($result) foreach($result as $row) {
            if(!isset($row['group'])) continue;
            $groups[] = $row['group'];
        }

        $groups = array_unique($groups);
        sort($groups);
        return $groups;
    }

    /**
     * Executes a query
     *
     * @param string $sql The SQL statement to execute
     * @param array $arguments Named parameters to be used in the statement
     * @return array|bool The result as associative array
     */
    protected function query($sql, $arguments) {
        // prepare parameters - we only use those that exist in the SQL
        $params = array();
        foreach($arguments as $key => $value) {
            if(is_array($value)) continue;
            if(is_object($value)) continue;
            if($key[0] != ':') $key = ":$key"; // prefix with colon if needed
            if(strpos($sql, $key) !== false) $params[$key] = $value;
        }

        // execute
        try {
            $sth = $this->pdo->prepare($sql);
            $sth->execute($params);
            $result = $sth->fetchAll();
            if((int) $sth->errorCode()) {
                $this->_debug(join(' ',$sth->errorInfo()), -1, __LINE__);
                $result = false;
            }
            $sth->closeCursor();
            $sth = null;
        } catch(PDOException $e) {
            $this->_debug($e);
            $result = false;
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
}

// vim:ts=4:sw=4:et:
