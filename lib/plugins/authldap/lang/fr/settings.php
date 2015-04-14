<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Bruno Veilleux <bruno.vey@gmail.com>
 * @author schplurtz <Schplurtz@laposte.net>
 * @author Schplurtz le Déboulonné <schplurtz@laposte.net>
 */
$lang['server']                = 'Votre serveur LDAP. Soit le nom d\'hôte (<code>localhost</code>) ou l\'URL complète (<code>ldap://serveur.dom:389</code>)';
$lang['port']                  = 'Port du serveur LDAP si l\'URL complète n\'a pas été indiquée ci-dessus';
$lang['usertree']              = 'Où trouver les comptes utilisateur. Ex.: <code>ou=Utilisateurs, dc=serveur, dc=dom</code>';
$lang['grouptree']             = 'Où trouver les groupes d\'utilisateurs. Ex.: <code>ou=Groupes, dc=serveur, dc=dom</code>';
$lang['userfilter']            = 'Filtre LDAP pour rechercher les comptes utilisateur. Ex.: <code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>';
$lang['groupfilter']           = 'Filtre LDAP pour rechercher les groupes. Ex.: <code>(&amp;(objectClass=posixGroup)(|(gidNumber=%{gid})(memberUID=%{user})))</code>';
$lang['version']               = 'La version de protocole à utiliser. Il se peut que vous deviez utiliser <code>3</code>';
$lang['starttls']              = 'Utiliser les connexions TLS?';
$lang['referrals']             = 'Suivre les références?';
$lang['deref']                 = 'Comment déréférencer les alias ?';
$lang['binddn']                = 'Nom de domaine d\'un utilisateur de connexion facultatif si une connexion anonyme n\'est pas suffisante. Ex. : <code>cn=admin, dc=mon, dc=accueil</code>';
$lang['bindpw']                = 'Mot de passe de l\'utilisateur ci-dessus.';
$lang['userscope']             = 'Limiter la portée de recherche d\'utilisateurs';
$lang['groupscope']            = 'Limiter la portée de recherche de groupes';
$lang['userkey']               = 'Attribut indiquant le nom d\'utilisateur. Doit être en accord avec le filtre d\'utilisateur.';
$lang['groupkey']              = 'Affiliation aux groupes à partir de n\'importe quel attribut utilisateur (au lieu des groupes AD standards), p. ex. groupes par département ou numéro de téléphone';
$lang['modPass']               = 'Peut-on changer le mot de passe LDAP depuis DokiWiki ?';
$lang['debug']                 = 'Afficher des informations de bégogage supplémentaires pour les erreurs';
$lang['deref_o_0']             = 'LDAP_DEREF_NEVER';
$lang['deref_o_1']             = 'LDAP_DEREF_SEARCHING';
$lang['deref_o_2']             = 'LDAP_DEREF_FINDING';
$lang['deref_o_3']             = 'LDAP_DEREF_ALWAYS';
$lang['referrals_o_-1']        = 'comportement par défaut';
$lang['referrals_o_0']         = 'ne pas suivre les références';
$lang['referrals_o_1']         = 'suivre les références';
