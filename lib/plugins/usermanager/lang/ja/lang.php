<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Yuji Takenaka <webmaster@davilin.com>
 * @author Chris Smith <chris@jalakai.co.uk>
 * @author Ikuo Obataya <i.obataya@gmail.com>
 * @author Daniel Dupriest <kououken@gmail.com>
 * @author Kazutaka Miyasaka <kazmiya@gmail.com>
 * @author Taisuke Shimamoto <dentostar@gmail.com>
 * @author Satoshi Sahara <sahara.satoshi@gmail.com>
 * @author Hideaki SAWADA <sawadakun@live.jp>
 * @author Hideaki SAWADA <chuno@live.jp>
 */
$lang['menu']                  = 'ユーザー管理';
$lang['noauth']                = '（ユーザー認証が無効です）';
$lang['nosupport']             = '（ユーザー管理はサポートされていません）';
$lang['badauth']               = '認証のメカニズムが無効です';
$lang['user_id']               = 'ユーザー';
$lang['user_pass']             = 'パスワード';
$lang['user_name']             = 'フルネーム';
$lang['user_mail']             = 'メールアドレス';
$lang['user_groups']           = 'グループ';
$lang['field']                 = '項目';
$lang['value']                 = '値';
$lang['add']                   = '追加';
$lang['delete']                = '削除';
$lang['delete_selected']       = '選択したユーザーを削除';
$lang['edit']                  = '編集';
$lang['edit_prompt']           = 'このユーザーを編集';
$lang['modify']                = '変更を保存';
$lang['search']                = '検索';
$lang['search_prompt']         = '検索を実行';
$lang['clear']                 = '検索フィルターをリセット';
$lang['filter']                = 'フィルター';
$lang['export_all']            = '全ユーザーのエクスポート（CSV）';
$lang['export_filtered']       = '抽出したユーザー一覧のエクスポート（CSV）';
$lang['import']                = '新規ユーザーのインポート';
$lang['line']                  = '行番号';
$lang['error']                 = 'エラーメッセージ';
$lang['summary']               = 'ユーザー %1$d-%2$d / %3$d, 総ユーザー数 %4$d';
$lang['nonefound']             = 'ユーザーが見つかりません, 総ユーザー数 %d';
$lang['delete_ok']             = '%d ユーザーが削除されました';
$lang['delete_fail']           = '%d ユーザーの削除に失敗しました';
$lang['update_ok']             = 'ユーザーは更新されました';
$lang['update_fail']           = 'ユーザーの更新に失敗しました';
$lang['update_exists']         = 'ユーザー名（%s）は既に存在するため、ユーザー名の変更に失敗しました（その他の項目は変更されました）。';
$lang['start']                 = '最初';
$lang['prev']                  = '前へ';
$lang['next']                  = '次へ';
$lang['last']                  = '最後';
$lang['edit_usermissing']      = '選択したユーザーは見つかりません。削除もしくは変更された可能性があります。';
$lang['user_notify']           = 'ユーザーに通知する';
$lang['note_notify']           = '通知メールは、ユーザーに新たなパスワードが設定された場合のみ送信されます。';
$lang['note_group']            = 'グループを指定しない場合は、既定のグループ（%s）に配属されます。';
$lang['note_pass']             = '”ユーザーに通知する”をチェックしてパスワードを空欄にすると、パスワードは自動生成されます。';
$lang['add_ok']                = 'ユーザーを登録しました';
$lang['add_fail']              = 'ユーザーの登録に失敗しました';
$lang['notify_ok']             = '通知メールを送信しました';
$lang['notify_fail']           = '通知メールを送信できませんでした';
$lang['import_userlistcsv']    = 'ユーザー一覧ファイル（CSV）：';
$lang['import_header']         = '最新インポート - 失敗';
$lang['import_success_count']  = 'ユーザーインポート：ユーザーが%d件あり、%d件正常にインポートされました。';
$lang['import_failure_count']  = 'ユーザーインポート：%d件が失敗しました。失敗は次のとおりです。';
$lang['import_error_fields']   = '列の不足（４列必要）が%d件ありました。';
$lang['import_error_baduserid'] = '欠落したユーザーID';
$lang['import_error_badname']  = '不正なフルネーム';
$lang['import_error_badmail']  = '不正な電子メールアドレス';
$lang['import_error_upload']   = 'インポートが失敗しました。CSVファイルをアップロードできなかったか、ファイルが空です。';
$lang['import_error_readfail'] = 'インポートが失敗しました。アップロードされたファイルが読込できません。';
$lang['import_error_create']   = 'ユーザーが作成できません。';
$lang['import_notify_fail']    = '通知メッセージがインポートされたユーザー（%s）・電子メールアドレス（%s）に送信できませんでした。';
$lang['import_downloadfailures'] = '修正用に失敗を CSVファイルとしてダウンロードする。';
