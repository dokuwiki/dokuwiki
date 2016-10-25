<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Michael Klier <chi@chimeric.de>
 * @author Leo Moll <leo@yeasoft.com>
 * @author Florian Anderiasch <fa@art-core.org>
 * @author Robin Kluth <commi1993@gmail.com>
 * @author Arne Pelka <mail@arnepelka.de>
 * @author Dirk Einecke <dirk@dirkeinecke.de>
 * @author Blitzi94@gmx.de
 * @author Robert Bogenschneider <robog@GMX.de>
 * @author Niels Lange <niels@boldencursief.nl>
 * @author Christian Wichmann <nospam@zone0.de>
 * @author Paul Lachewsky <kaeptn.haddock@gmail.com>
 * @author Pierre Corell <info@joomla-praxis.de>
 * @author Matthias Schulte <dokuwiki@lupo49.de>
 * @author Sven <Svenluecke48@gmx.d>
 * @author christian studer <cstuder@existenz.ch>
 * @author Ben Fey <benedikt.fey@beck-heun.de>
 * @author Jonas Gröger <jonas.groeger@gmail.com>
 * @author Uwe Benzelrath <uwebenzelrath@gmail.com>
 * @author ms <msocial@posteo.de>
 * @author Carsten Perthel <carsten@cpesoft.com>
 */
$lang['menu']                  = 'Benutzerverwaltung';
$lang['noauth']                = '(Authentifizierungssystem nicht verfügbar)';
$lang['nosupport']             = '(Benutzerverwaltung nicht unterstützt)';
$lang['badauth']               = 'Ungültige Methode zur Authentifizierung';
$lang['user_id']               = 'Benutzername';
$lang['user_pass']             = 'Passwort';
$lang['user_name']             = 'Voller Name';
$lang['user_mail']             = 'E-Mail';
$lang['user_groups']           = 'Gruppen';
$lang['field']                 = 'Feld';
$lang['value']                 = 'Wert';
$lang['add']                   = 'Hinzufügen';
$lang['delete']                = 'Löschen';
$lang['delete_selected']       = 'Ausgewählte löschen';
$lang['edit']                  = 'Ändern';
$lang['edit_prompt']           = 'Benutzerdaten ändern';
$lang['modify']                = 'Speichern';
$lang['search']                = 'Suchen';
$lang['search_prompt']         = 'Benutzerdaten filtern';
$lang['clear']                 = 'Filter zurücksetzen';
$lang['filter']                = 'Filter';
$lang['export_all']            = 'Alle User exportieren (CSV)';
$lang['export_filtered']       = 'Exportiere gefilterte Userliste (CSV)';
$lang['import']                = 'Importiere neue User';
$lang['line']                  = 'Zeilennr.';
$lang['error']                 = 'Fehlermeldung';
$lang['summary']               = 'Zeige Benutzer %1$d-%2$d von %3$d gefundenen. %4$d Benutzer insgesamt.';
$lang['nonefound']             = 'Keine Benutzer gefunden. %d Benutzer insgesamt.';
$lang['delete_ok']             = '%d Benutzer gelöscht';
$lang['delete_fail']           = '%d konnten nicht gelöscht werden.';
$lang['update_ok']             = 'Benutzerdaten erfolgreich geändert.';
$lang['update_fail']           = 'Änderung der Benutzerdaten fehlgeschlagen.';
$lang['update_exists']         = 'Benutzername konnte nicht geändert werden, weil der angegebene Benutzer (%s) bereits existiert (alle anderen Änderungen wurden durchgeführt).';
$lang['start']                 = 'Anfang';
$lang['prev']                  = 'Vorherige';
$lang['next']                  = 'Nächste';
$lang['last']                  = 'Ende';
$lang['edit_usermissing']      = 'Der ausgewählte Benutzer wurde nicht gefunden. Möglicherweise wurde er gelöscht oder der Benutzer wurde anderswo geändert.';
$lang['user_notify']           = 'Nutzer benachrichtigen';
$lang['note_notify']           = 'Benachrichtigungs-E-Mails werden nur versandt, wenn ein neues Passwort vergeben wurde.';
$lang['note_group']            = 'Neue Benutzer werden der Standard-Gruppe (%s) hinzugefügt, wenn keine Gruppe angegeben wurde.';
$lang['note_pass']             = 'Das Passwort wird automatisch generiert, wenn das entsprechende Feld leergelassen wird und die Benachrichtigung des Benutzers aktiviert ist.';
$lang['add_ok']                = 'Nutzer erfolgreich angelegt';
$lang['add_fail']              = 'Nutzer konnte nicht angelegt werden';
$lang['notify_ok']             = 'Benachrichtigungsmail wurde versandt';
$lang['notify_fail']           = 'Benachrichtigungsmail konnte nicht versandt werden';
$lang['import_userlistcsv']    = 'Benutzerliste (CSV-Datei):';
$lang['import_header']         = 'Letzte Fehler bei Import';
$lang['import_success_count']  = 'User-Import: %d User gefunden, %d erfolgreich importiert.';
$lang['import_failure_count']  = 'User-Import: %d fehlgeschlagen. Fehlgeschlagene User sind nachfolgend aufgelistet.';
$lang['import_error_fields']   = 'Unzureichende Anzahl an Feldern: %d gefunden, benötigt sind 4.';
$lang['import_error_baduserid'] = 'User-Id fehlt';
$lang['import_error_badname']  = 'Ungültiger Name';
$lang['import_error_badmail']  = 'Ungültige E-Mail';
$lang['import_error_upload']   = 'Import fehlgeschlagen. Die CSV-Datei konnte nicht hochgeladen werden, oder ist leer.';
$lang['import_error_readfail'] = 'Import fehlgeschlagen. Die hochgeladene Datei konnte nicht gelesen werden.';
$lang['import_error_create']   = 'User konnte nicht angelegt werden';
$lang['import_notify_fail']    = 'Benachrichtigung konnte nicht an den importierten Benutzer %s (E-Mail: %s) gesendet werden.';
$lang['import_downloadfailures'] = 'Fehler als CSV-Datei zur Korrektur herunterladen';
$lang['addUser_error_missing_pass'] = 'Bitte vergeben Sie entweder ein Passwort oder Sie aktivieren die Benutzerbenachrichtigung, um die Passwortgenerierung zu ermöglichen.';
$lang['addUser_error_pass_not_identical'] = 'Die eingegebenen Passwörter stimmen nicht überein.';
$lang['addUser_error_modPass_disabled'] = 'Das Bearbeiten von Passwörtern ist momentan deaktiviert';
$lang['addUser_error_name_missing'] = 'Bitte geben Sie den Namen des neuen Benutzer ein.';
$lang['addUser_error_modName_disabled'] = 'Das Bearbeiten von Namen ist momentan deaktiviert.';
$lang['addUser_error_mail_missing'] = 'Bitte geben Sie die E-Mail-Adresse des neuen Benutzer ein.';
$lang['addUser_error_modMail_disabled'] = 'Das Bearbeiten von E-Mailadressen ist momentan deaktiviert.';
$lang['addUser_error_create_event_failed'] = 'Ein Plug-in hat das Hinzufügen des neuen Benutzers verhindert. Für weitere Informationen sehen Sie sich mögliche andere Meldungen an.';
