<?php
/**
 * japanese language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Yuji Takenaka <webmaster@davilin.com>
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Anika Henke <henke@cosmocode.de>
 * @author     Matthias Grimm <matthiasgrimm@users.sourceforge.net>
 */
$lang['encoding']   = 'utf-8';
$lang['direction']  = 'ltr';

$lang['btn_edit']   = '文書の編集';
$lang['btn_source'] = 'ソースの表示';
$lang['btn_show']   = '文書の表示';
$lang['btn_create'] = '文書の作成';
$lang['btn_search'] = '検索';
$lang['btn_save']   = '保存';
$lang['btn_preview']= '確認';
$lang['btn_top']    = '文書の先頭へ';
$lang['btn_newer']  = '<< より新しい';
$lang['btn_older']  = 'より古い >>';
$lang['btn_revs']   = '以前のリビジョン';
$lang['btn_recent'] = '最近の変更';
$lang['btn_upload'] = 'アップロード';
$lang['btn_cancel'] = 'キャンセル';
$lang['btn_index']  = '索引';
$lang['btn_secedit']= '編集';
$lang['btn_login']  = 'ログイン';
$lang['btn_logout'] = 'ログアウト';
$lang['btn_admin']  = '管理';
$lang['btn_update'] = '更新';
$lang['btn_delete'] = '削除';
$lang['btn_back']   = '戻る';
$lang['btn_backlink']    = "バックリンク";
$lang['btn_backtomedia'] = 'メディアファイル選択に戻る';
$lang['btn_subscribe']   = '変更履歴配信の登録';
$lang['btn_unsubscribe'] = '変更履歴配信の解除';
$lang['btn_profile']    = 'ユーザー情報の更新';
$lang['btn_reset']     = 'リセット';
$lang['btn_resendpwd'] = 'パスワードの再発行';

$lang['loggedinas'] = 'ようこそ';
$lang['user']       = 'ユーザー名';
$lang['pass']       = 'パスワード';
$lang['newpass']    = '新しいパスワード';
$lang['oldpass']    = '現在のパスワード';
$lang['passchk']    = '確認';
$lang['remember']   = 'ユーザー名とパスワードを記憶する';
$lang['fullname']   = 'フルネーム';
$lang['email']      = 'メールアドレス';
$lang['register']   = 'ユーザー登録';
$lang['profile']    = 'ユーザー情報';
$lang['badlogin']   = 'ユーザー名かパスワードが違います。';
$lang['minoredit']  = '小変更';

$lang['regmissing'] = '全ての項目を入力してください。';
$lang['reguexists'] = 'このユーザー名は既に存在しています。';
$lang['regsuccess'] = '新しいユーザーが作成されました。パスワードは登録したメールアドレス宛てに送付されます。';
$lang['regsuccess2']= '新しいユーザーが作成されました。';
$lang['regmailfail']= 'パスワードのメール送信に失敗しました。お手数ですが管理者まで連絡をお願いします。';
$lang['regbadmail'] = 'メールアドレスが有効ではありません。';
$lang['regbadpass'] = '確認用のパスワードが正しくありません。';
$lang['regpwmail']  = 'あなたの DokuWiki パスワード';
$lang['reghere']    = 'ご自分用のアカウントを取ってみては如何ですか？';

$lang['profna']       = 'ユーザー情報の変更は出来ません';
$lang['profnochange'] = '変更点はありませんでした。';
$lang['profnoempty']  = 'ユーザー名とメールアドレスを入力して下さい。';
$lang['profchanged']  = 'ユーザー情報は更新されました。';

$lang['pwdforget'] = 'パスワードをお忘れですか？パスワード再発行';
$lang['resendna']  = 'パスワードの再発行は出来ません。';
$lang['resendpwd'] = '新しいパスワードを送信します：';
$lang['resendpwdmissing'] = '全ての項目を入力して下さい。';
$lang['resendpwdnouser']  = '入力されたユーザーが見つかりませんでした。';
$lang['resendpwdsuccess'] = '新しいパスワードがメールで送信されました。';

$lang['txt_upload']   = 'アップロードするファイルを選んでください。';
$lang['txt_filename'] = '名前を変更してアップロード（オプション）';
$lang['txt_overwrt']  = '既存のファイルを上書き';
$lang['lockedby']     = 'この文書は次のユーザによってロックされています';
$lang['lockexpire']   = '排他期限：';
$lang['willexpire']   = '編集中の文書は排他期限を過ぎようとしています。このままロックする場合は、一度文書の確認を行ってロックタイマーをリセットしてください。';

$lang['notsavedyet'] = '変更は保存されません。このまま処理を続けてよろしいですか？';
$lang['rssfailed']   = 'RSSの取り出しに失敗しました：';
$lang['nothingfound']= '該当文書はありませんでした。';

$lang['mediaselect'] = 'メディアファイルを選択';
$lang['fileupload']  = 'メディアファイルをアップロード';
$lang['uploadsucc']  = 'アップロード完了';
$lang['uploadfail']  = 'アップロードに失敗しました。権限がありません。';
$lang['uploadwrong'] = 'アップロードは拒否されました。この拡張子は許可されていません。';
$lang['uploadexist'] = '同名のファイルが存在するため、アップロードできません。';
$lang['deletesucc']  = 'ファイル "%s" は削除されました。';
$lang['deletefail']  = 'ファイル "%s" が削除できません。権限を確認して下さい。';
$lang['mediainuse']  = 'ファイル "%s" は使用中のため、削除されませんでした。';
$lang['namespaces']  = '名前空間';
$lang['mediafiles']  = '有効なファイル：';

$lang['reference']   = '参照先';
$lang['ref_inuse']   = 'このファイルは、次のページで使用中のため削除できません。';
$lang['ref_hidden']  = 'このページに存在するいくつかの参照先は、権限が無いため読むことができません。';

$lang['hits']       = 'ヒット';
$lang['quickhits']  = 'マッチした文書名';
$lang['toc']        = '目次';
$lang['current']    = '現在';
$lang['yours']      = 'あなたのバージョン';
$lang['diff']       = '現在のバージョンとの差分を表示';
$lang['line']       = 'ライン';
$lang['breadcrumb'] = 'トレース';
$lang['youarehere'] = '現在位置';
$lang['lastmod']    = '最終更新';
$lang['by']         = 'by';
$lang['deleted']    = '削除';
$lang['created']    = '作成';
$lang['restored']   = '以前のバージョンを復元';
$lang['summary']    = 'サマリーを編集';

$lang['mail_newpage'] = '文書の追加：';
$lang['mail_changed'] = '文書の変更：';

$lang['nosmblinks'] = 'ウィンドウズの共有フォルダへリンクは Microsoft Internet Explorer でのみ可能となります。\n当然、カットアンドペーストが使用できます。';

$lang['qb_alert']   = '文書の最後に挿入したい定型文書を入力して下さい。';
$lang['qb_bold']    = '太字';
$lang['qb_italic']  = '斜体';
$lang['qb_underl']  = '下線';
$lang['qb_code']    = 'コード';
$lang['qb_strike']  = '打消線';
$lang['qb_h1']      = '第一見出し';
$lang['qb_h2']      = '第二見出し';
$lang['qb_h3']      = '第三見出し';
$lang['qb_h4']      = '第四見出し';
$lang['qb_h5']      = '第五見出し';
$lang['qb_link']    = '内部リンク';
$lang['qb_extlink'] = '外部リンク';
$lang['qb_hr']      = '横罫線';
$lang['qb_ol']      = '記号付きリスト';
$lang['qb_ul']      = '記号なしリスト';
$lang['qb_media']   = 'イメージやファイルの追加';
$lang['qb_sig']     = '署名の挿入';
$lang['qb_smileys'] = 'スマイリー';
$lang['qb_chars']   = '特殊文字';

$lang['del_confirm']= '選択した項目を本当に削除しますか？';
$lang['admin_register']= '新規ユーザー作成';

$lang['spell_start'] = 'スペルチェック';
$lang['spell_stop']  = '再開';
$lang['spell_wait']  = 'しばらくお待ち下さい...';
$lang['spell_noerr'] = 'スペルエラーなし';
$lang['spell_nosug'] = '候補なし';
$lang['spell_change']= '変更';

$lang['metaedit']    = 'メタデータ編集';
$lang['metasaveerr'] = 'メタデータの書き込みに失敗しました';
$lang['metasaveok']  = 'メタデータは保存されました';
$lang['img_backto']  = '戻る';
$lang['img_title']   = 'タイトル';
$lang['img_caption'] = '見出し';
$lang['img_date']    = '日付';
$lang['img_fname']   = 'ファイル名';
$lang['img_fsize']   = 'サイズ';
$lang['img_artist']  = '作成者';
$lang['img_copyr']   = '著作権';
$lang['img_format']  = 'フォーマット';
$lang['img_camera']  = '使用カメラ';
$lang['img_keywords']= 'キーワード';

$lang['subscribe_success']  = '変更履歴配信の登録が完了しました。';
$lang['subscribe_error']    = '変更履歴配信の登録に失敗しました。';
$lang['subscribe_noaddress']= 'ログインしていないため、変更履歴配信に登録することはできません。';
$lang['unsubscribe_success']= '変更履歴配信の解除が完了しました。';
$lang['unsubscribe_error']  = '変更履歴配信の解除に失敗しました。';

/* auth.class language support */
$lang['authmodfailed']   = 'ユーザー認証の設定が正しくありません。Wikiの管理者に連絡して下さい。';
$lang['authtempfail']    = 'ユーザー認証が一時的に使用できなくなっています。この状態が続いているようであれば、Wikiの管理者に連絡して下さい。';

//Setup VIM: ex: et ts=2 enc=utf-8 :
