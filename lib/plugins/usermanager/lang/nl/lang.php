<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Wouter Schoot <wouter@schoot.org>
 * @author John de Graaff <john@de-graaff.net>
 * @author Niels Schoot <niels.schoot@quintiq.com>
 * @author Dion Nicolaas <dion@nicolaas.net>
 * @author Danny Rotsaert <danny.rotsaert@edpnet.be>
 * @author Marijn Hofstra hofstra.m@gmail.com
 * @author Matthias Carchon webmaster@c-mattic.be
 * @author Marijn Hofstra <hofstra.m@gmail.com>
 * @author Timon Van Overveldt <timonvo@gmail.com>
 * @author Jeroen
 * @author Ricardo Guijt <ricardoguijt@gmail.com>
 * @author Gerrit Uitslag <klapinklapin@gmail.com>
 * @author Rene <wllywlnt@yahoo.com>
 * @author Wesley de Weerd <wesleytiel@gmail.com>
 */
$lang['menu']                  = 'Gebruikersbeheer';
$lang['noauth']                = '(gebruikersauthenticatie niet beschikbaar)';
$lang['nosupport']             = '(gebruikersbeheer niet ondersteund)';
$lang['badauth']               = 'ongeldige authenticatiemethode';
$lang['user_id']               = 'Gebruiker';
$lang['user_pass']             = 'Wachtwoord';
$lang['user_name']             = 'Volledige naam';
$lang['user_mail']             = 'E-mail';
$lang['user_groups']           = 'Groepen';
$lang['field']                 = 'Veld';
$lang['value']                 = 'Waarde';
$lang['add']                   = 'Toevoegen';
$lang['delete']                = 'Verwijder';
$lang['delete_selected']       = 'Verwijder geselecteerden';
$lang['edit']                  = 'Wijzigen';
$lang['edit_prompt']           = 'Wijzig deze gebruiker';
$lang['modify']                = 'Wijzigingen opslaan';
$lang['search']                = 'Zoek';
$lang['search_prompt']         = 'Voer zoekopdracht uit';
$lang['clear']                 = 'Verwijder zoekfilter';
$lang['filter']                = 'Filter';
$lang['export_all']            = 'Exporteer Alle Gebruikers (CSV)';
$lang['export_filtered']       = 'Exporteer Gefilterde Gebruikers (CSV)';
$lang['import']                = 'Importeer Nieuwe Gebruikers';
$lang['line']                  = 'Regelnummer';
$lang['error']                 = 'Foutmelding';
$lang['summary']               = 'Weergegeven gebruikers %1$d-%2$d van %3$d gevonden. %4$d gebruikers in totaal.';
$lang['nonefound']             = 'Geen gebruikers gevonden. %d gebruikers in totaal.';
$lang['delete_ok']             = '%d gebruikers verwijderd';
$lang['delete_fail']           = '%d kon niet worden verwijderd.';
$lang['update_ok']             = 'Gebruiker succesvol gewijzigd';
$lang['update_fail']           = 'Gebruiker wijzigen mislukt';
$lang['update_exists']         = 'Gebruikersnaam veranderen mislukt, de opgegeven gebruikersnaam (%s) bestaat reeds (overige aanpassingen worden wel doorgevoerd).';
$lang['start']                 = 'start';
$lang['prev']                  = 'vorige';
$lang['next']                  = 'volgende';
$lang['last']                  = 'laatste';
$lang['edit_usermissing']      = 'Geselecteerde gebruiker niet gevonden, de opgegeven gebruikersnaam kan verwijderd zijn of elders aangepast.';
$lang['user_notify']           = 'Gebruiker notificeren';
$lang['note_notify']           = 'Notificatie-e-mails worden alleen verstuurd wanneer de gebruiker een nieuw wachtwoord wordt toegekend.';
$lang['note_group']            = 'Nieuwe gebruikers zullen aan de standaard groep (%s) worden toegevoegd als er geen groep opgegeven is.';
$lang['note_pass']             = 'Het wachtwoord wordt automatisch gegenereerd als het veld wordt leeggelaten en gebruikersnotificaties aanstaan.';
$lang['add_ok']                = 'Gebruiker succesvol toegevoegd';
$lang['add_fail']              = 'Gebruiker kon niet worden toegevoegd';
$lang['notify_ok']             = 'Notificatie-e-mail verzonden';
$lang['notify_fail']           = 'Notificatie-e-mail kon niet worden verzonden';
$lang['import_userlistcsv']    = 'Gebruikerslijst (CSV-bestand):';
$lang['import_header']         = 'Meest recente import - Gevonden fouten';
$lang['import_success_count']  = 'Gebruikers importeren: %d gebruikers gevonden, %d geïmporteerd';
$lang['import_failure_count']  = 'Gebruikers importeren: %d mislukt. Fouten zijn hieronder weergegeven.';
$lang['import_error_fields']   = 'Onvoldoende velden, gevonden %d, nodig 4.';
$lang['import_error_baduserid'] = 'Gebruikers-id mist';
$lang['import_error_badname']  = 'Verkeerde naam';
$lang['import_error_badmail']  = 'Verkeerd e-mailadres';
$lang['import_error_upload']   = 'Importeren mislukt. Het CSV bestand kon niet worden geüpload of is leeg.';
$lang['import_error_readfail'] = 'Importeren mislukt. Lezen van het geüploade bestand is mislukt.';
$lang['import_error_create']   = 'Aanmaken van de gebruiker was niet mogelijk.';
$lang['import_notify_fail']    = 'Notificatiebericht kon niet naar de geïmporteerde gebruiker worden verstuurd, %s met e-mail %s.';
$lang['import_downloadfailures'] = 'Download de gevonden fouten als CSV voor correctie';
$lang['addUser_error_missing_pass'] = 'Vul een wachtwoord in of activeer de gebruikers notificatie om een wachtwoord te genereren.';
$lang['addUser_error_pass_not_identical'] = 'De ingevulde wachtwoorden komen niet overeen';
$lang['addUser_error_modPass_disabled'] = 'Het aanpassen van wachtwoorden is momenteel uitgeschakeld';
$lang['addUser_error_name_missing'] = 'Vul een naam in voor de nieuwe gebruiker';
$lang['addUser_error_modName_disabled'] = 'Het aanpassen van namen is momenteel uitgeschakeld';
$lang['addUser_error_mail_missing'] = 'Vul een email adres in voor de nieuwe gebruiker';
$lang['addUser_error_modMail_disabled'] = 'Het aanpassen van uw email adres is momenteel uitgeschakeld';
$lang['addUser_error_create_event_failed'] = 'Een plugin heeft voorkomen dat de nieuwe gebruiker wordt toegevoegd . Bekijk mogelijke andere berichten voor meer informatie.';
