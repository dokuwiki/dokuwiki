<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Schplurtz le Déboulonné <schplurtz@laposte.net>
 * @author Andreas Gohr, Michael Große <dokuwiki@cosmocode.de>
 * @author Laynee <seedfloyd@gmail.com>
 * @author lerdt <pro@nicolas-hemard.eu>
 * @author Digitalin <digikatya@yahoo.fr>
 * @author Nicolas Friedli <nicolas@theologique.ch>
 * @author ubibene <services.m@benard.info>
 */
$lang['menu']                  = 'Struct - Éditeur de schémas';
$lang['menu_assignments']      = 'Struct - Affectation de schémas';
$lang['headline']              = 'Données structurées';
$lang['page schema']           = 'Schéma de page';
$lang['lookup schema']         = 'Schéma de consultation';
$lang['edithl page']           = 'Édition du schéma de page <i>%s</i>';
$lang['edithl lookup']         = 'Édition du schéma de consultation <i>%s</i>';
$lang['create']                = 'Créer un nouveau schéma';
$lang['schemaname']            = 'Nom du schéma :';
$lang['save']                  = 'Sauvegarder';
$lang['createhint']            = 'Remarque : les schémas ne peuvent être renommés ultérieurement.';
$lang['pagelabel']             = 'Page';
$lang['rowlabel']              = 'N° de ligne';
$lang['revisionlabel']         = 'Dernière mise à jour';
$lang['userlabel']             = 'Dernier éditeur';
$lang['summary']               = 'Struct : données mises à jour';
$lang['export']                = 'Exporter le schéma au format JSON';
$lang['btn_export']            = 'Exporter';
$lang['import']                = 'Importer un schéma depuis  JSON';
$lang['btn_import']            = 'Importer';
$lang['import_warning']        = 'Attention : tous les champs précédemment remplis seront remplacés !';
$lang['del_confirm']           = 'Entrez le nom du schéma pour confirmer sa suppression';
$lang['del_fail']              = 'Le nom que vous avez entré ne correspond pas à celui du schéma actuel. Il n\'a pas été supprimé.';
$lang['del_ok']                = 'Le schéma a été supprimé.';
$lang['btn_delete']            = 'Supprimer';
$lang['js']['confirmAssignmentsDelete'] = 'Voulez-vous réellement supprimer l\'assignement du schéma "{0}" à la page/catégorie de page "{1}"?';
$lang['js']['lookup_delete']   = 'Supprimer l\'entrée';
$lang['clear_confirm']         = 'Entre le nom du schéma pour confirmer l\'effacement de toutes les données.';
$lang['clear_fail']            = 'Les noms de schéma ne correspondent pas. Données non détruites.';
$lang['clear_ok']              = 'Les données du schéma on été détruites.';
$lang['btn_clear']             = 'effacer';
$lang['tab_edit']              = 'Editer le Schéma';
$lang['tab_export']            = 'Import/Export';
$lang['tab_delete']            = 'Suppression';
$lang['editor_sort']           = 'Tri';
$lang['editor_label']          = 'Nom du champ';
$lang['editor_multi']          = 'Valeurs multiples?';
$lang['editor_conf']           = 'Configuration';
$lang['editor_type']           = 'Type';
$lang['editor_enabled']        = 'Activé';
$lang['editor_editors']        = 'Liste séparée par une virgule d\'utilisateurs et de @groupes qui peuvent modifier les données du schéma (vide pour tous)';
$lang['assign_add']            = 'Ajouter';
$lang['assign_del']            = 'Supprimer';
$lang['assign_assign']         = 'Page / Catégorie';
$lang['assign_tbl']            = 'Schéma';
$lang['multi']                 = 'Vous pouvez entrer plusieurs valeurs séparées par des virgules.';
$lang['multidropdown']         = 'Maintenez les touches CTRL ou CMD pour sélectionner plusieurs valeurs.';
$lang['duplicate_label']       = 'L\'étiquette <code>%s</code> existe déjà dans ce schéma, la deuxième occurrence a été renommée en <code>%s</code>.';
$lang['emptypage']             = 'Les données du greffon Struct ne sont pas sauvegardées si la page est vide.';
$lang['validation_prefix']     = 'Champ [%s] : ';
$lang['Validation Exception Decimal needed'] = 'seuls les décimaux sont autorisés';
$lang['Validation Exception Decimal min'] = 'doit être supérieur ou égal à %d';
$lang['Validation Exception Decimal max'] = 'doit être inférieur ou égal à %d';
$lang['Validation Exception User not found'] = 'doit être un utilisateur existant. L\'utilisateur \'%s\' n\'a pas été trouvé.';
$lang['Validation Exception Media mime type'] = 'Les fichiers de type %s ne sont pas autorisés. Fichiers autorisés : %s';
$lang['Validation Exception Url invalid'] = '%s n\'est pas un URL valide';
$lang['Validation Exception Mail invalid'] = '%s n\'est pas une adresse email valide';
$lang['Validation Exception invalid date format'] = 'doit être au format AAAA-MM-JJ';
$lang['Validation Exception invalid datetime format'] = 'doit être au format AAAA-MM-JJ HH:MM';
$lang['Validation Exception bad color specification'] = 'doit être au format #RRVVBB';
$lang['Exception illegal option'] = 'L\'option \'<code>%s</code>\' est invalide pour ce type d\'agrégation.';
$lang['Exception noschemas']   = 'Schéma non spécifié, impossible de charger des colonnes';
$lang['Exception nocolname']   = 'Aucun nom de colonne spécifié';
$lang['Exception nolookupmix'] = 'Vous ne pouvez pas agréger plus d\'un schéma de consultation ou le mélanger avec des données de page.';
$lang['Exception nolookupassign'] = 'Vous ne pouvez assigner un schéma de consultation à des pages';
$lang['Exception No data saved'] = 'Aucune donnée sauvegardée';
$lang['Exception no sqlite']   = 'Le plugin struct requiert le plugin sqlite. Merci de l\'installer et l\'activer.';
$lang['Exception column not in table'] = 'Il n\'y a pas de colonne %s dans le schéma %s.';
$lang['Warning: no filters for cloud'] = 'Les filtres ne sont pas supportés pour les nuages struct.';
$lang['sort']                  = 'Trier selon cette colonne';
$lang['next']                  = 'Page suivante';
$lang['prev']                  = 'Page précédente';
$lang['none']                  = 'Rien n\'a été trouvé.';
$lang['csvexport']             = 'Export CSV';
$lang['admin_csvexport']       = 'Exporter des données brutes vers un fichier CSV';
$lang['admin_csvimport']       = 'Importer des données brutes depuis un fichier CSV';
$lang['admin_csvdone']         = 'Fichier CSV importé';
$lang['admin_csvhelp']         = 'Veuillez vous référer au manuel sur l\'importation CSV pour les détails du format. (link to french struct doc)';
$lang['tablefilteredby']       = 'Filtre : %s';
$lang['tableresetfilter']      = 'Tout montrer (Supprimer les filtres/le tri)';
$lang['Exception schema missing'] = 'Le schéma %s n\'existe pas !';
$lang['no_lookup_for_page']    = 'Vous ne pouvez pas utiliser l\'éditeur de consultation sur une page de schéma!';
$lang['lookup new entry']      = 'Créer une nouvelle entrée';
$lang['bureaucracy_action_struct_lookup_thanks'] = 'L\'entrée a été stockée. <a href="%s">Ajouter une autre entrée</a>.';
