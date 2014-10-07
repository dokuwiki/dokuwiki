<?php
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * Plaintext authentication backend
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Chris Smith <chris@jalakai.co.uk>
 * @author     Jan Schumann <js@schumann-it.com>
 */
class auth_plugin_authplain extends DokuWiki_Auth_Plugin {
    /** @var array user cache */
    protected $users = null;

    /** @var array filter pattern */
    protected $_pattern = array();

    /** @var bool safe version of preg_split */
    protected $_pregsplit_safe = false;

    /**
     * Constructor
     *
     * Carry out sanity checks to ensure the object is
     * able to operate. Set capabilities.
     *
     * @author  Christopher Smith <chris@jalakai.co.uk>
     */
    public function __construct() {
        parent::__construct();
        global $config_cascade;

        if(!@is_readable($config_cascade['plainauth.users']['default'])) {
            $this->success = false;
        } else {
            if(@is_writable($config_cascade['plainauth.users']['default'])) {
                $this->cando['addUser']   = true;
                $this->cando['delUser']   = true;
                $this->cando['modLogin']  = true;
                $this->cando['modPass']   = true;
                $this->cando['modName']   = true;
                $this->cando['modMail']   = true;
                $this->cando['modGroups'] = true;
            }
            $this->cando['getUsers']     = true;
            $this->cando['getUserCount'] = true;
        }

        $this->_pregsplit_safe = version_compare(PCRE_VERSION,'6.7','>=');
    }

    /**
     * Check user+password
     *
     * Checks if the given user exists and the given
     * plaintext password is correct
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @param string $user
     * @param string $pass
     * @return  bool
     */
    public function checkPass($user, $pass) {
        $userinfo = $this->getUserData($user);
        if($userinfo === false) return false;

        return auth_verifyPassword($pass, $this->users[$user]['pass']);
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
     * @param string $user
     * @param bool $requireGroups  (optional) ignored by this plugin, grps info always supplied
     * @return array|bool
     */
    public function getUserData($user, $requireGroups=true) {
        if($this->users === null) $this->_loadUserData();
        return isset($this->users[$user]) ? $this->users[$user] : false;
    }

    /**
     * Creates a string suitable for saving as a line
     * in the file database
     * (delimiters escaped, etc.)
     *
     * @param string $user
     * @param string $pass
     * @param string $name
     * @param string $mail
     * @param array  $grps list of groups the user is in
     * @return string
     */
    protected function _createUserLine($user, $pass, $name, $mail, $grps) {
        $groups   = join(',', $grps);
        $userline = array($user, $pass, $name, $mail, $groups);
        $userline = str_replace('\\', '\\\\', $userline); // escape \ as \\
        $userline = str_replace(':', '\\:', $userline); // escape : as \:
        $userline = join(':', $userline)."\n";
        return $userline;
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
     *
     * @param string $user
     * @param string $pwd
     * @param string $name
     * @param string $mail
     * @param array  $grps
     * @return bool|null|string
     */
    public function createUser($user, $pwd, $name, $mail, $grps = null) {
        global $conf;
        global $config_cascade;

        // user mustn't already exist
        if($this->getUserData($user) !== false) return false;

        $pass = auth_cryptPassword($pwd);

        // set default group if no groups specified
        if(!is_array($grps)) $grps = array($conf['defaultgroup']);

        // prepare user line
        $userline = $this->_createUserLine($user, $pass, $name, $mail, $grps);

        if(io_saveFile($config_cascade['plainauth.users']['default'], $userline, true)) {
            $this->users[$user] = compact('pass', 'name', 'mail', 'grps');
            return $pwd;
        }

        msg(
            'The '.$config_cascade['plainauth.users']['default'].
                ' file is not writable. Please inform the Wiki-Admin', -1
        );
        return null;
    }

    /**
     * Modify user data
     *
     * @author  Chris Smith <chris@jalakai.co.uk>
     * @param   string $user      nick of the user to be changed
     * @param   array  $changes   array of field/value pairs to be changed (password will be clear text)
     * @return  bool
     */
    public function modifyUser($user, $changes) {
        global $ACT;
        global $config_cascade;

        // sanity checks, user must already exist and there must be something to change
        if(($userinfo = $this->getUserData($user)) === false) return false;
        if(!is_array($changes) || !count($changes)) return true;

        // update userinfo with new data, remembering to encrypt any password
        $newuser = $user;
        foreach($changes as $field => $value) {
            if($field == 'user') {
                $newuser = $value;
                continue;
            }
            if($field == 'pass') $value = auth_cryptPassword($value);
            $userinfo[$field] = $value;
        }

        $userline = $this->_createUserLine($newuser, $userinfo['pass'], $userinfo['name'], $userinfo['mail'], $userinfo['grps']);

        if(!$this->deleteUsers(array($user))) {
            msg('Unable to modify user data. Please inform the Wiki-Admin', -1);
            return false;
        }

        if(!io_saveFile($config_cascade['plainauth.users']['default'], $userline, true)) {
            msg('There was an error modifying your user data. You should register again.', -1);
            // FIXME, user has been deleted but not recreated, should force a logout and redirect to login page
            $ACT = 'register';
            return false;
        }

        $this->users[$newuser] = $userinfo;
        return true;
    }

    /**
     * Remove one or more users from the list of registered users
     *
     * @author  Christopher Smith <chris@jalakai.co.uk>
     * @param   array  $users   array of users to be deleted
     * @return  int             the number of users deleted
     */
    public function deleteUsers($users) {
        global $config_cascade;

        if(!is_array($users) || empty($users)) return 0;

        if($this->users === null) $this->_loadUserData();

        $deleted = array();
        foreach($users as $user) {
            if(isset($this->users[$user])) $deleted[] = preg_quote($user, '/');
        }

        if(empty($deleted)) return 0;

        $pattern = '/^('.join('|', $deleted).'):/';
        io_deleteFromFile($config_cascade['plainauth.users']['default'], $pattern, true);

        // reload the user list and count the difference
        $count = count($this->users);
        $this->_loadUserData();
        $count -= count($this->users);
        return $count;
    }

    /**
     * Return a count of the number of user which meet $filter criteria
     *
     * @author  Chris Smith <chris@jalakai.co.uk>
     *
     * @param array $filter
     * @return int
     */
    public function getUserCount($filter = array()) {

        if($this->users === null) $this->_loadUserData();

        if(!count($filter)) return count($this->users);

        $count = 0;
        $this->_constructPattern($filter);

        foreach($this->users as $user => $info) {
            $count += $this->_filter($user, $info);
        }

        return $count;
    }

    /**
     * Bulk retrieval of user data
     *
     * @author  Chris Smith <chris@jalakai.co.uk>
     *
     * @param   int   $start index of first user to be returned
     * @param   int   $limit max number of users to be returned
     * @param   array $filter array of field/pattern pairs
     * @return  array userinfo (refer getUserData for internal userinfo details)
     */
    public function retrieveUsers($start = 0, $limit = 0, $filter = array()) {

        if($this->users === null) $this->_loadUserData();

        ksort($this->users);

        $i     = 0;
        $count = 0;
        $out   = array();
        $this->_constructPattern($filter);

        foreach($this->users as $user => $info) {
            if($this->_filter($user, $info)) {
                if($i >= $start) {
                    $out[$user] = $info;
                    $count++;
                    if(($limit > 0) && ($count >= $limit)) break;
                }
                $i++;
            }
        }

        return $out;
    }

    /**
     * Only valid pageid's (no namespaces) for usernames
     *
     * @param string $user
     * @return string
     */
    public function cleanUser($user) {
        global $conf;
        return cleanID(str_replace(':', $conf['sepchar'], $user));
    }

    /**
     * Only valid pageid's (no namespaces) for groupnames
     *
     * @param string $group
     * @return string
     */
    public function cleanGroup($group) {
        global $conf;
        return cleanID(str_replace(':', $conf['sepchar'], $group));
    }

    /**
     * Load all user data
     *
     * loads the user file into a datastructure
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     */
    protected function _loadUserData() {
        global $config_cascade;

        $this->users = array();

        if(!@file_exists($config_cascade['plainauth.users']['default'])) return;

        $lines = file($config_cascade['plainauth.users']['default']);
        foreach($lines as $line) {
            $line = preg_replace('/#.*$/', '', $line); //ignore comments
            $line = trim($line);
            if(empty($line)) continue;

            /* NB: preg_split can be deprecated/replaced with str_getcsv once dokuwiki is min php 5.3 */
            $row = $this->_splitUserData($line);
            $row = str_replace('\\:', ':', $row);
            $row = str_replace('\\\\', '\\', $row);

            $groups = array_values(array_filter(explode(",", $row[4])));

            $this->users[$row[0]]['pass'] = $row[1];
            $this->users[$row[0]]['name'] = urldecode($row[2]);
            $this->users[$row[0]]['mail'] = $row[3];
            $this->users[$row[0]]['grps'] = $groups;
        }
    }

    protected function _splitUserData($line){
        // due to a bug in PCRE 6.6, preg_split will fail with the regex we use here
        // refer github issues 877 & 885
        if ($this->_pregsplit_safe){
            return preg_split('/(?<![^\\\\]\\\\)\:/', $line, 5);       // allow for : escaped as \:
        }

        $row = array();
        $piece = '';
        $len = strlen($line);
        for($i=0; $i<$len; $i++){
            if ($line[$i]=='\\'){
                $piece .= $line[$i];
                $i++;
                if ($i>=$len) break;
            } else if ($line[$i]==':'){
                $row[] = $piece;
                $piece = '';
                continue;
            }
            $piece .= $line[$i];
        }
        $row[] = $piece;

        return $row;
    }

    /**
     * return true if $user + $info match $filter criteria, false otherwise
     *
     * @author   Chris Smith <chris@jalakai.co.uk>
     *
     * @param string $user User login
     * @param array  $info User's userinfo array
     * @return bool
     */
    protected function _filter($user, $info) {
        foreach($this->_pattern as $item => $pattern) {
            if($item == 'user') {
                if(!preg_match($pattern, $user)) return false;
            } else if($item == 'grps') {
                if(!count(preg_grep($pattern, $info['grps']))) return false;
            } else {
                if(!preg_match($pattern, $info[$item])) return false;
            }
        }
        return true;
    }

    /**
     * construct a filter pattern
     *
     * @param array $filter
     */
    protected function _constructPattern($filter) {
        $this->_pattern = array();
        foreach($filter as $item => $pattern) {
            $this->_pattern[$item] = '/'.str_replace('/', '\/', $pattern).'/i'; // allow regex characters
        }
    }
}