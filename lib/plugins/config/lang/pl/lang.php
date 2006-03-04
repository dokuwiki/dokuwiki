<?php
/**
 * polish language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Grzegorz Żur <grzegorz.zur@gmail.com>
 */

// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu']       = 'Menadżer konfiguracji'; 

$lang['error']      = 'Ustawienia nie zostały zapisane z powodu błędnych wartości, przejrzyj je i ponów próbę zapisu.
                       <br />Niepoprawne wartości są wyróżnione kolorem czerwonym.';
$lang['updated']    = 'Ustawienia zostały zmienione.';
$lang['nochoice']   = '(brak innych możliwości)';
$lang['locked']     = 'Plik ustawień nie mógł zostać zmieniony, upewnij się, czy uprawnienia do plik są odpowiednie.';

// settings prompts
$lang['fmode']       = 'tryb tworzenia pliku';         //directory mask accordingly
$lang['dmode']       = 'tryb tworzenia katalogu';    //directory mask accordingly
$lang['lang']        = 'język';           //your language
$lang['basedir']     = 'katalog główny';     //absolute dir from serveroot - blank for autodetection
$lang['baseurl']     = 'główny URL';           //URL to server including protocol - blank for autodetect
$lang['savedir']     = 'katalog z danymi';     //where to store all the files
$lang['start']       = 'tytuł strony początkowej';    //name of start page
$lang['title']       = 'tytuł wiki';         //what to show in the title
$lang['template']    = 'wzorzec';           //see tpl directory
$lang['fullpath']    = 'wyświetlanie pełnych ścieżek';      //show full path of the document or relative to datadir only? 0|1
$lang['recent']      = 'ilość ostatnich zmiań';     //how many entries to show in recent
$lang['breadcrumbs'] = 'długość śladu';        //how many recent visited pages to show
$lang['typography']  = 'konwersja cudzysłowu, myślników itp.';         //convert quotes, dashes and stuff to typographic equivalents? 0|1
$lang['htmlok']      = 'wstawki HTML';//may raw HTML be embedded? This may break layout and XHTML validity 0|1
$lang['phpok']       = 'wstawki PHP'; //may PHP code be embedded? Never do this on the internet! 0|1
$lang['dformat']     = 'format daty';        //dateformat accepted by PHPs date() function
$lang['signature']   = 'podpis';          //signature see wiki:langig for details
$lang['toptoclevel'] = 'minimalny poziom spisu treści';      //Level starting with and below to include in AutoTOC (max. 5)
$lang['maxtoclevel'] = 'maksymalny poziom spisu treści';      //Up to which level include into AutoTOC (max. 5)
$lang['maxseclevel'] = 'maksymalny poziom podziału na sekcje edycyjne';   //Up to which level create editable sections (max. 5)
$lang['camelcase']   = 'bikapitalizacja (CamelCase)';  //Use CamelCase for linking? (I don't like it) 0|1
$lang['deaccent']    = 'podmieniaj znaki spoza ASCII w nazwach';    //convert accented chars to unaccented ones in pagenames?
$lang['useheading']  = 'pierwszy nagłówek jako tytuł';        //use the first heading in a page as its name
$lang['refcheck']    = 'sprawdzanie odwołań przed usunięciem pliku';    //check for references before deleting media files
$lang['refshow']     = 'ilość pokazywanych odwołań do pliku'; //how many references should be shown, 5 is a good value
$lang['allowdebug']  = 'debugowanie (niebezpieczne!)';   //make debug possible, disable after install! 0|1

$lang['usewordblock']= 'blokowanie spamu na podstawie słów';  //block spam based on words? 0|1
$lang['indexdelay']  = 'okres indeksowania w sekundach'; //allow indexing after this time (seconds) default is 5 days
$lang['relnofollow'] = 'nagłówek rel="nofollow" dla odnośników zewnętrznych';         //use rel="nofollow" for external links?
$lang['mailguard']   = 'utrudnianie odczytu adresów e-mail';  //obfuscate email addresses against spam harvesters?

/* Authentication Options - read http://www.splitbrain.org/dokuwiki/wiki:acl */
$lang['useacl']      = 'kontrola uprawnień ACL';                //Use Access Control Lists to restrict access?
$lang['openregister']= 'pozwolenie na rejestrację nowych użytkowników';          //Should users to be allowed to register?
$lang['autopasswd']  = 'automatyczne generowanie haseł'; //autogenerate passwords and email them to user
$lang['resendpasswd']= 'przypominanie hasła';  //allow resend password function?
$lang['authtype']    = 'typ autoryzacji'; //which authentication backend should be used
$lang['passcrypt']   = 'kodowanie hasła';    //Used crypt method (smd5,md5,sha1,ssha,crypt,mysql,my411)
$lang['defaultgroup']= 'domyślna grupa';          //Default groups new Users are added to
$lang['superuser']   = 'administrator';              //The admin can be user or @group
$lang['profileconfirm'] = 'potwierdzanie zmiany profilu hasłem';     //Require current password to langirm changes to user profile

/* Advanced Options */
$lang['userewrite']  = 'proste adresy URL';             //this makes nice URLs: 0: off 1: .htaccess 2: internal
$lang['useslash']    = 'ukośnik';                 //use slash instead of colon? only when rewrite is on
$lang['sepchar']     = 'znak rozdzielający wyrazy nazw';  //word separator character in page names; may be a
$lang['canonical']   = 'kanoniczne adresy URL';  //Should all URLs use full canonical http://... style?
$lang['autoplural']  = 'automatyczne tworzenie liczby mnogiej';               //try (non)plural form of nonexisting files?
$lang['usegzip']     = 'kompresja gzip dla starych wersji';      //gzip old revisions?
$lang['cachetime']   = 'maksymalny wiek cache w sekundach';  //maximum age for cachefile in seconds (defaults to a day)
$lang['purgeonadd']  = 'czyść cache po dodaniu strony';        //purge cache when a new file is added (needed for up to date links)
$lang['locktime']    = 'maksymalny wiek blockad w sekundach';  //maximum age for lockfiles (defaults to 15 minutes)
$lang['notify']      = 'wysyłanie powiadomień na adres e-mail';      //send change info to this email (leave blank for nobody)
$lang['mailfrom']    = 'adres e-mail tego wiki';            //use this email when sending mails
$lang['gdlib']       = 'wersja biblioteki GDLib';              //the GDlib version (0, 1 or 2) 2 tries to autodetect
$lang['im_convert']  = 'ścieżka do programu imagemagick';            //path to ImageMagicks convert (will be used instead of GD)
$lang['spellchecker']= 'sprawdzanie pisownii';         //enable Spellchecker (needs PHP >= 4.3.0 and aspell installed)
$lang['subscribers'] = 'subskrypcja'; //enable change notice subscription support
$lang['compress']    = 'kompresja arkuszy CSS & i plików JavaScript';  //Strip whitespaces and comments from Styles and JavaScript? 1|0
$lang['hidepages']   = 'ukrywanie stron pasujących do wzorca (regex)';      //Regexp for pages to be skipped from RSS, Search and Recent Changes
$lang['send404']     = 'nagłówek "HTTP404/Page Not Found" dla nieistniejących stron';    //Send a HTTP 404 status for non existing pages?
$lang['sitemap']     = 'okres generowania Google Sitemap w dniach';   //Create a google sitemap? How often? In days.

$lang['rss_type']    = 'typ RSS';             //type of RSS feed to provide, by default:
$lang['rss_linkto']  = 'odnośniki w RSS';              //what page RSS entries link to:

//Set target to use when creating links - leave empty for same window
$lang['target____wiki']      = 'okno docelowe odnośników wewnętrznych';
$lang['target____interwiki'] = 'okno docelowe odnośników do innych wiki';
$lang['target____extern']    = 'okno docelowe odnośników zewnętrznych';
$lang['target____media']     = 'okno docelowe odnośników do plików';
$lang['target____windows']   = 'okno docelowe odnośników zasobów Windows';

//Proxy setup - if your Server needs a proxy to access the web set these
$lang['proxy____host'] = 'proxy - serwer';
$lang['proxy____port'] = 'proxy - port';
$lang['proxy____user'] = 'proxy - nazwa użytkownika';
$lang['proxy____pass'] = 'proxy - hasło';
$lang['proxy____ssl']  = 'proxy - SSL';

/* Safemode Hack */
$lang['safemodehack'] = 'bezpieczny tryb (przez FTP)';  //read http://wiki.splitbrain.org/wiki:safemodehack !
$lang['ftp____host'] = 'ftp - serwer';
$lang['ftp____port'] = 'ftp - port';
$lang['ftp____user'] = 'ftp - nazwa użytkownika';
$lang['ftp____pass'] = 'ftp - hasło';
$lang['ftp____root'] = 'ftp - katalog główny';

/* userewrite options */
$lang['userewrite_o_0'] = 'brak';
$lang['userewrite_o_1'] = 'htaccess';
$lang['userewrite_o_2'] = 'dokuwiki';

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

