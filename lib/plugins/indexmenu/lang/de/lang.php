<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Fabian Pfannes <fpfannes@web.de>
 * @author Dennis Plöger <develop@dieploegers.de>
 * @author Kilian Maier <kilian.maier1@web.de>
 * @author e-dschungel <githib@e-dschungel.de>
 * @author liz <marliza@web.de>
 */
$lang['menu']                  = 'Indexmenü Werkzeuge';
$lang['fetch']                 = 'Anzeigen';
$lang['install']               = 'Installieren';
$lang['delete']                = 'Löschen';
$lang['check']                 = 'Überprüfen';
$lang['no_repos']              = 'Keine URL für ein Template-Repository vorhanden.';
$lang['disabled']              = 'Deaktiviert';
$lang['conn_err']              = 'Verbindung fehlgeschlagen';
$lang['dir_err']               = 'Fehler bei der Erzeugung eines Verzeichnisses. Designvorlage konnte nicht gespeichert werden';
$lang['down_err']              = 'Fehler beim Herunterladen der Designvorlage';
$lang['zip_err']               = 'Fehler beim Erstellen oder Entpacken der Zip-Datei';
$lang['install_ok']            = 'Designvorlage wurde erfolgreich erstellt. Die neue Designvorlage ist in der Werkzeugleiste der Konfigurationsseite oder über die js#theme_name Option verfügbar.';
$lang['install_no']            = 'Verbindungsfehler. Sie können aber versuchen die Designvorlage <a href="http://samuele.netsons.org/dokuwiki/lib/plugins/indexmenu/upload/">hier</a> hochzuladen.';
$lang['delete_ok']             = 'Designvorlage wurde erfolgreich gelöscht.';
$lang['delete_no']             = 'Beim Löschen ist ein Fehler aufgetreten';
$lang['upload']                = 'Teilen';
$lang['checkupdates']          = 'Plugin updaten';
$lang['noupdates']             = 'Indexmenu muss nicht upgedated werden. Sie haben bereits die aktuellste Version:';
$lang['infos']                 = 'Sie können eine neue Designvorlage erstellen. Beachten Sie die Anleitung im <a href="https://www.dokuwiki.org/plugin:indexmenu#theme_tutorial">Designvorlagen Tutorial</a. <br />Wenn Sie den "Hochladen" Button unter der Designvorlage drücken können Sie viele Leute glücklich machen :-) dadurch, dass Sie sie im öffentlichen Indexmenu Repository bereitstellen.';
$lang['showsort']              = 'Indexmenu Sortierungsnummer: ';
$lang['donation_text']         = 'Das indexmenu-Plugin wurde vom niemandem unterstützt, aber ich entwickle und unterstütze es in meiner Freizeit. Wenn Sie sich bedanken oder die Entwicklung unterstützen wollen, denken Sie über eine Spende nach.';
$lang['js']['indexmenuwizard'] = 'Indexmenu-Wizard';
$lang['js']['index']           = 'Index';
$lang['js']['options']         = 'Optionen';
$lang['js']['navigation']      = 'Navigation';
$lang['js']['sort']            = 'Sortierung';
$lang['js']['filter']          = 'Filter';
$lang['js']['performance']     = 'Performance';
$lang['js']['namespace']       = 'Namensraum';
$lang['js']['nsdepth']         = 'Tiefe';
$lang['js']['js']              = 'Der Baum wird in Javascript gerendert, Sie können Ihr eigenes Theme festlegen';
$lang['js']['theme']           = 'Theme';
$lang['js']['navbar']          = 'Der Baum öffnet am aktuellen Namensraum';
$lang['js']['context']         = 'Den Baum auf Basis des aktuellen Namensraums anzeigen';
$lang['js']['nocookie']        = 'Den Geöffnet/Geschlossen-Status einzelner Knoten nicht speichern während der Navigation';
$lang['js']['noscroll']        = 'Das Scrollen des Baums ausschalten, wenn er nicht auf die Seite passt';
$lang['js']['notoc']           = 'Vorschau des Inhaltsverzeichnisses deaktivieren';
$lang['js']['tsort']           = 'Nach Titel';
$lang['js']['dsort']           = 'Nach Datum';
$lang['js']['msort']           = 'Nach Meta-Tag';
$lang['js']['nsort']           = 'Auch die Namensräume sortieren';
$lang['js']['hsort']           = 'Startseite oben sortieren';
$lang['js']['rsort']           = 'Sortierung der Seiten umdrehen';
$lang['js']['nons']            = 'Nur Seiten zeigen';
$lang['js']['nopg']            = 'Nur Namensräume zeigen';
$lang['js']['max']             = 'Wieviele Ebenen sollen mit AJAX geholt werden, wenn ein Knoten geöffnet wird? Außerdem: wieviele Unterebenen unterhalb dieser Ebene sollen mit AJAX geholt werden anstatt während des Seitenaufbaus?';
$lang['js']['maxjs']           = 'Wieviele Ebenen sollen im Browser statt auf dem Server gerendert werden, wenn ein Knoten geöffnet wird?';
$lang['js']['id']              = 'Benutzerspezifische Cookie-ID für dieses Indexmenu';
$lang['js']['insert']          = 'Indexmenu einfügen';
$lang['js']['metanum']         = 'Meta-Nummer zur Sortierung';
$lang['js']['insertmetanum']   = 'Metanummer einfügen';
$lang['js']['page']            = 'Seite';
$lang['js']['revs']            = 'Revision';
$lang['js']['tocpreview']      = 'Toc Vorschau';
$lang['js']['editmode']        = 'Bearbeitungsmodus';
$lang['js']['insertdwlink']    = 'Einfügen als DWlink';
$lang['js']['insertdwlinktooltip'] = 'Link dieser Seite in Eingabefeld an der entsprechenden Cursor-Position eingeben';
$lang['js']['ns']              = 'Namensraum';
$lang['js']['search']          = 'Suchen ...';
$lang['js']['searchtooltip']   = 'Nach Seiten innerhalb dieses Namensraumes suchen';
$lang['js']['create']          = 'Erstellen';
$lang['js']['more']            = 'Mehr';
$lang['js']['headpagetooltip'] = 'Eine neue headpage unter dieser Seite anlegen';
$lang['js']['startpage']       = 'Startseite';
$lang['js']['startpagetooltip'] = 'Erstelle eine neue Startseite unter dieser Seite';
$lang['js']['custompage']      = 'Benutzerdefinierte Seite';
$lang['js']['custompagetooltip'] = 'Erstelle eine neue Seite unter dieser Seite';
$lang['js']['acls']            = 'ACLs';
$lang['js']['purgecache']      = 'Cache löschen';
$lang['js']['exporthtml']      = 'Exportieren als HTML';
$lang['js']['exporttext']      = 'Exportieren als Text';
$lang['js']['headpagehere']    = 'Headpage hier';
$lang['js']['headpageheretooltip'] = 'Eine neue headpage innerhalb dieses Namensraums anlegen';
$lang['js']['newpage']         = 'Neue Seite';
$lang['js']['newpagetooltip']  = 'Eine neue Seite innerhalb dieses Namensraums erzeugen';
$lang['js']['newpagehere']     = 'Neue Seite hier';
$lang['js']['insertkeywords']  = 'Suchbegriff(e) für die Suche innerhalb dieses Namensraums eingeben';
$lang['js']['insertpagename']  = 'Seitenname zum Erstellen eingeben';
$lang['js']['edit']            = 'Bearbeiten';
$lang['js']['loading']         = 'Lädt ...';
