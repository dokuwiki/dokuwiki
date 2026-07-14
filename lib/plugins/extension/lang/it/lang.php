<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Roberto Bellingeri <bellingeri@netguru.it>
 * @author Filippo <abrickslife@gmail.com>
 * @author Francesco <francesco.cavalli@hotmail.com>
 * @author Fabio <fabioslurp@yahoo.it>
 * @author Torpedo <dgtorpedo@gmail.com>
 * @author Maurizio <mcannavo@katamail.com>
 */
$lang['menu']                  = 'Manager delle Extension';
$lang['tab_plugins']           = 'Plugin Installati';
$lang['tab_templates']         = 'Template Installati';
$lang['tab_search']            = 'Ricerca e Installazione';
$lang['tab_install']           = 'Installazione Manuale';
$lang['notimplemented']        = 'Questa funzionalità non è ancora stata implementata';
$lang['pluginlistsaveerror']   = 'Si è verificato un errore durante il salvataggio dell\'elenco dei plugin';
$lang['unknownauthor']         = 'Autore sconosciuto';
$lang['unknownversion']        = 'Revisione sconosciuta';
$lang['btn_info']              = 'Mostra maggiori informazioni';
$lang['btn_update']            = 'Aggiorna';
$lang['btn_uninstall']         = 'Disinstalla';
$lang['btn_enable']            = 'Abilita';
$lang['btn_disable']           = 'Disabilita';
$lang['btn_install']           = 'Installa';
$lang['btn_reinstall']         = 'Reinstalla';
$lang['js']['reallydel']       = 'Sicuro di disinstallare questa estensione?';
$lang['js']['display_viewoptions'] = 'Opzioni di Visualizzazione:';
$lang['js']['display_enabled'] = 'abilitato';
$lang['js']['display_disabled'] = 'disabilitato';
$lang['js']['display_updatable'] = 'aggiornabile';
$lang['js']['close']           = 'Clicca per chiudere';
$lang['js']['filter']          = 'Mostra solo le estensioni aggiornabili';
$lang['search_for']            = 'Extension di Ricerca:';
$lang['search']                = 'Cerca';
$lang['extensionby']           = '<strong>%s</strong> da %s';
$lang['screenshot']            = 'Screenshot di %s';
$lang['popularity']            = 'Popolarità: %s%%';
$lang['homepage_link']         = 'Documenti';
$lang['bugs_features']         = 'Bug';
$lang['tags']                  = 'Tag:';
$lang['author_hint']           = 'Cerca estensioni per questo autore';
$lang['installed']             = 'Installato:';
$lang['downloadurl']           = 'URL download:';
$lang['repository']            = 'Repository';
$lang['unknown']               = '<em>sconosciuto</em>';
$lang['installed_version']     = 'Versione installata';
$lang['install_date']          = 'Il tuo ultimo aggiornamento:';
$lang['available_version']     = 'Versione disponibile:';
$lang['compatible']            = 'Compatibile con:';
$lang['depends']               = 'Dipende da:';
$lang['similar']               = 'Simile a:';
$lang['conflicts']             = 'Conflitto con:';
$lang['donate']                = 'Simile a questo?';
$lang['donate_action']         = 'Paga un caffè all\'autore!';
$lang['repo_retry']            = 'Riprova';
$lang['provides']              = 'Fornisce:';
$lang['status']                = 'Status:';
$lang['status_installed']      = 'installato';
$lang['status_not_installed']  = 'non installato';
$lang['status_protected']      = 'protetto';
$lang['status_enabled']        = 'abilitato';
$lang['status_disabled']       = 'disabilitato';
$lang['status_unmodifiable']   = 'inmodificabile';
$lang['status_plugin']         = 'plugin';
$lang['status_template']       = 'modello';
$lang['status_bundled']        = 'accoppiato';
$lang['msg_enabled']           = 'Plugin %s abilitato';
$lang['msg_disabled']          = 'Plugin %s disabilitato';
$lang['msg_delete_success']    = 'Estensione %s disinstallata';
$lang['msg_delete_failed']     = 'Disinstallazione dell\'Extension %s fallita';
$lang['msg_install_success']   = 'Estensione %s installata correttamente';
$lang['msg_update_success']    = 'Estensione %s aggiornata correttamente';
$lang['msg_upload_failed']     = 'Caricamento del file fallito';
$lang['msg_nooverwrite']       = 'L\'estensione %s esiste già e non è stata sovrascritta; per sovrascriverla, seleziona l\'opzione "overwrite" o "sovrascrivi"';
$lang['missing_dependency']    = 'Dipendenza mancante o disabilitata: %s';
$lang['found_conflict']        = 'Questa estensione è contrassegnata come in conflitto con le seguenti estensioni installate: %s';
$lang['security_issue']        = 'Problema di sicurezza: %s';
$lang['security_warning']      = 'Avvertimento di sicurezza: %s';
$lang['update_message']        = 'Messaggio di aggiornamento: %s';
$lang['wrong_folder']          = 'Plugin non installato correttamente: rinomina la directory del plugin "%s" in "%s".';
$lang['url_change']            = 'URL cambiato: l\'URL per il download è cambiato dall\'ultima volta che è stato utilizzato. Controlla se il nuovo URL è valido prima di aggiornare l\'estensione.
Nuovo: %s
Vecchio: %s';
$lang['error_badurl']          = 'URLs deve iniziare con http o https';
$lang['error_dircreate']       = 'Impossibile creare una cartella temporanea per ricevere il download';
$lang['error_download']        = 'Impossibile scaricare il file: %s %s %s';
$lang['error_decompress']      = 'Impossibile decomprimere il file scaricato. Ciò può dipendere da errori in fase di download, nel qual caso dovreste ripetere l\'operazione; oppure il formato di compressione è sconosciuto, e in questo caso dovrete scaricare e installare manualmente.';
$lang['error_findfolder']      = 'Impossibile identificare la directory dell\'extension, dovrete scaricare e installare manualmente';
$lang['error_copy']            = 'C\'è stato un errore di copia dei file mentre si tentava di copiare i file per la directory <em>%s</em>: il disco potrebbe essere pieno o i pemessi di accesso ai file potrebbero essere sbagliati. Questo potrebbe aver causato una parziale installazione dei plugin lasciando il tuo wiki instabile';
$lang['error_copy_read']       = 'Impossibile leggere la cartella %s';
$lang['error_copy_mkdir']      = 'Impossibile creare la cartella %s';
$lang['error_copy_copy']       = 'Impossibile copiare %s in %s';
$lang['error_archive_read']    = 'Impossibile aprire l\'archivio %s per la lettura';
$lang['error_archive_extract'] = 'Impossibile estrarre l\'archivio %s: %s';
$lang['error_uninstall_protected'] = 'L\'estensione %s è protetta e non può essere disinstallata';
$lang['error_uninstall_dependants'] = 'L\'estensione %s è ancora richiesta da %s e quindi non può essere disinstallata';
$lang['error_disable_protected'] = 'L\'estensione %s è protetta e non può essere disabilitata';
$lang['error_disable_dependants'] = 'L\'estensione %s è ancora richiesta da %s e quindi non può essere disabilitata';
$lang['error_nourl']           = 'Non è stato trovato alcun URL di download per l\'estensione %s';
$lang['error_notinstalled']    = 'L\'estensione %s non è installata';
$lang['error_alreadyenabled']  = 'L\'estensione %s è già stata abilitata';
$lang['error_alreadydisabled'] = 'L\'estensione %s è già stata disabilitata';
$lang['error_minphp']          = 'L\'estensione %s richiede almeno PHP %s ma questo wiki esegue PHP %s';
$lang['error_maxphp']          = 'L\'estensione %s supporta solo PHP fino a %s, ma questo wiki esegue PHP %s';
$lang['noperms']               = 'La directory Extension non è scrivibile';
$lang['notplperms']            = 'Il modello di cartella non è scrivibile';
$lang['nopluginperms']         = 'La cartella plugin non è scrivibile';
$lang['git']                   = 'Questa extension è stata installata da git, potreste non volerla aggiornare qui.';
$lang['auth']                  = 'Questo plugin di autenticazione non è abilitato nella configurazione, considera di disabilitarlo.';
$lang['install_url']           = 'Installa da URL:';
$lang['install_upload']        = 'Caricamento Extension:';
$lang['repo_badresponse']      = 'Il repository dei plugin ha restituito una risposta non valida.';
$lang['repo_error']            = 'Il repository dei plugin non può essere raggiunto. Assicuratevi che il vostro server sia abilitato a contattare l\'indirizzo www.dokuwiki.org e controllate le impostazioni del vostro proxy.';
$lang['nossl']                 = 'La tua installazione PHP sembra mancare del supporto SSL. I download per molte estensioni di DokuWiki non funzioneranno.';
$lang['popularity_high']       = 'Questa è una delle estensioni più popolari';
$lang['popularity_medium']     = 'Questa estensione è piuttosto popolare';
$lang['popularity_low']        = 'Questa estensione ha suscitato un certo interesse';
$lang['details']               = 'Dettagli';
