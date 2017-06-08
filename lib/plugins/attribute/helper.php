<?php
/**
 * DokuWiki Plugin attribute (Helper Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Mike Wilmes <mwilmes@avc.edu>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class helper_plugin_attribute extends DokuWiki_Plugin {	
	public $success = false;
	protected $storepath = null;
	protected $cache = null;
	
	public function __construct() {		
		$this->loadConfig(); 		
		// Create the path used for attribute data.
		$path = substr($this->conf['store'],0,1) == '/' ? $this->conf['store'] : DOKU_INC.$this->conf['store'];
		$this->storepath = ($this->conf['store'] === '' || !is_dir($path)) ? null : $path;
        // A directory is needed.
        if(is_null($this->storepath)) {            
			msg("Attribute: Configuration item 'store' is not set to a writeable directory.", -1);            
            return;
        }
		$this->success = true;
		// Create a memory cache for this execution.		
		$this->cache = array();
	}
	
	/**
	 * return some info
	 */
	public function getInfo(){
		return array(
            'author' => 'Mike Wilmes',
            'email'  => 'mwilmes@avc.edu',
            'date'   => '2015-09-03',
            'name'   => 'Attribute Plugin',
            'desc'   => 'Arbitrary attribute definition and storage for user associated data.',
            'url'    => 'None for now, hoping for http://www.dokuwiki.org/plugin:attribute',
		);
	}

    /**
     * Return info about supported methods in this Helper Plugin
     *
     * @return array of public methods
     */
    public function getMethods() {
        $result = array();
        $result[] = array(
                'name'   => 'enumerateAttributes',
                'desc'   => "Generates a list of known attributes in the specified namespace for a user.  If user is present, must be an admin, otherwise defaults to currently logged in user.",
				'parameters' => array(
					'namespace' => 'string',
					'user' => 'string (optional)',
					),
				'return' => array('attributes'=>'array'), // returns false on error.
                );
        $result[] = array(
                'name'   => 'enumerateUsers',
                'desc'   => "Generates a list of users that have assigned attributes in the specified namespace.",
				'parameters' => array(
					'namespace' => 'string',
					),
				'return' => array('users'=>'array'), // returns false on error.
                );
        $result[] = array(
                'name'   => 'set',
                'desc'   => "Set the value of an attribute in a specified namespace. Returns boolean success (false if something went wrong). If user is present, must be an admin, otherwise defaults to currently logged in user.",
				'parameters' => array(
					'namespace' => 'string',
					'attribute' => 'string',
					'value' => 'mixed (serializable)',
					'user' => 'string (optional)',
					),
				'return' => array('success'=>'boolean'),
                );
        $result[] = array(
                'name'   => 'exists',
                'desc'   => "Checks if an attribute exists for a user in a given namespace. If user is present, must be an admin, otherwise defaults to currently logged in user.",
				'parameters' => array(
					'namespace' => 'string',
					'attribute' => 'string',					
					'user' => 'string (optional)',
					),
				'return' => array('exists'=>'boolean'),
                );
        $result[] = array(
                'name'   => 'del',
                'desc'   => "Deletes attribute data in a specified namespace by its name. If user is present, must be an admin, otherwise defaults to currently logged in user.",
				'parameters' => array(
					'namespace' => 'string',
					'attribute' => 'string',
					'user' => 'string (optional)',
					),
				'return' => array('success'=>'boolean'),
                );
        $result[] = array(
                'name'   => 'get',
                'desc'   => "Retrieves a value for an attribute in a specified namespace. Returns retrieved value or null. \$success out-parameter can be checked to check success (you may have false, null, 0, or '' as stored value). If user is present, must be an admin, otherwise defaults to currently logged in user.",
				'parameters' => array(
					'namespace' => 'string',
					'attribute' => 'string',
					'success' => 'boolean (out)',
					'user' => 'string (optional)',
					),
				'return' => array('value'=>'mixed'), // returns false on error.
                );
        $result[] = array(
                'name'   => 'purge',
                'desc'   => "Deletes all attribute data for a specified namespace for a user. Only useable by an admin.",
				'parameters' => array(
					'namespace' => 'string',
					'user' => 'string',
					),
				'return' => array('success'=>'boolean'),
                );
        return $result;
    }

	public function enumerateAttributes($namespace, $user = null){
		// Verify that this plugin is functional.
		if (!$this->success) { return false; }
		// Identify the user whose attributes will be accessed.		
		$user = $this->validateUser($user);
		// If $user is now null, then the user is not logged in.
		if ($user === null) { return false; }
		// Obtain the lock to ensure consistency.
		$lock = $this->getLock();
		// If we did not get the lock, then return failure. 
		// This should never happen.
		if (!$lock) { return false; }
		// Get the attributes.
		$data = $this->loadAttributes($namespace, $user);
		// Release the lock.
		$this->releaseLock($lock);
		// If there is no data, return false;
		if ($data === null) { return false; }
		// Return just the keys.
		return array_keys($data);
	}

	public function enumerateUsers($namespace){
		// Verify that this plugin is functional.
		if (!$this->success) { return false; }
		// Obtain the lock to ensure consistency.
		$lock = $this->getLock();
		// If we did not get the lock, then return failure. 
		// This should never happen.
		if (!$lock) { return false; }
		// Collect the list of attribute files-
		// the files are named with the username.
		$listing = scandir($this->storepath, SCANDIR_SORT_DESCENDING);
		// Release the lock.
		$this->releaseLock($lock);
		// Strip out ., .., and .flock filenames
		// Generate a partial file key.
		$key = rawurlencode($namespace) . '.';
		// Filter files to the desired namespace.
		$files = array_filter($listing, function ($x) use ($key) { return substr($x, 0, strlen($key)) == $key; });
		// Strip the namespace from the files to identify the users.
		$users = array_map(function ($x)  use ($key) { return substr($x, strlen($key)); }, $files);
		// Return the list of users.
		return $users;
	}

	public function set($namespace, $attribute, $value, $user = null){
		// Verify that this plugin is functional.
		if (!$this->success) { return false; }
		// Identify the user whose attributes will be accessed.		
		$user = $this->validateUser($user);
		// If $user is now null, then the user is not logged in.
		if ($user === null) { return false; }
		// Obtain the lock to ensure consistency.
		$lock = $this->getLock();
		// If we did not get the lock, then return failure. 
		// This should never happen.
		if (!$lock) { return false; }
		// Get this user's attributes.
		$data = $this->loadAttributes($namespace, $user);
		// If there was an error getting the attributes, then fail.
		$result == false; 
		if ($data !== null) { // Otherwise commit the change.
			// Set the data in the array.
			$data[$attribute] = $value;
			// Store the changed data.
			$result = $this->saveAttributes($namespace, $user, $data);
		}
		// Release the lock.
		$this->releaseLock($lock);
		// Return the result of saving the data.
		return $result;
	}
	
	public function exists($namespace, $attribute, $user = null){
		// Verify that this plugin is functional.
		if (!$this->success) { return false; }
		// Identify the user whose attributes will be accessed.		
		$user = $this->validateUser($user);
		// If $user is now null, then the user is not logged in.
		if ($user === null) { return false; }
		// Obtain the lock to ensure consistency.
		$lock = $this->getLock();
		// If we did not get the lock, then return failure. 
		// This should never happen.
		if (!$lock) { return false; }
		// Get this user's attributes.
		$data = $this->loadAttributes($namespace, $user);
		// Release the lock.
		$this->releaseLock($lock);
		// If there was an error getting the attributes, then fail.
		if (!is_array($data)) { return false; }
		// Return the presence of the attribute.
		return array_key_exists($attribute, $data);				
	}
	
	public function del($namespace, $attribute, $user = null){
		// Verify that this plugin is functional.
		if (!$this->success) { return false; }
		// Identify the user whose attributes will be accessed.		
		$user = $this->validateUser($user);
		// If $user is now null, then the user is not logged in.
		if ($user === null) { return false; }
		// Obtain the lock to ensure consistency.
		$lock = $this->getLock();
		// If we did not get the lock, then return failure. 
		// This should never happen.
		if (!$lock) { return false; }
		// Get this user's attributes.
		$data = $this->loadAttributes($namespace, $user);
		// If there was an error getting the attributes, then fail.
		if ($data !== null) { 
			// Special case- if the attribute already does not exist, then
			// return true. We are at the desired state.
			if (array_key_exists($attribute, $data)) { 
				// Unset the attribute being deleted.
				unset($data[$attribute]);
				// Commit these changes.
				$result = $this->saveAttributes($namespace, $user, $data);
			}
			else {
				// The key is already not present, 
				// so we have reached the desired outcome of this operation.
				$result = true;
			}
		}
		else {
			$result = false;
		}
		// Release the lock.
		$this->releaseLock($lock);
		// Return the result of this operation.
		return $result; 		
	}	
	
	public function purge($namespace, $user){
		// Verify that this plugin is functional.
		if (!$this->success) { return false; }
		// Ensure this user is an admin.
		global $INFO;
		if (!$INFO['isadmin']) { return false; }
		// Obtain the lock to ensure consistency.
		$lock = $this->getLock();
		// If we did not get the lock, then return failure. 
		// This should never happen.
		if (!$lock) { return false; }
		// Generate the key.
		$key = rawurlencode($namespace) . '.' . rawurlencode($user);
		// Generate the file path.		
		$filename = $this->storepath . "/" . $key;
		// If the file exists, unlink it.
		if (file_exists($filename)) { 
			$result = unlink($filename); 
		}
		else {
			// If the file does not exist, the desired end state has been 
			// reached.
			$result = true;
		}
		// Release the lock.
		$this->releaseLock($lock);
		// Return the result of this operation.
		return $result; 		
	}	
	
	public function get($namespace, $attribute, &$success = false, $user = null){
		// Prepare the supplied success flag as false.  It will be changed to
		// true on success.
		$success = false;
		// Verify that this plugin is functional.
		if (!$this->success) { return false; }
		// Identify the user whose attributes will be accessed.		
		$user = $this->validateUser($user);
		// If $user is now null, then the user is not logged in.
		if ($user === null) { return false; }
		// Obtain the lock to ensure consistency.
		$lock = $this->getLock();
		// If we did not get the lock, then return failure. 
		// This should never happen.
		if (!$lock) { return false; }
		// Get this user's attributes.
		$data = $this->loadAttributes($namespace, $user);
		// Release the lock.
		$this->releaseLock($lock);
		// If there was an error getting the attributes, then fail.
		if ($data === null) { return false; }
		// If the attribute does not exist, then return with failure.
		if (!array_key_exists($attribute, $data)) { return false; }
		// The attribute exists. Return success and the attribute value.
		$success = true;
		return $data[$attribute];
	}

	/* validateUser - validate that the user may access another user's attribute.
	 * If the user is an admin and another user name is supplied, that value is 
	 * returned. Otherwise the name of the logged in user is supplied. If no
	 * user is logged in, null is returned.
	 */
	private function validateUser($user) {
		// We need a special circumstance.  If a user is not logged in, but we 
		// are performing a login, enable access to the attributes of the user
		// being logged in IF DIRECTLY SPECIFIED.
		global $INFO, $ACT, $USERINFO, $INPUT;
		if ($ACT=='login' && !$USERINFO && $user == $INPUT->str('u')) return $user;
		// This does not meet the special circumstance listed above.  
		// Perform rights validation.		
		// If no one is logged in, then return null.
		if ($_SERVER['REMOTE_USER'] == '') { return null; }
		// If the user is not an admin, no user is specified, or the 
		// named user is not the logged in user, then return the currently 
		// logged in user.
		if (!$user || ($user !== $_SERVER['REMOTE_USER'] && !$INFO['isadmin'])) { return $_SERVER['REMOTE_USER']; }
		return $user;
	}
	
	/* loadAttributes - load all attribute data for a user.
	 * This loads all user attribute data from file.  A copy is stored in 
	 * memory to alleviate repeated file accesses.
	 */
	private function loadAttributes($namespace, $user){
		// Generate the key.
		$key = rawurlencode($namespace) . '.' . rawurlencode($user);
		// Generate the file path.		
		$filename = $this->storepath . "/" . $key;
		// If the file does not exist, then return an empty attribute array.
		if (!is_file($filename)) { return array(); }
		// See if this value is already in cache.
		if (array_key_exists($filename, $this->cache)) {
			// Return the cached value.
			return $this->cache[$filename];
		}
		// Get the munged contents of the file.
		$munge = file_get_contents($filename);
		// One last annoying thing to do - unmunge the data.
		$packet = $this->mungeData($munge, $key);
		// Set reasonable defaults for compressed.
		$compressed = false; // This prevents decomressing garbage.
		// Separate the compression flag from the actual data.
		list($compressed, $serial) = @unserialize($packet);
		// Decompress the data if needed.
		if ($compressed) {
			$serial = gzuncompress($serial);
		}
		// Set the return variable to a useful default, in case unserialize fails.
		$data = array();
		// Unserialize the attributes array.		
		$data = @unserialize($serial);
		// Cache this just in case.
		$this->cache[$filename] = $data;
		// Return the attribute data.
		return $data;
	}
	
	private function saveAttributes($namespace, $user, $data){
		// Generate the key.
		$key = rawurlencode($namespace) . '.' . rawurlencode($user);
		// Generate the file path.		
		$filename = $this->storepath . "/" . $key;
		// Update the cache.
		$this->cache[$filename] = $data;
		// Commit to file.
		$serial = serialize($data);		
		// Compress the data if needed.
		$compressed = $this->conf['compress'] === 1;
		if ($compressed) {
			$serial = gzcompress($serial);
		}
		// Serialize the data with the compression flag.
		$packet = serialize(array($compressed, $serial));
		// One last annoying thing to do - munge the data.
		$munge = $this->mungeData($packet, $key);
		// Store the data into a file and return the result of that operation.
		return (boolean)file_put_contents($filename, $munge);
	}
	
	private function getLock() {
		// Define the lock file path.
		$filename = $this->storepath . '/.flock';
		// Open the lock file.
		$handle = fopen($filename, 'w');
		// If the handle could not be opened, then return failure.
		if ($handle === false) { return false; }
		// Lock the lock file for exclusive access.  This should block until 
		// the file is available for locking.
		$state = flock($handle, LOCK_EX);
		// If failed, close the handle and return failure.
		if (!$state) { 
			fclose($filename);
			return false;
		}
		// Return the handle so it can be unlocked later.
		return $handle;
	}
	
	private function releaseLock($handle) {		
		// Release the lock so another thread can open it.
		flock($handle, LOCK_UN);
		// Close the lock file.
		fclose($handle);
	}
	
	private function mungeData($data, $key) {
		// This is to keep an admin from just renaming an attribute
		// file to change attributes... Not very secure but easy to do.
		// It XORs the the key repeatedly into the data.
		// Running this function with the same key will unmunge the data.
		$keylen = strlen($key);
		for ($i = 0; $i < strlen($data); ++$i) {
			$data[$i] = chr(ord($data[$i]) ^ ord($key[$i % $keylen]));
		}		
		return $data;
	}
}

// vim:ts=4:sw=4:et:
