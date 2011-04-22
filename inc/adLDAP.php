<?php
/**
 * PHP LDAP CLASS FOR MANIPULATING ACTIVE DIRECTORY
 * Version 3.3.2
 *
 * PHP Version 5 with SSL and LDAP support
 *
 * Written by Scott Barnett, Richard Hyland
 *   email: scott@wiggumworld.com, adldap@richardhyland.com
 *   http://adldap.sourceforge.net/
 *
 * Copyright (c) 2006-2010 Scott Barnett, Richard Hyland
 *
 * We'd appreciate any improvements or additions to be submitted back
 * to benefit the entire community :)
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @category ToolsAndUtilities
 * @package adLDAP
 * @author Scott Barnett, Richard Hyland
 * @copyright (c) 2006-2010 Scott Barnett, Richard Hyland
 * @license http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPLv2.1
 * @revision $Revision: 91 $
 * @version 3.3.2
 * @link http://adldap.sourceforge.net/
 */

/**
 * Define the different types of account in AD
 */
define ('ADLDAP_NORMAL_ACCOUNT', 805306368);
define ('ADLDAP_WORKSTATION_TRUST', 805306369);
define ('ADLDAP_INTERDOMAIN_TRUST', 805306370);
define ('ADLDAP_SECURITY_GLOBAL_GROUP', 268435456);
define ('ADLDAP_DISTRIBUTION_GROUP', 268435457);
define ('ADLDAP_SECURITY_LOCAL_GROUP', 536870912);
define ('ADLDAP_DISTRIBUTION_LOCAL_GROUP', 536870913);
define ('ADLDAP_FOLDER', 'OU');
define ('ADLDAP_CONTAINER', 'CN');

/**
* Main adLDAP class
*
* Can be initialised using $adldap = new adLDAP();
*
* Something to keep in mind is that Active Directory is a permissions
* based directory. If you bind as a domain user, you can't fetch as
* much information on other users as you could as a domain admin.
*
* Before asking questions, please read the Documentation at
* http://adldap.sourceforge.net/wiki/doku.php?id=api
*/
class adLDAP {
    /**
    * The account suffix for your domain, can be set when the class is invoked
    *
    * @var string
    */
    protected $_account_suffix = "@mydomain.local";

    /**
    * The base dn for your domain
    *
    * @var string
    */
    protected $_base_dn = "DC=mydomain,DC=local";

    /**
    * Array of domain controllers. Specifiy multiple controllers if you
    * would like the class to balance the LDAP queries amongst multiple servers
    *
    * @var array
    */
    protected $_domain_controllers = array ("dc01.mydomain.local");

    /**
    * Optional account with higher privileges for searching
    * This should be set to a domain admin account
    *
    * @var string
    * @var string
    */
    protected $_ad_username=NULL;
    protected $_ad_password=NULL;

    /**
    * AD does not return the primary group. http://support.microsoft.com/?kbid=321360
    * This tweak will resolve the real primary group.
    * Setting to false will fudge "Domain Users" and is much faster. Keep in mind though that if
    * someone's primary group is NOT domain users, this is obviously going to mess up the results
    *
    * @var bool
    */
    protected $_real_primarygroup=true;

    /**
    * Use SSL (LDAPS), your server needs to be setup, please see
    * http://adldap.sourceforge.net/wiki/doku.php?id=ldap_over_ssl
    *
    * @var bool
    */
    protected $_use_ssl=false;

    /**
    * Use TLS
    * If you wish to use TLS you should ensure that $_use_ssl is set to false and vice-versa
    *
    * @var bool
    */
    protected $_use_tls=false;

    /**
    * When querying group memberships, do it recursively
    * eg. User Fred is a member of Group A, which is a member of Group B, which is a member of Group C
    * user_ingroup("Fred","C") will returns true with this option turned on, false if turned off
    *
    * @var bool
    */
    protected $_recursive_groups=true;

    // You should not need to edit anything below this line
    //******************************************************************************************

    /**
    * Connection and bind default variables
    *
    * @var mixed
    * @var mixed
    */
    protected $_conn;
    protected $_bind;

    /**
    * Getters and Setters
    */

    /**
    * Set the account suffix
    *
    * @param string $_account_suffix
    * @return void
    */
    public function set_account_suffix($_account_suffix)
    {
          $this->_account_suffix = $_account_suffix;
    }

    /**
    * Get the account suffix
    *
    * @return string
    */
    public function get_account_suffix()
    {
          return $this->_account_suffix;
    }

    /**
    * Set the domain controllers array
    *
    * @param array $_domain_controllers
    * @return void
    */
    public function set_domain_controllers(array $_domain_controllers)
    {
          $this->_domain_controllers = $_domain_controllers;
    }

    /**
    * Get the list of domain controllers
    *
    * @return void
    */
    public function get_domain_controllers()
    {
          return $this->_domain_controllers;
    }

    /**
    * Set the username of an account with higher priviledges
    *
    * @param string $_ad_username
    * @return void
    */
    public function set_ad_username($_ad_username)
    {
          $this->_ad_username = $_ad_username;
    }

    /**
    * Get the username of the account with higher priviledges
    *
    * This will throw an exception for security reasons
    */
    public function get_ad_username()
    {
          throw new adLDAPException('For security reasons you cannot access the domain administrator account details');
    }

    /**
    * Set the password of an account with higher priviledges
    *
    * @param string $_ad_password
    * @return void
    */
    public function set_ad_password($_ad_password)
    {
          $this->_ad_password = $_ad_password;
    }

    /**
    * Get the password of the account with higher priviledges
    *
    * This will throw an exception for security reasons
    */
    public function get_ad_password()
    {
          throw new adLDAPException('For security reasons you cannot access the domain administrator account details');
    }

    /**
    * Set whether to detect the true primary group
    *
    * @param bool $_real_primary_group
    * @return void
    */
    public function set_real_primarygroup($_real_primarygroup)
    {
          $this->_real_primarygroup = $_real_primarygroup;
    }

    /**
    * Get the real primary group setting
    *
    * @return bool
    */
    public function get_real_primarygroup()
    {
          return $this->_real_primarygroup;
    }

    /**
    * Set whether to use SSL
    *
    * @param bool $_use_ssl
    * @return void
    */
    public function set_use_ssl($_use_ssl)
    {
          $this->_use_ssl = $_use_ssl;
    }

    /**
    * Get the SSL setting
    *
    * @return bool
    */
    public function get_use_ssl()
    {
          return $this->_use_ssl;
    }

    /**
    * Set whether to use TLS
    *
    * @param bool $_use_tls
    * @return void
    */
    public function set_use_tls($_use_tls)
    {
          $this->_use_tls = $_use_tls;
    }

    /**
    * Get the TLS setting
    *
    * @return bool
    */
    public function get_use_tls()
    {
          return $this->_use_tls;
    }

    /**
    * Set whether to lookup recursive groups
    *
    * @param bool $_recursive_groups
    * @return void
    */
    public function set_recursive_groups($_recursive_groups)
    {
          $this->_recursive_groups = $_recursive_groups;
    }

    /**
    * Get the recursive groups setting
    *
    * @return bool
    */
    public function get_recursive_groups()
    {
          return $this->_recursive_groups;
    }

    /**
    * Default Constructor
    *
    * Tries to bind to the AD domain over LDAP or LDAPs
    *
    * @param array $options Array of options to pass to the constructor
    * @throws Exception - if unable to bind to Domain Controller
    * @return bool
    */
    function __construct($options=array()){
        // You can specifically overide any of the default configuration options setup above
        if (count($options)>0){
            if (array_key_exists("account_suffix",$options)){ $this->_account_suffix=$options["account_suffix"]; }
            if (array_key_exists("base_dn",$options)){ $this->_base_dn=$options["base_dn"]; }
            if (array_key_exists("domain_controllers",$options)){ $this->_domain_controllers=$options["domain_controllers"]; }
            if (array_key_exists("ad_username",$options)){ $this->_ad_username=$options["ad_username"]; }
            if (array_key_exists("ad_password",$options)){ $this->_ad_password=$options["ad_password"]; }
            if (array_key_exists("real_primarygroup",$options)){ $this->_real_primarygroup=$options["real_primarygroup"]; }
            if (array_key_exists("use_ssl",$options)){ $this->_use_ssl=$options["use_ssl"]; }
            if (array_key_exists("use_tls",$options)){ $this->_use_tls=$options["use_tls"]; }
            if (array_key_exists("recursive_groups",$options)){ $this->_recursive_groups=$options["recursive_groups"]; }
        }

        if ($this->ldap_supported() === false) {
            throw new adLDAPException('No LDAP support for PHP.  See: http://www.php.net/ldap');
        }

        return $this->connect();
    }

    /**
    * Default Destructor
    *
    * Closes the LDAP connection
    *
    * @return void
    */
    function __destruct(){ $this->close(); }

    /**
    * Connects and Binds to the Domain Controller
    *
    * @return bool
    */
    public function connect() {
        // Connect to the AD/LDAP server as the username/password
        $dc=$this->random_controller();
        if ($this->_use_ssl){
            $this->_conn = ldap_connect("ldaps://".$dc, 636);
        } else {
            $this->_conn = ldap_connect($dc);
        }

        // Set some ldap options for talking to AD
        ldap_set_option($this->_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->_conn, LDAP_OPT_REFERRALS, 0);

        if ($this->_use_tls) {
            ldap_start_tls($this->_conn);
        }

        // Bind as a domain admin if they've set it up
        if ($this->_ad_username!=NULL && $this->_ad_password!=NULL){
            $this->_bind = @ldap_bind($this->_conn,$this->_ad_username.$this->_account_suffix,$this->_ad_password);
            if (!$this->_bind){
                if ($this->_use_ssl && !$this->_use_tls){
                    // If you have problems troubleshooting, remove the @ character from the ldap_bind command above to get the actual error message
                    throw new adLDAPException('Bind to Active Directory failed. Either the LDAPs connection failed or the login credentials are incorrect. AD said: ' . $this->get_last_error());
                } else {
                    throw new adLDAPException('Bind to Active Directory failed. Check the login credentials and/or server details. AD said: ' . $this->get_last_error());
                }
            }
        }

        if ($this->_base_dn == NULL) {
            $this->_base_dn = $this->find_base_dn();
        }

        return (true);
    }

    /**
    * Closes the LDAP connection
    *
    * @return void
    */
    public function close() {
        ldap_close ($this->_conn);
    }

    /**
    * Validate a user's login credentials
    *
    * @param string $username A user's AD username
    * @param string $password A user's AD password
    * @param bool optional $prevent_rebind
    * @return bool
    */
    public function authenticate($username, $password, $prevent_rebind = false) {
        // Prevent null binding
        if ($username === NULL || $password === NULL) { return false; }
        if (empty($username) || empty($password)) { return false; }

        // Bind as the user
        $ret = true;
        $this->_bind = @ldap_bind($this->_conn, $username . $this->_account_suffix, $password);
        if (!$this->_bind){ $ret = false; }

        // Cnce we've checked their details, kick back into admin mode if we have it
        if ($this->_ad_username !== NULL && !$prevent_rebind) {
            $this->_bind = @ldap_bind($this->_conn, $this->_ad_username . $this->_account_suffix , $this->_ad_password);
            if (!$this->_bind){
                // This should never happen in theory
                throw new adLDAPException('Rebind to Active Directory failed. AD said: ' . $this->get_last_error());
            }
        }

        return $ret;
    }

    //*****************************************************************************************************************
    // GROUP FUNCTIONS

    /**
    * Add a group to a group
    *
    * @param string $parent The parent group name
    * @param string $child The child group name
    * @return bool
    */
    public function group_add_group($parent,$child){

        // Find the parent group's dn
        $parent_group=$this->group_info($parent,array("cn"));
        if ($parent_group[0]["dn"]===NULL){ return (false); }
        $parent_dn=$parent_group[0]["dn"];

        // Find the child group's dn
        $child_group=$this->group_info($child,array("cn"));
        if ($child_group[0]["dn"]===NULL){ return (false); }
        $child_dn=$child_group[0]["dn"];

        $add=array();
        $add["member"] = $child_dn;

        $result=@ldap_mod_add($this->_conn,$parent_dn,$add);
        if ($result==false){ return (false); }
        return (true);
    }

    /**
    * Add a user to a group
    *
    * @param string $group The group to add the user to
    * @param string $user The user to add to the group
    * @param bool $isGUID Is the username passed a GUID or a samAccountName
    * @return bool
    */
    public function group_add_user($group,$user,$isGUID=false){
        // Adding a user is a bit fiddly, we need to get the full DN of the user
        // and add it using the full DN of the group

        // Find the user's dn
        $user_dn=$this->user_dn($user,$isGUID);
        if ($user_dn===false){ return (false); }

        // Find the group's dn
        $group_info=$this->group_info($group,array("cn"));
        if ($group_info[0]["dn"]===NULL){ return (false); }
        $group_dn=$group_info[0]["dn"];

        $add=array();
        $add["member"] = $user_dn;

        $result=@ldap_mod_add($this->_conn,$group_dn,$add);
        if ($result==false){ return (false); }
        return (true);
    }

    /**
    * Add a contact to a group
    *
    * @param string $group The group to add the contact to
    * @param string $contact_dn The DN of the contact to add
    * @return bool
    */
    public function group_add_contact($group,$contact_dn){
        // To add a contact we take the contact's DN
        // and add it using the full DN of the group

        // Find the group's dn
        $group_info=$this->group_info($group,array("cn"));
        if ($group_info[0]["dn"]===NULL){ return (false); }
        $group_dn=$group_info[0]["dn"];

        $add=array();
        $add["member"] = $contact_dn;

        $result=@ldap_mod_add($this->_conn,$group_dn,$add);
        if ($result==false){ return (false); }
        return (true);
    }

    /**
    * Create a group
    *
    * @param array $attributes Default attributes of the group
    * @return bool
    */
    public function group_create($attributes){
        if (!is_array($attributes)){ return ("Attributes must be an array"); }
        if (!array_key_exists("group_name",$attributes)){ return ("Missing compulsory field [group_name]"); }
        if (!array_key_exists("container",$attributes)){ return ("Missing compulsory field [container]"); }
        if (!array_key_exists("description",$attributes)){ return ("Missing compulsory field [description]"); }
        if (!is_array($attributes["container"])){ return ("Container attribute must be an array."); }
        $attributes["container"]=array_reverse($attributes["container"]);

        //$member_array = array();
        //$member_array[0] = "cn=user1,cn=Users,dc=yourdomain,dc=com";
        //$member_array[1] = "cn=administrator,cn=Users,dc=yourdomain,dc=com";

        $add=array();
        $add["cn"] = $attributes["group_name"];
        $add["samaccountname"] = $attributes["group_name"];
        $add["objectClass"] = "Group";
        $add["description"] = $attributes["description"];
        //$add["member"] = $member_array; UNTESTED

        $container="OU=".implode(",OU=",$attributes["container"]);
        $result=ldap_add($this->_conn,"CN=".$add["cn"].", ".$container.",".$this->_base_dn,$add);
        if ($result!=true){ return (false); }

        return (true);
    }

    /**
    * Remove a group from a group
    *
    * @param string $parent The parent group name
    * @param string $child The child group name
    * @return bool
    */
    public function group_del_group($parent,$child){

        // Find the parent dn
        $parent_group=$this->group_info($parent,array("cn"));
        if ($parent_group[0]["dn"]===NULL){ return (false); }
        $parent_dn=$parent_group[0]["dn"];

        // Find the child dn
        $child_group=$this->group_info($child,array("cn"));
        if ($child_group[0]["dn"]===NULL){ return (false); }
        $child_dn=$child_group[0]["dn"];

        $del=array();
        $del["member"] = $child_dn;

        $result=@ldap_mod_del($this->_conn,$parent_dn,$del);
        if ($result==false){ return (false); }
        return (true);
    }

    /**
    * Remove a user from a group
    *
    * @param string $group The group to remove a user from
    * @param string $user The AD user to remove from the group
    * @param bool $isGUID Is the username passed a GUID or a samAccountName
    * @return bool
    */
    public function group_del_user($group,$user,$isGUID=false){

        // Find the parent dn
        $group_info=$this->group_info($group,array("cn"));
        if ($group_info[0]["dn"]===NULL){ return (false); }
        $group_dn=$group_info[0]["dn"];

        // Find the users dn
        $user_dn=$this->user_dn($user,$isGUID);
        if ($user_dn===false){ return (false); }

        $del=array();
        $del["member"] = $user_dn;

        $result=@ldap_mod_del($this->_conn,$group_dn,$del);
        if ($result==false){ return (false); }
        return (true);
    }

    /**
    * Remove a contact from a group
    *
    * @param string $group The group to remove a user from
    * @param string $contact_dn The DN of a contact to remove from the group
    * @return bool
    */
    public function group_del_contact($group,$contact_dn){

        // Find the parent dn
        $group_info=$this->group_info($group,array("cn"));
        if ($group_info[0]["dn"]===NULL){ return (false); }
        $group_dn=$group_info[0]["dn"];

        $del=array();
        $del["member"] = $contact_dn;

        $result=@ldap_mod_del($this->_conn,$group_dn,$del);
        if ($result==false){ return (false); }
        return (true);
    }

    /**
    * Return a list of groups in a group
    *
    * @param string $group The group to query
    * @param bool $recursive Recursively get groups
    * @return array
    */
    public function groups_in_group($group, $recursive = NULL){
        if (!$this->_bind){ return (false); }
        if ($recursive===NULL){ $recursive=$this->_recursive_groups; } // Use the default option if they haven't set it

        // Search the directory for the members of a group
        $info=$this->group_info($group,array("member","cn"));
        $groups=$info[0]["member"];
        if (!is_array($groups)) {
            return (false);
        }

        $group_array=array();

        for ($i=0; $i<$groups["count"]; $i++){
             $filter="(&(objectCategory=group)(distinguishedName=".$this->ldap_slashes($groups[$i])."))";
             $fields = array("samaccountname", "distinguishedname", "objectClass");
             $sr=ldap_search($this->_conn,$this->_base_dn,$filter,$fields);
             $entries = ldap_get_entries($this->_conn, $sr);

             // not a person, look for a group
             if ($entries['count'] == 0 && $recursive == true) {
                $filter="(&(objectCategory=group)(distinguishedName=".$this->ldap_slashes($groups[$i])."))";
                $fields = array("distinguishedname");
                $sr=ldap_search($this->_conn,$this->_base_dn,$filter,$fields);
                $entries = ldap_get_entries($this->_conn, $sr);
                if (!isset($entries[0]['distinguishedname'][0])) {
                    continue;
                }
                $sub_groups = $this->groups_in_group($entries[0]['distinguishedname'][0], $recursive);
                if (is_array($sub_groups)) {
                    $group_array = array_merge($group_array, $sub_groups);
                    $group_array = array_unique($group_array);
                }
                continue;
             }

             $group_array[] = $entries[0]['distinguishedname'][0];
        }
        return ($group_array);
    }

    /**
    * Return a list of members in a group
    *
    * @param string $group The group to query
    * @param bool $recursive Recursively get group members
    * @return array
    */
    public function group_members($group, $recursive = NULL){
        if (!$this->_bind){ return (false); }
        if ($recursive===NULL){ $recursive=$this->_recursive_groups; } // Use the default option if they haven't set it
        // Search the directory for the members of a group
        $info=$this->group_info($group,array("member","cn"));
        $users=$info[0]["member"];
        if (!is_array($users)) {
            return (false);
        }

        $user_array=array();

        for ($i=0; $i<$users["count"]; $i++){
             $filter="(&(objectCategory=person)(distinguishedName=".$this->ldap_slashes($users[$i])."))";
             $fields = array("samaccountname", "distinguishedname", "objectClass");
             $sr=ldap_search($this->_conn,$this->_base_dn,$filter,$fields);
             $entries = ldap_get_entries($this->_conn, $sr);

             // not a person, look for a group
             if ($entries['count'] == 0 && $recursive == true) {
                $filter="(&(objectCategory=group)(distinguishedName=".$this->ldap_slashes($users[$i])."))";
                $fields = array("samaccountname");
                $sr=ldap_search($this->_conn,$this->_base_dn,$filter,$fields);
                $entries = ldap_get_entries($this->_conn, $sr);
                if (!isset($entries[0]['samaccountname'][0])) {
                    continue;
                }
                $sub_users = $this->group_members($entries[0]['samaccountname'][0], $recursive);
                if (is_array($sub_users)) {
                    $user_array = array_merge($user_array, $sub_users);
                    $user_array = array_unique($user_array);
                }
                continue;
             }

             if ($entries[0]['samaccountname'][0] === NULL && $entries[0]['distinguishedname'][0] !== NULL) {
                 $user_array[] = $entries[0]['distinguishedname'][0];
             }
             elseif ($entries[0]['samaccountname'][0] !== NULL) {
                $user_array[] = $entries[0]['samaccountname'][0];
             }
        }
        return ($user_array);
    }

    /**
    * Group Information.  Returns an array of information about a group.
    * The group name is case sensitive
    *
    * @param string $group_name The group name to retrieve info about
    * @param array $fields Fields to retrieve
    * @return array
    */
    public function group_info($group_name,$fields=NULL){
        if ($group_name===NULL){ return (false); }
        if (!$this->_bind){ return (false); }

        if (stristr($group_name, '+')) {
            $group_name=stripslashes($group_name);
        }

        $filter="(&(objectCategory=group)(name=".$this->ldap_slashes($group_name)."))";
        //echo ($filter."!!!<br>");
        if ($fields===NULL){ $fields=array("member","memberof","cn","description","distinguishedname","objectcategory","samaccountname"); }
        $sr=ldap_search($this->_conn,$this->_base_dn,$filter,$fields);
        $entries = ldap_get_entries($this->_conn, $sr);
        //print_r($entries);
        return ($entries);
    }

    /**
    * Return a complete list of "groups in groups"
    *
    * @param string $group The group to get the list from
    * @return array
    */
    public function recursive_groups($group){
        if ($group===NULL){ return (false); }

        $ret_groups=array();

        $groups=$this->group_info($group,array("memberof"));
        if (isset($groups[0]["memberof"]) && is_array($groups[0]["memberof"])) {
            $groups=$groups[0]["memberof"];

            if ($groups){
                $group_names=$this->nice_names($groups);
                $ret_groups=array_merge($ret_groups,$group_names); //final groups to return

                foreach ($group_names as $id => $group_name){
                    $child_groups=$this->recursive_groups($group_name);
                    $ret_groups=array_merge($ret_groups,$child_groups);
                }
            }
        }

        return ($ret_groups);
    }

    /**
    * Returns a complete list of the groups in AD based on a SAM Account Type
    *
    * @param string $samaccounttype The account type to return
    * @param bool $include_desc Whether to return a description
    * @param string $search Search parameters
    * @param bool $sorted Whether to sort the results
    * @return array
    */
    public function search_groups($samaccounttype = ADLDAP_SECURITY_GLOBAL_GROUP, $include_desc = false, $search = "*", $sorted = true) {
        if (!$this->_bind){ return (false); }

        $filter = '(&(objectCategory=group)';
        if ($samaccounttype !== null) {
            $filter .= '(samaccounttype='. $samaccounttype .')';
        }
        $filter .= '(cn='.$search.'))';
        // Perform the search and grab all their details
        $fields=array("samaccountname","description");
        $sr=ldap_search($this->_conn,$this->_base_dn,$filter,$fields);
        $entries = ldap_get_entries($this->_conn, $sr);

        $groups_array = array();
        for ($i=0; $i<$entries["count"]; $i++){
            if ($include_desc && strlen($entries[$i]["description"][0]) > 0 ){
                $groups_array[ $entries[$i]["samaccountname"][0] ] = $entries[$i]["description"][0];
            } elseif ($include_desc){
                $groups_array[ $entries[$i]["samaccountname"][0] ] = $entries[$i]["samaccountname"][0];
            } else {
                array_push($groups_array, $entries[$i]["samaccountname"][0]);
            }
        }
        if( $sorted ){ asort($groups_array); }
        return ($groups_array);
    }

    /**
    * Returns a complete list of all groups in AD
    *
    * @param bool $include_desc Whether to return a description
    * @param string $search Search parameters
    * @param bool $sorted Whether to sort the results
    * @return array
    */
    public function all_groups($include_desc = false, $search = "*", $sorted = true){
        $groups_array = $this->search_groups(null, $include_desc, $search, $sorted);
        return ($groups_array);
    }

    /**
    * Returns a complete list of security groups in AD
    *
    * @param bool $include_desc Whether to return a description
    * @param string $search Search parameters
    * @param bool $sorted Whether to sort the results
    * @return array
    */
    public function all_security_groups($include_desc = false, $search = "*", $sorted = true){
        $groups_array = $this->search_groups(ADLDAP_SECURITY_GLOBAL_GROUP, $include_desc, $search, $sorted);
        return ($groups_array);
    }

    /**
    * Returns a complete list of distribution lists in AD
    *
    * @param bool $include_desc Whether to return a description
    * @param string $search Search parameters
    * @param bool $sorted Whether to sort the results
    * @return array
    */
    public function all_distribution_groups($include_desc = false, $search = "*", $sorted = true){
        $groups_array = $this->search_groups(ADLDAP_DISTRIBUTION_GROUP, $include_desc, $search, $sorted);
        return ($groups_array);
    }

    //*****************************************************************************************************************
    // USER FUNCTIONS

    /**
    * Create a user
    *
    * If you specify a password here, this can only be performed over SSL
    *
    * @param array $attributes The attributes to set to the user account
    * @return bool
    */
        public function user_create($attributes){
        // Check for compulsory fields
        if (!array_key_exists("username",$attributes)){ return ("Missing compulsory field [username]"); }
        if (!array_key_exists("firstname",$attributes)){ return ("Missing compulsory field [firstname]"); }
        if (!array_key_exists("surname",$attributes)){ return ("Missing compulsory field [surname]"); }
        if (!array_key_exists("email",$attributes)){ return ("Missing compulsory field [email]"); }
        if (!array_key_exists("container",$attributes)){ return ("Missing compulsory field [container]"); }
        if (!is_array($attributes["container"])){ return ("Container attribute must be an array."); }

        if (array_key_exists("password",$attributes) && (!$this->_use_ssl && !$this->_use_tls)){
            throw new adLDAPException('SSL must be configured on your webserver and enabled in the class to set passwords.');
        }

        if (!array_key_exists("display_name",$attributes)){ $attributes["display_name"]=$attributes["firstname"]." ".$attributes["surname"]; }

        // Translate the schema
        $add=$this->adldap_schema($attributes);

        // Additional stuff only used for adding accounts
        $add["cn"][0]=$attributes["display_name"];
        $add["samaccountname"][0]=$attributes["username"];
        $add["objectclass"][0]="top";
        $add["objectclass"][1]="person";
        $add["objectclass"][2]="organizationalPerson";
        $add["objectclass"][3]="user"; //person?
        //$add["name"][0]=$attributes["firstname"]." ".$attributes["surname"];

        // Set the account control attribute
        $control_options=array("NORMAL_ACCOUNT");
        if (!$attributes["enabled"]){ $control_options[]="ACCOUNTDISABLE"; }
        $add["userAccountControl"][0]=$this->account_control($control_options);
        //echo ("<pre>"); print_r($add);

        // Determine the container
        $attributes["container"]=array_reverse($attributes["container"]);
        $container="OU=".implode(",OU=",$attributes["container"]);

        // Add the entry
        $result=@ldap_add($this->_conn, "CN=".$add["cn"][0].", ".$container.",".$this->_base_dn, $add);
        if ($result!=true){ return (false); }

        return (true);
    }

    /**
    * Delete a user account
    *
    * @param string $username The username to delete (please be careful here!)
    * @param bool $isGUID Is the username a GUID or a samAccountName
    * @return array
    */
    public function user_delete($username,$isGUID=false) {
        $userinfo = $this->user_info($username, array("*"),$isGUID);
        $dn = $userinfo[0]['distinguishedname'][0];
        $result=$this->dn_delete($dn);
        if ($result!=true){ return (false); }
        return (true);
    }

    /**
    * Groups the user is a member of
    *
    * @param string $username The username to query
    * @param bool $recursive Recursive list of groups
    * @param bool $isGUID Is the username passed a GUID or a samAccountName
    * @return array
    */
    public function user_groups($username,$recursive=NULL,$isGUID=false){
        if ($username===NULL){ return (false); }
        if ($recursive===NULL){ $recursive=$this->_recursive_groups; } // Use the default option if they haven't set it
        if (!$this->_bind){ return (false); }

        // Search the directory for their information
        $info=@$this->user_info($username,array("memberof","primarygroupid"),$isGUID);
        $groups=$this->nice_names($info[0]["memberof"]); // Presuming the entry returned is our guy (unique usernames)

        if ($recursive === true){
            foreach ($groups as $id => $group_name){
                $extra_groups=$this->recursive_groups($group_name);
                $groups=array_merge($groups,$extra_groups);
            }
        }

        return ($groups);
    }

    /**
    * Find information about the users
    *
    * @param string $username The username to query
    * @param array $fields Array of parameters to query
    * @param bool $isGUID Is the username passed a GUID or a samAccountName
    * @return array
    */
    public function user_info($username,$fields=NULL,$isGUID=false){
        if ($username===NULL){ return (false); }
        if (!$this->_bind){ return (false); }

        if ($isGUID === true) {
            $username = $this->strguid2hex($username);
            $filter="objectguid=".$username;
        }
        else if (strstr($username, "@")) {
             $filter="userPrincipalName=".$username;
        }
        else {
             $filter="samaccountname=".$username;
        }
        $filter = "(&(objectCategory=person)({$filter}))";
        if ($fields===NULL){ $fields=array("samaccountname","mail","memberof","department","displayname","telephonenumber","primarygroupid","objectsid"); }
        if (!in_array("objectsid",$fields)){
            $fields[] = "objectsid";
        }
        $sr=ldap_search($this->_conn,$this->_base_dn,$filter,$fields);
        $entries = ldap_get_entries($this->_conn, $sr);

        if (isset($entries[0])) {
            if ($entries[0]['count'] >= 1) {
                if (in_array("memberof", $fields)) {
                    // AD does not return the primary group in the ldap query, we may need to fudge it
                    if ($this->_real_primarygroup && isset($entries[0]["primarygroupid"][0]) && isset($entries[0]["objectsid"][0])){
                        //$entries[0]["memberof"][]=$this->group_cn($entries[0]["primarygroupid"][0]);
                        $entries[0]["memberof"][]=$this->get_primary_group($entries[0]["primarygroupid"][0], $entries[0]["objectsid"][0]);
                    } else {
                        $entries[0]["memberof"][]="CN=Domain Users,CN=Users,".$this->_base_dn;
                    }
                    $entries[0]["memberof"]["count"]++;
                }
            }
            return $entries;
        }
        return false;
    }

    /**
    * Determine if a user is in a specific group
    *
    * @param string $username The username to query
    * @param string $group The name of the group to check against
    * @param bool $recursive Check groups recursively
    * @param bool $isGUID Is the username passed a GUID or a samAccountName
    * @return bool
    */
    public function user_ingroup($username,$group,$recursive=NULL,$isGUID=false){
        if ($username===NULL){ return (false); }
        if ($group===NULL){ return (false); }
        if (!$this->_bind){ return (false); }
        if ($recursive===NULL){ $recursive=$this->_recursive_groups; } // Use the default option if they haven't set it

        // Get a list of the groups
        $groups=$this->user_groups($username,$recursive,$isGUID);

        // Return true if the specified group is in the group list
        if (in_array($group,$groups)){ return (true); }

        return (false);
    }

    /**
    * Determine a user's password expiry date
    *
    * @param string $username The username to query
    * @param book $isGUID Is the username passed a GUID or a samAccountName
    * @requires bcmath http://www.php.net/manual/en/book.bc.php
    * @return array
    */
    public function user_password_expiry($username,$isGUID=false) {
        if ($username===NULL){ return ("Missing compulsory field [username]"); }
        if (!$this->_bind){ return (false); }
        if (!function_exists('bcmod')) { return ("Missing function support [bcmod] http://www.php.net/manual/en/book.bc.php"); };

        $userinfo = $this->user_info($username, array("pwdlastset", "useraccountcontrol"), $isGUID);
        $pwdlastset = $userinfo[0]['pwdlastset'][0];
        $status = array();

        if ($userinfo[0]['useraccountcontrol'][0] == '66048') {
            // Password does not expire
            return "Does not expire";
        }
        if ($pwdlastset === '0') {
            // Password has already expired
            return "Password has expired";
        }

         // Password expiry in AD can be calculated from TWO values:
         //   - User's own pwdLastSet attribute: stores the last time the password was changed
         //   - Domain's maxPwdAge attribute: how long passwords last in the domain
         //
         // Although Microsoft chose to use a different base and unit for time measurements.
         // This function will convert them to Unix timestamps
         $sr = ldap_read($this->_conn, $this->_base_dn, 'objectclass=*', array('maxPwdAge'));
         if (!$sr) {
             return false;
         }
         $info = ldap_get_entries($this->_conn, $sr);
         $maxpwdage = $info[0]['maxpwdage'][0];


         // See MSDN: http://msdn.microsoft.com/en-us/library/ms974598.aspx
         //
         // pwdLastSet contains the number of 100 nanosecond intervals since January 1, 1601 (UTC),
         // stored in a 64 bit integer.
         //
         // The number of seconds between this date and Unix epoch is 11644473600.
         //
         // maxPwdAge is stored as a large integer that represents the number of 100 nanosecond
         // intervals from the time the password was set before the password expires.
         //
         // We also need to scale this to seconds but also this value is a _negative_ quantity!
         //
         // If the low 32 bits of maxPwdAge are equal to 0 passwords do not expire
         //
         // Unfortunately the maths involved are too big for PHP integers, so I've had to require
         // BCMath functions to work with arbitrary precision numbers.
         if (bcmod($maxpwdage, 4294967296) === '0') {
            return "Domain does not expire passwords";
        }

        // Add maxpwdage and pwdlastset and we get password expiration time in Microsoft's
        // time units.  Because maxpwd age is negative we need to subtract it.
        $pwdexpire = bcsub($pwdlastset, $maxpwdage);

        // Convert MS's time to Unix time
        $status['expiryts'] = bcsub(bcdiv($pwdexpire, '10000000'), '11644473600');
        $status['expiryformat'] = date('Y-m-d H:i:s', bcsub(bcdiv($pwdexpire, '10000000'), '11644473600'));

        return $status;
    }

    /**
    * Modify a user
    *
    * @param string $username The username to query
    * @param array $attributes The attributes to modify.  Note if you set the enabled attribute you must not specify any other attributes
    * @param bool $isGUID Is the username passed a GUID or a samAccountName
    * @return bool
    */
    public function user_modify($username,$attributes,$isGUID=false){
        if ($username===NULL){ return ("Missing compulsory field [username]"); }
        if (array_key_exists("password",$attributes) && !$this->_use_ssl){
            throw new adLDAPException('SSL must be configured on your webserver and enabled in the class to set passwords.');
        }

        // Find the dn of the user
        $user_dn=$this->user_dn($username,$isGUID);
        if ($user_dn===false){ return (false); }

        // Translate the update to the LDAP schema
        $mod=$this->adldap_schema($attributes);

        // Check to see if this is an enabled status update
        if (!$mod && !array_key_exists("enabled", $attributes)){ return (false); }

        // Set the account control attribute (only if specified)
        if (array_key_exists("enabled",$attributes)){
            if ($attributes["enabled"]){ $control_options=array("NORMAL_ACCOUNT"); }
            else { $control_options=array("NORMAL_ACCOUNT","ACCOUNTDISABLE"); }
            $mod["userAccountControl"][0]=$this->account_control($control_options);
        }

        // Do the update
        $result=@ldap_modify($this->_conn,$user_dn,$mod);
        if ($result==false){ return (false); }

        return (true);
    }

    /**
    * Disable a user account
    *
    * @param string $username The username to disable
    * @param bool $isGUID Is the username passed a GUID or a samAccountName
    * @return bool
    */
    public function user_disable($username,$isGUID=false){
        if ($username===NULL){ return ("Missing compulsory field [username]"); }
        $attributes=array("enabled"=>0);
        $result = $this->user_modify($username, $attributes, $isGUID);
        if ($result==false){ return (false); }

        return (true);
    }

    /**
    * Enable a user account
    *
    * @param string $username The username to enable
    * @param bool $isGUID Is the username passed a GUID or a samAccountName
    * @return bool
    */
    public function user_enable($username,$isGUID=false){
        if ($username===NULL){ return ("Missing compulsory field [username]"); }
        $attributes=array("enabled"=>1);
        $result = $this->user_modify($username, $attributes, $isGUID);
        if ($result==false){ return (false); }

        return (true);
    }

    /**
    * Set the password of a user - This must be performed over SSL
    *
    * @param string $username The username to modify
    * @param string $password The new password
    * @param bool $isGUID Is the username passed a GUID or a samAccountName
    * @return bool
    */
    public function user_password($username,$password,$isGUID=false){
        if ($username===NULL){ return (false); }
        if ($password===NULL){ return (false); }
        if (!$this->_bind){ return (false); }
        if (!$this->_use_ssl && !$this->_use_tls){
            throw new adLDAPException('SSL must be configured on your webserver and enabled in the class to set passwords.');
        }

        $user_dn=$this->user_dn($username,$isGUID);
        if ($user_dn===false){ return (false); }

        $add=array();
        $add["unicodePwd"][0]=$this->encode_password($password);

        $result=@ldap_mod_replace($this->_conn,$user_dn,$add);
        if ($result==false){
            $err = ldap_errno($this->_conn);
            if($err){
                $msg = 'Error '.$err.': '.ldap_err2str($err).'.';
                if($err == 53) $msg .= ' Your password might not match the password policy.';
                throw new adLDAPException($msg);
            }else{
                return false;
            }
        }

        return (true);
    }

    /**
    * Return a list of all users in AD
    *
    * @param bool $include_desc Return a description of the user
    * @param string $search Search parameter
    * @param bool $sorted Sort the user accounts
    * @return array
    */
    public function all_users($include_desc = false, $search = "*", $sorted = true){
        if (!$this->_bind){ return (false); }

        // Perform the search and grab all their details
        $filter = "(&(objectClass=user)(samaccounttype=". ADLDAP_NORMAL_ACCOUNT .")(objectCategory=person)(cn=".$search."))";
        $fields=array("samaccountname","displayname");
        $sr=ldap_search($this->_conn,$this->_base_dn,$filter,$fields);
        $entries = ldap_get_entries($this->_conn, $sr);

        $users_array = array();
        for ($i=0; $i<$entries["count"]; $i++){
            if ($include_desc && strlen($entries[$i]["displayname"][0])>0){
                $users_array[ $entries[$i]["samaccountname"][0] ] = $entries[$i]["displayname"][0];
            } elseif ($include_desc){
                $users_array[ $entries[$i]["samaccountname"][0] ] = $entries[$i]["samaccountname"][0];
            } else {
                array_push($users_array, $entries[$i]["samaccountname"][0]);
            }
        }
        if ($sorted){ asort($users_array); }
        return ($users_array);
    }

    /**
    * Converts a username (samAccountName) to a GUID
    *
    * @param string $username The username to query
    * @return string
    */
    public function username2guid($username) {
        if (!$this->_bind){ return (false); }
        if ($username === null){ return ("Missing compulsory field [username]"); }

        $filter = "samaccountname=" . $username;
        $fields = array("objectGUID");
        $sr = @ldap_search($this->_conn, $this->_base_dn, $filter, $fields);
        if (ldap_count_entries($this->_conn, $sr) > 0) {
            $entry = @ldap_first_entry($this->_conn, $sr);
            $guid = @ldap_get_values_len($this->_conn, $entry, 'objectGUID');
            $strGUID = $this->binary2text($guid[0]);
            return ($strGUID);
        }
        else {
            return (false);
        }
    }

    /**
    * Move a user account to a different OU
    *
    * @param string $username The username to move (please be careful here!)
    * @param array $container The container or containers to move the user to (please be careful here!).
    * accepts containers in 1. parent 2. child order
    * @return array
    */
    public function user_move($username, $container) {
        if (!$this->_bind){ return (false); }
        if ($username === null){ return ("Missing compulsory field [username]"); }
        if ($container === null){ return ("Missing compulsory field [container]"); }
        if (!is_array($container)){ return ("Container must be an array"); }

        $userinfo = $this->user_info($username, array("*"));
        $dn = $userinfo[0]['distinguishedname'][0];
        $newrdn = "cn=" . $username;
        $container = array_reverse($container);
        $newcontainer = "ou=" . implode(",ou=",$container);
        $newbasedn = strtolower($newcontainer) . "," . $this->_base_dn;
        $result=@ldap_rename($this->_conn,$dn,$newrdn,$newbasedn,true);
        if ($result !== true) {
            return (false);
        }
        return (true);
    }

    //*****************************************************************************************************************
    // CONTACT FUNCTIONS
    // * Still work to do in this area, and new functions to write

    /**
    * Create a contact
    *
    * @param array $attributes The attributes to set to the contact
    * @return bool
    */
    public function contact_create($attributes){
        // Check for compulsory fields
        if (!array_key_exists("display_name",$attributes)){ return ("Missing compulsory field [display_name]"); }
        if (!array_key_exists("email",$attributes)){ return ("Missing compulsory field [email]"); }
        if (!array_key_exists("container",$attributes)){ return ("Missing compulsory field [container]"); }
        if (!is_array($attributes["container"])){ return ("Container attribute must be an array."); }

        // Translate the schema
        $add=$this->adldap_schema($attributes);

        // Additional stuff only used for adding contacts
        $add["cn"][0]=$attributes["display_name"];
        $add["objectclass"][0]="top";
        $add["objectclass"][1]="person";
        $add["objectclass"][2]="organizationalPerson";
        $add["objectclass"][3]="contact";
        if (!isset($attributes['exchange_hidefromlists'])) {
            $add["msExchHideFromAddressLists"][0]="TRUE";
        }

        // Determine the container
        $attributes["container"]=array_reverse($attributes["container"]);
        $container="OU=".implode(",OU=",$attributes["container"]);

        // Add the entry
        $result=@ldap_add($this->_conn, "CN=".$add["cn"][0].", ".$container.",".$this->_base_dn, $add);
        if ($result!=true){ return (false); }

        return (true);
    }

    /**
    * Determine the list of groups a contact is a member of
    *
    * @param string $distinguisedname The full DN of a contact
    * @param bool $recursive Recursively check groups
    * @return array
    */
    public function contact_groups($distinguishedname,$recursive=NULL){
        if ($distinguishedname===NULL){ return (false); }
        if ($recursive===NULL){ $recursive=$this->_recursive_groups; } //use the default option if they haven't set it
        if (!$this->_bind){ return (false); }

        // Search the directory for their information
        $info=@$this->contact_info($distinguishedname,array("memberof","primarygroupid"));
        $groups=$this->nice_names($info[0]["memberof"]); //presuming the entry returned is our contact

        if ($recursive === true){
            foreach ($groups as $id => $group_name){
                $extra_groups=$this->recursive_groups($group_name);
                $groups=array_merge($groups,$extra_groups);
            }
        }

        return ($groups);
    }

    /**
    * Get contact information
    *
    * @param string $distinguisedname The full DN of a contact
    * @param array $fields Attributes to be returned
    * @return array
    */
    public function contact_info($distinguishedname,$fields=NULL){
        if ($distinguishedname===NULL){ return (false); }
        if (!$this->_bind){ return (false); }

        $filter="distinguishedName=".$distinguishedname;
        if ($fields===NULL){ $fields=array("distinguishedname","mail","memberof","department","displayname","telephonenumber","primarygroupid","objectsid"); }
        $sr=ldap_search($this->_conn,$this->_base_dn,$filter,$fields);
        $entries = ldap_get_entries($this->_conn, $sr);

        if ($entries[0]['count'] >= 1) {
            // AD does not return the primary group in the ldap query, we may need to fudge it
            if ($this->_real_primarygroup && isset($entries[0]["primarygroupid"][0]) && isset($entries[0]["primarygroupid"][0])){
                //$entries[0]["memberof"][]=$this->group_cn($entries[0]["primarygroupid"][0]);
                $entries[0]["memberof"][]=$this->get_primary_group($entries[0]["primarygroupid"][0], $entries[0]["objectsid"][0]);
            } else {
                $entries[0]["memberof"][]="CN=Domain Users,CN=Users,".$this->_base_dn;
            }
        }

        $entries[0]["memberof"]["count"]++;
        return ($entries);
    }

    /**
    * Determine if a contact is a member of a group
    *
    * @param string $distinguisedname The full DN of a contact
    * @param string $group The group name to query
    * @param bool $recursive Recursively check groups
    * @return bool
    */
    public function contact_ingroup($distinguisedname,$group,$recursive=NULL){
        if ($distinguisedname===NULL){ return (false); }
        if ($group===NULL){ return (false); }
        if (!$this->_bind){ return (false); }
        if ($recursive===NULL){ $recursive=$this->_recursive_groups; } //use the default option if they haven't set it

        // Get a list of the groups
        $groups=$this->contact_groups($distinguisedname,array("memberof"),$recursive);

        // Return true if the specified group is in the group list
        if (in_array($group,$groups)){ return (true); }

        return (false);
    }

    /**
    * Modify a contact
    *
    * @param string $distinguishedname The contact to query
    * @param array $attributes The attributes to modify.  Note if you set the enabled attribute you must not specify any other attributes
    * @return bool
    */
    public function contact_modify($distinguishedname,$attributes){
        if ($distinguishedname===NULL){ return ("Missing compulsory field [distinguishedname]"); }

        // Translate the update to the LDAP schema
        $mod=$this->adldap_schema($attributes);

        // Check to see if this is an enabled status update
        if (!$mod){ return (false); }

        // Do the update
        $result=ldap_modify($this->_conn,$distinguishedname,$mod);
        if ($result==false){ return (false); }

        return (true);
    }

    /**
    * Delete a contact
    *
    * @param string $distinguishedname The contact dn to delete (please be careful here!)
    * @return array
    */
    public function contact_delete($distinguishedname) {
        $result = $this->dn_delete($distinguishedname);
        if ($result!=true){ return (false); }
        return (true);
    }

    /**
    * Return a list of all contacts
    *
    * @param bool $include_desc Include a description of a contact
    * @param string $search The search parameters
    * @param bool $sorted Whether to sort the results
    * @return array
    */
    public function all_contacts($include_desc = false, $search = "*", $sorted = true){
        if (!$this->_bind){ return (false); }

        // Perform the search and grab all their details
        $filter = "(&(objectClass=contact)(cn=".$search."))";
        $fields=array("displayname","distinguishedname");
        $sr=ldap_search($this->_conn,$this->_base_dn,$filter,$fields);
        $entries = ldap_get_entries($this->_conn, $sr);

        $users_array = array();
        for ($i=0; $i<$entries["count"]; $i++){
            if ($include_desc && strlen($entries[$i]["displayname"][0])>0){
                $users_array[ $entries[$i]["distinguishedname"][0] ] = $entries[$i]["displayname"][0];
            } elseif ($include_desc){
                $users_array[ $entries[$i]["distinguishedname"][0] ] = $entries[$i]["distinguishedname"][0];
            } else {
                array_push($users_array, $entries[$i]["distinguishedname"][0]);
            }
        }
        if ($sorted){ asort($users_array); }
        return ($users_array);
    }

    //*****************************************************************************************************************
    // FOLDER FUNCTIONS

    /**
    * Returns a folder listing for a specific OU
    * See http://adldap.sourceforge.net/wiki/doku.php?id=api_folder_functions
    *
    * @param array $folder_name An array to the OU you wish to list.
    *                           If set to NULL will list the root, strongly recommended to set
    *                           $recursive to false in that instance!
    * @param string $dn_type The type of record to list.  This can be ADLDAP_FOLDER or ADLDAP_CONTAINER.
    * @param bool $recursive Recursively search sub folders
    * @param bool $type Specify a type of object to search for
    * @return array
    */
    public function folder_list($folder_name = NULL, $dn_type = ADLDAP_FOLDER, $recursive = NULL, $type = NULL) {
        if ($recursive===NULL){ $recursive=$this->_recursive_groups; } //use the default option if they haven't set it
        if (!$this->_bind){ return (false); }

        $filter = '(&';
        if ($type !== NULL) {
            switch ($type) {
                case 'contact':
                    $filter .= '(objectClass=contact)';
                    break;
                case 'computer':
                    $filter .= '(objectClass=computer)';
                    break;
                case 'group':
                    $filter .= '(objectClass=group)';
                    break;
                case 'folder':
                    $filter .= '(objectClass=organizationalUnit)';
                    break;
                case 'container':
                    $filter .= '(objectClass=container)';
                    break;
                case 'domain':
                    $filter .= '(objectClass=builtinDomain)';
                    break;
                default:
                    $filter .= '(objectClass=user)';
                    break;
            }
        }
        else {
            $filter .= '(objectClass=*)';
        }
        // If the folder name is null then we will search the root level of AD
        // This requires us to not have an OU= part, just the base_dn
        $searchou = $this->_base_dn;
        if (is_array($folder_name)) {
            $ou = $dn_type . "=".implode("," . $dn_type . "=",$folder_name);
            $filter .= '(!(distinguishedname=' . $ou . ',' . $this->_base_dn . ')))';
            $searchou = $ou . ',' . $this->_base_dn;
        }
        else {
            $filter .= '(!(distinguishedname=' . $this->_base_dn . ')))';
        }

        if ($recursive === true) {
            $sr=ldap_search($this->_conn, $searchou, $filter, array('objectclass', 'distinguishedname', 'samaccountname'));
            $entries = @ldap_get_entries($this->_conn, $sr);
            if (is_array($entries)) {
                return $entries;
            }
        }
        else {
            $sr=ldap_list($this->_conn, $searchou, $filter, array('objectclass', 'distinguishedname', 'samaccountname'));
            $entries = @ldap_get_entries($this->_conn, $sr);
            if (is_array($entries)) {
                return $entries;
            }
        }

        return false;
    }

    //*****************************************************************************************************************
    // COMPUTER FUNCTIONS

    /**
    * Get information about a specific computer
    *
    * @param string $computer_name The name of the computer
    * @param array $fields Attributes to return
    * @return array
    */
    public function computer_info($computer_name,$fields=NULL){
        if ($computer_name===NULL){ return (false); }
        if (!$this->_bind){ return (false); }

        $filter="(&(objectClass=computer)(cn=".$computer_name."))";
        if ($fields===NULL){ $fields=array("memberof","cn","displayname","dnshostname","distinguishedname","objectcategory","operatingsystem","operatingsystemservicepack","operatingsystemversion"); }
        $sr=ldap_search($this->_conn,$this->_base_dn,$filter,$fields);
        $entries = ldap_get_entries($this->_conn, $sr);

        return ($entries);
    }

    /**
    * Check if a computer is in a group
    *
    * @param string $computer_name The name of the computer
    * @param string $group The group to check
    * @param bool $recursive Whether to check recursively
    * @return array
    */
    public function computer_ingroup($computer_name,$group,$recursive=NULL){
        if ($computer_name===NULL){ return (false); }
        if ($group===NULL){ return (false); }
        if (!$this->_bind){ return (false); }
        if ($recursive===NULL){ $recursive=$this->_recursive_groups; } // use the default option if they haven't set it

        //get a list of the groups
        $groups=$this->computer_groups($computer_name,array("memberof"),$recursive);

        //return true if the specified group is in the group list
        if (in_array($group,$groups)){ return (true); }

        return (false);
    }

    /**
    * Get the groups a computer is in
    *
    * @param string $computer_name The name of the computer
    * @param bool $recursive Whether to check recursively
    * @return array
    */
    public function computer_groups($computer_name,$recursive=NULL){
        if ($computer_name===NULL){ return (false); }
        if ($recursive===NULL){ $recursive=$this->_recursive_groups; } //use the default option if they haven't set it
        if (!$this->_bind){ return (false); }

        //search the directory for their information
        $info=@$this->computer_info($computer_name,array("memberof","primarygroupid"));
        $groups=$this->nice_names($info[0]["memberof"]); //presuming the entry returned is our guy (unique usernames)

        if ($recursive === true){
            foreach ($groups as $id => $group_name){
              $extra_groups=$this->recursive_groups($group_name);
              $groups=array_merge($groups,$extra_groups);
            }
        }

        return ($groups);
    }

    //************************************************************************************************************
    //  ORGANIZATIONAL UNIT FUNCTIONS

     /**
    * Create an organizational unit
    *
    * @param array $attributes Default attributes of the ou
    * @return bool
    */
    public function ou_create($attributes){
        if (!is_array($attributes)){ return ("Attributes must be an array"); }
        if (!array_key_exists("ou_name",$attributes)){ return ("Missing compulsory field [ou_name]"); }
        if (!array_key_exists("container",$attributes)){ return ("Missing compulsory field [container]"); }
        if (!is_array($attributes["container"])){ return ("Container attribute must be an array."); }
        $attributes["container"]=array_reverse($attributes["container"]);

        $add=array();
        $add["objectClass"] = "organizationalUnit";

        $container="OU=".implode(",OU=",$attributes["container"]);
        $result=ldap_add($this->_conn,"CN=".$add["cn"].", ".$container.",".$this->_base_dn,$add);
        if ($result!=true){ return (false); }

        return (true);
    }

    //************************************************************************************************************
    // EXCHANGE FUNCTIONS

    /**
    * Create an Exchange account
    *
    * @param string $username The username of the user to add the Exchange account to
    * @param array $storagegroup The mailbox, Exchange Storage Group, for the user account, this must be a full CN
    *                            If the storage group has a different base_dn to the adLDAP configuration, set it using $base_dn
    * @param string $emailaddress The primary email address to add to this user
    * @param string $mailnickname The mail nick name.  If mail nickname is blank, the username will be used
    * @param bool $usedefaults Indicates whether the store should use the default quota, rather than the per-mailbox quota.
    * @param string $base_dn Specify an alternative base_dn for the Exchange storage group
    * @param bool $isGUID Is the username passed a GUID or a samAccountName
    * @return bool
    */
    public function exchange_create_mailbox($username, $storagegroup, $emailaddress, $mailnickname=NULL, $usedefaults=TRUE, $base_dn=NULL, $isGUID=false){
        if ($username===NULL){ return ("Missing compulsory field [username]"); }
        if ($storagegroup===NULL){ return ("Missing compulsory array [storagegroup]"); }
        if (!is_array($storagegroup)){ return ("[storagegroup] must be an array"); }
        if ($emailaddress===NULL){ return ("Missing compulsory field [emailaddress]"); }

        if ($base_dn===NULL) {
            $base_dn = $this->_base_dn;
        }

        $container="CN=".implode(",CN=",$storagegroup);

        if ($mailnickname===NULL) { $mailnickname=$username; }
        $mdbUseDefaults = $this->bool2str($usedefaults);

        $attributes = array(
            'exchange_homemdb'=>$container.",".$base_dn,
            'exchange_proxyaddress'=>'SMTP:' . $emailaddress,
            'exchange_mailnickname'=>$mailnickname,
            'exchange_usedefaults'=>$mdbUseDefaults
        );
        $result = $this->user_modify($username,$attributes,$isGUID);
        if ($result==false){ return (false); }
        return (true);
    }

    /**
    * Add an X400 address to Exchange
    * See http://tools.ietf.org/html/rfc1685 for more information.
    * An X400 Address looks similar to this X400:c=US;a= ;p=Domain;o=Organization;s=Doe;g=John;
    *
    * @param string $username The username of the user to add the X400 to to
    * @param string $country Country
    * @param string $admd Administration Management Domain
    * @param string $pdmd Private Management Domain (often your AD domain)
    * @param string $org Organization
    * @param string $surname Surname
    * @param string $givenName Given name
    * @param bool $isGUID Is the username passed a GUID or a samAccountName
    * @return bool
    */
    public function exchange_add_X400($username, $country, $admd, $pdmd, $org, $surname, $givenname, $isGUID=false) {
        if ($username===NULL){ return ("Missing compulsory field [username]"); }

        $proxyvalue = 'X400:';

        // Find the dn of the user
        $user=$this->user_info($username,array("cn","proxyaddresses"), $isGUID);
        if ($user[0]["dn"]===NULL){ return (false); }
        $user_dn=$user[0]["dn"];

        // We do not have to demote an email address from the default so we can just add the new proxy address
        $attributes['exchange_proxyaddress'] = $proxyvalue . 'c=' . $country . ';a=' . $admd . ';p=' . $pdmd . ';o=' . $org . ';s=' . $surname . ';g=' . $givenname . ';';

        // Translate the update to the LDAP schema
        $add=$this->adldap_schema($attributes);

        if (!$add){ return (false); }

        // Do the update
        // Take out the @ to see any errors, usually this error might occur because the address already
        // exists in the list of proxyAddresses
        $result=@ldap_mod_add($this->_conn,$user_dn,$add);
        if ($result==false){ return (false); }

        return (true);
    }

    /**
    * Add an address to Exchange
    *
    * @param string $username The username of the user to add the Exchange account to
    * @param string $emailaddress The email address to add to this user
    * @param bool $default Make this email address the default address, this is a bit more intensive as we have to demote any existing default addresses
    * @param bool $isGUID Is the username passed a GUID or a samAccountName
    * @return bool
    */
    public function exchange_add_address($username, $emailaddress, $default=FALSE, $isGUID=false) {
        if ($username===NULL){ return ("Missing compulsory field [username]"); }
        if ($emailaddress===NULL) { return ("Missing compulsory fields [emailaddress]"); }

        $proxyvalue = 'smtp:';
        if ($default === true) {
            $proxyvalue = 'SMTP:';
        }

        // Find the dn of the user
        $user=$this->user_info($username,array("cn","proxyaddresses"),$isGUID);
        if ($user[0]["dn"]===NULL){ return (false); }
        $user_dn=$user[0]["dn"];

        // We need to scan existing proxy addresses and demote the default one
        if (is_array($user[0]["proxyaddresses"]) && $default===true) {
            $modaddresses = array();
            for ($i=0;$i<sizeof($user[0]['proxyaddresses']);$i++) {
                if (strstr($user[0]['proxyaddresses'][$i], 'SMTP:') !== false) {
                    $user[0]['proxyaddresses'][$i] = str_replace('SMTP:', 'smtp:', $user[0]['proxyaddresses'][$i]);
                }
                if ($user[0]['proxyaddresses'][$i] != '') {
                    $modaddresses['proxyAddresses'][$i] = $user[0]['proxyaddresses'][$i];
                }
            }
            $modaddresses['proxyAddresses'][(sizeof($user[0]['proxyaddresses'])-1)] = 'SMTP:' . $emailaddress;

            $result=@ldap_mod_replace($this->_conn,$user_dn,$modaddresses);
            if ($result==false){ return (false); }

            return (true);
        }
        else {
            // We do not have to demote an email address from the default so we can just add the new proxy address
            $attributes['exchange_proxyaddress'] = $proxyvalue . $emailaddress;

            // Translate the update to the LDAP schema
            $add=$this->adldap_schema($attributes);

            if (!$add){ return (false); }

            // Do the update
            // Take out the @ to see any errors, usually this error might occur because the address already
            // exists in the list of proxyAddresses
            $result=@ldap_mod_add($this->_conn,$user_dn,$add);
            if ($result==false){ return (false); }

            return (true);
        }
    }

    /**
    * Remove an address to Exchange
    * If you remove a default address the account will no longer have a default,
    * we recommend changing the default address first
    *
    * @param string $username The username of the user to add the Exchange account to
    * @param string $emailaddress The email address to add to this user
    * @param bool $isGUID Is the username passed a GUID or a samAccountName
    * @return bool
    */
    public function exchange_del_address($username, $emailaddress, $isGUID=false) {
        if ($username===NULL){ return ("Missing compulsory field [username]"); }
        if ($emailaddress===NULL) { return ("Missing compulsory fields [emailaddress]"); }

        // Find the dn of the user
        $user=$this->user_info($username,array("cn","proxyaddresses"),$isGUID);
        if ($user[0]["dn"]===NULL){ return (false); }
        $user_dn=$user[0]["dn"];

        if (is_array($user[0]["proxyaddresses"])) {
            $mod = array();
            for ($i=0;$i<sizeof($user[0]['proxyaddresses']);$i++) {
                if (strstr($user[0]['proxyaddresses'][$i], 'SMTP:') !== false && $user[0]['proxyaddresses'][$i] == 'SMTP:' . $emailaddress) {
                    $mod['proxyAddresses'][0] = 'SMTP:' . $emailaddress;
                }
                elseif (strstr($user[0]['proxyaddresses'][$i], 'smtp:') !== false && $user[0]['proxyaddresses'][$i] == 'smtp:' . $emailaddress) {
                    $mod['proxyAddresses'][0] = 'smtp:' . $emailaddress;
                }
            }

            $result=@ldap_mod_del($this->_conn,$user_dn,$mod);
            if ($result==false){ return (false); }

            return (true);
        }
        else {
            return (false);
        }
    }
    /**
    * Change the default address
    *
    * @param string $username The username of the user to add the Exchange account to
    * @param string $emailaddress The email address to make default
    * @param bool $isGUID Is the username passed a GUID or a samAccountName
    * @return bool
    */
    public function exchange_primary_address($username, $emailaddress, $isGUID=false) {
        if ($username===NULL){ return ("Missing compulsory field [username]"); }
        if ($emailaddress===NULL) { return ("Missing compulsory fields [emailaddress]"); }

        // Find the dn of the user
        $user=$this->user_info($username,array("cn","proxyaddresses"), $isGUID);
        if ($user[0]["dn"]===NULL){ return (false); }
        $user_dn=$user[0]["dn"];

        if (is_array($user[0]["proxyaddresses"])) {
            $modaddresses = array();
            for ($i=0;$i<sizeof($user[0]['proxyaddresses']);$i++) {
                if (strstr($user[0]['proxyaddresses'][$i], 'SMTP:') !== false) {
                    $user[0]['proxyaddresses'][$i] = str_replace('SMTP:', 'smtp:', $user[0]['proxyaddresses'][$i]);
                }
                if ($user[0]['proxyaddresses'][$i] == 'smtp:' . $emailaddress) {
                    $user[0]['proxyaddresses'][$i] = str_replace('smtp:', 'SMTP:', $user[0]['proxyaddresses'][$i]);
                }
                if ($user[0]['proxyaddresses'][$i] != '') {
                    $modaddresses['proxyAddresses'][$i] = $user[0]['proxyaddresses'][$i];
                }
            }

            $result=@ldap_mod_replace($this->_conn,$user_dn,$modaddresses);
            if ($result==false){ return (false); }

            return (true);
        }

    }

    /**
    * Mail enable a contact
    * Allows email to be sent to them through Exchange
    *
    * @param string $distinguishedname The contact to mail enable
    * @param string $emailaddress The email address to allow emails to be sent through
    * @param string $mailnickname The mailnickname for the contact in Exchange.  If NULL this will be set to the display name
    * @return bool
    */
    public function exchange_contact_mailenable($distinguishedname, $emailaddress, $mailnickname=NULL){
        if ($distinguishedname===NULL){ return ("Missing compulsory field [distinguishedname]"); }
        if ($emailaddress===NULL){ return ("Missing compulsory field [emailaddress]"); }

        if ($mailnickname !== NULL) {
            // Find the dn of the user
            $user=$this->contact_info($distinguishedname,array("cn","displayname"));
            if ($user[0]["displayname"]===NULL){ return (false); }
            $mailnickname = $user[0]['displayname'][0];
        }

        $attributes = array("email"=>$emailaddress,"contact_email"=>"SMTP:" . $emailaddress,"exchange_proxyaddress"=>"SMTP:" . $emailaddress,"exchange_mailnickname"=>$mailnickname);

        // Translate the update to the LDAP schema
        $mod=$this->adldap_schema($attributes);

        // Check to see if this is an enabled status update
        if (!$mod){ return (false); }

        // Do the update
        $result=ldap_modify($this->_conn,$distinguishedname,$mod);
        if ($result==false){ return (false); }

        return (true);
    }

    /**
    * Returns a list of Exchange Servers in the ConfigurationNamingContext of the domain
    *
    * @param array $attributes An array of the AD attributes you wish to return
    * @return array
    */
    public function exchange_servers($attributes = array('cn','distinguishedname','serialnumber')) {
        if (!$this->_bind){ return (false); }

        $configurationNamingContext = $this->get_root_dse(array('configurationnamingcontext'));
        $sr = @ldap_search($this->_conn,$configurationNamingContext[0]['configurationnamingcontext'][0],'(&(objectCategory=msExchExchangeServer))',$attributes);
        $entries = @ldap_get_entries($this->_conn, $sr);
        return $entries;
    }

    /**
    * Returns a list of Storage Groups in Exchange for a given mail server
    *
    * @param string $exchangeServer The full DN of an Exchange server.  You can use exchange_servers() to find the DN for your server
    * @param array $attributes An array of the AD attributes you wish to return
    * @param bool $recursive If enabled this will automatically query the databases within a storage group
    * @return array
    */
    public function exchange_storage_groups($exchangeServer, $attributes = array('cn','distinguishedname'), $recursive = NULL) {
        if (!$this->_bind){ return (false); }
        if ($exchangeServer===NULL){ return ("Missing compulsory field [exchangeServer]"); }
        if ($recursive===NULL){ $recursive=$this->_recursive_groups; }

        $filter = '(&(objectCategory=msExchStorageGroup))';
        $sr=@ldap_search($this->_conn, $exchangeServer, $filter, $attributes);
        $entries = @ldap_get_entries($this->_conn, $sr);

        if ($recursive === true) {
            for ($i=0; $i<$entries['count']; $i++) {
                $entries[$i]['msexchprivatemdb'] = $this->exchange_storage_databases($entries[$i]['distinguishedname'][0]);
            }
        }

        return $entries;
    }

    /**
    * Returns a list of Databases within any given storage group in Exchange for a given mail server
    *
    * @param string $storageGroup The full DN of an Storage Group.  You can use exchange_storage_groups() to find the DN
    * @param array $attributes An array of the AD attributes you wish to return
    * @return array
    */
    public function exchange_storage_databases($storageGroup, $attributes = array('cn','distinguishedname','displayname')) {
        if (!$this->_bind){ return (false); }
        if ($storageGroup===NULL){ return ("Missing compulsory field [storageGroup]"); }

        $filter = '(&(objectCategory=msExchPrivateMDB))';
        $sr=@ldap_search($this->_conn, $storageGroup, $filter, $attributes);
        $entries = @ldap_get_entries($this->_conn, $sr);
        return $entries;
    }

    //************************************************************************************************************
    // SERVER FUNCTIONS

    /**
    * Find the Base DN of your domain controller
    *
    * @return string
    */
    public function find_base_dn() {
        $namingContext = $this->get_root_dse(array('defaultnamingcontext'));
        return $namingContext[0]['defaultnamingcontext'][0];
    }

    /**
    * Get the RootDSE properties from a domain controller
    *
    * @param array $attributes The attributes you wish to query e.g. defaultnamingcontext
    * @return array
    */
    public function get_root_dse($attributes = array("*", "+")) {
        if (!$this->_bind){ return (false); }

        $sr = @ldap_read($this->_conn, NULL, 'objectClass=*', $attributes);
        $entries = @ldap_get_entries($this->_conn, $sr);
        return $entries;
    }

    //************************************************************************************************************
    // UTILITY FUNCTIONS (Many of these functions are protected and can only be called from within the class)

    /**
    * Get last error from Active Directory
    *
    * This function gets the last message from Active Directory
    * This may indeed be a 'Success' message but if you get an unknown error
    * it might be worth calling this function to see what errors were raised
    *
    * return string
    */
    public function get_last_error() {
        return @ldap_error($this->_conn);
    }

    /**
    * Detect LDAP support in php
    *
    * @return bool
    */
    protected function ldap_supported() {
        if (!function_exists('ldap_connect')) {
            return (false);
        }
        return (true);
    }

    /**
    * Schema
    *
    * @param array $attributes Attributes to be queried
    * @return array
    */
    protected function adldap_schema($attributes){

        // LDAP doesn't like NULL attributes, only set them if they have values
        // If you wish to remove an attribute you should set it to a space
        // TO DO: Adapt user_modify to use ldap_mod_delete to remove a NULL attribute
        $mod=array();

        // Check every attribute to see if it contains 8bit characters and then UTF8 encode them
        array_walk($attributes, array($this, 'encode8bit'));

        if ($attributes["address_city"]){ $mod["l"][0]=$attributes["address_city"]; }
        if ($attributes["address_code"]){ $mod["postalCode"][0]=$attributes["address_code"]; }
        //if ($attributes["address_country"]){ $mod["countryCode"][0]=$attributes["address_country"]; } // use country codes?
        if ($attributes["address_country"]){ $mod["c"][0]=$attributes["address_country"]; }
        if ($attributes["address_pobox"]){ $mod["postOfficeBox"][0]=$attributes["address_pobox"]; }
        if ($attributes["address_state"]){ $mod["st"][0]=$attributes["address_state"]; }
        if ($attributes["address_street"]){ $mod["streetAddress"][0]=$attributes["address_street"]; }
        if ($attributes["company"]){ $mod["company"][0]=$attributes["company"]; }
        if ($attributes["change_password"]){ $mod["pwdLastSet"][0]=0; }
        if ($attributes["department"]){ $mod["department"][0]=$attributes["department"]; }
        if ($attributes["description"]){ $mod["description"][0]=$attributes["description"]; }
        if ($attributes["display_name"]){ $mod["displayName"][0]=$attributes["display_name"]; }
        if ($attributes["email"]){ $mod["mail"][0]=$attributes["email"]; }
        if ($attributes["expires"]){ $mod["accountExpires"][0]=$attributes["expires"]; } //unix epoch format?
        if ($attributes["firstname"]){ $mod["givenName"][0]=$attributes["firstname"]; }
        if ($attributes["home_directory"]){ $mod["homeDirectory"][0]=$attributes["home_directory"]; }
        if ($attributes["home_drive"]){ $mod["homeDrive"][0]=$attributes["home_drive"]; }
        if ($attributes["initials"]){ $mod["initials"][0]=$attributes["initials"]; }
        if ($attributes["logon_name"]){ $mod["userPrincipalName"][0]=$attributes["logon_name"]; }
        if ($attributes["manager"]){ $mod["manager"][0]=$attributes["manager"]; }  //UNTESTED ***Use DistinguishedName***
        if ($attributes["office"]){ $mod["physicalDeliveryOfficeName"][0]=$attributes["office"]; }
        if ($attributes["password"]){ $mod["unicodePwd"][0]=$this->encode_password($attributes["password"]); }
        if ($attributes["profile_path"]){ $mod["profilepath"][0]=$attributes["profile_path"]; }
        if ($attributes["script_path"]){ $mod["scriptPath"][0]=$attributes["script_path"]; }
        if ($attributes["surname"]){ $mod["sn"][0]=$attributes["surname"]; }
        if ($attributes["title"]){ $mod["title"][0]=$attributes["title"]; }
        if ($attributes["telephone"]){ $mod["telephoneNumber"][0]=$attributes["telephone"]; }
        if ($attributes["mobile"]){ $mod["mobile"][0]=$attributes["mobile"]; }
        if ($attributes["pager"]){ $mod["pager"][0]=$attributes["pager"]; }
        if ($attributes["ipphone"]){ $mod["ipphone"][0]=$attributes["ipphone"]; }
        if ($attributes["web_page"]){ $mod["wWWHomePage"][0]=$attributes["web_page"]; }
        if ($attributes["fax"]){ $mod["facsimileTelephoneNumber"][0]=$attributes["fax"]; }
        if ($attributes["enabled"]){ $mod["userAccountControl"][0]=$attributes["enabled"]; }

        // Distribution List specific schema
        if ($attributes["group_sendpermission"]){ $mod["dlMemSubmitPerms"][0]=$attributes["group_sendpermission"]; }
        if ($attributes["group_rejectpermission"]){ $mod["dlMemRejectPerms"][0]=$attributes["group_rejectpermission"]; }

        // Exchange Schema
        if ($attributes["exchange_homemdb"]){ $mod["homeMDB"][0]=$attributes["exchange_homemdb"]; }
        if ($attributes["exchange_mailnickname"]){ $mod["mailNickname"][0]=$attributes["exchange_mailnickname"]; }
        if ($attributes["exchange_proxyaddress"]){ $mod["proxyAddresses"][0]=$attributes["exchange_proxyaddress"]; }
        if ($attributes["exchange_usedefaults"]){ $mod["mDBUseDefaults"][0]=$attributes["exchange_usedefaults"]; }
        if ($attributes["exchange_policyexclude"]){ $mod["msExchPoliciesExcluded"][0]=$attributes["exchange_policyexclude"]; }
        if ($attributes["exchange_policyinclude"]){ $mod["msExchPoliciesIncluded"][0]=$attributes["exchange_policyinclude"]; }
        if ($attributes["exchange_addressbook"]){ $mod["showInAddressBook"][0]=$attributes["exchange_addressbook"]; }

        // This schema is designed for contacts
        if ($attributes["exchange_hidefromlists"]){ $mod["msExchHideFromAddressLists"][0]=$attributes["exchange_hidefromlists"]; }
        if ($attributes["contact_email"]){ $mod["targetAddress"][0]=$attributes["contact_email"]; }

        //echo ("<pre>"); print_r($mod);
        /*
        // modifying a name is a bit fiddly
        if ($attributes["firstname"] && $attributes["surname"]){
            $mod["cn"][0]=$attributes["firstname"]." ".$attributes["surname"];
            $mod["displayname"][0]=$attributes["firstname"]." ".$attributes["surname"];
            $mod["name"][0]=$attributes["firstname"]." ".$attributes["surname"];
        }
        */

        if (count($mod)==0){ return (false); }
        return ($mod);
    }

    /**
    * Coping with AD not returning the primary group
    * http://support.microsoft.com/?kbid=321360
    *
    * For some reason it's not possible to search on primarygrouptoken=XXX
    * If someone can show otherwise, I'd like to know about it :)
    * this way is resource intensive and generally a pain in the @#%^
    *
    * @deprecated deprecated since version 3.1, see get get_primary_group
    * @param string $gid Group ID
    * @return string
    */
    protected function group_cn($gid){
        if ($gid===NULL){ return (false); }
        $r=false;

        $filter="(&(objectCategory=group)(samaccounttype=". ADLDAP_SECURITY_GLOBAL_GROUP ."))";
        $fields=array("primarygrouptoken","samaccountname","distinguishedname");
        $sr=ldap_search($this->_conn,$this->_base_dn,$filter,$fields);
        $entries = ldap_get_entries($this->_conn, $sr);

        for ($i=0; $i<$entries["count"]; $i++){
            if ($entries[$i]["primarygrouptoken"][0]==$gid){
                $r=$entries[$i]["distinguishedname"][0];
                $i=$entries["count"];
            }
        }

        return ($r);
    }

    /**
    * Coping with AD not returning the primary group
    * http://support.microsoft.com/?kbid=321360
    *
    * This is a re-write based on code submitted by Bruce which prevents the
    * need to search each security group to find the true primary group
    *
    * @param string $gid Group ID
    * @param string $usersid User's Object SID
    * @return string
    */
    protected function get_primary_group($gid, $usersid){
        if ($gid===NULL || $usersid===NULL){ return (false); }
        $r=false;

        $gsid = substr_replace($usersid,pack('V',$gid),strlen($usersid)-4,4);
        $filter='(objectsid='.$this->getTextSID($gsid).')';
        $fields=array("samaccountname","distinguishedname");
        $sr=ldap_search($this->_conn,$this->_base_dn,$filter,$fields);
        $entries = ldap_get_entries($this->_conn, $sr);

        return $entries[0]['distinguishedname'][0];
     }

    /**
    * Convert a binary SID to a text SID
    *
    * @param string $binsid A Binary SID
    * @return string
    */
     protected function getTextSID($binsid) {
        $hex_sid = bin2hex($binsid);
        $rev = hexdec(substr($hex_sid, 0, 2));
        $subcount = hexdec(substr($hex_sid, 2, 2));
        $auth = hexdec(substr($hex_sid, 4, 12));
        $result = "$rev-$auth";

        for ($x=0;$x < $subcount; $x++) {
            $subauth[$x] =
                hexdec($this->little_endian(substr($hex_sid, 16 + ($x * 8), 8)));
                $result .= "-" . $subauth[$x];
        }

        // Cheat by tacking on the S-
        return 'S-' . $result;
     }

    /**
    * Converts a little-endian hex number to one that hexdec() can convert
    *
    * @param string $hex A hex code
    * @return string
    */
     protected function little_endian($hex) {
        $result = '';
        for ($x = strlen($hex) - 2; $x >= 0; $x = $x - 2) {
            $result .= substr($hex, $x, 2);
        }
        return $result;
     }

    /**
    * Converts a binary attribute to a string
    *
    * @param string $bin A binary LDAP attribute
    * @return string
    */
    protected function binary2text($bin) {
        $hex_guid = bin2hex($bin);
        $hex_guid_to_guid_str = '';
        for($k = 1; $k <= 4; ++$k) {
            $hex_guid_to_guid_str .= substr($hex_guid, 8 - 2 * $k, 2);
        }
        $hex_guid_to_guid_str .= '-';
        for($k = 1; $k <= 2; ++$k) {
            $hex_guid_to_guid_str .= substr($hex_guid, 12 - 2 * $k, 2);
        }
        $hex_guid_to_guid_str .= '-';
        for($k = 1; $k <= 2; ++$k) {
            $hex_guid_to_guid_str .= substr($hex_guid, 16 - 2 * $k, 2);
        }
        $hex_guid_to_guid_str .= '-' . substr($hex_guid, 16, 4);
        $hex_guid_to_guid_str .= '-' . substr($hex_guid, 20);
        return strtoupper($hex_guid_to_guid_str);
    }

    /**
    * Converts a binary GUID to a string GUID
    *
    * @param string $binaryGuid The binary GUID attribute to convert
    * @return string
    */
    public function decodeGuid($binaryGuid) {
        if ($binaryGuid === null){ return ("Missing compulsory field [binaryGuid]"); }

        $strGUID = $this->binary2text($binaryGuid);
        return ($strGUID);
    }

    /**
    * Converts a string GUID to a hexdecimal value so it can be queried
    *
    * @param string $strGUID A string representation of a GUID
    * @return string
    */
    protected function strguid2hex($strGUID) {
        $strGUID = str_replace('-', '', $strGUID);

        $octet_str = '\\' . substr($strGUID, 6, 2);
        $octet_str .= '\\' . substr($strGUID, 4, 2);
        $octet_str .= '\\' . substr($strGUID, 2, 2);
        $octet_str .= '\\' . substr($strGUID, 0, 2);
        $octet_str .= '\\' . substr($strGUID, 10, 2);
        $octet_str .= '\\' . substr($strGUID, 8, 2);
        $octet_str .= '\\' . substr($strGUID, 14, 2);
        $octet_str .= '\\' . substr($strGUID, 12, 2);
        //$octet_str .= '\\' . substr($strGUID, 16, strlen($strGUID));
        for ($i=16; $i<=(strlen($strGUID)-2); $i++) {
            if (($i % 2) == 0) {
                $octet_str .= '\\' . substr($strGUID, $i, 2);
            }
        }

        return $octet_str;
    }

    /**
    * Obtain the user's distinguished name based on their userid
    *
    *
    * @param string $username The username
    * @param bool $isGUID Is the username passed a GUID or a samAccountName
    * @return string
    */
    protected function user_dn($username,$isGUID=false){
        $user=$this->user_info($username,array("cn"),$isGUID);
        if ($user[0]["dn"]===NULL){ return (false); }
        $user_dn=$user[0]["dn"];
        return ($user_dn);
    }

    /**
    * Encode a password for transmission over LDAP
    *
    * @param string $password The password to encode
    * @return string
    */
    protected function encode_password($password){
        $password="\"".$password."\"";
        $encoded="";
        for ($i=0; $i <strlen($password); $i++){ $encoded.="{$password{$i}}\000"; }
        return ($encoded);
    }

    /**
    * Escape strings for the use in LDAP filters
    *
    * DEVELOPERS SHOULD BE DOING PROPER FILTERING IF THEY'RE ACCEPTING USER INPUT
    * Ported from Perl's Net::LDAP::Util escape_filter_value
    *
    * @param string $str The string the parse
    * @author Port by Andreas Gohr <andi@splitbrain.org>
    * @return string
    */
    protected function ldap_slashes($str){
        return preg_replace('/([\x00-\x1F\*\(\)\\\\])/e',
                            '"\\\\\".join("",unpack("H2","$1"))',
                            $str);
    }

    /**
    * Select a random domain controller from your domain controller array
    *
    * @return string
    */
    protected function random_controller(){
        mt_srand(doubleval(microtime()) * 100000000); // For older PHP versions
        return ($this->_domain_controllers[array_rand($this->_domain_controllers)]);
    }

    /**
    * Account control options
    *
    * @param array $options The options to convert to int
    * @return int
    */
    protected function account_control($options){
        $val=0;

        if (is_array($options)){
            if (in_array("SCRIPT",$options)){ $val=$val+1; }
            if (in_array("ACCOUNTDISABLE",$options)){ $val=$val+2; }
            if (in_array("HOMEDIR_REQUIRED",$options)){ $val=$val+8; }
            if (in_array("LOCKOUT",$options)){ $val=$val+16; }
            if (in_array("PASSWD_NOTREQD",$options)){ $val=$val+32; }
            //PASSWD_CANT_CHANGE Note You cannot assign this permission by directly modifying the UserAccountControl attribute.
            //For information about how to set the permission programmatically, see the "Property flag descriptions" section.
            if (in_array("ENCRYPTED_TEXT_PWD_ALLOWED",$options)){ $val=$val+128; }
            if (in_array("TEMP_DUPLICATE_ACCOUNT",$options)){ $val=$val+256; }
            if (in_array("NORMAL_ACCOUNT",$options)){ $val=$val+512; }
            if (in_array("INTERDOMAIN_TRUST_ACCOUNT",$options)){ $val=$val+2048; }
            if (in_array("WORKSTATION_TRUST_ACCOUNT",$options)){ $val=$val+4096; }
            if (in_array("SERVER_TRUST_ACCOUNT",$options)){ $val=$val+8192; }
            if (in_array("DONT_EXPIRE_PASSWORD",$options)){ $val=$val+65536; }
            if (in_array("MNS_LOGON_ACCOUNT",$options)){ $val=$val+131072; }
            if (in_array("SMARTCARD_REQUIRED",$options)){ $val=$val+262144; }
            if (in_array("TRUSTED_FOR_DELEGATION",$options)){ $val=$val+524288; }
            if (in_array("NOT_DELEGATED",$options)){ $val=$val+1048576; }
            if (in_array("USE_DES_KEY_ONLY",$options)){ $val=$val+2097152; }
            if (in_array("DONT_REQ_PREAUTH",$options)){ $val=$val+4194304; }
            if (in_array("PASSWORD_EXPIRED",$options)){ $val=$val+8388608; }
            if (in_array("TRUSTED_TO_AUTH_FOR_DELEGATION",$options)){ $val=$val+16777216; }
        }
        return ($val);
    }

    /**
    * Take an LDAP query and return the nice names, without all the LDAP prefixes (eg. CN, DN)
    *
    * @param array $groups
    * @return array
    */
    protected function nice_names($groups){

        $group_array=array();
        for ($i=0; $i<$groups["count"]; $i++){ // For each group
            $line=$groups[$i];

            if (strlen($line)>0){
                // More presumptions, they're all prefixed with CN=
                // so we ditch the first three characters and the group
                // name goes up to the first comma
                $bits=explode(",",$line);
                $group_array[]=substr($bits[0],3,(strlen($bits[0])-3));
            }
        }
        return ($group_array);
    }

    /**
    * Delete a distinguished name from Active Directory
    * You should never need to call this yourself, just use the wrapper functions user_delete and contact_delete
    *
    * @param string $dn The distinguished name to delete
    * @return bool
    */
    protected function dn_delete($dn){
        $result=ldap_delete($this->_conn, $dn);
        if ($result!=true){ return (false); }
        return (true);
    }

    /**
    * Convert a boolean value to a string
    * You should never need to call this yourself
    *
    * @param bool $bool Boolean value
    * @return string
    */
    protected function bool2str($bool) {
        return ($bool) ? 'TRUE' : 'FALSE';
    }

    /**
    * Convert 8bit characters e.g. accented characters to UTF8 encoded characters
    */
    protected function encode8bit(&$item, $key) {
        $encode = false;
        if (is_string($item)) {
            for ($i=0; $i<strlen($item); $i++) {
                if (ord($item[$i]) >> 7) {
                    $encode = true;
                }
            }
        }
        if ($encode === true && $key != 'password') {
            $item = utf8_encode($item);
        }
    }
}

/**
* adLDAP Exception Handler
*
* Exceptions of this type are thrown on bind failure or when SSL is required but not configured
* Example:
* try {
*   $adldap = new adLDAP();
* }
* catch (adLDAPException $e) {
*   echo $e;
*   exit();
* }
*/
class adLDAPException extends Exception {}

?>
