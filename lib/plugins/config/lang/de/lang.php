<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Michael Klier <chi@chimeric.de>
 * @author Leo Moll <leo@yeasoft.com>
 * @author Florian Anderiasch <fa@art-core.org>
 * @author Robin Kluth <commi1993@gmail.com>
 * @author Arne Pelka <mail@arnepelka.de>
 * @author Dirk Einecke <dirk@dirkeinecke.de>
 * @author Blitzi94@gmx.de
 * @author Robert Bogenschneider <robog@gmx.de>
 * @author Niels Lange <niels@boldencursief.nl>
 * @author Christian Wichmann <nospam@zone0.de>
 * @author Paul Lachewsky <kaeptn.haddock@gmail.com>
 * @author Pierre Corell <info@joomla-praxis.de>
 * @author Matthias Schulte <dokuwiki@lupo49.de>
 * @author Mateng Schimmerlos <mateng@firemail.de>
 * @author Anika Henke <anika@selfthinker.org>
 * @author Marco Hofmann <xenadmin@meinekleinefarm.net>
 * @author Hella Breitkopf <hella.breitkopf@gmail.com>
 */
$lang['menu']                  = 'Konfiguration';
$lang['error']                 = 'Die Einstellungen wurden wegen einer fehlerhaften Eingabe nicht gespeichert.<br /> Bitte überprüfen sie die rot umrandeten Eingaben und speichern Sie erneut.';
$lang['updated']               = 'Einstellungen erfolgreich gespeichert.';
$lang['nochoice']              = '(keine Auswahlmöglichkeiten vorhanden)';
$lang['locked']                = 'Die Konfigurationsdatei kann nicht geändert werden. Wenn dies unbeabsichtigt ist, <br />überprüfen Sie, ob die Dateiberechtigungen korrekt gesetzt sind.';
$lang['danger']                = 'Vorsicht: Die Änderung dieser Option könnte Ihr Wiki und das Konfigurationsmenü unzugänglich machen.';
$lang['warning']               = 'Hinweis: Die Änderung dieser Option könnte unbeabsichtigtes Verhalten hervorrufen.';
$lang['security']              = 'Sicherheitswarnung: Die Änderung dieser Option könnte ein Sicherheitsrisiko darstellen.';
$lang['_configuration_manager'] = 'Konfigurations-Manager';
$lang['_header_dokuwiki']      = 'DokuWiki';
$lang['_header_plugin']        = 'Plugin';
$lang['_header_template']      = 'Template';
$lang['_header_undefined']     = 'Nicht gesetzte Einstellungen';
$lang['_basic']                = 'Basis';
$lang['_display']              = 'Darstellung';
$lang['_authentication']       = 'Authentifizierung';
$lang['_anti_spam']            = 'Anti-Spam';
$lang['_editing']              = 'Bearbeitung';
$lang['_links']                = 'Links';
$lang['_media']                = 'Medien';
$lang['_notifications']        = 'Benachrichtigung';
$lang['_syndication']          = 'Syndication (RSS)';
$lang['_advanced']             = 'Erweitert';
$lang['_network']              = 'Netzwerk';
$lang['_msg_setting_undefined'] = 'Keine Konfigurationsmetadaten.';
$lang['_msg_setting_no_class'] = 'Keine Konfigurationsklasse.';
$lang['_msg_setting_no_default'] = 'Kein Standardwert.';
$lang['title']                 = 'Titel des Wikis';
$lang['start']                 = 'Startseitenname';
$lang['lang']                  = 'Sprache';
$lang['template']              = 'Designvorlage (Template)';
$lang['tagline']               = 'Tag-Linie (nur, wenn vom Template unterstützt)';
$lang['sidebar']               = 'Name der Sidebar-Seite (nur, wenn vom Template unterstützt), ein leeres Feld deaktiviert die Sidebar';
$lang['license']               = 'Unter welcher Lizenz sollen Ihre Inhalte veröffentlicht werden?';
$lang['savedir']               = 'Speicherverzeichnis';
$lang['basedir']               = 'Installationsverzeichnis';
$lang['baseurl']               = 'Installationspfad (URL)';
$lang['cookiedir']             = 'Cookiepfad. Frei lassen, um den gleichen Pfad wie "baseurl" zu benutzen.';
$lang['dmode']                 = 'Berechtigungen für neue Verzeichnisse';
$lang['fmode']                 = 'Berechtigungen für neue Dateien';
$lang['allowdebug']            = 'Debug-Ausgaben erlauben <b>Abschalten wenn nicht benötigt!</b>';
$lang['recent']                = 'Anzahl der Einträge in der Änderungsliste';
$lang['recent_days']           = 'Wie viele letzte Änderungen sollen einsehbar bleiben? (Tage)';
$lang['breadcrumbs']           = 'Anzahl der Einträge im "Krümelpfad"';
$lang['youarehere']            = 'Hierarchische Pfadnavigation verwenden';
$lang['fullpath']              = 'Den kompletten Dateipfad im Footer anzeigen';
$lang['typography']            = 'Typographische Ersetzungen';
$lang['dformat']               = 'Datumsformat (Siehe PHP <a href="http://php.net/strftime">strftime</a> Funktion)';
$lang['signature']             = 'Signatur';
$lang['showuseras']            = 'Welche Informationen über einen Benutzer anzeigen, der zuletzt eine Seite bearbeitet hat';
$lang['toptoclevel']           = 'Inhaltsverzeichnis bei dieser Überschriftengröße beginnen';
$lang['tocminheads']           = 'Mindestanzahl der Überschriften die entscheidet, ob ein Inhaltsverzeichnis erscheinen soll';
$lang['maxtoclevel']           = 'Maximale Überschriftengröße für Inhaltsverzeichnis';
$lang['maxseclevel']           = 'Abschnitte bis zu dieser Stufe einzeln editierbar machen';
$lang['camelcase']             = 'CamelCase-Verlinkungen verwenden';
$lang['deaccent']              = 'Seitennamen bereinigen';
$lang['useheading']            = 'Erste Überschrift als Seitennamen verwenden';
$lang['sneaky_index']          = 'Standardmäßig zeigt DokuWiki alle Namensräume in der Übersicht. Wenn diese Option aktiviert wird, werden alle Namensräume, für die der Benutzer keine Lese-Rechte hat, nicht angezeigt. Dies kann unter Umständen dazu führen, das lesbare Unter-Namensräume nicht angezeigt werden und macht die Übersicht evtl. unbrauchbar in Kombination mit bestimmten ACL Einstellungen.';
$lang['hidepages']             = 'Seiten verstecken (Regulärer Ausdruck)';
$lang['useacl']                = 'Zugangskontrolle verwenden';
$lang['autopasswd']            = 'Passwort automatisch generieren';
$lang['authtype']              = 'Authentifizierungsmechanismus';
$lang['passcrypt']             = 'Verschlüsselungsmechanismus';
$lang['defaultgroup']          = 'Standardgruppe';
$lang['superuser']             = 'Administrator - Eine Gruppe oder Benutzer mit vollem Zugriff auf alle Seiten und Administrationswerkzeuge.';
$lang['manager']               = 'Manager - Eine Gruppe oder Benutzer mit Zugriff auf einige Administrationswerkzeuge.';
$lang['profileconfirm']        = 'Profiländerung nur nach Passwortbestätigung';
$lang['rememberme']            = 'Permanente Login-Cookies erlauben (Auf diesem Computer eingeloggt bleiben)';
$lang['disableactions']        = 'DokuWiki-Aktionen deaktivieren';
$lang['disableactions_check']  = 'Check';
$lang['disableactions_subscription'] = 'Seiten-Abonnements';
$lang['disableactions_wikicode'] = 'Quelltext betrachten/exportieren';
$lang['disableactions_profile_delete'] = 'Eigenes Benutzerprofil löschen';
$lang['disableactions_other']  = 'Andere Aktionen (durch Komma getrennt)';
$lang['disableactions_rss']    = 'XML-Syndikation (RSS)';
$lang['auth_security_timeout'] = 'Authentifikations-Timeout (Sekunden)';
$lang['securecookie']          = 'Sollen Cookies, die via HTTPS gesetzt wurden nur per HTTPS versendet werden? Deaktivieren Sie diese Option, wenn nur der Login Ihres Wikis mit SSL gesichert ist, aber das Betrachten des Wikis ungesichert geschieht.';
$lang['remote']                = 'Aktiviert den externen API-Zugang. Diese Option erlaubt es externen Anwendungen von außen auf die XML-RPC-Schnittstelle oder anderweitigen Schnittstellen zu zugreifen.';
$lang['remoteuser']            = 'Zugriff auf die externen Schnittstellen durch kommaseparierte Angabe von Benutzern oder Gruppen einschränken. Ein leeres Feld erlaubt Zugriff für jeden.';
$lang['usewordblock']          = 'Spam-Blocking (nach Wörterliste) benutzen';
$lang['relnofollow']           = 'rel="nofollow" verwenden';
$lang['indexdelay']            = 'Zeit bevor Suchmaschinenindexierung erlaubt ist (in Sekunden)';
$lang['mailguard']             = 'E-Mail-Adressen schützen';
$lang['iexssprotect']          = 'Hochgeladene Dateien auf bösartigen JavaScript- und HTML-Code untersuchen';
$lang['usedraft']              = 'Während des Bearbeitens automatisch Zwischenentwürfe speichern';
$lang['htmlok']                = 'HTML erlauben';
$lang['phpok']                 = 'PHP erlauben';
$lang['locktime']              = 'Maximales Alter für Seitensperren (Sekunden)';
$lang['cachetime']             = 'Maximale Cachespeicherung (Sekunden)';
$lang['target____wiki']        = 'Zielfenster für interne Links (target Attribut)';
$lang['target____interwiki']   = 'Zielfenster für InterWiki-Links (target Attribut)';
$lang['target____extern']      = 'Zielfenster für Externe Links (target Attribut)';
$lang['target____media']       = 'Zielfenster für (Bild-)Dateien (target Attribut)';
$lang['target____windows']     = 'Zielfenster für Windows Freigaben (target Attribut)';
$lang['mediarevisions']        = 'Media-Revisionen (ältere Versionen) aktivieren?';
$lang['refcheck']              = 'Auf Verwendung beim Löschen von Media-Dateien testen';
$lang['gdlib']                 = 'GD Lib Version';
$lang['im_convert']            = 'Pfad zum ImageMagicks-Konvertierwerkzeug';
$lang['jpg_quality']           = 'JPEG Kompressionsqualität (0-100)';
$lang['fetchsize']             = 'Maximale Größe (in Bytes), die fetch.php von extern herunterladen darf';
$lang['subscribers']           = 'E-Mail-Abos zulassen';
$lang['subscribe_time']        = 'Zeit nach der Zusammenfassungs- und Änderungslisten-E-Mails verschickt werden (Sekunden); Dieser Wert sollte kleiner als die in recent_days konfigurierte Zeit sein.';
$lang['notify']                = 'Änderungsmitteilungen an diese E-Mail-Adresse versenden';
$lang['registernotify']        = 'Information über neu registrierte Benutzer an diese E-Mail-Adresse senden';
$lang['mailfrom']              = 'Absender-E-Mail-Adresse für automatische Mails';
$lang['mailprefix']            = 'Präfix für E-Mail-Betreff beim automatischen Versand von Benachrichtigungen (Leer lassen um den Wiki-Titel zu verwenden)';
$lang['htmlmail']              = 'Versendet optisch angenehmere, aber größere E-Mails im HTML-Format (multipart). Deaktivieren, um Text-Mails zu versenden.';
$lang['sitemap']               = 'Google Sitemap erzeugen (Tage). Mit 0 deaktivieren.';
$lang['rss_type']              = 'XML-Feed-Format';
$lang['rss_linkto']            = 'XML-Feed verlinken auf';
$lang['rss_content']           = 'Welche Inhalte sollen im XML-Feed dargestellt werden?';
$lang['rss_update']            = 'XML-Feed Aktualisierungsintervall (Sekunden)';
$lang['rss_show_summary']      = 'Bearbeitungs-Zusammenfassung im XML-Feed anzeigen';
$lang['rss_media']             = 'Welche Änderungen sollen im XML-Feed angezeigt werden?';
$lang['updatecheck']           = 'Automatisch auf Updates und Sicherheitswarnungen prüfen? DokuWiki muss sich dafür mit update.dokuwiki.org verbinden.';
$lang['userewrite']            = 'Schöne Seitenadressen (URL rewriting)';
$lang['useslash']              = 'Schrägstrich (/) als Namensraumtrenner in URLs verwenden';
$lang['sepchar']               = 'Worttrenner für Seitennamen in URLs';
$lang['canonical']             = 'Immer Links mit vollständigen URLs erzeugen';
$lang['fnencode']              = 'Methode um nicht-ASCII Dateinamen zu kodieren.';
$lang['autoplural']            = 'Bei Links automatisch nach vorhandenen Pluralformen suchen';
$lang['compression']           = 'Komprimierungsmethode für alte Seitenrevisionen';
$lang['gzip_output']           = 'Seiten mit gzip komprimiert ausliefern';
$lang['compress']              = 'JavaScript und Stylesheets komprimieren';
$lang['cssdatauri']            = 'Größe in Bytes, bis zu der Bilder in CSS-Dateien referenziert werden können, um HTTP-Anfragen zu minimieren. Empfohlene Einstellung: <code>400</code> to <code>600</code> Bytes. Setzen Sie die Einstellung auf <code>0</code> um die Funktion zu deaktivieren.';
$lang['send404']               = 'Bei nicht vorhandenen Seiten mit 404 Fehlercode antworten';
$lang['broken_iua']            = 'Falls die Funktion ignore_user_abort auf Ihrem System nicht funktioniert, könnte der Such-Index nicht funktionieren. IIS+PHP/CGI ist bekannt dafür. Siehe auch <a href="http://bugs.splitbrain.org/?do=details&amp;task_id=852">Bug 852</a>.';
$lang['xsendfile']             = 'Den X-Sendfile-Header nutzen, um Dateien direkt vom Webserver ausliefern zu lassen? Ihr Webserver muss dies unterstützen!';
$lang['renderer_xhtml']        = 'Standard-Renderer für die normale (XHTML) Wiki-Ausgabe.';
$lang['renderer__core']        = '%s (DokuWiki Kern)';
$lang['renderer__plugin']      = '%s (Plugin)';
$lang['dnslookups']            = 'DokuWiki löst die IP-Adressen von Benutzern zu deren Hostnamen auf. Wenn Sie einen langsamen oder unzuverlässigen DNS-Server verwenden oder die Funktion nicht benötigen, dann sollte diese Option deaktiviert sein.';
$lang['jquerycdn']             = 'Sollen jQuery und jQuery UI Skriptdateien von einem CDN (Contend Delivery Network) geladen werden? Dadurch entstehen zusätzliche HTTP-Anfragen, aber die Daten werden voraussichtlich schneller geladen und eventuell sind sie auch schon beim Benutzer im Cache.';
$lang['jquerycdn_o_0']         = 'Kein CDN, ausschließlich lokale Auslieferung';
$lang['jquerycdn_o_jquery']    = 'CDN von code.jquery.com';
$lang['jquerycdn_o_cdnjs']     = 'CDN von cdnjs.com';
$lang['proxy____host']         = 'Proxy-Server';
$lang['proxy____port']         = 'Proxy-Port';
$lang['proxy____user']         = 'Proxy Benutzername';
$lang['proxy____pass']         = 'Proxy Passwort';
$lang['proxy____ssl']          = 'SSL bei Verbindung zum Proxy verwenden';
$lang['proxy____except']       = 'Regulärer Ausdruck für URLs, bei denen kein Proxy verwendet werden soll';
$lang['safemodehack']          = 'Safemodehack verwenden';
$lang['ftp____host']           = 'FTP-Host für Safemodehack';
$lang['ftp____port']           = 'FTP-Port für Safemodehack';
$lang['ftp____user']           = 'FTP Benutzername für Safemodehack';
$lang['ftp____pass']           = 'FTP Passwort für Safemodehack';
$lang['ftp____root']           = 'FTP Wurzelverzeichnis für Safemodehack';
$lang['license_o_']            = 'Keine gewählt';
$lang['typography_o_0']        = 'keine';
$lang['typography_o_1']        = 'ohne einfache Anführungszeichen';
$lang['typography_o_2']        = 'mit einfachen Anführungszeichen (funktioniert nicht immer)';
$lang['userewrite_o_0']        = 'keines';
$lang['userewrite_o_1']        = '.htaccess';
$lang['userewrite_o_2']        = 'DokuWiki intern';
$lang['deaccent_o_0']          = 'aus';
$lang['deaccent_o_1']          = 'Akzente und Umlaute umwandeln';
$lang['deaccent_o_2']          = 'Umschrift';
$lang['gdlib_o_0']             = 'GD Lib nicht verfügbar';
$lang['gdlib_o_1']             = 'Version 1.x';
$lang['gdlib_o_2']             = 'Automatisch finden';
$lang['rss_type_o_rss']        = 'RSS 0.91';
$lang['rss_type_o_rss1']       = 'RSS 1.0';
$lang['rss_type_o_rss2']       = 'RSS 2.0';
$lang['rss_type_o_atom']       = 'Atom 0.3';
$lang['rss_type_o_atom1']      = 'Atom 1.0';
$lang['rss_content_o_abstract'] = 'Abstrakt';
$lang['rss_content_o_diff']    = 'Unified Diff';
$lang['rss_content_o_htmldiff'] = 'HTML formatierte Diff-Tabelle';
$lang['rss_content_o_html']    = 'Vollständiger HTML-Inhalt';
$lang['rss_linkto_o_diff']     = 'Änderungen zeigen';
$lang['rss_linkto_o_page']     = 'geänderte Seite';
$lang['rss_linkto_o_rev']      = 'Liste aller Änderungen';
$lang['rss_linkto_o_current']  = 'Aktuelle Seite';
$lang['compression_o_0']       = 'keine';
$lang['compression_o_gz']      = 'gzip';
$lang['compression_o_bz2']     = 'bz2';
$lang['xsendfile_o_0']         = 'nicht benutzen';
$lang['xsendfile_o_1']         = 'Proprietärer lighttpd-Header (vor Release 1.5)';
$lang['xsendfile_o_2']         = 'Standard X-Sendfile-Header';
$lang['xsendfile_o_3']         = 'Proprietärer Nginx X-Accel-Redirect-Header';
$lang['showuseras_o_loginname'] = 'Login-Name';
$lang['showuseras_o_username'] = 'Vollständiger Name des Benutzers';
$lang['showuseras_o_username_link'] = 'Kompletter Name des Benutzers als Interwiki-Link';
$lang['showuseras_o_email']    = 'E-Mail-Adresse des Benutzers (je nach Mailguard-Einstellung verschleiert)';
$lang['showuseras_o_email_link'] = 'E-Mail-Adresse des Benutzers als mailto:-Link';
$lang['useheading_o_0']        = 'Nie';
$lang['useheading_o_navigation'] = 'Nur Navigation';
$lang['useheading_o_content']  = 'Nur Wikiinhalt';
$lang['useheading_o_1']        = 'Immer';
$lang['readdircache']          = 'Maximales Alter des readdir-Caches (Sekunden)';
