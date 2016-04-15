<?php
/**
 * Welsh language file
 *
 * @author Chris Smith <chris@jalakai.co.uk>
 * @author Alan Davies <ben.brynsadler@gmail.com>
 */

$lang['menu'] = 'Rheolwr Defnyddwyr';

// custom language strings for the plugin
$lang['noauth']      = '(dilysiad defnddwyr ddim ar gael)';
$lang['nosupport']   = '(rheolaeth defnyddwyr heb ei chynnal)';

$lang['badauth']     = 'mecanwaith dilysu annilys';     // should never be displayed!

$lang['user_id']     = 'Defnyddiwr';
$lang['user_pass']   = 'Cyfrinair';
$lang['user_name']   = 'Enw Cywir';
$lang['user_mail']   = 'Ebost';
$lang['user_groups'] = 'Grwpiau';

$lang['field']       = 'Maes';
$lang['value']       = 'Gwerth';
$lang['add']         = 'Ychwanegu';
$lang['delete']      = 'Dileu';
$lang['delete_selected'] = 'Dileu\'r Dewisiadau';
$lang['edit']        = 'Golygu';
$lang['edit_prompt'] = 'Golygu\'r defnyddiwr hwn';
$lang['modify']      = 'Cadw Newidiadau';
$lang['search']      = 'Chwilio';
$lang['search_prompt'] = 'Perfformio chwiliad';
$lang['clear']       = 'Ailosod Hidlydd Chwilio';
$lang['filter']      = 'Hidlo';
$lang['export_all']  = 'Allforio Pob Defnyddiwr (CSV)';
$lang['export_filtered'] = 'Allforio Rhestr Defnyddwyr wedi\'u Hidlo (CSV)';
$lang['import']      = 'Mewnforio Defnyddwyr Newydd';
$lang['line']        = 'Llinell rhif';
$lang['error']       = 'Gwallneges';

$lang['summary']     = 'Yn dangos %1$d-%2$d defnyddiwr allan o %3$d wedi\'u darganfod. %4$d defnyddiwr yn gyfan gwbl.';
$lang['nonefound']   = 'Dim defnyddwyr wedi\'u darganfod. %d defnyddiwr yn gyfan gwbl.';
$lang['delete_ok']   = 'Dilëwyd %d defnyddiwr';
$lang['delete_fail'] = 'Dileu %d wedi methu.';
$lang['update_ok']   = 'Diweddarwyd y defnyddiwr yn llwyddiannus';
$lang['update_fail'] = 'Methodd diweddariad y defnyddiwr';
$lang['update_exists'] = 'Methodd newid y defnyddair, mae\'r defnyddair hwnnw (%s) yn bodoli eisoes (caiff pob newid arall ei gyflwyno).';

$lang['start']  = 'dechrau';
$lang['prev']   = 'blaenorol';
$lang['next']   = 'nesaf';
$lang['last']   = 'diwethaf';

// added after 2006-03-09 release
$lang['edit_usermissing'] = 'Methu darganfod y defnyddiwr hwn. Efallai bod y defnyddair hwn wedi\'i ddileu neu wedi\'i newid mewn man arall.';
$lang['user_notify'] = 'Hysbysu defnyddiwr';
$lang['note_notify'] = 'Bydd ebyst hysbysu eu hanfon dim ond os ydy defnyddiwr yn derbyn cyfrinair newydd.';
$lang['note_group'] = 'Bydd defnyddwyr newydd yn cael eu hychwanegu i\'r grŵp diofyn (%s) os na chaiff grŵp ei enwi.';
$lang['note_pass'] = 'Caiff y cyfrinair ei generadu\'n awtomatig os caiff y maes ei adael yn wag a bod hysbysu\'r defnyddiwr wedi\'i alluogi.';
$lang['add_ok'] = 'Ychwanegwyd y defnyddiwr yn llwyddiannus';
$lang['add_fail'] = 'Methodd ychwanegu defnyddiwr';
$lang['notify_ok'] = 'Anfonwyd yr ebost hysbysu';
$lang['notify_fail'] = 'Doedd dim modd anfon yr ebost hysbysu';

// import & errors
$lang['import_userlistcsv'] = 'Ffeil rhestr defnyddwyr (CSV):  ';
$lang['import_header'] = 'Mewnforiad Diweddaraf - Methiannau';
$lang['import_success_count'] = 'Mewnforio Defnyddwyr: darganfuwyd %d defnyddiwr, mewnforiwyd %d yn llwyddiannus.';
$lang['import_failure_count'] = 'Mewnforio Defnyddwyr: methodd %d. Rhestrwyd y methiannau isod.';
$lang['import_error_fields']  = "Meysydd annigonol, darganfuwyd %d, angen 4.";
$lang['import_error_baduserid'] = "Id-defnyddiwr ar goll";
$lang['import_error_badname'] = 'Enw gwael';
$lang['import_error_badmail'] = 'Cyfeiriad ebost gwael';
$lang['import_error_upload']  = 'Methodd y Mewnforiad. Doedd dim modd lanlwytho\'r ffeil neu roedd yn wag.';
$lang['import_error_readfail'] = 'Methodd y Mewnforiad. Methu â darllen y ffeil a lanlwythwyd.';
$lang['import_error_create']  = 'Methu â chreu\'r defnyddiwr';
$lang['import_notify_fail']   = 'Doedd dim modd anfon neges hysbysu i\'r defyddiwr a fewnforiwyd, %s gydag ebost %s.';
$lang['import_downloadfailures'] = 'Lawlwytho Methiannau fel CSV er mwyn cywiro';

$lang['addUser_error_missing_pass'] = 'Gosodwch gyfrinair neu trowch hysbysu defnyddwyr ymlaen i alluogi generadu cyfrineiriau.';
$lang['addUser_error_pass_not_identical'] = '\'Dyw\'r cyfrineiriau hyn ddim yn cydweddu.';
$lang['addUser_error_modPass_disabled'] = 'Mae newid cyfrineiriau wedi\'i analluogi\'n bresennol.';
$lang['addUser_error_name_missing'] = 'Rhowch enw ar gyfer y defnyddiwr newydd.';
$lang['addUser_error_modName_disabled'] = 'Mae newid enwau wedi\'i analluogi\'n bresennol.';
$lang['addUser_error_mail_missing'] = 'Rhowch gyfeiriad ebost ar gyfer y defnyddiwr newydd.';
$lang['addUser_error_modMail_disabled'] = 'Mae newid cyfeiriadau ebost wedi\'i analluogi\'n bresennol.';
$lang['addUser_error_create_event_failed'] = 'Mae ategyn wedi atal ychwanegu\'r defnyddiwr newydd. Adolygwch negeseuon ychwanegol bosib am wybodaeth bellach.';
