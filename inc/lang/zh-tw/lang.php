<?php
/**
 * Chinese(Traditional) language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     chinsan <chinsan@mail2000.com.tw>
 */
$lang['encoding']   = 'utf-8';
$lang['direction']  = 'ltr';

$lang['btn_edit']   = '編修本頁';
$lang['btn_source'] = '顯示頁面來源';
$lang['btn_show']   = '顯示頁面';
$lang['btn_create'] = '建立此頁';
$lang['btn_search'] = '搜尋';
$lang['btn_save']   = '儲存';
$lang['btn_preview']= '預覽';
$lang['btn_top']    = '回到頁頂';
$lang['btn_newer']  = '<< 較新';
$lang['btn_older']  = '較舊 >>';
$lang['btn_revs']   = '舊版';
$lang['btn_recent'] = '最近更新';
$lang['btn_upload'] = '上傳';
$lang['btn_cancel'] = '取消';
$lang['btn_index']  = '索引頁';
$lang['btn_secedit']= '改這段';
$lang['btn_login']  = '登入';
$lang['btn_logout'] = '登出';
$lang['btn_admin']  = '管理選單';
$lang['btn_update'] = '更新設定';
$lang['btn_delete'] = '刪除';
$lang['btn_back']   = '回上一步';
$lang['btn_backlink']    = "Backlinks";
$lang['btn_backtomedia'] = '重新選擇圖檔';
$lang['btn_subscribe']   = '訂閱更動通知';
$lang['btn_unsubscribe'] = '退訂更動通知';
$lang['btn_profile']     = '更新個人資料';
$lang['btn_reset']       = '資料重設';
$lang['btn_resendpwd']   = '寄新密碼';
$lang['btn_draft']    = '編輯草稿';
$lang['btn_recover']  = '復原草稿';
$lang['btn_draftdel'] = '捨棄草稿';

$lang['loggedinas'] = '登入為';
$lang['user']       = '帳號';
$lang['pass']       = '密碼';
$lang['newpass']    = '新的密碼';
$lang['oldpass']    = '目前的密碼';
$lang['passchk']    = '再次打新的密碼';
$lang['remember']   = '記住帳號密碼';
$lang['fullname']   = '暱稱';
$lang['email']      = 'E-Mail';
$lang['register']   = '註冊';
$lang['profile']    = '使用者個人資料';
$lang['badlogin']   = '很抱歉，您的使用者名稱或密碼可能有錯誤';
$lang['minoredit']  = '次要性的修改';
$lang['draftdate']  = '草稿自動存檔於'; // full dformat date will be added

$lang['regmissing'] = '很抱歉，所有的欄位都要填哦';
$lang['reguexists'] = '很抱歉，已有人註冊該帳號了喔';
$lang['regsuccess'] = '使用者已建立，密碼已經用 email 寄到您信箱了唷。';
$lang['regsuccess2']= '使用者已建立';
$lang['regmailfail']= '寄出密碼信似乎發生錯誤，請跟管理者聯絡！';
$lang['regbadmail'] = '您輸入的 email 似乎不對，如果您認為是正確的，請與管理者聯絡。';
$lang['regbadpass'] = '兩次打的密碼不一致，請再重試，謝謝。';
$lang['regpwmail']  = '您的 DokuWiki 帳號密碼';
$lang['reghere']    = '您還沒有帳號對吧？來註冊一個吧。';

$lang['profna']       = '本 wiki 不開放修改個人資料';
$lang['profnochange'] = '未做任何變更';
$lang['profnoempty']  = '帳號或 email 地址不可以沒有寫喔！';
$lang['profchanged']  = '個人資料已成功更新囉。';

$lang['pwdforget'] = '忘記密碼嗎？寄新密碼！';
$lang['resendna']  = '本 wiki 不開放重寄新密碼';
$lang['resendpwd'] = '寄新密碼給';
$lang['resendpwdmissing'] = '很抱歉，您必須全填這些資料才可以';
$lang['resendpwdnouser']  = '很抱歉，資料庫內查無此人';
$lang['resendpwdsuccess'] = '新密碼函已經以 email 寄出了。';

$lang['txt_upload']   = '請選擇要上傳的檔案';
$lang['txt_filename'] = '請輸入要存在 wiki 內的檔名 (optional)';
$lang['txt_overwrt']  = '是否要覆蓋原有檔案';
$lang['lockedby']     = '目前已被下列人員鎖定';
$lang['lockexpire']   = '預計解除鎖定於';
$lang['willexpire']   = '您目前編輯這頁的鎖定將會在一分鐘內解除。\若要避免發生意外，請按「預覽」鍵來重新設定鎖定狀態';

$lang['notsavedyet'] = '有尚未儲存的變更將會遺失。\n真的要繼續嗎？';
$lang['rssfailed']   = '當抓取餵送過來的 RSS 資料時發生錯誤: ';
$lang['nothingfound']= '沒找到任何結果。';

$lang['mediaselect'] = '選擇圖檔';
$lang['fileupload']  = '上傳圖檔';
$lang['uploadsucc']  = '上傳成功';
$lang['uploadfail']  = '上傳失敗。或許權限設定錯誤了嗎？';
$lang['uploadwrong'] = '拒絕上傳。該檔案類型不被支援。';
$lang['uploadexist'] = '該檔案已有存在了喔，故取消上傳動作。';
$lang['deletesucc']  = '"%s" 檔已刪除完畢。';
$lang['deletefail']  = '"%s" 檔無法刪除，請先檢查權限設定。';
$lang['mediainuse']  = '"%s" 檔因還在使用中，故目前尚無法刪除。';
$lang['namespaces']  = 'Namespaces';
$lang['mediafiles']  = '可用的檔案有';

$lang['js']['keepopen']    = 'Keep window open on selection';
$lang['js']['hidedetails'] = 'Hide Details';
$lang['mediausage']  = 'Use the following syntax to reference this file:';
$lang['mediaview']   = '檢視原始檔案';
$lang['mediaroot']   = 'root';
$lang['mediaupload'] = 'Upload a file to the current namespace here. To create subnamespaces, prepend them to your "Upload as" filename separated by colons.';
$lang['mediaextchange'] = '檔案類型已由 .%s 變更為 .%s 囉!';

$lang['reference']   = '引用到本頁的，合計有';
$lang['ref_inuse']   = '這檔還不能刪除，因為還有以下的頁面在使用它：';
$lang['ref_hidden']  = '有些引用到這個的頁面，您目前還沒有權限可讀取喔。';

$lang['hits']       = '個符合';
$lang['quickhits']  = '符合的頁面名稱';
$lang['toc']        = '本頁目錄';
$lang['current']    = '目前版本';
$lang['yours']      = '您的版本';
$lang['diff']       = '顯示跟目前版本的差異';
$lang['line']       = '行';
$lang['breadcrumb'] = '目前的足跡';
$lang['youarehere'] = '(目前所在位置)';
$lang['lastmod']    = '上一次變更';
$lang['by']            = '來自';
$lang['deleted']    = '移除';
$lang['created']    = '建立';
$lang['restored']   = '已恢復為舊版';
$lang['summary']    = '編輯摘要';

$lang['mail_newpage'] = '增加的頁面:';
$lang['mail_changed'] = '變更的頁面:';

$lang['nosmblinks'] = '只有在 Microsoft IE 下才能執行「連結到 Windows shares」。\n不過您仍可拷貝、複製這連結';

$lang['qb_alert']   = '請輸入想要格式化的文字，\n這會附加到文件的結尾。';
$lang['qb_bold']    = '粗體';
$lang['qb_italic']  = '斜體';
$lang['qb_underl']  = '底線';
$lang['qb_code']    = '程式碼';
$lang['qb_strike']  = '刪除線';
$lang['qb_h1']      = 'H1 標題';
$lang['qb_h2']      = 'H2 標題';
$lang['qb_h3']      = 'H3 標題';
$lang['qb_h4']      = 'H4 標題';
$lang['qb_h5']      = 'H5 標題';
$lang['qb_link']    = 'WIKI內部連結';
$lang['qb_extlink'] = '連結外部URL';
$lang['qb_hr']      = '水平線';
$lang['qb_ol']      = '項目表(數字)';
$lang['qb_ul']      = '項目表(符號)';
$lang['qb_media']   = '加入圖片或檔案';
$lang['qb_sig']     = '插入簽名';
$lang['qb_smileys'] = '表情符號';
$lang['qb_chars']   = '特殊字元';

$lang['del_confirm']= '確定要刪除該管理規則?';
$lang['admin_register']= '新增使用者中';

$lang['metaedit']    = '更改相片資料(EXIF)';
$lang['metasaveerr'] = '相片資料(EXIF)儲存失敗喔';
$lang['metasaveok']  = '相片資料已成功儲存';
$lang['img_backto']  = '回上一頁';
$lang['img_title']   = '標題';
$lang['img_caption'] = '照片說明';
$lang['img_date']    = '日期';
$lang['img_fname']   = '檔名';
$lang['img_fsize']   = '大小';
$lang['img_artist']  = '攝影者';
$lang['img_copyr']   = '版權';
$lang['img_format']  = '格式';
$lang['img_camera']  = '相機';
$lang['img_keywords']= '關鍵字';

$lang['subscribe_success']  = '已將『%s』加入 %s 訂閱清單內';
$lang['subscribe_error']    = '要把『%s』加入 %s 訂閱清單時，發生錯誤';
$lang['subscribe_noaddress']= '您的帳號內並無 Email 資料，因此還無法使用訂閱功能唷。';
$lang['unsubscribe_success']= '已將『%s』從 %s 訂閱清單中移除';
$lang['unsubscribe_error']  = '要把『%s』從 %s 訂閱清單中移除時，發生錯誤';

$lang['txt_insert']    = '放入日曆';
$lang['qb_calendar']   = '放入一個日曆';
$lang['btn_insert']    = '產生日曆';
$lang['txt_toinsert']  = '這將會被放入';
$lang['txt_clickhere'] = '按這就會幫您自動放好了';
$lang['txt_year']      = '年份';
$lang['txt_month']     = '月份';
$lang['arr_daysofweek']= array('日','一','二','三','四','五','六');

/* auth.class language support */
$lang['authmodfailed']   = '帳號認證的設定不正確，請通知該 Wiki 管理員。';
$lang['authtempfail']    = '帳號認證目前暫不提供，若本狀況持續發生的話，請通知該 Wiki 管理員。';

//Setup VIM: ex: et ts=2 enc=utf-8 :
