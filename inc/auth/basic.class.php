<?php
/**
 * auth/basic.class.php
 *
 * foundation authorisation class
 * all auth classes should inherit from this class
 *
 * @author    Chris Smith <chris@jalakai.co.uk>
 */

class auth_basic {

  var $success = true;


  /**
   * Posible things an auth backend module may be able to
   * do. The things a backend can do need to be set to true
   * in the constructor.
   */
  var $cando = array (
    'addUser'     => false, // can Users be created?
    'delUser'     => false, // can Users be deleted?
    'modLogin'    => false, // can login names be changed?
    'modPass'     => false, // can passwords be changed?
    'modName'     => false, // can real names be changed?
    'modMail'     => false, // can emails be changed?
    'modGroups'   => false, // can groups be changed?
    'getUsers'    => false, // can a (filtered) list of users be retrieved?
    'getUserCount'=> false, // can the number of users be retrieved?
    'getGroups'   => false, // can a list of available groups be retrieved?
    'external'    => false, // does the module do external auth checking?
    'logout'      => true,  // can the user logout again? (eg. not possible with HTTP auth)
  );


  /**
   * Constructor.
   *
   * Carry out sanity checks to ensure the object is
   * able to operate. Set capabilities in $this->cando
   * array here
   *
   * Set $this->success to false if checks fail
   *
   * @author  Christopher Smith <chris@jalakai.co.uk>
   */
  function auth_basic() {
     // the base class constructor does nothing, derived class
    // constructors do the real work
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
   * @return  bool
   */
  function canDo($cap) {
    switch($cap){
      case 'Profile':
        // can at least one of the user's properties be changed?
        return ( $this->cando['modPass']  ||
                 $this->cando['modName']  ||
                 $this->cando['modMail'] );
        break;
      case 'UserMod':
        // can at least anything be changed?
        return ( $this->cando['modPass']   ||
                 $this->cando['modName']   ||
                 $this->cando['modMail']   ||
                 $this->cando['modLogin']  ||
                 $this->cando['modGroups'] ||
                 $this->cando['modMail'] );
        break;
      default:
        // print a helping message for developers
        if(!isset($this->cando[$cap])){
          msg("Check for unknown capability '$cap' - Do you use an outdated Plugin?",-1);
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
   * @param array $params Parameters for the createUser, modifyUser or deleteUsers method. The content of this array depends on the modification type
   * @return mixed Result from the modification function or false if an event handler has canceled the action
   */
  function triggerUserMod($type, $params)
  {
    $validTypes = array(
      'create' => 'createUser',
      'modify' => 'modifyUser',
      'delete' => 'deleteUsers'
    );
    if(empty($validTypes[$type]))
      return false;
    $eventdata = array('type' => $type, 'params' => $params, 'modification_result' => null);
    $evt = new Doku_Event('AUTH_USER_CHANGE', $eventdata);
    if ($evt->advise_before(true)) {
      $result = call_user_func_array(array($this, $validTypes[$type]), $params);
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
  function logOff(){
  }

  /**
   * Do all authentication [ OPTIONAL ]
   *
   * Set $this->cando['external'] = true when implemented
   *
   * If this function is implemented it will be used to
   * authenticate a user - all other DokuWiki internals
   * will not be used for authenticating, thus
   * implementing the checkPass() function is not needed
   * anymore.
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
   * @see auth_login()
   * @author  Andreas Gohr <andi@splitbrain.org>
   *
   * @param   string  $user    Username
   * @param   string  $pass    Cleartext Password
   * @param   bool    $sticky  Cookie should not expire
   * @return  bool             true on successful auth
   */
  function trustExternal($user,$pass,$sticky=false){
#    // some example:
#
#    global $USERINFO;
#    global $conf;
#    $sticky ? $sticky = true : $sticky = false; //sanity check
#
#    // do the checking here
#
#    // set the globals if authed
#    $USERINFO['name'] = 'FIXME';
#    $USERINFO['mail'] = 'FIXME';
#    $USERINFO['grps'] = array('FIXME');
#    $_SERVER['REMOTE_USER'] = $user;
#    $_SESSION[DOKU_COOKIE]['auth']['user'] = $user;
#    $_SESSION[DOKU_COOKIE]['auth']['pass'] = $pass;
#    $_SESSION[DOKU_COOKIE]['auth']['info'] = $USERINFO;
#    return true;
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
   * @return  bool
   */
  function checkPass($user,$pass){
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
   * mail string  email addres of the user
   * grps array   list of groups the user is in
   *
   * @author  Andreas Gohr <andi@splitbrain.org>
   * @return  array containing user data or false
   */
  function getUserData($user) {
    if(!$this->cando['external']) msg("no valid authorisation system in use", -1);
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
   */
  function createUser($user,$pass,$name,$mail,$grps=null){
    msg("authorisation method does not allow creation of new users", -1);
    return null;
  }

  /**
   * Modify user data [implement only where required/possible]
   *
   * Set the mod* capabilities according to the implemented features
   *
   * @author  Chris Smith <chris@jalakai.co.uk>
   * @param   $user      nick of the user to be changed
   * @param   $changes   array of field/value pairs to be changed (password will be clear text)
   * @return  bool
   */
  function modifyUser($user, $changes) {
    msg("authorisation method does not allow modifying of user data", -1);
    return false;
  }

  /**
   * Delete one or more users [implement only where required/possible]
   *
   * Set delUser capability when implemented
   *
   * @author  Chris Smith <chris@jalakai.co.uk>
   * @param   array  $users
   * @return  int    number of users deleted
   */
  function deleteUsers($users) {
    msg("authorisation method does not allow deleting of users", -1);
    return false;
  }

  /**
   * Return a count of the number of user which meet $filter criteria
   * [should be implemented whenever retrieveUsers is implemented]
   *
   * Set getUserCount capability when implemented
   *
   * @author  Chris Smith <chris@jalakai.co.uk>
   */
  function getUserCount($filter=array()) {
    msg("authorisation method does not provide user counts", -1);
    return 0;
  }

  /**
   * Bulk retrieval of user data [implement only where required/possible]
   *
   * Set getUsers capability when implemented
   *
   * @author  Chris Smith <chris@jalakai.co.uk>
   * @param   start     index of first user to be returned
   * @param   limit     max number of users to be returned
   * @param   filter    array of field/pattern pairs, null for no filter
   * @return  array of userinfo (refer getUserData for internal userinfo details)
   */
  function retrieveUsers($start=0,$limit=-1,$filter=null) {
    msg("authorisation method does not support mass retrieval of user data", -1);
    return array();
  }

  /**
   * Define a group [implement only where required/possible]
   *
   * Set addGroup capability when implemented
   *
   * @author  Chris Smith <chris@jalakai.co.uk>
   * @return  bool
   */
  function addGroup($group) {
    msg("authorisation method does not support independent group creation", -1);
    return false;
  }

  /**
   * Retrieve groups [implement only where required/possible]
   *
   * Set getGroups capability when implemented
   *
   * @author  Chris Smith <chris@jalakai.co.uk>
   * @return  array
   */
  function retrieveGroups($start=0,$limit=0) {
    msg("authorisation method does not support group list retrieval", -1);
    return array();
  }

  /**
   * Return case sensitivity of the backend [OPTIONAL]
   *
   * When your backend is caseinsensitive (eg. you can login with USER and
   * user) then you need to overwrite this method and return false
   */
  function isCaseSensitive(){
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
   * @param string $user - username
   * @param string - the cleaned username
   */
  function cleanUser($user){
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
   * @param string $group - groupname
   * @param string - the cleaned groupname
   */
  function cleanGroup($group){
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
  function useSessionCache($user){
    global $conf;
    return ($_SESSION[DOKU_COOKIE]['auth']['time'] >= @filemtime($conf['cachedir'].'/sessionpurge'));
  }

}
//Setup VIM: ex: et ts=2 :
