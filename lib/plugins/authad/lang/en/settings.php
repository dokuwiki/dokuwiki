<?php

$lang['account_suffix']     = 'Your account suffix. Eg. <code>@my.domain.org</code>';
$lang['base_dn']            = 'Your base DN. Eg. <code>DC=my,DC=domain,DC=org</code>';
$lang['domain_controllers'] = 'A comma separated list of Domain controllers. Eg. <code>srv1.domain.org,srv2.domain.org</code>';
$lang['admin_username']     = 'A privileged Active Directory user with access to all other user\'s data. Optional, but needed for certain actions like sending subscription mails.';
$lang['admin_password']     = 'The password of the above user.';
$lang['sso']                = 'Should Single-Sign-On via Kerberos or NTLM be used?';
$lang['sso_charset']        = 'The charset your webserver will pass the Kerberos or NTLM username in. Empty for UTF-8 or latin-1. Requires the iconv extension.';
$lang['real_primarygroup']  = 'Should the real primary group be resolved instead of assuming "Domain Users" (slower).';
$lang['use_ssl']            = 'Use SSL connection? If used, do not enable TLS below.';
$lang['use_tls']            = 'Use TLS connection? If used, do not enable SSL above.';
$lang['debug']              = 'Display additional debugging output on errors?';
$lang['expirywarn']         = 'Days in advance to warn user about expiring password. 0 to disable.';
$lang['additional']         = 'A comma separated list of additional AD attributes to fetch from user data. Used by some plugins.';
$lang['admin_features']     = 'A comma separated list of administration feature overrides.<br/> Allowed settings: '.
	'<code>addUser (-**), delUser (-**), modLogin (-), modPass (+*), modName (+), modMail (+), modGroups (-*), getUsers (+**), getUserCount (+*), getGroups (+**), external (-*), logout (-*)</code>,'.
	'all case sensitive (defaults as "+"/"-").<br/>'.
	'Some settings should not be changed (marked with **) others depend on environment settings (marked with *).<br/>'.
	'Use "+" and "-" to enable/disable features e. g. <code>+modPass, -modName, -modMail</code>';
$lang['doku_signin']		= 'Automatically sign in users authed by HTTP NTML/kerberos negotiation. Alternative to "sso".';
$lang['user_caching']		= 'Enable user caching using cachewrapper plugin.';
$lang['user_caching_ttl']	= 'Cache TTL in seconds.  Set to 0 to disable TTL (not recommended for AD).';
