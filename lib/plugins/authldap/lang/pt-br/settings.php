<?php
/**
 * Brazilian Portuguese language file
 *
 * @author Victor Westmann <victor.westmann@gmail.com>
 */
$lang['server']                = 'Seu servidor LDAP. Ou hostname (<code>localhost</code>) ou uma URL completa (<code>ldap://server.tld:389</code>)';
$lang['port']                  = 'Porta LDAP do servidor se nenhuma URL completa tiver sido fornecida acima';
$lang['usertree']              = 'Onde encontrar as contas de usuários. Eg. <code>ou=Pessoas, dc=servidor, dc=tld</code>';
$lang['grouptree']             = 'Onde encontrar os grupos de usuários. Eg. <code>ou=Pessoas, dc=servidor, dc=tld</code>';
$lang['version']               = 'A versão do protocolo para usar. Você talvez deva definir isto para <code>3</code>';
$lang['starttls']              = 'Usar conexões TLS?';
$lang['referrals']             = 'Permitir referências serem seguidas?';
$lang['binddn']                = 'DN de um vínculo opcional de usuário se vínculo anônimo não for suficiente. Eg. <code>cn=admin, dc=my, dc=home</code>';
$lang['bindpw']                = 'Senha do usuário acima';
$lang['userscope']             = 'Limitar escopo da busca para busca de usuário';
$lang['groupscope']            = 'Limitar escopo da busca para busca de grupo';
$lang['groupkey']              = 'Membro de grupo vem de qualquer atributo do usuário (ao invés de grupos padrões AD) e.g. departamento de grupo ou número de telefone';
$lang['debug']                 = 'Mostrar informações adicionais de depuração em erros';
