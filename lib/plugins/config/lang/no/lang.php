<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Thomas Nygreen <nygreen@gmail.com>
 * @author Arild Burud <arildb@met.no>
 * @author Torkill Bruland <torkar-b@online.no>
 * @author Rune M. Andersen <rune.andersen@gmail.com>
 * @author Jakob Vad Nielsen (me@jakobnielsen.net)
 * @author Kjell Tore Næsgaard <kjell.t.nasgaard@ntnu.no>
 * @author Knut Staring <knutst@gmail.com>
 * @author Lisa Ditlefsen <lisa@vervesearch.com>
 * @author Erik Pedersen <erik.pedersen@shaw.ca>
 * @author Erik Bjørn Pedersen <erik.pedersen@shaw.ca>
 * @author Rune Rasmussen syntaxerror.no@gmail.com
 * @author Jon Bøe <jonmagneboe@hotmail.com>
 * @author Egil Hansen <egil@rosetta.no>
 * @author Arne Hanssen <arne.hanssen@getmail.no>
 * @author Arne Hanssen <arnehans@getmail.no>
 * @author Patrick Sletvold <patricksletvold@hotmail.com>
 */
$lang['menu']                  = 'Konfigurasjonsinnstillinger';
$lang['error']                 = 'Innstillingene ble ikke oppdatert på grunn av en eller flere ugyldig verdier. Vennligst se gjennom endringene og prøv på nytt.
<br />Ugyldige verdi(er) vil vises i rød ramme.';
$lang['updated']               = 'Innstillingene ble oppdatert.';
$lang['nochoice']              = '(ingen andre mulige valg)';
$lang['locked']                = 'Innstillingene kan ikke oppdateres. Hvis dette ikke er meningen,<br />
forsikre deg om at fila med de lokale innstillingene har korrekt filnavn<br/>
og tillatelser.';
$lang['danger']                = 'Advarsel: Endring av dette valget kan føre til at wiki og konfigurasjonsmenyen ikke blir tilgjengelig.';
$lang['warning']               = 'Advarsel: Endring av dette valget kan føre til utilsiktet atferd.

';
$lang['security']              = 'Sikkerhetsadvarsel: Endring av dette valget kan innebære en sikkerhetsrisiko.';
$lang['_configuration_manager'] = 'Konfigurasjonsinnstillinger';
$lang['_header_dokuwiki']      = 'Innstillinger for DokuWiki';
$lang['_header_plugin']        = 'Innstillinger for programtillegg';
$lang['_header_template']      = 'Innstillinger for maler';
$lang['_header_undefined']     = 'Udefinerte innstillinger';
$lang['_basic']                = 'Grunnleggende innstillinger';
$lang['_display']              = 'Innstillinger for visning av sider';
$lang['_authentication']       = 'Innstillinger for autentisering';
$lang['_anti_spam']            = 'Anti-Spam innstillinger';
$lang['_editing']              = 'Innstillinger for redigering';
$lang['_links']                = 'Innstillinger for lenker';
$lang['_media']                = 'Innstillinger for mediafiler';
$lang['_notifications']        = 'Melding';
$lang['_syndication']          = 'Informasjonsstrøm (RSS)';
$lang['_advanced']             = 'Avanserte innstillinger';
$lang['_network']              = 'Nettverksinnstillinger';
$lang['_msg_setting_undefined'] = 'Ingen innstillingsmetadata';
$lang['_msg_setting_no_class'] = 'Ingen innstillingsklasse';
$lang['_msg_setting_no_default'] = 'Ingen standard verdi';
$lang['title']                 = 'Navn på Wikien';
$lang['start']                 = 'Sidenavn på forsiden';
$lang['lang']                  = 'Språk';
$lang['template']              = 'Mal';
$lang['tagline']               = 'Slagord (dersom malen støtter dette)';
$lang['sidebar']               = 'Sidestolpens navn (dersom malen støtter dette), la stå tomt for å slå av sidestolpen';
$lang['license']               = 'Under hvilken lisens skal ditt innhold utgis?';
$lang['savedir']               = 'Mappe for lagring av data';
$lang['basedir']               = 'Sti til hovekatalog (eks. <code>/dokuwiki/</code>). La stå blank for automatisk deteksjon.';
$lang['baseurl']               = 'Nettadresse til server (eks. <code>http://www.yourserver.com</code>).  La stå blank for automatisk deteksjon.';
$lang['cookiedir']             = 'Sti for informasjonskapsler. La stå blankt for å bruke nettadresse til server.';
$lang['dmode']                 = 'Rettigheter for nye mapper';
$lang['fmode']                 = 'Rettigheter for nye filer';
$lang['allowdebug']            = 'Tillat feilsøking <b>skru av om det ikke behøves!</b>';
$lang['recent']                = 'Siste endringer';
$lang['recent_days']           = 'Hvor lenge skal nylige endringer beholdes (dager)';
$lang['breadcrumbs']           = 'Antall nylig besøkte sider som vises';
$lang['youarehere']            = 'Vis hvor i hvilke(t) navnerom siden er';
$lang['fullpath']              = 'Vis full sti til sider i bunnteksten';
$lang['typography']            = 'Gjør typografiske erstatninger';
$lang['dformat']               = 'Datoformat (se <a href="http://php.net/strftime">PHPs datofunksjon</a>)';
$lang['signature']             = 'Signatur';
$lang['showuseras']            = 'Hva som skal med når man viser brukeren som sist redigerte en side.';
$lang['toptoclevel']           = 'Toppnivå for innholdsfortegnelse';
$lang['tocminheads']           = 'Minimum antall overskrifter som bestemmer om innholdsbetegnelse skal bygges.';
$lang['maxtoclevel']           = 'Maksimalt antall nivåer i innholdsfortegnelse';
$lang['maxseclevel']           = 'Maksimalt nivå for redigering av seksjon';
$lang['camelcase']             = 'Gjør KamelKasse til lenke automatisk';
$lang['deaccent']              = 'Rensk sidenavn';
$lang['useheading']            = 'Bruk første overskrift som tittel';
$lang['sneaky_index']          = 'DokuWiki vil som standard vise alle navnerom i innholdsfortegnelsen. Hvis du skrur på dette alternativet vil brukere bare se de navnerommene der de har lesetilgang. Dette kan føre til at tilgjengelige undernavnerom skjules. Det kan gjøre innholdsfortegnelsen ubrukelig med enkelte ACL-oppsett.';
$lang['hidepages']             = 'Skjul sider fra automatiske lister (regulære uttrykk)';
$lang['useacl']                = 'Bruk lister for adgangskontroll (ACL)';
$lang['autopasswd']            = 'Generer passord automatisk';
$lang['authtype']              = 'Autentiseringsmetode';
$lang['passcrypt']             = 'Metode for kryptering av passord';
$lang['defaultgroup']          = 'Standardgruppe';
$lang['superuser']             = 'Superbruker - en gruppe, bruker eller liste (kommaseparert) med full tilgang til alle sider og funksjoner uavhengig av ACL-innstillingene';
$lang['manager']               = 'Administrator - en gruppe, bruker eller liste (kommaseparert) med tilgang til visse administratorfunksjoner';
$lang['profileconfirm']        = 'Bekreft profilendringer med passord';
$lang['rememberme']            = 'Tillat permanente informasjonskapsler for innlogging (husk meg)';
$lang['disableactions']        = 'Skru av følgende DokuWiki-kommandoer';
$lang['disableactions_check']  = 'Sjekk';
$lang['disableactions_subscription'] = 'Meld på/av';
$lang['disableactions_wikicode'] = 'Vis kildekode/eksporter rådata';
$lang['disableactions_profile_delete'] = 'Slett egen konto';
$lang['disableactions_other']  = 'Andre kommandoer (kommaseparert)';
$lang['disableactions_rss']    = 'XML-informasjonsstrøm (RSS)';
$lang['auth_security_timeout'] = 'Autentisering utløper etter (sekunder)';
$lang['securecookie']          = 'Skal informasjonskapsler satt via HTTPS kun sendes via HTTPS av nettleseren? Skal ikke velges dersom bare innloggingen til din wiki er sikret med SSL, og annen navigering  på wikien er usikret.';
$lang['remote']                = 'Slå på det eksterne API-grensesnittet. Dette gir andre program tilgang til denne wikien via XML-RPC, eller via andre mekanismer.';
$lang['remoteuser']            = 'Begrens ekstern API-tilgang til bare å gjelde denne kommaseparerte listen med grupper eller brukere. La stå tomt for å gi tilgang for alle.';
$lang['usewordblock']          = 'Blokker søppel basert på ordliste';
$lang['relnofollow']           = 'Bruk rel="nofollow" på eksterne lenker';
$lang['indexdelay']            = 'Forsinkelse før indeksering (sekunder)';
$lang['mailguard']             = 'Beskytt e-postadresser';
$lang['iexssprotect']          = 'Sjekk om opplastede filer inneholder skadelig JavaScrips- eller HTML-kode';
$lang['usedraft']              = 'Lagre kladd automatisk ved redigering';
$lang['htmlok']                = 'Tillat HTML';
$lang['phpok']                 = 'Tillat PHP';
$lang['locktime']              = 'Maksimal alder på låsefiler (sekunder)';
$lang['cachetime']             = 'Maksimal alder på hurtiglager (sekunder)';
$lang['target____wiki']        = 'Mål for interne lenker';
$lang['target____interwiki']   = 'Mål for interwiki-lenker';
$lang['target____extern']      = 'Mål for eksterne lenker';
$lang['target____media']       = 'Mål for lenker til mediafiler';
$lang['target____windows']     = 'Mål for lenker til nettverksstasjoner i Windows';
$lang['mediarevisions']        = 'Slå på mediaversjonering?';
$lang['refcheck']              = 'Sjekk referanser før mediafiler slettes';
$lang['gdlib']                 = 'Versjon av libGD';
$lang['im_convert']            = 'Sti til ImageMagicks konverteringsverktøy';
$lang['jpg_quality']           = 'JPEG-kvalitet (0-100)';
$lang['fetchsize']             = 'Maksimal størrelse (i byte) fetch.php kan laste eksternt';
$lang['subscribers']           = 'Åpne for abonnement på endringer av en side';
$lang['subscribe_time']        = 'Hvor lenge det skal gå mellom utsending av e-poster med endringer (i sekunder). Denne verdien bør være mindre enn verdien i recent_days.';
$lang['notify']                = 'Send meldinger om endringer til denne e-postadressen';
$lang['registernotify']        = 'Send info om nylig registrerte brukere til denne e-postadressen';
$lang['mailfrom']              = 'Avsenderadresse for automatiske e-poster';
$lang['mailprefix']            = 'Tekst å henge på i starten av emne-feltet i automatiske e-poster. La stå blank for å bruke wikiens tittel. ';
$lang['htmlmail']              = 'Send e-poster som HTMLmultipart-form, e-postene vil da se bedre ut. Skru av for å sende e-poster i ren-tekstform.';
$lang['sitemap']               = 'Lag Google-sidekart (dager)';
$lang['rss_type']              = 'Type XML-feed';
$lang['rss_linkto']            = 'XML-feed lenker til';
$lang['rss_content']           = 'Hva skal vises i XML-feed elementer?';
$lang['rss_update']            = 'Intervall for oppdatering av XML-feed (sekunder)';
$lang['rss_show_summary']      = 'Vis redigeringskommentar i tittelen på elementer i XML-feed ';
$lang['rss_media']             = 'Hvilke typer endringer skal listes i XML-strømmen?';
$lang['updatecheck']           = 'Se etter oppdateringer og sikkerhetsadvarsler? Denne funksjonen er avhengig av å kontakte update.dokuwiki.org.';
$lang['userewrite']            = 'Bruk pene URLer';
$lang['useslash']              = 'Bruk / som skilletegn mellom navnerom i URLer';
$lang['sepchar']               = 'Skilletegn mellom ord i sidenavn';
$lang['canonical']             = 'Bruk fulle URLer (i stedet for relative)';
$lang['fnencode']              = 'Metode for å kode ikke-ASCII-filnavn';
$lang['autoplural']            = 'Se etter flertallsformer i lenker';
$lang['compression']           = 'Metode for komprimering av gamle filer';
$lang['gzip_output']           = 'Bruk gzip Content-Encoding for XHTML';
$lang['compress']              = 'Kompakt CSS og JavaScript';
$lang['cssdatauri']            = 'Opp til denne størrelsen (i byte) skal bilder som er vist til i CSS-filer kodes direkte inn i fila for å redusere antall HTTP-forespørsler. Denne teknikken fungerer ikke i IE < 8! Mellom <code>400</code> og <code>600</code> bytes er fornuftige verdier. Bruk <code>0</code> for å skru av funksjonen.';
$lang['send404']               = 'Send "HTTP 404/Page Not Found" for ikke-eksisterende sider';
$lang['broken_iua']            = 'Er funksjonen ignore_user_abort på ditt system ødelagt? Dette kan gjøre at indeksering av søk ikke fungerer. Dette er et kjent problem med IIS+PHP/CGI. Se <a href="http://bugs.splitbrain.org/?do=details&amp;task_id=852">Bug 852</a> for mer informasjon.';
$lang['xsendfile']             = 'Bruk X-Sendfile header for å la webserver levere statiske filer? Din webserver må støtte dette.';
$lang['renderer_xhtml']        = 'Renderer til bruk for wiki-output (XHTML)';
$lang['renderer__core']        = '%s (dokuwikikjerne)';
$lang['renderer__plugin']      = '%s (programutvidelse)';
$lang['dnslookups']            = 'Dokuwiki vil, for sider som blir redigert, slå opp vertsnavn for brukere med eksterne IP-adresse Hvis du har en treg, eller en ikke fungerende DNS-server  bør du deaktivere dette alternativet';
$lang['proxy____host']         = 'Navn på proxyserver';
$lang['proxy____port']         = 'Port på på proxyserver';
$lang['proxy____user']         = 'Brukernavn på proxyserver';
$lang['proxy____pass']         = 'Passord på proxyserver';
$lang['proxy____ssl']          = 'Bruk SSL for å koble til proxyserver';
$lang['proxy____except']       = 'Regulært uttrykk for URLer som ikke trenger bruk av proxy';
$lang['safemodehack']          = 'Bruk safemode-hack';
$lang['ftp____host']           = 'FTP-server for safemode-hack';
$lang['ftp____port']           = 'FTP-port for safemode-hack';
$lang['ftp____user']           = 'FTP-brukernavn for safemode-hack';
$lang['ftp____pass']           = 'FTP-passord for safemode-hack';
$lang['ftp____root']           = 'FTP-rotmappe for safemode-hack';
$lang['license_o_']            = 'Ingen valgt';
$lang['typography_o_0']        = 'ingen';
$lang['typography_o_1']        = 'Kun doble anførselstegn';
$lang['typography_o_2']        = 'Alle anførselstegn (virker ikke alltid)';
$lang['userewrite_o_0']        = 'ingen';
$lang['userewrite_o_1']        = 'Apache (.htaccess)';
$lang['userewrite_o_2']        = 'DokuWiki internt';
$lang['deaccent_o_0']          = 'av';
$lang['deaccent_o_1']          = 'fjern aksenter';
$lang['deaccent_o_2']          = 'bytt til kun latinske bokstaver';
$lang['gdlib_o_0']             = 'GD lib ikke tilgjengelig';
$lang['gdlib_o_1']             = 'Versjon 1.x';
$lang['gdlib_o_2']             = 'Oppdag automatisk';
$lang['rss_type_o_rss']        = 'RSS 0.91';
$lang['rss_type_o_rss1']       = 'RSS 1.0';
$lang['rss_type_o_rss2']       = 'RSS 2.0';
$lang['rss_type_o_atom']       = 'Atom 0.3';
$lang['rss_type_o_atom1']      = 'Atom 1.0';
$lang['rss_content_o_abstract'] = 'Ingress';
$lang['rss_content_o_diff']    = 'Ulikh. sammenslått';
$lang['rss_content_o_htmldiff'] = 'HTML-formatert endr. tabell';
$lang['rss_content_o_html']    = 'Full HTML sideinnhold';
$lang['rss_linkto_o_diff']     = 'endringsvisning';
$lang['rss_linkto_o_page']     = 'den endrede siden';
$lang['rss_linkto_o_rev']      = 'liste over endringer';
$lang['rss_linkto_o_current']  = 'den nåværende siden';
$lang['compression_o_0']       = 'ingen';
$lang['compression_o_gz']      = 'gzip';
$lang['compression_o_bz2']     = 'bz2';
$lang['xsendfile_o_0']         = 'ikke bruk';
$lang['xsendfile_o_1']         = 'Proprietær lighttpd header (før release 1.5)';
$lang['xsendfile_o_2']         = 'Standard X-Sendfile header';
$lang['xsendfile_o_3']         = 'Proprietær Nginx X-Accel-Redirect header';
$lang['showuseras_o_loginname'] = 'Brukernavn';
$lang['showuseras_o_username'] = 'Brukerens fulle navn';
$lang['showuseras_o_username_link'] = 'Brukers fulle navn som interwiki-brukerlenke';
$lang['showuseras_o_email']    = 'Brukerens e-postadresse (tilpasset i henhold til mailguar-instilling)';
$lang['showuseras_o_email_link'] = 'Brukerens e-postaddresse som "mailto:"-lenke';
$lang['useheading_o_0']        = 'Aldri';
$lang['useheading_o_navigation'] = 'Kun navigering';
$lang['useheading_o_content']  = 'Kun wiki-innhold';
$lang['useheading_o_1']        = 'Alltid';
$lang['readdircache']          = 'Maksimal alder for mellomlagring av mappen med søkeindekser (sekunder)';
