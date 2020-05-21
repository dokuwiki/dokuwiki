<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Jacob Palm <jacobpalmdk@icloud.com>
 * @author Soren Birk <soer9648@hotmail.com>
 * @author Jens Hyllegaard <jens.hyllegaard@gmail.com>
 */
$lang['account_suffix']        = 'Dit konto suffiks. F.eks. <code>@mit.domæne.dk</code>';
$lang['base_dn']               = 'Dit grund DN. F.eks. <code>DC=mit,DC=domæne,DC=dk</code>';
$lang['domain_controllers']    = 'En kommasepareret liste over domænecontrollere. F.eks. <code>srv1.domain.org,srv2.domain.org</code>';
$lang['admin_username']        = 'En privilegeret Active Directory bruger med adgang til alle andre brugeres data. Valgfri, men skal bruges til forskellige handlinger såsom at sende abonnement e-mails.';
$lang['admin_password']        = 'Adgangskoden til den ovenstående brugerkonto.';
$lang['sso']                   = 'Skal der benyttes Single-Sign-On via Kerberos eller NTLM?';
$lang['sso_charset']           = 'Tegnsættet din webserver leverer Kerberos eller NTLM brugernavnet i. Efterlad blank for UTF-8 eller latin-1. Kræver iconv udvidelsen.';
$lang['real_primarygroup']     = 'Bør den korrekte primære gruppe findes i stedet for at antage "Domain Users" (langsommere)';
$lang['use_ssl']               = 'Benyt SSL forbindelse? Hvis ja, vælg ikke TLS herunder.';
$lang['use_tls']               = 'Benyt TLS forbindelse? Hvis ja, vælg ikke SSL herover.';
$lang['debug']                 = 'Vis yderligere debug output ved fejl?';
$lang['expirywarn']            = 'Dage før udløb af adgangskode brugere skal advares. Angiv 0 for at deaktivere notifikation.';
$lang['additional']            = 'En kommasepareret liste over yderligere AD attributter der skal hentes fra brugerdata. Brug af nogen udvidelser.';
$lang['update_name']           = 'Tillad at brugere opdaterer deres visningnavn i AD?';
$lang['update_mail']           = 'Tillad at brugere opdaterer deres e-mail adresse?';
$lang['recursive_groups']      = 'Opslå nedarvede grupper til deres individuelle medlemmer (langsommere)';
