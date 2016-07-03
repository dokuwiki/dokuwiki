<?php
/**
 * French language file for struct plugin
 *
 * @author Andreas Gohr, Michael Große <dokuwiki@cosmocode.de>
 * @author Laynee <seedfloyd@gmail.com>
 */


$lang['menu'] = 'Struct - Editeur de schémas';
$lang['menu_assignments'] = 'Struct - Assignement de schemas';

$lang['headline'] = 'Données structurées';

$lang['edithl'] = 'Edition du schéma <i>%s</i>';
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
$lang['del_fail'] = 'Le nom que vous avez entré ne correspond pas au schéma actuel. Il n\'a pas été supprimé.';
$lang['del_ok'] = 'Le schéma a été supprimé.';
$lang['btn_delete'] = 'Supprimer';

$lang['tab_edit'] = 'Edition';
$lang['tab_export'] = 'Import/Export';
$lang['tab_delete'] = 'Suppression';

$lang['editor_sort'] = 'Ordre de tri';
$lang['editor_label'] = 'Nom du champ';
$lang['editor_multi'] = 'Valeurs multiples ?';
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

$lang['emptypage'] = 'Struct data has not been saved for an empty page';

$lang['validation_prefix'] = "Champ [%s] : ";

$lang['Validation Exception Integer needed'] = 'only integers are allowed';
$lang['Validation Exception Integer min'] = 'has to be equal or greater than %d';
$lang['Validation Exception Integer max'] = 'has to be equal or less than %d';
$lang['Validation Exception User not found'] = 'has to be an existing user. User \'%s\' was not found.';
$lang['Validation Exception Media mime type'] = 'MIME type %s has to match the allowed set of %s';
$lang['Validation Exception Url invalid'] = '%s is not a valid URL';
$lang['Validation Exception Mail invalid'] = '%s is not a valid email address';
$lang['Validation Exception invalid date format'] = 'must be of format YYYY-MM-DD';

$lang['Exception noschemas'] = 'There have been no schemas given to load columns from';
$lang['Exception nocolname'] = 'No column name given';

$lang['sort']      = 'Trier selon cette colonne';
$lang['next']      = 'Page suivante';
$lang['prev']      = 'Page précédente';

$lang['none']      = 'Rien n\'a été trouvé.';

$lang['tablefilteredby'] = 'Filtre : %s';
$lang['tableresetfilter'] = 'Aucun filtre / Afficher tout';

$lang['Exception schema missing'] = "Le schéma %s n\'existe pas !";

//Setup VIM: ex: et ts=4 :
