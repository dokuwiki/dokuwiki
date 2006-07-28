<?php
/**
 * german language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Esther Brunner <esther@kaffeehaus.ch>
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

$lang['menu']         = 'Plugins verwalten';

// custom language strings for the plugin
$lang['refresh']      = "Liste der installierten Plugins aktualisieren";
$lang['refresh_x']    = "Benutze diese Option, wenn Du Plugins manuell verändert hast";
$lang['download']     = "Neues Plugin herunterladen und installieren";
$lang['manage']       = "Installierte Plugins";
$lang['btn_info']     = 'Info';
$lang['btn_update']   = 'Update';
$lang['btn_delete']   = 'Löschen';
$lang['btn_settings'] = 'Einstellungen';
$lang['btn_refresh']  = 'Aktualisieren';
$lang['btn_download'] = 'Herunterladen';
$lang['btn_enable']   = 'Speichern';
$lang['url']          = 'URL';

$lang['installed']    = 'Installiert:';
$lang['lastupdate']   = 'Letzte Version:';
$lang['source']       = 'Quelle:';
$lang['unknown']      = 'unbekannt';

// ..ing = header message
// ..ed = success message

$lang['refreshing']   = 'Aktualisiere ...';
$lang['refreshed']    = 'Aktualisieren der Plugins abgeschlossen.';

$lang['updating']     = 'Lade Update ...';
$lang['updated']      = 'Update von Plugin %s erfolgreich installiert';
$lang['updates']      = 'Die folgenden Plugins wurden erfolgreich aktualisiert';
$lang['update_none']  = 'Keine Updates gefunden.';

$lang['deleting']     = 'Löschen ...';
$lang['deleted']      = 'Plugin %s gelöscht.';

$lang['downloading']  = 'Lade herunter ...';
$lang['downloaded']   = 'Plugin %s erfolgreich installiert';
$lang['downloads']    = 'Die folgenden Plugins wurden erfolgreich installiert:';
$lang['download_none']   = 'Keine Plugins gefunden oder es trat ein Fehler beim Herunterladen auf.';

// info titles
$lang['plugin']           = 'Plugin:';
$lang['components']       = 'Komponenten';
$lang['noinfo']           = 'Dieses Plugin liefert keine Informationen, möglicherweise ist es fehlerhaft.';
$lang['name']             = 'Name:';
$lang['date']             = 'Datum:';
$lang['type']             = 'Typ:';
$lang['desc']             = 'Beschreibung:';
$lang['author']           = 'Entwickler:';
$lang['www']              = 'Web:';

// error messages
$lang['error']           = 'Ein unbekannter Fehler ist aufgetreten.';
$lang['error_download']  = 'Konnte das Plugin %s nicht installieren';
$lang['error_badurl']    = 'Wahrscheinlich ungültige URL, konnte keinen Dateinamen ausfindig machen';
$lang['error_dircreate'] = 'Konnte keinen temporären Ordner für die Downloads erstellen';

$lang['error_decompress'] = 'Der Plugin Manager konnte das Plugin archiv nicht entpacken. Entweder ist '.
                            'der Download fehlerhaft oder das Komprimierungsverfahren wird nicht unterstützt. '.
                            'Bitte versuchen Sie es erneut oder downloaden und installieren Sie das Plugin '.
                            'manuell';
$lang['error_copy']       = 'Beim kopieren der Dateien des Plugins trat ein Fehler auf <em>%s</em>: '.
                            'möglicherweise ist die Festplatte voll oder die Dateiberechtigungen falsch. '.
                            'Möglicherweise wurde das Plugin nur teilweise installiert, sie sollten das Plugin '.
                            'manuell entfernen um Instabilitäten zu vermeiden.';
$lang['error_delete']     = 'Es gab einem Fehler beim Versuch das Plugin zu löschen <em>%s</em>. '.
                            'Dies liegt warscheinlich an fehlenden Dateiberechtigungen.';

//Setup VIM: ex: et ts=4 enc=utf-8 :
