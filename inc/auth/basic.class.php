<?php
/**
 * auth/basic.class.php
 *
 * foundation authorisation class 
 * all auth classes should inherit from this class
 *
 * @author    Chris Smith <chris@jalakaic.co.uk>
 */
 
class auth_basic {

	/**
	 * Check user+password [ MUST BE OVERRIDDEN ]
	 *
	 * Checks if the given user exists and the given
	 * plaintext password is correct
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

      msg("no valid authorisation system in use", -1);
      return false;
	}
	
	/**
	 * Create a new User [implement only where required/possible]
	 *
	 * Returns false if the user already exists, null when an error
	 * occured and the cleartext password of the new user if
	 * everything went well.
	 * 
	 * The new user HAS TO be added to the default group by this
	 * function!
	 *
	 * @author  Andreas Gohr <andi@splitbrain.org>
	 */
#	function createUser($user,$pass,$name,$mail,$grps=null){
#		
#	  msg("authorisation method does not allow creation of new users", -1);
#	  return null;
#	}
	
	/**
	 * Modify user data [implement only where required/possible]
	 *
	 * @author  Chris Smith <chris@jalakai.co.uk>
	 * @param   $user      nick of the user to be changed
	 * @param   $changes   array of field/value pairs to be changed (password will be clear text)
	 * @return  bool
	 */
#	function modifyUser($user, $changes) {
#	  msg("authorisation method does not allow modifying of user data", -1);
#	  return false;
#	}
	
	/**
	 * Delete one or more users [implement only where required/possible]
	 *
	 * @author  Chris Smith <chris@jalakai.co.uk>
	 * @param   array  $users
	 * @return  int    number of users deleted
	 */
#	function deleteUsers($users) {
#	  msg("authorisation method does not allow deleting of users", -1);
#	  return false;
#	}

	/**
	 * Return a count of the number of user which meet $filter criteria
	 * [should be implemented whenever retrieveUsers is implemented]
	 *
	 * @author  Chris Smith <chris@jalakai.co.uk>
	 */
#	function getUserCount($filter=array()) {
#	
#	  msg("authorisation method does not provide user counts", -1);
#	  return 0;
#	}
	
	/**
	 * Bulk retrieval of user data [implement only where required/possible]
	 *
	 * @author  Chris Smith <chris@jalakai.co.uk>
	 * @param   start     index of first user to be returned
	 * @param   limit     max number of users to be returned
	 * @param   filter    array of field/pattern pairs, null for no filter
	 * @return  array of userinfo (refer getUserData for internal userinfo details)
	 */
#	function retrieveUsers($start=0,$limit=-1,$filter=null) {
#	  msg("authorisation method does not support mass retrieval of user data", -1);
#	  return array();
#	}
	
	/**
	 * Define a group [implement only where required/possible]
	 * 
	 * @author  Chris Smith <chris@jalakai.co.uk>
	 * @return  bool
	 */
#	function addGroup($group) {
#	  msg("authorisation method does not support independent group creation", -1);
#	  return false;
#	}

	/**
	 * Retrieve groups [implement only where required/possible]
	 * 
	 * @author  Chris Smith <chris@jalakai.co.uk>
	 * @return  array
	 */
#	function retrieveGroups($start=0,$limit=0) {
#	  msg("authorisation method does not support group list retrieval", -1);
#	  return array();
#	}

	/**
	 * Give user membership of a group [implement only where required/possible]
	 * 
	 * @author  Chris Smith <chris@jalakai.co.uk>
	 * @return  bool
	 */
#	function joinGroup($user, $group) {
#	  msg("authorisation method does not support alteration of group memberships", -1);
#	  return false;
#	}

	/**
	 * Remove user from a group [implement only where required/possible]
	 * 
	 * @author  Chris Smith <chris@jalakai.co.uk>
	 * @return  bool
	 */
#	function leaveGroup($user, $group) {
#	  msg("authorisation method does not support alteration of group memberships", -1);
#	  return false;
#	}
}