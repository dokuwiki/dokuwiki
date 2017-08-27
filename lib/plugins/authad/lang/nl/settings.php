<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Remon <no@email.local>
 * @author Gerrit Uitslag <klapinklapin@gmail.com>
 * @author Sjoerd <sjoerd@sjomar.eu>
 */
$lang['account_suffix']        = 'Je account domeinnaam. Bijv <code>@mijn.domein.org</code>';
$lang['base_dn']               = 'Je basis DN. Bijv. <code>DC=mijn,DC=domein,DC=org</code>';
$lang['domain_controllers']    = 'Eeen kommagescheiden lijst van domeinservers. Bijv. <code>srv1.domein.org,srv2.domein.org</code>';
$lang['admin_username']        = 'Een geprivilegeerde Active Directory gebruiker die bij alle gebruikersgegevens kan komen. Dit is optioneel maar kan nodig zijn voor bepaalde acties, zoals het versturen van abonnementsmailtjes.';
$lang['admin_password']        = 'Het wachtwoord van bovenstaande gebruiker.';
$lang['sso']                   = 'Wordt voor Single-Sign-on Kerberos of NTLM gebruikt?';
$lang['sso_charset']           = 'Het tekenset waarin je webserver de Kerberos of NTLM gebruikersnaam doorsturen. Leeglaten voor UTF-8 of latin-1. Vereist de iconv extensie.';
$lang['real_primarygroup']     = 'Moet de echte primaire groep worden opgezocht in plaats van het aannemen van "Domeingebruikers" (langzamer)';
$lang['use_ssl']               = 'SSL verbinding gebruiken? Zo ja, activeer dan niet de TLS optie hieronder.';
$lang['use_tls']               = 'TLS verbinding gebruiken? Zo ja, activeer dan niet de SSL verbinding hierboven.';
$lang['debug']                 = 'Aanvullende debug informatie tonen bij fouten?';
$lang['expirywarn']            = 'Waarschuwingstermijn voor vervallen wachtwoord. 0 om te deactiveren.';
$lang['additional']            = 'Een kommagescheiden lijst van extra AD attributen van de gebruiker. Wordt gebruikt door sommige plugins.';
$lang['update_name']           = 'Sta gebruikers toe om hun getoonde AD naam bij te werken';
$lang['update_mail']           = 'Sta gebruikers toe hun email adres bij te werken';
