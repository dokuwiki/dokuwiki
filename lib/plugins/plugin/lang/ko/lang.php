<?php
/**
 * korean language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 * @author     jk Lee <bootmeta@gmail.com>
 */

$lang['menu'] = '플러그인 관리자';

// custom language strings for the plugin
$lang['download'] = "새로운 플러그인 다운로드 및 설치";
$lang['manage'] = "이미 설치된 플러그인들";

$lang['btn_info'] = '정보';
$lang['btn_update'] = '갱신';
$lang['btn_delete'] = '삭제';
$lang['btn_settings'] = '설정';
$lang['btn_download'] = '다운로드';
$lang['btn_enable'] = '저장';

$lang['url']              = 'URL';

$lang['installed']        = '설치된:';
$lang['lastupdate']       = '가장 최근에 갱신된:';
$lang['source']           = '소스:';
$lang['unknown']          = '알 수 없는';

// ..ing = header message
// ..ed = success message

$lang['updating']         = '갱신 중 ...';
$lang['updated']          = '%s 플러그인이 성공적으로 갱신되었습니다.';
$lang['updates']          = '다음 플러그인들이 성공적으로 갱신되었습니다:';
$lang['update_none']      = '갱신 가능한 플러그인이 없습니다.';

$lang['deleting']         = '삭제 중 ...';
$lang['deleted']          = '%s 플러그인이 삭제되었습니다.';

$lang['downloading']      = '다운로드 중 ...';
$lang['downloaded']       = '%s 플러그인이 성공적으로 설치되었습니다.';
$lang['downloads']        = '다음 플러그인들이 성공적으로 설치되었습니다:';
$lang['download_none']    = '플러그인이 없거나 다운로드/설치 중에 알수 없는 문제가 발생했습니다.';

// info titles
$lang['plugin']           = '플러그인:';
$lang['components']       = '콤퍼넌트들';
$lang['noinfo']           = '이 플러그인은 어떤 정보도 없습니다. 유효한 플러그인이 아닐 지도 모릅니다.';
$lang['name']             = '이름:';
$lang['date']             = '날짜:';
$lang['type']             = '타입:';
$lang['desc']             = '설명:';
$lang['author']           = '제작자:';
$lang['www']              = '웹:';

// error messages
$lang['error']            = '알 수 없는 문제가 발생했습니다.';
$lang['error_download']   = '플러그인 파일을 다운로드 할 수 없습니다: %s';
$lang['error_badurl']     = '잘못된 URL같습니다. - URL에서 파일 이름을 알 수 없습니다.';
$lang['error_dircreate']  = '다운로드를 받기 위한 임시 디렉토리를 만들 수 없습니다.';
$lang['error_decompress'] = '플러그인 매니저가 다운로드 받은 파일을 압축해제할 수 없습니다.'.
                            '잘못 다운로드 받았을 수도 있으니 다시 한번 시도해보기 바랍니다; '.
                            '압축 포맷을 알 수 없는 경우에는 다운로드 후 수동으로 직접 설치하기 바랍니다.';
$lang['error_copy']       = '플러그인 설치하는 동안 파일 복사 에러가 발생했습니다. '.
                            '<em>%s</em>: 디스크가 꽉 찼거나 파일 접근 권한이 잘못된 경우입니다. '.
                            '플러그인 설치가 부분적으로만 이루어졌을 것입니다.'.
                            '설치가 불완전합니다.';
$lang['error_delete']     = '<em>%s</em> 플러그인 삭제 도중 에러가 발생했습니다. '.
                            '대부분의 경우, 불완전한 파일이거나 디렉토리 접근 권한이 잘못된 경우입니다.';

//Setup VIM: ex: et ts=4 enc=utf-8 :
