<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Chris Smith <chris@jalakai.co.uk>
 * @author Silvia Sargentoni <polinnia@tin.it>
 * @author Pietro Battiston <toobaz@email.it>
 * @author Lorenzo Breda <lbreda@gmail.com>
 * @author robocap <robocap1@gmail.com>
 * @author Jacopo Corbetta <jacopo.corbetta@gmail.com>
 * @author Matteo Pasotti <matteo@xquiet.eu>
 * @author Claudio Lanconelli <lancos@libero.it>
 * @author Francesco <francesco.cavalli@hotmail.com>
 * @author Fabio <fabioslurp@yahoo.it>
 * @author Torpedo <dgtorpedo@gmail.com>
 */
$lang['menu']                  = 'Gestione Utenti';
$lang['noauth']                = '(autenticazione non disponibile)';
$lang['nosupport']             = '(gestione utenti non supportata)';
$lang['badauth']               = 'sistema di autenticazione non valido';
$lang['user_id']               = 'ID utente';
$lang['user_pass']             = 'Password';
$lang['user_name']             = 'Nome completo';
$lang['user_mail']             = 'Email';
$lang['user_groups']           = 'Gruppi';
$lang['field']                 = 'Campo';
$lang['value']                 = 'Valore';
$lang['add']                   = 'Aggiungi';
$lang['delete']                = 'Elimina';
$lang['delete_selected']       = 'Elimina selezionati';
$lang['edit']                  = 'Modifica';
$lang['edit_prompt']           = 'Modifica questo utente';
$lang['modify']                = 'Salva modifiche';
$lang['search']                = 'Cerca';
$lang['search_prompt']         = 'Esegui ricerca';
$lang['clear']                 = 'Azzera filtro di ricerca';
$lang['filter']                = 'Filtro';
$lang['export_all']            = 'Esporta tutti gli utenti (CSV)';
$lang['export_filtered']       = 'Esporta elenco utenti filtrati (CSV)';
$lang['import']                = 'Importa nuovi utenti';
$lang['line']                  = 'Linea numero';
$lang['error']                 = 'Messaggio di errore';
$lang['summary']               = 'Visualizzazione utenti %1$d-%2$d di %3$d trovati. %4$d utenti totali.';
$lang['nonefound']             = 'Nessun utente trovato. %d utenti totali.';
$lang['delete_ok']             = '%d utenti eliminati';
$lang['delete_fail']           = 'Eliminazione %d fallita.';
$lang['update_ok']             = 'Aggiornamento utente riuscito';
$lang['update_fail']           = 'Aggiornamento utente fallito';
$lang['update_exists']         = 'Modifica nome utente fallita, il nome utente specificato (%s) esiste già (qualunque altra modifica sarà  applicata).';
$lang['start']                 = 'primo';
$lang['prev']                  = 'precedente';
$lang['next']                  = 'successivo';
$lang['last']                  = 'ultimo';
$lang['edit_usermissing']      = 'Utente selezionato non trovato, il nome utente specificato potrebbe essere stato eliminato o modificato altrove.';
$lang['user_notify']           = 'Notifica utente';
$lang['note_notify']           = 'Le email di notifica sono inviate soltanto se all\'utente è stata assegnata una nuova password.';
$lang['note_group']            = 'Se non si specifica alcun gruppo, i nuovi utenti saranno aggiunti al gruppo predefinito (%s).';
$lang['note_pass']             = 'La password verrà generata automaticamente qualora il campo di inserimento relativo venisse lasciato vuoto e le notifiche all\'utente fossero abilitate.';
$lang['add_ok']                = 'Utente aggiunto correttamente';
$lang['add_fail']              = 'Aggiunta utente fallita';
$lang['notify_ok']             = 'Email di notifica inviata';
$lang['notify_fail']           = 'L\'email di notifica non può essere inviata';
$lang['import_userlistcsv']    = 'File lista utente (CSV):';
$lang['import_header']         = 'Importazioni più recenti - Non riuscite';
$lang['import_success_count']  = 'Importazione utenti: %d utenti trovati, %d utenti importati con successo.';
$lang['import_failure_count']  = 'Importazione utenti: %d falliti. Errori riportati qui sotto.';
$lang['import_error_fields']   = 'Campi insufficienti, trovati %d, richiesti 4.';
$lang['import_error_baduserid'] = 'User-id non trovato';
$lang['import_error_badname']  = 'Nome errato';
$lang['import_error_badmail']  = 'Indirizzo email errato';
$lang['import_error_upload']   = 'Importazione fallita. Il file CSV non può essere caricato, o è vuoto.';
$lang['import_error_readfail'] = 'Importazione in errore. Impossibile leggere i file caricati.';
$lang['import_error_create']   = 'Impossibile creare l\'utente';
$lang['import_notify_fail']    = 'Non è stato possibile inviare un messaggio di notifica per l\'utente importato %s con e-mail %s.';
$lang['import_downloadfailures'] = 'Scarica operazioni non riuscite come CSV per correzione';
$lang['addUser_error_missing_pass'] = 'Imposta una password oppure attiva la notifica utente per abilitare la generazione password.';
$lang['addUser_error_pass_not_identical'] = 'Le password inserite non sono identiche.';
$lang['addUser_error_modPass_disabled'] = 'La modifica delle password è al momento disabilitata.';
$lang['addUser_error_name_missing'] = 'Inserire un nome per il nuovo utente.';
$lang['addUser_error_modName_disabled'] = 'La modifica dei nomi è al momento disabilitata.';
$lang['addUser_error_mail_missing'] = 'Inserire un indirizzo e-mail per il nuovo utente.';
$lang['addUser_error_modMail_disabled'] = 'La modifica degli indirizzi e-mail è al momento disabilitata.';
$lang['addUser_error_create_event_failed'] = 'Un plugin ha impedito che il nuovo utente venisse aggiunto. Rivedere gli altri messaggi per maggiori informazioni.';
