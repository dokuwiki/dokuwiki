<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Hideaki SAWADA <chuno@live.jp>
 */
$lang['menu']                  = 'Struct スキーマ編集';
$lang['menu_assignments']      = 'Struct スキーマ割当て';
$lang['headline']              = '構造化データ';
$lang['page schema']           = 'ページスキーマ：';
$lang['lookup schema']         = '検索スキーマ：';
$lang['edithl page']           = '<i>%s</i> ページスキーマの編集';
$lang['edithl lookup']         = '<i>%s</i> 検索スキーマの編集';
$lang['create']                = '新スキーマの作成';
$lang['schemaname']            = 'スキーマ名：';
$lang['save']                  = '保存';
$lang['createhint']            = '注意：後でスキーマ名を変更できません';
$lang['pagelabel']             = 'ページ';
$lang['rowlabel']              = '行番号';
$lang['revisionlabel']         = '最終更新';
$lang['userlabel']             = '最終更新者';
$lang['summary']               = 'Struct データは変更されました';
$lang['export']                = 'JSON としてスキーマ出力';
$lang['btn_export']            = '出力';
$lang['import']                = 'JSON からのスキーマ入力';
$lang['btn_import']            = '入力';
$lang['import_warning']        = '警告：これは定義済みのフィールドを上書きします！';
$lang['del_confirm']           = '削除を確認するために、スキーマ名を入力してください';
$lang['del_fail']              = 'スキーマは削除されました';
$lang['del_ok']                = 'スキーマは削除されました';
$lang['btn_delete']            = '削除';
$lang['js']['confirmAssignmentsDelete'] = '"{0}" スキーマの "{1}" ページ・名前空間への割当てを本当に削除しますか？';
$lang['js']['lookup_delete']   = '登録の削除';
$lang['tab_edit']              = 'スキーマ編集';
$lang['tab_export']            = '入力・出力';
$lang['tab_delete']            = '削除';
$lang['editor_sort']           = 'ソート';
$lang['editor_label']          = 'フィールド名';
$lang['editor_multi']          = '複数入力？';
$lang['editor_conf']           = '設定';
$lang['editor_type']           = '型';
$lang['editor_enabled']        = '有効';
$lang['editor_editors']        = 'このスキーマデータを編集可能なユーザー・＠グループのカンマ区切り一覧（空の場合は全員）：';
$lang['assign_add']            = '追加';
$lang['assign_del']            = '削除';
$lang['assign_assign']         = 'ページ・名前空間';
$lang['assign_tbl']            = 'スキーマ';
$lang['multi']                 = '複数の値をコンマ区切りで入力してください。';
$lang['multidropdown']         = '複数の値を選択するために CTRL キーか CMD キーを押したままにして下さい。';
$lang['duplicate_label']       = '<code>%s</code> ラベルはスキーマ内に存在します。二回目の方を <code>%s</code> に変更しました。';
$lang['emptypage']             = '空ページの Struct データは保存されていません。';
$lang['validation_prefix']     = '[%s]フィールド：';
$lang['Validation Exception Decimal needed'] = '小数のみ有効';
$lang['Validation Exception Decimal min'] = '%d 以上';
$lang['Validation Exception Decimal max'] = '%d 以下';
$lang['Validation Exception User not found'] = '既存のユーザーのみを許可します。\'%s\' は存在しません。';
$lang['Validation Exception Url invalid'] = '%s は有効な URL ではありません。';
$lang['Validation Exception Mail invalid'] = '%s は有効なメールアドレスではありません。';
$lang['Validation Exception invalid date format'] = 'YYYY-MM-DD 形式ではありません。';
$lang['Validation Exception invalid datetime format'] = 'YYYY-MM-DD HH:MM:SS 形式';
$lang['Validation Exception bad color specification'] = '#RRGGBB 形式';
$lang['Exception nocolname']   = '指定された列名はありません';
$lang['Exception nolookupmix'] = '複数の検索を集約したり、ページデータと混ぜたりはできません';
$lang['Exception nolookupassign'] = '検索スキーマはページに割当てできません';
$lang['Exception No data saved'] = '保存されるデータはありません';
$lang['Exception no sqlite']   = 'Struct プラグインには Sqlite プラグインが必要です。インストールして有効にしてください。';
$lang['sort']                  = 'この項目でソート';
$lang['next']                  = '次ページ';
$lang['prev']                  = '前ページ';
$lang['none']                  = '何もありません。';
$lang['csvexport']             = 'CSV 出力';
$lang['tablefilteredby']       = '%s で抽出';
$lang['tableresetfilter']      = '全て表示（抽出・ソートを解除）';
$lang['Exception schema missing'] = '%s スキーマは存在しません！';
$lang['lookup new entry']      = '新規登録の作成';
