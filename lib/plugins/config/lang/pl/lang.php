<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Marek Adamski <fevbew@wp.pl>
 * @author pavulondit <pavloo@vp.pl>
 * @author Bartek S <sadupl@gmail.com>
 * @author Wojciech Lichota <wojciech@lichota.pl>
 * @author Max <maxrb146@gmail.com>
 * @author Grzegorz Żur <grzegorz.zur@gmail.com>
 * @author Mariusz Kujawski <marinespl@gmail.com>
 * @author Maciej Kurczewski <pipijajko@gmail.com>
 * @author Sławomir Boczek <slawkens@gmail.com>
 * @author Piotr JANKOWSKI <jankowski.piotr@gmail.com>
 * @author sleshek <sleshek@wp.pl>
 * @author Leszek Stachowski <shazarre@gmail.com>
 * @author maros <dobrimaros@yahoo.pl>
 * @author Grzegorz Widła <dzesdzes@gmail.com>
 * @author Łukasz Chmaj <teachmeter@gmail.com>
 * @author Begina Felicysym <begina.felicysym@wp.eu>
 * @author Aoi Karasu <aoikarasu@gmail.com>
 */
$lang['menu']                  = 'Ustawienia';
$lang['error']                 = 'Ustawienia nie zostały zapisane z powodu błędnych wartości, przejrzyj je i ponów próbę zapisu. <br/> Niepoprawne wartości są wyróżnione kolorem czerwonym.';
$lang['updated']               = 'Ustawienia zostały zmienione.';
$lang['nochoice']              = '(brak innych możliwości)';
$lang['locked']                = 'Plik ustawień nie mógł zostać zmieniony, upewnij się, czy uprawnienia do pliku są odpowiednie.';
$lang['danger']                = 'Uwaga: Zmiana tej opcji może uniemożliwić dostęp do twojej wiki oraz konfiguracji.';
$lang['warning']               = 'Ostrzeżenie: Zmiana tej opcji może spowodować nieporządane skutki.';
$lang['security']              = 'Alert bezpieczeństwa: Zmiana tej opcji może obniżyć bezpieczeństwo.';
$lang['_configuration_manager'] = 'Menadżer konfiguracji';
$lang['_header_dokuwiki']      = 'Ustawienia DokuWiki';
$lang['_header_plugin']        = 'Ustawienia wtyczek';
$lang['_header_template']      = 'Ustawienia motywu';
$lang['_header_undefined']     = 'Inne ustawienia';
$lang['_basic']                = 'Podstawowe';
$lang['_display']              = 'Wygląd';
$lang['_authentication']       = 'Autoryzacja';
$lang['_anti_spam']            = 'Spam';
$lang['_editing']              = 'Edycja';
$lang['_links']                = 'Odnośniki';
$lang['_media']                = 'Media';
$lang['_notifications']        = 'Ustawienia powiadomień';
$lang['_syndication']          = 'Ustawienia RSS';
$lang['_advanced']             = 'Zaawansowane';
$lang['_network']              = 'Sieć';
$lang['_msg_setting_undefined'] = 'Brak danych o ustawieniu.';
$lang['_msg_setting_no_class'] = 'Brak kategorii ustawień.';
$lang['_msg_setting_no_known_class'] = 'Klasa ustawień niedostępna.';
$lang['_msg_setting_no_default'] = 'Brak wartości domyślnej.';
$lang['title']                 = 'Tytuł wiki';
$lang['start']                 = 'Tytuł strony początkowej';
$lang['lang']                  = 'Język';
$lang['template']              = 'Motyw';
$lang['tagline']               = 'Motto (jeśli szablon daje taką możliwość)';
$lang['sidebar']               = 'Nazwa strony paska bocznego (jeśli szablon je obsługuje), puste pole wyłącza pasek boczny';
$lang['license']               = 'Pod jaką licencją publikować treści wiki?';
$lang['savedir']               = 'Katalog z danymi';
$lang['basedir']               = 'Katalog główny';
$lang['baseurl']               = 'Główny URL';
$lang['cookiedir']             = 'Ścieżka plików ciasteczek. Zostaw puste by użyć baseurl.';
$lang['dmode']                 = 'Tryb tworzenia katalogu';
$lang['fmode']                 = 'Tryb tworzenia pliku';
$lang['allowdebug']            = 'Debugowanie (niebezpieczne!)';
$lang['recent']                = 'Ilość ostatnich zmian';
$lang['recent_days']           = 'Ilość ostatnich zmian (w dniach)';
$lang['breadcrumbs']           = 'Długość śladu';
$lang['youarehere']            = 'Ślad według struktury';
$lang['fullpath']              = 'Wyświetlanie pełnych ścieżek';
$lang['typography']            = 'Konwersja cudzysłowu, myślników itp.';
$lang['dformat']               = 'Format daty';
$lang['signature']             = 'Podpis';
$lang['showuseras']            = 'Sposób wyświetlania nazwy użytkownika, który ostatnio edytował stronę';
$lang['toptoclevel']           = 'Minimalny poziom spisu treści';
$lang['tocminheads']           = 'Minimalna liczba nagłówków niezbędna do wytworzenia spisu treści.';
$lang['maxtoclevel']           = 'Maksymalny poziom spisu treści';
$lang['maxseclevel']           = 'Maksymalny poziom podziału na sekcje edycyjne';
$lang['camelcase']             = 'Bikapitalizacja odnośników (CamelCase)';
$lang['deaccent']              = 'Podmieniaj znaki spoza ASCII w nazwach';
$lang['useheading']            = 'Pierwszy nagłówek jako tytuł';
$lang['sneaky_index']          = 'Domyślnie, Dokuwiki pokazuje wszystkie katalogi w indeksie. Włączenie tej opcji ukryje katalogi, do których użytkownik nie ma praw. Może to spowodować ukrycie podkatalogów, do których użytkownik ma prawa. Ta opcja może spowodować błędne działanie indeksu w połączeniu z pewnymi konfiguracjami praw dostępu.';
$lang['hidepages']             = 'Ukrywanie stron pasujących do wzorca (wyrażenie regularne)';
$lang['useacl']                = 'Kontrola uprawnień ACL';
$lang['autopasswd']            = 'Automatyczne generowanie haseł';
$lang['authtype']              = 'Typ autoryzacji';
$lang['passcrypt']             = 'Kodowanie hasła';
$lang['defaultgroup']          = 'Domyślna grupa';
$lang['superuser']             = 'Administrator - grupa lub użytkownik z pełnymi uprawnieniami';
$lang['manager']               = 'Menadżer - grupa lub użytkownik z uprawnieniami do zarządzania wiki';
$lang['profileconfirm']        = 'Potwierdzanie zmiany profilu hasłem';
$lang['rememberme']            = 'Pozwól na ciasteczka automatycznie logujące (pamiętaj mnie)';
$lang['disableactions']        = 'Wyłącz akcje DokuWiki';
$lang['disableactions_check']  = 'Sprawdzanie';
$lang['disableactions_subscription'] = 'Subskrypcje';
$lang['disableactions_wikicode'] = 'Pokazywanie źródeł';
$lang['disableactions_profile_delete'] = 'Usuń własne konto ';
$lang['disableactions_other']  = 'Inne akcje (oddzielone przecinkiem)';
$lang['disableactions_rss']    = 'XML Syndication (RSS)';
$lang['auth_security_timeout'] = 'Czas wygaśnięcia uwierzytelnienia (w sekundach)';
$lang['securecookie']          = 'Czy ciasteczka wysłane do przeglądarki przez HTTPS powinny być przez nią odsyłane też tylko przez HTTPS? Odznacz tę opcję tylko wtedy, gdy logowanie użytkowników jest zabezpieczone SSL, ale przeglądanie stron odbywa się bez zabezpieczenia.';
$lang['samesitecookie']        = 'Atrybut pliku cookie tej samej witryny do użycia. Pozostawienie go pustym pozwoli przeglądarce zdecydować o zasadach tej samej witryny.';
$lang['remote']                = 'Włącz API zdalnego dostępu. Pozwoli to innym aplikacjom na dostęp do wiki poprzez XML-RPC lub inne mechanizmy.';
$lang['remoteuser']            = 'Ogranicz dostęp poprzez API zdalnego dostępu do podanych grup lub użytkowników, oddzielonych przecinkami. Pozostaw to pole puste by pozwolić na dostęp be ograniczeń.';
$lang['remotecors']            = 'Włącz udostępnianie zasobów między źródłami (CORS) dla interfejsów zdalnych. Gwiazdka (*), aby zezwolić na wszystkie źródła. Pozostaw puste, aby odrzucić CORS.';
$lang['usewordblock']          = 'Blokowanie spamu na podstawie słów';
$lang['relnofollow']           = 'Nagłówek rel="nofollow" dla odnośników zewnętrznych';
$lang['indexdelay']            = 'Okres indeksowania w sekundach';
$lang['mailguard']             = 'Utrudnianie odczytu adresów e-mail';
$lang['iexssprotect']          = 'Wykrywanie złośliwego kodu JavaScript i HTML w plikach';
$lang['usedraft']              = 'Automatyczne zapisywanie szkicu podczas edycji';
$lang['locktime']              = 'Maksymalny wiek blokad w sekundach';
$lang['cachetime']             = 'Maksymalny wiek cache w sekundach';
$lang['target____wiki']        = 'Okno docelowe odnośników wewnętrznych';
$lang['target____interwiki']   = 'Okno docelowe odnośników do innych wiki';
$lang['target____extern']      = 'Okno docelowe odnośników zewnętrznych';
$lang['target____media']       = 'Okno docelowe odnośników do plików';
$lang['target____windows']     = 'Okno docelowe odnośników zasobów Windows';
$lang['mediarevisions']        = 'Włączyć wersjonowanie multimediów?';
$lang['refcheck']              = 'Sprawdzanie odwołań przed usunięciem pliku';
$lang['gdlib']                 = 'Wersja biblioteki GDLib';
$lang['im_convert']            = 'Ścieżka do programu imagemagick';
$lang['jpg_quality']           = 'Jakość kompresji JPG (0-100)';
$lang['fetchsize']             = 'Maksymalny rozmiar pliku (w bajtach) jaki można pobrać z zewnątrz';
$lang['subscribers']           = 'Subskrypcja';
$lang['subscribe_time']        = 'Czas po którym są wysyłane listy subskrypcji i streszczenia (sek.); Powinna być to wartość większa niż podana w zmiennej recent_days.';
$lang['notify']                = 'Wysyłanie powiadomień na adres e-mail';
$lang['registernotify']        = 'Prześlij informacje o nowych użytkownikach na adres e-mail';
$lang['mailfrom']              = 'Adres e-mail tego wiki';
$lang['mailreturnpath']        = 'Adres e-mail odbiorcy dla powiadomień o niedostarczeniu';
$lang['mailprefix']            = 'Prefiks tematu e-mail do automatycznych wiadomości';
$lang['htmlmail']              = 'Wysyłaj wiadomości e-mail w formacie HTML, które wyglądają lepiej, lecz ich rozmiar jest większy. Wyłącz wysyłanie wiadomości zawierających tekst niesformatowany.';
$lang['dontlog']               = 'Wyłącz logowanie dla tego typu logów.';
$lang['logretain']             = 'Liczba dni przechowywania logów.';
$lang['sitemap']               = 'Okres generowania Google Sitemap (w dniach)';
$lang['rss_type']              = 'Typ RSS';
$lang['rss_linkto']            = 'Odnośniki w RSS';
$lang['rss_content']           = 'Rodzaj informacji wyświetlanych w RSS ';
$lang['rss_update']            = 'Okres aktualizacji RSS (w sekundach)';
$lang['rss_show_summary']      = 'Podsumowanie w tytule';
$lang['rss_show_deleted']      = 'Pokaż usunięte kanały';
$lang['rss_media']             = 'Rodzaj zmian wyświetlanych w RSS';
$lang['rss_media_o_both']      = 'oba';
$lang['rss_media_o_pages']     = 'strony';
$lang['rss_media_o_media']     = 'media';
$lang['updatecheck']           = 'Sprawdzanie aktualizacji i bezpieczeństwa. DokuWiki będzie kontaktować się z serwerem update.dokuwiki.org.';
$lang['userewrite']            = 'Proste adresy URL';
$lang['useslash']              = 'Używanie ukośnika jako separatora w adresie URL';
$lang['sepchar']               = 'Znak rozdzielający wyrazy nazw';
$lang['canonical']             = 'Kanoniczne adresy URL';
$lang['fnencode']              = 'Metoda kodowana nazw pików bez użycia ASCII.';
$lang['autoplural']            = 'Automatyczne tworzenie liczby mnogiej';
$lang['compression']           = 'Metoda kompresji dla usuniętych plików';
$lang['gzip_output']           = 'Używaj kodowania GZIP dla zawartości XHTML';
$lang['compress']              = 'Kompresja arkuszy CSS i plików JavaScript';
$lang['cssdatauri']            = 'Rozmiar w bajtach, poniżej którego odwołania do obrazów w plikach CSS powinny być osadzone bezpośrednio w arkuszu stylów by zmniejszyć ogólne żądania nagłówków HTTP. <code>400</code> do <code>600</code> bajtów jest dobrą wartością. Ustaw <code>0</code> aby wyłączyć.';
$lang['send404']               = 'Nagłówek "HTTP 404/Page Not Found" dla nieistniejących stron';
$lang['broken_iua']            = 'Czy funkcja "ignore_user_abort" działa? Jeśli nie, może to powodować problemy z indeksem przeszukiwania. Funkcja nie działa przy konfiguracji oprogramowania IIS+PHP/CGI. Szczegółowe informacje: <a href="http://bugs.splitbrain.org/?do=details&amp;task_id=852">Bug 852</a>.';
$lang['xsendfile']             = 'Użyj nagłówka HTTP X-Sendfile w celu przesyłania statycznych plików. Serwer HTTP musi obsługiwać ten nagłówek.';
$lang['renderer_xhtml']        = 'Mechanizm renderowania głównej treści strony (xhtml)';
$lang['renderer__core']        = '%s (dokuwiki)';
$lang['renderer__plugin']      = '%s (wtyczka)';
$lang['search_nslimit']        = 'Ogranicz wyszukiwanie do bieżących przestrzeni nazw X. Gdy wyszukiwanie jest wykonywane ze strony w głębszej przestrzeni nazw, pierwsze przestrzenie nazw X zostaną dodane jako filtr';
$lang['search_fragment']       = 'Określ domyślne zachowanie wyszukiwania fragmentów';
$lang['search_fragment_o_exact'] = 'dokładny';
$lang['search_fragment_o_starts_with'] = 'zaczyna się z';
$lang['search_fragment_o_ends_with'] = 'kończy się z';
$lang['search_fragment_o_contains'] = 'zawiera';
$lang['trustedproxy']          = 'Zaufaj serwerom proxy odpowiadającym temu wyrażeniu regularnemu co do prawdziwego adresu IP klienta, który zgłaszają. Domyślnie dotyczy sieci lokalnych. Pozostaw puste, aby nie ufać żadnym serwerom proxy.';
$lang['_feature_flags']        = 'Flagi funkcji';
$lang['defer_js']              = 'Odrocz wykonanie skryptu JavaScript po przeanalizowaniu kodu HTML strony. Poprawia postrzeganą szybkość strony, ale może zepsuć niewielką liczbę wtyczek.';
$lang['hidewarnings']          = 'Nie wyświetlaj żadnych ostrzeżeń wydawanych przez PHP. Może to ułatwić przejście na PHP8+. Ostrzeżenia będą nadal rejestrowane w dzienniku błędów i powinny być zgłaszane.';
$lang['dnslookups']            = 'DokiWiki wyszuka nazwy hostów dla zdalnych adresów IP użytkowników edytujących strony. Jeśli twój serwer DNS działa zbyt wolno, uległ awarii lub nie chcesz używać wyszukiwania, wyłącz tę opcję.';
$lang['jquerycdn']             = 'Czy pliki skryptów jQuery i jQuery UI powinny być ładowane z CDN? Powoduje to dodanie dodatkowych żądań HTTP, ale pliki mogą być ładowane szybciej, a użytkownicy mogą już je mieć zbuforowane.';
$lang['jquerycdn_o_0']         = 'Bez CDN, tylko lokalne zasoby';
$lang['jquerycdn_o_jquery']    = 'CDN z code.jquery.com';
$lang['jquerycdn_o_cdnjs']     = 'CDN z cdnjs.com';
$lang['proxy____host']         = 'Proxy - serwer';
$lang['proxy____port']         = 'Proxy - port';
$lang['proxy____user']         = 'Proxy - nazwa użytkownika';
$lang['proxy____pass']         = 'Proxy - hasło';
$lang['proxy____ssl']          = 'Proxy - SSL';
$lang['proxy____except']       = 'Wyrażenie regularne określające adresy URL, do których nie należy używać proxy.';
$lang['license_o_']            = 'Nie wybrano żadnej';
$lang['typography_o_0']        = 'brak';
$lang['typography_o_1']        = 'tylko podwójne cudzysłowy';
$lang['typography_o_2']        = 'wszystkie cudzysłowy (nie działa we wszystkich przypadkach)';
$lang['userewrite_o_0']        = 'brak';
$lang['userewrite_o_1']        = '.htaccess';
$lang['userewrite_o_2']        = 'dokuwiki';
$lang['deaccent_o_0']          = 'zostaw oryginalną pisownię';
$lang['deaccent_o_1']          = 'usuń litery';
$lang['deaccent_o_2']          = 'zamień na ASCII';
$lang['gdlib_o_0']             = 'biblioteka GDLib niedostępna';
$lang['gdlib_o_1']             = 'wersja 1.x';
$lang['gdlib_o_2']             = 'automatyczne wykrywanie';
$lang['rss_type_o_rss']        = 'RSS 0.91';
$lang['rss_type_o_rss1']       = 'RSS 1.0';
$lang['rss_type_o_rss2']       = 'RSS 2.0';
$lang['rss_type_o_atom']       = 'Atom 0.3';
$lang['rss_type_o_atom1']      = 'Atom 1.0';
$lang['rss_content_o_abstract'] = 'Streszczenie';
$lang['rss_content_o_diff']    = 'Różnice';
$lang['rss_content_o_htmldiff'] = 'Różnice w postaci HTML';
$lang['rss_content_o_html']    = 'Pełna strona w postaci HTML';
$lang['rss_linkto_o_diff']     = 'różnice';
$lang['rss_linkto_o_page']     = 'zmodyfikowana strona';
$lang['rss_linkto_o_rev']      = 'lista zmian';
$lang['rss_linkto_o_current']  = 'aktualna strona';
$lang['compression_o_0']       = 'brak';
$lang['compression_o_gz']      = 'gzip';
$lang['compression_o_bz2']     = 'bz2';
$lang['xsendfile_o_0']         = 'nie używaj';
$lang['xsendfile_o_1']         = 'Specyficzny nagłówek lightttpd (poniżej wersji 1.5)';
$lang['xsendfile_o_2']         = 'Standardowy nagłówek HTTP X-Sendfile';
$lang['xsendfile_o_3']         = 'Specyficzny nagłówek Nginx X-Accel-Redirect';
$lang['showuseras_o_loginname'] = 'Login użytkownika';
$lang['showuseras_o_username'] = 'Pełne nazwisko użytkownika';
$lang['showuseras_o_username_link'] = 'Imię i nazwisko użytkownika jako połączenie między wiki';
$lang['showuseras_o_email']    = 'E-mail użytkownika (ukrywanie według ustawień mailguard)';
$lang['showuseras_o_email_link'] = 'Adresy e-mail użytkowników w formie linku mailto:';
$lang['useheading_o_0']        = 'Nigdy';
$lang['useheading_o_navigation'] = 'W nawigacji';
$lang['useheading_o_content']  = 'W treści';
$lang['useheading_o_1']        = 'Zawsze';
$lang['readdircache']          = 'Maksymalny czas dla bufora readdir (w sek).';
