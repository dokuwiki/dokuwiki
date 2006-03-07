<?php
/**
 * Czech language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Tomas Valenta <t.valenta@sh.cvut.cz>
 */

$lang['menu'] = 'Správa modulů'; 

// custom language strings for the plugin
$lang['download'] = "Stáhnout a instalovat modul";
$lang['manage'] = "Seznam instalovaných modulů";

$lang['btn_info'] = 'info';
$lang['btn_update'] = 'aktualizovat';
$lang['btn_delete'] = 'smazat';
$lang['btn_settings'] = 'nastavení';
$lang['btn_download'] = 'Stáhnout';

$lang['url']              = 'URL';

$lang['installed']        = 'Instalován:';
$lang['lastupdate']       = 'Poslední aktualizace:';
$lang['source']           = 'Zdroj:';
$lang['unknown']          = 'neznámý';

// ..ing = header message
// ..ed = success message

$lang['updating']         = 'Aktualizuji ...';
$lang['updated']          = 'Modul %s úspěšně aktualizován';
$lang['updates']          = 'Následjící moduly byly úspěšně aktualizovány';
$lang['update_none']      = 'Žádné aktualizace nenalezeny.';

$lang['deleting']         = 'Probíhá mazání ...';
$lang['deleted']          = 'Modul %s smazán.';

$lang['downloading']      = 'Stahuji ...';
$lang['downloaded']       = 'Modul %s nainstalován';
$lang['downloads']        = 'Následující moduly byly úspěšně instalováný:';
$lang['download_none']    = 'Žádné moduly nenalezeny, nebo se vyskytla nečekaná chyba.';

// info titles
$lang['plugin']           = 'Modul:';
$lang['components']       = 'Součásti';
$lang['noinfo']           = 'Modul nevrátil informace, může být poškozen nebo špatný.';
$lang['name']             = 'Jméno:';
$lang['date']             = 'Datum:';
$lang['type']             = 'Typ:';
$lang['desc']             = 'Popis:';
$lang['author']           = 'Autor:';
$lang['www']              = 'Web:';
    
// error messages
$lang['error']            = 'Nastala neznámá chyba.';
$lang['error_download']   = 'Nelze stáhnout soubor s modulem: %s';
$lang['error_badurl']     = 'Zřejmě chybá URL - nelze určit název souboru z URL';
$lang['error_dircreate']  = 'Nelze vytvořit dočasný adresář ke stažení dat';
$lang['error_decompress'] = 'Správce modulů nemůže rozbalit stažený soubor. '.
                            'Toto může být způsobené chybou při stažení. Můžete se pokusit to stáhnout znova. '.
                            'Také může být chyba v kompresním formátu souboru. V tom případě bude nutné stáhnout '.
                            'a instalovat modul ručně.';
$lang['error_copy']       = 'Došlo k chybě při instalaci modulu <em>%s</em>. Mohlo dojít misto na disku, nebo '.
                            'mohou být špatná přístupová práva. Pozor že mohlo dojít k častečné a tudíž chybné '.
                            'instalaci modulu a tím může být ohrožena stabilita Wiki.. ';
$lang['error_delete']     = 'Došlo k chybě při pokusu o smazání modulu <em>%s</em>. '.
                            'Nejspíše je chyba v nastavení souborových nebo adresářových práv.';

//Setup VIM: ex: et ts=4 enc=utf-8 :
