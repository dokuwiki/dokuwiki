<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Myeongjin <aranet100@gmail.com>
 * @author Erial <erial2@gmail.com>
 */
$lang['checkupdate']           = '업데이트를 정기적으로 확인합니다.';
$lang['only_admins']           = 'Indexmenu 문법을 관리자에게만 허용합니다.<br>관리자가 아닌 사용자가 편집한 문서는 매번 포함된 Indexmenu 트리가 사라짐에 주의하세요.';
$lang['aclcache']              = 'ACL에 대한 Indexmenu 캐시를 최적화합니다. (루트 요청된 이름공간에 대해서만 작동)<br>방법을 선택하는 것은 문서 권한이 아니라, Indexmenu 트리의 노드만 시각화에 영향을 줍니다.<ul><li>없음: 표준. 빠른 방법이고 추가적인 캐시 파일을 만들지 않지만, 거부된 권한이 있는 노드가 권한이 없는 사용자나 그 반대에게 보일 수 있습니다. ACL에 의한 문서 접근을 거부하지 않거나 트리를 표시하는 방법을 상관하지 않을 때 권장합니다.<li>사용자: 사용자마다 로그인. 느린 방법이고 캐시 파일을 많이 만들지만, 항상 올바르게 거부된 문서를 숨깁니다. 사용자 로그인에 의존하는 문서 ACL이 있을 때 권장합니다.<li>그룹: 그룹마다 구성원 자격. 이전의 두 방법 사이에 타협이 좋지만, 읽기 ACL 인증이 있는 그룹에 속하는 사용자에게 읽기 ACL을 거부하는 경우에, 어쨌든 트리에 있는 노드를 표시할 수 있습니다. 그룹 구성원에 의존하는 전체 사이트 ACL일 때 권장합니다.</ul>';
$lang['headpage']              = '머릿문서 메서드: 이름공간의 제목과 링크를 얻는 문서입니다.<br>다음 값 중 하나일 수 있습니다:<ul><li>전역 시작 문서입니다.<li>거기 안에 있는 이름공간 이름이 있는 문서입니다.<li>이름공간 이름과 같은 수준이 있는 문서입니다.<li>사용자 지정 이름 문서입니다.<li>문서 이름의 쉼표로 구분된 목록입니다.</ul>';
$lang['hide_headpage']         = '머릿문서를 숨깁니다.';
$lang['page_index']            = '주 도쿠위키 색인을 바꿀 문서. 문서를 만들고 Indexmenu 문법을 넣으세요. navbar 옵션으로 Indexmenu 사이드바가 이미 있으면 <code>id#random</code>을 사용하세요. 내 제안은 <code>{{indexmenu>..|js navbar nocookie id#random}}</code>입니다.';
$lang['empty_msg']             = '트리가 비어있을 때 보여줄 메시지. 도쿠위키 문법을 사용하고 html 코드를 사용하지 마세요. <code>{{ns}}</code> 변수는 요청한 이름공간에 대한 단축입니다.';
$lang['skip_index']            = '건너뛰는 이름공간 ID. 정규 표현식 형식을 사용하세요. 예: <code>/(사이트바|비공개:내이름공간)/</code>';
$lang['skip_file']             = '건너뛰는 문서 ID. 정규 표현식 형식을 사용하세요. 예: <code>/(:start$|^public:newstart$)/</code>';
$lang['show_sort']             = '문서 노드의 위로 Indexmenu 정렬 숫자를 관리자에게 보이기';
$lang['themes_url']            = '이 http url에서 js 테마를 다운로드합니다.';
$lang['be_repo']               = '다른 사용자가 사이트에서 테마를 다운로드할 수 있습니다.';
$lang['defaultoptions']        = 'indexmenu의 설정은 공간으로 나뉩니다. 설정된 내용은 플러그인 문법에 따라 명령이 지정된 경우를 제외한 모든 indexmenu에 기본으로 적용됩니다';
