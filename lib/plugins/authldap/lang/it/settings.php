<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Edmondo Di Tucci <snarchio@gmail.com>
 * @author Claudio Lanconelli <lancos@libero.it>
 * @author Francesco <francesco.cavalli@hotmail.com>
 * @author Torpedo <dgtorpedo@gmail.com>
 */
$lang['server']                = 'Il tuo server LDAP. Inserire o l\'hostname (<code>localhost</code>) oppure un URL completo (<code>ldap://server.tld:389</code>)';
$lang['port']                  = 'Porta del server LDAP se non è stato fornito un URL completo più sopra.';
$lang['usertree']              = 'Dove cercare l\'account utente. Eg. <code>ou=People, dc=server, dc=tld</code>';
$lang['grouptree']             = 'Dove cercare i gruppi utente. Eg. <code>ou=Group, dc=server, dc=tld</code>';
$lang['userfilter']            = 'Filtro per cercare l\'account utente LDAP. Eg. <code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>';
$lang['groupfilter']           = 'Filtro per cercare i gruppi LDAP. Eg. <code>(&amp;(objectClass=posixGroup)(|(gidNumber=%{gid})(memberUID=%{user})))</code>';
$lang['version']               = 'Versione protocollo da usare. Pu<code>3</code>';
$lang['starttls']              = 'Usare la connessione TSL?';
$lang['deref']                 = 'Come differenziare un alias?';
$lang['bindpw']                = 'Password del utente di cui sopra';
$lang['userscope']             = 'Limita il contesto di ricerca per la ricerca degli utenti';
$lang['groupscope']            = 'Limita il contesto di ricerca per la ricerca dei gruppi';
$lang['debug']                 = 'In caso di errori mostra ulteriori informazioni di debug';
$lang['deref_o_0']             = 'LDAP_DEREF_NEVER';
$lang['deref_o_1']             = 'LDAP_DEREF_SEARCHING';
$lang['deref_o_2']             = 'LDAP_DEREF_FINDING';
$lang['deref_o_3']             = 'LDAP_DEREF_ALWAYS';
$lang['referrals_o_-1']        = 'usa default';
$lang['referrals_o_0']         = 'non seguire i reindirizzamenti';
$lang['referrals_o_1']         = 'segui i reindirizzamenti';
