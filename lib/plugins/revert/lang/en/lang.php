<?php
/**
 * english language file
 */

// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu'] = 'Revert Manager';

// custom language strings for the plugin

$lang['filter']   = 'Search spammy pages';
$lang['revert']   = 'Revert selected pages';
$lang['reverted'] = '%s reverted to revision %s';
$lang['removed']  = '%s removed';
$lang['revstart'] = 'Reversion process started. This can take a long time. If the
                     script times out before finishing, you need to revert in smaller
                     chunks.';
$lang['revstop']  = 'Reversion process finished successfully.';
$lang['note1']    = 'Note: this search is case sensitive';
$lang['note2']    = 'Note: the page will be reverted to the last version not containing the given spam term <i>%s</i>.';

//Setup VIM: ex: et ts=4 :
