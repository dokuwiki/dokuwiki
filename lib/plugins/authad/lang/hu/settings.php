<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Fekete Ádám Zsolt <fadam@egbcsoport.hu>
 * @author Hamp Gábor <gabor.hamp@gmail.com>
 * @author Marton Sebok <sebokmarton@gmail.com>
 * @author Marina Vladi <deldadam@gmail.com>
 */
$lang['account_suffix']        = 'Felhasználói azonosító végződése, pl. <code>@my.domain.org</code>.';
$lang['base_dn']               = 'Bázis DN, pl. <code>DC=my,DC=domain,DC=org</code>.';
$lang['domain_controllers']    = 'Tartománykezelők listája vesszővel elválasztva, pl. <code>srv1.domain.org,srv2.domain.org</code>.';
$lang['admin_username']        = 'Privilegizált AD felhasználó, aki az összes feéhasználó adatait elérheti. Elhagyható, de bizonyos funkciókhoz, például a feliratkozási e-mailek kiküldéséhez szükséges.';
$lang['admin_password']        = 'Ehhez tartozó jelszó.';
$lang['sso']                   = 'Kerberos egyszeri bejelentkezés vagy NTLM használata?';
$lang['sso_charset']           = 'A webkiszolgáló karakterkészlete megfelel a Kerberos- és NTLM-felhasználóneveknek. Üres UTF-8 és Latin-1-hez. Szükséges az iconv bővítmény.';
$lang['real_primarygroup']     = 'A valódi elsődleges csoport feloldása a "Tartományfelhasználók" csoport használata helyett? (lassabb)';
$lang['use_ssl']               = 'SSL használata? Ha használjuk, tiltsuk le a TLS-t!';
$lang['use_tls']               = 'TLS használata? Ha használjuk, tiltsuk le az SSL-t!';
$lang['debug']                 = 'További hibakeresési üzenetek megjelenítése hiba esetén';
$lang['expirywarn']            = 'Felhasználók értesítése ennyi nappal a jelszavuk lejárata előtt. 0 a funkció kikapcsolásához.';
$lang['additional']            = 'Vesszővel elválasztott lista a további AD attribútumok lekéréséhez. Néhány bővítmény használhatja.';
$lang['update_name']           = 'Engedélyezed a felhasználóknak, hogy módosítsák az AD megjelenési nevüket?';
$lang['update_mail']           = 'A felhasználók frissíthetik (megváltozatathatják) az emailcímüket?';
$lang['update_pass']           = 'Engedélyezed a felhasználóknak a jelszavuk módosítását? Az SSL vagy TLS engedélyezése szükséges.';
