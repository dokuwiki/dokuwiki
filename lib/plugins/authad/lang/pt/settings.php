<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author André Neves <drakferion@gmail.com>
 * @author Murilo <muriloricci@hotmail.com>
 * @author Paulo Silva <paulotsilva@yahoo.com>
 * @author Guido Salatino <guidorafael23@gmail.com>
 */
$lang['account_suffix']        = 'O sufixo da sua conta. Por exemplo, <code>@my.domain.org</code>';
$lang['base_dn']               = 'Sua base DN. Eg. <code> DC=meu, DC=dominio, DC=org </code>';
$lang['domain_controllers']    = 'Uma lista separada por vírgulas de Controladores de Domínio (AD DC). Ex.: <code>srv1.domain.org,srv2.domain.org</code>';
$lang['admin_username']        = 'Um utilizador com privilégios na Active Directory que tenha acesso aos dados de todos os outros utilizadores. Opcional, mas necessário para certas ações como enviar emails de subscrição.';
$lang['admin_password']        = 'A senha para o utilizador acima.';
$lang['sso']                   = 'Deve ser usado o Single-Sign-On via Kerberos ou NTLM?';
$lang['sso_charset']           = 'O charset do seu servidor web vai passar o nome de usuário Kerberos ou NTLM  vazio para UTF-8 ou latin-1. Requer a extensão iconv.';
$lang['real_primarygroup']     = 'Deveria ser resolvido, de fato, o grupo primário ao invés de assumir "Usuários de Domínio" (mais lento).';
$lang['use_ssl']               = 'Usar ligação SSL? Se usada, não ative TLS abaixo.';
$lang['use_tls']               = 'Usar ligação TLS? Se usada, não ative SSL abaixo.';
$lang['debug']                 = 'Deve-se mostrar saída adicional de depuração de erros?';
$lang['expirywarn']            = 'Número de dias de avanço para avisar o utilizador da expiração da senha. 0 para desativar.';
$lang['additional']            = 'Uma lista separada por vírgula de atributos adicionais de AD para buscar a partir de dados do usuário. Usado por alguns plugins.';
