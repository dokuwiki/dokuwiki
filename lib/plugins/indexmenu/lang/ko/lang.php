<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Myeongjin <aranet100@gmail.com>
 * @author Erial <erial2@gmail.com>
 */
$lang['menu']                  = 'Indexmenu 유틸리티';
$lang['fetch']                 = '보이기';
$lang['install']               = '설치';
$lang['delete']                = '삭제';
$lang['check']                 = '확인';
$lang['no_repos']              = '설정된 테마 저장소 url이 없습니다.';
$lang['disabled']              = '비활성화';
$lang['conn_err']              = '연결 오류';
$lang['dir_err']               = '테마를 받을 임시 폴더를 만들 수 없습니다';
$lang['down_err']              = '테마를 받을 수 없습니다';
$lang['zip_err']               = 'Zip 만들기나 압축 풀기 오류';
$lang['install_ok']            = '테마가 성공적으로 설치되었습니다. 새 테마는 편집 페이지의 도구 모음이나 <code>js#theme_name option</code>으로 사용할 수 있습니다.';
$lang['install_no']            = '연결 오류입니다. 그러나 <a href="http://samuele.netsons.org/dokuwiki/lib/plugins/indexmenu/upload/">여기</a>에서 수동으로 테마를 올려볼 수 있습니다.';
$lang['delete_ok']             = '테마가 성공적으로 삭제되었습니다.';
$lang['delete_no']             = '테마를 삭제하는 도중 오류가 발생했습니다';
$lang['upload']                = '공유';
$lang['checkupdates']          = '플러그인 업데이트';
$lang['noupdates']             = 'Indexmenu를 업데이트할 필요가 없습니다. 이미 최신 배포판이 있습니다:';
$lang['infos']                 = '<a href="https://www.dokuwiki.org/plugin:indexmenu#theme_tutorial">테마 자습서</a> 문서에서 지시에 따라 테마를 만들 수 있습니다. <br />다음으로 해당 테마를 "공유" 버튼으로, 공개 Indexmenu 저장소에 보내서 더 많은 사람을 행복하게 :-) 할 수 있습니다.';
$lang['showsort']              = 'Indexmenu 정렬 숫자:';
$lang['donation_text']         = 'Indexmenu 플러그인은 누군가의 후원을 받지 않지만 플러그인을 개발하고 틈틈이 무료로 지원합니다. 무언가 감사를 얻었거나 그 개발을 지원하려면, 기부를 고려할 수 있습니다.';
$lang['js']['indexmenuwizard'] = 'Indexmenu 마법사';
$lang['js']['index']           = '색인';
$lang['js']['options']         = '설정';
$lang['js']['navigation']      = '둘러보기';
$lang['js']['sort']            = '정렬';
$lang['js']['filter']          = '필터';
$lang['js']['performance']     = '성능';
$lang['js']['namespace']       = '이름공간';
$lang['js']['nsdepth']         = '깊이';
$lang['js']['js']              = '자바스크립트에 의해 렌더되는 트리로, 자신의 테마를 정의할 수 있습니다';
$lang['js']['theme']           = '테마';
$lang['js']['navbar']          = '트리는 현재 이름공간에서 엽니다';
$lang['js']['context']         = '현재 위키 이름공간 문맥의 트리를 표시합니다';
$lang['js']['nocookie']        = '사용자가 둘러보는 동안 열린/닫힌 노드를 기억하지 않습니다';
$lang['js']['noscroll']        = '트리의 컨테이너 너비에 맞추지 않으면 트리의 스크롤을 막습니다';
$lang['js']['notoc']           = '목차 미리 보기 기능을 비활성화합니다';
$lang['js']['tsort']           = '제목별';
$lang['js']['dsort']           = '날자별';
$lang['js']['msort']           = '메타 태그별';
$lang['js']['nsort']           = '이름공간도 정렬';
$lang['js']['hsort']           = '머릿문서 위에 정렬';
$lang['js']['rsort']           = '문서 정렬을 반대로';
$lang['js']['nons']            = '문서만 보기';
$lang['js']['nopg']            = '이름공간만 보기';
$lang['js']['max']             = '노드가 열릴 때 얼마나 많은 수준을 ajax로 렌더합니까. 추가로 해당 수준 아래에 얼마나 많은 하위 수준이 해당 문서를 보여주는 대신 AJAX로 검색됩니까.';
$lang['js']['maxjs']           = '노드가 열릴 때 얼마나 많은 수준이 클라이언트 브라우저에서 렌더합니까';
$lang['js']['id']              = 'Indexmenu에 대한 자기 정의된 쿠키 ID';
$lang['js']['insert']          = 'Indexmenu 넣기';
$lang['js']['metanum']         = '정렬에 대한 메타 숫자';
$lang['js']['insertmetanum']   = '메타숫자 넣기';
$lang['js']['page']            = '문서';
$lang['js']['revs']            = '판';
$lang['js']['tocpreview']      = '목차 미리 보기';
$lang['js']['editmode']        = '편집 모드';
$lang['js']['insertdwlink']    = 'DW링크 넣기';
$lang['js']['insertdwlinktooltip'] = '커서 위치에서 편집 상자에 이 문서의 링크를 넣기';
$lang['js']['ns']              = '이름공간';
$lang['js']['search']          = '검색 ...';
$lang['js']['searchtooltip']   = '이 이름공간 안의 문서를 검색';
$lang['js']['create']          = '만들기';
$lang['js']['more']            = '더 보기';
$lang['js']['headpage']        = '머릿문서';
$lang['js']['headpagetooltip'] = '이 문서 아래에 새 머릿문서 만들기';
$lang['js']['startpage']       = '시작 문서';
$lang['js']['startpagetooltip'] = '이 문서 아래에 새 시작 문서 만들기';
$lang['js']['custompage']      = '사용자 지정 문서';
$lang['js']['custompagetooltip'] = '이 문서 아래에 새 문서 만들기';
$lang['js']['acls']            = '접근 제어 목록';
$lang['js']['purgecache']      = '캐시 지우기';
$lang['js']['exporthtml']      = 'HTML로 내보내기';
$lang['js']['exporttext']      = '텍스트로 내보내기';
$lang['js']['headpagehere']    = '여기를 머릿문서로';
$lang['js']['headpageheretooltip'] = '이 이름공간 안에 새 머릿문서 만들기';
$lang['js']['newpage']         = '새 문서';
$lang['js']['newpagetooltip']  = '이 이름공간 안에 새 문서 만들기';
$lang['js']['newpagehere']     = '여기에 새 문서';
$lang['js']['insertkeywords']  = '이 이름공간 안에서 검색할 단어를 넣어주세요';
$lang['js']['insertpagename']  = '새로 만들 문서 이름을 넣어주세요';
$lang['js']['edit']            = '편집';
$lang['js']['loading']         = '불러오는 중...';
