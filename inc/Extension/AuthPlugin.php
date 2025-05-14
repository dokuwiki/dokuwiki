<?php

namespace dokuwiki\Extension;

/**
 * Auth Plugin Prototype
 *
 * allows to authenticate users in a plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Chris Smith <chris@jalakai.co.uk>
 * @author     Jan Schumann <js@jschumann-it.com>
 */
abstract class AuthPlugin extends Plugin
{
    public $success = true;

    /**
     * Possible things an auth backend module may be able to
     * do. The things a backend can do need to be set to true
     * in the constructor.
     */
    protected $cando = [
        'addUser' => false, // can Users be created?
        'delUser' => false, // can Users be deleted?
        'modLogin' => false, // can login names be changed?
        'modPass' => false, // can passwords be changed?
        'modName' => false, // can real names be changed?
        'modMail' => false, // can emails be changed?
        'modGroups' => false, // can groups be changed?
        'getUsers' => false, // can a (filtered) list of users be retrieved?
        'getUserCount' => false, // can the number of users be retrieved?
        'getGroups' => false, // can a list of available groups be retrieved?
        'external' => false, // does the module do external auth checking?
        'logout' => true, // can the user logout again? (eg. not possible with HTTP auth)
    ];

    /**
     * Constructor.
     *
     * Carry out sanity checks to ensure the object is
     * able to operate. Set capabilities in $this->cando
     * array here
     *
     * For future compatibility, sub classes should always include a call
     * to parent::__constructor() in their constructors!
     *
     * Set $this->success to false if checks fail
     *
     * @author  Christopher Smith <chris@jalakai.co.uk>
     */
    public function __construct()
    {
        // the base class constructor does nothing, derived class
        // constructors do the real work
    }

    /**
     * Available Capabilities. [ DO NOT OVERRIDE ]
     *
     * For introspection/debugging
     *
     * @author  Christopher Smith <chris@jalakai.co.uk>
     * @return  array
     */
    public function getCapabilities()
    {
        return array_keys($this->cando);
    }

    /**
     * Capability check. [ DO NOT OVERRIDE ]
     *
     * Checks the capabilities set in the $this->cando array and
     * some pseudo capabilities (shortcutting access to multiple
     * ones)
     *
     * ususal capabilities start with lowercase letter
     * shortcut capabilities start with uppercase letter
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @param   string $cap the capability to check
     * @return  bool
     */
    public function canDo($cap)
    {
        switch ($cap) {
            case 'Profile':
                // can at least one of the user's properties be changed?
                return ($this->cando['modPass'] ||
                    $this->cando['modName'] ||
                    $this->cando['modMail']);
            case 'UserMod':
                // can at least anything be changed?
                return ($this->cando['modPass'] ||
                    $this->cando['modName'] ||
                    $this->cando['modMail'] ||
                    $this->cando['modLogin'] ||
                    $this->cando['modGroups'] ||
                    $this->cando['modMail']);
            default:
                // print a helping message for developers
                if (!isset($this->cando[$cap])) {
                    msg("Check for unknown capability '$cap' - Do you use an outdated Plugin?", -1);
                }
                return $this->cando[$cap];
        }
    }

    /**
     * Trigger the AUTH_USERDATA_CHANGE event and call the modification function. [ DO NOT OVERRIDE ]
     *
     * You should use this function instead of calling createUser, modifyUser or
     * deleteUsers directly. The event handlers can prevent the modification, for
     * example for enforcing a user name schema.
     *
     * @author Gabriel Birke <birke@d-scribe.de>
     * @param string $type Modification type ('create', 'modify', 'delete')
     * @param array $params Parameters for the createUser, modifyUser or deleteUsers method.
     *                       The content of this array depends on the modification type
     * @return bool|null|int Result from the modification function or false if an event handler has canceled the action
     */
    public function triggerUserMod($type, $params)
    {
        $validTypes = [
            'create' => 'createUser',
            'modify' => 'modifyUser',
            'delete' => 'deleteUsers'
        ];
        if (empty($validTypes[$type])) {
            return false;
        }

        $result = false;
        $eventdata = ['type' => $type, 'params' => $params, 'modification_result' => null];
        $evt = new Event('AUTH_USER_CHANGE', $eventdata);
        if ($evt->advise_before(true)) {
            $result = call_user_func_array([$this, $validTypes[$type]], $evt->data['params']);
            $evt->data['modification_result'] = $result;
        }
        $evt->advise_after();
        unset($evt);
        return $result;
    }

    /**
     * Log off the current user [ OPTIONAL ]
     *
     * Is run in addition to the ususal logoff method. Should
     * only be needed when trustExternal is implemented.
     *
     * @see     auth_logoff()
     * @author  Andreas Gohr <andi@splitbrain.org>
     */
    public function logOff()
    {
    }

    /**
     * Do all authentication [ OPTIONAL ]
     *
     * Set $this->cando['external'] = true when implemented
     *
     * If this function is implemented it will be used to
     * authenticate a user - all other DokuWiki internals
     * will not be used for authenticating (except this
     * function returns null, in which case, DokuWiki will
     * still run auth_login as a fallback, which may call
     * checkPass()). If this function is not returning null,
     * implementing checkPass() is not needed here anymore.
     *
     * The function can be used to authenticate against third
     * party cookies or Apache auth mechanisms and replaces
     * the auth_login() function
     *
     * The function will be called with or without a set
     * username. If the Username is given it was called
     * from the login form and the given credentials might
     * need to be checked. If no username was given it
     * the function needs to check if the user is logged in
     * by other means (cookie, environment).
     *
     * The function needs to set some globals needed by
     * DokuWiki like auth_login() does.
     *
     * @see     auth_login()
     * @author  Andreas Gohr <andi@splitbrain.org>
     *
     * @param   string $user Username
     * @param   string $pass Cleartext Password
     * @param   bool $sticky Cookie should not expire
     * @return  bool         true on successful auth,
     *                       null on unknown result (fallback to checkPass)
     */
    public function trustExternal($user, $pass, $sticky = false)
    {
        /* some example:

        global $USERINFO;
        global $conf;
        $sticky ? $sticky = true : $sticky = false; //sanity check

        // do the checking here

        // set the globals if authed
        $USERINFO['name'] = 'FIXME';
        $USERINFO['mail'] = 'FIXME';
        $USERINFO['grps'] = array('FIXME');
        $_SERVER['REMOTE_USER'] = $user;
        $_SESSION[DOKU_COOKIE]['auth']['user'] = $user;
        $_SESSION[DOKU_COOKIE]['auth']['pass'] = $pass;
        $_SESSION[DOKU_COOKIE]['auth']['info'] = $USERINFO;
        return true;

        */
    }

    /**
     * Check user+password [ MUST BE OVERRIDDEN ]
     *
     * Checks if the given user exists and the given
     * plaintext password is correct
     *
     * May be ommited if trustExternal is used.
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @param   string $user the user name
     * @param   string $pass the clear text password
     * @return  bool
     */
    public function checkPass($user, $pass)
    {
        msg("no valid authorisation system in use", -1);
        return false;
    }

    /**
     * Return user info [ MUST BE OVERRIDDEN ]
     *
     * Returns info about the given user needs to contain
     * at least these fields:
     *
     * name string  full name of the user
     * mail string  email address of the user
     * grps array   list of groups the user is in
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @param   string $user the user name
     * @param   bool $requireGroups whether or not the returned data must include groups
     * @return  false|array containing user data or false
     */
    public function getUserData($user, $requireGroups = true)
    {
        if (!$this->cando['external']) msg("no valid authorisation system in use", -1);
        return false;
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
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @param  string $user
     * @param  string $pass
     * @param  string $name
     * @param  string $mail
     * @param  null|array $grps
     * @return bool|null
     */
    public function createUser($user, $pass, $name, $mail, $grps = null)
    {
        msg("authorisation method does not allow creation of new users", -1);
        return null;
    }

    /**
     * Modify user data [implement only where required/possible]
     *
     * Set the mod* capabilities according to the implemented features
     *
     * @author  Chris Smith <chris@jalakai.co.uk>
     * @param   string $user nick of the user to be changed
     * @param   array $changes array of field/value pairs to be changed (password will be clear text)
     * @return  bool
     */
    public function modifyUser($user, $changes)
    {
        msg("authorisation method does not allow modifying of user data", -1);
        return false;
    }

    /**
     * Delete one or more users [implement only where required/possible]
     *
     * Set delUser capability when implemented
     *
     * @author  Chris Smith <chris@jalakai.co.uk>
     * @param   array $users
     * @return  int    number of users deleted
     */
    public function deleteUsers($users)
    {
        msg("authorisation method does not allow deleting of users", -1);
        return 0;
    }

    /**
     * Return a count of the number of user which meet $filter criteria
     * [should be implemented whenever retrieveUsers is implemented]
     *
     * Set getUserCount capability when implemented
     *
     * @author Chris Smith <chris@jalakai.co.uk>
     * @param  array $filter array of field/pattern pairs, empty array for no filter
     * @return int
     */
    public function getUserCount($filter = [])
    {
        msg("authorisation method does not provide user counts", -1);
        return 0;
    }

    /**
     * Bulk retrieval of user data [implement only where required/possible]
     *
     * Set getUsers capability when implemented
     *
     * @author  Chris Smith <chris@jalakai.co.uk>
     * @param   int $start index of first user to be returned
     * @param   int $limit max number of users to be returned, 0 for unlimited
     * @param   array $filter array of field/pattern pairs, null for no filter
     * @return  array list of userinfo (refer getUserData for internal userinfo details)
     */
    public function retrieveUsers($start = 0, $limit = 0, $filter = null)
    {
        msg("authorisation method does not support mass retrieval of user data", -1);
        return [];
    }

    /**
     * Define a group [implement only where required/possible]
     *
     * Set addGroup capability when implemented
     *
     * @author  Chris Smith <chris@jalakai.co.uk>
     * @param   string $group
     * @return  bool
     */
    public function addGroup($group)
    {
        msg("authorisation method does not support independent group creation", -1);
        return false;
    }

    /**
     * Retrieve groups [implement only where required/possible]
     *
     * Set getGroups capability when implemented
     *
     * @author  Chris Smith <chris@jalakai.co.uk>
     * @param   int $start
     * @param   int $limit
     * @return  array
     */
    public function retrieveGroups($start = 0, $limit = 0)
    {
        msg("authorisation method does not support group list retrieval", -1);
        return [];
    }

    /**
     * Return case sensitivity of the backend [OPTIONAL]
     *
     * When your backend is caseinsensitive (eg. you can login with USER and
     * user) then you need to overwrite this method and return false
     *
     * @return bool
     */
    public function isCaseSensitive()
    {
        return true;
    }

    /**
     * Sanitize a given username [OPTIONAL]
     *
     * This function is applied to any user name that is given to
     * the backend and should also be applied to any user name within
     * the backend before returning it somewhere.
     *
     * This should be used to enforce username restrictions.
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @param string $user username
     * @return string the cleaned username
     */
    public function cleanUser($user)
    {
        return $user;
    }

    /**
     * Sanitize a given groupname [OPTIONAL]
     *
     * This function is applied to any groupname that is given to
     * the backend and should also be applied to any groupname within
     * the backend before returning it somewhere.
     *
     * This should be used to enforce groupname restrictions.
     *
     * Groupnames are to be passed without a leading '@' here.
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @param  string $group groupname
     * @return string the cleaned groupname
     */
    public function cleanGroup($group)
    {
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
     * @author Andreas Gohr <andi@splitbrain.org>
     * @return bool
     */
    public function useSessionCache($user)
    {
        global $conf;
        return ($_SESSION[DOKU_COOKIE]['auth']['time'] >= @filemtime($conf['cachedir'] . '/sessionpurge'));
    }
}
