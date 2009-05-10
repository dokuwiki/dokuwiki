<?php
/**
 * valencian language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Bernat Arlandis i Mañó <berarma@ya.com>
 */

$lang['menu'] = 'Gestor de plúgins';

// custom language strings for the plugin
$lang['download'] = "Descarregar i instalar un nou plúgin";
$lang['manage'] = "Plúgins instalats";

$lang['btn_info'] = 'info';
$lang['btn_update'] = 'actualisar';
$lang['btn_delete'] = 'borrar';
$lang['btn_settings'] = 'ajusts';
$lang['btn_download'] = 'Descarregar';
$lang['btn_enable'] = 'Guardar';

$lang['url']              = 'URL';

$lang['installed']        = 'Instalat:';
$lang['lastupdate']       = 'Última actualisació:';
$lang['source']           = 'Font:';
$lang['unknown']          = 'desconegut';

// ..ing = header message
// ..ed = success message

$lang['updating']         = 'Actualisant ...';
$lang['updated']          = 'Plúgin %s actualisat correctament';
$lang['updates']          = 'Els següents plúgins s\'han actualisat correctament:';
$lang['update_none']      = 'No s\'han trobat actualisacions.';

$lang['deleting']         = 'Borrant ...';
$lang['deleted']          = 'Plúgin %s borrat.';

$lang['downloading']      = 'Descarregant ...';
$lang['downloaded']       = 'Plúgin %s instalat correctament';
$lang['downloads']        = 'Els següents plúgins s\'han instalat correctament:';
$lang['download_none']    = 'No s\'han trobat plúgins o ha hagut algun problema descarregant i instalant.';

// info titles
$lang['plugin']           = 'Plúgin:';
$lang['components']       = 'Components';
$lang['noinfo']           = 'Este plúgin no ha tornat informació, pot ser invàlit.';
$lang['name']             = 'Nom:';
$lang['date']             = 'Data:';
$lang['type']             = 'Tipo:';
$lang['desc']             = 'Descripció:';
$lang['author']           = 'Autor:';
$lang['www']              = 'Web:';

// error messages
$lang['error']            = 'Ha ocorregut un erro desconegut.';
$lang['error_download']   = 'No es pot descarregar l\'archiu del plúgin: %s';
$lang['error_badurl']     = 'Possible URL roïn - no es pot determinar el nom de l\'archiu de la URL';
$lang['error_dircreate']  = 'No es pot crear la carpeta temporal per a rebre descàrregues';
$lang['error_decompress'] = 'El gestor de plúgins no ha pogut descomprimir l\'archiu descarregat. '.
                            'Açò pot ser degut a una descàrrega fallida, en eixe cas deuria intentar-ho de nou; '.
                            'o el format de compressió pot ser desconegut, en eixe cas necessitarà '.
                            'descarregar i instalar el plúgin manualment.';
$lang['error_copy']       = 'Ha ocorregut un erro en la còpia de l\'archiu instalant archius del plugin '.
                            '<em>%s</em>: el disc podria estar ple o els permissos d\'accés a l\'archiu estan mal. '.
                            'Açò podria haver deixat el plúgin parcialment instalat i deixar el wiki inestable.';
$lang['error_delete']     = 'Ha ocorregut un erro intentant borrar el plúgin <em>%s</em>.  '.
                            'La causa més provable és que els permissos d\'accés a l\'archiu o el directori no siguen suficients';

//Setup VIM: ex: et ts=4 enc=utf-8 :
