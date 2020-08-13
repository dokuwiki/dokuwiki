<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Christopher Schive <chschive@frisurf.no>
 * @author Patrick <spill.p@hotmail.com>
 * @author Danny Buckhof <daniel.raknes@hotmail.no>
 * @author Patrick Sletvold <patricksletvold@hotmail.com>
 * @author Arne Hanssen <arnehans@getmail.no>
 */
$lang['account_suffix']        = 'Ditt konto-suffiks F. Eks. <code>@my.domain.org</code>';
$lang['base_dn']               = 'Din rot-DN. F.eks. <code>DC=my,DC=domain,DC=org</code>';
$lang['domain_controllers']    = 'En kommaseparert liste over domenekontrollere. Eks. <code>srv1.domene.org,srv2.domene.org</code>';
$lang['admin_username']        = 'En «Active Directory»-bruker med tilgang til alle andre brukeres data. Valgfritt, men nødvendig for visse handlinger f.eks. for utsendelse av e-poster til abonnenter.';
$lang['admin_password']        = 'Passordet til brukeren over.';
$lang['sso']                   = 'Skal engangspålogging via Kerberos eller NTLM bli brukt?';
$lang['sso_charset']           = 'Tegnsettet din web-server vil bruke for ditt Kerberos- eller NTLM-brukernavn. La stå tomt for UTF-8 eller ISO Latin-1. Avhengig av utvidelsen iconv.';
$lang['real_primarygroup']     = 'Skal en finne den virkelige gruppen i stedet for å anta at dette er "domene-brukere" (tregere).';
$lang['use_ssl']               = 'Bruk SSL tilknytning? Hvis denne brukes, ikke aktiver TLS nedenfor.';
$lang['use_tls']               = 'Bruk TLS tilknytning? Hvis denne brukes, ikke aktiver SSL over.';
$lang['debug']                 = 'Ved feil, vise tilleggsinformasjon for feilsøking?';
$lang['expirywarn']            = 'Antall dager på forhånd brukeren varsles om at passordet utgår. 0 for å deaktivere.';
$lang['additional']            = 'En kommaseparert liste med AD-attributter som skal hentes fra brukerdata. Blir brukt av enkelte programtillegg.';
$lang['update_name']           = 'Tillate at brukere endrer AD-visningsnavnet sitt?';
$lang['update_mail']           = 'Tillate at brukere endrer e-postadressen sin?';
