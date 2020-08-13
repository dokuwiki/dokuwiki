<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Bartek S <sadupl@gmail.com>
 * @author Przemek <p_kudriawcew@o2.pl>
 * @author Wojciech Lichota <wojciech@lichota.pl>
 * @author Max <maxrb146@gmail.com>
 * @author Tomasz Bosak <bosak.tomasz@gmail.com>
 * @author Paweł Jan Czochański <czochanski@gmail.com>
 * @author Mati <mackosa@wp.pl>
 * @author Maciej Helt <geraldziu@gmail.com>
 * @author Kris Charatonik <krishary@gmail.com>
 */
$lang['account_suffix']        = 'Przyrostek twojej nazwy konta np. <code>@my.domain.org</code>';
$lang['base_dn']               = 'Twoje bazowe DN. Na przykład: <code>DC=my,DC=domain,DC=org</code>';
$lang['domain_controllers']    = 'Podzielona przecinkami lista kontrolerów domen np. <code>srv1.domena.pl,srv2.domena.pl</code>';
$lang['admin_username']        = 'Uprawniony użytkownik katalogu Active Directory z dostępem do danych wszystkich użytkowników.
Opcjonalne, ale wymagane dla niektórych akcji np. wysyłania emailowych subskrypcji.';
$lang['admin_password']        = 'Hasło dla powyższego użytkownika.';
$lang['sso']                   = 'Czy pojedyncze logowanie powinno korzystać z Kerberos czy NTML?';
$lang['sso_charset']           = 'Kodowanie znaków wykorzystywane do przesyłania nazwy użytkownika dla Kerberos lub NTLM. Pozostaw puste dla UTF-8 lub latin-1. Wymaga rozszerzenia iconv.';
$lang['real_primarygroup']     = 'Czy prawdziwa grupa podstawowa powinna zostać pobrana, zamiast  przyjmowania domyślnej wartości "Domain Users" (wolniej).';
$lang['use_ssl']               = 'Użyć połączenie SSL? Jeśli tak to nie aktywuj TLS poniżej.';
$lang['use_tls']               = 'Użyć połączenie TLS? Jeśli tak to nie aktywuj SSL powyżej.';
$lang['debug']                 = 'Wyświetlać dodatkowe informacje do debugowania w przypadku błędów?';
$lang['expirywarn']            = 'Dni poprzedzających powiadomienie użytkownika o wygasającym haśle. 0 aby wyłączyć.';
$lang['additional']            = 'Oddzielona przecinkami lista dodatkowych atrybutów AD do pobrania z danych użytkownika. Używane przez niektóre wtyczki.';
$lang['update_name']           = 'Zezwól użytkownikom na uaktualnianie nazwy wyświetlanej w AD?';
$lang['update_mail']           = 'Zezwól użytkownikom na uaktualnianie ich adresu email?';
$lang['recursive_groups']      = 'Rozpatrz grupy zagnieżdżone dla odpowiednich członków (wolniej).';
