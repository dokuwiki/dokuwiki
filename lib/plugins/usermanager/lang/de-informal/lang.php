<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Alexander Fischer <tbanus@os-forge.net>
 * @author Juergen Schwarzer <jschwarzer@freenet.de>
 * @author Marcel Metz <marcel_metz@gmx.de>
 * @author Matthias Schulte <dokuwiki@lupo49.de>
 * @author Christian Wichmann <nospam@zone0.de>
 * @author Pierre Corell <info@joomla-praxis.de>
 * @author Frank Loizzi <contact@software.bacal.de>
 * @author Volker Bödker <volker@boedker.de>
 * @author Dennis Plöger <develop@dieploegers.de>
 * @author F. Mueller-Donath <j.felix@mueller-donath.de>
 */
$lang['menu']                  = 'Benutzerverwaltung';
$lang['noauth']                = '(Benutzeranmeldung ist nicht verfügbar)';
$lang['nosupport']             = '(Benutzerverwaltung wird nicht unterstützt)';
$lang['badauth']               = 'Ungültige Authentifizierung';
$lang['user_id']               = 'Benutzer';
$lang['user_pass']             = 'Passwort';
$lang['user_name']             = 'Echter Name';
$lang['user_mail']             = 'E-Mail';
$lang['user_groups']           = 'Gruppen';
$lang['field']                 = 'Feld';
$lang['value']                 = 'Wert';
$lang['add']                   = 'Zufügen';
$lang['delete']                = 'Löschen';
$lang['delete_selected']       = 'Lösche Ausgewähltes';
$lang['edit']                  = 'Bearbeiten';
$lang['edit_prompt']           = 'Bearbeite diesen Benutzer';
$lang['modify']                = 'Änderungen speichern';
$lang['search']                = 'Suchen';
$lang['search_prompt']         = 'Suche ausführen';
$lang['clear']                 = 'Suchfilter zurücksetzen';
$lang['filter']                = 'Filter';
$lang['export_all']            = 'Alle Benutzer exportieren (CSV)';
$lang['export_filtered']       = 'Gefilterte Benutzerliste exportieren (CSV)';
$lang['import']                = 'Neue Benutzer importieren';
$lang['line']                  = 'Zeile Nr.';
$lang['error']                 = 'Fehlermeldung';
$lang['summary']               = 'Zeige Benutzer %1$d-%2$d von %3$d gefundenen. %4$d Benutzer insgesamt.';
$lang['nonefound']             = 'Keinen Benutzer gefunden. Insgesamt %d Benutzer.';
$lang['delete_ok']             = '%d Benutzer wurden gelöscht';
$lang['delete_fail']           = '%d konnte nicht gelöscht werden';
$lang['update_ok']             = 'Benutzer wurde erfolgreich aktualisiert';
$lang['update_fail']           = 'Aktualisierung des Benutzers ist fehlgeschlagen';
$lang['update_exists']         = 'Benutzername konnte nicht geändert werden, der angegebene Benutzername (%s) existiert bereits (alle anderen Änderungen werden angewandt).';
$lang['start']                 = 'Start';
$lang['prev']                  = 'vorige';
$lang['next']                  = 'nächste';
$lang['last']                  = 'letzte';
$lang['edit_usermissing']      = 'Der gewählte Benutzer wurde nicht gefunden. Der angegebene Benutzername könnte gelöscht oder an anderer Stelle geändert worden sein.';
$lang['user_notify']           = 'Benutzer benachrichtigen';
$lang['note_notify']           = 'Benachrichtigungsmails werden nur versandt, wenn der Benutzer ein neues Kennwort erhält.';
$lang['note_group']            = 'Neue Benutzer werden zur Standardgruppe (%s) hinzugefügt, wenn keine Gruppe angegeben wird.';
$lang['note_pass']             = 'Das Passwort wird automatisch erzeugt, wenn das Feld freigelassen wird und der Benutzer Benachrichtigungen aktiviert hat.';
$lang['add_ok']                = 'Benutzer erfolgreich hinzugefügt';
$lang['add_fail']              = 'Hinzufügen des Benutzers fehlgeschlagen';
$lang['notify_ok']             = 'Benachrichtigungsmail wurde versendet';
$lang['notify_fail']           = 'Benachrichtigungsemail konnte nicht gesendet werden';
$lang['import_userlistcsv']    = 'Benutzerliste (CSV-Datei):';
$lang['import_header']         = 'Letzte Fehler bei Import';
$lang['import_success_count']  = 'Benutzerimport: %d Benutzer gefunden, %d erfolgreich importiert.';
$lang['import_failure_count']  = 'Benutzerimport: %d Benutzerimporte fehlgeschalten. Alle Fehler werden unten angezeigt.';
$lang['import_error_fields']   = 'Falsche Anzahl Felder. Gefunden: %d. Benötigt: 4.';
$lang['import_error_baduserid'] = 'Benutzername fehlt';
$lang['import_error_badname']  = 'Ungültiger Name';
$lang['import_error_badmail']  = 'Ungültige E-Mailadresse';
$lang['import_error_upload']   = 'Import fehlgeschlagen. Die CSV-Datei konnte nicht hochgeladen werden oder ist leer.';
$lang['import_error_readfail'] = 'Import fehlgeschlagen. Konnte die hochgeladene Datei nicht lesen.';
$lang['import_error_create']   = 'Konnte den Benutzer nicht erzeugen';
$lang['import_notify_fail']    = 'Benachrichtigung konnte an Benutzer %s (%s) nicht geschickt werden.';
$lang['import_downloadfailures'] = 'Fehler als CSV-Datei zur Korrektur herunterladen';
$lang['addUser_error_missing_pass'] = 'Bitte setze entweder ein Passwort oder aktiviere die Benutzerbenachrichtigung, um die Passwortgenerierung zu ermöglichen.';
$lang['addUser_error_pass_not_identical'] = 'Die eingegebenen Passwörter stimmen nicht überein.';
$lang['addUser_error_modPass_disabled'] = 'Das Bearbeiten von Passwörtern ist momentan deaktiviert';
$lang['addUser_error_name_missing'] = 'Bitte gib den Namen des neuen Benutzer ein.';
$lang['addUser_error_modName_disabled'] = 'Das Bearbeiten von Namen ist momentan deaktiviert.';
$lang['addUser_error_mail_missing'] = 'Bitte gib die E-Mail-Adresse des neuen Benutzer ein.';
$lang['addUser_error_modMail_disabled'] = 'Das Bearbeiten von E-Mailadressen ist momentan deaktiviert.';
$lang['addUser_error_create_event_failed'] = 'Ein Plug-in hat das Hinzufügen des neuen Benutzers verhindert. Für weitere Informationen sieh dir mögliche andere Meldungen an.';
