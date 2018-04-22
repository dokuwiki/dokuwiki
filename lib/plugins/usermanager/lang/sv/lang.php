<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Tor Härnqvist <tor@harnqvist.se>
 * @author Per Foreby <per@foreby.se>
 * @author Nicklas Henriksson <nicklas[at]nihe.se>
 * @author Håkan Sandell <hakan.sandell@home.se>
 * @author Dennis Karlsson
 * @author Tormod Otter Johansson <tormod@latast.se>
 * @author Pontus Bergendahl <pontus.bergendahl@gmail.com>
 * @author Emil Lind <emil@sys.nu>
 * @author Bogge Bogge <bogge@bogge.com>
 * @author Peter Åström <eaustreum@gmail.com>
 */
$lang['menu']                  = 'Hantera användare';
$lang['noauth']                = '(användarautentisering ej tillgänlig)';
$lang['nosupport']             = '(användarhantering stödjs ej)';
$lang['badauth']               = 'ogiltig autentiseringsmekanism';
$lang['user_id']               = 'Användare';
$lang['user_pass']             = 'Lösenord';
$lang['user_name']             = 'Namn';
$lang['user_mail']             = 'E-post';
$lang['user_groups']           = 'Grupper';
$lang['field']                 = 'Fält';
$lang['value']                 = 'Värde';
$lang['add']                   = 'Lägg till';
$lang['delete']                = 'Radera';
$lang['delete_selected']       = 'Radera markerade';
$lang['edit']                  = 'Redigera';
$lang['edit_prompt']           = 'Redigera användaren';
$lang['modify']                = 'Spara ändringar';
$lang['search']                = 'Sök';
$lang['search_prompt']         = 'Utför sökning';
$lang['clear']                 = 'Återställ sökfilter';
$lang['filter']                = 'Filter';
$lang['export_all']            = 'Exportera alla användare (CSV)';
$lang['export_filtered']       = 'Exportera filtrerade användarlistningen (CSV)';
$lang['import']                = 'Importera nya användare';
$lang['error']                 = 'Error-meddelande';
$lang['summary']               = 'Visar användare %1$d-%2$d av %3$d funna. %4$d användare totalt.';
$lang['nonefound']             = 'Inga användare hittades. %d användare totalt.';
$lang['delete_ok']             = '%d användare raderade';
$lang['delete_fail']           = '%d kunde inte raderas.';
$lang['update_ok']             = 'Användaren uppdaterad';
$lang['update_fail']           = 'Användaruppdatering misslyckades';
$lang['update_exists']         = 'Kunde inte ändra användarnamn,, det angivna användarnamnet (%s) finns redan (andra ändringar kommer att utföras).';
$lang['start']                 = 'start';
$lang['prev']                  = 'föregående';
$lang['next']                  = 'nästa';
$lang['last']                  = 'sista';
$lang['edit_usermissing']      = 'Vald användare hittades inte. Den angivna användaren kan ha blivit raderad, eller ändrats någon annanstans.';
$lang['user_notify']           = 'Meddela användaren';
$lang['note_notify']           = 'E-postmeddelanden skickas bara om användaren har fått ett nytt lösenord.';
$lang['note_group']            = 'Nya användare läggs till i standardgruppen (%s) om inga grupper anges.';
$lang['note_pass']             = 'Lösenordet kommer att autogenereras om fältet är tomt och e-postmeddelanden till användaren är påslaget.';
$lang['add_ok']                = 'Användaren tillagd';
$lang['add_fail']              = 'Användare kunde inte läggas till';
$lang['notify_ok']             = 'E-postmeddelande skickat';
$lang['notify_fail']           = 'E-postmeddelande kunde inte skickas';
$lang['import_userlistcsv']    = 'Fillista över användare (CSV):';
$lang['import_success_count']  = 'Användar-import: %d användare funna, %d importerade framgångsrikt.';
$lang['import_failure_count']  = 'Användar-import: %d misslyckades. Misslyckandena listas nedan.';
$lang['import_error_baduserid'] = 'Användar-id saknas';
$lang['import_error_badname']  = 'Felaktigt namn';
$lang['import_error_badmail']  = 'Felaktig e-postadress';
$lang['import_error_upload']   = 'Import misslyckades. Csv-filen kunde inte laddas upp eller är tom.';
$lang['import_error_readfail'] = 'Import misslyckades. Den uppladdade filen gick inte att läsa.';
$lang['import_error_create']   = 'Misslyckades att skapa användaren.';
$lang['addUser_error_pass_not_identical'] = 'De angivna lösenorden var inte identiska.';
$lang['addUser_error_name_missing'] = 'Var god fyll i namn på den nya användaren.';
$lang['addUser_error_mail_missing'] = 'Var god fyll i e-postadress för den nya användaren.';
