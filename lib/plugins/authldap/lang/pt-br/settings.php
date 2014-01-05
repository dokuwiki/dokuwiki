<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Victor Westmann <victor.westmann@gmail.com>
 * @author Frederico Guimarães <frederico@teia.bio.br>
 */
$lang['server']                = 'Seu servidor LDAP. Ou hostname (<code>localhost</code>) ou uma URL completa (<code>ldap://server.tld:389</code>)';
$lang['port']                  = 'Porta LDAP do servidor se nenhuma URL completa tiver sido fornecida acima';
$lang['usertree']              = 'Onde encontrar as contas de usuários. Eg. <code>ou=Pessoas, dc=servidor, dc=tld</code>';
$lang['grouptree']             = 'Onde encontrar os grupos de usuários. Eg. <code>ou=Pessoas, dc=servidor, dc=tld</code>';
$lang['userfilter']            = 'Filtro LDAP para pesquisar por contas de usuários. Ex. <code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>';
$lang['groupfilter']           = 'Filtro LDAP para pesquisar por grupos. Ex. <code>(&amp;(objectClass=posixGroup)(|(gidNumber=%{gid})(memberUID=%{user})))</code>';
$lang['version']               = 'A versão do protocolo para usar. Você talvez deva definir isto para <code>3</code>';
$lang['starttls']              = 'Usar conexões TLS?';
$lang['referrals']             = 'Permitir que as referências sejam seguidas?';
$lang['deref']                 = 'Como dereferenciar os aliases?';
$lang['binddn']                = 'DN de um vínculo opcional de usuário se vínculo anônimo não for suficiente. Eg. <code>cn=admin, dc=my, dc=home</code>';
$lang['bindpw']                = 'Senha do usuário acima';
$lang['userscope']             = 'Limitar escopo da busca para busca de usuário';
$lang['groupscope']            = 'Limitar escopo da busca para busca de grupo';
$lang['groupkey']              = 'Membro de grupo vem de qualquer atributo do usuário (ao invés de grupos padrões AD) e.g. departamento de grupo ou número de telefone';
$lang['debug']                 = 'Mostrar informações adicionais de depuração em erros';
$lang['deref_o_0']             = 'LDAP_DEREF_NEVER';
$lang['deref_o_1']             = 'LDAP_DEREF_SEARCHING';
$lang['deref_o_2']             = 'LDAP_DEREF_FINDING';
$lang['deref_o_3']             = 'LDAP_DEREF_ALWAYS';
