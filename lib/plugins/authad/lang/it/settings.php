<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Edmondo Di Tucci <snarchio@gmail.com>
 * @author Torpedo <dgtorpedo@gmail.com>
 */
$lang['account_suffix']        = 'Il suffisso del tuo account. Eg. <code>@my.domain.org</code>';
$lang['base_dn']               = 'Il tuo DN. base Eg. <code>DC=my,DC=domain,DC=org</code>';
$lang['domain_controllers']    = 'Elenco separato da virgole di Domain Controllers. Eg. <code>srv1.domain.org,srv2.domain.org</code>';
$lang['admin_username']        = 'Utente privilegiato di Active Directory con accesso ai dati di tutti gli utenti. Opzionale ma necessario per alcune attività come mandare email di iscrizione.';
$lang['admin_password']        = 'La password dell\'utente soprascritto.';
$lang['sso']                   = 'Deve essere usato Single-Sign-On via Kerberos oppure NTLM?';
$lang['sso_charset']           = 'Il set di caratteri che il tuo web server passera nel nome utente Kerberos o NTLM. Lasciare vuoto per UTF-8 p latin-1. Richiesta estensione iconv. ';
$lang['real_primarygroup']     = 'Se il vero gruppo primario dovesse essere risolo invece di assumere "Domain Users" (lento).';
$lang['use_ssl']               = 'Usare la connessione SSL? Se usata, non abilitare TSL qui sotto.';
$lang['use_tls']               = 'Usare la connessione TSL? Se usata, non abilitare SSL qui sopra.';
$lang['debug']                 = 'Visualizzare output addizionale di debug per gli errori?';
$lang['expirywarn']            = 'Giorni di preavviso per la scadenza della password dell\'utente. 0 per disabilitare.';
$lang['additional']            = 'Valori separati da virgola di attributi AD addizionali da caricare dai dati utente. Usato da alcuni plugin.';
