<?php
/**
 * polish language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Grzegorz Żur <grzegorz.zur@gmail.com>
 */

// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu']       = 'Ustawienia'; 

$lang['error']      = 'Ustawienia nie zostały zapisane z powodu błędnych wartości, przejrzyj je i ponów próbę zapisu.
                       <br />Niepoprawne wartości są wyróżnione kolorem czerwonym.';
$lang['updated']    = 'Ustawienia zostały zmienione.';
$lang['nochoice']   = '(brak innych możliwości)';
$lang['locked']     = 'Plik ustawień nie mógł zostać zmieniony, upewnij się, czy uprawnienia do pliku są odpowiednie.';


/* --- Config Setting Headers --- */
$lang['_configuration_manager'] = 'Menadżer konfiguracji'; //same as heading in intro.txt
$lang['_header_dokuwiki'] = 'Ustawienia DokuWiki';
$lang['_header_plugin'] = 'Ustawienia wtyczek';
$lang['_header_template'] = 'Ustawienia motywu';
$lang['_header_undefined'] = 'Inne ustawienia';

/* --- Config Setting Groups --- */
$lang['_basic'] = 'Podstawowe';
$lang['_display'] = 'Wygląd';
$lang['_authentication'] = 'Autoryzacja';
$lang['_anti_spam'] = 'Spam';
$lang['_editing'] = 'Edycja';
$lang['_links'] = 'Odnośniki';
$lang['_media'] = 'Media';
$lang['_advanced'] = 'Zaawansowane';
$lang['_network'] = 'Sieć';
// The settings group name for plugins and templates can be set with
// plugin_settings_name and template_settings_name respectively. If one
// of these lang properties is not set, the group name will be generated
// from the plugin or template name and the localized suffix.
$lang['_plugin_sufix'] = 'Wtyczki';
$lang['_template_sufix'] = 'Motywy';

/* --- Undefined Setting Messages --- */
$lang['_msg_setting_undefined'] = 'Brak danych o ustawieniu.';
$lang['_msg_setting_no_class'] = 'Brak kategorii ustawień.';
$lang['_msg_setting_no_default'] = 'Brak wartości domyślnej.';

/* -------------------- Config Options --------------------------- */

$lang['fmode']       = 'Tryb tworzenia pliku';
$lang['dmode']       = 'Tryb tworzenia katalogu';
$lang['lang']        = 'Język';
$lang['basedir']     = 'Katalog główny';
$lang['baseurl']     = 'Główny URL';
$lang['savedir']     = 'Katalog z danymi';
$lang['start']       = 'Tytuł strony początkowej';
$lang['title']       = 'Tytuł wiki';
$lang['template']    = 'Motyw';
$lang['fullpath']    = 'Wyświetlanie pełnych ścieżek';
$lang['recent']      = 'Ilość ostatnich zmiań';
$lang['breadcrumbs'] = 'Długość śladu';
$lang['youarehere']  = 'Ślad według struktury';
$lang['typography']  = 'Konwersja cudzysłowu, myślników itp.';
$lang['htmlok']      = 'Wstawki HTML';
$lang['phpok']       = 'Wstawki PHP';
$lang['dformat']     = 'Format daty';
$lang['signature']   = 'Podpis';
$lang['toptoclevel'] = 'Minimalny poziom spisu treści';
$lang['maxtoclevel'] = 'Maksymalny poziom spisu treści';
$lang['maxseclevel'] = 'Maksymalny poziom podziału na sekcje edycyjne';
$lang['camelcase']   = 'Bikapitalizacja (CamelCase)';
$lang['deaccent']    = 'Podmieniaj znaki spoza ASCII w nazwach';
$lang['useheading']  = 'Pierwszy nagłówek jako tytuł';
$lang['refcheck']    = 'Sprawdzanie odwołań przed usunięciem pliku';
$lang['refshow']     = 'Ilość pokazywanych odwołań do pliku';
$lang['allowdebug']  = 'Debugowanie (niebezpieczne!)';

$lang['usewordblock']= 'Blokowanie spamu na podstawie słów';
$lang['indexdelay']  = 'Okres indeksowania w sekundach';
$lang['relnofollow'] = 'Nagłówek rel="nofollow" dla odnośników zewnętrznych';
$lang['mailguard']   = 'Utrudnianie odczytu adresów e-mail';

/* Authentication Options */
$lang['useacl']      = 'Kontrola uprawnień ACL';
$lang['openregister']= 'Pozwolenie na rejestrację nowych użytkowników';
$lang['autopasswd']  = 'Automatyczne generowanie haseł';
$lang['resendpasswd']= 'Przypominanie hasła';
$lang['authtype']    = 'Typ autoryzacji';
$lang['passcrypt']   = 'Kodowanie hasła';
$lang['defaultgroup']= 'Domyślna grupa';
$lang['superuser']   = 'Administrator';
$lang['profileconfirm'] = 'Potwierdzanie zmiany profilu hasłem';
$lang['disableactions'] = 'Wyłącz akcje DokuWiki';
$lang['disableactions_check'] = 'Sprawdzanie';
$lang['disableactions_subscription'] = 'Subskrypcje';
$lang['disableactions_wikicode'] = 'Pokazywanie źródeł';
$lang['disableactions_other'] = 'Inne akcje (oddzielone przecinkiem)';

/* Advanced Options */
$lang['userewrite']  = 'Proste adresy URL';
$lang['useslash']    = 'Ukośnik';
$lang['sepchar']     = 'Znak rozdzielający wyrazy nazw';
$lang['canonical']   = 'Kanoniczne adresy URL';
$lang['autoplural']  = 'Automatyczne tworzenie liczby mnogiej';
$lang['usegzip']     = 'Kompresja gzip dla starych wersji';
$lang['cachetime']   = 'Maksymalny wiek cache w sekundach';
$lang['locktime']    = 'Maksymalny wiek blockad w sekundach';
$lang['notify']      = 'Wysyłanie powiadomień na adres e-mail';
$lang['registernotify'] = 'Prześlij informacje o nowych użytkownikach na adres e-mail';
$lang['mailfrom']    = 'Adres e-mail tego wiki';
$lang['gzip_output'] = 'Używaj GZIP dla XHTML';
$lang['gdlib']       = 'Wersja biblioteki GDLib';
$lang['im_convert']  = 'Ścieżka do programu imagemagick';
$lang['jpg_quality'] = 'Jakość kompresji JPG (0-100)';
$lang['spellchecker']= 'Sprawdzanie pisownii';
$lang['subscribers'] = 'Subskrypcja';
$lang['compress']    = 'Kompresja arkuszy CSS & i plików JavaScript';
$lang['hidepages']   = 'Ukrywanie stron pasujących do wzorca (wyrażenie regularne)';
$lang['send404']     = 'Nagłówek "HTTP 404/Page Not Found" dla nieistniejących stron';
$lang['sitemap']     = 'Okres generowania Google Sitemap (w dniach)';

$lang['rss_type']    = 'Typ RSS';
$lang['rss_linkto']  = 'Odnośniki w RSS';
$lang['rss_update']  = 'Okres aktualizacji RSS (w sekundach)';

/* Target options */
$lang['target____wiki']      = 'Okno docelowe odnośników wewnętrznych';
$lang['target____interwiki'] = 'Okno docelowe odnośników do innych wiki';
$lang['target____extern']    = 'Okno docelowe odnośników zewnętrznych';
$lang['target____media']     = 'Okno docelowe odnośników do plików';
$lang['target____windows']   = 'Okno docelowe odnośników zasobów Windows';

/* Proxy Options */
$lang['proxy____host'] = 'Proxy - serwer';
$lang['proxy____port'] = 'Proxy - port';
$lang['proxy____user'] = 'Proxy - nazwa użytkownika';
$lang['proxy____pass'] = 'Proxy - hasło';
$lang['proxy____ssl']  = 'Proxy - SSL';

/* Safemode Hack */
$lang['safemodehack'] = 'Bezpieczny tryb (przez FTP)';
$lang['ftp____host'] = 'FTP - serwer';
$lang['ftp____port'] = 'FTP - port';
$lang['ftp____user'] = 'FTP - nazwa użytkownika';
$lang['ftp____pass'] = 'FTP - hasło';
$lang['ftp____root'] = 'FTP - katalog główny';

/* userewrite options */
$lang['userewrite_o_0'] = 'brak';
$lang['userewrite_o_1'] = '.htaccess';
$lang['userewrite_o_2'] = 'dokuwiki';

/* deaccent options */
$lang['deaccent_o_0'] = 'zostaw orginalną pisownię';
$lang['deaccent_o_1'] = 'usuń litery';
$lang['deaccent_o_2'] = 'zamień na ASCII';

/* gdlib options */
$lang['gdlib_o_0'] = 'biblioteka GDLib niedostępna';
$lang['gdlib_o_1'] = 'wersja 1.x';
$lang['gdlib_o_2'] = 'automatyczne wykrywanie';

/* rss_type options */
$lang['rss_type_o_rss']  = 'RSS 0.91';
$lang['rss_type_o_rss1'] = 'RSS 1.0';
$lang['rss_type_o_rss2'] = 'RSS 2.0';
$lang['rss_type_o_atom'] = 'Atom 0.3';

/* rss_linkto options */
$lang['rss_linkto_o_diff']    = 'różnice';
$lang['rss_linkto_o_page']    = 'zmodyfikowana strona';
$lang['rss_linkto_o_rev']     = 'lista zmian';
$lang['rss_linkto_o_current'] = 'aktualna strona';

