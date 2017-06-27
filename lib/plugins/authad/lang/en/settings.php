<?php

$lang['account_suffix']     = 'Your account suffix. Eg. <code>@my.domain.org</code>';
$lang['base_dn']            = 'Your base DN. Eg. <code>DC=my,DC=domain,DC=org</code>';
$lang['domain_controllers'] = 'A comma separated list of Domain controllers. Eg. <code>srv1.domain.org,srv2.domain.org</code>';
$lang['admin_username']     = 'A privileged Active Directory user with access to all other user\'s data. Optional, but needed for certain actions like sending subscription mails.';
$lang['admin_password']     = 'The password of the above user.';
$lang['admin_account_prefix']  = 'A prefix to prepend to the admins username.';
$lang['admin_account_suffix']  = 'A suffix that is appended on the admins username. This suffix will override the account suffix.';
$lang['sso']                = 'Should Single-Sign-On via Kerberos or NTLM be used?';
$lang['sso_charset']        = 'The charset your webserver will pass the Kerberos or NTLM username in. Empty for UTF-8 or latin-1. Requires the iconv extension.';
$lang['real_primarygroup']  = 'Should the real primary group be resolved instead of assuming "Domain Users" (slower).';
$lang['use_ssl']            = 'Use SSL connection? If used, do not enable TLS below.';
$lang['use_tls']            = 'Use TLS connection? If used, do not enable SSL above.';
$lang['debug']              = 'Display additional debugging output on errors?';
$lang['expirywarn']         = 'Days in advance to warn user about expiring password. 0 to disable.';
$lang['additional']         = 'A comma separated list of additional AD attributes to fetch from user data. Used by some plugins.';
$lang['update_name']        = 'Allow users to update their AD display name?';
$lang['update_mail']        = 'Allow users to update their email address?';
$lang['option_deref']       = 'Specifice alternative rules to follow aliases on the server.';
$lang['option_sizelimit']   =  'Specifice the maximal size of entries to be returned on a search.';
$lang['option_timelimit']   =  'Specifice the time in seconds to wait for search results.';
$lang['option_network_timeout'] =  'Sets the network timeout (since PHP 5.3.0+).';
$lang['option_error_number'] =  'Used to return an error code associated with the most recent LDAP error.';
$lang['option_restart']     =  'Determines whether the library should implicitly restart connections.';
$lang['option_hostname']    = 'Sets a space-separated list of hosts to be contacted by the library when trying to establish a connection.';
$lang['option_error_string'] =  'Sets a string containing the error string associated to the LDAP handle.';
$lang['option_matched_dn']  = 'Sets a string containing the matched DN associated to the LDAP handle.';