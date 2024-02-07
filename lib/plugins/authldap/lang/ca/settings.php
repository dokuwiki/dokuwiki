<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Marc Zulet <marczulet@gmail.com>
 * @author David Surroca <davidsurrocaestrada@gmail.com>
 * @author Adolfo Jayme Barrientos <fito@libreoffice.org>
 * @author Àngel Pérez Beroy <aperezberoy@gmail.com>
 * @author Aniol Marti <amarti@caliu.cat>
 */
$lang['server']                = 'El vostre servidor LDAP. Nom d\'amfitrió (<code>localhost</code>) o URL qualificat complet (<code>ldap://server.tld:389</code>)';
$lang['port']                  = 'Port del servidor LDAP si no s\'ha donat cap URL complet més amunt';
$lang['usertree']              = 'On trobar els comptes d\'usuari. Per exemple. <code>ou=People, dc=server, dc=tld</code>';
$lang['grouptree']             = 'On trobar els grups d\'usuaris. Per exemple. <code>ou=Grup, dc=servidor, dc=tld</code>';
$lang['userfilter']            = 'Filtre LDAP per cercar comptes d\'usuari. Per exemple. <code>(&(uid=%{user})(objectClass=posixAccount))</code>';
$lang['groupfilter']           = 'Filtre LDAP per cercar grups. Per exemple. <code>(&(objectClass=posixGroup)(|(gidNumber=%{gid})(memberUID=%{user})))</code>';
$lang['version']               = 'La versió del protocol a utilitzar. Pot ser que hagueu d’establir-ho a <code>3</code>';
$lang['starttls']              = 'Utilitzar connexions TLS?';
$lang['referrals']             = 'S\'han de seguir les referències?';
$lang['deref']                 = 'Com desreferenciar els àlies?';
$lang['binddn']                = 'DN d\'un usuari opcional enllaçat si l\'enllaç anònim no és suficient. Per exemple. <code>cn=admin, dc=my, dc=home</code>';
$lang['bindpw']                = 'Contrasenya de l\'usuari referit abans.';
$lang['attributes']            = 'Atributs a demanar a la consulta LDAP.';
$lang['userscope']             = 'Limiteu l\'abast de cerca per a la cerca d\'usuaris';
$lang['groupscope']            = 'Limiteu l\'abast de cerca per a la cerca de grups';
$lang['userkey']               = 'Atribut que denota el nom d\'usuari; ha de ser coherent amb el filtre d\'usuari.';
$lang['groupkey']              = 'Pertinença al grup des de qualsevol atribut d\'usuari (en lloc de grups AD estàndard), p. ex. grup del departament o número de telèfon';
$lang['modPass']               = 'Es pot canviar la contrasenya del LDAP mitjançant el Dokuwiki?';
$lang['debug']                 = 'Mostra informació addicional de depuració als errors.';
$lang['deref_o_0']             = 'LDAP_DEREF_NEVER';
$lang['deref_o_1']             = 'LDAP_DEREF_SEARCHING';
$lang['deref_o_2']             = 'LDAP_DEREF_FINDING';
$lang['deref_o_3']             = 'LDAP_DEREF_ALWAYS';
$lang['referrals_o_-1']        = 'ús per defecte';
$lang['referrals_o_0']         = 'no seguir les referències';
$lang['referrals_o_1']         = 'seguir les referències';
