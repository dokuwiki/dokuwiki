<?php
/**
 * DokuWiki Plugin authpdo (Auth Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <andi@splitbrain.org>
 */

/**
 * Class auth_plugin_authpdo
 */
class auth_plugin_authpdo extends DokuWiki_Auth_Plugin
{

    /** @var PDO */
    protected $pdo;

    /** @var null|array The list of all groups */
    protected $groupcache = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(); // for compatibility

        if (!class_exists('PDO')) {
            $this->debugMsg('PDO extension for PHP not found.', -1, __LINE__);
            $this->success = false;
            return;
        }

        if (!$this->getConf('dsn')) {
            $this->debugMsg('No DSN specified', -1, __LINE__);
            $this->success = false;
            return;
        }

        try {
            $this->pdo = new PDO(
                $this->getConf('dsn'),
                $this->getConf('user'),
                conf_decodeString($this->getConf('pass')),
                array(
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // always fetch as array
                    PDO::ATTR_EMULATE_PREPARES => true, // emulating prepares allows us to reuse param names
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // we want exceptions, not error codes
                )
            );
        } catch (PDOException $e) {
            $this->debugMsg($e);
            msg($this->getLang('connectfail'), -1);
            $this->success = false;
            return;
        }

        // can Users be created?
        $this->cando['addUser'] = $this->checkConfig(
            array(
                'select-user',
                'select-user-groups',
                'select-groups',
                'insert-user',
                'insert-group',
                'join-group'
            )
        );

        // can Users be deleted?
        $this->cando['delUser'] = $this->checkConfig(
            array(
                'select-user',
                'select-user-groups',
                'select-groups',
                'leave-group',
                'delete-user'
            )
        );

        // can login names be changed?
        $this->cando['modLogin'] = $this->checkConfig(
            array(
                'select-user',
                'select-user-groups',
                'update-user-login'
            )
        );

        // can passwords be changed?
        $this->cando['modPass'] = $this->checkConfig(
            array(
                'select-user',
                'select-user-groups',
                'update-user-pass'
            )
        );

        // can real names be changed?
        $this->cando['modName'] = $this->checkConfig(
            array(
                'select-user',
                'select-user-groups',
                'update-user-info:name'
            )
        );

        // can real email be changed?
        $this->cando['modMail'] = $this->checkConfig(
            array(
                'select-user',
                'select-user-groups',
                'update-user-info:mail'
            )
        );

        // can groups be changed?
        $this->cando['modGroups'] = $this->checkConfig(
            array(
                'select-user',
                'select-user-groups',
                'select-groups',
                'leave-group',
                'join-group',
                'insert-group'
            )
        );

        // can a filtered list of users be retrieved?
        $this->cando['getUsers'] = $this->checkConfig(
            array(
                'list-users'
            )
        );

        // can the number of users be retrieved?
        $this->cando['getUserCount'] = $this->checkConfig(
            array(
                'count-users'
            )
        );

        // can a list of available groups be retrieved?
        $this->cando['getGroups'] = $this->checkConfig(
            array(
                'select-groups'
            )
        );

        $this->success = true;
    }

    /**
     * Check user+password
     *
     * @param string $user the user name
     * @param string $pass the clear text password
     * @return  bool
     */
    public function checkPass($user, $pass)
    {

        $userdata = $this->selectUser($user);
        if ($userdata == false) return false;

        // password checking done in SQL?
        if ($this->checkConfig(array('check-pass'))) {
            $userdata['clear'] = $pass;
            $userdata['hash'] = auth_cryptPassword($pass);
            $result = $this->query($this->getConf('check-pass'), $userdata);
            if ($result === false) return false;
            return (count($result) == 1);
        }

        // we do password checking on our own
        if (isset($userdata['hash'])) {
            // hashed password
            $passhash = new \dokuwiki\PassHash();
            return $passhash->verify_hash($pass, $userdata['hash']);
        } else {
            // clear text password in the database O_o
            return ($pass === $userdata['clear']);
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
     * @param string $user the user name
     * @param bool $requireGroups whether or not the returned data must include groups
     * @return array|bool containing user data or false
     */
    public function getUserData($user, $requireGroups = true)
    {
        $data = $this->selectUser($user);
        if ($data == false) return false;

        if (isset($data['hash'])) unset($data['hash']);
        if (isset($data['clean'])) unset($data['clean']);

        if ($requireGroups) {
            $data['grps'] = $this->selectUserGroups($data);
            if ($data['grps'] === false) return false;
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
     * @param string $user
     * @param string $clear
     * @param string $name
     * @param string $mail
     * @param null|array $grps
     * @return bool|null
     */
    public function createUser($user, $clear, $name, $mail, $grps = null)
    {
        global $conf;

        if (($info = $this->getUserData($user, false)) !== false) {
            msg($this->getLang('userexists'), -1);
            return false; // user already exists
        }

        // prepare data
        if ($grps == null) $grps = array();
        array_unshift($grps, $conf['defaultgroup']);
        $grps = array_unique($grps);
        $hash = auth_cryptPassword($clear);
        $userdata = compact('user', 'clear', 'hash', 'name', 'mail');

        // action protected by transaction
        $this->pdo->beginTransaction();
        {
            // insert the user
            $ok = $this->query($this->getConf('insert-user'), $userdata);
            if ($ok === false) goto FAIL;
            $userdata = $this->getUserData($user, false);
            if ($userdata === false) goto FAIL;

            // create all groups that do not exist, the refetch the groups
            $allgroups = $this->selectGroups();
            foreach ($grps as $group) {
                if (!isset($allgroups[$group])) {
                    $ok = $this->addGroup($group);
                    if ($ok === false) goto FAIL;
                }
            }
            $allgroups = $this->selectGroups();

            // add user to the groups
            foreach ($grps as $group) {
                $ok = $this->joinGroup($userdata, $allgroups[$group]);
                if ($ok === false) goto FAIL;
            }
        }
        $this->pdo->commit();
        return true;

        // something went wrong, rollback
        FAIL:
        $this->pdo->rollBack();
        $this->debugMsg('Transaction rolled back', 0, __LINE__);
        msg($this->getLang('writefail'), -1);
        return null; // return error
    }

    /**
     * Modify user data
     *
     * @param string $user nick of the user to be changed
     * @param array $changes array of field/value pairs to be changed (password will be clear text)
     * @return  bool
     */
    public function modifyUser($user, $changes)
    {
        // secure everything in transaction
        $this->pdo->beginTransaction();
        {
            $olddata = $this->getUserData($user);
            $oldgroups = $olddata['grps'];
            unset($olddata['grps']);

            // changing the user name?
            if (isset($changes['user'])) {
                if ($this->getUserData($changes['user'], false)) goto FAIL;
                $params = $olddata;
                $params['newlogin'] = $changes['user'];

                $ok = $this->query($this->getConf('update-user-login'), $params);
                if ($ok === false) goto FAIL;
            }

            // changing the password?
            if (isset($changes['pass'])) {
                $params = $olddata;
                $params['clear'] = $changes['pass'];
                $params['hash'] = auth_cryptPassword($changes['pass']);

                $ok = $this->query($this->getConf('update-user-pass'), $params);
                if ($ok === false) goto FAIL;
            }

            // changing info?
            if (isset($changes['mail']) || isset($changes['name'])) {
                $params = $olddata;
                if (isset($changes['mail'])) $params['mail'] = $changes['mail'];
                if (isset($changes['name'])) $params['name'] = $changes['name'];

                $ok = $this->query($this->getConf('update-user-info'), $params);
                if ($ok === false) goto FAIL;
            }

            // changing groups?
            if (isset($changes['grps'])) {
                $allgroups = $this->selectGroups();

                // remove membership for previous groups
                foreach ($oldgroups as $group) {
                    if (!in_array($group, $changes['grps']) && isset($allgroups[$group])) {
                        $ok = $this->leaveGroup($olddata, $allgroups[$group]);
                        if ($ok === false) goto FAIL;
                    }
                }

                // create all new groups that are missing
                $added = 0;
                foreach ($changes['grps'] as $group) {
                    if (!isset($allgroups[$group])) {
                        $ok = $this->addGroup($group);
                        if ($ok === false) goto FAIL;
                        $added++;
                    }
                }
                // reload group info
                if ($added > 0) $allgroups = $this->selectGroups();

                // add membership for new groups
                foreach ($changes['grps'] as $group) {
                    if (!in_array($group, $oldgroups)) {
                        $ok = $this->joinGroup($olddata, $allgroups[$group]);
                        if ($ok === false) goto FAIL;
                    }
                }
            }

        }
        $this->pdo->commit();
        return true;

        // something went wrong, rollback
        FAIL:
        $this->pdo->rollBack();
        $this->debugMsg('Transaction rolled back', 0, __LINE__);
        msg($this->getLang('writefail'), -1);
        return false; // return error
    }

    /**
     * Delete one or more users
     *
     * Set delUser capability when implemented
     *
     * @param array $users
     * @return  int    number of users deleted
     */
    public function deleteUsers($users)
    {
        $count = 0;
        foreach ($users as $user) {
            if ($this->deleteUser($user)) $count++;
        }
        return $count;
    }

    /**
     * Bulk retrieval of user data [implement only where required/possible]
     *
     * Set getUsers capability when implemented
     *
     * @param int $start index of first user to be returned
     * @param int $limit max number of users to be returned
     * @param array $filter array of field/pattern pairs, null for no filter
     * @return  array list of userinfo (refer getUserData for internal userinfo details)
     */
    public function retrieveUsers($start = 0, $limit = -1, $filter = null)
    {
        if ($limit < 0) $limit = 10000; // we don't support no limit
        if (is_null($filter)) $filter = array();

        if (isset($filter['grps'])) $filter['group'] = $filter['grps'];
        foreach (array('user', 'name', 'mail', 'group') as $key) {
            if (!isset($filter[$key])) {
                $filter[$key] = '%';
            } else {
                $filter[$key] = '%' . $filter[$key] . '%';
            }
        }
        $filter['start'] = (int)$start;
        $filter['end'] = (int)$start + $limit;
        $filter['limit'] = (int)$limit;

        $result = $this->query($this->getConf('list-users'), $filter);
        if (!$result) return array();
        $users = array();
        if (is_array($result)) {
            foreach ($result as $row) {
                if (!isset($row['user'])) {
                    $this->debugMsg("list-users statement did not return 'user' attribute", -1, __LINE__);
                    return array();
                }
                $users[] = $this->getUserData($row['user']);
            }
        } else {
            $this->debugMsg("list-users statement did not return a list of result", -1, __LINE__);
        }
        return $users;
    }

    /**
     * Return a count of the number of user which meet $filter criteria
     *
     * @param array $filter array of field/pattern pairs, empty array for no filter
     * @return int
     */
    public function getUserCount($filter = array())
    {
        if (is_null($filter)) $filter = array();

        if (isset($filter['grps'])) $filter['group'] = $filter['grps'];
        foreach (array('user', 'name', 'mail', 'group') as $key) {
            if (!isset($filter[$key])) {
                $filter[$key] = '%';
            } else {
                $filter[$key] = '%' . $filter[$key] . '%';
            }
        }

        $result = $this->query($this->getConf('count-users'), $filter);
        if (!$result || !isset($result[0]['count'])) {
            $this->debugMsg("Statement did not return 'count' attribute", -1, __LINE__);
        }
        return (int)$result[0]['count'];
    }

    /**
     * Create a new group with the given name
     *
     * @param string $group
     * @return bool
     */
    public function addGroup($group)
    {
        $sql = $this->getConf('insert-group');

        $result = $this->query($sql, array(':group' => $group));
        $this->clearGroupCache();
        if ($result === false) return false;
        return true;
    }

    /**
     * Retrieve groups
     *
     * Set getGroups capability when implemented
     *
     * @param int $start
     * @param int $limit
     * @return  array
     */
    public function retrieveGroups($start = 0, $limit = 0)
    {
        $groups = array_keys($this->selectGroups());
        if ($groups === false) return array();

        if (!$limit) {
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
    protected function selectUser($user)
    {
        $sql = $this->getConf('select-user');

        $result = $this->query($sql, array(':user' => $user));
        if (!$result) return false;

        if (count($result) > 1) {
            $this->debugMsg('Found more than one matching user', -1, __LINE__);
            return false;
        }

        $data = array_shift($result);
        $dataok = true;

        if (!isset($data['user'])) {
            $this->debugMsg("Statement did not return 'user' attribute", -1, __LINE__);
            $dataok = false;
        }
        if (!isset($data['hash']) && !isset($data['clear']) && !$this->checkConfig(array('check-pass'))) {
            $this->debugMsg("Statement did not return 'clear' or 'hash' attribute", -1, __LINE__);
            $dataok = false;
        }
        if (!isset($data['name'])) {
            $this->debugMsg("Statement did not return 'name' attribute", -1, __LINE__);
            $dataok = false;
        }
        if (!isset($data['mail'])) {
            $this->debugMsg("Statement did not return 'mail' attribute", -1, __LINE__);
            $dataok = false;
        }

        if (!$dataok) return false;
        return $data;
    }

    /**
     * Delete a user after removing all their group memberships
     *
     * @param string $user
     * @return bool true when the user was deleted
     */
    protected function deleteUser($user)
    {
        $this->pdo->beginTransaction();
        {
            $userdata = $this->getUserData($user);
            if ($userdata === false) goto FAIL;
            $allgroups = $this->selectGroups();

            // remove group memberships (ignore errors)
            foreach ($userdata['grps'] as $group) {
                if (isset($allgroups[$group])) {
                    $this->leaveGroup($userdata, $allgroups[$group]);
                }
            }

            $ok = $this->query($this->getConf('delete-user'), $userdata);
            if ($ok === false) goto FAIL;
        }
        $this->pdo->commit();
        return true;

        FAIL:
        $this->pdo->rollBack();
        return false;
    }

    /**
     * Select all groups of a user
     *
     * @param array $userdata The userdata as returned by _selectUser()
     * @return array|bool list of group names, false on error
     */
    protected function selectUserGroups($userdata)
    {
        global $conf;
        $sql = $this->getConf('select-user-groups');
        $result = $this->query($sql, $userdata);
        if ($result === false) return false;

        $groups = array($conf['defaultgroup']); // always add default config
        if (is_array($result)) {
            foreach ($result as $row) {
                if (!isset($row['group'])) {
                    $this->debugMsg("No 'group' field returned in select-user-groups statement", -1, __LINE__);
                    return false;
                }
                $groups[] = $row['group'];
            }
        } else {
            $this->debugMsg("select-user-groups statement did not return a list of result", -1, __LINE__);
        }

        $groups = array_unique($groups);
        sort($groups);
        return $groups;
    }

    /**
     * Select all available groups
     *
     * @return array|bool list of all available groups and their properties
     */
    protected function selectGroups()
    {
        if ($this->groupcache) return $this->groupcache;

        $sql = $this->getConf('select-groups');
        $result = $this->query($sql);
        if ($result === false) return false;

        $groups = array();
        if (is_array($result)) {
            foreach ($result as $row) {
                if (!isset($row['group'])) {
                    $this->debugMsg("No 'group' field returned from select-groups statement", -1, __LINE__);
                    return false;
                }

                // relayout result with group name as key
                $group = $row['group'];
                $groups[$group] = $row;
            }
        } else {
            $this->debugMsg("select-groups statement did not return a list of result", -1, __LINE__);
        }

        ksort($groups);
        return $groups;
    }

    /**
     * Remove all entries from the group cache
     */
    protected function clearGroupCache()
    {
        $this->groupcache = null;
    }

    /**
     * Adds the user to the group
     *
     * @param array $userdata all the user data
     * @param array $groupdata all the group data
     * @return bool
     */
    protected function joinGroup($userdata, $groupdata)
    {
        $data = array_merge($userdata, $groupdata);
        $sql = $this->getConf('join-group');
        $result = $this->query($sql, $data);
        if ($result === false) return false;
        return true;
    }

    /**
     * Removes the user from the group
     *
     * @param array $userdata all the user data
     * @param array $groupdata all the group data
     * @return bool
     */
    protected function leaveGroup($userdata, $groupdata)
    {
        $data = array_merge($userdata, $groupdata);
        $sql = $this->getConf('leave-group');
        $result = $this->query($sql, $data);
        if ($result === false) return false;
        return true;
    }

    /**
     * Executes a query
     *
     * @param string $sql The SQL statement to execute
     * @param array $arguments Named parameters to be used in the statement
     * @return array|int|bool The result as associative array for SELECTs, affected rows for others, false on error
     */
    protected function query($sql, $arguments = array())
    {
        $sql = trim($sql);
        if (empty($sql)) {
            $this->debugMsg('No SQL query given', -1, __LINE__);
            return false;
        }

        // execute
        $params = array();
        $sth = $this->pdo->prepare($sql);
        $result = false;
        try {
            // prepare parameters - we only use those that exist in the SQL
            foreach ($arguments as $key => $value) {
                if (is_array($value)) continue;
                if (is_object($value)) continue;
                if ($key[0] != ':') $key = ":$key"; // prefix with colon if needed
                if (strpos($sql, $key) === false) continue; // skip if parameter is missing

                if (is_int($value)) {
                    $sth->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $sth->bindValue($key, $value);
                }
                $params[$key] = $value; //remember for debugging
            }

            $sth->execute();
            // only report last line's result
            $hasnextrowset = true;
            $currentsql = $sql;
            while ($hasnextrowset) {
                if (strtolower(substr($currentsql, 0, 6)) == 'select') {
                    $result = $sth->fetchAll();
                } else {
                    $result = $sth->rowCount();
                }
                $semi_pos = strpos($currentsql, ';');
                if ($semi_pos) {
                    $currentsql = trim(substr($currentsql, $semi_pos + 1));
                }
                try {
                    $hasnextrowset = $sth->nextRowset(); // run next rowset
                } catch (PDOException $rowset_e) {
                    $hasnextrowset = false; // driver does not support multi-rowset, should be executed in one time
                }
            }
        } catch (Exception $e) {
            // report the caller's line
            $trace = debug_backtrace();
            $line = $trace[0]['line'];
            $dsql = $this->debugSQL($sql, $params, !defined('DOKU_UNITTEST'));
            $this->debugMsg($e, -1, $line);
            $this->debugMsg("SQL: <pre>$dsql</pre>", -1, $line);
        }
        $sth->closeCursor();
        $sth = null;

        return $result;
    }

    /**
     * Wrapper around msg() but outputs only when debug is enabled
     *
     * @param string|Exception $message
     * @param int $err
     * @param int $line
     */
    protected function debugMsg($message, $err = 0, $line = 0)
    {
        if (!$this->getConf('debug')) return;
        if (is_a($message, 'Exception')) {
            $err = -1;
            $msg = $message->getMessage();
            if (!$line) $line = $message->getLine();
        } else {
            $msg = $message;
        }

        if (defined('DOKU_UNITTEST')) {
            printf("\n%s, %s:%d\n", $msg, __FILE__, $line);
        } else {
            msg('authpdo: ' . $msg, $err, $line, __FILE__);
        }
    }

    /**
     * Check if the given config strings are set
     *
     * @param string[] $keys
     * @return  bool
     * @author  Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     *
     */
    protected function checkConfig($keys)
    {
        foreach ($keys as $key) {
            $params = explode(':', $key);
            $key = array_shift($params);
            $sql = trim($this->getConf($key));

            // check if sql is set
            if (!$sql) return false;
            // check if needed params are there
            foreach ($params as $param) {
                if (strpos($sql, ":$param") === false) return false;
            }
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
    protected function debugSQL($sql, $params, $htmlescape = true)
    {
        foreach ($params as $key => $val) {
            if (is_int($val)) {
                $val = $this->pdo->quote($val, PDO::PARAM_INT);
            } elseif (is_bool($val)) {
                $val = $this->pdo->quote($val, PDO::PARAM_BOOL);
            } elseif (is_null($val)) {
                $val = 'NULL';
            } else {
                $val = $this->pdo->quote($val);
            }
            $sql = str_replace($key, $val, $sql);
        }
        if ($htmlescape) $sql = hsc($sql);
        return $sql;
    }
}

// vim:ts=4:sw=4:et:
