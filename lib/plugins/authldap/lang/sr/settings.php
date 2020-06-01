<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Milan Oparnica <milan.opa@gmail.com>
 * @author Марко М. Костић <marko.m.kostic@gmail.com>
 */
$lang['server']                = 'Vaš LDAP server. Bilo po nazivu (<code>localhost</code>) ili po punoj URL putanju  (<code>ldap://server.tld:389</code>)';
$lang['port']                  = 'Port LDAP servera ako nije zadat u više unetoj punoj URL putanji.';
$lang['usertree']              = 'Mesto za potragu za korisničkim nalozima. Npr. <code>ou=People, dc=server, dc=tld</code>';
$lang['grouptree']             = 'Mesto za potragu za korisničkim grupama. Npr. <code>ou=Group, dc=server, dc=tld</code>';
$lang['userfilter']            = 'LDAP filter za pretragu za korisničkim nalozima. Npr. <code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>';
$lang['groupfilter']           = 'LDAP filter za pretragu za korisničkim grupama. Npr. <code>(&amp;(objectClass=posixGroup)(|(gidNumber=%{gid})(memberUID=%{user})))</code>';
$lang['version']               = 'Verzija protokola. Može biti neophodno da ovo postavite na vrednost <code>3</code>';
$lang['starttls']              = 'Користити TLS везе?';
$lang['referrals']             = 'Да ли треба пратити реферале?';
$lang['deref']                 = 'Kako razrešiti pseudonime?';
$lang['bindpw']                = 'Лозинка корисника изнад';
$lang['userscope']             = 'Ограничи опсег претраживања за корисничке претраге';
$lang['groupscope']            = 'Ограничи опсег претраживања за групне претраге';
$lang['modPass']               = 'Омогућити измену LDAP лозинке преко докувикија?';
$lang['debug']                 = 'Прикажи додатне податке за поправљање грешака приликом настанка грешака';
$lang['referrals_o_-1']        = 'користи подразумевано';
$lang['referrals_o_0']         = 'не прати реферале';
$lang['referrals_o_1']         = 'прати реферале';
