<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author smocap <smocap@gmail.com>
 * @author Pablo <tuthotep@gmail.com>
 * @author Domingo Redal <docxml@gmail.com>
 * @author Antonio Bueno <atnbueno@gmail.com>
 * @author Antonio Castilla <antoniocastilla@trazoide.com>
 * @author Jonathan Hernández <me@jhalicea.com>
 * @author Álvaro Iradier <airadier@gmail.com>
 * @author Mauricio Segura <maose38@yahoo.es>
 */
$lang['menu']                  = 'Administrador de extensiones ';
$lang['tab_plugins']           = 'Complementos instalados';
$lang['tab_templates']         = 'Plantillas instaladas';
$lang['tab_search']            = 'Buscar e instalar';
$lang['tab_install']           = 'Instalación manual';
$lang['notimplemented']        = 'Esta característica no se ha implementado aún';
$lang['pluginlistsaveerror']   = 'Se ha producido un error al guardar la lista de complementos';
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
$lang['js']['close']           = 'Click para cerrar';
$lang['js']['filter']          = 'Mostrar sólo extensiones actualizables';
$lang['search_for']            = 'Buscar extensión:';
$lang['search']                = 'Buscar';
$lang['extensionby']           = '<strong>%s</strong> por %s';
$lang['screenshot']            = 'Captura de %s';
$lang['popularity']            = 'Popularidad:%s%%';
$lang['homepage_link']         = 'Documentos';
$lang['bugs_features']         = 'Errores';
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
$lang['msg_enabled']           = 'Complemento %s activado';
$lang['msg_disabled']          = 'Complemento %s desactivado';
$lang['msg_delete_success']    = 'Extensión %s desinstalada';
$lang['msg_delete_failed']     = 'La desinstalación de la extensión %s ha fallado';
$lang['msg_install_success']   = 'Extensión %s instalada correctamente';
$lang['msg_update_success']    = 'Extensión %s actualizada correctamente';
$lang['msg_upload_failed']     = 'Falló la carga del archivo: %s';
$lang['msg_nooverwrite']       = 'La extensión %s ya existe, por lo que no se sobrescribe; para sobrescribirla, marque la opción de sobrescritura';
$lang['missing_dependency']    = 'Dependencia faltante o deshabilitada: %s';
$lang['found_conflict']        = 'Esta extensión está marcada como incompatible con las siguientes extensiones instaladas: %s';
$lang['security_issue']        = 'Problema de seguridad: %s';
$lang['security_warning']      = 'Aviso de seguridad: %s';
$lang['update_message']        = 'Mensaje de actualización: %s';
$lang['wrong_folder']          = 'Extensión instalada incorrectamente: Cambie el nombre del directorio de "%s" a "%s".';
$lang['url_change']            = 'URL modificada: El URL de descarga ha cambiado desde la última descarga. Verifica si el nuevo URL es válido antes de actualizar la extensión.
Nuevo: %s
Antiguo: %s';
$lang['error_badurl']          = 'URLs deberían empezar con http o https';
$lang['error_dircreate']       = 'No es posible de crear un directorio temporero para poder recibir el download';
$lang['error_download']        = 'No es posible descargar el documento: %s %s %s';
$lang['error_decompress']      = 'No se pudo descomprimir el fichero descargado. Puede ser a causa de una descarga incorrecta, en cuyo caso puedes intentarlo de nuevo; o puede que el formato de compresión sea desconocido, en cuyo caso necesitarás descargar e instalar manualmente.';
$lang['error_findfolder']      = 'No se ha podido identificar el directorio de la extensión, es necesario descargar e instalar manualmente';
$lang['error_copy']            = 'Hubo un error durante la copia de archivos al intentar instalar los archivos del directorio <em>%s</em>: el disco puede estar lleno o los permisos de acceso a los archivos pueden ser incorrectos. Esto puede haber dado lugar a un complemento instalado parcialmente y dejar su instalación wiki inestable';
$lang['error_copy_read']       = 'No se puede leer el directorio %s';
$lang['error_copy_mkdir']      = 'No se puede crear el directorio %s';
$lang['error_copy_copy']       = 'No se puede copiar %s a %s';
$lang['error_archive_read']    = 'No se pudo abrir el archivo %s para su lectura';
$lang['error_archive_extract'] = 'No se pudo extraer el archivo %s: %s';
$lang['error_uninstall_protected'] = 'La extensión %s está protegida y no puede desinstalarse';
$lang['error_uninstall_dependants'] = 'La extensión %s sigue siendo requerida por %s y, por lo tanto, no puede desinstalarse';
$lang['error_disable_protected'] = 'La extensión %s está protegida y no puede desactivarse';
$lang['error_disable_dependants'] = 'La extensión %s sigue siendo requerida por %s y, por lo tanto, no puede desactivarse';
$lang['error_nourl']           = 'No se encontró la URL de descarga para la extensión %s';
$lang['error_notinstalled']    = 'La extensión %s no está instalada';
$lang['error_alreadyenabled']  = 'La extensión %s ya ha sido activada';
$lang['error_alreadydisabled'] = 'La extensión %s ya ha sido desactivada';
$lang['error_minphp']          = 'La extensión %s requiere al menos PHP %s, pero este wiki está ejecutando PHP %s';
$lang['error_maxphp']          = 'La extensión %s sólo es compatible con PHP hasta la versión %s, pero este wiki está ejecutando PHP %s';
$lang['noperms']               = 'El directorio de extensiones no tiene permiso de escritura.';
$lang['notplperms']            = 'El directorio de plantillas no tiene permiso de escritura.';
$lang['nopluginperms']         = 'No se puede escribir en el directorio de complementos';
$lang['git']                   = 'Esta extensión fue instalada a través de git, quizás usted no quiera actualizarla aquí mismo.';
$lang['auth']                  = 'Este complemento de autenticación no está habilitado en la configuración, considere la posibilidad de desactivarlo.';
$lang['install_url']           = 'Instalar desde URL:';
$lang['install_upload']        = 'Subir Extensión:';
$lang['repo_badresponse']      = 'El repositorio de complementos devolvió una respuesta no válida.';
$lang['repo_error']            = 'El repositorio de complementos no puede ser contactado. Asegúrese que su servidor pueda contactar www.dokuwiki.org y verificar la configuración de su proxy.';
$lang['nossl']                 = 'Tu PHP parece no tener soporte SSL. Las descargas no funcionaran para muchas extensiones de DokuWiki.';
$lang['popularity_high']       = 'Esta es una de las extensiones más populares';
$lang['popularity_medium']     = 'Esta extensión es bastante popular';
$lang['popularity_low']        = 'Esta extensión ha despertado cierto interés';
$lang['details']               = 'Detalles';
