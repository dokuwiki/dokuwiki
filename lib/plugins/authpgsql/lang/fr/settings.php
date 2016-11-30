<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Bruno Veilleux <bruno.vey@gmail.com>
 */
$lang['server']                = 'Votre serveur PostgreSQL';
$lang['port']                  = 'Le port de votre serveur PostgreSQL';
$lang['user']                  = 'Nom d\'utilisateur PostgreSQL';
$lang['password']              = 'Mot de passe pour l\'utilisateur ci-dessus';
$lang['database']              = 'Base de données à utiliser';
$lang['debug']                 = 'Afficher des informations de débogage supplémentaires';
$lang['forwardClearPass']      = 'Passer les mots de passe aux requêtes SQL ci-dessous en cleartext plutôt qu\'avec l\'option passcrypt';
$lang['checkPass']             = 'Requête SQL pour la vérification des mots de passe';
$lang['getUserInfo']           = 'Requête SQL pour la récupération des informations d\'un utilisateur';
$lang['getGroups']             = 'Requête SQL pour la récupération des groupes d\'un utilisateur';
$lang['getUsers']              = 'Requête SQL pour énumérer tous les utilisateurs';
$lang['FilterLogin']           = 'Clause SQL pour filtrer les utilisateurs par identifiant';
$lang['FilterName']            = 'Clause SQL pour filtrer les utilisateurs par nom complet';
$lang['FilterEmail']           = 'Clause SQL pour filtrer les utilisateurs par adresse électronique';
$lang['FilterGroup']           = 'Clause SQL pour filtrer les utilisateurs par groupes';
$lang['SortOrder']             = 'Clause SQL pour trier les utilisateurs';
$lang['addUser']               = 'Requête SQL pour ajouter un nouvel utilisateur';
$lang['addGroup']              = 'Requête SQL pour ajouter un nouveau groupe';
$lang['addUserGroup']          = 'Requête SQL pour ajouter un utilisateur à un groupe existant';
$lang['delGroup']              = 'Requête SQL pour retirer un groupe';
$lang['getUserID']             = 'Requête SQL pour obtenir la clé primaire d\'un utilisateur';
$lang['delUser']               = 'Requête SQL pour supprimer un utilisateur';
$lang['delUserRefs']           = 'Requête SQL pour retirer un utilisateur de tous les groupes';
$lang['updateUser']            = 'Requête SQL pour mettre à jour le profil d\'un utilisateur';
$lang['UpdateLogin']           = 'Clause de mise à jour pour mettre à jour l\'identifiant d\'un utilisateur';
$lang['UpdatePass']            = 'Clause de mise à jour pour mettre à jour le mot de passe d\'un utilisateur';
$lang['UpdateEmail']           = 'Clause de mise à jour pour mettre à jour l\'adresse électronique d\'un utilisateur';
$lang['UpdateName']            = 'Clause de mise à jour pour mettre à jour le nom complet d\'un utilisateur';
$lang['UpdateTarget']          = 'Clause de limite pour identifier l\'utilisateur durant une mise à jour';
$lang['delUserGroup']          = 'Requête SQL pour retirer un utilisateur d\'un groupe donné';
$lang['getGroupID']            = 'Requête SQL pour obtenir la clé primaire d\'un groupe donné';
