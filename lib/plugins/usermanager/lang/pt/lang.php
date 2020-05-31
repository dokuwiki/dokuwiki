<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Paulo Schopf <pschopf@gmail.com>
 * @author Mario AlexandTeixeira dos Santos <masterofclan@gmail.com>
 * @author Maykon Oliveira <maykonoliveira850@gmail.com>
 * @author José Vieira <jmsv63@gmail.com>
 * @author José Monteiro <Jose.Monteiro@DoWeDo-IT.com>
 * @author Enrico Nicoletto <liverig@gmail.com>
 * @author Fil <fil@meteopt.com>
 * @author André Neves <drakferion@gmail.com>
 * @author José Campos <zecarlosdecampos@gmail.com>
 * @author Guido Salatino <guidorafael23@gmail.com>
 * @author Romulo Pereira <romuloccomp@gmail.com>
 * @author Paulo Carmino <contato@paulocarmino.com>
 * @author Alfredo Silva <alfredo.silva@sky.com>
 * @author Guilherme Sá <guilherme.sa@hotmail.com>
 */
$lang['menu']                  = 'Gerenciador de Perfis';
$lang['noauth']                = '(autenticação de usuário indisponível)';
$lang['nosupport']             = '(gerenciamento de usuários não suportado)';
$lang['badauth']               = 'Mecanismo de autenticação inválido';
$lang['user_id']               = 'Usuário';
$lang['user_pass']             = 'Senha';
$lang['user_name']             = 'Nome Real';
$lang['user_mail']             = 'E-mail';
$lang['user_groups']           = 'Grupos';
$lang['field']                 = 'Campo';
$lang['value']                 = 'Valor';
$lang['add']                   = 'Adicionar';
$lang['delete']                = 'Excluir';
$lang['delete_selected']       = 'Excluir Selecionado(s)';
$lang['edit']                  = 'Editar';
$lang['edit_prompt']           = 'Editar usuário';
$lang['modify']                = 'Salvar Alterações';
$lang['search']                = 'Pesquisar';
$lang['search_prompt']         = 'Pesquisar';
$lang['clear']                 = 'Limpar Filtro de Pesquisa';
$lang['filter']                = 'Filtro';
$lang['export_all']            = 'Exportar Todos os Usuários (CSV)';
$lang['export_filtered']       = 'Exportar a Lista de Usuários Filtrada (CSV)';
$lang['import']                = 'Importar Novos Usuários';
$lang['line']                  = 'Linha nº';
$lang['error']                 = 'Mensagem de erro';
$lang['summary']               = 'Mostrando usuários %1$d-%2$d de %3$d encontrados. Total de %4$d inscritos.';
$lang['nonefound']             = 'Nenhum usuário encontrado. Total de %d inscritos.';
$lang['delete_ok']             = '%d usuários excluídos';
$lang['delete_fail']           = '%d exclusões com erro.';
$lang['update_ok']             = 'Usuário atualizado';
$lang['update_fail']           = 'Usuário não atualizado';
$lang['update_exists']         = 'Erro na alteração do nome, porque o usuário (%s) já existe (as alterações restantes serão aplicadas).';
$lang['start']                 = 'início';
$lang['prev']                  = 'anterior';
$lang['next']                  = 'seguinte';
$lang['last']                  = 'último';
$lang['edit_usermissing']      = 'Usuário selecionado não encontrado. Terá já sido excluído ou alterado?';
$lang['user_notify']           = 'Notificar usuário';
$lang['note_notify']           = 'Notificações só são enviadas se for atribuída uma nova senha ao usuário.';
$lang['note_group']            = 'Os novos usuários são adicionados ao grupo padrão (%s) se não for especificado nenhum grupo.';
$lang['note_pass']             = 'A senha será automaticamente gerada se o campo esquerdo estiver vazio e a notificação de usuário estiver ativada.';
$lang['add_ok']                = 'Usuário adicionado';
$lang['add_fail']              = 'Usuário não adicionado';
$lang['notify_ok']             = 'E-mail de notificação enviada.';
$lang['notify_fail']           = 'Não foi possível enviar e-mail de notificação';
$lang['import_userlistcsv']    = 'Arquivo de lista de usuário (CSV):
';
$lang['import_header']         = 'Importações Mais Recentes - Falhas';
$lang['import_success_count']  = 'Importar Usuários: %d usuários encontrados, %d importados com sucesso.';
$lang['import_failure_count']  = 'Importar Usuários: %d falharam. As falhas estão listadas abaixo.';
$lang['import_error_fields']   = 'Campos insuficientes, encontrados %d mas requeridos 4.';
$lang['import_error_baduserid'] = 'Falta id de usuário';
$lang['import_error_badname']  = 'Nome inválido';
$lang['import_error_badmail']  = 'E-mail inválido';
$lang['import_error_upload']   = 'Erro na importação. O arquivo csv não pôde ser importado ou está vazio.';
$lang['import_error_readfail'] = 'Erro na importação. Não foi possível ler o arquivo submetido.';
$lang['import_error_create']   = 'Não foi possível criar o usuário';
$lang['import_notify_fail']    = 'A mensagem de notificação não pôde ser enviada para o usuário importado, %s com e-mail %s.';
$lang['import_downloadfailures'] = 'Baixar Falhas como CSV para correção';
$lang['addUser_error_missing_pass'] = 'Por favor, defina uma senha ou ative a notificação do usuário para ativar a geração de senha.';
$lang['addUser_error_pass_not_identical'] = 'As senhas digitadas não são idênticas.';
$lang['addUser_error_modPass_disabled'] = 'A alteração de senhas está desativada no momento';
$lang['addUser_error_name_missing'] = 'Por favor, insira um nome para o novo usuário.';
$lang['addUser_error_modName_disabled'] = 'A alteração de nomes está desativada no momento.';
$lang['addUser_error_mail_missing'] = 'Por favor, insira um endereço de e-mail para o novo usuário.';
$lang['addUser_error_modMail_disabled'] = 'A alteração do e-mail está desativada no momento.';
$lang['addUser_error_create_event_failed'] = 'Um plugin impediu que o novo usuário fosse adicionado. Revise outras possíveis mensagens para mais informações.';
