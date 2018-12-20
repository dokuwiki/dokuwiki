<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Frank Loizzi <contact@software.bacal.de>
 * @author Matthias Schulte <dokuwiki@lupo49.de>
 * @author Ben Fey <benedikt.fey@beck-heun.de>
 * @author Jonas Gröger <jonas.groeger@gmail.com>
 * @author Carsten Perthel <carsten@cpesoft.com>
 */
$lang['account_suffix']        = 'Ihr Account-Suffix. Z. B. <code>@my.domain.org</code>';
$lang['base_dn']               = 'Ihr Base-DN. Z. B. <code>DC=my,DC=domain,DC=org</code>';
$lang['domain_controllers']    = 'Eine Komma-separierte Liste von Domänen-Controllern. Z. B. <code>srv1.domain.org,srv2.domain.org</code>';
$lang['groups_base_dn']               = 'Ihr Base-DN für die (rekursive) Suche von Gruppen. Z. B. <code>DC=my,DC=domain,DC=org</code>';
$lang['groups_domain_controllers']    = 'Eine Komma-separierte Liste von Domänen-Controllern zum (rekusriven) Suchen der Gruppen. Z. B. <code>srv1.domain.org,srv2.domain.org</code>';
$lang['admin_account_prefix']  = 'Ein Prefix, das dem Admin-Account bei der Suche vorangesetzt wird.';
$lang['admin_account_suffix']  = 'Ein Suffix, das dem Admin-Account bei der Suche angehängt wird. Dieses Suffix überschreibt das Account-Suffix.';
$lang['admin_username']        = 'Ein priviligierter Active Directory-Benutzer mit Zugriff zu allen anderen Benutzerdaten. Optional, aber wird benötigt für Aktionen wie z. B. dass Senden von Benachrichtigungs-Mails.';
$lang['admin_password']        = 'Das Passwort des obigen Benutzers.';
$lang['sso']                   = 'Soll Single-Sign-On via Kerberos oder NTLM benutzt werden?';
$lang['sso_charset']           = 'Der Zeichensatz, mit dem der Server den Kerberos- oder NTLM-Benutzernamen versendet. Leer lassen für UTF-8 oder latin-1. Benötigt die iconv-Erweiterung.';
$lang['real_primarygroup']     = 'Soll die echte primäre Gruppe aufgelöst werden anstelle der Annahme "Domain Users" (langsamer)';
$lang['use_ssl']               = 'SSL-Verbindung benutzen? Falls ja, TLS unterhalb nicht aktivieren.';
$lang['use_tls']               = 'TLS-Verbindung benutzen? Falls ja, SSL oberhalb nicht aktivieren.';
$lang['debug']                 = 'Zusätzliche Debug-Informationen bei Fehlern anzeigen?';
$lang['expirywarn']            = 'Tage im Voraus um Benutzer über ablaufende Passwörter zu informieren. 0 zum Ausschalten.';
$lang['additional']            = 'Eine Komma-separierte Liste von zusätzlichen AD-Attributen, die von den Benutzerobjekten abgefragt werden. Wird von einigen Plugins benutzt.';
$lang['update_name']           = 'Benutzern erlauben, ihren AD Anzeige-Namen zu ändern?';
$lang['update_mail']           = 'Benutzern erlauben, ihre E-Mail-Adresse zu ändern?';
$lang['option_deref']       = 'Spezifiziert alternative Regeln für das Folgen von Aliasen auf dem Server.';
$lang['option_sizelimit']   =  'Gibt die Höchstanzahl von Einträgen an, die bei einer Suchoperation zurückgegeben werden können.';
$lang['option_timelimit']   =  'Gibt die Anzahl von Sekunden an, für die auf Suchergebnisse gewartet werden soll.';
$lang['option_network_timeout'] =  'Option zum Setzen eines Netzwerk-Timeout (ab PHP 5.3.0).';
$lang['option_error_number'] =  'Wird benutzt um eine Fehlernummer über den letzten LDAP-Fehler zurückzugeben.';
$lang['option_restart']     =  'Legt fest, ob Verbindungen implizit neugestartet werden sollen.';
$lang['option_hostname']    = 'Setzt eine mit Leerzeichen getrennte Liste von Hosts die kontaktiert werden sollen wenn eine Verbindung aufgebaut wird.';
$lang['option_error_string'] =  'Setzt einen LDAP-Fehler für eine LDAP Operation.';
$lang['option_matched_dn']  = 'Setzt einen passende DN für eine LDAP Operation.';