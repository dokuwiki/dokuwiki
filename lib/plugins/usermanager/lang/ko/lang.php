<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author jk Lee
 * @author dongnak@gmail.com
 * @author Song Younghwan <purluno@gmail.com>
 * @author Seung-Chul Yoo <dryoo@live.com>
 * @author erial2@gmail.com
 * @author Myeongjin <aranet100@gmail.com>
 * @author Gerrit Uitslag <klapinklapin@gmail.com>
 * @author Garam <rowain8@gmail.com>
 * @author Erial <erial2@gmail.com>
 */
$lang['menu']                  = '사용자 관리자';
$lang['noauth']                = '(사용자 인증을 사용할 수 없습니다)';
$lang['nosupport']             = '(사용자 관리가 지원되지 않습니다)';
$lang['badauth']               = '인증 메커니즘이 잘못되었습니다';
$lang['user_id']               = '사용자';
$lang['user_pass']             = '비밀번호';
$lang['user_name']             = '실명';
$lang['user_mail']             = '이메일 ';
$lang['user_groups']           = '그룹';
$lang['field']                 = '항목';
$lang['value']                 = '값';
$lang['add']                   = '추가';
$lang['delete']                = '삭제';
$lang['delete_selected']       = '선택 삭제';
$lang['edit']                  = '편집';
$lang['edit_prompt']           = '이 사용자 편집';
$lang['modify']                = '바뀜 저장';
$lang['search']                = '검색';
$lang['search_prompt']         = '검색 수행';
$lang['clear']                 = '검색 필터 재설정';
$lang['filter']                = '필터';
$lang['export_all']            = '모든 사용자 목록 내보내기 (CSV)';
$lang['export_filtered']       = '필터된 사용자 목록 내보내기 (CSV)';
$lang['import']                = '새 사용자 가져오기';
$lang['line']                  = '줄 번호';
$lang['error']                 = '오류 메시지';
$lang['summary']               = '찾은 사용자 %3$d명 중 %1$d-%2$d을(를) 봅니다. 전체 사용자는 %4$d명입니다.';
$lang['nonefound']             = '찾은 사용자가 없습니다. 전체 사용자는 %d명입니다.';
$lang['delete_ok']             = '사용자 %d명이 삭제되었습니다';
$lang['delete_fail']           = '사용자 %d명을 삭제하는 데 실패했습니다.';
$lang['update_ok']             = '사용자 정보를 성공적으로 바꾸었습니다';
$lang['update_fail']           = '사용자 정보를 업데이트하는 데 실패했습니다';
$lang['update_exists']         = '사용자 이름을 바꾸는 데 실패했습니다. 사용자 이름(%s)이 이미 존재합니다. (다른 항목의 바뀜은 적용됩니다)';
$lang['start']                 = '시작';
$lang['prev']                  = '이전';
$lang['next']                  = '다음';
$lang['last']                  = '마지막';
$lang['edit_usermissing']      = '선택된 사용자를 찾을 수 없습니다, 사용자 이름이 삭제되거나 바뀌었을 수도 있습니다.';
$lang['user_notify']           = '사용자에게 알림';
$lang['note_notify']           = '사용자에게 새로운 비밀번호를 준 경우에만 알림 이메일이 보내집니다.';
$lang['note_group']            = '새로운 사용자는 어떤 그룹도 설정하지 않은 경우에 기본 그룹(%s)에 추가됩니다.';
$lang['note_pass']             = '사용자 알림이 지정되어 있을 때 필드에 아무 값도 입력하지 않으면 비밀번호가 자동으로 생성됩니다.';
$lang['add_ok']                = '사용자를 성공적으로 추가했습니다';
$lang['add_fail']              = '사용자 추가를 실패했습니다';
$lang['notify_ok']             = '알림 이메일을 성공적으로 보냈습니다';
$lang['notify_fail']           = '알림 이메일을 보낼 수 없습니다';
$lang['import_userlistcsv']    = '사용자 목록 파일 (CSV):';
$lang['import_header']         = '가장 최근 가져오기 - 실패';
$lang['import_success_count']  = '사용자 가져오기: 사용자 %d명을 찾았고, %d명을 성공적으로 가져왔습니다.';
$lang['import_failure_count']  = '사용자 가져오기: %d명을 가져오지 못했습니다. 실패는 아래에 나타나 있습니다.';
$lang['import_error_fields']   = '충분하지 않은 필드로, %d개를 찾았고, 4개가 필요합니다.';
$lang['import_error_baduserid'] = '사용자 ID 없음';
$lang['import_error_badname']  = '잘못된 이름';
$lang['import_error_badmail']  = '잘못된 이메일 주소';
$lang['import_error_upload']   = '가져오기를 실패했습니다. CSV 파일을 올릴 수 없거나 비어 있습니다.';
$lang['import_error_readfail'] = '가져오기를 실패했습니다. 올린 파일을 읽을 수 없습니다.';
$lang['import_error_create']   = '사용자를 만들 수 없습니다';
$lang['import_notify_fail']    = '알림 메시지를 가져온 %s (이메일: %s) 사용자에게 보낼 수 없습니다.';
$lang['import_downloadfailures'] = '교정을 위한 CSV로 다운로드 실패';
$lang['addUser_error_missing_pass'] = '비밀번호를 설정하거나 비밀번호 생성을 활성화하려면 사용자 알림을 활성화해주시기 바랍니다.';
$lang['addUser_error_pass_not_identical'] = '입력된 비밀번호가 일치하지 않습니다.';
$lang['addUser_error_modPass_disabled'] = '비밀번호를 수정하는 것은 현재 비활성화되어 있습니다.';
$lang['addUser_error_name_missing'] = '새 사용자의 이름을 입력하세요.';
$lang['addUser_error_modName_disabled'] = '이름을 수정하는 것은 현재 비활성화되어 있습니다.';
$lang['addUser_error_mail_missing'] = '새 사용자의 이메일 주소를 입력하세요.';
$lang['addUser_error_modMail_disabled'] = '이메일 주소를 수정하는 것은 현재 비활성화되어 있습니다.';
$lang['addUser_error_create_event_failed'] = '플러그인이 새 사용자가 추가되는 것을 막았습니다. 자세한 정보에 대해서는 가능한 다른 메시지를 검토하세요.';
