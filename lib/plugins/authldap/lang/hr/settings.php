<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Davor Turkalj <turki.bsc@gmail.com>
 */
$lang['server']                = 'Vaš LDAP server. Upišite ili naziv računala (<code>localhost</code>) ili puni URL (<code>ldap://server.tld:389</code>)';
$lang['port']                  = 'LDAP server port, ako gore nije specificiran puni URL.';
$lang['usertree']              = 'Gdje da nađem korisničke prijave. Npr. <code>ou=People, dc=server, dc=tld</code>';
$lang['grouptree']             = 'Gdje da nađem korisničke grupe. Npr. <code>ou=Group, dc=server, dc=tld</code>';
$lang['userfilter']            = 'LDAP filter za pretragu korisničkih prijava. Npr. <code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>';
$lang['groupfilter']           = 'LDAP filter za pretragu grupa. Npr. <code>(&amp;(objectClass=posixGroup)(|(gidNumber=%{gid})(memberUID=%{user})))</code>';
$lang['version']               = 'Protokol koji se koristi. Možda će te trebati postaviti na <code>3</code>';
$lang['starttls']              = 'Korisni TLS vezu?';
$lang['referrals']             = 'Da li da slijedim uputnice?';
$lang['deref']                 = 'Kako da razlikujem aliase?';
$lang['binddn']                = 'DN opcionalnog korisnika ako anonimni korisnik nije dovoljan. Npr. <code>cn=admin, dc=my, dc=home</code>';
$lang['bindpw']                = 'Lozinka gore navedenog korisnika';
$lang['userscope']             = 'Ograniči područje za pretragu korisnika';
$lang['groupscope']            = 'Ograniči područje za pretragu grupa';
$lang['groupkey']              = 'Članstvo grupa iz svih atributa korisnika (umjesto standardnih AD grupa) npr. grupa iz odjela ili telefonskog broja';
$lang['debug']                 = 'Prikaži dodatne informacije u slučaju greške';
$lang['deref_o_0']             = 'LDAP_DEREF_NEVER';
$lang['deref_o_1']             = 'LDAP_DEREF_SEARCHING';
$lang['deref_o_2']             = 'LDAP_DEREF_FINDING';
$lang['deref_o_3']             = 'LDAP_DEREF_ALWAYS';
