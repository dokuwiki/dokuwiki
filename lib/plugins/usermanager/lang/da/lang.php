<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Jacob Palm <mail@jacobpalm.dk>
 * @author Lars Næsbye Christensen <larsnaesbye@stud.ku.dk>
 * @author Kalle Sommer Nielsen <kalle@php.net>
 * @author Esben Laursen <hyber@hyber.dk>
 * @author Harith <haj@berlingske.dk>
 * @author Daniel Ejsing-Duun <dokuwiki@zilvador.dk>
 * @author Erik Bjørn Pedersen <erik.pedersen@shaw.ca>
 * @author rasmus <rasmus@kinnerup.com>
 * @author Mikael Lyngvig <mikael@lyngvig.org>
 * @author soer9648 <soer9648@eucl.dk>
 */
$lang['menu']                  = 'Brugerstyring';
$lang['noauth']                = '(Brugervalidering er ikke tilgængelig)';
$lang['nosupport']             = '(Brugerstyring er ikke understøttet)';
$lang['badauth']               = 'Ugyldig brugerbekræftelsesmetode';
$lang['user_id']               = 'Brugernavn';
$lang['user_pass']             = 'Adgangskode';
$lang['user_name']             = 'Navn';
$lang['user_mail']             = 'E-mail adresse';
$lang['user_groups']           = 'Grupper';
$lang['field']                 = 'Felt';
$lang['value']                 = 'Værdi';
$lang['add']                   = 'Tilføj';
$lang['delete']                = 'Slet';
$lang['delete_selected']       = 'Slet valgte';
$lang['edit']                  = 'Rediger';
$lang['edit_prompt']           = 'Rediger denne bruger';
$lang['modify']                = 'Gem ændringer';
$lang['search']                = 'Søg';
$lang['search_prompt']         = 'Udfør søgning';
$lang['clear']                 = 'Nulstil søgefilter';
$lang['filter']                = 'Filter';
$lang['export_all']            = 'Eksporter alle brugere (CSV)';
$lang['export_filtered']       = 'Eksporter filteret brugerliste (CSV)';
$lang['import']                = 'Importér nye brugere';
$lang['line']                  = 'Linje nr.';
$lang['error']                 = 'Fejlmeddelelse';
$lang['summary']               = 'Viser brugerne %1$d-%2$d ud af %3$d fundne. %4$d brugere totalt.';
$lang['nonefound']             = 'Ingen brugere fundet. %d brugere totalt.';
$lang['delete_ok']             = '%d brugere slettet';
$lang['delete_fail']           = '%d kunne ikke slettes.';
$lang['update_ok']             = 'Bruger opdateret korrekt';
$lang['update_fail']           = 'Opdatering af bruger mislykkedes';
$lang['update_exists']         = 'Ændring af brugernavn mislykkedes, det valgte brugernavn (%s) er allerede benyttet (øvrige ændringer vil blive udført).';
$lang['start']                 = 'begynde';
$lang['prev']                  = 'forrige';
$lang['next']                  = 'næste';
$lang['last']                  = 'sidste';
$lang['edit_usermissing']      = 'Den valgte bruger blev ikke fundet. Brugernavnet kan være slettet eller ændret andetsteds.';
$lang['user_notify']           = 'Notificer bruger';
$lang['note_notify']           = 'Notifikationsmails bliver kun sendt, hvis brugeren får tildelt en nyt adgangskode.';
$lang['note_group']            = 'Nye brugere vil blive tilføjet til standardgruppen (%s), hvis ingen gruppe er opgivet.';
$lang['note_pass']             = 'Adgangskoden vil blive dannet automatisk hvis feltet er tomt og underretning af brugeren er aktiveret.';
$lang['add_ok']                = 'Bruger tilføjet uden fejl.';
$lang['add_fail']              = 'Tilføjelse af bruger mislykkedes';
$lang['notify_ok']             = 'Notifikationsmail sendt';
$lang['notify_fail']           = 'Notifikationsmail kunne ikke sendes';
$lang['import_userlistcsv']    = 'Fil med brugerliste (CSV):';
$lang['import_header']         = 'Nyeste import - fejl';
$lang['import_success_count']  = 'Bruger import: %d brugere fundet, %d importeret med succes.';
$lang['import_failure_count']  = 'Bruger import: %d fejlet. Fejl er listet nedenfor.';
$lang['import_error_fields']   = 'Utilstrækkelige felter - fandt %d, påkrævet 4.';
$lang['import_error_baduserid'] = 'Bruger-id mangler';
$lang['import_error_badname']  = 'Ugyldigt navn';
$lang['import_error_badmail']  = 'Ugyldig email-adresse';
$lang['import_error_upload']   = 'Import fejlet. CSV-filen kunne ikke uploades, eller er tom.';
$lang['import_error_readfail'] = 'Import fejlet. Ikke muligt at læse uploadede fil.';
$lang['import_error_create']   = 'Ikke muligt at oprette brugeren';
$lang['import_notify_fail']    = 'Notifikationsmeddelelse kunne ikke sendes for importerede bruger %s, med e-mail adressen %s.';
$lang['import_downloadfailures'] = 'Download fejlliste som CSV, til rettelse';
$lang['addUser_error_missing_pass'] = 'Angiv venligst en adgangskode, eller aktiver brugernotifikation for at tillade dannelse af adgangskoder.';
$lang['addUser_error_pass_not_identical'] = 'De indtastede adgangskoder var ikke ens.';
$lang['addUser_error_modPass_disabled'] = 'Skift af adgangskode er i øjeblikket deaktiveret';
$lang['addUser_error_name_missing'] = 'Indtast venligst et navn til den nye bruger.';
$lang['addUser_error_modName_disabled'] = 'Ændring af navne er i øjeblikket deaktiveret.';
$lang['addUser_error_mail_missing'] = 'Indtast venligst en e-mail adresse til den nye bruger';
$lang['addUser_error_modMail_disabled'] = 'Ændring af e-mail adresser er i øjeblikket deaktiveret.';
$lang['addUser_error_create_event_failed'] = 'En udvidelse forhindrede den nye bruger i at blive tilføjet. For yderligere information, kontroller om der er øvrige fejlmeddelelser.';
