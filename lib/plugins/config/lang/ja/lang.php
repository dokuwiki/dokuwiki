<?php
/**
 * japanese language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Yuji Takenaka <webmaster@davilin.com>
 * @author     Christopher Smith <chris@jalakai.co.uk>
 */

// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu']       = 'サイト設定'; 

$lang['error']      = '不正な値が存在するため、設定は更新されませんでした。入力値を確認してから、再度更新してください。
                       <br />不正な値が入力されている項目は赤い線で囲まれています。';
$lang['updated']    = '設定は正しく更新されました。';
$lang['nochoice']   = '（他の選択肢はありません）';
$lang['locked']     = '設定用ファイルを更新できません。もし意図して変更不可にしているのでなければ、<br />
                       ローカル設定ファイルの名前と権限を確認して下さい。';


/* -------------------- Config Options --------------------------- */

$lang['fmode']       = 'ファイル作成マスク';
$lang['dmode']       = 'フォルダ作成マスク';
$lang['lang']        = '使用言語';
$lang['basedir']     = 'ベースディレクトリ';
$lang['baseurl']     = 'ベースURL';
$lang['savedir']     = '保存ディレクトリ';
$lang['start']       = 'スタートページ名';
$lang['title']       = 'WIKIタイトル';
$lang['template']    = 'テンプレート';
$lang['fullpath']    = 'ページのフッターに絶対パスを表示';
$lang['recent']      = '最近の変更表示数';
$lang['breadcrumbs'] = 'トレース（パンくず）表示数';
$lang['youarehere']  = 'トレースを現在位置表示に変更';
$lang['typography']  = 'タイポグラフィー変換';
$lang['htmlok']      = 'HTML埋め込み';
$lang['phpok']       = 'PHP埋め込み';
$lang['dformat']     = '日付フォーマット（PHPの<a href="http://www.php.net/date">date</a>関数を参照）';
$lang['signature']   = '署名';
$lang['toptoclevel'] = '目次 トップレベル見出し';
$lang['maxtoclevel'] = '目次 表示限度見出し';
$lang['maxseclevel'] = '編集可能見出し';
$lang['camelcase']   = 'キャメルケースリンク';
$lang['deaccent']    = 'ページ名アクセント';
$lang['useheading']  = '最初の見出しをページ名とする';
$lang['refcheck']    = 'メディア参照元チェック';
$lang['refshow']     = 'メディア参照元表示数';
$lang['allowdebug']  = 'デバッグモード（<b>必要で無いときは無効にしてください</b>）';

$lang['usewordblock']= '単語リストに基づくスパムブロック';
$lang['indexdelay']  = 'インデックスを許可（何秒後）';
$lang['relnofollow'] = 'rel="nofollow"を付加';
$lang['mailguard']   = 'メールアドレス保護';

/* Authentication Options */
$lang['useacl']      = 'アクセス管理を行う（ACL）';
$lang['openregister']= 'ユーザー登録を許可（ACL）';
$lang['autopasswd']  = 'パスワードの自動生成（ACL）';
$lang['resendpasswd']= 'パスワードの再発行（ACL）';
$lang['authtype']    = '認証方法（ACL）';
$lang['passcrypt']   = '暗号化方法（ACL）';
$lang['defaultgroup']= 'デフォルトグループ（ACL）';
$lang['superuser']   = 'スーパーユーザー（ACL）';
$lang['profileconfirm'] = 'プロフィール変更時に現在のパスワードを要求（ACL）';

/* Advanced Options */
$lang['userewrite']  = 'URLの書き換え';
$lang['useslash']    = 'URL上の名前空間の区切りにスラッシュを使用';
$lang['sepchar']     = 'ページ名の単語区切り文字';
$lang['canonical']   = 'canonical URL（正準URL）を使用';
$lang['autoplural']  = '自動複数形処理';
$lang['usegzip']     = '古い文書にgzipを使用';
$lang['cachetime']   = 'キャッシュ保持時間（秒）';
$lang['purgeonadd']  = 'ファイル追加時にキャッシュを破棄';
$lang['locktime']    = 'ファイルロック期限（秒）';
$lang['notify']      = '変更を通知するメールアドレス';
$lang['mailfrom']    = 'メール送信時のアドレス';
$lang['gdlib']       = 'GDlibバージョン';
$lang['im_convert']  = 'ImageMagick変換ツールへのパス';
$lang['spellchecker']= 'スペルチェック';
$lang['subscribers'] = '更新通知機能';
$lang['compress']    = 'CSSとJavaScriptを圧縮';
$lang['hidepages']   = '非公開ページ（Regex）';
$lang['send404']     = '文書が存在しないページに"HTTP404/Page Not Found"を使用';
$lang['sitemap']     = 'Googleサイトマップ作成頻度（日数）';

$lang['rss_type']    = 'RSSフィード形式';
$lang['rss_linkto']  = 'RSS内リンク先';

/* Target options */
$lang['target____wiki']      = '内部リンクの表示先';
$lang['target____interwiki'] = '内部wikiの表示先';
$lang['target____extern']    = '外部リンクの表示先';
$lang['target____media']     = 'メディアリンクの表示先';
$lang['target____windows']   = 'Windowsリンクの表示先';

/* Proxy Options */
$lang['proxy____host'] = 'プロキシ - サーバー名';
$lang['proxy____port'] = 'プロキシ - ポート';
$lang['proxy____user'] = 'プロキシ - ユーザー名';
$lang['proxy____pass'] = 'プロキシ - パスワード';
$lang['proxy____ssl']  = 'プロキシへの接続にsslを使用';

/* Safemode Hack */

$lang['safemodehack'] = 'セーフモード対策を行う';
$lang['ftp____host'] = 'FTP サーバー名（セーフモード対策）';
$lang['ftp____port'] = 'FTP ポート（セーフモード対策）';
$lang['ftp____user'] = 'FTP ユーザー名（セーフモード対策）';
$lang['ftp____pass'] = 'FTP パスワード（セーフモード対策）';
$lang['ftp____root'] = 'FTP ルートディレクトリ（セーフモード対策）';

/* userewrite options */
$lang['userewrite_o_0'] = '使用しない';
$lang['userewrite_o_1'] = '.htaccess';
$lang['userewrite_o_2'] = 'DokuWikiによる設定';

/* deaccent options */
$lang['deaccent_o_0'] = '指定しない';
$lang['deaccent_o_1'] = 'アクセントを除去';
$lang['deaccent_o_2'] = 'ローマナイズ';

/* gdlib options */
$lang['gdlib_o_0'] = 'GDlibを使用しない';
$lang['gdlib_o_1'] = 'バージョン 1.x';
$lang['gdlib_o_2'] = '自動検出';

/* rss_type options */
$lang['rss_type_o_rss']  = 'RSS 0.91';
$lang['rss_type_o_rss1'] = 'RSS 1.0';
$lang['rss_type_o_rss2'] = 'RSS 2.0';
$lang['rss_type_o_atom'] = 'Atom 0.3';

/* rss_linkto options */
$lang['rss_linkto_o_diff']    = '変更点のリスト';
$lang['rss_linkto_o_page']    = '変更されたページ';
$lang['rss_linkto_o_rev']     = 'リビジョンのリスト';
$lang['rss_linkto_o_current'] = '現在のページ';

