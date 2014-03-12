<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Martin Michalek <michalek.dev@gmail.com>
 */
$lang['account_suffix']        = 'Prípona používateľského účtu. Napr. <code>@my.domain.org</code>';
$lang['base_dn']               = 'Vaše base DN. Napr. <code>DC=my,DC=domain,DC=org</code>';
$lang['domain_controllers']    = 'Zoznam doménových radičov oddelených čiarkou. Napr. <code>srv1.domain.org,srv2.domain.org</code>';
$lang['admin_username']        = 'Privilegovaný používateľ Active Directory s prístupom ku všetkým dátam ostatných používateľov. Nepovinné nastavenie, ale potrebné pre určité akcie ako napríklad zasielanie mailov o zmenách.';
$lang['admin_password']        = 'Heslo vyššie uvedeného používateľa.';
$lang['sso']                   = 'Použiť Single-Sign-On cez Kerberos alebo NTLM?';
$lang['sso_charset']           = 'Znaková sada, v ktorej bude webserver prenášať meno Kerberos or NTLM používateľa. Prázne pole znamená UTF-8 alebo latin-1. Vyžaduje iconv rozšírenie.';
$lang['real_primarygroup']     = 'Použiť skutočnú primárnu skupinu používateľa namiesto "Doménoví používatelia" (pomalšie).';
$lang['use_ssl']               = 'Použiť SSL pripojenie? Ak áno, nepovoľte TLS nižšie.';
$lang['use_tls']               = 'Použiť TLS pripojenie? Ak áno, nepovoľte SSL vyššie.';
$lang['debug']                 = 'Zobraziť dodatočné ladiace informácie pri chybe?';
$lang['expirywarn']            = 'Počet dní pred uplynutím platnosti hesla, počas ktorých používateľ dostáva upozornenie. 0 deaktivuje túto voľbu.';
$lang['additional']            = 'Zoznam dodatočných AD atribútov oddelených čiarkou získaných z údajov používateľa. Používané niektorými pluginmi.';
