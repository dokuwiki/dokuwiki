<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Eduardo Mozart de Oliveira <eduardomozart182@gmail.com>
 * @author Alexandre Belchior <alexbelchior@gmail.com>
 */
$lang['checkupdate']           = 'Verifique periodicamente por atualizações.';
$lang['only_admins']           = 'Permitir a sintaxe de indexmenu apenas para administradores. <br> Note que uma página editada por um usuário não administrador perderá todas as árvores de indexmenu contidas.';
$lang['aclcache']              = 'Otimize o cache do indexmenu para acl (funciona somente para namespaces solicitados por raiz). <br> A escolha do método afeta apenas a visualização de nós na árvore de indexmenu, não as autorizações de página. <Ul> <li> Nenhum: Padrão. É o método mais rápido e não cria mais arquivos de cache, mas os nós com permissão negada podem ser mostrados para usuários não autorizados ou vice-versa. Recomendado quando você não nega o acesso às páginas por acl ou não se importa como a árvore é exibida. <Li> Usuário: Login por usuário. Método mais lento e cria muitos arquivos de cache, mas sempre oculta as páginas corretamente negadas. Recomendado quando você tem acls de página que dependem do login de usuários. <Li> Grupos: associação por grupo. Bom compromisso entre os métodos anteriores, mas no caso de você negar a acl de leitura a um usuário que pertença a um grupo com uma autenticação acl de leitura, ele poderá exibir esses nós na árvore. Recomendado quando o acls do seu site inteiro depende da associação de grupos. </ Ul>';
$lang['headpage']              = 'Método de cabeçalho: a página a partir da qual retrata o título e o link de um namespace. <br> Pode ser qualquer um desses valores: <ul> <li> A página inicial global. <Li> Uma página com o nome do namespace e que está dentro it. <li> Uma página com o nome do namespace e que está no mesmo nível. <li> Uma página de nome personalizada. <li> Uma lista separada por vírgulas de nomes de páginas. </ ul>';
$lang['hide_headpage']         = 'Esconder cabeçalhos.';
$lang['page_index']            = 'A página que irá substituir o índice dokuwiki principal. Crie-o e insira a sintaxe indexmenu. Use <code> id # random </code> se você já tem uma barra lateral indexmenu com a opção navbar. Minha sugestão é <code> {{indexmenu> .. | js navbar nocookie id # random}} </code>.';
$lang['empty_msg']             = 'Mensagem para mostrar quando a árvore está vazia. Use a sintaxe do Dokuwiki, não o código html. A variável <code> {{ns}} </code> é um atalho para o namespace solicitado.';
$lang['skip_index']            = 'Id de namespaces para pular. Use o formato Expressão Regular. Exemplo: <code> / (sidebars | private: myns) / </code>';
$lang['skip_file']             = 'Ide de páginas para pular. Use o formato Expressão Regular. Exemplo: <code> / (sidebars | private: myns) / </code>';
$lang['show_sort']             = 'Mostrar aos administradores o número de classificação do indexmenu como parte superior da nota da página';
$lang['themes_url']            = 'Baixe temas js desta url http.';
$lang['be_repo']               = 'Permitir que outras pessoas baixem temas do seu site.';
$lang['defaultoptions']        = 'Lista de opções indexmenu separadas por espaços. Estas opções serão aplicadas por padrão a cada indexmenu e podem ser desfeitas com um comando reverso na sintaxe do plugin';
