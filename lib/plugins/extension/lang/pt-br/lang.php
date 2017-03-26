<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Felipe Castro <fefcas@gmail.com>
 * @author Hudson FAS <hudsonfas@gmail.com>
 * @author Frederico Gonçalves Guimarães <frederico@teia.bio.br>
 */
$lang['menu']                  = 'Gerenciador de extensões';
$lang['tab_plugins']           = 'Extensões instaladas';
$lang['tab_templates']         = 'Modelos instalados';
$lang['tab_search']            = 'Procurar e instalar';
$lang['tab_install']           = 'Instalar manualmente';
$lang['notimplemented']        = 'Esta função ainda não foi implementada';
$lang['notinstalled']          = 'Esta extensão não está instalada';
$lang['alreadyenabled']        = 'Esta extensão já foi habilitada';
$lang['alreadydisabled']       = 'Esta extensão já foi desabilitada';
$lang['pluginlistsaveerror']   = 'Houve um erro ao salvar a lista de extensões';
$lang['unknownauthor']         = 'Autor desconhecido';
$lang['unknownversion']        = 'Versão desconhecida';
$lang['btn_info']              = 'Mostrar mais informações';
$lang['btn_update']            = 'Atualizar';
$lang['btn_uninstall']         = 'Desinstalar';
$lang['btn_enable']            = 'Habilitar';
$lang['btn_disable']           = 'Desabilitar';
$lang['btn_install']           = 'Instalar';
$lang['btn_reinstall']         = 'Re-instalar';
$lang['js']['reallydel']       = 'Quer mesmo desinstalar esta extensão?';
$lang['js']['display_viewoptions'] = 'Opções de visualização:';
$lang['js']['display_enabled'] = 'habilitado';
$lang['js']['display_disabled'] = 'desabilitado';
$lang['js']['display_updatable'] = 'atualizável';
$lang['search_for']            = 'Procurar extensão:';
$lang['search']                = 'Procurar';
$lang['extensionby']           = '<strong>%s</strong> de %s';
$lang['screenshot']            = 'Tela congelada de %s';
$lang['popularity']            = 'Popularidade: %s%%';
$lang['homepage_link']         = 'Docs';
$lang['bugs_features']         = 'Erros';
$lang['tags']                  = 'Etiquetas:';
$lang['author_hint']           = 'Procurar extensões deste autor';
$lang['installed']             = 'Instalado:';
$lang['downloadurl']           = 'URL para baixar:';
$lang['repository']            = 'Repositório:';
$lang['unknown']               = '<em>desconhecido</em>';
$lang['installed_version']     = 'Versão instalada:';
$lang['install_date']          = 'Sua última atualização:';
$lang['available_version']     = 'Versão disponível:';
$lang['compatible']            = 'Compatível com:';
$lang['depends']               = 'Depende de:';
$lang['similar']               = 'Similar a:';
$lang['conflicts']             = 'Colide com:';
$lang['donate']                = 'Gostou deste?';
$lang['donate_action']         = 'Pague um café ao autor!';
$lang['repo_retry']            = 'Tentar de novo';
$lang['provides']              = 'Disponibiliza:';
$lang['status']                = 'Estado:';
$lang['status_installed']      = 'instalado';
$lang['status_not_installed']  = 'não instalado';
$lang['status_protected']      = 'protegido';
$lang['status_enabled']        = 'habilitado';
$lang['status_disabled']       = 'desabilitado';
$lang['status_unmodifiable']   = 'não modificável';
$lang['status_plugin']         = 'extensão';
$lang['status_template']       = 'modelo';
$lang['status_bundled']        = 'agrupado';
$lang['msg_enabled']           = 'Extensão %s habilitada';
$lang['msg_disabled']          = 'Extensão %s desabilitada';
$lang['msg_delete_success']    = 'Extensão %s desinstalada';
$lang['msg_delete_failed']     = 'Falha na desinstalação da extensão %s';
$lang['msg_template_install_success'] = 'Modelo %s instalado com sucesso';
$lang['msg_template_update_success'] = 'Modelo %s atualizado com sucesso';
$lang['msg_plugin_install_success'] = 'Extensão %s instalada com sucesso';
$lang['msg_plugin_update_success'] = 'Extensão %s atualizada com sucesso';
$lang['msg_upload_failed']     = 'Subida do arquivo falhou';
$lang['missing_dependency']    = '<strong>Dependência faltante ou desabilitada:</strong> %s';
$lang['security_issue']        = '<strong>Problema com segurança:</strong> %s';
$lang['security_warning']      = '<strong>Aviso sobre segurança:</strong> %s';
$lang['update_available']      = '<strong>Atualização:</strong> Nova versão %s está disponível.';
$lang['wrong_folder']          = '<strong>Extensão instalada incorretamente:</strong> Renomeie o diretório de extensões "%s" para "%s".';
$lang['url_change']            = '<strong>URL mudou:</strong> A URL para baixar mudou desde a última baixada. Verifique se a nova URL é válida antes de atualizar a extensão.<br />Novo: %s<br />Velho: %s';
$lang['error_badurl']          = 'O URL deve começar com http ou https';
$lang['error_dircreate']       = 'Impossível criar pasta temporária para receber o download';
$lang['error_download']        = 'Impossável baixar o arquivo: %s';
$lang['error_decompress']      = 'Impossável descompimir o arquivo baixado. Isso pode ser resultado de um download ruim que neste caso pode ser tentado novamente; ou o formato da compressão pode ser desconhecido, neste caso baixe e instale manualmente.';
$lang['error_findfolder']      = 'Impossíl identificar a extensão do diretório, você deve baixar e instalar manualmente.';
$lang['error_copy']            = 'Houve um erro de cópia de arquivo durante a tentativa de instalar os arquivos para o diretório <em>%s</em> : o disco pode estar cheio ou as permissões de acesso ao arquivo podem estar incorreta. Isso pode ter resultado em um plugin parcialmente instalado e deixar a sua instalação wiki instável';
$lang['noperms']               = 'Diretório de extensão não é gravável';
$lang['notplperms']            = 'Diretório de modelo (Template) não é gravável';
$lang['nopluginperms']         = 'Diretório de plugin não é gravável';
$lang['git']                   = 'A extensão foi instalada via git, você talvez não queira atualizá-lo aqui.';
$lang['auth']                  = 'O plugin auth não está ativado na configuração, considere desativá-lo.';
$lang['install_url']           = 'Instale a partir do URL:';
$lang['install_upload']        = 'Publicar Extensão:';
$lang['repo_error']            = 'O repositório de plugin não pode ser contactado. Certifique-se de que o servidor pode acessar www.dokuwiki.org e confira suas configurações de proxy.';
$lang['nossl']                 = 'Sua instalação PHP parece que não suporta SSL. Algumas extensões DokuWiki não serão baixadas.';
