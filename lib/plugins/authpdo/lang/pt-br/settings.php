<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Frederico Gonçalves Guimarães <frederico@teia.bio.br>
 */
$lang['debug']                 = 'Exibir mensagens de erro detalhadas. Deve ser desabilitado após a configuração.';
$lang['dsn']                   = 'O DSN para conectar ao banco de dados.';
$lang['user']                  = 'O usuário para a conexão ao banco de dados acima (em branco para sqlite)';
$lang['pass']                  = 'A senha para a conexão ao banco de dados acima (em branco para sqlite)';
$lang['select-user']           = 'Declaração SQL para selecionar os dados de um único usuário';
$lang['select-user-groups']    = 'Declaração SQL para selecionar todos os grupos de um único usuário';
$lang['select-groups']         = 'Declaração SQL para selecionar todos os grupos disponíveis';
$lang['insert-user']           = 'Declaração SQL para inserir um novo usuário no banco de dados';
$lang['delete-user']           = 'Declaração SQL para remover um único usuário do banco de dados';
$lang['list-users']            = 'Declaração SQL para listar usuários correspondentes a um filtro';
$lang['count-users']           = 'Declaração SQL para contar usuários correspondentes a um filtro';
$lang['update-user-info']      = 'Declaração SQL para atualizar o nome completo e endereço de e-mail de um único usuário';
$lang['update-user-login']     = 'Declaração SQL para atualizar o nome de usuário de e-mail de um único usuário';
$lang['update-user-pass']      = 'Declaração SQL para atualizar a senha de um único usuário';
$lang['insert-group']          = 'Declaração SQL para inserir um novo grupo no banco de dados';
$lang['join-group']            = 'Declaração SQL para adicionar um usuário a um grupo existente';
$lang['leave-group']           = 'Declaração SQL para remover um usuário de um grupo';
$lang['check-pass']            = 'Declaração SQL para verificar a senha de um usuário. Pode ser deixada em branco se a informação da senha for obtida a partir do usuário selecionado.';
