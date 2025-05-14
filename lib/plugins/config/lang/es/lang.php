<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author cadetill <cadetill@gmail.com>
 * @author Luna Frax <lunafrax@gmail.com>
 * @author Domingo Redal <docxml@gmail.com>
 * @author Miguel Pagano <miguel.pagano@gmail.com>
 * @author Oscar M. Lage <r0sk10@gmail.com>
 * @author Gabriel Castillo <gch@pumas.ii.unam.mx>
 * @author oliver <oliver@samera.com.py>
 * @author Enrico Nicoletto <liverig@gmail.com>
 * @author Manuel Meco <manuel.meco@gmail.com>
 * @author VictorCastelan <victorcastelan@gmail.com>
 * @author Jordan Mero <hack.jord@gmail.com>
 * @author Felipe Martinez <metalmartinez@gmail.com>
 * @author Javier Aranda <internet@javierav.com>
 * @author Zerial <fernando@zerial.org>
 * @author Marvin Ortega <maty1206@maryanlinux.com>
 * @author Daniel Castro Alvarado <dancas2@gmail.com>
 * @author Fernando J. Gómez <fjgomez@gmail.com>
 * @author Mauro Javier Giamberardino <mgiamberardino@gmail.com>
 * @author emezeta <emezeta@infoprimo.com>
 * @author Oscar Ciudad <oscar@jacho.net>
 * @author Ruben Figols <ruben.figols@gmail.com>
 * @author Gerardo Zamudio <gerardo@gerardozamudio.net>
 * @author Mercè López <mercelz@gmail.com>
 */
$lang['menu']                  = 'Parámetros de configuración';
$lang['error']                 = 'Los parámetros no han sido actualizados a causa de un valor inválido, por favor revise los cambios y re-envíe el formulario. <br /> Los valores incorrectos se mostrarán con un marco rojo alrededor.';
$lang['updated']               = 'Los parámetros se actualizaron con éxito.';
$lang['nochoice']              = '(no hay otras alternativas disponibles)';
$lang['locked']                = 'El archivo de configuración no ha podido ser actualizado, si esto no es lo deseado, <br /> asegúrese que el nombre del archivo local de configuraciones y los permisos sean los correctos.';
$lang['danger']                = 'Atención: Cambiar esta opción podría hacer inaccesible el wiki y su menú de configuración.';
$lang['warning']               = 'Advertencia:  Cambiar esta opción podría causar comportamientos no deseados.';
$lang['security']              = 'Advertencia de Seguridad: Cambiar esta opción podría representar un riesgo de seguridad.';
$lang['_configuration_manager'] = 'Administrador de configuración';
$lang['_header_dokuwiki']      = 'Parámetros de DokuWiki';
$lang['_header_plugin']        = 'Parámetros de Plugin';
$lang['_header_template']      = 'Parámetros de Plantillas';
$lang['_header_undefined']     = 'Parámetros sin categoría';
$lang['_basic']                = 'Parámetros Básicos';
$lang['_display']              = 'Parámetros de Presentación';
$lang['_authentication']       = 'Parámetros de Autenticación';
$lang['_anti_spam']            = 'Parámetros Anti-Spam';
$lang['_editing']              = 'Parámetros de Edición';
$lang['_links']                = 'Parámetros de Enlaces';
$lang['_media']                = 'Parámetros de Medios';
$lang['_notifications']        = 'Configuración de notificaciones';
$lang['_syndication']          = 'Configuración de sindicación';
$lang['_advanced']             = 'Parámetros Avanzados';
$lang['_network']              = 'Parámetros de Red';
$lang['_msg_setting_undefined'] = 'Sin parámetros de metadata.';
$lang['_msg_setting_no_class'] = 'Sin clase establecida.';
$lang['_msg_setting_no_known_class'] = 'Configuración de la clase no disponible.';
$lang['_msg_setting_no_default'] = 'Sin valor por defecto.';
$lang['title']                 = 'Título del wiki';
$lang['start']                 = 'Nombre de la página inicial';
$lang['lang']                  = 'Idioma';
$lang['template']              = 'Plantilla';
$lang['tagline']               = 'Lema (si la plantilla lo soporta)';
$lang['sidebar']               = 'Nombre de la barra lateral (si la plantilla lo soporta), un campo vacío la desactiva';
$lang['license']               = '¿Bajo qué licencia será liberado tu contenido?';
$lang['savedir']               = 'Directorio para guardar los datos';
$lang['basedir']               = 'Directorio de base';
$lang['baseurl']               = 'URL de base';
$lang['cookiedir']             = 'Ruta para las Cookie. Dejar en blanco para usar la ruta básica.';
$lang['dmode']                 = 'Modo de creación de directorios';
$lang['fmode']                 = 'Modo de creación de ficheros';
$lang['allowdebug']            = 'Permitir debug <b>deshabilítelo si no lo necesita!</b>';
$lang['recent']                = 'Cambios recientes';
$lang['recent_days']           = 'Cuántos cambios recientes mantener (días)';
$lang['breadcrumbs']           = 'Número de pasos de traza';
$lang['youarehere']            = 'Traza jerárquica';
$lang['fullpath']              = 'Mostrar ruta completa en el pie de página';
$lang['typography']            = 'Realizar reemplazos tipográficos';
$lang['dformat']               = 'Formato de fecha (ver la función de PHP <a href="http://php.net/strftime">strftime</a>)';
$lang['signature']             = 'Firma';
$lang['showuseras']            = 'Qué ver al mostrar el último usuario que editó una página';
$lang['toptoclevel']           = 'Nivel superior para la tabla de contenidos';
$lang['tocminheads']           = 'La cantidad mínima de titulares que determina si el TOC es construido';
$lang['maxtoclevel']           = 'Máximo nivel para la tabla de contenidos';
$lang['maxseclevel']           = 'Máximo nivel para edición de sección';
$lang['camelcase']             = 'Usar CamelCase para enlaces';
$lang['deaccent']              = 'Nombres de páginas "limpios"';
$lang['useheading']            = 'Usar el primer encabezado para nombres de páginas';
$lang['sneaky_index']          = 'Por defecto, DokuWiki mostrará todos los namespaces en el index. Habilitando esta opción los ocultará si el usuario no tiene permisos de lectura. Los sub-namespaces pueden resultar inaccesibles. El index puede hacerse poco usable dependiendo de las configuraciones ACL.';
$lang['hidepages']             = 'Ocultar páginas con coincidencias (expresiones regulares)';
$lang['useacl']                = 'Usar listas de control de acceso (ACL)';
$lang['autopasswd']            = 'Autogenerar contraseñas';
$lang['authtype']              = 'Método de Autenticación';
$lang['passcrypt']             = 'Método de cifrado de contraseñas';
$lang['defaultgroup']          = 'Grupo por defecto';
$lang['superuser']             = 'Super-usuario - grupo ó usuario con acceso total a todas las páginas y funciones, configuraciones ACL';
$lang['manager']               = 'Manager - grupo o usuario con acceso a ciertas tareas de mantenimiento';
$lang['profileconfirm']        = 'Confirmar cambios en perfil con contraseña';
$lang['rememberme']            = 'Permitir cookies para acceso permanente (recordarme)';
$lang['disableactions']        = 'Deshabilitar acciones DokuWiki';
$lang['disableactions_check']  = 'Controlar';
$lang['disableactions_subscription'] = 'Suscribirse/Cancelar suscripción';
$lang['disableactions_wikicode'] = 'Ver la fuente/Exportar en formato raw';
$lang['disableactions_profile_delete'] = 'Borrar tu propia cuenta';
$lang['disableactions_other']  = 'Otras acciones (separadas por coma)';
$lang['disableactions_rss']    = 'Sindicación XML (RSS)';
$lang['auth_security_timeout'] = 'Tiempo de Autenticación (en segundos), por motivos de seguridad';
$lang['securecookie']          = 'Las cookies establecidas por HTTPS, ¿el naveagdor solo puede enviarlas por HTTPS? Inhabilite esta opción cuando solo se asegure con SSL la entrada, pero no la navegación de su wiki.';
$lang['remote']                = 'Activar el sistema API remoto. Esto permite a otras aplicaciones acceder al wiki a traves de XML-RPC u otros mecanismos.';
$lang['remoteuser']            = 'Restringir el acceso remoto por API a los grupos o usuarios separados por comas que se dan aquí. Dejar en blanco para dar acceso a todo el mundo.';
$lang['remotecors']            = 'Habilitar el Uso Compartido de Recursos entre Orígenes (CORS) para las interfaces remotas. Asterisco (*) para permitir todos los orígenes. Dejar vacío para denegar CORS.';
$lang['usewordblock']          = 'Bloquear spam usando una lista de palabras';
$lang['relnofollow']           = 'Usar rel="nofollow" en enlaces externos';
$lang['indexdelay']            = 'Intervalo de tiempo antes de indexar (segundos)';
$lang['mailguard']             = 'Ofuscar direcciones de correo electrónico';
$lang['iexssprotect']          = 'Comprobar posible código malicioso (JavaScript ó HTML) en archivos subidos';
$lang['usedraft']              = 'Guardar automáticamente un borrador mientras se edita';
$lang['locktime']              = 'Edad máxima para archivos de bloqueo (segundos)';
$lang['cachetime']             = 'Edad máxima para caché (segundos)';
$lang['target____wiki']        = 'Ventana para enlaces internos';
$lang['target____interwiki']   = 'Ventana para enlaces interwikis';
$lang['target____extern']      = 'Ventana para enlaces externos';
$lang['target____media']       = 'Ventana para enlaces a medios';
$lang['target____windows']     = 'Ventana para enlaces a ventanas';
$lang['mediarevisions']        = '¿Habilitar Mediarevisions?';
$lang['refcheck']              = 'Control de referencia a medios';
$lang['gdlib']                 = 'Versión de GD Lib';
$lang['im_convert']            = 'Ruta a la herramienta de conversión de ImageMagick';
$lang['jpg_quality']           = 'Calidad de compresión de JPG (0-100)';
$lang['fetchsize']             = 'Tamaño máximo (bytes) que fetch.php puede descargar de sitios externos';
$lang['subscribers']           = 'Habilitar soporte para suscripción a páginas';
$lang['subscribe_time']        = 'Tiempo después que alguna lista de suscripción fue enviada (seg); Debe ser menor que el tiempo especificado en días recientes.';
$lang['notify']                = 'Enviar notificación de cambios a esta dirección de correo electrónico';
$lang['registernotify']        = 'Enviar información cuando se registran nuevos usuarios a esta dirección de correo electrónico';
$lang['mailfrom']              = 'Dirección de correo electrónico para emails automáticos';
$lang['mailreturnpath']        = 'Dirección de correo electrónico del destinatario para las notificaciones de no entrega';
$lang['mailprefix']            = 'Asunto por defecto que se utilizará en mails automáticos.';
$lang['htmlmail']              = 'Enviar correos electronicos en HTML con mejor aspecto pero mayor peso. Desactivar para enviar correos electronicos en texto plano.';
$lang['dontlog']               = 'Deshabilitar inicio de sesión para este tipo de registros.';
$lang['sitemap']               = 'Generar sitemap de Google (días)';
$lang['rss_type']              = 'Tipo de resumen (feed) XML';
$lang['rss_linkto']            = 'Feed XML enlaza a';
$lang['rss_content']           = '¿Qué mostrar en los items del archivo XML?';
$lang['rss_update']            = 'Intervalo de actualización de feed XML (segundos)';
$lang['rss_show_summary']      = 'Feed XML muestra el resumen en el título';
$lang['rss_show_deleted']      = 'Fuente XML Mostrar fuentes eliminadas';
$lang['rss_media']             = '¿Qué tipo de cambios deberían aparecer en el feed XML?';
$lang['rss_media_o_both']      = 'ambos';
$lang['rss_media_o_pages']     = 'páginas';
$lang['rss_media_o_media']     = 'multimedia';
$lang['updatecheck']           = '¿Comprobar actualizaciones y advertencias de seguridad? Esta característica requiere que DokuWiki se conecte a update.dokuwiki.org.';
$lang['userewrite']            = 'Usar URLs bonitas';
$lang['useslash']              = 'Usar barra (/) como separador de espacios de nombres en las URLs';
$lang['sepchar']               = 'Separador de palabras en nombres de páginas';
$lang['canonical']             = 'Usar URLs totalmente canónicas';
$lang['fnencode']              = 'Método para codificar nombres de archivo no-ASCII.';
$lang['autoplural']            = 'Controlar plurales en enlaces';
$lang['compression']           = 'Método de compresión para archivos en el ático';
$lang['gzip_output']           = 'Usar gzip Content-Encoding para xhtml';
$lang['compress']              = 'Compactar la salida de CSS y javascript';
$lang['cssdatauri']            = 'Tamaño en bytes hasta el cual las imágenes referenciadas en archivos CSS deberían ir incrustadas en la hoja de estilos para reducir el número de cabeceras de petición HTTP. ¡Esta técnica no funcionará en IE < 8! De <code>400</code> a <code>600</code> bytes es un valor adecuado. Establezca <code>0</code> para deshabilitarlo.';
$lang['send404']               = 'Enviar "HTTP 404/Page Not Found" para páginas no existentes';
$lang['broken_iua']            = '¿Se ha roto (broken) la función ignore_user_abort en su sistema? Esto puede causar que no funcione el index de búsqueda. Se sabe que IIS+PHP/CGI está roto. Vea <a href="http://bugs.splitbrain.org/?do=details&amp;task_id=852">Bug 852</a>para más información.';
$lang['xsendfile']             = '¿Utilizar la cabecera X-Sendfile para permitirle al servidor web enviar archivos estáticos? Su servidor web necesita tener la capacidad para hacerlo.';
$lang['renderer_xhtml']        = 'Visualizador a usar para salida (xhtml) principal del wiki';
$lang['renderer__core']        = '%s (núcleo dokuwiki)';
$lang['renderer__plugin']      = '%s (plugin)';
$lang['search_nslimit']        = 'Limite la búsqueda a los actuales X espacios de nombres. Cuando se ejecuta una búsqueda desde una página dentro de un espacio de nombres más profundo, los primeros X espacios de nombres se agregarán como filtro';
$lang['search_fragment']       = 'Especifique el comportamiento predeterminado de la búsqueda de fragmentos';
$lang['search_fragment_o_exact'] = 'exacto';
$lang['search_fragment_o_starts_with'] = 'comienza con';
$lang['search_fragment_o_ends_with'] = 'termina con';
$lang['search_fragment_o_contains'] = 'contiene';
$lang['_feature_flags']        = 'Configuración de características';
$lang['defer_js']              = 'Aplazar JavaScript para que se ejecute después de que se haya analizado el HTML de la página. Mejora la velocidad percibida de la página, pero podría romper un pequeño número de complementos.';
$lang['hidewarnings']          = 'No mostrar ninguna advertencia emitida por PHP. Esto puede facilitar la transición a PHP8+. Las advertencias seguirán siendo registradas en el registro de errores y deben ser reportadas.';
$lang['dnslookups']            = 'DokuWiki buscara los hostnames para usuarios editando las páginas con IP remota. Si usted tiene un servidor DNS bastante lento o que no funcione, favor de desactivar esta opción.';
$lang['jquerycdn']             = '¿Deberían cargarse los ficheros de script jQuery y jQuery UI desde un CDN? Esto añade peticiones HTTP adicionales, pero los ficheros se pueden cargar más rápido y los usuarios pueden tenerlas ya almacenadas en caché.';
$lang['jquerycdn_o_0']         = 'No CDN, sólo entrega local';
$lang['jquerycdn_o_jquery']    = 'CDN en code.jquery.com';
$lang['jquerycdn_o_cdnjs']     = 'CDN en cdnjs.com';
$lang['proxy____host']         = 'Nombre del servidor Proxy';
$lang['proxy____port']         = 'Puerto del servidor Proxy';
$lang['proxy____user']         = 'Nombre de usuario para el servidor Proxy';
$lang['proxy____pass']         = 'Contraseña para el servidor Proxy';
$lang['proxy____ssl']          = 'Usar ssl para conectarse al servidor Proxy';
$lang['proxy____except']       = 'Expresiones regulares para encontrar URLs que el proxy debería omitir.';
$lang['license_o_']            = 'No se eligió ninguna';
$lang['typography_o_0']        = 'ninguno';
$lang['typography_o_1']        = 'Dobles comillas solamente';
$lang['typography_o_2']        = 'Todas las comillas (puede ser que no siempre funcione)';
$lang['userewrite_o_0']        = 'ninguno';
$lang['userewrite_o_1']        = '.htaccess';
$lang['userewrite_o_2']        = 'Interno de DokuWiki';
$lang['deaccent_o_0']          = 'apagado';
$lang['deaccent_o_1']          = 'eliminar tildes';
$lang['deaccent_o_2']          = 'romanizar';
$lang['gdlib_o_0']             = 'GD Lib no está disponible';
$lang['gdlib_o_1']             = 'Versión 1.x';
$lang['gdlib_o_2']             = 'Autodetección';
$lang['rss_type_o_rss']        = 'RSS 0.91';
$lang['rss_type_o_rss1']       = 'RSS 1.0';
$lang['rss_type_o_rss2']       = 'RSS 2.0';
$lang['rss_type_o_atom']       = 'Atom 0.3';
$lang['rss_type_o_atom1']      = 'Atom 1.0';
$lang['rss_content_o_abstract'] = 'Resumen';
$lang['rss_content_o_diff']    = 'Diferencias unificadas';
$lang['rss_content_o_htmldiff'] = 'Tabla de diferencias en formato HTML';
$lang['rss_content_o_html']    = 'Página que solo contiene código HTML';
$lang['rss_linkto_o_diff']     = 'ver las diferencias';
$lang['rss_linkto_o_page']     = 'la página revisada';
$lang['rss_linkto_o_rev']      = 'lista de revisiones';
$lang['rss_linkto_o_current']  = 'la página actual';
$lang['compression_o_0']       = 'ninguna';
$lang['compression_o_gz']      = 'gzip';
$lang['compression_o_bz2']     = 'bz2';
$lang['xsendfile_o_0']         = 'no utilizar';
$lang['xsendfile_o_1']         = 'Encabezado propietario de lighttpd (antes de la versión 1.5)';
$lang['xsendfile_o_2']         = 'Encabezado X-Sendfile estándar';
$lang['xsendfile_o_3']         = 'Encabezado propietario Nginx X-Accel-Redirect';
$lang['showuseras_o_loginname'] = 'Nombre de entrada';
$lang['showuseras_o_username'] = 'Nombre completo del usuario';
$lang['showuseras_o_username_link'] = 'Nombre completo del usuario como enlace de usuario interwiki';
$lang['showuseras_o_email']    = 'Dirección de correo electrónico del usuario (ofuscada según la configuración de "mailguard")';
$lang['showuseras_o_email_link'] = 'Dirección de correo de usuario como enlace de envío de correo';
$lang['useheading_o_0']        = 'Nunca';
$lang['useheading_o_navigation'] = 'Solamente Navegación';
$lang['useheading_o_content']  = 'Contenido wiki solamente';
$lang['useheading_o_1']        = 'Siempre';
$lang['readdircache']          = 'Tiempo máximo para la cache readdir (en segundos)';
