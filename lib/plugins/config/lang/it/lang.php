<?php
/**
 * Italian language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 * @author     Silvia Sargentoni <polinnia@tin.it>
 */

// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu']       = 'Configurazione Wiki';

$lang['error']      = 'Impostazioni non aggiornate a causa di un valore non corretto, controlla le modifiche apportate e salva di nuovo.
                       <br />I valori non corretti sono evidenziati da un riquadro rosso.';
$lang['updated']    = 'Aggiornamento impostazioni riuscito.';
$lang['nochoice']   = '(nessun\'altra scelta disponibile)';
$lang['locked']     = 'Il file di configurazione non può essere aggiornato, se questo non è intenzionale, <br />
                       assicurati che il nome e i permessi del file contenente la configurazione locale siano corretti.';


/* -------------------- Config Options --------------------------- */

$lang['fmode']       = 'Permessi per i nuovi file';
$lang['dmode']       = 'Permessi per le nuove directory';
$lang['lang']        = 'Lingua';
$lang['basedir']     = 'Directory di base';
$lang['baseurl']     = 'URL di base';
$lang['savedir']     = 'Directory per il salvataggio dei dati';
$lang['start']       = 'Nome della pagina iniziale';
$lang['title']       = 'Titolo del wiki';
$lang['template']    = 'Template';
$lang['fullpath']    = 'Mostra il percorso completo delle pagine';
$lang['recent']      = 'Ultime modifiche';
$lang['breadcrumbs'] = 'Numero di breadcrumb';
$lang['youarehere']  = 'Breadcrumb gerarchici';
$lang['typography']  = 'Abilita la sostituzione tipografica';
$lang['htmlok']      = 'Consenti HTML incorporato';
$lang['phpok']       = 'Consenti PHP incorporato';
$lang['dformat']     = 'Formato delle date (vedi la funzione <a href="http://www.php.net/date">data</a> di PHP)';
$lang['signature']   = 'Firma';
$lang['toptoclevel'] = 'Livello superiore per l\'indice';
$lang['maxtoclevel'] = 'Numero massimo di livelli per l\'indice';
$lang['maxseclevel'] = 'Livello massimo per le sezioni modificabili';
$lang['camelcase']   = 'Usa CamelCase per i collegamenti';
$lang['deaccent']    = 'Pulizia dei nomi di pagina';
$lang['useheading']  = 'Usa la prima intestazione come nome di pagina';
$lang['refcheck']    = 'Controlla i riferimenti ai file';
$lang['refshow']     = 'Numero di riferimenti da visualizzare';
$lang['allowdebug']  = 'Abilita il debug <b>(disabilitare se non serve!)</b>';

$lang['usewordblock']= 'Blocca lo spam in base alla blacklist';
$lang['indexdelay']  = 'Intervallo di tempo prima dell\'indicizzazione';
$lang['relnofollow'] = 'Usa rel="nofollow"';
$lang['mailguard']   = 'Oscuramento indirizzi e-mail';

/* Authentication Options */
$lang['useacl']      = 'Usa lista di controllo accessi (ACL)';
$lang['openregister']= 'Consenti agli utenti di registrarsi';
$lang['autopasswd']  = 'Genera password in automatico';
$lang['resendpasswd']= 'Consenti l\'invio di nuove password';
$lang['authtype']    = 'Sistema di autenticazione';
$lang['passcrypt']   = 'Metodo di cifratura password';
$lang['defaultgroup']= 'Gruppo predefinito';
$lang['superuser']   = 'Amministratore';
$lang['profileconfirm'] = 'Richiedi la password per modifiche al profilo';

/* Advanced Options */
$lang['userewrite']  = 'Usa il rewrite delle URL';
$lang['useslash']    = 'Usa lo slash come separatore nelle URL';
$lang['usedraft']    = 'Salva una bozza in automatico in fase di modifica';
$lang['sepchar']     = 'Separatore di parole nei nomi di pagina';
$lang['canonical']   = 'Usa URL canoniche';
$lang['autoplural']  = 'Controlla il plurale nei collegamenti';
$lang['usegzip']     = 'Usa gzip (per l\'archivio)';
$lang['cachetime']   = 'Durata della cache (sec)';
$lang['purgeonadd']  = 'Pulisci la cache quando si aggiungono nuove pagine';
$lang['locktime']    = 'Durata dei file di lock (sec)';
$lang['notify']      = 'Invia notifiche sulle modifiche a questo indirizzo';
$lang['mailfrom']    = 'Mittente per le mail automatiche';
$lang['gdlib']       = 'Versione GD Lib ';
$lang['im_convert']  = 'Percorso per il convertitore di ImageMagick';
$lang['spellchecker']= 'Abilita il controllo ortografico';
$lang['subscribers'] = 'Abilita la sottoscrizione alle pagine';
$lang['compress']    = 'Comprimi i file CSS e javascript';
$lang['hidepages']   = 'Nascondi le pagine che soddisfano la condizione (inserire un\'espressione regolare)';
$lang['send404']     = 'Invia "HTTP 404/Page Not Found" per le pagine inesistenti';
$lang['sitemap']     = 'Genera una sitemap Google (giorni)';

$lang['rss_type']    = 'Tipo di feed XML';
$lang['rss_linkto']  = 'Collega i feed XML a';
$lang['rss_update']  = 'Intervallo di aggiornamento dei feed XML (sec)';

/* Target options */
$lang['target____wiki']      = 'Finestra target per i collegamenti interni';
$lang['target____interwiki'] = 'Finestra target per i collegamenti interwiki';
$lang['target____extern']    = 'Finestra target per i collegamenti esterni';
$lang['target____media']     = 'Finestra target per i collegamenti ai file';
$lang['target____windows']   = 'Finestra target per i collegamenti alle risorse condivise';

/* Proxy Options */
$lang['proxy____host'] = 'Nome server proxy';
$lang['proxy____port'] = 'Porta proxy';
$lang['proxy____user'] = 'Nome utente proxy';
$lang['proxy____pass'] = 'Password proxy';
$lang['proxy____ssl']  = 'Usa SSL per connetterti al proxy';

/* Safemode Hack */
$lang['safemodehack'] = 'Abilita safemode hack';
$lang['ftp____host'] = 'Server FTP per safemode hack';
$lang['ftp____port'] = 'Porta FTP per safemode hack';
$lang['ftp____user'] = 'Nome utente FTP per safemode hack';
$lang['ftp____pass'] = 'Password FTP per safemode hack';
$lang['ftp____root'] = 'Root directory FTP per safemode hack';

/* userewrite options */
$lang['userewrite_o_0'] = 'nessuno';
$lang['userewrite_o_1'] = '.htaccess';
$lang['userewrite_o_2'] = 'DokuWiki';

/* deaccent options */
$lang['deaccent_o_0'] = 'disabilitata';
$lang['deaccent_o_1'] = 'rimuovi gli accenti';
$lang['deaccent_o_2'] = 'romanizza';

/* gdlib options */
$lang['gdlib_o_0'] = 'GD Lib non disponibile';
$lang['gdlib_o_1'] = 'Versione 1.x';
$lang['gdlib_o_2'] = 'Rileva automaticamente';

/* rss_type options */
$lang['rss_type_o_rss']  = 'RSS 0.91';
$lang['rss_type_o_rss1'] = 'RSS 1.0';
$lang['rss_type_o_rss2'] = 'RSS 2.0';
$lang['rss_type_o_atom'] = 'Atom 0.3';

/* rss_linkto options */
$lang['rss_linkto_o_diff']    = 'vista differenze';
$lang['rss_linkto_o_page']    = 'pagina revisionata';
$lang['rss_linkto_o_rev']     = 'elenco revisioni';
$lang['rss_linkto_o_current'] = 'pagina corrente';

