<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Christopher Smith <chris@jalakai.co.uk>
 * @author Silvia Sargentoni <polinnia@tin.it>
 * @author Pietro Battiston toobaz@email.it
 * @author Diego Pierotto ita.translations@tiscali.it
 * @author ita.translations@tiscali.it
 * @author Lorenzo Breda <lbreda@gmail.com>
 * @author snarchio@alice.it
 * @author robocap <robocap1@gmail.com>
 * @author Osman Tekin osman.tekin93@hotmail.it
 * @author Jacopo Corbetta <jacopo.corbetta@gmail.com>
 * @author Matteo Pasotti <matteo@xquiet.eu>
 * @author snarchio@gmail.com
 * @author Torpedo <dgtorpedo@gmail.com>
 * @author Riccardo <riccardofila@gmail.com>
 * @author Paolo <paolopoz12@gmail.com>
 */
$lang['menu']                  = 'Configurazione Wiki';
$lang['error']                 = 'Impostazioni non aggiornate a causa di un valore non corretto, controlla le modifiche apportate e salva di nuovo.
<br />I valori non corretti sono evidenziati da un riquadro rosso.';
$lang['updated']               = 'Aggiornamento impostazioni riuscito.';
$lang['nochoice']              = '(nessun\'altra scelta disponibile)';
$lang['locked']                = 'Il file di configurazione non può essere aggiornato, se questo non è intenzionale, <br />
assicurati che il nome e i permessi del file contenente la configurazione locale siano corretti.';
$lang['danger']                = 'Attenzione: cambiare questa opzione può rendere inaccessibile il wiki e il menu di configurazione.';
$lang['warning']               = 'Avviso: cambiare questa opzione può causare comportamenti indesiderati.';
$lang['security']              = 'Avviso di sicurezza: vambiare questa opzione può esporre a rischi di sicurezza.';
$lang['_configuration_manager'] = 'Configurazione Wiki';
$lang['_header_dokuwiki']      = 'Impostazioni DokuWiki';
$lang['_header_plugin']        = 'Impostazioni Plugin';
$lang['_header_template']      = 'Impostazioni Modello';
$lang['_header_undefined']     = 'Impostazioni non definite';
$lang['_basic']                = 'Impostazioni Base';
$lang['_display']              = 'Impostazioni Visualizzazione';
$lang['_authentication']       = 'Impostazioni Autenticazione';
$lang['_anti_spam']            = 'Impostazioni Anti-Spam';
$lang['_editing']              = 'Impostazioni Modifica';
$lang['_links']                = 'Impostazioni Collegamenti';
$lang['_media']                = 'Impostazioni File';
$lang['_notifications']        = 'Impostazioni di notifica';
$lang['_syndication']          = 'Impostazioni di collaborazione';
$lang['_advanced']             = 'Impostazioni Avanzate';
$lang['_network']              = 'Impostazioni Rete';
$lang['_msg_setting_undefined'] = 'Nessun metadato definito.';
$lang['_msg_setting_no_class'] = 'Nessuna classe definita.';
$lang['_msg_setting_no_default'] = 'Nessun valore predefinito.';
$lang['title']                 = 'Titolo del wiki';
$lang['start']                 = 'Nome della pagina iniziale';
$lang['lang']                  = 'Lingua';
$lang['template']              = 'Modello';
$lang['tagline']               = 'Tagline (se il template lo supporta)';
$lang['sidebar']               = 'Nome pagina in barra laterale (se il template lo supporta), il campo vuoto disabilita la barra laterale';
$lang['license']               = 'Sotto quale licenza vorresti rilasciare il tuo contenuto?';
$lang['savedir']               = 'Directory per il salvataggio dei dati';
$lang['basedir']               = 'Directory di base';
$lang['baseurl']               = 'URL di base';
$lang['cookiedir']             = 'Percorso cookie. Lascia in bianco per usare baseurl.';
$lang['dmode']                 = 'Permessi per le nuove directory';
$lang['fmode']                 = 'Permessi per i nuovi file';
$lang['allowdebug']            = 'Abilita il debug <b>(disabilitare se non serve!)</b>';
$lang['recent']                = 'Ultime modifiche';
$lang['recent_days']           = 'Quante modifiche recenti tenere (giorni)';
$lang['breadcrumbs']           = 'Numero di breadcrumb';
$lang['youarehere']            = 'Breadcrumb gerarchici';
$lang['fullpath']              = 'Mostra il percorso completo delle pagine';
$lang['typography']            = 'Abilita la sostituzione tipografica';
$lang['dformat']               = 'Formato delle date (vedi la funzione <a href="http://php.net/strftime">strftime</a> di PHP)';
$lang['signature']             = 'Firma';
$lang['showuseras']            = 'Cosa visualizzare quando si mostra l\'ultimo utente che ha modificato una pagina';
$lang['toptoclevel']           = 'Livello superiore per l\'indice';
$lang['tocminheads']           = 'Ammontare minimo di intestazioni che determinano la creazione del TOC';
$lang['maxtoclevel']           = 'Numero massimo di livelli per l\'indice';
$lang['maxseclevel']           = 'Livello massimo per le sezioni modificabili';
$lang['camelcase']             = 'Usa CamelCase per i collegamenti';
$lang['deaccent']              = 'Pulizia dei nomi di pagina';
$lang['useheading']            = 'Usa la prima intestazione come nome di pagina';
$lang['sneaky_index']          = 'Normalmente, DokuWiki mostra tutte le categorie nella vista indice. Abilitando questa opzione, saranno nascoste quelle per cui l\'utente non ha il permesso in lettura. Questo potrebbe far sì che alcune sottocategorie accessibili siano nascoste. La pagina indice potrebbe quindi diventare inutilizzabile con alcune configurazioni dell\'ACL.';
$lang['hidepages']             = 'Nascondi le pagine che soddisfano la condizione (inserire un\'espressione regolare)';
$lang['useacl']                = 'Usa lista di controllo accessi (ACL)';
$lang['autopasswd']            = 'Genera password in automatico';
$lang['authtype']              = 'Sistema di autenticazione';
$lang['passcrypt']             = 'Metodo di cifratura password';
$lang['defaultgroup']          = 'Gruppo predefinito';
$lang['superuser']             = 'Amministratore - gruppo, utente o elenco di utenti separati da virgole (user1,@group1,user2) con accesso completo a tutte le pagine e le funzioni che riguardano le  impostazioni ACL';
$lang['manager']               = 'Gestore - gruppo, utente o elenco di utenti separati da virgole (user1,@group1,user2) con accesso a determinate funzioni di gestione';
$lang['profileconfirm']        = 'Richiedi la password per modifiche al profilo';
$lang['rememberme']            = 'Permetti i cookies di accesso permanenti (ricordami)';
$lang['disableactions']        = 'Disabilita azioni DokuWiki';
$lang['disableactions_check']  = 'Controlla';
$lang['disableactions_subscription'] = 'Sottoscrivi/Rimuovi sottoscrizione';
$lang['disableactions_wikicode'] = 'Mostra sorgente/Esporta Raw';
$lang['disableactions_profile_delete'] = 'Elimina il proprio account';
$lang['disableactions_other']  = 'Altre azioni (separate da virgola)';
$lang['disableactions_rss']    = 'XML Syndication (RSS)';
$lang['auth_security_timeout'] = 'Tempo di sicurezza per l\'autenticazione (secondi)';
$lang['securecookie']          = 'Devono i cookies impostati tramite HTTPS essere inviati al browser solo tramite HTTPS? Disattiva questa opzione solo quando l\'accesso al tuo wiki viene effettuato con il protocollo SSL ma la navigazione del wiki non risulta sicura.';
$lang['remote']                = 'Abilita il sistema di API remoto. Questo permette ad altre applicazioni di accedere al wiki tramite XML-RPC o altri meccanismi.';
$lang['remoteuser']            = 'Restringi l\'accesso dell\'aPI remota ai gruppi o utenti qui specificati separati da virgola. Lascia vuoto per dare accesso a chiunque.';
$lang['usewordblock']          = 'Blocca lo spam in base alla blacklist';
$lang['relnofollow']           = 'Usa rel="nofollow" nei collegamenti esterni';
$lang['indexdelay']            = 'Intervallo di tempo prima dell\'indicizzazione';
$lang['mailguard']             = 'Oscuramento indirizzi email';
$lang['iexssprotect']          = 'Controlla i file caricati in cerca di possibile codice JavaScript o HTML maligno.';
$lang['usedraft']              = 'Salva una bozza in automatico in fase di modifica';
$lang['htmlok']                = 'Consenti HTML incorporato';
$lang['phpok']                 = 'Consenti PHP incorporato';
$lang['locktime']              = 'Durata dei file di lock (sec)';
$lang['cachetime']             = 'Durata della cache (sec)';
$lang['target____wiki']        = 'Finestra di destinazione per i collegamenti interni';
$lang['target____interwiki']   = 'Finestra di destinazione per i collegamenti interwiki';
$lang['target____extern']      = 'Finestra di destinazione per i collegamenti esterni';
$lang['target____media']       = 'Finestra di destinazione per i collegamenti ai file';
$lang['target____windows']     = 'Finestra di destinazione per i collegamenti alle risorse condivise';
$lang['mediarevisions']        = 'Abilita Mediarevisions?';
$lang['refcheck']              = 'Controlla i riferimenti ai file';
$lang['gdlib']                 = 'Versione GD Lib ';
$lang['im_convert']            = 'Percorso per il convertitore di ImageMagick';
$lang['jpg_quality']           = 'Qualità di compressione JPG (0-100)';
$lang['fetchsize']             = 'Dimensione massima (bytes) scaricabile da fetch.php da extern';
$lang['subscribers']           = 'Permetti agli utenti la sottoscrizione alle modifiche delle pagine via e-mail';
$lang['subscribe_time']        = 'Tempo dopo il quale le liste di sottoscrizione e i riassunti vengono inviati (sec); Dovrebbe essere inferiore al tempo specificato in recent_days.';
$lang['notify']                = 'Invia notifiche sulle modifiche a questo indirizzo';
$lang['registernotify']        = 'Invia informazioni sui nuovi utenti registrati a questo indirizzo email';
$lang['mailfrom']              = 'Mittente per le mail automatiche';
$lang['mailprefix']            = 'Prefisso da inserire nell\'oggetto delle mail automatiche';
$lang['htmlmail']              = 'Invia email HTML multipart più gradevoli ma più ingombranti in dimensione. Disabilita per mail in puro testo.';
$lang['sitemap']               = 'Genera una sitemap Google (giorni)';
$lang['rss_type']              = 'Tipo di feed XML';
$lang['rss_linkto']            = 'Collega i feed XML a';
$lang['rss_content']           = 'Cosa mostrare negli elementi dei feed XML?';
$lang['rss_update']            = 'Intervallo di aggiornamento dei feed XML (sec)';
$lang['rss_show_summary']      = 'I feed XML riportano un sommario nel titolo';
$lang['rss_media']             = 'Quale tipo di cambiamento dovrebbe essere elencato nel feed XML?';
$lang['updatecheck']           = 'Controllare aggiornamenti e avvisi di sicurezza? DokuWiki deve contattare update.dokuwiki.org per questa funzione.';
$lang['userewrite']            = 'Usa il rewrite delle URL';
$lang['useslash']              = 'Usa la barra rovescia (slash) come separatore nelle URL';
$lang['sepchar']               = 'Separatore di parole nei nomi di pagina';
$lang['canonical']             = 'Usa URL canoniche';
$lang['fnencode']              = 'Metodo per codificare i filenames non-ASCII.';
$lang['autoplural']            = 'Controlla il plurale nei collegamenti';
$lang['compression']           = 'Usa la compressione per i file dell\'archivio';
$lang['gzip_output']           = 'Usa il Content-Encoding gzip per xhtml';
$lang['compress']              = 'Comprimi i file CSS e javascript';
$lang['cssdatauri']            = 'Dimensione massima in byte di un\'immagine che può essere integrata nel CSS per ridurre l\'overhead delle richieste HTTP. Da <code>400</code> a <code>600</code> bytes è un buon valore. Impostare a <code>0</code> per disabilitare.';
$lang['send404']               = 'Invia "HTTP 404/Pagina non trovata" per le pagine inesistenti';
$lang['broken_iua']            = 'La funzione ignore_user_abort non funziona sul tuo sistema? Questo potrebbe far sì che l\'indice di ricerca sia inutilizzabile. È noto che nella configurazione IIS+PHP/CGI non funziona. Vedi il<a href="http://bugs.splitbrain.org/?do=details&amp;task_id=852">Bug 852</a> per maggiori informazioni.';
$lang['xsendfile']             = 'Usare l\'header X-Sendfile per permettere al webserver di fornire file statici? Questa funzione deve essere supportata dal tuo webserver.';
$lang['renderer_xhtml']        = 'Renderer da usare per la visualizzazione del wiki (xhtml)';
$lang['renderer__core']        = '%s (dokuwiki)';
$lang['renderer__plugin']      = '%s (plugin)';
$lang['dnslookups']            = 'Dokuwiki farà il lookup dei nomi host per ricavare l\'indirizzo IP remoto degli utenti che modificano le pagine. Se hai un DNS lento o non funzionante o se non vuoi questa funzione, disabilita l\'opzione';
$lang['jquerycdn']             = 'Vuoi che gli script jQuery e jQuery UI siano caricati da una CDN? Questo richiederà richieste HTTP aggiuntive ma i file potrebbero caricarsi più velocemente e gli utenti potrebbero averli già in cache.';
$lang['jquerycdn_o_0']         = 'Nessuna CDN, solo consegna locale';
$lang['jquerycdn_o_jquery']    = 'CDN presso code.jquery.com';
$lang['jquerycdn_o_cdnjs']     = 'CDN presso cdnjs.com';
$lang['proxy____host']         = 'Nome server proxy';
$lang['proxy____port']         = 'Porta proxy';
$lang['proxy____user']         = 'Nome utente proxy';
$lang['proxy____pass']         = 'Password proxy';
$lang['proxy____ssl']          = 'Usa SSL per connetterti al proxy';
$lang['proxy____except']       = 'Espressioni regolari per far corrispondere le URLs per i quali i proxy dovrebbero essere ommessi.';
$lang['safemodehack']          = 'Abilita safemode hack';
$lang['ftp____host']           = 'Server FTP per safemode hack';
$lang['ftp____port']           = 'Porta FTP per safemode hack';
$lang['ftp____user']           = 'Nome utente FTP per safemode hack';
$lang['ftp____pass']           = 'Password FTP per safemode hack';
$lang['ftp____root']           = 'Directory principale FTP per safemode hack';
$lang['license_o_']            = 'Nessuna scelta';
$lang['typography_o_0']        = 'nessuno';
$lang['typography_o_1']        = 'Solo virgolette';
$lang['typography_o_2']        = 'Tutti (potrebbe non funzionare sempre)';
$lang['userewrite_o_0']        = 'nessuno';
$lang['userewrite_o_1']        = '.htaccess';
$lang['userewrite_o_2']        = 'DokuWiki';
$lang['deaccent_o_0']          = 'disabilitata';
$lang['deaccent_o_1']          = 'rimuovi gli accenti';
$lang['deaccent_o_2']          = 'romanizza';
$lang['gdlib_o_0']             = 'GD Lib non disponibile';
$lang['gdlib_o_1']             = 'Versione 1.x';
$lang['gdlib_o_2']             = 'Rileva automaticamente';
$lang['rss_type_o_rss']        = 'RSS 0.91';
$lang['rss_type_o_rss1']       = 'RSS 1.0';
$lang['rss_type_o_rss2']       = 'RSS 2.0';
$lang['rss_type_o_atom']       = 'Atom 0.3';
$lang['rss_type_o_atom1']      = 'Atom 1.0';
$lang['rss_content_o_abstract'] = 'Sunto';
$lang['rss_content_o_diff']    = 'Diff unificata';
$lang['rss_content_o_htmldiff'] = 'Tabella delle diff formattata HTML';
$lang['rss_content_o_html']    = 'Tutto il contenuto della pagina in HTML';
$lang['rss_linkto_o_diff']     = 'vista differenze';
$lang['rss_linkto_o_page']     = 'pagina revisionata';
$lang['rss_linkto_o_rev']      = 'elenco revisioni';
$lang['rss_linkto_o_current']  = 'pagina attuale';
$lang['compression_o_0']       = 'nessuna';
$lang['compression_o_gz']      = 'gzip';
$lang['compression_o_bz2']     = 'bz2';
$lang['xsendfile_o_0']         = 'non usare';
$lang['xsendfile_o_1']         = 'Header proprietario lighttpd (prima della versione 1.5)';
$lang['xsendfile_o_2']         = 'Header standard X-Sendfile';
$lang['xsendfile_o_3']         = 'Header proprietario Nginx X-Accel-Redirect';
$lang['showuseras_o_loginname'] = 'Nome utente';
$lang['showuseras_o_username'] = 'Nome completo dell\'utente';
$lang['showuseras_o_username_link'] = 'Nome completo dell\'utente come link interwiki';
$lang['showuseras_o_email']    = 'Indirizzo email dell\'utente (offuscato in base alle impostazioni di sicurezza posta)';
$lang['showuseras_o_email_link'] = 'Indirizzo email dell\'utente come collegamento mailto:';
$lang['useheading_o_0']        = 'Mai';
$lang['useheading_o_navigation'] = 'Solo navigazione';
$lang['useheading_o_content']  = 'Solo contenuto wiki';
$lang['useheading_o_1']        = 'Sempre';
$lang['readdircache']          = 'Tempo massimo per le readdir cache (sec)';
