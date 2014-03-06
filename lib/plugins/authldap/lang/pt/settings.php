<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author André Neves <drakferion@gmail.com>
 */
$lang['server']                = 'O seu servidor de LDAP. Ou hostname (<code>localhost</code>) ou URL qualificado completo (<code>ldap://servidor.tld:389</code>)';
$lang['port']                  = 'Porta de servidor de LDAP se o URL completo não foi fornecido acima';
$lang['usertree']              = 'Onde encontrar as contas de utilizador. Por exemplo <code>ou=Pessoas, dc=servidor, dc=tld</code>';
$lang['grouptree']             = 'Onde encontrar os grupos de utilizadores. Por exemplo code>ou=Grupo, dc=servidor, dc=tld</code>';
$lang['userfilter']            = 'Filtro LDAP para procurar por contas de utilizador. Por exemplo <code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>';
$lang['groupfilter']           = 'Filtro LDAP para procurar por grupos. Por exemplo <code>(&amp;(objectClass=posixGroup)(|(gidNumber=%{gid})(memberUID=%{user})))</code>';
$lang['version']               = 'A versão do protocolo a utilizar. Pode precisar de alterar isto para <code>3</code>';
$lang['starttls']              = 'Usar ligações TLS?';
$lang['referrals']             = 'Referrals devem ser seguidos?';
$lang['bindpw']                = 'Senha do utilizador acima';
$lang['debug']                 = 'Mostrar informação adicional de debug aquando de erros';
