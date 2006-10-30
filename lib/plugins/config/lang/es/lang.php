<?php
/**
 * spanish language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Miguel Pagano <miguel.pagano@gmail.com>
 */

// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu']       = 'Parámetros de Configuración';

$lang['error']      = 'Los parámetros no han sido actualizados a causa de un valor inválido, por favor
		    revise los cambios y re-envíe el formulario. <br /> Los valores incorrectos se
		    mostrarán con un marco rojo alrededor.';
$lang['updated']    = 'Los parámetros se actualizaron exitosamente.';
$lang['nochoice']   = '(no hay otras alternativas disponibles)';
$lang['locked']     = 'El archivo de configuración no ha podido ser actualizado, si esto 
		    no es lo deseado, <br /> asegúrese que el nombre del archivo local
		    de configuraciones y los permisos sean los correctos.';

/* --- Config Setting Headers --- */
$lang['_configuration_manager'] = 'Administrador de configuración'; //same as heading in intro.txt
$lang['_header_dokuwiki'] = 'Parámetros de DokuWiki';
$lang['_header_plugin'] = 'Parámetros de Plugin';
$lang['_header_template'] = 'Parámetros de Plantillas';
$lang['_header_undefined'] = 'Parámetros sin categoría';

/* --- Config Setting Groups --- */
$lang['_basic'] = 'Parámetros Básicos';
$lang['_display'] = 'Parámetros de Presentación';
$lang['_authentication'] = 'Parámetros de Autenticación';
$lang['_anti_spam'] = 'Parámetros Anti-Spam';
$lang['_editing'] = 'Parámetros de Edición';
$lang['_links'] = 'Parámetros de Enlaces';
$lang['_media'] = 'Parámetros de Almacenamiento';
$lang['_advanced'] = 'Parámetros Avanzados';
$lang['_network'] = 'Parámetros de Red';
// The settings group name for plugins and templates can be set with
// plugin_settings_name and template_settings_name respectively. If one
// of these lang properties is not set, the group name will be generated
// from the plugin or template name and the localized suffix.
$lang['_plugin_sufix'] = 'Parámetros de Plugins';
$lang['_template_sufix'] = 'Parámetros de Plantillas';

/* --- Undefined Setting Messages --- */
$lang['_msg_setting_undefined'] = 'Sin parámetros de metadata.';
$lang['_msg_setting_no_class'] = 'No setting class.';
$lang['_msg_setting_no_default'] = 'No default value.';

/* -------------------- Config Options --------------------------- */

$lang['fmode']       = 'Modo de creación de ficheros';
$lang['dmode']       = 'Modo de creación de directorios';
$lang['lang']        = 'Idioma';
$lang['basedir']     = 'Directorio de Base';
$lang['baseurl']     = 'URL de Base';
$lang['savedir']     = 'Directorio para guardar los datos';
$lang['start']       = 'Nombre de la página inicial';
$lang['title']       = 'Título del Wiki';
$lang['template']    = 'Plantilla';
$lang['fullpath']    = 'Reveal full path of pages in the footer';
$lang['recent']      = 'Cambios recientes';
$lang['breadcrumbs'] = 'Número de pasos de traza';
$lang['youarehere']  = 'Traza jerárquica';
$lang['typography']  = 'Realizar reemplazos tipográficos';
$lang['htmlok']      = 'Permitir HTML embebido';
$lang['phpok']       = 'Permitir PHP embebido';
$lang['dformat']     = 'Formato de fecha (ver la función de PHP <a href="http://www.php.net/date">date</a>)';
$lang['signature']   = 'Firma';
$lang['toptoclevel'] = 'Nivel superior para la tabla de contenidos';
$lang['maxtoclevel'] = 'Máximo nivel para la tabla de contenidos';
$lang['maxseclevel'] = 'Máximo nivel para edición de sección';
$lang['camelcase']   = 'Usar CamelCase para enlaces';
$lang['deaccent']    = 'Nombres de páginas "limpios"';
$lang['useheading']  = 'Usar el primer encabezado para nombres de páginas';
$lang['refcheck']    = 'Control de referencia a medios';
$lang['refshow']     = 'Número de referencias a medios a mostrar';
$lang['allowdebug']  = 'Permitir debug <b>deshabilítelo si no lo necesita!</b>';

$lang['usewordblock']= 'Bloquear spam usando una lista de palabras';
$lang['indexdelay']  = 'Intervalo de tiempo antes de indexar (segundos)';
$lang['relnofollow'] = 'Usar rel="nofollow" en enlaces externos';
$lang['mailguard']   = 'Ofuscar direcciones de correo electrónico';

/* Authentication Options */
$lang['useacl']      = 'Usar listas de control de acceso (ACL)';
$lang['autopasswd']  = 'Autogenerar contraseñas';
$lang['authtype']    = 'Método de Autenticación';
$lang['passcrypt']   = 'Método de cifrado de contraseñas';
$lang['defaultgroup']= 'Grupo por defecto';
$lang['superuser']   = 'Super-usuario';
$lang['profileconfirm'] = 'Confirmar cambios en perfil con contraseña';
$lang['disableactions'] = 'Deshabilitar acciones DokuWiki';
$lang['disableactions_check'] = 'Controlar';
$lang['disableactions_subscription'] = 'Subscribirse/Desubscribirse';
$lang['disableactions_wikicode'] = 'Ver el fuente/Exportar en formato crudo (raw)';
$lang['disableactions_other'] = 'Otras acciones (separadas por coma)';

/* Advanced Options */
$lang['updatecheck'] = 'Comprobar actualizaciones y advertencias de seguridad? Esta característica requiere que Dokuwiki se conecte a splitbrain.org.
Check for updates and security warnings? DokuWiki needs to contact splitbrain.org for this feature.';
$lang['userewrite']  = 'Usar URLs bonitas';
$lang['useslash']    = 'Usar barra (/) como separador de espacios de nombres en las URLs';
$lang['usedraft']    = 'Guardar automáticamente un borrador mientras se edita';
$lang['sepchar']     = 'Separador de palabras en nombres de páginas';
$lang['canonical']   = 'Usar URLs totalmente canónicas';
$lang['compression'] = 'Método de compresión para archivos en el ático';
$lang['autoplural']  = 'Controlar plurales en enlaces';
$lang['usegzip']     = 'Usar gzip para archivos del ático';
$lang['cachetime']   = 'Edad máxima para caché (segundos)';
$lang['locktime']    = 'Edad máxima para archivos de lock (segundos)';
$lang['fetchsize']   = 'Tamañao máximo (bytes) que fetch.php puede descargar de sitios externos';
$lang['notify']      = 'Enviar notificación de cambios a esta dirección de correo electrónico';
$lang['registernotify'] = 'Enviar información cuando se registran nuevos usuarios a esta dirección de correo electrónico';
$lang['mailfrom']    = 'Dirección de correo electrónico para emails automáticos';
$lang['gzip_output'] = 'Usar gzip Content-Encoding para xhtml';
$lang['gdlib']       = 'Versión de GD Lib';
$lang['im_convert']  = 'Ruta a la herramienta de conversión de ImageMagick';
$lang['jpg_quality'] = 'Calidad de compresión de JPG (0-100)';
$lang['spellchecker']= 'Habilitar corrector ortográfico';
$lang['subscribers'] = 'Habilitar soporte para subscripción a páginas';
$lang['compress']    = 'Compactar la salida de CSS y javascript';
$lang['hidepages']   = 'Ocultar páginas con coincidencias (expresiones regulares)';
$lang['send404']     = 'Enviar "HTTP 404/Page Not Found" para páginas no existentes';
$lang['sitemap']     = 'Generar sitemap de Google (días)';

$lang['rss_type']    = 'Tipo de resumen (feed) XML';
$lang['rss_linkto']  = 'Resumen XML enlaza a';
$lang['rss_update']  = 'Intervalo de actualización de resumen XML (segundos)';
$lang['recent_days'] = 'Cuántos cambios recientes mantener (días)';

/* Target options */
$lang['target____wiki']      = 'Ventana para enlaces internos';
$lang['target____interwiki'] = 'Ventana para enlaces interwikis';
$lang['target____extern']    = 'Ventana para enlaces externos';
$lang['target____media']     = 'Ventana para enlaces a medios';
$lang['target____windows']   = 'Ventana para enlaces a ventanas';

/* Proxy Options */
$lang['proxy____host'] = 'Nombre del servidor Proxy';
$lang['proxy____port'] = 'Puerto del servidor Proxy';
$lang['proxy____user'] = 'Nombre de usuario para el servidor Proxy';
$lang['proxy____pass'] = 'Contraseña para el servidor Proxy';
$lang['proxy____ssl']  = 'Usar ssl para conectarse al servidor Proxy';

/* Safemode Hack */
$lang['safemodehack'] = 'Habilitar edición (hack) de modo seguro';
$lang['ftp____host'] = 'Nombre del servidor FTP  para modo seguro';
$lang['ftp____port'] = 'Puerto del servidor FTP  para modo seguro';
$lang['ftp____user'] = 'Nombre de usuario para el servidor FTP  para modo seguro';
$lang['ftp____pass'] = 'Contraseña para el servidor FTP  para modo seguro';
$lang['ftp____root'] = 'Directorio raiz para el servidor FTP  para modo seguro';

/* userewrite options */
$lang['userewrite_o_0'] = 'ninguno';
$lang['userewrite_o_1'] = '.htaccess';
$lang['userewrite_o_2'] = 'Interno de DokuWiki';

/* deaccent options */
$lang['deaccent_o_0'] = 'Apagado';
$lang['deaccent_o_1'] = 'Eliminar tildes';
$lang['deaccent_o_2'] = 'Romanizar';

/* gdlib options */
$lang['gdlib_o_0'] = 'GD Lib no está disponible';
$lang['gdlib_o_1'] = 'Versión 1.x';
$lang['gdlib_o_2'] = 'Autodetección';

/* rss_type options */
$lang['rss_type_o_rss']  = 'RSS 0.91';
$lang['rss_type_o_rss1'] = 'RSS 1.0';
$lang['rss_type_o_rss2'] = 'RSS 2.0';
$lang['rss_type_o_atom'] = 'Atom 0.3';

/* rss_linkto options */
$lang['rss_linkto_o_diff']    = 'ver las diferencias';
$lang['rss_linkto_o_page']    = 'la página revisada';
$lang['rss_linkto_o_rev']     = 'lista de revisiones';
$lang['rss_linkto_o_current'] = 'la página actual';

/* compression options */
$lang['compression_o_0']   = 'Ninguna';
$lang['compression_o_gz']  = 'gzip';
$lang['compression_o_bz2'] = 'bz2';
