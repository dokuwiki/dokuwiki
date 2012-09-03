<?php

/**
 * ACL Action plugin, hooks in to the acl check hook.
 */

if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define( 'DOKU_PLUGIN', DOKU_INC.'lib/plugins/' );
require_once( DOKU_PLUGIN.'action.php' );
require_once( DOKU_INC.'inc/auth.php' );

class action_plugin_acl extends DokuWiki_Action_Plugin {

	function __construct() {
		global $AUTH_ACL;
		
		// load ACL into a global array
		// TODO: Global arrays isn't nessecary correct within a plugin, fixme?
		$AUTH_ACL = $this->auth_loadACL();
	}

	function register( &$controller ) {
		$controller->register_hook( 'AUTH_ACL_CHECK', 'BEFORE', $this, 'aclcheck' );
	}
	
	function aclcheck( $event, $param ) {
		global $conf;
		global $AUTH_ACL;
		global $auth;
		
		list( $id,$user,$groups ) = $event->data;
		
		$ci = '';
		if(!$auth->isCaseSensitive()) $ci = 'ui';

		$user = $auth->cleanUser($user);
		$groups = array_map(array($auth,'cleanGroup'),(array)$groups);
		$user = auth_nameencode($user);

		//prepend groups with @ and nameencode
		$cnt = count($groups);
		for($i=0; $i<$cnt; $i++){
			$groups[$i] = '@'.auth_nameencode($groups[$i]);
		}

		$ns	= getNS($id);
		$perm  = -1;

		if($user || count($groups)){
			//add ALL group
			$groups[] = '@ALL';
			//add User
			if($user) $groups[] = $user;
			//build regexp
			$regexp   = join('|',$groups);
		}else{
			$regexp = '@ALL';
		}

		//check exact match first
		$matches = preg_grep('/^'.preg_quote($id,'/').'\s+('.$regexp.')\s+/'.$ci,$AUTH_ACL);
		if(count($matches)){
			foreach($matches as $match){
				$match = preg_replace('/#.*$/','',$match); //ignore comments
				$acl   = preg_split('/\s+/',$match);
				if($acl[2] > AUTH_DELETE) $acl[2] = AUTH_DELETE; //no admins in the ACL!
				if($acl[2] > $perm){
					$perm = $acl[2];
				}
			}
			if($perm > -1){
				//we had a match - return it
				$event->preventDefault();
				$event->result = $perm;
				return;
			}
		}

		//still here? do the namespace checks
		if($ns){
			$path = $ns.':*';
		}else{
			$path = '*'; //root document
		}

		do{
			$matches = preg_grep('/^'.preg_quote($path,'/').'\s+('.$regexp.')\s+/'.$ci,$AUTH_ACL);
			if(count($matches)){
				foreach($matches as $match){
					$match = preg_replace('/#.*$/','',$match); //ignore comments
					$acl   = preg_split('/\s+/',$match);
					if($acl[2] > AUTH_DELETE) $acl[2] = AUTH_DELETE; //no admins in the ACL!
					if($acl[2] > $perm){
						$perm = $acl[2];
					}
				}
				//we had a match - return it
				$event->preventDefault();
				$event->result = $perm;
				return;
			}

			//get next higher namespace
			$ns   = getNS($ns);

			if($path != '*'){
				$path = $ns.':*';
				if($path == ':*') $path = '*';
			}else{
				//we did this already
				//looks like there is something wrong with the ACL
				//break here
				msg('No ACL setup yet, but ACL plugin loaded, using default handler.');
				return;
			}
		}while(1); //this should never loop endless
		
		/* Unknown... just pass to default handler... */
	}
	
	/**
	 * Loads the ACL setup and handle user wildcards
	 *
	 * @author Andreas Gohr <andi@splitbrain.org>
	 * @returns array
	 */
	function auth_loadACL(){
		global $config_cascade;

		if(!is_readable($config_cascade['acl']['default'])) return array();

		$acl = file($config_cascade['acl']['default']);

		//support user wildcard
		if(isset($_SERVER['REMOTE_USER'])){
			$len = count($acl);
			for($i=0; $i<$len; $i++){
				if($acl[$i]{0} == '#') continue;
				list($id,$rest) = preg_split('/\s+/',$acl[$i],2);
				$id   = str_replace('%USER%',cleanID($_SERVER['REMOTE_USER']),$id);
				$rest = str_replace('%USER%',auth_nameencode($_SERVER['REMOTE_USER']),$rest);
				$acl[$i] = "$id\t$rest";
			}
		}
		return $acl;
	}
}
