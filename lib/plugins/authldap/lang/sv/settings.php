<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Gorm <gorm.chandler@gmail.com>
 * @author Patrik K Lundberg <patrik.kotiranta.lundberg@gmail.com>
 * @author Tor Härnqvist <tor@harnqvist.se>
 * @author Smorkster Andersson <smorkster@gmail.com>
 */
$lang['server']                = 'Din LDAP server. Antingen värdnamn (<code>localhost</code>) eller giltig full URL (<code>ldap://server.tld:389</code>)';
$lang['port']                  = 'LDAP server port, om det inte angavs full URL ovan';
$lang['usertree']              = 'Specificera var användarkonton finns. T.ex. <code>ou=Användare, dc=server, dc=tld</code>';
$lang['grouptree']             = 'Specificera var grupper finns. T.ex. <code>ou=Grupp, dc=server, dc=tld</code>';
$lang['userfilter']            = 'LDAP filter för att söka efter användarkonton. T.ex. <code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>';
$lang['groupfilter']           = 'LDAP filter för att söka efter grupper. T.ex. <code>(&amp;(objectClass=posixGroup)(|(gidNumber=%{gid})(memberUID=%{user})))</code>';
$lang['version']               = 'Version av protokoll att använda. Du kan behöva sätta detta till <code>3</code>';
$lang['starttls']              = 'Använd TLS-anslutningar';
$lang['referrals']             = 'Senaste månaden';
$lang['deref']                 = 'Senaste året';
$lang['binddn']                = 'Sortera efter träffar';
$lang['bindpw']                = 'Lösenord för användare ovan';
$lang['attributes']            = 'Sortera efter senast modifierad';
$lang['userscope']             = 'Begränsa sökomfattning för användarsökning';
$lang['groupscope']            = 'Begränsa sökomfattning för gruppsökning';
$lang['userkey']               = 'Lista av alla tillåtna extensions';
$lang['groupkey']              = 'Gruppmedlemskap från något användarattribut (istället för standard AD grupp) t.ex. grupp från avdelning eller telefonnummer';
$lang['modPass']               = 'Får LDAP-lösenordet ändras via DokuWiki?';
$lang['debug']                 = 'Visa ytterligare felsökningsinformation vid fel';
$lang['deref_o_0']             = 'LDAP_DEREF_NEVER';
$lang['deref_o_1']             = 'LDAP_DEREF_SEARCHING';
$lang['deref_o_2']             = 'LDAP_DEREF_FINDING';
$lang['deref_o_3']             = 'LDAP_DEREF_ALWAYS';
$lang['referrals_o_-1']        = 'använd standard';
