<?php
/**
 * german language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Esther Brunner <esther@kaffeehaus.ch>
 */

// settings must be present and set appropriately for the language
$lang['encoding']   = 'utf-8';
$lang['direction']  = 'ltr';

// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu']         = 'Plugins verwalten...'; 

// custom language strings for the plugin
$lang['refresh']      = "Liste der installierten Plugins aktualisieren";
$lang['refresh_x']    = "Benutze diese Option, wenn Du Plugins manuell verändert hast"; 
$lang['download']     = "Neues Plugin herunterladen und installieren";
$lang['manage']       = "Installiete Plugins";
$lang['btn_info']     = 'Info';
$lang['btn_update']   = 'Update';
$lang['btn_delete']   = 'Löschen';
$lang['btn_settings'] = 'Einstellungen';
$lang['btn_refresh']  = 'Aktualisieren';
$lang['btn_download'] = 'Herunterladen';
$lang['url']          = 'URL';
//$lang[''] = '';

$lang['installed']    = 'Installiert:';
$lang['lastupdate']   = 'Letzte Version:';
$lang['source']       = 'Quelle:';
$lang['unknown']      = 'unbekannt';

// ..ing = header message
// ..ed = success message

$lang['refreshing']   = 'Aktualisiere ...';
$lang['refreshed']    = 'Aktualisieren der Plugins abgeshlossen.';

$lang['updating']     = 'Lade Update ...';
$lang['updated']      = 'Update von Plugin %s erfolgreich installiert';

$lang['downloading']  = 'Lade herunter ...';
$lang['downloaded']   = 'Plugin %s erfolgreich installiert';
$lang['downloads']    = 'Die folgenden Plugins wurden erfolgreich installiert:';
$lang['download_none']   = 'Keine Plugins gefunden oder es trat ein Fehler beim Herunterladen auf.';
	
// error messages
$lang['error_download']  = 'Konnte das Plugin %s nicht installieren';
$lang['error_badurl']    = 'Wahrscheinlich ungültige URL, konnte keinen Dateinamen ausfindig machen';
$lang['error_dircreate'] = 'Konnte keinen temporären Ordner für die Downloads erstellen';
//$lang['error_'] = '';	
	

//Setup VIM: ex: et ts=2 enc=utf-8 :
