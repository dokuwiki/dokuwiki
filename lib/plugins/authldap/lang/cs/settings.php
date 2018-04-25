<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author mkucera66 <mkucera66@seznam.cz>
 * @author Jaroslav Lichtblau <jlichtblau@seznam.cz>
 * @author Martin Růžička <martinr@post.cz>
 */
$lang['server']                = 'Váš server LDAP. Buď jméno hosta (<code>localhost</code>) nebo plně kvalifikovaný popis URL (<code>ldap://server.tld:389</code>)';
$lang['port']                  = 'Port serveru LDAP. Pokud není, bude využito URL výše';
$lang['usertree']              = 'Kde najít uživatelské účty, tj. <code>ou=Lide, dc=server, dc=tld</code>';
$lang['grouptree']             = 'Kde najít uživatelské skupiny, tj. <code>ou=Skupina, dc=server, dc=tld</code>';
$lang['userfilter']            = 'Filtr LDAPu pro vyhledávání uživatelských účtů, tj. <code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>';
$lang['groupfilter']           = 'Filtr LDAPu pro vyhledávání uživatelských skupin, tj. <code>(&amp;(objectClass=posixGroup)(|(gidNumber=%{gid})(memberUID=%{user})))</code>';
$lang['version']               = 'Verze použitého protokolu. Můžete potřebovat jej nastavit na <code>3</code>';
$lang['starttls']              = 'Využít spojení TLS?';
$lang['referrals']             = 'Přeposílat odkazy?';
$lang['deref']                 = 'Jak rozlišovat aliasy?';
$lang['binddn']                = 'Doménový název DN volitelně připojeného uživatele, pokus anonymní připojení není vyhovující, tj.  <code>cn=admin, dc=muj, dc=domov</code>';
$lang['bindpw']                = 'Heslo uživatele výše';
$lang['userscope']             = 'Omezení rozsahu vyhledávání uživatele';
$lang['groupscope']            = 'Omezení rozsahu vyhledávání skupiny';
$lang['userkey']               = 'Atribut označující uživatelské jméno; musí být konzistetní s uživatelským filtrem.';
$lang['groupkey']              = 'Atribut členství uživatele ve skupinách (namísto standardních AD skupin), tj. skupina z oddělení nebo telefonní číslo';
$lang['modPass']               = 'Může být LDAP heslo změněno přes dokuwiki?';
$lang['debug']                 = 'Zobrazit dodatečné debugovací informace';
$lang['deref_o_0']             = 'LDAP_DEREF_NEVER';
$lang['deref_o_1']             = 'LDAP_DEREF_SEARCHING';
$lang['deref_o_2']             = 'LDAP_DEREF_FINDING';
$lang['deref_o_3']             = 'LDAP_DEREF_ALWAYS';
$lang['referrals_o_-1']        = 'použít výchozí';
$lang['referrals_o_0']         = 'nenásledovat odkazy';
$lang['referrals_o_1']         = 'následovat odkazy';
