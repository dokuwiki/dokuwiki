<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Bruno Veilleux <bruno.vey@gmail.com>
 * @author Momo50 <c.brothelande@gmail.com>
 * @author Schplurtz le Déboulonné <Schplurtz@laposte.net>
 */
$lang['account_suffix']        = 'Le suffixe de votre compte. Ex.: <code>@mon.domaine.org</code>';
$lang['base_dn']               = 'Votre nom de domaine de base. <code>DC=mon,DC=domaine,DC=org</code>';
$lang['domain_controllers']    = 'Une liste de contrôleurs de domaine séparés par des virgules. Ex.: <code>srv1.domaine.org,srv2.domaine.org</code>';
$lang['admin_username']        = 'Un utilisateur Active Directory avec accès aux données de tous les autres utilisateurs. Facultatif, mais nécessaire pour certaines actions telles que l\'envoi de courriels d\'abonnement.';
$lang['admin_password']        = 'Le mot de passe de l\'utilisateur ci-dessus.';
$lang['sso']                   = 'Est-ce que la connexion unique (Single-Sign-On) par Kerberos ou NTLM doit être utilisée?';
$lang['sso_charset']           = 'Le jeu de caractères de votre serveur web va passer le nom d\'utilisateur Kerberos ou NTLM. Vide pour UTF-8 ou latin-1. Nécessite l\'extension iconv.';
$lang['real_primarygroup']     = 'Est-ce que le véritable groupe principal doit être résolu au lieu de présumer "Domain Users" (plus lent)?';
$lang['use_ssl']               = 'Utiliser une connexion SSL? Si utilisée, n\'activez pas TLS ci-dessous.';
$lang['use_tls']               = 'Utiliser une connexion TLS? Si utilisée, n\'activez pas SSL ci-dessus.';
$lang['debug']                 = 'Afficher des informations de débogage supplémentaires pour les erreurs?';
$lang['expirywarn']            = 'Jours d\'avance pour l\'avertissement envoyé aux utilisateurs lorsque leur mot de passe va expirer. 0 pour désactiver.';
$lang['additional']            = 'Une liste séparée par des virgules d\'attributs AD supplémentaires à récupérer dans les données utilisateur. Utilisée par certains modules.';
$lang['update_name']           = 'Autoriser les utilisateurs à modifier leur nom affiché de l\'AD ?';
$lang['update_mail']           = 'Autoriser les utilisateurs à modifier leur adresse de courriel ?';
