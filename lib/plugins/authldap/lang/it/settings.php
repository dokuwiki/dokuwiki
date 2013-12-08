<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Edmondo Di Tucci <snarchio@gmail.com>
 * @author Claudio Lanconelli <lancos@libero.it>
 */
$lang['server']                = 'Il tuo server LDAP. Inserire o l\'hostname (<code>localhost</code>) oppure un URL completo (<code>ldap://server.tld:389</code>)';
$lang['port']                  = 'Porta del server LDAP se non è stato fornito un URL completo più sopra.';
$lang['usertree']              = 'Dove cercare l\'account utente. Eg. <code>ou=People, dc=server, dc=tld</code>';
$lang['grouptree']             = 'Dove cercare i gruppi utente. Eg. <code>ou=Group, dc=server, dc=tld</code>';
$lang['userfilter']            = 'Filtro per cercare l\'account utente LDAP. Eg. <code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>';
$lang['groupfilter']           = 'Filtro per cercare i gruppi LDAP. Eg. <code>(&amp;(objectClass=posixGroup)(|(gidNumber=%{gid})(memberUID=%{user})))</code>';
$lang['version']               = 'Versione protocollo da usare. Pu<code>3</code>';
$lang['starttls']              = 'Usare la connessione TSL?';
$lang['userscope']             = 'Limita il contesto di ricerca per la ricerca degli utenti';
$lang['groupscope']            = 'Limita il contesto di ricerca per la ricerca dei gruppi';
$lang['debug']                 = 'In caso di errori mostra ulteriori informazioni di debug';
