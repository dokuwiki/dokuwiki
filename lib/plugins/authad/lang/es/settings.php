<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author monica <may.dorado@gmail.com>
 * @author Antonio Bueno <atnbueno@gmail.com>
 * @author Juan De La Cruz <juann.dlc@gmail.com>
 * @author Eloy <ej.perezgomez@gmail.com>
 */
$lang['account_suffix']        = 'Su cuenta, sufijo. Ejem. <code> @ my.domain.org </code>';
$lang['base_dn']               = 'Su base DN. Ejem. <code>DC=my,DC=dominio,DC=org</code>';
$lang['domain_controllers']    = 'Una lista separada por coma de los controladores de dominios. Ejem. <code>srv1.dominio.org,srv2.dominio.org</code>';
$lang['admin_username']        = 'Un usuario con privilegios de Active Directory con acceso a los datos de cualquier otro usuario. Opcional, pero es necesario para determinadas acciones como el envío de suscripciones de correos electrónicos.';
$lang['admin_password']        = 'La contraseña del usuario anterior.';
$lang['sso']                   = 'En caso de inicio de sesión usará ¿Kerberos o NTLM?';
$lang['sso_charset']           = 'La codificación con que tu servidor web pasará el nombre de usuario Kerberos o NTLM. Si es UTF-8 o latin-1 dejar en blanco. Requiere la extensión iconv.';
$lang['real_primarygroup']     = 'Resolver el grupo primario real en vez de asumir "Domain Users" (más lento)';
$lang['use_ssl']               = '¿Usar conexión SSL? Si se usa, no habilitar TLS abajo.';
$lang['use_tls']               = '¿Usar conexión TLS? Si se usa, no habilitar SSL arriba.';
$lang['debug']                 = 'Mostrar información adicional de depuración sobre los errores?';
$lang['expirywarn']            = 'Días por adelantado para avisar al usuario de que contraseña expirará. 0 para deshabilitar.';
$lang['additional']            = 'Una lista separada por comas de atributos AD adicionales a obtener de los datos de usuario. Usado por algunos plugins.';
