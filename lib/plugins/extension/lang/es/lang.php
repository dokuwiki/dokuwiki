<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Domingo Redal <docxml@gmail.com>
 * @author Antonio Bueno <atnbueno@gmail.com>
 * @author Antonio Castilla <antoniocastilla@trazoide.com>
 * @author Jonathan Hernández <me@jhalicea.com>
 * @author Álvaro Iradier <airadier@gmail.com>
 * @author Mauricio Segura <maose38@yahoo.es>
 */
$lang['menu']                  = 'Administrador de Extensiones ';
$lang['tab_plugins']           = 'Plugins instalados';
$lang['tab_templates']         = 'Plantillas instaladas';
$lang['tab_search']            = 'Buscar e instalar';
$lang['tab_install']           = 'Instalación manual';
$lang['notimplemented']        = 'Esta característica no se ha implementado aún';
$lang['notinstalled']          = 'Esta expensión no está instalada';
$lang['alreadyenabled']        = 'Esta extensión ya había sido activada';
$lang['alreadydisabled']       = 'Esta extensión ya había sido desactivada';
$lang['pluginlistsaveerror']   = 'Se ha producido un error al guardar la lista de plugins';
$lang['unknownauthor']         = 'autor desconocido';
$lang['unknownversion']        = 'versión desconocida';
$lang['btn_info']              = 'Mostrar más información';
$lang['btn_update']            = 'Actualizar';
$lang['btn_uninstall']         = 'Desinstalar';
$lang['btn_enable']            = 'Activar';
$lang['btn_disable']           = 'Desactivar';
$lang['btn_install']           = 'Instalar';
$lang['btn_reinstall']         = 'Reinstalar';
$lang['js']['reallydel']       = '¿Realmente quiere desinstalar esta extensión?';
$lang['js']['display_viewoptions'] = 'Ver opciones:';
$lang['js']['display_enabled'] = 'habilitado';
$lang['js']['display_disabled'] = 'deshabilitado';
$lang['js']['display_updatable'] = 'actualizable';
$lang['search_for']            = 'Extensión de búsqueda :';
$lang['search']                = 'Buscar';
$lang['extensionby']           = '<strong>%s</strong> por %s';
$lang['screenshot']            = 'Captura de %s';
$lang['popularity']            = 'Popularidad:%s%%';
$lang['homepage_link']         = 'Documentos';
$lang['bugs_features']         = 'Bugs';
$lang['tags']                  = 'Etiquetas:';
$lang['author_hint']           = 'Buscar extensiones de este autor';
$lang['installed']             = 'Instalado:';
$lang['downloadurl']           = 'URL de descarga:';
$lang['repository']            = 'Repositorio:';
$lang['unknown']               = '<em>desconocido</em>';
$lang['installed_version']     = 'Versión instalada:';
$lang['install_date']          = 'Tú última actualización:';
$lang['available_version']     = 'Versión disponible:';
$lang['compatible']            = 'Compatible con:';
$lang['depends']               = 'Dependencias:';
$lang['similar']               = 'Similar a:';
$lang['conflicts']             = 'Conflictos con:';
$lang['donate']                = '¿Cómo está?';
$lang['donate_action']         = '¡Págale un café al autor!';
$lang['repo_retry']            = 'Trate otra vez';
$lang['provides']              = 'Provee: ';
$lang['status']                = 'Estado:';
$lang['status_installed']      = 'instalado';
$lang['status_not_installed']  = 'no instalado';
$lang['status_protected']      = 'protegido';
$lang['status_enabled']        = 'activado';
$lang['status_disabled']       = 'desactivado';
$lang['status_unmodifiable']   = 'no modificable';
$lang['status_plugin']         = 'plugin';
$lang['status_template']       = 'plantilla';
$lang['status_bundled']        = 'agrupado';
$lang['msg_enabled']           = 'Plugin %s activado';
$lang['msg_disabled']          = 'Plugin %s desactivado';
$lang['msg_delete_success']    = 'Extensión %s desinstalada';
$lang['msg_delete_failed']     = 'La desinstalación de la extensión %s ha fallado';
$lang['msg_template_install_success'] = 'Plantilla %s instalada con éxito';
$lang['msg_template_update_success'] = 'Plantilla %s actualizada con éxito';
$lang['msg_plugin_install_success'] = 'Plugin %s instalado con éxito';
$lang['msg_plugin_update_success'] = 'Plugin %s actualizado con éxito';
$lang['msg_upload_failed']     = 'Falló la carga del archivo';
$lang['msg_nooverwrite']       = 'La extensión %s ya existe, por lo que no se sobrescribe; para sobrescribirla, marque la opción de sobrescritura';
$lang['missing_dependency']    = '<strong>Dependencia deshabilitada o perdida:</strong> %s';
$lang['security_issue']        = '<strong>Problema de seguridad:</strong> %s';
$lang['security_warning']      = '<strong>Aviso de seguridad:</strong> %s';
$lang['update_available']      = '<strong>Actualizar:</strong> Nueva versión %s disponible.';
$lang['wrong_folder']          = '<strong>"Plugin" instalado incorrectamente:</strong> Cambie el nombre del directorio del plugin "%s" a "%s".';
$lang['url_change']            = '<strong>URL actualizada:</strong> El Download URL ha cambiado desde el último download. Verifica si el nuevo URL es valido antes de actualizar la extensión .<br />Nuevo: %s<br />Viejo: %s';
$lang['error_badurl']          = 'URLs deberían empezar con http o https';
$lang['error_dircreate']       = 'No es posible de crear un directorio temporero para poder recibir el download';
$lang['error_download']        = 'No es posible descargar el documento: %s';
$lang['error_decompress']      = 'No se pudo descomprimir el fichero descargado. Puede ser a causa de una descarga incorrecta, en cuyo caso puedes intentarlo de nuevo; o puede que el formato de compresión sea desconocido, en cuyo caso necesitarás descargar e instalar manualmente.';
$lang['error_findfolder']      = 'No se ha podido identificar el directorio de la extensión, es necesario descargar e instalar manualmente';
$lang['error_copy']            = 'Hubo un error durante la copia de archivos al intentar instalar los archivos del directorio <em>%s</em>: el disco puede estar lleno o los permisos de acceso a los archivos pueden ser incorrectos. Esto puede haber dado lugar a un plugin instalado parcialmente y dejar su instalación wiki inestable';
$lang['noperms']               = 'El directorio de extensiones no tiene permiso de escritura.';
$lang['notplperms']            = 'El directorio de plantillas no tiene permiso de escritura.';
$lang['nopluginperms']         = 'No se puede escribir en el directorio de plugins';
$lang['git']                   = 'Esta extensión fue instalada a través de git, quizás usted no quiera actualizarla aquí mismo.';
$lang['auth']                  = 'Este plugin de autenticación no está habilitada en la configuración, considere la posibilidad de desactivarlo.';
$lang['install_url']           = 'Instalar desde URL:';
$lang['install_upload']        = 'Subir Extensión:';
$lang['repo_error']            = 'El repositorio de plugins no puede ser contactado. Asegúrese que su servidor pueda contactar www.dokuwiki.org y verificar la configuración de su proxy.';
$lang['nossl']                 = 'Tu PHP parece no tener soporte SSL. Las descargas no funcionaran para muchas extensiones de DokuWiki.';
