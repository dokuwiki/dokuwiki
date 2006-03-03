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

// settings prompts
$lang['umask']       = 'グローバル権限マスク';     //set the umask for new files
$lang['fmode']       = 'ファイル作成マスク';         //directory mask accordingly
$lang['dmode']       = 'フォルダ作成マスク';    //directory mask accordingly
$lang['lang']        = '使用言語';           //your language
$lang['basedir']     = 'ベースディレクトリ';     //absolute dir from serveroot - blank for autodetection
$lang['baseurl']     = 'ベースURL';           //URL to server including protocol - blank for autodetect
$lang['savedir']     = '保存ディレクトリ';     //where to store all the files
$lang['start']       = 'スタートページ名';    //name of start page
$lang['title']       = 'WIKIタイトル';         //what to show in the title
$lang['template']    = 'テンプレート';           //see tpl directory
$lang['fullpath']    = 'パス指定（絶対/相対）';      //show full path of the document or relative to datadir only? 0|1
$lang['recent']      = '最近の変更表示数';     //how many entries to show in recent
$lang['breadcrumbs'] = 'トレース表示数';        //how many recent visited pages to show
$lang['typography']  = 'タイポグラフィー';         //convert quotes, dashes and stuff to typographic equivalents? 0|1
$lang['htmlok']      = 'HTML埋め込み許可';//may raw HTML be embedded? This may break layout and XHTML validity 0|1
$lang['phpok']       = 'PHP埋め込み許可'; //may PHP code be embedded? Never do this on the internet! 0|1
$lang['dformat']     = '日付フォーマット';        //dateformat accepted by PHPs date() function
$lang['signature']   = '署名';          //signature see wiki:langig for details
$lang['toptoclevel'] = '目次 トップレベル見出し';      //Level starting with and below to include in AutoTOC (max. 5)
$lang['maxtoclevel'] = '目次 表示限度見出し';      //Up to which level include into AutoTOC (max. 5)
$lang['maxseclevel'] = '編集可能見出し';   //Up to which level create editable sections (max. 5)
$lang['camelcase']   = 'キャメルケースリンク';  //Use CamelCase for linking? (I don't like it) 0|1
$lang['deaccent']    = 'ページ名アクセント除去';    //convert accented chars to unaccented ones in pagenames?
$lang['useheading']  = '最初の見出しをページ名';        //use the first heading in a page as its name
$lang['refcheck']    = 'メディア参照元チェック';    //check for references before deleting media files
$lang['refshow']     = 'メディア参照元表示数'; //how many references should be shown, 5 is a good value
$lang['allowdebug']  = 'デバッグモード（無効）';   //make debug possible, disable after install! 0|1

$lang['usewordblock']= '単語によるスパムブロック';  //block spam based on words? 0|1
$lang['indexdelay']  = 'インデックスを許可（何秒後）'; //allow indexing after this time (seconds) default is 5 days
$lang['relnofollow'] = 'rel="nofollow" を付加';         //use rel="nofollow" for external links?
$lang['mailguard']   = 'メールアドレス保護';  //obfuscate email addresses against spam harvesters?

/* Authentication Options - read http://www.splitbrain.org/dokuwiki/wiki:acl */
$lang['useacl']      = 'アクセス管理';                //Use Access Control Lists to restrict access?
$lang['openregister']= 'ユーザー登録を許可';          //Should users to be allowed to register?
$lang['autopasswd']  = 'パスワードの自動生成'; //autogenerate passwords and email them to user
$lang['resendpasswd']= 'パスワードの再発行';  //allow resend password function?
$lang['authtype']    = '認証方法'; //which authentication backend should be used
$lang['passcrypt']   = '暗号化方法';    //Used crypt method (smd5,md5,sha1,ssha,crypt,mysql,my411)
$lang['defaultgroup']= '初期グループ';          //Default groups new Users are added to
$lang['superuser']   = 'スーパーユーザー';              //The admin can be user or @group
$lang['profileconfirm'] = '現在のパスワードを要求';     //Require current password to confirm changes to user profile

/* Advanced Options */
$lang['userewrite']  = 'URLの書き換え';             //this makes nice URLs: 0: off 1: .htaccess 2: internal
$lang['useslash']    = 'スラッシュを使用';                 //use slash instead of colon? only when rewrite is on
$lang['sepchar']     = '単語区切り文字';  //word separator character in page names; may be a
$lang['canonical']   = '標準的なURLを使用';  //Should all URLs use full canonical http://... style?
$lang['autoplural']  = '自動複数形処理';               //try (non)plural form of nonexisting files?
$lang['usegzip']     = '古い文書にgzipを使用';      //gzip old revisions?
$lang['cachetime']   = 'キャッシュ保持時間（秒）';  //maximum age for cachefile in seconds (defaults to a day)
$lang['purgeonadd']  = 'ファイル追加時にキャッシュを破棄';        //purge cache when a new file is added (needed for up to date links)
$lang['locktime']    = 'ファイルロック期限（秒）';  //maximum age for lockfiles (defaults to 15 minutes)
$lang['notify']      = '変更を通知するメールアドレス';      //send change info to this email (leave blank for nobody)
$lang['mailfrom']    = 'メール送信時のアドレス';            //use this email when sending mails
$lang['gdlib']       = 'GDlibバージョン';              //the GDlib version (0, 1 or 2) 2 tries to autodetect
$lang['im_convert']  = 'ImageMagicksパス';            //path to ImageMagicks convert (will be used instead of GD)
$lang['spellchecker']= 'スペルチェック';         //enable Spellchecker (needs PHP >= 4.3.0 and aspell installed)
$lang['subscribers'] = '更新通知機能'; //enable change notice subscription support
$lang['compress']    = 'CSSとJavaScriptによる圧縮';  //Strip whitespaces and comments from Styles and JavaScript? 1|0
$lang['hidepages']   = '非公開ページ（Regex）';      //Regexp for pages to be skipped from RSS, Search and Recent Changes
$lang['send404']     = '"HTTP404/Page Not Found"を使用';    //Send a HTTP 404 status for non existing pages?
$lang['sitemap']     = 'Googleサイトマップ作成頻度（日数）';   //Create a google sitemap? How often? In days.

$lang['rss_type']    = 'RSSフィード';             //type of RSS feed to provide, by default:
$lang['rss_linkto']  = 'RSS内リンク先';              //what page RSS entries link to:

//Set target to use when creating links - leave empty for same window
$lang['target____wiki']      = '内部リンクの表示先';
$lang['target____interwiki'] = '内部wikiの表示先';
$lang['target____extern']    = '外部リンクの表示先';
$lang['target____media']     = 'メディアリンクの表示先';
$lang['target____windows']   = 'Windowsリンクの表示先';

//Proxy setup - if your Server needs a proxy to access the web set these
$lang['proxy____host'] = 'プロキシ - ホスト';
$lang['proxy____port'] = 'プロキシ - ポート';
$lang['proxy____user'] = 'プロキシ - ユーザー名';
$lang['proxy____pass'] = 'プロキシ - パスワード';
$lang['proxy____ssl']  = 'プロキシ - ssl';

/* Safemode Hack */
$lang['safemodehack'] = 'セーフモード対策';  //read http://wiki.splitbrain.org/wiki:safemodehack !
$lang['ftp____host'] = 'ftp - ホスト';
$lang['ftp____port'] = 'ftp - ポート';
$lang['ftp____user'] = 'ftp - ユーザー名';
$lang['ftp____pass'] = 'ftp - パスワード';
$lang['ftp____root'] = 'ftp - ルートディレクトリ';

/* userewrite options */
$lang['userewrite_o_0'] = '使用しない';
$lang['userewrite_o_1'] = 'htaccess';
$lang['userewrite_o_2'] = 'dokuwiki';

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

