<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Marton Sebok <sebokmarton@gmail.com>
 * @author Marina Vladi <deldadam@gmail.com>
 */
$lang['server']                = 'LDAP-szerver. Kiszolgálónév (<code>localhost</code>) vagy teljes URL-cím (<code>ldap://server.tld:389</code>)';
$lang['port']                  = 'LDAP-kiszolgáló portja, ha URL-cím nem lett megadva';
$lang['usertree']              = 'Hol találom a felhasználókat? Pl. <code>ou=People, dc=server, dc=tld</code>';
$lang['grouptree']             = 'Hol találom a csoportokat? Pl. <code>ou=Group, dc=server, dc=tld</code>';
$lang['userfilter']            = 'LDAP szűrő a felhasználók kereséséhez, pl. <code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>';
$lang['groupfilter']           = 'LDAP szűrő a csoportok kereséséhez, pl. <code>(&amp;(objectClass=posixGroup)(|(gidNumber=%{gid})(memberUID=%{user})))</code>';
$lang['version']               = 'A használt protokollverzió. Valószínűleg a <code>3</code> megfelelő';
$lang['starttls']              = 'TLS használata?';
$lang['referrals']             = 'Hivatkozások követése?';
$lang['deref']                 = 'Hogyan fejtsük vissza az aliasokat?';
$lang['binddn']                = 'Egy hozzáféréshez használt felhasználó DN-je, ha nincs névtelen hozzáférés. Pl. <code>cn=admin, dc=my, dc=home</code>';
$lang['bindpw']                = 'Ehhez tartozó jelszó.';
$lang['userscope']             = 'A keresési tartomány korlátozása erre a felhasználókra való keresésnél';
$lang['groupscope']            = 'A keresési tartomány korlátozása erre a csoportokra való keresésnél';
$lang['userkey']               = 'A felhasználónevet leíró attribútum; konzisztensnek kell lennie a felhasználói szűrővel (userfilter).';
$lang['groupkey']              = 'Csoport meghatározása a következő attribútumból (az alapértelmezett AD csoporttagság helyett), pl. a szervezeti egység vagy a telefonszám';
$lang['modPass']               = 'Az LDAP jelszó megváltoztatható a DokuWiki-n keresztül?';
$lang['debug']                 = 'Továbi hibakeresési információk megjelenítése hiba esetén';
$lang['deref_o_0']             = 'LDAP_DEREF_NEVER';
$lang['deref_o_1']             = 'LDAP_DEREF_SEARCHING';
$lang['deref_o_2']             = 'LDAP_DEREF_FINDING';
$lang['deref_o_3']             = 'LDAP_DEREF_ALWAYS';
$lang['referrals_o_-1']        = 'alapértelmezett érték használata';
$lang['referrals_o_0']         = 'ne kövesse az átirányításokat (referral)';
$lang['referrals_o_1']         = 'kövesse az átirányításokat (referral)';
