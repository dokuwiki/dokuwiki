<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Paulo Schopf <pschopf@gmail.com>
 * @author Mario AlexandTeixeira dos Santos <masterofclan@gmail.com>
 * @author Maykon Oliveira <maykonoliveira850@gmail.com>
 * @author André Neves <drakferion@gmail.com>
 * @author Guido Salatino <guidorafael23@gmail.com>
 * @author Romulo Pereira <romuloccomp@gmail.com>
 * @author Paulo Carmino <contato@paulocarmino.com>
 */
$lang['server']                = 'O seu servidor de LDAP. Ou hostname (<code>localhost</code>) ou URL qualificada completa (<code>ldap://servidor.tld:389</code>)';
$lang['port']                  = 'Porta de servidor de LDAP se a URL completa não foi fornecida acima';
$lang['usertree']              = 'Onde encontrar as contas de usuário. Por exemplo <code>ou=Pessoas, dc=servidor, dc=tld</code>';
$lang['grouptree']             = 'Onde encontrar os grupos de usuário. Por exemplo code>ou=Grupo, dc=servidor, dc=tld</code>';
$lang['userfilter']            = 'Filtro LDAP para procurar por contas de utilizador. Por exemplo <code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>';
$lang['groupfilter']           = 'Filtro LDAP para procurar por grupos. Por exemplo <code>(&amp;(objectClass=posixGroup)(|(gidNumber=%{gid})(memberUID=%{user})))</code>';
$lang['version']               = 'A versão do protocolo a utilizar. Pode precisar de alterar isto para <code>3</code>';
$lang['starttls']              = 'Usar conexões TLS?';
$lang['referrals']             = 'Referrals devem ser seguidos?';
$lang['deref']                 = 'Como desreferenciar aliases?';
$lang['binddn']                = 'DN de um usuário de ligação opcional, quando a ligação é anônima não é suficiente. Ex. <code> cn = admin, dc = my, dc = home </code>';
$lang['bindpw']                = 'Senha do usuário acima';
$lang['attributes']            = 'Atributos a serem recuperados com a pesquisa LDAP.';
$lang['userscope']             = 'Escopo de pesquisa Limite para pesquisa de usuário';
$lang['groupscope']            = 'Escopo de pesquisa limite para pesquisa de grupo';
$lang['userkey']               = 'Atributo denotando o nome de usuário; deve ser consistente com o filtro do usuário.';
$lang['groupkey']              = 'A participação no grupo a partir de qualquer atributo de usuário (em vez de AD padrão de grupos) ex: grupo de departamento ou número de telefone';
$lang['modPass']               = 'Sua senha LDAP pode ser alterada via dokuwiki?';
$lang['debug']                 = 'Mostrar informação adicional de debug quando ocorrerem erros';
$lang['deref_o_0']             = 'LDAP_DEREF_NEVER';
$lang['deref_o_1']             = 'LDAP_DEREF_SEARCHING';
$lang['deref_o_2']             = 'LDAP_DEREF_FINDING';
$lang['deref_o_3']             = 'LDAP_DEREF_ALWAYS';
$lang['referrals_o_-1']        = 'usar padrão';
$lang['referrals_o_0']         = 'não seguir as referências';
$lang['referrals_o_1']         = 'seguir as referências';
