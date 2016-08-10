<?php
/**
 * French language file for struct plugin
 *
 * @author Andreas Gohr, Michael Große <dokuwiki@cosmocode.de>
 * @author Laynee <seedfloyd@gmail.com>
 */


$lang['menu'] = 'Struct - Éditeur de schémas';
$lang['menu_assignments'] = 'Struct - Assignement de schémas';

$lang['headline'] = 'Données structurées';

$lang['edithl'] = 'Édition du schéma <i>%s</i>';
$lang['create'] = 'Créer un nouveau schéma';
$lang['schemaname'] = 'Nom du schéma :';
$lang['save'] = 'Sauvegarder';
$lang['createhint'] = 'Remarque : les shémas ne peuvent être renommés ultérieurement.';
$lang['pagelabel'] = 'Page';
$lang['summary'] = 'Struct : données mises à jour';
$lang['export'] = 'Exporter le schéma dans un fichier JSON';
$lang['btn_export'] = 'Exporter';
$lang['import'] = 'Importer un schéma depuis un fichier JSON';
$lang['btn_import'] = 'Importer';
$lang['import_warning'] = 'Attention : tous les champs précédement remplis seront remplacés !';

$lang['del_confirm'] = 'Entrez le nom du schéma pour confirmer sa suppression';
$lang['del_fail'] = 'Le nom que vous avez entré ne correspond pas à celui du schéma actuel. Il n\'a pas été supprimé.';
$lang['del_ok'] = 'Le schéma a été supprimé.';
$lang['btn_delete'] = 'Supprimer';

$lang['tab_edit'] = 'Édition';
$lang['tab_export'] = 'Import/Export';
$lang['tab_delete'] = 'Suppression';

$lang['editor_sort'] = 'Ordre de tri';
$lang['editor_label'] = 'Nom du champ';
$lang['editor_multi'] = 'Valeurs multiples';
$lang['editor_conf'] = 'Configuration';
$lang['editor_type'] = 'Type';
$lang['editor_enabled'] = 'Activer';

$lang['assign_add'] = 'Ajouter';
$lang['assign_del'] = 'Supprimer';
$lang['assign_assign'] = 'Page / Namespace';
$lang['assign_tbl'] = 'Schéma';

$lang['multi'] = 'Vous pouvez entrer plusieurs valeurs, séparées par des virgules.';
$lang['multidropdown'] = 'Maintenez les touches CTRL ou CMD pour sélectionner plusieurs valeurs.';
$lang['duplicate_label'] = "Le label <code>%s</code> existe déjà dans ce schéma, la deuxième occurence a été renommée en <code>%s</code>.";

$lang['emptypage'] = 'Les données du plugin Struct ne sont pas sauvegardées si la page est vide.';

$lang['validation_prefix'] = "Champ [%s] : ";

$lang['Validation Exception Decimal min'] = 'doit être supérieur ou égal à %d';
$lang['Validation Exception Decimal max'] = 'doit être inférieur ou égal à %d';
$lang['Validation Exception User not found'] = 'doit être un utilisateur existant. L\'utilisateur \'%s\' n\'a pas été trouvé.';
$lang['Validation Exception Media mime type'] = 'Les fichiers de type %s ne sont pas autorisés. Fichiers autorisés : %s';
$lang['Validation Exception Url invalid'] = '%s n\'est pas une URL valide';
$lang['Validation Exception Mail invalid'] = '%s n\'est pas une adresse email valide';
$lang['Validation Exception invalid date format'] = 'doit être au format AAAA-MM-JJ';

$lang['Exception noschemas'] = 'Schéma non spécifié, impossible de charger des colonnes';
$lang['Exception nocolname'] = 'Nom de colonne non spécifié';

$lang['sort']      = 'Trier selon cette colonne';
$lang['next']      = 'Page suivante';
$lang['prev']      = 'Page précédente';

$lang['none']      = 'Rien n\'a été trouvé.';

$lang['tablefilteredby'] = 'Filtre : %s';
$lang['tableresetfilter'] = 'Supprimer les filtres';

$lang['Exception schema missing'] = "Le schéma %s n\'existe pas !";

//Setup VIM: ex: et ts=4 :
