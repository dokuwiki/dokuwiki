<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Davor Turkalj <turki.bsc@gmail.com>
 */
$lang['account_suffix']        = 'Vaš sufiks korisničkog imena. Npr. <code>@my.domain.org</code>';
$lang['base_dn']               = 'Vaš bazni DN. Npr. <code>DC=my,DC=domain,DC=org</code>';
$lang['domain_controllers']    = 'Zarezom odvojena lista domenskih kontrolera. Npr. <code>srv1.domain.org,srv2.domain.org</code>';
$lang['admin_username']        = 'Privilegirani korisnik Active Directory-a s pristupom svim korisničkim podacima. Opcionalno, ali potrebno za određene akcije kao što je slanje pretplatničkih poruka.';
$lang['admin_password']        = 'Lozinka gore navedenoga korisnika.';
$lang['sso']                   = 'Da li će Single-Sign-On prijava biti korištena preko Kerberosa ili NTLM-a?';
$lang['sso_charset']           = 'Znakovni set koji će se koristiti Kerberos ili NTLM pri slanju imena korisnika. Prazno za UTF-8 ili latin-1. Zahtjeva iconv ekstenziju.';
$lang['real_primarygroup']     = 'Da li da se razluči stvarna primarna grupa umjesto pretpostavke da je to "Domain Users" (sporije !).';
$lang['use_ssl']               = 'Koristi SSL vezu? Ako da, dolje ne koristi TLS!';
$lang['use_tls']               = 'Koristi TLS vezu? Ako da, gore ne koristi SSL!';
$lang['debug']                 = 'Prikaži dodatni debug ispis u slučaju greške? ';
$lang['expirywarn']            = 'Upozori korisnike o isteku lozinke ovoliko dana. 0 za onemogućavanje. ';
$lang['additional']            = 'Zarezom odvojena lista dodatnih AD atributa koji se dohvaćaju iz korisničkih podataka. Koristi se u nekim dodatcima (plugin).';
$lang['update_name']           = 'Omogućiti korisnicima da izmjene svoje ime u AD-u?';
$lang['update_mail']           = 'Omogućiti korisnicima da izmjene svoju email adresu?';
