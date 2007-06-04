<?php
/**
 * japanese language file
 */

// settings must be present and set appropriately for the language
$lang['encoding']   = 'utf-8';
$lang['direction']  = 'ltr';

// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu'] = '復元管理';

// custom language strings for the plugin

$lang['filter']   = 'スパムを受けたページを検索';
$lang['revert']   = '選択したページを検索';
$lang['reverted'] = '%s はリビジョン %s へ復元されました';
$lang['removed']  = '%s は削除されました';
$lang['revstart'] = '復元処理中です。時間が掛かる可能性がありますが、もしタイムアウトした場合は、復元を複数回に分けて行ってください。';


$lang['revstop']  = '復元処理が正しく完了しました。';
$lang['note1']    = '注意：検索語句は大文字・小文字を区別します';
$lang['note2']    = '注意：最新の内容に検索したスパムキーワード <i>%s</i> が含まれていないページが復元されます。';

//Setup VIM: ex: et ts=4 enc=utf-8 :
