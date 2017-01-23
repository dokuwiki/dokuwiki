<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Guy Brand <gb@unistra.fr>
 * @author Delassaux Julien <julien@delassaux.fr>
 * @author Maurice A. LeBlanc <leblancma@cooptel.qc.ca>
 * @author stephane.gully@gmail.com
 * @author Guillaume Turri <guillaume.turri@gmail.com>
 * @author Erik Pedersen <erik.pedersen@shaw.ca>
 * @author olivier duperray <duperray.olivier@laposte.net>
 * @author Vincent Feltz <psycho@feltzv.fr>
 * @author Philippe Bajoit <philippe.bajoit@gmail.com>
 * @author Florian Gaub <floriang@floriang.net>
 * @author Samuel Dorsaz samuel.dorsaz@novelion.net
 * @author Johan Guilbaud <guilbaud.johan@gmail.com>
 * @author skimpax@gmail.com
 * @author Yannick Aure <yannick.aure@gmail.com>
 * @author Olivier DUVAL <zorky00@gmail.com>
 * @author Anael Mobilia <contrib@anael.eu>
 * @author Bruno Veilleux <bruno.vey@gmail.com>
 * @author Antoine Turmel <geekshadow@gmail.com>
 * @author Jérôme Brandt <jeromebrandt@gmail.com>
 * @author Schplurtz le Déboulonné <Schplurtz@laposte.net>
 * @author Olivier Humbert <trebmuh@tuxfamily.org>
 * @author Eric <ericstevenart@netc.fr>
 */
$lang['menu']                  = 'Gestion des utilisateurs';
$lang['noauth']                = '(authentification de l\'utilisateur non disponible)';
$lang['nosupport']             = '(gestion de l\'utilisateur non supportée)';
$lang['badauth']               = 'mécanisme d\'authentification invalide';
$lang['user_id']               = 'Identifiant ';
$lang['user_pass']             = 'Mot de passe ';
$lang['user_name']             = 'Nom ';
$lang['user_mail']             = 'Courriel ';
$lang['user_groups']           = 'Groupes ';
$lang['field']                 = 'Champ';
$lang['value']                 = 'Valeur';
$lang['add']                   = 'Ajouter';
$lang['delete']                = 'Supprimer';
$lang['delete_selected']       = 'Supprimer la sélection';
$lang['edit']                  = 'Modifier';
$lang['edit_prompt']           = 'Modifier cet utilisateur';
$lang['modify']                = 'Enregistrer les modifications';
$lang['search']                = 'Rechercher';
$lang['search_prompt']         = 'Effectuer la recherche';
$lang['clear']                 = 'Réinitialiser la recherche';
$lang['filter']                = 'Filtre';
$lang['export_all']            = 'Exporter tous les utilisateurs (CSV)';
$lang['export_filtered']       = 'Exporter la liste d\'utilisateurs filtrés (CSV)';
$lang['import']                = 'Importer de nouveaux utilisateurs';
$lang['line']                  = 'Ligne n°';
$lang['error']                 = 'Message d\'erreur';
$lang['summary']               = 'Affichage des utilisateurs %1$d-%2$d parmi %3$d trouvés. %4$d utilisateurs au total.';
$lang['nonefound']             = 'Aucun utilisateur trouvé. %d utilisateurs au total.';
$lang['delete_ok']             = '%d utilisateurs effacés';
$lang['delete_fail']           = '%d effacements échoués.';
$lang['update_ok']             = 'Utilisateur mis à jour avec succès';
$lang['update_fail']           = 'Échec lors de la mise à jour de l\'utilisateur';
$lang['update_exists']         = 'Échec lors du changement du nom d\'utilisateur : le nom spécifié (%s) existe déjà (toutes les autres modifications seront effectuées).';
$lang['start']                 = 'Début';
$lang['prev']                  = 'Précédent';
$lang['next']                  = 'Suivant';
$lang['last']                  = 'Fin';
$lang['edit_usermissing']      = 'Utilisateur sélectionné non trouvé, cet utilisateur a peut-être été supprimé ou modifié ailleurs.';
$lang['user_notify']           = 'Notifier l\'utilisateur ';
$lang['note_notify']           = 'Expédition de notification par courriel uniquement lorsque l\'utilisateur fourni un nouveau mot de passe.';
$lang['note_group']            = 'Les nouveaux utilisateurs seront ajoutés au groupe par défaut (%s) si aucun groupe n\'est spécifié.';
$lang['note_pass']             = 'Le mot de passe sera généré automatiquement si le champ est laissé vide et si la notification de l\'utilisateur est activée.';
$lang['add_ok']                = 'Utilisateur ajouté avec succès';
$lang['add_fail']              = 'Échec de l\'ajout de l\'utilisateur';
$lang['notify_ok']             = 'Courriel de notification expédié';
$lang['notify_fail']           = 'Échec de l\'expédition du courriel de notification';
$lang['import_userlistcsv']    = 'Liste utilisateur (fichier CSV)';
$lang['import_header']         = 'Erreurs d\'import les plus récentes';
$lang['import_success_count']  = 'Import d’utilisateurs : %d utilisateurs trouvés, %d utilisateurs importés avec succès.';
$lang['import_failure_count']  = 'Import d\'utilisateurs : %d ont échoué. Les erreurs sont listées ci-dessous.';
$lang['import_error_fields']   = 'Nombre de champs insuffisant, %d trouvé, 4 requis.';
$lang['import_error_baduserid'] = 'Identifiant de l\'utilisateur manquant';
$lang['import_error_badname']  = 'Mauvais nom';
$lang['import_error_badmail']  = 'Mauvaise adresse e-mail';
$lang['import_error_upload']   = 'L\'import a échoué. Le fichier csv n\'a pas pu être téléchargé ou bien il est vide.';
$lang['import_error_readfail'] = 'L\'import a échoué. Impossible de lire le fichier téléchargé.';
$lang['import_error_create']   = 'Impossible de créer l\'utilisateur';
$lang['import_notify_fail']    = 'Impossible d\'expédier une notification à l\'utilisateur importé %s, adresse %s.';
$lang['import_downloadfailures'] = 'Télécharger les erreurs au format CSV pour correction';
$lang['addUser_error_missing_pass'] = 'Veuillez saisir un mot de passe ou activer la notification à l\'utilisateur pour permettre la génération d\'un mot de passe.';
$lang['addUser_error_pass_not_identical'] = 'Les mots de passe saisis diffèrent.';
$lang['addUser_error_modPass_disabled'] = 'La modification des mots de passe est actuellement désactivée.';
$lang['addUser_error_name_missing'] = 'Veuillez saisir un nom pour le nouvel utilisateur.';
$lang['addUser_error_modName_disabled'] = 'La modification des noms est actuellement désactivée.';
$lang['addUser_error_mail_missing'] = 'Veuillez saisir une adresse de courriel pour le nouvel utilisateur.';
$lang['addUser_error_modMail_disabled'] = 'La modification des adresses de courriel est actuellement désactivée.';
$lang['addUser_error_create_event_failed'] = 'Un greffon a empêché l\'ajout du nouvel utilisateur. Examinez les autres messages potentiels pour obtenir de plus amples informations.';
