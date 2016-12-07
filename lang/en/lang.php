<?php
/**
 * English language file for struct plugin
 *
 * @author Andreas Gohr, Michael Große <dokuwiki@cosmocode.de>
 */


$lang['menu'] = 'Struct Schema Editor';
$lang['menu_assignments'] = 'Struct Schema Assignments';

$lang['headline'] = 'Structured Data';

$lang['page schema'] = 'Page Schema:';
$lang['lookup schema'] = 'Lookup Schema:';
$lang['edithl page'] = 'Editing Page Schema <i>%s</i>';
$lang['edithl lookup'] = 'Editing Lookup Schema <i>%s</i>';
$lang['create'] = 'Create new Schema';
$lang['schemaname'] = 'Schema Name:';
$lang['save'] = 'Save';
$lang['createhint'] = 'Please note: schemas can not be renamed later';
$lang['pagelabel'] = 'Page';
$lang['rowlabel'] = 'Row #';
$lang['revisionlabel'] = 'Last Updated';
$lang['userlabel'] = 'Last Editor';
$lang['summary'] = 'Struct data changed';
$lang['export'] = 'Export Schema as JSON';
$lang['btn_export'] = 'Export';
$lang['import'] = 'Import a Schema from JSON';
$lang['btn_import'] = 'Import';
$lang['import_warning'] = 'Warning: this will overwrite already defined fields!';

$lang['del_confirm'] = 'Enter schema name to confirm deletion';
$lang['del_fail'] = 'Schema names did not match. Schema not deleted';
$lang['del_ok'] = 'Schema has been deleted';
$lang['btn_delete'] = 'Delete';
$lang['js']['confirmAssignmentsDelete'] = 'Do you really want to delete the assignment of schema "{0}" to page/namespace "{1}"?';

$lang['tab_edit'] = 'Edit Schema';
$lang['tab_export'] = 'Import/Export';
$lang['tab_delete'] = 'Delete';

$lang['editor_sort'] = 'Sort';
$lang['editor_label'] = 'Field Name';
$lang['editor_multi'] = 'Multi-Input?';
$lang['editor_conf'] = 'Configuration';
$lang['editor_type'] = 'Type';
$lang['editor_enabled'] = 'Enabled';
$lang['editor_editors'] = 'Comma separated list of users and @groups who may edit this schema\'s data (empty for all):';

$lang['assign_add'] = 'Add';
$lang['assign_del'] = 'Delete';
$lang['assign_assign'] = 'Page/Namespace';
$lang['assign_tbl'] = 'Schema';

$lang['multi'] = 'Enter multiple values separated by commas.';
$lang['multidropdown'] = 'Hold CTRL or CMD to select multiple values.';
$lang['duplicate_label'] = "Label <code>%s</code> already exists in schema, second occurance was renamed to <code>%s</code>.";

$lang['emptypage'] = 'Struct data has not been saved for an empty page';

$lang['validation_prefix'] = "Field [%s]: ";

$lang['Validation Exception Decimal needed'] = 'only decimals are allowed';
$lang['Validation Exception Decimal min'] = 'has to be equal or greater than %d';
$lang['Validation Exception Decimal max'] = 'has to be equal or less than %d';
$lang['Validation Exception User not found'] = 'has to be an existing user. User \'%s\' was not found.';
$lang['Validation Exception Media mime type'] = 'MIME type %s has to match the allowed set of %s';
$lang['Validation Exception Url invalid'] = '%s is not a valid URL';
$lang['Validation Exception Mail invalid'] = '%s is not a valid email address';
$lang['Validation Exception invalid date format'] = 'must be of format YYYY-MM-DD';
$lang['Validation Exception invalid datetime format'] = 'must be of format YYYY-MM-DD HH:MM:SS';
$lang['Validation Exception bad color specification'] = 'must be of format #RRGGBB';

$lang['Exception noschemas'] = 'There have been no schemas given to load columns from';
$lang['Exception nocolname'] = 'No column name given';
$lang['Exception nolookupmix'] = 'You can not aggregate more than one Lookup or mix it with Page data';
$lang['Exception nolookupassign'] = 'You can not assign Lookup schemas to pages';
$lang['Exception No data saved'] = 'No data saved';
$lang['Exception no sqlite'] = 'The struct plugin requires the sqlite plugin. Please install and enable it.';

$lang['sort']      = 'Sort by this column';
$lang['next']      = 'Next page';
$lang['prev']      = 'Previous page';

$lang['none']      = 'Nothing found';
$lang['csvexport'] = 'CSV Export';

$lang['admin_csvexport'] = 'Export raw data to a CSV file';
$lang['admin_csvimport'] = 'Import raw data from a CSV file';
$lang['admin_csvdone'] = 'CSV file imported';
$lang['admin_csvhelp'] = 'Please refer to the manual on CSV Import for format details.';

$lang['tablefilteredby'] = 'Filtered by %s';
$lang['tableresetfilter'] = 'Show all (remove filter/sort)';

$lang['Exception schema missing'] = "Schema %s does not exist!";

$lang['no_lookup_for_page'] = 'You can\'t use the Lookup Editor on a page schema!';
$lang['lookup new entry'] = 'Create new Entry';
$lang['js']['lookup_delete'] = 'Delete Entry';

//Setup VIM: ex: et ts=4 :
