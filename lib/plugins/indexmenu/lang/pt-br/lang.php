<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Eduardo Mozart de Oliveira <eduardomozart182@gmail.com>
 * @author Alexandre Belchior <alexbelchior@gmail.com>
 */
$lang['menu']                  = 'Utilitários Indexmenu';
$lang['fetch']                 = 'Show';
$lang['install']               = 'Instalar';
$lang['delete']                = 'Delete';
$lang['check']                 = 'Checar';
$lang['no_repos']              = 'Nenhuma URL de repositório de tema configurado.';
$lang['disabled']              = 'Desabilitado';
$lang['conn_err']              = 'Erro de conexão';
$lang['dir_err']               = 'Não é possível criar pasta temporária para receber o tema';
$lang['down_err']              = 'Não é possível receber o tema';
$lang['zip_err']               = 'Erro de criação ou extração de zip';
$lang['install_ok']            = 'tema instalado com sucesso. O novo tema está disponível na barra de ferramentas da pagina de edição ou com o <code>js#theme_name option</code>.';
$lang['install_no']            = 'Erro de conexão. No entanto, você pode tentar enviar manualmente seu tema de <a href="http://samuele.netsons.org/dokuwiki/lib/plugins/indexmenu/upload/">Aqui</a>.';
$lang['delete_ok']             = 'Tema deletado com sucesso.';
$lang['delete_no']             = 'Ocorreu um erro durante a exclusão do tema';
$lang['upload']                = 'Compartilhar';
$lang['checkupdates']          = 'Atualização de plugins';
$lang['noupdates']             = 'Indexmenu não precisa de atualização. Você já possui a última versão.';
$lang['infos']                 = 'Você pode criar seu tema seguindo as instruções na página <a href="https://www.dokuwiki.org/plugin:indexmenu#theme_tutorial">Theme Tutorial</a>. <br /> Então você poderia deixar mais pessoas felizes :-) enviando para o repositório público do indexmenu, com o botão "share" sob esse tema.';
$lang['showsort']              = 'Número de ordem do Indexmenu';
$lang['donation_text']         = 'O plug-in indexmenu não é patrocinado por ninguém, mas eu desenvolvo e dou suporte de graça durante meu tempo livre. Se você ganhar algo graças a isso ou quiser apoiar o seu desenvolvimento, você pode considerar fazer uma doação.';
$lang['js']['indexmenuwizard'] = 'Assistente do Indexmenu';
$lang['js']['index']           = 'Índice';
$lang['js']['options']         = 'Opções';
$lang['js']['navigation']      = 'Navegação';
$lang['js']['sort']            = 'Ordenar';
$lang['js']['filter']          = 'Filtro';
$lang['js']['performance']     = 'Performance';
$lang['js']['namespace']       = 'Namespace';
$lang['js']['nsdepth']         = 'Profundidade';
$lang['js']['js']              = 'Árvore renderizada por Javascript, você pode definir seu próprio tema';
$lang['js']['theme']           = 'Tema';
$lang['js']['navbar']          = 'A árvore é aberta no namespace atual';
$lang['js']['context']         = 'Exibe a árvore do contexto de namespace do wiki atual';
$lang['js']['nocookie']        = 'Não se lembre de nós abertos / fechados durante a navegação do usuário';
$lang['js']['noscroll']        = 'Evitar a rolagem da árvore quando ela não se encaixa na largura do contêiner';
$lang['js']['notoc']           = 'Desativar o recurso de visualização do toc';
$lang['js']['tsort']           = 'Por título';
$lang['js']['dsort']           = 'Por data';
$lang['js']['msort']           = 'Por meta tag';
$lang['js']['nsort']           = 'Ordenar também namespaces';
$lang['js']['hsort']           = 'Ordenar headpage up';
$lang['js']['rsort']           = 'Inverta a classificação de páginas';
$lang['js']['nons']            = 'Mostrar apenas páginas';
$lang['js']['nopg']            = 'Mostrar apenas namespaces';
$lang['js']['max']             = 'Quantos níveis renderizar com ajax quando um nó é aberto. Além disso, quantos subníveis abaixo desse nível são recuperados com AJAX, e não de uma só vez.';
$lang['js']['maxjs']           = 'Quantos níveis para renderizar no navegador do cliente quando um nó é aberto';
$lang['js']['id']              = 'ID de cookie definido automaticamente para este indexmenu';
$lang['js']['insert']          = 'Inserir indexmenu';
$lang['js']['metanum']         = 'Número Meta para classificação';
$lang['js']['insertmetanum']   = 'Inserir metanumber';
$lang['js']['page']            = 'Página';
$lang['js']['revs']            = 'Revisões';
$lang['js']['tocpreview']      = 'Pré-visualização Toc';
$lang['js']['editmode']        = 'Modo de edição';
$lang['js']['insertdwlink']    = 'Inserir como DWlink';
$lang['js']['insertdwlinktooltip'] = 'Insira o link desta página na caixa de edição na posição do cursor';
$lang['js']['ns']              = 'Namespace';
$lang['js']['search']          = 'Procurar...';
$lang['js']['searchtooltip']   = 'Procurar por páginas dentro deste namespace';
$lang['js']['create']          = 'Criar';
$lang['js']['more']            = 'Mais';
$lang['js']['headpage']        = 'Cabeçalho';
$lang['js']['headpagetooltip'] = 'Crie um novo cabeçalho nesta página';
$lang['js']['startpage']       = 'Página de início';
$lang['js']['startpagetooltip'] = 'Crie uma nova página inicial nesta página';
$lang['js']['custompage']      = 'Página personalizada ...';
$lang['js']['custompagetooltip'] = 'Crie uma nova página (insira o nome via popup) nesta página';
$lang['js']['acls']            = 'Acls';
$lang['js']['purgecache']      = 'Limpar cache';
$lang['js']['exporthtml']      = 'Exportar como HTML';
$lang['js']['exporttext']      = 'Exportar como texto';
$lang['js']['headpagehere']    = 'Cabeçalho aqui';
$lang['js']['headpageheretooltip'] = 'Crie um novo cabeçalho dentro deste namespace';
$lang['js']['newpage']         = 'Nova página...';
$lang['js']['newpagetooltip']  = 'Crie uma nova página (insira o nome via popup) dentro deste namespace';
$lang['js']['newpagehere']     = 'Nova página aqui';
$lang['js']['insertkeywords']  = 'Inserir palavras-chave para procurar dentro deste namespace';
$lang['js']['insertpagename']  = 'Insira o nome da página para criar';
$lang['js']['edit']            = 'Editar';
$lang['js']['loading']         = 'Carregando...';
