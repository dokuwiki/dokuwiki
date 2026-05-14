<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Fabian Pfannes <fpfannes@web.de>
 * @author e-dschungel <githib@e-dschungel.de>
 * @author liz <marliza@web.de>
 */
$lang['checkupdate']           = 'Regelmäßig auf Updates überprüfen.';
$lang['only_admins']           = 'Indexmenu Syntax f&uuml;r Nicht-Admins verbieten.<br>Beachten Sie, dass durch das Editieren einer Seite durch einen Nicht-Admin jedes enthaltende Indexmenu verloren geht.';
$lang['aclcache']              = 'Optimiert den Indexmenu Cache für ACL (nur für den Root Namespace).<br>Die Auswahl einer Methode beinflußt nur die Anzeige der Knoten im Menü, nicht aber die Zugriffsrechte.<ul><li>None: Standard. Die schnellste Methode. Es werden keine weiteren Cache Dateien erzeugt, aber Knoten mit mangelnden Zugriffsrechten können nicht autorisierten Benutzer gezeigt werden oder umgekehrt. Empfohlen, wenn Sie kein ACL verwenden oder es keine Rolle spielt wer die Menüstruktur sieht.<li>User: Für jeden User. Langsamere Methode. Es werden viele Cache Dateien erzeugt, aber gesperrte Seiten werden nicht angezeigt. Empfohlen wenn Sie ACL für einzelne Benutzer verwenden.<li>Groups: Für die Mitgliedschaft in einer Gruppe. Guter Kompromiss zwischen den beiden vorherigen Methoden, aber falls Sie die Seite vor einem User verstecken, der in einer Gruppe ist, die mit Schreibrechten für die Seite ausgestattet ist, kann er den Knoten im Menü dennoch sehen. Empfohlen, wenn die Seite mit ACL und Gruppenrichtlinien verwaltet wird.</ul>';
$lang['headpage']              = 'Startseiten Methode: die Seite von der der Titel und der Link für den Namespace genommen wird.<br>Kann einer dieser Werte sein:<ul><li>Die Wiki Startseite.<li>Eine Seite mit dem Namen des Namespaces die auch in diesem liegt.<li>Eine Seite mit dem Namen des Namespaces die auf der gleichen Ebene wie dieser liegt.<li>Ein ganz normale Seite.<li>Eine kommagetrennte Liste mit Seitennamen.</ul>';
$lang['hide_headpage']         = 'Startseiten verstecken.';
$lang['page_index']            = 'Die Seite die den DokuWiki Index ersetzen soll. Erstellen Sie diese und fügen Sie folgende Indexmenu Syntax ein. Nehmen Sie <code>id#random</code> falls Sie bereits eine Indexmenu Sidebar mit der Navigations-Option verwenden. Mein Vorschlag ist <code>{{indexmenu>..|js navbar nocookie id#random}}</code>.';
$lang['empty_msg']             = 'Nachricht die angezeigt wird, falls der Baum leer ist. Verwenden Sie Dokuwikisyntax, keinen HTML Code. Die <code>{{ns}}</code> Variable ist eine Abkürzung für den verwendeten Namespace.';
$lang['skip_index']            = 'Namespaces die nicht aufgenommen werden sollen. Sie müssen Regular Ausdrücke verweden. Beispiel: <code>/(sidebars|private:myns)/</code>';
$lang['skip_file']             = 'Dateien, die nicht aufgenommen werden sollen. Sie müssen auch reguläre Ausdrücke verwenden. Beispiel: <code>/(:start$|^public:newstart)/</code>';
$lang['show_sort']             = 'Zeigt den Admins die Indexmenu Sortierungsnummer als top of page note';
$lang['themes_url']            = 'JS Designvorlage von folgender http URL herunterladen.';
$lang['be_repo']               = 'Andere Personen von Ihrer Seite Designvorlagen herunterladen lassen.';
$lang['defaultoptions']        = 'Liste der Menüverzeichnis-Optionen durch Leerzeichen getrennt. Diese Optionen werden standardmäßig auf jedes Menüverzeichnis angewendet und können durch den reverse-Befehl in der Plugin-Syntax rückgängig gemacht werden.';
