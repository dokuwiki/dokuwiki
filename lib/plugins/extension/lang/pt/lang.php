<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Thalles Lázaro <dokuwiki@thall.es>
 * @author Eduardo Mozart de Oliveira <eduardomozart182@gmail.com>
 * @author JPdroid <jpdroid.jpo@gmail.com>
 * @author Rafael Fernandes <rafa.fernan10@gmail.com>
 * @author Gustavo B. Schenkel <gustavo.schenkel@gmail.com>
 * @author Paulo <pschopf@gmail.com>
 * @author Mario AlexandTeixeira dos Santos <masterofclan@gmail.com>
 * @author Maykon Oliveira <maykonoliveira850@gmail.com>
 * @author José Vieira <jmsv63@gmail.com>
 * @author Guido Salatino <guidorafael23@gmail.com>
 * @author Romulo Pereira <romuloccomp@gmail.com>
 * @author Paulo Carmino <contato@paulocarmino.com>
 * @author Alfredo Silva <alfredo.silva@sky.com>
 * @author Guilherme Sá <guilherme.sa@hotmail.com>
 */
$lang['menu']                  = 'Gerenciador de Extensões';
$lang['tab_plugins']           = 'Plugins Instalados';
$lang['tab_templates']         = 'Modelos Instalados';
$lang['tab_search']            = 'Pesquisar e Instalar';
$lang['tab_install']           = 'Instalação Manual';
$lang['notimplemented']        = 'Este recurso não foi implementado ainda';
$lang['pluginlistsaveerror']   = 'Houve um erro ao salvar a lista de plugins';
$lang['unknownauthor']         = 'Autor desconhecido';
$lang['unknownversion']        = 'Versão desconhecida';
$lang['btn_info']              = 'Mostrar mais informações';
$lang['btn_update']            = 'Atualizar';
$lang['btn_uninstall']         = 'Desinstalar';
$lang['btn_enable']            = 'Ativar';
$lang['btn_disable']           = 'Desativar';
$lang['btn_install']           = 'Instalar';
$lang['btn_reinstall']         = 'Reinstalar';
$lang['js']['reallydel']       = 'Confirma a desinstalação desta extensão?';
$lang['js']['display_viewoptions'] = 'Opções de Visualização:';
$lang['js']['display_enabled'] = 'ativado';
$lang['js']['display_disabled'] = 'desativado';
$lang['js']['display_updatable'] = 'atualizável';
$lang['js']['close']           = 'Clique para fechar';
$lang['js']['filter']          = 'Mostrar apenas extensões atualizáveis';
$lang['search_for']            = 'Pesquisar Extensão:';
$lang['search']                = 'Pesquisar';
$lang['extensionby']           = '<strong>%s</strong> by %s';
$lang['screenshot']            = 'Screenshot de %s';
$lang['popularity']            = 'Popularidade: %s%%';
$lang['homepage_link']         = 'Documentos';
$lang['bugs_features']         = 'Erros';
$lang['tags']                  = 'Rótulos:';
$lang['author_hint']           = 'Pesquisar extensões deste autor';
$lang['installed']             = 'Instalado:';
$lang['downloadurl']           = 'Baixar URL:';
$lang['repository']            = 'Repositório:';
$lang['unknown']               = '<em>desconhecido</em>';
$lang['installed_version']     = 'Versão instalada:';
$lang['install_date']          = 'Sua última atualização:';
$lang['available_version']     = 'Versão disponível:';
$lang['compatible']            = 'Compatível com:';
$lang['depends']               = 'Depende de:';
$lang['similar']               = 'Semelhante a:';
$lang['conflicts']             = 'Conflita com:';
$lang['donate']                = 'Assim?';
$lang['donate_action']         = 'Pague um café para o autor!';
$lang['repo_retry']            = 'Tentar novamente';
$lang['provides']              = 'Fornece:';
$lang['status']                = 'Status:';
$lang['status_installed']      = 'instalado';
$lang['status_not_installed']  = 'não instalado';
$lang['status_protected']      = 'protegido';
$lang['status_enabled']        = 'ativado';
$lang['status_disabled']       = 'desativado';
$lang['status_unmodifiable']   = 'inalterável';
$lang['status_plugin']         = 'plugin';
$lang['status_template']       = 'modelo';
$lang['status_bundled']        = 'empacotado';
$lang['msg_enabled']           = 'Plugin %s ativado';
$lang['msg_disabled']          = 'Plugin %s desativado';
$lang['msg_delete_success']    = 'Extensão %s desinstalada';
$lang['msg_delete_failed']     = 'A desinstalação da Extensão %s falhou';
$lang['msg_install_success']   = 'Extensão %s instalada com sucesso';
$lang['msg_update_success']    = 'Extensão %s atualizada com sucesso';
$lang['msg_upload_failed']     = 'O envio do arquivo falhou';
$lang['msg_nooverwrite']       = 'A extensão %s já existe e não será substituída. Para substituir, marque a opção de substituição';
$lang['missing_dependency']    = 'dependência ausente ou desabilitada: %s';
$lang['found_conflict']        = 'Esta extensão está marcada como conflitante com as seguintes extensões instaladas: %s';
$lang['security_issue']        = 'Questão de Segurança: %s';
$lang['security_warning']      = 'Aviso de Segurança: %s';
$lang['update_message']        = 'Mensagem de Atualização: %s';
$lang['wrong_folder']          = 'Plugin instalado incorretamente: Renomear pasta de plugins de "%s" para "%s".';
$lang['url_change']            = 'A URL mudou: A URL para download mudou desde o último download. Verifique se a nova URL é válida antes de atualizar a extensão
Nova:%s
Antiga:%s';
$lang['error_badurl']          = 'URLs deve começar com http ou https';
$lang['error_dircreate']       = 'Não é possível criar pasta temporária para receber o download';
$lang['error_download']        = 'Não é possível baixar o arquivo:%s';
$lang['error_decompress']      = 'Não é possível descompactar o arquivo baixado. Talvez seja resultado de um download ruim e nesse caso você deve tentar novamente; ou o formato de compressão pode ser desconhecido e nesse caso, você precisará baixar e instalar manualmente.';
$lang['error_findfolder']      = 'Não foi possível identificar diretório de extensão, você precisa baixar e instalar manualmente';
$lang['error_copy']            = 'Houve um erro na cópia do arquivo durante a tentativa de instalar os arquivos para o diretório <em>%s</em>: o disco pode estar cheio ou as permissões de acesso incorretas. Isso pode ter resultado em um plugin parcialmente instalado e tornar instável seu wiki';
$lang['error_copy_read']       = 'Não foi possível ler o diretório %s';
$lang['error_copy_mkdir']      = 'Não foi possível criar o diretório %s';
$lang['error_copy_copy']       = 'Não foi possível copiar %s para %s';
$lang['error_archive_read']    = 'Não foi possível abrir o arquivo %s para leitura';
$lang['error_archive_extract'] = 'Não foi possível extrair o arquivo %s: %s';
$lang['error_uninstall_protected'] = 'A extensão %s está protegida e não pode ser desinstalada';
$lang['error_uninstall_dependants'] = 'A extensão %s ainda é necessária para %s e, portanto, não pode ser desinstalada';
$lang['error_disable_protected'] = 'A extensão %s está protegida e não pode ser desabilitada';
$lang['error_disable_dependants'] = 'A extensão %s ainda é necessária por %s e, portanto, não pode ser desabilitada';
$lang['error_nourl']           = 'Não foi possível encontrar nenhuma URL de download para a extensão %s';
$lang['error_notinstalled']    = 'A extensão %s não está instalada';
$lang['error_alreadyenabled']  = 'A extensão %s já foi habilitada';
$lang['error_alreadydisabled'] = 'A extensão %s já foi desabilitada';
$lang['error_minphp']          = 'A extensão %s requer pelo menos PHP %s, mas este wiki está executando PHP %s';
$lang['error_maxphp']          = 'A extensão %s suporta apenas PHP até %s, mas este wiki está executando PHP %s';
$lang['noperms']               = 'Diretório da extensão não é gravável';
$lang['notplperms']            = 'Diretório do modelo não é gravável';
$lang['nopluginperms']         = 'Diretório do plugin não é gravável';
$lang['git']                   = 'Esta extensão foi instalada via git, você pode não querer atualizá-la aqui.';
$lang['auth']                  = 'Este plugin não está ativado na configuração, considere desativá-lo.';
$lang['install_url']           = 'Instalar a partir da URL:';
$lang['install_upload']        = 'Enviar Extensão:';
$lang['repo_badresponse']      = 'O repositório de plugin retornou uma mensagem inválida.';
$lang['repo_error']            = 'O repositório do plugin não pôde ser contactado. Verifique se o seu servidor está autorizado a conectar com www.dokuwiki.org e verifique as configurações de proxy do servidor.';
$lang['nossl']                 = 'Seu PHP parece que perdeu o suporte a SSL. O download não vai funcionar para muitas extensões DokuWiki.';
$lang['popularity_high']       = 'Esta é uma das extensões mais populares';
$lang['popularity_medium']     = 'Esta extensão é bastante popular';
$lang['popularity_low']        = 'Esta extensão despertou algum interesse';
$lang['details']               = 'Detalhes';
