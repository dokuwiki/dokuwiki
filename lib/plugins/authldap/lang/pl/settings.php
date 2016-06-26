<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Paweł Jan Czochański <czochanski@gmail.com>
 * @author Maciej Helt <geraldziu@gmail.com>
 */
$lang['server']                = 'Twój serwer LDAP. Podaj nazwę hosta (<code>localhost</code>) albo pełen adres URL (<code>ldap://server.tld:389</code>).';
$lang['port']                  = 'Port serwera LDAP jeżeli nie podano pełnego adresu URL wyżej.';
$lang['usertree']              = 'Gdzie szukać kont użytkownika? np. <code>ou=People, dc=server, dc=tld</code>';
$lang['grouptree']             = 'Gdzie szukać grup użytkowników? np. <code>ou=Group, dc=server, dc=tld</code>';
$lang['userfilter']            = 'Filtr LDAP wykorzystany przy szukaniu kont użytkowników np. <code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>';
$lang['groupfilter']           = 'Filtr LDAP wykorzystany przy szukaniu grup użytkowników np. <code>(&amp;(objectClass=posixGroup)(|(gidNumber=%{gid})(memberUID=%{user})))</code>';
$lang['version']               = 'Wykorzystywana wersja protokołu. Być może konieczne jest ustawienie tego na <code>3</code>.';
$lang['starttls']              = 'Użyć połączeń TLS?';
$lang['bindpw']                = 'Hasło powyższego użytkownika';
$lang['debug']                 = 'Przy błędach wyświetl dodatkowe informacje debugujące.';
$lang['deref_o_0']             = 'LDAP_DEREF_NEVER';
$lang['deref_o_1']             = 'LDAP_DEREF_SEARCHING';
$lang['deref_o_2']             = 'LDAP_DEREF_FINDING';
$lang['deref_o_3']             = 'LDAP_DEREF_ALWAYS';
