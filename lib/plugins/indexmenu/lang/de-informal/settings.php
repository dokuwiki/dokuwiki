<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Thomas Templin <templin@gnuwhv.de>
 * @author Dana <dannax3@gmx.de>
 */
$lang['checkupdate']           = 'Regelm&auml;&szlig;ig auf Updates &uuml;berpr&uuml;fen.';
$lang['only_admins']           = 'Indexmenu Syntax f&uuml;r Nicht-Admins verbieten.<br>Beachte, dass durch das Editieren einer Seite durch einen Nicht-Admin jedes enthaltende Indexmenu verloren geht.';
$lang['aclcache']              = 'Optimiert den Indexmenu Cache f&uuml;r ACL (nur f&uuml;r den Root Namespace).<br>Die Auswahl einer Methode beinflusst nur die Anzeige der Knoten im Menu, nicht aber die Zugriffsrechte.<ul><li>None: Standard. Die schnellste Methode. Es werden keine weiteren Cache Dateien erzeugt, aber Knoten mit mangelnden Zugriffsrechten k&ouml;nnen nicht authorisierten Benutzer gezeigt werden oder umgekehrt. Empfohlen, wenn Sie kein ACL verwenden oder es keine Rolle spielt wer die Menustruktur sieht.<li>User: F&uuml;r jeden User. Langsamere Methode. Es werden viele Cache Dateien erzeugt, aber gesperrte Seiten werden nicht angezeigt. Empfohlen wenn Du ACL f&uuml;r einzelne Benutzer verwendest.<li>Groups: F&uuml;r die Mitliedschaft in einer Gruppe. Guter Kompromiss zwischen den beiden vorhergenden Methoden, aber falls Du die Seite vor einem User versteckst, der in einer Gruppe ist, die mit Schreibrechten f&uuml;r die Seite ausgestatte ist, kann er den Knoten im Menu dennoch sehen. Empfohlen, wenn die Seite mit ACL und Gruppenrichtlinien verwaltet wird.</ul>';
$lang['headpage']              = 'Startseiten Methode: die Seite von der der Titel und der Link f&uuml;r den Namespace genommen wird.<br>Kann einer dieser Werte sein:<ul><li>Die Wiki Startseite.<li>Eine Seite mit dem Namen des Namespaces die auch in diesem liegt.<li>Eine Seite mit dem Namen des Namespaces die auf der gleichen Ebene wie dieser liegt.<li>Ein ganz normale Seite.<li>Eine kommagetrennte Liste mit Seitennamen.</ul>';
$lang['hide_headpage']         = 'Startseiten verstecken.';
$lang['page_index']            = 'Die Seite die den DokuWiki Index ersetzen soll. Erstelle diese Seite und f&uuml;ge folgende Indexmenu Syntax ein. Nimm <code>id#random</code> falls Du bereits eine Indexmenu Sidebar mit der Navigations-Option verwedest. Mein Vorschlag ist <code>{{indexmenu>..|js navbar nocookie id#random}}</code>.';
$lang['empty_msg']             = 'Nachricht die angezeigt wird, falls der Baum leer ist. Verwende Dokuwiki syntax, kein HTML Code. Die <code>{{ns}}</code> Variable ist eine Abk&uuml;rzung f&uuml;r den verwendeten Namespace.';
$lang['skip_index']            = 'Namespaces die nicht aufgenommen werden sollen. Du musst Regul&auml;re Ausdr&uuml;cke verweden. Beispiel: <code>/(sidebars|private:myns)/</code>';
$lang['skip_file']             = 'Dateien die nicht aufgenommen werden sollen. Du musst ebenfalls Regul&auml;re Ausdr&uuml;cke verweden. Beispiel: <code>/(:start$|^public:newstart)/</code>';
$lang['show_sort']             = 'Zeigt den Admins die Indexmenu Sortierungsnummer als top of page note';
$lang['themes_url']            = 'JS Designvorlage von folgender http URL herunterladen.';
$lang['be_repo']               = 'Andere Personen von Ihrer Seite Designvorlagen herunterladen lassen.';
$lang['defaultoptions']        = 'Eine durch Leerzeichen getrennte Liste von Indexmenu-Optionen.
Diese Optionen werden standardmäßig auf alle Indexmenus angewandt und können durch einen umgekehrten Befehl in der Plugin-Syntax rückgängig gemacht werden.';
