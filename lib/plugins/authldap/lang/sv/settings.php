<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Smorkster Andersson smorkster@gmail.com
 * @author Tor Härnqvist <tor.harnqvist@gmail.com>
 */
$lang['server']                = 'Din LDAO server. Antingen värdnamn (<code>localhost</code>) eller giltig full URL (<code>ldap://server.tld:389</code>)';
$lang['port']                  = 'LDAP server port, om det inte angavs full URL ovan';
$lang['usertree']              = 'Specificera var användarkonton finns. T.ex. <code>ou=Användare, dc=server, dc=tld</code>';
$lang['grouptree']             = 'Specificera var grupper finns. T.ex. <code>ou=Grupp, dc=server, dc=tld</code>';
$lang['userfilter']            = 'LDAP filter för att söka efter användarkonton. T.ex. <code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>';
$lang['groupfilter']           = 'LDAP filter för att söka efter grupper. T.ex. <code>(&amp;(objectClass=posixGroup)(|(gidNumber=%{gid})(memberUID=%{user})))</code>';
$lang['version']               = 'Version av protokoll att använda. Du kan behöva sätta detta till <code>3</code>';
$lang['starttls']              = 'Använd TLS-anslutningar';
$lang['bindpw']                = 'Lösenord för användare ovan';
$lang['groupkey']              = 'Gruppmedlemskap från något användarattribut (istället för standard AD grupp) t.ex. grupp från avdelning eller telefonnummer';
$lang['debug']                 = 'Visa ytterligare felsökningsinformation vid fel';
