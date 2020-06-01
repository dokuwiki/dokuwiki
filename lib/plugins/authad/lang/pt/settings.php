<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Paulo Schopf <pschopf@gmail.com>
 * @author Maykon Oliveira <maykonoliveira850@gmail.com>
 * @author André Neves <drakferion@gmail.com>
 * @author Murilo <muriloricci@hotmail.com>
 * @author Paulo Silva <paulotsilva@yahoo.com>
 * @author Guido Salatino <guidorafael23@gmail.com>
 * @author Guilherme Sá <guilherme.sa@hotmail.com>
 */
$lang['account_suffix']        = 'O sufixo da sua conta. Por exemplo, <code>@my.domain.org</code>';
$lang['base_dn']               = 'Sua base DN. Eg. <code> DC=meu, DC=dominio, DC=org </code>';
$lang['domain_controllers']    = 'Uma lista separada por vírgulas de Controladores de Domínio (AD DC). Ex.: <code>srv1.domain.org,srv2.domain.org</code>';
$lang['admin_username']        = 'Um usuário com privilégios no Active Directory que tenha acesso aos dados de todos os outros usuários. Opcional, mas necessário para certas ações como enviar e-mails de subscrição.';
$lang['admin_password']        = 'A senha para o usuário acima.';
$lang['sso']                   = 'Deve ser usado o Single-Sign-On via Kerberos ou NTLM?';
$lang['sso_charset']           = 'O charset do seu servidor web vai passar o nome de usuário Kerberos ou NTLM  vazio para UTF-8 ou latin-1. Requer a extensão iconv.';
$lang['real_primarygroup']     = 'O grupo primário deveria ser resolvido ao invés de assumir "Usuários de Domínio" (mais lento).';
$lang['use_ssl']               = 'Usar conexão SSL? Se usada, não ative a TLS abaixo.';
$lang['use_tls']               = 'Usar conexão TLS? Se usada, não ative SSL abaixo.';
$lang['debug']                 = 'Deve-se mostrar saída adicional de depuração de erros?';
$lang['expirywarn']            = 'Número de dias de avanço para avisar o utilizador da expiração da senha. 0 para desativar.';
$lang['additional']            = 'Uma lista separada por vírgula de atributos adicionais de AD para buscar a partir de dados do usuário. Usado por alguns plugins.';
$lang['update_name']           = 'Permitir que os usuários atualizem seu nome de exibição do AD?';
$lang['update_mail']           = 'Permitir que usuários atualizem seus endereços de e-mail?';
$lang['recursive_groups']      = 'Resolve grupos aninhados para seus respectivos membros (mais lento).';
