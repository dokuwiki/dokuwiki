<?php
/**
 * Portuguese language file
 *
 * @author Frederico Gonçalves Guimarães <frederico@teia.bio.br>
 * @author Felipe Castro <fefcas@gmail.com>
 * @author Lucien Raven <lucienraven@yahoo.com.br>
 * @author Enrico Nicoletto <liverig@gmail.com>
 * @author Flávio Veras <flaviove@gmail.com>
 * @author Jeferson Propheta <jeferson.propheta@gmail.com>
 * @author jair.henrique@gmail.com
 * @author Luis Dantas <luis@dantas.com>
 * @author Frederico Guimarães <frederico@teia.bio.br>
 * @author Jair Henrique <jair.henrique@gmail.com>
 * @author Luis Dantas <luisdantas@gmail.com>
 * @author Sergio Motta sergio@cisne.com.br
 * @author Isaias Masiero Filho <masiero@masiero.org>
 * @author Balaco Baco <balacobaco@imap.cc>
 */
$lang['menu']                  = 'Configurações do DokuWiki';
$lang['error']                 = 'As configurações não foram atualizadas devido a um valor inválido. Por favor, reveja suas alterações e reenvie-as.<br />O(s) valor(es) incorreto(s) serão exibidos contornados por uma borda vermelha.';
$lang['updated']               = 'As configurações foram atualizadas com sucesso.';
$lang['nochoice']              = '(nenhuma outra opção disponível)';
$lang['locked']                = 'Não foi possível atualizar o arquivo de configurações. Se isso <br />
não for intencional, certifique-se de que o nome do arquivo e as <br />
e as suas permissões estejam corretos.';
$lang['danger']                = 'Perigo: Alterar esta opção poderá tornar o seu wiki e menu de configuração inacessíveis.';
$lang['warning']               = 'Aviso: A alteração desta opção pode causar um comportamento indesejável.';
$lang['security']              = 'Aviso de segurança: A alteração desta opção pode representar um risco de segurança.';
$lang['_configuration_manager'] = 'Gerenciador de configurações';
$lang['_header_dokuwiki']      = 'Configurações do DokuWiki';
$lang['_header_plugin']        = 'Configurações de plug-ins';
$lang['_header_template']      = 'Configurações de modelos';
$lang['_header_undefined']     = 'Configurações indefinidas';
$lang['_basic']                = 'Configurações básicas';
$lang['_display']              = 'Configurações de exibição';
$lang['_authentication']       = 'Configurações de autenticação';
$lang['_anti_spam']            = 'Configurações do anti-spam';
$lang['_editing']              = 'Configurações de edição';
$lang['_links']                = 'Configurações de link';
$lang['_media']                = 'Configurações de mídia';
$lang['_advanced']             = 'Configurações avançadas';
$lang['_network']              = 'Configurações de rede';
$lang['_plugin_sufix']         = 'Configurações de plug-ins';
$lang['_template_sufix']       = 'Configurações de modelos';
$lang['_msg_setting_undefined'] = 'Nenhum metadado configurado.';
$lang['_msg_setting_no_class'] = 'Nenhuma classe definida.';
$lang['_msg_setting_no_default'] = 'Nenhum valor padrão.';
$lang['fmode']                 = 'Modo de criação do arquivo';
$lang['dmode']                 = 'Modo de criação do diretório';
$lang['lang']                  = 'Idioma';
$lang['basedir']               = 'Diretório base';
$lang['baseurl']               = 'URL base';
$lang['savedir']               = 'Diretório para salvar os dados';
$lang['cookiedir']             = 'Caminhos dos cookies. Deixe em branco para usar a url base.';
$lang['start']                 = 'Nome da página inicial';
$lang['title']                 = 'Título do wiki';
$lang['template']              = 'Modelo';
$lang['license']               = 'Sob qual licença o seu conteúdo deve ser disponibilizado?';
$lang['fullpath']              = 'Indica o caminho completo das páginas no rodapé';
$lang['recent']                = 'Modificações recentes';
$lang['breadcrumbs']           = 'Número de elementos na trilha de páginas visitadas';
$lang['youarehere']            = 'Trilha hierárquica';
$lang['typography']            = 'Efetuar modificações tipográficas';
$lang['htmlok']                = 'Permitir incorporação de HTML';
$lang['phpok']                 = 'Permitir incorporação de PHP';
$lang['dformat']               = 'Formato da data (veja a função <a href="http://www.php.net/strftime">strftime</a> do PHP)';
$lang['signature']             = 'Assinatura';
$lang['toptoclevel']           = 'Nível mais alto para a tabela de conteúdos';
$lang['tocminheads']           = 'Quantidade mínima de cabeçalhos para a construção da tabela de conteúdos.';
$lang['maxtoclevel']           = 'Nível máximo para entrar na tabela de conteúdos';
$lang['maxseclevel']           = 'Nível máximo para gerar uma seção de edição';
$lang['camelcase']             = 'Usar CamelCase para links';
$lang['deaccent']              = '"Limpar" os nomes das páginas';
$lang['useheading']            = 'Usar o primeiro cabeçalho como nome da página';
$lang['refcheck']              = 'Verificação de referência da mídia';
$lang['refshow']               = 'Número de referências de mídia a exibir';
$lang['allowdebug']            = 'Habilitar a depuração <b>(desabilite se não for necessário!)</b>';
$lang['usewordblock']          = 'Bloquear spam baseado em lista de palavras';
$lang['indexdelay']            = 'Tempo de espera antes da indexação (seg)';
$lang['relnofollow']           = 'Usar rel="nofollow" em links externos';
$lang['mailguard']             = 'Obscurecer endereços de e-mail';
$lang['iexssprotect']          = 'Verificar a existência de possíveis códigos maliciosos em HTML ou JavaScript nos arquivos enviados';
$lang['showuseras']            = 'O que exibir quando mostrar o usuário que editou a página pela última vez';
$lang['useacl']                = 'Usar listas de controle de acesso';
$lang['autopasswd']            = 'Gerar senhas automaticamente';
$lang['authtype']              = 'Método de autenticação';
$lang['passcrypt']             = 'Método de criptografia da senha';
$lang['defaultgroup']          = 'Grupo padrão';
$lang['superuser']             = 'Superusuário - um grupo, usuário ou uma lista separada por vírgulas (usuário1,@grupo1,usuário2) que tenha acesso completo a todas as páginas e funções, independente das definições da ACL';
$lang['manager']               = 'Gerente - um grupo, usuário ou uma lista separada por vírgulas (usuário1,@grupo1,usuário2) que tenha acesso a certas funções de gerenciamento';
$lang['profileconfirm']        = 'Confirmar mudanças no perfil com a senha';
$lang['disableactions']        = 'Desabilitar as ações do DokuWiki';
$lang['disableactions_check']  = 'Verificação';
$lang['disableactions_subscription'] = 'Monitoramento';
$lang['disableactions_wikicode'] = 'Ver a fonte/Exportar sem processamento';
$lang['disableactions_other']  = 'Outras ações (separadas por vírgula)';
$lang['sneaky_index']          = 'Por padrão, o DokuWiki irá exibir todos os espaços de nomes na visualização do índice. Ao habilitar essa opção, serão escondidos aqueles que o usuário não tiver permissão de leitura. Isso pode resultar na omissão de subespaços de nomes, tornando o índice inútil para certas configurações de ACL.';
$lang['auth_security_timeout'] = 'Tempo limite de segurança para autenticações (seg)';
$lang['securecookie']          = 'Os cookies definidos via HTTPS devem ser enviados para o navegador somente via HTTPS? Desabilite essa opção quando somente a autenticação do seu wiki for realizada de maneira segura via SSL e a navegação, de maneira insegura.';
$lang['xmlrpc']                = 'Habilitar/desabilitar interface XML-RPC.';
$lang['xmlrpcuser']            = 'Acesso Restrito ao XML-RPC para grupos separados por virgula ou usuários aqui. Deixe em branco para conveder acesso a todos.';
$lang['updatecheck']           = 'Verificar atualizações e avisos de segurança? O DokuWiki precisa contactar o "splitbrain.org" para efetuar esse recurso.';
$lang['userewrite']            = 'Usar URLs "limpas"';
$lang['useslash']              = 'Usar a barra como separador de espaços de nomes nas URLs';
$lang['usedraft']              = 'Salvar o rascunho automaticamente durante a edição';
$lang['sepchar']               = 'Separador de palavras no nome da página';
$lang['canonical']             = 'Usar URLs absolutas (http://servidor/caminho)';
$lang['fnencode']              = 'Método de codificação não-ASCII de nome de arquivos.';
$lang['autoplural']            = 'Verificar formas plurais nos links';
$lang['compression']           = 'Método de compressão para arquivos antigos';
$lang['cachetime']             = 'Tempo máximo para o cache (seg)';
$lang['locktime']              = 'Tempo máximo para o bloqueio de arquivos (seg)';
$lang['fetchsize']             = 'Tamanho máximo (em bytes) que o "fetch.php" pode transferir do exterior';
$lang['notify']                = 'Enviar notificações de mudança para esse endereço de e-mail';
$lang['registernotify']        = 'Enviar informações de usuários registrados para esse endereço de e-mail';
$lang['mailfrom']              = 'Endereço de e-mail a ser utilizado para mensagens automáticas';
$lang['mailprefix']            = 'Prefixo do assunto dos e-mails de envio automático';
$lang['gzip_output']           = 'Usar "Content-Encoding" do gzip para o código xhtml';
$lang['gdlib']                 = 'Versão da biblioteca "GD Lib"';
$lang['im_convert']            = 'Caminho para a ferramenta de conversão ImageMagick';
$lang['jpg_quality']           = 'Qualidade de compressão do JPG (0-100)';
$lang['subscribers']           = 'Habilitar o suporte ao monitoramento de páginas';
$lang['subscribe_time']        = 'Tempo de espera antes do envio das listas e mensagens de monitoramento (segundos); este tempo deve ser menor que o especificado no parâmetro recent_days';
$lang['compress']              = 'Compactar as saídas de CSS e JavaScript';
$lang['cssdatauri']            = 'Tamanho máximo em bytes para o qual as imagens referenciadas em arquivos CSS devam ser incorporadas na folha de estilos (o arquivo CSS) para reduzir o custo dos pedidos HTTP. Essa técnica não funcionará na versões do IE < 8!  Valores de <code>400</code> a <code>600</code> são bons. Defina o valor <code>0</code> para desativar.';
$lang['hidepages']             = 'Esconder páginas correspondentes (expressão regular)';
$lang['send404']               = 'Enviar "HTTP 404/Página não encontrada" para páginas não existentes';
$lang['sitemap']               = 'Gerar Google Sitemap (dias)';
$lang['broken_iua']            = 'A função "ignore_user_abort" está com defeito no seu sistema? Isso pode causar um índice de busca defeituoso. IIS+PHP/CGI reconhecidamente possui esse erro. Veja o <a href="http://bugs.splitbrain.org/?do=details&amp;task_id=852">bug 852</a> para mais informações.';
$lang['xsendfile']             = 'Usar o cabeçalho "X-Sendfile" para permitir que o servidor web encaminhe arquivos estáticos? Seu servidor web precisa ter suporte a isso.';
$lang['renderer_xhtml']        = 'Renderizador a ser utilizado para a saída principal (xhtml) do wiki';
$lang['renderer__core']        = '%s (núcleo do DokuWiki)';
$lang['renderer__plugin']      = '%s ("plug-in")';
$lang['rememberme']            = 'Permitir cookies de autenticação permanentes ("Lembre-se de mim")';
$lang['rss_type']              = 'Tipo de fonte XML';
$lang['rss_linkto']            = 'Os links da fonte XML apontam para';
$lang['rss_content']           = 'O que deve ser exibido nos itens da fonte XML?';
$lang['rss_update']            = 'Intervalo de atualização da fonte XML (seg)';
$lang['recent_days']           = 'Quantas mudanças recentes devem ser mantidas (dias)?';
$lang['rss_show_summary']      = 'Resumo de exibição da fonte XML no título';
$lang['target____wiki']        = 'Parâmetro "target" para links internos';
$lang['target____interwiki']   = 'Parâmetro "target" para links interwiki';
$lang['target____extern']      = 'Parâmetro "target" para links externos';
$lang['target____media']       = 'Parâmetro "target" para links de mídia';
$lang['target____windows']     = 'Parâmetro "target" para links do Windows';
$lang['proxy____host']         = 'Nome do servidor proxy';
$lang['proxy____port']         = 'Porta do proxy';
$lang['proxy____user']         = 'Nome de usuário do proxy';
$lang['proxy____pass']         = 'Senha do proxy';
$lang['proxy____ssl']          = 'Usar SSL para conectar ao proxy';
$lang['proxy____except']       = 'Expressões regulares de URL para excessão de proxy.';
$lang['safemodehack']          = 'Habilitar o contorno de segurança';
$lang['ftp____host']           = 'Servidor FTP para o contorno de segurança';
$lang['ftp____port']           = 'Porta do FTP para o contorno de segurança';
$lang['ftp____user']           = 'Nome do usuário FTP para o contorno de segurança';
$lang['ftp____pass']           = 'Senha do usuário FTP para o contorno de segurança';
$lang['ftp____root']           = 'Diretório raiz do FTP para o contorno de segurança';
$lang['license_o_']            = 'Nenhuma escolha';
$lang['typography_o_0']        = 'nenhuma';
$lang['typography_o_1']        = 'excluir aspas simples';
$lang['typography_o_2']        = 'incluir aspas simples (nem sempre funciona)';
$lang['userewrite_o_0']        = 'não';
$lang['userewrite_o_1']        = '.htaccess';
$lang['userewrite_o_2']        = 'interno do DokuWiki';
$lang['deaccent_o_0']          = 'não';
$lang['deaccent_o_1']          = 'remover acentos';
$lang['deaccent_o_2']          = 'romanizar';
$lang['gdlib_o_0']             = 'a "GD Lib" não está disponível';
$lang['gdlib_o_1']             = 'versão 1.x';
$lang['gdlib_o_2']             = 'detecção automática';
$lang['rss_type_o_rss']        = 'RSS 0.91';
$lang['rss_type_o_rss1']       = 'RSS 1.0';
$lang['rss_type_o_rss2']       = 'RSS 2.0';
$lang['rss_type_o_atom']       = 'Atom 0.3';
$lang['rss_type_o_atom1']      = 'Atom 1.0';
$lang['rss_content_o_abstract'] = 'resumo';
$lang['rss_content_o_diff']    = 'diff unificado';
$lang['rss_content_o_htmldiff'] = 'tabela de diff formatada em HTML';
$lang['rss_content_o_html']    = 'conteúdo completo da página em HTML';
$lang['rss_linkto_o_diff']     = 'visualização das diferenças';
$lang['rss_linkto_o_page']     = 'página revisada';
$lang['rss_linkto_o_rev']      = 'lista de revisões';
$lang['rss_linkto_o_current']  = 'página atual';
$lang['compression_o_0']       = 'nenhum';
$lang['compression_o_gz']      = 'gzip';
$lang['compression_o_bz2']     = 'bz2';
$lang['xsendfile_o_0']         = 'não usar';
$lang['xsendfile_o_1']         = 'cabeçalho proprietário lighttpd (anterior à versão 1.5)';
$lang['xsendfile_o_2']         = 'cabeçalho "X-Sendfile" padrão';
$lang['xsendfile_o_3']         = 'cabeçalho proprietário "Nginx X-Accel-Redirect"';
$lang['showuseras_o_loginname'] = 'nome de usuário';
$lang['showuseras_o_username'] = 'nome completo do usuário';
$lang['showuseras_o_email']    = 'endereço de e-mail do usuário (obscurecido segundo a definição anterior)';
$lang['showuseras_o_email_link'] = 'endereço de e-mail de usuário como um link "mailto:"';
$lang['useheading_o_0']        = 'nunca';
$lang['useheading_o_navigation'] = 'somente a navegação';
$lang['useheading_o_content']  = 'somente o conteúdo do wiki';
$lang['useheading_o_1']        = 'sempre';
$lang['readdircache']          = 'Tempo máximo para cache readdir (segundos)';
