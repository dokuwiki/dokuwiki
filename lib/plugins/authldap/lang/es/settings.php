<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author smocap <smocap@gmail.com>
 * @author Domingo Redal <docxml@gmail.com>
 * @author Antonio Bueno <atnbueno@gmail.com>
 * @author Eloy <ej.perezgomez@gmail.com>
 * @author Alejandro Nunez <nunez.alejandro@gmail.com>
 * @author Enny Rodriguez <aquilez.4@gmail.com>
 */
$lang['server']                = 'Tu servidor LDAP. Puede ser el nombre del host  (<code>localhost</code>) o una URL completa (<code>ldap://server.tld:389</code>)';
$lang['port']                  = 'Servidor LDAP en caso de que no se diera la URL completa anteriormente.';
$lang['usertree']              = 'Donde encontrar cuentas de usuario. Ej. <code>ou=People, dc=server, dc=tld</code>';
$lang['grouptree']             = 'Donde encontrar grupos de usuarios. Ej. <code>ou=Group, dc=server, dc=tld</code>';
$lang['userfilter']            = 'Filtro LDAP para la busqueda de cuentas de usuario. P. E. <code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>';
$lang['groupfilter']           = 'Filtro LDAP para la busqueda de grupos. P. E. <code>(&amp;(objectClass=posixGroup)(|(gidNumber=%{gid})(memberUID=%{user})))</code>';
$lang['version']               = 'La versión del protocolo a usar. Puede que necesites poner esto a <code>3</code>';
$lang['starttls']              = 'Usar conexiones TLS?';
$lang['referrals']             = '¿Deben ser seguidas las referencias?';
$lang['deref']                 = '¿Cómo desreferenciar los alias?';
$lang['binddn']                = 'DN de un usuario de enlace opcional si el enlace anónimo no es suficiente. P. ej. <code>cn=admin, dc=my, dc=home</code>';
$lang['bindpw']                = 'Contraseña del usuario de arriba.';
$lang['attributes']            = 'Atributos a recuperar de la búsqueda en LDAP.';
$lang['userscope']             = 'Limitar ámbito de búsqueda para búsqueda de usuarios';
$lang['groupscope']            = 'Limitar ámbito de búsqueda para búsqueda de grupos';
$lang['userkey']               = 'Atributo que denota el nombre de usuario; debe ser coherente con el filtro.';
$lang['groupkey']              = 'Pertenencia al grupo desde cualquier atributo de usuario (en lugar de grupos AD estándar) p.e., grupo a partir departamento o número de teléfono';
$lang['modPass']               = '¿Puede ser cambiada la contraseña LDAP vía dokuwiki?';
$lang['modPassPlain']          = '¿Enviar actualizaciones de contraseña en texto plano al servidor LDAP (en lugar de aplicar un identificador aleatorio y generar su hash con el algoritmo configurado antes de la transmisión)?';
$lang['debug']                 = 'Mostrar información adicional para depuración de errores';
$lang['deref_o_0']             = 'LDAP_DEREF_NEVER';
$lang['deref_o_1']             = 'LDAP_DEREF_SEARCHING';
$lang['deref_o_2']             = 'LDAP_DEREF_FINDING';
$lang['deref_o_3']             = 'LDAP_DEREF_ALWAYS';
$lang['referrals_o_-1']        = 'usar default';
$lang['referrals_o_0']         = 'no seguir referencias';
$lang['referrals_o_1']         = 'seguir referencias';
