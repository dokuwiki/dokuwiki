<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author pau <pau@estuditic.com>
 * @author Marc Zulet <marczulet@gmail.com>
 * @author Joan <aseques@gmail.com>
 * @author David Surroca <davidsurrocaestrada@gmail.com>
 * @author Adolfo Jayme Barrientos <fito@libreoffice.org>
 * @author controlonline.net <controlonline.net@gmail.com>
 * @author Àngel Pérez Beroy <aperezberoy@gmail.com>
 */
$lang['account_suffix']        = 'El teu nom de compte. Ej.<code>@my.domain.org</code>';
$lang['base_dn']               = 'Nom base DN. Ej. <code>DC=my,DC=domain,DC=org</code>';
$lang['domain_controllers']    = 'Llista separada per coma dels controladors de domini. Ex. <code>srv1.domain.org,srv2.domain.org</code>';
$lang['admin_username']        = 'Un usuari de Directori Actiu autoritzat a accedir a les dades de tots els usuaris. Opcional, però necessari per a certes accions, com enviar correus per subscripció.';
$lang['admin_password']        = 'La contrasenya de l\'usuari referit abans.
';
$lang['sso']                   = 'S\'hauria de fer servir Kerberos o NTLM per inici de sessió únic?';
$lang['sso_charset']           = 'El conjunt de caràcters del vostre servidor web passarà el nom d\'usuari Kerberos o NTLM. Buit per a UTF-8 o latin-1. Requereix l\'extensió iconv.';
$lang['real_primarygroup']     = 'S\'hauria de resoldre el grup principal real en lloc d\'assumir "Usuaris de domini" (més lent).';
$lang['use_ssl']               = 'Utilitzeu la connexió SSL? Si s\'usa, no activeu TLS a continuació.';
$lang['use_tls']               = 'Utilitzeu la connexió TLS? Si l\'useu, no activeu SSL a dalt.';
$lang['debug']                 = 'Mostrar informació addicional de depuració en cas d\'error?';
$lang['expirywarn']            = 'Dies per endavant en avisar l\'usuari sobre la caducitat de la contrasenya. 0 per desactivar.';
$lang['additional']            = 'Una llista separada per comes d\'atributs AD addicionals per obtenir de les dades d\'usuari. Utilitzat per alguns connectors.';
$lang['update_name']           = 'Voleu permetre als usuaris actualitzar el seu nom de visualització d\'AD?';
$lang['update_mail']           = 'Permetre els usuaris actualitzar la seva adreça de correu electrònic?';
$lang['update_pass']           = 'Permetre als usuaris actualitzar la seva clau? Es necessari SSL o TLS.';
$lang['recursive_groups']      = 'Resoleu els grups imbricats als seus respectius membres (més lentament).';
