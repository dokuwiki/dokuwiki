<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Soren Birk <soer9648@hotmail.com>
 * @author Jens Hyllegaard <jens.hyllegaard@gmail.com>
 * @author Jacob Palm <mail@jacobpalm.dk>
 */
$lang['account_suffix']        = 'Dit konto suffiks. F.eks. <code>@mit.domæne.dk</code>';
$lang['base_dn']               = 'Dit grund DN. F.eks. <code>DC=mit,DC=domæne,DC=dk</code>';
$lang['domain_controllers']    = 'En kommasepareret liste over domænecontrollere. F.eks. <code>srv1.domain.org,srv2.domain.org</code>';
$lang['admin_username']        = 'En privilegeret Active Directory bruger med adgang til alle andre brugeres data. Valgfri, men skal bruges til forskellige handlinger såsom at sende abonnement e-mails.';
$lang['admin_password']        = 'Kodeordet til den ovenstående bruger.';
$lang['sso']                   = 'Bør Single-Sign-On via Kerberos eller NTLM bruges?';
$lang['real_primarygroup']     = 'Bør den korrekte primære gruppe findes i stedet for at antage "Domain Users" (langsommere)';
$lang['use_ssl']               = 'Benyt SSL forbindelse? hvis ja, vælg ikke TLS herunder.';
$lang['use_tls']               = 'Benyt TLS forbindelse? hvis ja, vælg ikke SSL herover.';
$lang['debug']                 = 'Vis yderligere debug output ved fejl?';
$lang['expirywarn']            = 'Dage før brugere skal advares om udløben adgangskode. 0 for at deaktivere.';
$lang['additional']            = 'En kommasepareret liste over yderligere AD attributter der skal hentes fra brugerdata. Brug af nogen udvidelser.';
$lang['update_name']           = 'Tillad at brugere opdaterer deres visningnavn i AD?';
$lang['update_mail']           = 'Tillad at brugere opdaterer deres e-mail adresse?';
