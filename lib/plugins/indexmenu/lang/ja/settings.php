<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author king <b4688756@urhen.com>
 * @author Hideaki SAWADA <chuno@live.jp>
 */
$lang['checkupdate']           = '更新を定期的に確認する。';
$lang['only_admins']           = 'indexmenu 構文を管理者のみに許可します。<br>管理者でないユーザーが編集すると、ページ内の indexmenu ツリーはすべてなくなります。';
$lang['aclcache']              = 'ACL のための indexmenu キャッシュを最適化します（要求された名前空間の root 専用として動作します）。<br>選択した方法は、indexmenu ツリー上のノード表示だけに影響し、ページに対する権限には影響しません。<ul><li>None: 標準。速い方法で、キャッシュファイルを作成しませんが、権限に制限があるノードを無許可のユーザーに表示したり、その逆の可能性もあります。
ACL によってページへのアクセスを管理していないか、管理していてもにツリーに表示されることを気にしない場合に推奨。<li>User: ユーザーログイン毎。遅い方法で、多くのキャッシュファイルを作成しますが、常に正しく制限されたページを非表示にします。ログインユーザーに依存したページ ACL がある場合に推奨。<li>Groups: グループ毎。前の方法との程よい妥協。しかし、ACL 認証を持つグループに属しているユーザーに対して読み取り ACL を拒否した場合、そのユーザーはツリー内のノードを表示してしまします。サイト全体の ACL がグループに依存する場合に推奨。</ul>';
$lang['headpage']              = 'ヘッドページの方法： 名前空間の見出しとリンクの回収元のページ<br>この値のいずれかになります：<ul><li>グローバルスタートページ<li>名前空間の中にある名前空間と同名のページ<li>名前空間と同じ階層にある名前空間と同名のページ<li>カスタムページ名<li>ページ名のカンマ区切り一覧</ul>';
$lang['hide_headpage']         = 'ヘッドページを非表示にする';
$lang['empty_msg']             = 'ツリーが空の場合、表示するメッセージ。HTML コードではなく DokuWiki 構文を使用して下さい。<code>{{ns}}</code> 変数は要求された名前空間のショートカットです。';
$lang['skip_index']            = '対象外とする名前空間 ID。正規表現の形式を使用します。例： <code>/(sidebars|private:myns)/</code>';
$lang['skip_file']             = '対象外とするページ ID。正規表現の形式を使用します。例： <code>/(:start$|^public:newstart$)/</code>';
$lang['show_sort']             = '管理者にはページの注釈の先頭に indexmenu ソート番号を表示する。';
$lang['themes_url']            = 'この http URL から js テーマをダウンロードする。';
$lang['defaultoptions']        = 'indexmenu オプションのスペース区切り一覧。全ての indexmenu にデフォルト適用します。プラグイン構文の逆コマンドで元に戻すことができます。';
