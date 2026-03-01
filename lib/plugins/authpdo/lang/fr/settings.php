<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Schplurtz le Déboulonné <schplurtz@laposte.net>
 */
$lang['debug']                 = 'Afficher des messages d\'erreur détaillés. Devrait être désactivé passé la configuration.';
$lang['dsn']                   = 'Le DSN de connexion à la base de données.';
$lang['user']                  = 'L\'utilisateur pour la connexion à la base de donnée ci-dessus (vide pour sqlite)';
$lang['pass']                  = 'Le mot de passe pour la connexion à la base de donnée ci-dessus (vide pour sqlite)';
$lang['select-user']           = 'Instruction SQL pour sélectionner les données d\'un seul utilisateur';
$lang['select-user-groups']    = 'Instruction SQL pour sélectionner tous les groupes d\'un utilisateur donné';
$lang['select-groups']         = 'Instruction SQL pour sélectionner tous les groupes disponibles';
$lang['insert-user']           = 'Instruction SQL pour insérer un nouvel utilisateur dans la base de données';
$lang['delete-user']           = 'Instruction SQL pour retirer un utilisateur de la base de données';
$lang['list-users']            = 'Instruction SQL pour lister les utilisateurs correspondant à un filtre';
$lang['count-users']           = 'Instruction SQL pour compter les utilisateurs correspondant à un filtre';
$lang['update-user-info']      = 'Instruction SQL pour mettre à jour le nom complet et l\'adresse de courriel d\'un utilisateur donné';
$lang['update-user-login']     = 'Instruction SQL pour mettre à jour l\'identifiant d\'un utilisateur donné';
$lang['update-user-pass']      = 'Instruction SQL pour mettre à jour le mot de passe d\'un utilisateur donné';
$lang['insert-group']          = 'Instruction SQL pour mettre insérer un nouveau groupe dans la base de données';
$lang['join-group']            = 'Instruction SQL pour ajouter un utilisateur à un groupe existant';
$lang['leave-group']           = 'Instruction SQL pour retirer un utilisateur d\'un groupe';
$lang['check-pass']            = 'Instruction SQL pour vérifier le mot de passe d\'un utilisateur. Peut être laissé vide si l\'information de mot de passe est obtenue lors de la sélection d\'un utilisateur.';
