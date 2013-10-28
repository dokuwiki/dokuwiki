<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author mkucera66@seznam.cz
 */
$lang['server']                = 'Váš server LDAP. Buď jméno hosta (<code>localhost</code>) nebo plně kvalifikovaný popis URL (<code>ldap://server.tld:389</code>)';
$lang['port']                  = 'Port serveru LDAP. Pokud není, bude využito URL výše';
$lang['usertree']              = 'Kde najít uživatelské účty, tj. <code>ou=Lide, dc=server, dc=tld</code>';
$lang['grouptree']             = 'Kde najít uživatelské skupiny, tj. <code>ou=Skupina, dc=server, dc=tld</code>';
$lang['userfilter']            = 'Filter LDAPu pro vyhledávání uživatelských účtů, tj. <code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>';
$lang['groupfilter']           = 'Filter LDAPu pro vyhledávání uživatelských skupin, tj. <code>(&amp;(objectClass=posixGroup)(|(gidNumber=%{gid})(memberUID=%{user})))</code>';
$lang['version']               = 'Verze použitého protokolu. Můžete potřebovat jej nastavit na <code>3</code>';
$lang['starttls']              = 'Využít spojení TLS?';
$lang['referrals']             = 'Přeposílat odkazy?';
$lang['binddn']                = 'Doménový název DN volitelně připojeného uživatele, pokus anonymní připojení není vyhovující, tj.  <code>cn=admin, dc=muj, dc=domov</code>';
$lang['bindpw']                = 'Heslo uživatele výše';
$lang['userscope']             = 'Omezení rozsahu vyhledávání uživatele';
$lang['groupscope']            = 'Omezení rozsahu vyhledávání skupiny';
$lang['groupkey']              = 'Atribut šlenství uživatele ve skupinách (namísto standardních AD skupin), tj. skupina z oddělení nebo telefonní číslo';
$lang['debug']                 = 'Zobrazit dodatečné debugovací informace';
