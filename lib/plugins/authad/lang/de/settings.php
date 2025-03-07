<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author analogroboter <ropely@gmx.net>
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author C!own77 <clown77@posteo.de>
 * @author Frank Loizzi <contact@software.bacal.de>
 * @author Matthias Schulte <dokuwiki@lupo49.de>
 * @author Ben Fey <benedikt.fey@beck-heun.de>
 * @author Jonas Gröger <jonas.groeger@gmail.com>
 * @author Carsten Perthel <carsten@cpesoft.com>
 */
$lang['account_suffix']        = 'Dein Account-Suffix. Z.B. <code>@my.domain.org</code>';
$lang['base_dn']               = 'Dein Base-DN. Z.B. <code>DC=my,DC=domain,DC=org</code>';
$lang['domain_controllers']    = 'Eine Komma-separierte Liste von Domänen-Controllern. Z.B. <code>srv1.domain.org,srv2.domain.org</code>';
$lang['admin_username']        = 'Ein privilegierter Active Directory-Benutzer mit Zugriff zu allen anderen Benutzerdaten. Optional, aber wird benötigt für Aktionen wie z. B. dass Senden von Benachrichtigungs-Mails.';
$lang['admin_password']        = 'Das Passwort des obigen Benutzers.';
$lang['sso']                   = 'Soll Single-Sign-On via Kerberos oder NTLM benutzt werden?';
$lang['sso_charset']           = 'Der Zeichensatz in dem Kerberos oder NTLM den Usernamen übergibt. Leer lassen für UTF-8 oder latin-1. Erfordert die Erweiterung iconv.';
$lang['real_primarygroup']     = 'Soll die echte primäre Gruppe aufgelöst werden anstelle der Annahme "Domain Users" (langsamer)';
$lang['use_ssl']               = 'SSL-Verbindung benutzen? Falls ja, TLS unterhalb nicht aktivieren.';
$lang['use_tls']               = 'TLS-Verbindung benutzen? Falls ja, SSL oberhalb nicht aktivieren.';
$lang['debug']                 = 'Zusätzliche Debug-Informationen bei Fehlern anzeigen?';
$lang['expirywarn']            = 'Tage im Voraus um Benutzer über ablaufende Passwörter zu informieren. 0 zum Ausschalten.';
$lang['additional']            = 'Eine Komma-separierte Liste von zusätzlichen AD-Attributen, die von den Benutzerobjekten abgefragt werden. Wird von einigen Plugins benutzt.';
$lang['update_name']           = 'Nutzern erlauben ihren AD Anzeigenamen zu aktualisieren?';
$lang['update_mail']           = 'Nutzern erlauben ihre E-Mail-Adresse zu aktualisieren?';
$lang['update_pass']           = 'Nutzern erlauben, ihr Passwort zu ändern? Benötigt SSL oder TLS';
$lang['recursive_groups']      = 'Auflösen verschachtelter Gruppen für ihre jeweiligen Mitglieder (langsamer).';
