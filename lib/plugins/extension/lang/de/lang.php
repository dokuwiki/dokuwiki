<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author analogroboter <ropely@gmx.net>
 * @author Anika Rachow <rachowanika@gmail.com>
 * @author Jürgen Mayer <gro.ikiwukod@x.wellen.org>
 * @author Axel Schwarzer <SchwarzerA@gmail.com>
 * @author Benjamin Molitor <bmolitor@uos.de>
 * @author H. Richard <wanderer379@t-online.de>
 * @author Joerg <scooter22@gmx.de>
 * @author Simon <st103267@stud.uni-stuttgart.de>
 * @author Hoisl <hoisl@gmx.at>
 * @author Dominik Mahr <drache.mahr@gmx.de>
 * @author Noel Tilliot <noeltilliot@byom.de>
 * @author Philip Knack <p.knack@stollfuss.de>
 * @author Hella Breitkopf <hella.breitkopf@gmail.com>
 */
$lang['menu']                  = 'Erweiterungen verwalten';
$lang['tab_plugins']           = 'Installierte Plugins';
$lang['tab_templates']         = 'Installierte Templates';
$lang['tab_search']            = 'Suchen und installieren';
$lang['tab_install']           = 'Manuelle Installation';
$lang['notimplemented']        = 'Dieses Feature wurde leider noch nicht eingebaut';
$lang['pluginlistsaveerror']   = 'Fehler beim Speichern der Plugin-Liste';
$lang['unknownauthor']         = 'Unbekannter Autor';
$lang['unknownversion']        = 'Unbekannte Version';
$lang['btn_info']              = 'Zeige mehr Infos';
$lang['btn_update']            = 'Update';
$lang['btn_uninstall']         = 'Deinstallation';
$lang['btn_enable']            = 'Aktivieren';
$lang['btn_disable']           = 'Deaktivieren';
$lang['btn_install']           = 'Installation';
$lang['btn_reinstall']         = 'Neuinstallation';
$lang['js']['reallydel']       = 'Möchtest du diese Erweiterung wirklich deinstallieren';
$lang['js']['display_viewoptions'] = 'Einstellungen anzeigen:';
$lang['js']['display_enabled'] = 'aktiviert';
$lang['js']['display_disabled'] = 'deaktiviert';
$lang['js']['display_updatable'] = 'Update verfügbar';
$lang['js']['close']           = 'Anklicken zum Schließen';
$lang['js']['filter']          = 'Zeige nur Erweiterungen, die aktualisiert werden können.';
$lang['search_for']            = 'Suche Erweiterung:';
$lang['search']                = 'Suche';
$lang['extensionby']           = '<strong>%s</strong> von %s';
$lang['screenshot']            = 'Screenshot von %s';
$lang['popularity']            = 'Popularität: %s%%';
$lang['homepage_link']         = 'Doku';
$lang['bugs_features']         = 'Bugs';
$lang['tags']                  = 'Schlagworte:';
$lang['author_hint']           = 'Suche Erweiterungen dieses Autors';
$lang['installed']             = 'Installiert:';
$lang['downloadurl']           = 'URL zum Herunterladen:';
$lang['repository']            = 'Quelle:';
$lang['unknown']               = '<em>unbekannt</em>';
$lang['installed_version']     = 'Installierte Version:';
$lang['install_date']          = 'Dein letztes Update:';
$lang['available_version']     = 'Verfügbare Version:';
$lang['compatible']            = 'Kompatibel mit:';
$lang['depends']               = 'Abhängig von:';
$lang['similar']               = 'Ähnlich wie:';
$lang['conflicts']             = 'Nicht kompatibel mit:';
$lang['donate']                = 'Nützlich?';
$lang['donate_action']         = 'Spendiere dem Autor einen Kaffee!';
$lang['repo_retry']            = 'Wiederholen';
$lang['provides']              = 'Enthält:';
$lang['status']                = 'Status';
$lang['status_installed']      = 'installiert';
$lang['status_not_installed']  = 'nicht installiert';
$lang['status_protected']      = 'geschützt';
$lang['status_enabled']        = 'aktiviert';
$lang['status_disabled']       = 'deaktiviert';
$lang['status_unmodifiable']   = 'unveränderlich';
$lang['status_plugin']         = 'Plugin';
$lang['status_template']       = 'Template';
$lang['status_bundled']        = 'gebündelt';
$lang['msg_enabled']           = 'Plugin %s ist aktiviert';
$lang['msg_disabled']          = 'Erweiterung %s ist deaktiviert';
$lang['msg_delete_success']    = 'Erweiterung %s wurde entfernt';
$lang['msg_delete_failed']     = 'Deinstallation der Erweiterung %s fehlgeschlagen';
$lang['msg_install_success']   = 'Erweiterung %s erfolgreich installiert.';
$lang['msg_update_success']    = 'Erweiterung %s erfolgreich aktualisiert.';
$lang['msg_upload_failed']     = 'Fehler beim Hochladen der Datei';
$lang['msg_nooverwrite']       = 'Die Erweiterung %s ist bereits vorhanden, sodass sie nicht überschrieben wird. Zum Überschreiben aktiviere die Option "Überschreiben".';
$lang['missing_dependency']    = 'Fehlende oder deaktivierte Abhängigkeit: %s';
$lang['found_conflict']        = 'Diese Erweiterung wurde als unverträglich mit Erweiterung: %s';
$lang['security_issue']        = 'Sicherheitsproblem: %s';
$lang['security_warning']      = 'Sicherheitswarnung: %s';
$lang['update_message']        = 'Update Meldung: %s';
$lang['wrong_folder']          = 'Erweiterung wurde nicht korrekt installiert: Benenne das Verzeichnis von "%s" nach "%s" um.';
$lang['url_change']            = 'URL geändert: Die Download-URL wurde seit dem letzten Download geändert. Internetadresse vor Aktualisierung der Erweiterung auf Gültigkeit prüfen.
Neu: %s
Alt: %s';
$lang['error_badurl']          = 'URLs sollten mit http oder https beginnen';
$lang['error_dircreate']       = 'Temporärer Ordner konnte nicht erstellt werden um Download zu abzuspeichern';
$lang['error_download']        = 'Download der Datei: %s nicht möglich.';
$lang['error_decompress']      = 'Die heruntergeladene Datei konnte nicht entpackt werden. Dies kann die Folge eines fehlerhaften Downloads sein. In diesem Fall solltest du versuchen den Vorgang zu wiederholen. Es kann auch die Folge eines unbekannten Kompressionsformates sein, in diesem Fall musst du die Datei selber herunterladen und manuell installieren.';
$lang['error_findfolder']      = 'Das Erweiterungs-Verzeichnis konnte nicht identifiziert werden, lade die Datei herunter und installiere sie manuell.';
$lang['error_copy']            = 'Beim Versuch Dateien in den Ordner <em>%s</em>: zu installieren trat ein Kopierfehler auf. Die Dateizugriffsberechtigungen könnten falsch sein. Dies kann an einem unvollständig installierten Plugin liegen und beeinträchtigt somit die Stabilität deiner Wiki-Installation.';
$lang['error_copy_read']       = 'Kann Verzeichnis %s nicht lesen.';
$lang['error_copy_mkdir']      = 'Kann Verzeichnis %s nicht erstellen.';
$lang['error_copy_copy']       = 'Kann %s nach %s nicht kopieren';
$lang['error_archive_read']    = 'Kann das Archiv %s nicht zum lesen öffnen.';
$lang['error_archive_extract'] = 'Kann das Archiv %s nicht entpacken.';
$lang['error_uninstall_protected'] = 'Die Erweiterung %s ist geschützt und kann nicht deinstalliert werden.';
$lang['error_uninstall_dependants'] = 'Die Erweiterung %s ist für %s erforderlich und kann nicht deinstalliert werden.';
$lang['error_disable_protected'] = 'Erweiterung %s ist geschützt und kann nicht deaktiviert werden.';
$lang['error_disable_dependants'] = 'Die Erweiterung %s ist für %s erforderlich und kann nicht deaktiviert werden werden.';
$lang['error_nourl']           = 'Für die erweiterung %s konnte keine URL/Internetadresse gefunden werden';
$lang['error_notinstalled']    = 'Die Erweiterung %s ist nicht installiert.';
$lang['noperms']               = 'Das Erweiterungs-Verzeichnis ist schreibgeschützt';
$lang['notplperms']            = 'Das Template-Verzeichnis ist schreibgeschützt';
$lang['nopluginperms']         = 'Das Plugin-Verzeichnis ist schreibgeschützt';
$lang['git']                   = 'Diese Erweiterung wurde über git installiert und sollte daher nicht hier aktualisiert werden.';
$lang['auth']                  = 'Dieses Auth-Plugin ist in der Konfiguration nicht aktiviert, Du solltest es deaktivieren.';
$lang['install_url']           = 'Von URL installieren:';
$lang['install_upload']        = 'Erweiterung hochladen:';
$lang['repo_error']            = 'Es konnte keine Verbindung zum Plugin-Verzeichnis hergestellt werden. Stelle sicher, dass der Server Verbindung mit www.dokuwiki.org aufnehmen darf und überprüfe deine Proxy-Einstellungen.';
$lang['nossl']                 = 'Deine PHP-Installation scheint SSL nicht zu unterstützen. Das Herunterladen vieler DokuWiki-Erweiterungen wird scheitern.';
