<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author chinsan <chinsan.tw@gmail.com>
 * @author Li-Jiun Huang <ljhuang.tw@gmail.com>
 * @author http://www.chinese-tools.com/tools/converter-simptrad.html
 * @author Wayne San <waynesan@zerozone.tw>
 * @author Li-Jiun Huang <ljhuang.tw@gmai.com>
 * @author Cheng-Wei Chien <e.cwchien@gmail.com>
 * @author Shuo-Ting Jian <shoting@gmail.com>
 * @author syaoranhinata@gmail.com
 * @author Ichirou Uchiki <syaoranhinata@gmail.com>
 * @author tsangho <ou4222@gmail.com>
 * @author Danny Lin <danny0838@gmail.com>
 */
$lang['menu']                  = '帳號管理器';
$lang['noauth']                = '(帳號認證尚未開放)';
$lang['nosupport']             = '(尚不支援帳號管理)';
$lang['badauth']               = '錯誤的認證機制';
$lang['user_id']               = '帳號';
$lang['user_pass']             = '密碼';
$lang['user_name']             = '名稱';
$lang['user_mail']             = '電郵';
$lang['user_groups']           = '群組';
$lang['field']                 = '欄位';
$lang['value']                 = '設定值';
$lang['add']                   = '增加';
$lang['delete']                = '刪除';
$lang['delete_selected']       = '刪除所選的';
$lang['edit']                  = '修改';
$lang['edit_prompt']           = '修改該帳號';
$lang['modify']                = '儲存變更';
$lang['search']                = '搜尋';
$lang['search_prompt']         = '開始搜尋';
$lang['clear']                 = '重設篩選條件';
$lang['filter']                = '篩選條件 (Filter)';
$lang['export_all']            = '匯出所有使用者 (CSV)';
$lang['export_filtered']       = '匯出篩選後的使用者列表 (CSV)';
$lang['import']                = '匯入新使用者';
$lang['line']                  = '列號';
$lang['error']                 = '錯誤訊息';
$lang['summary']               = '顯示帳號 %1$d-%2$d，共 %3$d 筆符合。共有 %4$d 個帳號。';
$lang['nonefound']             = '找不到帳號。共有 %d 個帳號。';
$lang['delete_ok']             = '已刪除 %d 個帳號';
$lang['delete_fail']           = '%d 個帳號無法刪除。';
$lang['update_ok']             = '已更新該帳號';
$lang['update_fail']           = '無法更新該帳號';
$lang['update_exists']         = '無法變更帳號名稱 (%s) ，因為有同名帳號存在。其他修改則已套用。';
$lang['start']                 = '開始';
$lang['prev']                  = '上一頁';
$lang['next']                  = '下一頁';
$lang['last']                  = '最後一頁';
$lang['edit_usermissing']      = '找不到選取的帳號，可能已被刪除或改為其他名稱。';
$lang['user_notify']           = '通知使用者';
$lang['note_notify']           = '通知信只會在指定使用者新密碼時寄送。';
$lang['note_group']            = '如果沒有指定群組，新使用者將會列入至預設群組(%s)當中。';
$lang['note_pass']             = '如果勾選了通知使用者，而沒有輸入這個欄位，則會自動產生一組密碼。';
$lang['add_ok']                = '已新增使用者';
$lang['add_fail']              = '無法新增使用者';
$lang['notify_ok']             = '通知信已寄出';
$lang['notify_fail']           = '通知信無法寄出';
$lang['import_userlistcsv']    = '使用者列表檔案 (CSV):  ';
$lang['import_header']         = '最近一次匯入 - 失敗';
$lang['import_success_count']  = '使用者匯入：找到 %d 個使用者，已成功匯入 %d 個。';
$lang['import_failure_count']  = '使用者匯入：%d 個匯入失敗，列出於下。';
$lang['import_error_fields']   = '欄位不足，需要 4 個，找到 %d 個。';
$lang['import_error_baduserid'] = '使用者帳號遺失';
$lang['import_error_badname']  = '名稱不正確';
$lang['import_error_badmail']  = '電郵位址不正確';
$lang['import_error_upload']   = '匯入失敗，CSV 檔案內容空白或無法匯入。';
$lang['import_error_readfail'] = '匯入錯誤，無法讀取上傳的檔案';
$lang['import_error_create']   = '無法建立使用者';
$lang['import_notify_fail']    = '通知訊息無法寄給已匯入的使用者 %s（電郵 %s）';
$lang['import_downloadfailures'] = '下載失敗項的 CSV 檔案以供修正';
