<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Wojciech Lichota <wojciech@lichota.pl>
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
$lang['referrals']             = 'Czy należy podążać za przekierowaniami?';
$lang['deref']                 = 'Jak rozwiązywać aliasy?';
$lang['binddn']                = 'DN opcjonalnego użytkownika powiązanego, jeśli powiązanie anonimowe nie jest wystarczające, np. <code>cn=admin, dc=my, dc=home</code>';
$lang['bindpw']                = 'Hasło powyższego użytkownika';
$lang['userscope']             = 'Ogranicz zakres wyszukiwania do wyszukiwania użytkowników';
$lang['groupscope']            = 'Ogranicz zakres wyszukiwania do wyszukiwania grup użytkowników';
$lang['userkey']               = 'Atrybut opisujący nazwę użytkownika; musi być zgodny z filtrem użytkownika.';
$lang['groupkey']              = 'Przynależność do grupy z dowolnego atrybutu użytkownika (zamiast standardowych grup AD), np. grupa z działu lub numer telefonu';
$lang['modPass']               = 'Czy hasło LDAP można zmienić za pomocą dokuwiki?';
$lang['debug']                 = 'Przy błędach wyświetl dodatkowe informacje debugujące.';
$lang['deref_o_0']             = 'LDAP_DEREF_NEVER';
$lang['deref_o_1']             = 'LDAP_DEREF_SEARCHING';
$lang['deref_o_2']             = 'LDAP_DEREF_FINDING';
$lang['deref_o_3']             = 'LDAP_DEREF_ALWAYS';
$lang['referrals_o_-1']        = 'użyj domyślnej wartości';
$lang['referrals_o_0']         = 'nie podążaj za przekierowaniami';
$lang['referrals_o_1']         = 'podążaj za przekierowaniami';
