<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Robert Surý <rsurycz@seznam.cz>
 * @author mkucera66 <mkucera66@seznam.cz>
 * @author Jaroslav Lichtblau <jlichtblau@seznam.cz>
 * @author Daniel Slováček <danslo@danslo.cz>
 * @author Martin Růžička <martinr@post.cz>
 */
$lang['account_suffix']        = 'Přípona vašeho účtu, tj. <code>@moje.domena.org</code>';
$lang['base_dn']               = 'Vaše doménové jméno DN. tj. <code>DC=moje,DC=domena,DC=org</code>';
$lang['domain_controllers']    = 'Seznam čárkou oddělených kontrolérů, tj. <code>srv1.domena.org,srv2.domena.org</code>';
$lang['admin_username']        = 'Privilegovaný uživatel Active Directory s přístupem ke všem datům. Volitelně, ale nutné pro určité akce typu zasílání mailů.';
$lang['admin_password']        = 'Heslo uživatele výše';
$lang['sso']                   = 'Chcete přihlašování Single-Sign-On pomocí jádra Kerberos nebo NTLM ( autentizační protokol obvyklý ve Windows)?';
$lang['sso_charset']           = 'Znaková sada kterou bude webserverem přenášeno uživatelské jméno pro Kerberos nebo NTLM. Prázdné pro UTF-8 nebo latin-1. Vyžaduje rozšíření iconv.';
$lang['real_primarygroup']     = 'Má být zjištěna primární skupina namísto vyhodnocení hodnoty "doménoví uživatelé" (pomalejší)';
$lang['use_ssl']               = 'Použít spojení SSL? Pokud ano, nevyužívejte TLS níže.';
$lang['use_tls']               = 'Použít spojení TLS? Pokud ano, nevyužívejte SSL výše.';
$lang['debug']                 = 'Zobrazit dodatečné debugovací výstupy při chybách?';
$lang['expirywarn']            = 'Dny mezi varováním o vypršení hesla uživatele a jeho vypršením. 0 značí vypnuto.';
$lang['additional']            = 'Čárkou oddělený seznam dodatečných atributů získávaných z uživatelských dat. Využito některými pluginy.';
$lang['update_name']           = 'Povolit uživatelům upravit jejich AD zobrazované jméno?';
$lang['update_mail']           = 'Povolit uživatelům upravit svou emailovou adresu?';
$lang['recursive_groups']      = 'Vyřešte vnořené skupiny do jejich příslušných členů (pomalejší).';
