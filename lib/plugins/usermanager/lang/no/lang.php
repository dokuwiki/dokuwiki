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
 */
$lang['menu']                  = 'Behandle brukere';
$lang['noauth']                = '(autentisering av brukere ikke tilgjengelig)';
$lang['nosupport']             = '(behandling av brukere støttes ikke)';
$lang['badauth']               = 'ugyldig autentiseringsmekanisme';
$lang['user_id']               = 'Bruker';
$lang['user_pass']             = 'Passord';
$lang['user_name']             = 'Fullt navn';
$lang['user_mail']             = 'E-post';
$lang['user_groups']           = 'Grupper';
$lang['field']                 = 'Felt';
$lang['value']                 = 'Verdi';
$lang['add']                   = 'Legg til';
$lang['delete']                = 'Slett';
$lang['delete_selected']       = 'Slett valgte';
$lang['edit']                  = 'Rediger';
$lang['edit_prompt']           = 'Rediger denne brukeren';
$lang['modify']                = 'Lagre endringer';
$lang['search']                = 'Søk';
$lang['search_prompt']         = 'Start søk';
$lang['clear']                 = 'Tilbakestill søkefilter';
$lang['filter']                = 'Filter';
$lang['export_all']            = 'Eksporter alle brukere (CSV)';
$lang['export_filtered']       = 'Eksporter den filtrerte listen (CSV)';
$lang['import']                = 'Importer nye brukere';
$lang['line']                  = 'Linje nr.';
$lang['error']                 = 'Feilmelding';
$lang['summary']               = 'Viser brukere %1$d-%2$d av %3$d. %4$d users total.';
$lang['nonefound']             = 'Ingen brukere funnet. %d brukere totalt.';
$lang['delete_ok']             = '%d brukere slettet.';
$lang['delete_fail']           = '%d kunne ikke slettes.';
$lang['update_ok']             = 'Brukeren ble oppdatert';
$lang['update_fail']           = 'Oppdatering av brukeren feilet';
$lang['update_exists']         = 'Endring av brukernavn feilet. Det oppgitte brukernavnet (%s) eksisterer allerede (alle andre endringer vil bli gjort).';
$lang['start']                 = 'første';
$lang['prev']                  = 'forrige';
$lang['next']                  = 'neste';
$lang['last']                  = 'siste';
$lang['edit_usermissing']      = 'Fant ikke valgte brukere. Det oppgitte brukernavnet kan ha blitt slettet eller endret et annet sted.';
$lang['user_notify']           = 'Varsle bruker';
$lang['note_notify']           = 'E-post med varsling blir bare sendt hvis brukeren blir gitt nytt passord.';
$lang['note_group']            = 'Nye brukere vil bli lagt til standardgruppen (%s) hvis ingen gruppe oppgis.';
$lang['note_pass']             = 'Et nytt passordet vil bli laget dersom passordfeltet er tomt og«Varsle bruker» er huket av.';
$lang['add_ok']                = 'Brukeren ble lagt til';
$lang['add_fail']              = 'Brukeren kunne ikke legges til';
$lang['notify_ok']             = 'Varsling sendt';
$lang['notify_fail']           = 'Varsling kunne ikke sendes';
$lang['import_userlistcsv']    = 'Brukerliste (CSV):';
$lang['import_header']         = 'Siste brukerimport - Liste med feil';
$lang['import_success_count']  = 'Brukerimport: %d brukere ble funnet, %d av disse ble importert.';
$lang['import_failure_count']  = 'Brukerimport: %d brukere ble ikke importert. Feilene blir listet under her.';
$lang['import_error_fields']   = 'For få felt, fant %d, men trenger 4.';
$lang['import_error_baduserid'] = 'Mangler brukernavn';
$lang['import_error_badname']  = 'Noe feil med navn';
$lang['import_error_badmail']  = 'Noe feil med e-postadressen';
$lang['import_error_upload']   = 'Feil med import. Klarte ikke laste opp CSV-filen, eller så er denne tom.';
$lang['import_error_readfail'] = 'Feil med import. Klarte ikke lese filen som er lastet opp.';
$lang['import_error_create']   = 'Klarte ikke opprette brukeren';
$lang['import_notify_fail']    = 'Melding til bruker kunne ikke bli sent for importerte bruker, %s med e-postadresse %s.';
$lang['import_downloadfailures'] = 'Last ned feilende verdier som CSV for retting';
$lang['addUser_error_missing_pass'] = 'Du må enten skrive inn et passord, eller slå på «Varsle bruker» slik at systemet selv lager et nytt passord';
$lang['addUser_error_pass_not_identical'] = 'Passordene er ikk identisk';
$lang['addUser_error_modPass_disabled'] = 'Endre passord er slått av.';
$lang['addUser_error_name_missing'] = 'Skriv inn navnet til den nye brukeren';
$lang['addUser_error_modName_disabled'] = 'Endre navn er slått av.';
$lang['addUser_error_mail_missing'] = 'Skriv inn e-postadressen til den nye brukeren.';
$lang['addUser_error_modMail_disabled'] = 'Endre e-postadresse er slått av.';
$lang['addUser_error_create_event_failed'] = 'En utvidelse hindrer at den nye brukeren kan legges inn. Sjekk ev. andre tilbakemeldinger for mer informasjon.';
