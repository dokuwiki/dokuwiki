<?php
/**
 * Swedish language file
 *
 * @author Per Foreby <per@foreby.se>
 */

// settings must be present and set appropriately for the language
$lang['encoding']   = 'utf-8';
$lang['direction']  = 'ltr';

// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu'] = 'Hantera återställningar';

// custom language strings for the plugin

$lang['filter']   = 'Sök efter spamsidor';
$lang['revert']   = 'Återställ markerade redigeringar';
$lang['reverted'] = '%s återställd till version %s';
$lang['removed']  = '%s borttagen';
$lang['revstart'] = 'Återställningen startad. Detta kan ta lång tid. Om
                     skriptet får en timeout innan det är färdigt måste du köra återställningen
                     med färre sidor åt gången.';
$lang['revstop']  = 'Återställningen avslutades utan problem.';
$lang['note1']    = 'OBS: sökningen skiljer på stora och små bokstäver';
$lang['note2']    = 'OBS: sidan kommer att återställas till den senaste versionen som inte innehåller den angivna söksträngen <i>%s</i>.';

//Setup VIM: ex: et ts=4 enc=utf-8 :
