<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Domingo Redal <docxml@gmail.com>
 * @author Digna González Otero <digna.gonzalezotero [at] gmail [dot] com>
 */
$lang['checkupdate']           = 'Comprobar actualizaciones periódicamente.';
$lang['only_admins']           = 'Denegar sintaxis indexmenu a los no-administradores.<br>Tener en cuenta que una página editada por un usuario no administrador perderá cualquier árbol de indexmenu que contenga.';
$lang['aclcache']              = 'Optimizar la chache de indexmenu para acl (funciona sólo para espacios de nombres pedidos por root).<br>La elección del método afecta sólo a la visualización de los nodos en el árbol de indexmenu, no a las autorizaciones de las páginas.<ul><li>None: Estándar. Es el método más rápido y no crea más ficheros caché, pero los nodos con permiso denegado pueden ser mostrados a usuarios no autorizados o viceversa. Recomendado cuando no se restringe el acceso a páginas mediante acl o no importa cómo se muestre el árbol.<li>User: Por login de usuario. Método más lento y crea muchos ficheros cache, pero siempre oculta correctamente las páginas restringidas. Recomendado cuando se tienen accesos a páginas que dependen del login del usuario.<li>Groups: Por pertenencia a un grupo. Es un buen compromiso entre los métodos anteriores, pero en caso de que se deniegue permiso para ver una página a un usuario que pertenece a un grupo con permiso de lectura, se mostrarán los nodos en el árbol de todos modos. Recomendado cuando los permisos dependen de la pertenencia a grupos.</ul>';
$lang['headpage']              = 'Método de encabezado: página de la cual obtener el título y el link de un espacio de nombres.<br>Puede tener los siguientes valores:<ul><li>La página de inicio global.<li>Una página con el nombre del espacio de nombres y que está dentro del mismo.<li>Una página con el nombre del espacio de nombres y que está al mismo nivel.<li>Una página con un nombre personalizado<li>Una lista de nombres de página separada por una coma.</ul>';
$lang['hide_headpage']         = 'Ocultar encabezados.';
$lang['page_index']            = 'La página que sustituirá el índice principal de dokuwiki. Créalo e insértalo en la sintaxis de indexmenu. Usa id#random si ya tienes una barra lateral de indexmenu con la opción navbar. Mi sugerencia es <code>{{indexmenu>..|js navbar nocookie id#random}}</code>.';
$lang['empty_msg']             = 'Mensaje para mostrar cuando el árbol está vacío. Usa sintaxis de DokuWiki,no código html. La variable <code>{{ns}}</code> es un enlace al espacio de nombres solicitado.';
$lang['skip_index']            = 'Espacios de nombres a ignorar. Usa el formato de las expresiones regulares. Ejemplo: <code>/(sidebars|private:myns)/</code>';
$lang['skip_file']             = 'Archivos a ignorar. Usa el formato de las expresiones regulares. Ejemplo: <code>/(:start$|^public:newstart)/</code>';
$lang['show_sort']             = 'Mostrar a los administradores el número de posición de indexmenu junto a la nota al pie';
$lang['themes_url']            = 'Descargar temas js de esta url http.';
$lang['be_repo']               = 'Permitir a otros descargar temas de tu sitio.';
$lang['defaultoptions']        = 'Lista de opciones de indexmenu separadas por espacios. Estas opciones se aplicarán de forma predeterminada a cada indexmenu y se pueden deshacer con un comando inverso en la sintaxis del complemento';
