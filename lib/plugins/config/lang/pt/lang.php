<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author JPdroid <jpdroid.jpo@gmail.com>
 * @author Rafael Fernandes <rafa.fernan10@gmail.com>
 * @author Eduardo Mozart de Oliveira <eduardomozart182@gmail.com>
 * @author Gustavo B. Schenkel <gustavo.schenkel@gmail.com>
 * @author Paulo <pschopf@gmail.com>
 * @author Mario AlexandTeixeira dos Santos <masterofclan@gmail.com>
 * @author Maykon Oliveira <maykonoliveira850@gmail.com>
 * @author José Vieira <jmsv63@gmail.com>
 * @author José Monteiro <Jose.Monteiro@DoWeDo-IT.com>
 * @author Enrico Nicoletto <liverig@gmail.com>
 * @author Fil <fil@meteopt.com>
 * @author André Neves <drakferion@gmail.com>
 * @author José Campos <zecarlosdecampos@gmail.com>
 * @author Paulo Carmino <contato@paulocarmino.com>
 * @author Alfredo Silva <alfredo.silva@sky.com>
 * @author Guilherme Sá <guilherme.sa@hotmail.com>
 */
$lang['menu']                  = 'Configurações';
$lang['error']                 = 'Parâmetros de configuração não atualizados devido a valores inválidos. Reveja as modificações que pretende efetuar antes de re-submetê-las.<br />Os valores incorretos serão mostrados dentro de uma "moldura" vermelha.';
$lang['updated']               = 'Parâmetros de configuração atualizados.';
$lang['nochoice']              = '(não existem outras escolhas disponíveis)';
$lang['locked']                = 'O arquivo de configuração não pôde ser atualizado, se isso foi não intencional, <br />certifique-se que o nome e as permissões do arquivo de configuração estejam corretos.
';
$lang['danger']                = 'Perigo: Alterar esta opção poderá tornar seu wiki e o menu de configuração inacessíveis.';
$lang['warning']               = 'Aviso: A alteração desta opção poderá causar comportamento involuntário.';
$lang['security']              = 'Aviso de Segurança: Alterar esta opção pode apresentar um risco de segurança.';
$lang['_configuration_manager'] = 'Gerenciamento de Configuração';
$lang['_header_dokuwiki']      = 'DokuWiki';
$lang['_header_plugin']        = 'Plugins';
$lang['_header_template']      = 'Modelos';
$lang['_header_undefined']     = 'Configurações não Definidas';
$lang['_basic']                = 'Básicas';
$lang['_display']              = 'Apresentação';
$lang['_authentication']       = 'Autenticação';
$lang['_anti_spam']            = 'Anti-Spam';
$lang['_editing']              = 'Edição';
$lang['_links']                = 'Links';
$lang['_media']                = 'Mídia';
$lang['_notifications']        = 'Notificação';
$lang['_syndication']          = 'Sindicação (RSS)';
$lang['_advanced']             = 'Avançadas';
$lang['_network']              = 'Rede';
$lang['_msg_setting_undefined'] = 'Nenhum metadado definido.';
$lang['_msg_setting_no_class'] = 'Nenhuma classe definida.';
$lang['_msg_setting_no_known_class'] = 'Classe de configuração não disponível.';
$lang['_msg_setting_no_default'] = 'Sem valor padrão.';
$lang['title']                 = 'Título deste Wiki';
$lang['start']                 = 'Nome da Página Inicial';
$lang['lang']                  = 'Idioma';
$lang['template']              = 'Modelo';
$lang['tagline']               = 'Slogan (se o modelo for compatível)';
$lang['sidebar']               = 'Nome da página da barra lateral (se o modelo for compatível). Um campo vazio desativará a barra lateral';
$lang['license']               = 'Sob que licença o seu conteúdo deverá ser disponibilizado?';
$lang['savedir']               = 'Pasta para salvar dados';
$lang['basedir']               = 'Caminho do servidor (ex. <code>/dokuwiki/</code>). Deixe em branco para auto detecção.';
$lang['baseurl']               = 'URL do servidor (ex. <code>http://www.yourserver.com</code>). Deixe em branco para auto detecção.';
$lang['cookiedir']             = 'Caminho do cookie. Deixe em branco para usar a baseurl.';
$lang['dmode']                 = 'Modo de criação de pastas.';
$lang['fmode']                 = 'Modo de criação de arquivos.';
$lang['allowdebug']            = 'Permitir depuração <b>desabilite se não for necessário!</b>';
$lang['recent']                = 'Número de entradas por página em alterações recentes';
$lang['recent_days']           = 'Quantas mudanças recentes devem ser mantidas? (dias)';
$lang['breadcrumbs']           = 'Número máximo de breadcrumbs';
$lang['youarehere']            = 'Usar breadcrumbs hierárquicas (você provavelmente irá querer desativar a opção acima então)';
$lang['fullpath']              = 'Mostrar o caminho completo das páginas no rodapé';
$lang['typography']            = 'Executar substituições tipográficas';
$lang['dformat']               = 'Formato de Data (ver função PHP\'s <a href="http://php.net/strftime">strftime</a>)';
$lang['signature']             = 'O que inserir quando clicar no botão de assinatura do editor';
$lang['showuseras']            = 'O que exibir quando mostrar o utilizador que editou a página pela última vez';
$lang['toptoclevel']           = 'Nível de topo para a tabela de conteúdo';
$lang['tocminheads']           = 'Quantidade mínima de cabeçalhos para a construção da tabela de conteúdos.';
$lang['maxtoclevel']           = 'Nível máximo para a tabela de conteúdo';
$lang['maxseclevel']           = 'Nível máximo para editar seção';
$lang['camelcase']             = 'Usar CamelCase para links';
$lang['deaccent']              = 'Como limpar nomes de páginas';
$lang['useheading']            = 'Usar o primeiro cabeçalho para o nome da página';
$lang['sneaky_index']          = 'Por padrão, o DokuWiki irá exibir todos os namespaces na visualização do índice. Ao ativar essa opção, serão escondidos aqueles em que o utilizador não tenha permissão de leitura. Isto pode resultar na ocultação de subnamespaces acessíveis, o que poderá tornar o índice inútil para certas configurações de ACL.';
$lang['hidepages']             = 'Esconder páginas correspondentes com expressões regulares da pesquisa, índice e outros índices automáticos';
$lang['useacl']                = 'Usar listas de controle de acesso';
$lang['autopasswd']            = 'Auto-gerar senhas';
$lang['authtype']              = 'Método de autenticação';
$lang['passcrypt']             = 'Método de criptografia da senha';
$lang['defaultgroup']          = 'Grupo padrão onde novos usuários serão colocados';
$lang['superuser']             = 'Superusuário - um grupo, utilizador ou uma lista separada por vírgulas usuário1,@grupo1,usuário2 que tem acesso completo a todas as páginas e funções, independente das definições da ACL';
$lang['manager']               = 'Gerenciador - um grupo, utilizador ou uma lista separada por vírgulas usuário1,@grupo1,usuário2 que tem acesso a certas funções de gerenciamento';
$lang['profileconfirm']        = 'Confirmar mudanças no perfil com a senha';
$lang['rememberme']            = 'Permitir cookies de login permanentes (lembrar-me)';
$lang['disableactions']        = 'Desativar ações DokuWiki';
$lang['disableactions_check']  = 'Verificar';
$lang['disableactions_subscription'] = 'Subscrever/Des-subscrever';
$lang['disableactions_wikicode'] = 'Ver fonte/Exportar Direto';
$lang['disableactions_profile_delete'] = 'Excluir Sua Conta';
$lang['disableactions_other']  = 'Outras ações (separadas por vírgula)';
$lang['disableactions_rss']    = 'Sindicação XML (RSS)';
$lang['auth_security_timeout'] = 'Tempo Limite para Autenticações (segundos)';
$lang['securecookie']          = 'Os cookies definidos via HTTPS deverão ser enviados para o navegador somente via HTTPS? Desative essa opção quando somente a autenticação do seu wiki for realizada de maneira segura via SSL e a navegação de maneira insegura.';
$lang['samesitecookie']        = 'Outro site usa este cookie. Deixe vazio e permita que o navagador decida a política';
$lang['remote']                = 'Ativar o sistema de API remota. Isso permite que outros aplicativos acessem o wiki via XML-RPC ou outros mecanismos.';
$lang['remoteuser']            = 'Restringe o acesso remoto da API aos grupos separados por vírgula ou aos usuários fornecidos aqui. Deixe em branco para dar acesso a todos.';
$lang['remotecors']            = 'Habilitar Cross-Origin Resource Sharing (CORS) para interfaces remotas. Asterisco (*) permite todas as origens. Deixe em branco para negar CORS.';
$lang['usewordblock']          = 'Bloquear spam baseado em lista de palavras (wordlist)';
$lang['relnofollow']           = 'Usar rel="nofollow" em links externos';
$lang['indexdelay']            = 'Tempo de espera antes da indexação (seg)';
$lang['mailguard']             = 'Obscurecer endereços de email';
$lang['iexssprotect']          = 'Verificar os arquivos enviados contra possíveis códigos maliciosos em HTML ou JavaScript';
$lang['usedraft']              = 'Salvar o rascunho automaticamente durante a edição';
$lang['locktime']              = 'Idade máxima para arquivos de lock (seg)';
$lang['cachetime']             = 'Idade máxima para cache (seg)';
$lang['target____wiki']        = 'Parâmetro "target" para links internos';
$lang['target____interwiki']   = 'Parâmetro "target" para links entre wikis';
$lang['target____extern']      = 'Parâmetro "target" para links externos';
$lang['target____media']       = 'Parâmetro "target" para links de media';
$lang['target____windows']     = 'Parâmetro "target" para links do Windows';
$lang['mediarevisions']        = 'Ativar Mediarevisions?';
$lang['refcheck']              = 'Verificar se a mídia está em uso antes de excluí-la';
$lang['gdlib']                 = 'Versão GD Lib';
$lang['im_convert']            = 'Caminho para a ferramenta "convert" do ImageMagick';
$lang['jpg_quality']           = 'Compressão/Qualidade JPG (0-100)';
$lang['fetchsize']             = 'Tamanho máximo (bytes) que o fetch.php pode baixar de URLs externas, ex. para cache e redimensionamento de imagens externas.';
$lang['subscribers']           = 'Habilitar o suporte a subscrição de páginas  por e-mail';
$lang['subscribe_time']        = 'Tempo após o qual as listas de subscrição e "digests" são enviados (seg); Isto deve ser inferior ao tempo especificado em recent_days.';
$lang['notify']                = 'Sempre enviar notificações de mudanças para este endereço de e-mail';
$lang['registernotify']        = 'Sempre enviar informações de usuários registados para este endereço de e-mail';
$lang['mailfrom']              = 'Endereço de e-mail a ser utilizado para mensagens automáticas';
$lang['mailreturnpath']        = 'Endereço de e-mail do destinatário para notificações não entregues';
$lang['mailprefix']            = 'Prefixo de e-mail a ser utilizado para mensagens automáticas. Deixe em branco para usar o título do wiki';
$lang['htmlmail']              = 'Envie e-mails multipartes em HTML para uma melhor aparência, mas maiores em tamanho. Desative para mensagens em texto simples.';
$lang['dontlog']               = 'Desabilite a gravação de relatórios para estes tipos de relatórios.';
$lang['logretain']             = 'Quantos dias manter no log.';
$lang['sitemap']               = 'Gerar sitemap Google frequentemente (dias). 0 para desativar';
$lang['rss_type']              = 'Tipo de feed XML';
$lang['rss_linkto']            = 'Links de feed XML para';
$lang['rss_content']           = 'O que deve ser exibido nos itens do alimentador XML?';
$lang['rss_update']            = 'Intervalo de atualização do feed XML (seg)';
$lang['rss_show_summary']      = 'Resumo de exibição do feed XML no título';
$lang['rss_show_deleted']      = 'O feed XML mostra feeds excluídos';
$lang['rss_media']             = 'Que tipo de alterações devem ser listadas no feed XML?';
$lang['rss_media_o_both']      = 'ambos';
$lang['rss_media_o_pages']     = 'páginas';
$lang['rss_media_o_media']     = 'mídia';
$lang['updatecheck']           = 'Verificar por atualizações e avisos de segurança? O DokuWiki precisa contactar o "splitbrain.org" para efetuar esta verificação.';
$lang['userewrite']            = 'Usar URLs SEO';
$lang['useslash']              = 'Usar a barra como separador de namespaces nas URLs';
$lang['sepchar']               = 'Separador de palavras no nome da página';
$lang['canonical']             = 'Usar URLs absolutas (http://servidor/caminho)';
$lang['fnencode']              = 'Método de codificar nomes de arquivo não-ASCII.';
$lang['autoplural']            = 'Verificar formas plurais nos links';
$lang['compression']           = 'Método de compressão para arquivos attic';
$lang['gzip_output']           = 'Usar Content-Encoding do gzip para código xhtml';
$lang['compress']              = 'Compactar as saídas de CSS e JavaScript';
$lang['cssdatauri']            = 'Tamanho em bytes até ao qual as imagens referenciadas em arquivos CSS devem ser embutidas diretamente no CSS para reduzir a carga de pedidos HTTP extra. <code>400</code> a <code>600</code> bytes é um bom valor. Escolher <code>0</code> para desativar.';
$lang['send404']               = 'Enviar "HTTP 404/Página não encontrada" para páginas não existentes';
$lang['broken_iua']            = 'A função "ignore_user_abort" não está a funcionar no seu sistema? Isso pode causar um índice de busca defeituoso. Sistemas com IIS+PHP/CGI são conhecidos por possuírem este problema. Veja o <a href="http://bugs.splitbrain.org/?do=details&amp;task_id=852">bug 852</a> para mais informações.';
$lang['xsendfile']             = 'Usar o cabeçalho "X-Sendfile" para permitir o servidor de internet encaminhar arquivos estáticos? O seu servidor de internet precisa ter suporte a isso.';
$lang['renderer_xhtml']        = 'Renderizador a ser utilizado para a saída principal do wiki (xhtml)';
$lang['renderer__core']        = '%s (núcleo dokuwiki)';
$lang['renderer__plugin']      = '%s (plugin)';
$lang['search_nslimit']        = 'Limite a pesquisa aos atuais X namespaces. Quando uma pesquisa é executada a partir de uma página em um namespace mais profundo, os primeiros X namespaces serão adicionados como filtro';
$lang['search_fragment']       = 'Especifique o comportamento de pesquisa de fragmento padrão';
$lang['search_fragment_o_exact'] = 'exato';
$lang['search_fragment_o_starts_with'] = 'começa com';
$lang['search_fragment_o_ends_with'] = 'termina com';
$lang['search_fragment_o_contains'] = 'contém';
$lang['trustedproxy']          = 'Confie nos proxies de encaminhamento que correspondem a essa expressão regular sobre o verdadeiro IP do cliente que eles relatam. O padrão corresponde às redes locais. Deixe em branco para não confiar em proxy.';
$lang['_feature_flags']        = 'Sinalizadores de recursos';
$lang['defer_js']              = 'Adie a execução do javascript para depois da análise do HTML da página. Isso Melhora a velocidade da página, mas pode interromper um pequeno número de plugins.';
$lang['hidewarnings']          = 'Não exibir nenhum aviso (Warning) emitido pelo PHP. Isso pode facilitar a transição para o PHP8+. Avisos ainda serão registrados no log de erros e devem ser reportados.';
$lang['dnslookups']            = 'O DokuWiki irá procurar nomes de host para endereços IP remotos de usuários editando páginas. Se você tiver um servidor DNS lento, inoperante ou não quiser esse recurso, desabilite essa opção';
$lang['jquerycdn']             = 'Os arquivos de script jQuery e jQuery UI devem ser carregados de um CDN? Isso gera solicitações HTTP adicionais mas os arquivos são carregados mais rapidamente e os usuários já podem tê-los armazenados em cache.';
$lang['jquerycdn_o_0']         = 'Sem CDN, somente entrega local';
$lang['jquerycdn_o_jquery']    = 'CDN em code.jquery.com';
$lang['jquerycdn_o_cdnjs']     = 'CDN em cdnjs.com';
$lang['proxy____host']         = 'Nome do servidor proxy';
$lang['proxy____port']         = 'Porta de Proxy';
$lang['proxy____user']         = 'Nome de usuário Proxy';
$lang['proxy____pass']         = 'Senha do Proxy ';
$lang['proxy____ssl']          = 'Usar SSL para conectar ao proxy';
$lang['proxy____except']       = 'Expressão regular para bater com URLs para os quais o proxy deve ser ignorado.';
$lang['license_o_']            = 'Nenhuma escolha';
$lang['typography_o_0']        = 'nenhum';
$lang['typography_o_1']        = 'excluindo aspas simples';
$lang['typography_o_2']        = 'incluindo aspas simples (pode não funcionar sempre)';
$lang['userewrite_o_0']        = 'nenhum';
$lang['userewrite_o_1']        = '.htaccess';
$lang['userewrite_o_2']        = 'DokuWiki interno';
$lang['deaccent_o_0']          = 'desligado';
$lang['deaccent_o_1']          = 'remover acentos';
$lang['deaccent_o_2']          = 'romanizar';
$lang['gdlib_o_0']             = 'A GD Lib não está disponível';
$lang['gdlib_o_1']             = 'Versão 1.x';
$lang['gdlib_o_2']             = 'Auto-detecção';
$lang['rss_type_o_rss']        = 'RSS 0.91';
$lang['rss_type_o_rss1']       = 'RSS 1.0';
$lang['rss_type_o_rss2']       = 'RSS 2.0';
$lang['rss_type_o_atom']       = 'Atom 0.3';
$lang['rss_type_o_atom1']      = 'Atom 1.0';
$lang['rss_content_o_abstract'] = 'Abstrato';
$lang['rss_content_o_diff']    = 'Diferenças Unificadas';
$lang['rss_content_o_htmldiff'] = 'Tabela diff formatada em HTML';
$lang['rss_content_o_html']    = 'Conteúdo completo da página em HTML';
$lang['rss_linkto_o_diff']     = 'visualizar diferenças';
$lang['rss_linkto_o_page']     = 'página revista';
$lang['rss_linkto_o_rev']      = 'lista de revisões';
$lang['rss_linkto_o_current']  = 'página atual';
$lang['compression_o_0']       = 'sem compressão';
$lang['compression_o_gz']      = 'gzip';
$lang['compression_o_bz2']     = 'bz2';
$lang['xsendfile_o_0']         = 'não usar';
$lang['xsendfile_o_1']         = 'Cabeçalho proprietário lighttpd (anterior à versão 1.5)';
$lang['xsendfile_o_2']         = 'Cabeçalho X-Sendfile padrão';
$lang['xsendfile_o_3']         = 'Cabeçalho proprietário Nginx X-Accel-Redirect';
$lang['showuseras_o_loginname'] = 'Nome de usuário';
$lang['showuseras_o_username'] = 'Nome completo do usuário';
$lang['showuseras_o_username_link'] = 'Nome completo do usuário como link do usuário interwiki';
$lang['showuseras_o_email']    = 'E-mail do usuário (ofuscado de acordo com a configuração mailguard)';
$lang['showuseras_o_email_link'] = 'E-mail do usuário como um link mailto:';
$lang['useheading_o_0']        = 'Nunca';
$lang['useheading_o_navigation'] = 'Apenas Navegação';
$lang['useheading_o_content']  = 'Apenas Conteúdo Wiki';
$lang['useheading_o_1']        = 'Sempre';
$lang['readdircache']          = 'Idade máxima para a cache readdir (seg)';
