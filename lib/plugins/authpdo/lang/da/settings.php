<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Jacob Palm <mail@jacobpalm.dk>
 */
$lang['debug']                 = 'Vis detaljerede fejlmeddelelser. Bør deaktiveres efter opsætning.';
$lang['dsn']                   = 'DSN der benyttes til at forbinde til databasen.';
$lang['user']                  = 'Brugerkonto til ovenstående databaseforbindelse (blank ved sqlite)';
$lang['pass']                  = 'Adgangskode til ovenstående databaseforbindelse (blank ved sqlite)';
$lang['select-user']           = 'SQL statement til at selektere data for en enkelt bruger';
$lang['select-user-groups']    = 'SQL statement til at selektere alle grupper en enkelt bruger er medlem af';
$lang['select-groups']         = 'SQL statement til at selektere alle tilgængelige grupper';
$lang['insert-user']           = 'SQL statement til at indsætte en ny bruger i databasen';
$lang['delete-user']           = 'SQL statement til at fjerne en bruger fra databasen';
$lang['list-users']            = 'SQL statement til at selektere brugere ud fra et filter';
$lang['count-users']           = 'SQL statement til at tælle brugere der matcher et filter';
$lang['update-user-info']      = 'SQL statement til at opdatere fulde navn og e-mail adresse på en enkelt bruger';
$lang['update-user-login']     = 'SQL statement til at opdatere loginnavn på en enkelt bruger';
$lang['update-user-pass']      = 'SQL statement til at opdatere adgangskode på en enkelt bruger';
$lang['insert-group']          = 'SQL statement til at indsætte en ny gruppe i databasen';
$lang['join-group']            = 'SQL statement til at tilføje en bruger til en eksisterende gruppe';
$lang['leave-group']           = 'SQL statement til at fjerne en bruger fra en gruppe';
$lang['check-pass']            = 'SQL statement til at kontrollere adgangskode for en bruger. Kan efterlades blank hvis adgangskode information hentes når brugeren selekteres.';
