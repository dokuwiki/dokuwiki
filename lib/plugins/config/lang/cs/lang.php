<?php
/**
 * Czech language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Bohumir Zamecnik <bohumir@zamecnik.org>
 */

// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu']       = 'Správa nastavení';

$lang['error']      = 'Nastavení nebyla změněna kvůli alespoň jedné neplatné položce,
                       zkotrolujte prosím své úpravy a odešlete je znovu.<br />
                       Neplatné hodnoty se zobrazí v červeném rámečku.';
$lang['updated']    = 'Nastavení byla úspěšně upravena.';
$lang['nochoice']   = '(nejsou k dispozici žádné další volby)';
$lang['locked']     = 'Nelze upravovat soubor s nastavením. Pokud to není záměrné, ujistěte se, <br />
                       že název a přístupová práva souboru s lokálním nastavením jsou v pořádku.';

/* --- Config Setting Headers --- */
$lang['_configuration_manager'] = 'Správa nastavení'; //same as heading in intro.txt
$lang['_header_dokuwiki'] = 'Nastavení DokuWiki';
$lang['_header_plugin'] = 'Nastavení pluginů';
$lang['_header_template'] = 'Nastavení šablon';
$lang['_header_undefined'] = 'Další nastavení';

/* --- Config Setting Groups --- */
$lang['_basic'] = 'Základní nastavení';
$lang['_display'] = 'Nastavení zobrazení';
$lang['_authentication'] = 'Nastavení autentizace';
$lang['_anti_spam'] = 'Antispamová nastavení';
$lang['_editing'] = 'Nastavení editace';
$lang['_links'] = 'Nastavení odkazů';
$lang['_media'] = 'Nastavení médií';
$lang['_advanced'] = 'Pokročilá nastavení';
$lang['_network'] = 'Nastavení sítě';
// The settings group name for plugins and templates can be set with
// plugin_settings_name and template_settings_name respectively. If one
// of these lang properties is not set, the group name will be generated
// from the plugin or template name and the localized suffix.
$lang['_plugin_sufix'] = 'Nastavení pluginů ';
$lang['_template_sufix'] = 'Nastavení šablon';

/* --- Undefined Setting Messages --- */
$lang['_msg_setting_undefined'] = 'Chybí metadata položky.';
$lang['_msg_setting_no_class'] = 'Chybí třída položky.';
$lang['_msg_setting_no_default'] = 'Chybí výchozí hodnota položky.';

/* -------------------- Config Options --------------------------- */

$lang['fmode']       = 'Přístupová práva pro vytváření souborů';
$lang['dmode']       = 'Přístupová práva pro vytváření adresářů';
$lang['lang']        = 'Jazyk';
$lang['basedir']     = 'Kořenový adresář';
$lang['baseurl']     = 'Kořenové URL';
$lang['savedir']     = 'Adresář pro ukládání dat';
$lang['start']       = 'Název úvodních stránek';
$lang['title']       = 'Název celé wiki';
$lang['template']    = 'Šablona';
$lang['fullpath']    = 'Ukazovat plnou cestu ke stránkám v patičce';
$lang['recent']      = 'Nedávné změny';
$lang['breadcrumbs'] = 'Počet odkazů na navštívené stránky';
$lang['youarehere']  = 'Hierarchická "drobečková" navigace';
$lang['typography']  = 'Provádět typografické nahrazování';
$lang['htmlok']      = 'Povolit vložené HTML';
$lang['phpok']       = 'Povolit vložené PHP';
$lang['dformat']     = 'Formát data (viz PHP funkci <a href="http://www.php.net/date">date</a>)';
$lang['signature']   = 'Podpis';
$lang['toptoclevel'] = 'Nejvyšší úroveň, kterou začít automaticky generovaný obsah';
$lang['maxtoclevel'] = 'Maximální počet úrovní v automaticky generovaném obsahu';
$lang['maxseclevel'] = 'Nejnižší úroveň pro editaci i po sekcích';
$lang['camelcase']   = 'Používat CamelCase v odkazech';
$lang['deaccent']    = 'Čistit názvy stránek';
$lang['useheading']  = 'Používat první nadpis jako název stránky';
$lang['refcheck']    = 'Kontrolovat odkazy na média (před vymazáním)';
$lang['refshow']     = 'Počet zobrazených odkazů na média';
$lang['allowdebug']  = 'Povolit debugování. <b>Vypněte, pokud to nepotřebujete!</b>';

$lang['usewordblock']= 'Blokovat spam za použítí seznamu známých spamových slov';
$lang['indexdelay']  = 'Časová prodleva před indexací (sekundy)';
$lang['relnofollow'] = 'Používat rel="nofollow" na externí odkazy';
$lang['mailguard']   = 'Metoda "zašifrování" emailových addres';

/* Authentication Options */
$lang['useacl']      = 'Používat přístupová práva (ACL)';
$lang['openregister']= 'Povolit komukoliv se registrovat';
$lang['autopasswd']  = 'Generovat hesla automaticky';
$lang['resendpasswd']= 'Povolit posílání nových hesel';
$lang['authtype']    = 'Metoda autentizace';
$lang['passcrypt']   = 'Metoda šifrování hesel';
$lang['defaultgroup']= 'Výchozí skupina';
$lang['superuser']   = 'Superuživatel';
$lang['profileconfirm'] = 'Potvrdit změny v profilu zadáním hesla';

/* Advanced Options */
$lang['userewrite']  = 'Používat "pěkná" URL';
$lang['useslash']    = 'Používat lomítko jako oddělovač jmenných prostorů v URL';
$lang['usedraft']    = 'Během editace ukládat koncept automaticky';
$lang['sepchar']     = 'Znak pro oddělování slov v názvech stránek';
$lang['canonical']   = 'Používat plně kanonická URL';
$lang['autoplural']  = 'Kontrolovat plurálové tvary v odkazech';
$lang['usegzip']     = 'Používat gzip pro archivní soubory';
$lang['cachetime']   = 'Maximální životnost cache (sekundy)';
$lang['locktime']    = 'Maximální životnost zámkových souborů (sekundy)';
$lang['notify']      = 'Posílat oznámení o změnách na následující emailovou adresu';
$lang['mailfrom']    = 'Emailová addresa, která se bude používat pro automatické maily';
$lang['gzip_output'] = 'Používat pro xhtml Content-Encoding gzip';
$lang['gdlib']       = 'Verze GD Lib';
$lang['im_convert']  = 'Cesta k nástroji convert z balíku ImageMagick';
$lang['jpg_quality'] = 'Kvalita komprese JPEG (0-100)';
$lang['spellchecker']= 'Zapnout kontrolu pravopisu';
$lang['subscribers'] = 'Možnost přihlásit se k odběru novinek stránky';
$lang['compress']    = 'Zahustit CSS a JavaScript výstup';
$lang['hidepages']   = 'Skrýt stránky odpovídající vzoru (regulární výrazy)';
$lang['send404']     = 'Posílat "HTTP 404/Page Not Found" pro neexistují stránky';
$lang['sitemap']     = 'Generovat Google sitemap (interval ve dnech)';
$lang['rss_type']    = 'Typ XML kanálu';
$lang['rss_linkto']  = 'XML kanál odkazuje na';
$lang['rss_update']  = 'Interval aktualizace XML kanálu (sekundy)';

/* Target options */
$lang['target____wiki']      = 'Cílové okno pro interní odkazy';
$lang['target____interwiki'] = 'Cílové okno pro interwiki odkazy';
$lang['target____extern']    = 'Cílové okno pro externí odkazy';
$lang['target____media']     = 'Cílové okno pro odkazy na média';
$lang['target____windows']   = 'Cílové okno pro odkazy na windows sdílení';

/* Proxy Options */
$lang['proxy____host'] = 'Název proxy serveru';
$lang['proxy____port'] = 'Proxy port';
$lang['proxy____user'] = 'Proxy uživatelské jméno';
$lang['proxy____pass'] = 'Proxy heslo';
$lang['proxy____ssl']  = 'Použít SSL při připojení k proxy';

/* Safemode Hack */
$lang['safemodehack'] = 'Zapnout safemode hack';
$lang['ftp____host'] = 'FTP server pro safemode hack';
$lang['ftp____port'] = 'FTP port pro safemode hack';
$lang['ftp____user'] = 'FTP uživatelské jméno pro safemode hack';
$lang['ftp____pass'] = 'FTP heslo pro safemode hack';
$lang['ftp____root'] = 'FTP kořenový adresář pro safemode hack';

/* userewrite options */
$lang['userewrite_o_0'] = 'vypnuto';
$lang['userewrite_o_1'] = '.htaccess';
$lang['userewrite_o_2'] = 'interní metoda DokuWiki';

/* deaccent options */
$lang['deaccent_o_0'] = 'vypnuto';
$lang['deaccent_o_1'] = 'odstranit diakritiku';
$lang['deaccent_o_2'] = 'převést na latinku';

/* gdlib options */
$lang['gdlib_o_0'] = 'GD Lib není k dispozici';
$lang['gdlib_o_1'] = 'Verze 1.x';
$lang['gdlib_o_2'] = 'Autodetekce';

/* rss_type options */
$lang['rss_type_o_rss']  = 'RSS 0.91';
$lang['rss_type_o_rss1'] = 'RSS 1.0';
$lang['rss_type_o_rss2'] = 'RSS 2.0';
$lang['rss_type_o_atom'] = 'Atom 0.3';

/* rss_linkto options */
$lang['rss_linkto_o_diff']    = 'přehled změn';
$lang['rss_linkto_o_page']    = 'stránku samotnou'; // FIXME
$lang['rss_linkto_o_rev']     = 'seznam revizí';
$lang['rss_linkto_o_current'] = 'nejnovější revize';

