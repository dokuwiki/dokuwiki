<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author take <take@ruu.kr>
 * @author merefox <admin@homerecz.com>
 * @author pavement <pavement@rael.cc>
 * @author Traend <Traend@ruu.kr>
 * @author Seungheon Song <esketch@gmail.com>
 * @author jk Lee
 * @author dongnak <dongnak@gmail.com>
 * @author Song Younghwan <purluno@gmail.com>
 * @author Seung-Chul Yoo <dryoo@live.com>
 * @author erial2 <erial2@gmail.com>
 * @author Myeongjin <aranet100@gmail.com>
 * @author S.H. Lee <tuders@naver.com>
 */
$lang['menu']                  = '환경 설정';
$lang['error']                 = '잘못된 값 때문에 설정을 바꿀 수 없습니다, 바뀜을 검토하고 다시 제출하세요.
                       <br />잘못된 값은 빨간 선으로 둘러싸여 보여집니다.';
$lang['updated']               = '설정이 성공적으로 바뀌었습니다.';
$lang['nochoice']              = '(다른 선택은 할 수 없습니다)';
$lang['locked']                = '설정 파일을 바꿀 수 없습니다, 의도하지 않았다면, <br />
                       로컬 설정 파일 이름과 권한이 맞는지 확인하세요.';
$lang['danger']                = '위험: 이 옵션을 바꾸면 위키와 환경 설정 메뉴에 접근할 수 없을 수도 있습니다.';
$lang['warning']               = '경고: 이 옵션을 바꾸면 의도하지 않는 동작을 일으킬 수 있습니다.';
$lang['security']              = '보안 경고: 이 옵션을 바꾸면 보안 위험이 있을 수 있습니다.';
$lang['_configuration_manager'] = '환경 설정 관리자';
$lang['_header_dokuwiki']      = '도쿠위키';
$lang['_header_plugin']        = '플러그인';
$lang['_header_template']      = '템플릿';
$lang['_header_undefined']     = '정의되지 않은 설정';
$lang['_basic']                = '기본';
$lang['_display']              = '보이기';
$lang['_authentication']       = '인증';
$lang['_anti_spam']            = '스팸 방지';
$lang['_editing']              = '편집';
$lang['_links']                = '링크';
$lang['_media']                = '미디어';
$lang['_notifications']        = '알림';
$lang['_syndication']          = '신디케이션 (RSS)';
$lang['_advanced']             = '고급';
$lang['_network']              = '네트워크';
$lang['_msg_setting_undefined'] = '설정에 메타데이터가 없습니다.';
$lang['_msg_setting_no_class'] = '설정에 클래스가 없습니다.';
$lang['_msg_setting_no_default'] = '기본값이 없습니다.';
$lang['title']                 = '위키 제목 (위키 이름)';
$lang['start']                 = '각 이름공간에 시작점으로 사용할 문서 이름';
$lang['lang']                  = '인터페이스 언어';
$lang['template']              = '템플릿 (위키 디자인)';
$lang['tagline']               = '태그라인 (템플릿이 지원할 경우)';
$lang['sidebar']               = '사이드바 문서 이름 (템플릿이 지원할 경우), 필드를 비우면 사이드바를 비활성화';
$lang['license']               = '내용을 배포할 때 어떤 라이선스에 따라야 합니까?';
$lang['savedir']               = '데이터를 저장할 디렉터리';
$lang['basedir']               = '서버 경로 (예 <code>/dokuwiki/</code>). 자동 감지를 하려면 비워 두세요.';
$lang['baseurl']               = '서버 URL (예 <code>http://www.yourserver.com</code>). 자동 감지를 하려면 비워 두세요.';
$lang['cookiedir']             = '쿠키 경로. 기본 URL 위치로 지정하려면 비워 두세요.';
$lang['dmode']                 = '디렉터리 만들기 모드';
$lang['fmode']                 = '파일 만들기 모드';
$lang['allowdebug']            = '디버그 허용. <b>필요하지 않으면 비활성화하세요!</b>';
$lang['recent']                = '최근 바뀜에서 문서당 항목 수';
$lang['recent_days']           = '최근 바뀜을 유지할 기한 (일)';
$lang['breadcrumbs']           = '이동 경로 "추적" 수. 비활성화하려면 0으로 설정하세요.';
$lang['youarehere']            = '계층적 이동 경로 사용 (다음에 위 옵션을 비활성화하기를 원할 겁니다)';
$lang['fullpath']              = '바닥글에 문서의 전체 경로 밝히기';
$lang['typography']            = '타이포그래피 대체';
$lang['dformat']               = '날짜 형식 (PHP의 <a href="http://php.net/strftime">strftime</a> 함수 참고)';
$lang['signature']             = '편집기에서 서명 버튼을 누를 때 넣을 내용';
$lang['showuseras']            = '문서를 마지막으로 편집한 사용자를 보여줄지 여부';
$lang['toptoclevel']           = '목차의 최상위 단계';
$lang['tocminheads']           = '목차를 넣을 여부를 결정할 최소 문단 수';
$lang['maxtoclevel']           = '목차의 최대 단계';
$lang['maxseclevel']           = '문단의 최대 편집 단계';
$lang['camelcase']             = '링크에 CamelCase 사용';
$lang['deaccent']              = '문서 이름을 지우는 방법';
$lang['useheading']            = '문서 이름을 첫 문단 제목으로 사용';
$lang['sneaky_index']          = '기본적으로, 도쿠위키는 사이트맵에 모든 이름공간을 보여줍니다. 이 옵션을 활성화하면 사용자가 읽기 권한이 없는 이름공간을 숨기게 됩니다. 특정 ACL 설정으로 색인을 사용할 수 없게 할 수 있는 접근할 수 있는 하위 이름공간을 숨기면 설정됩니다.';
$lang['hidepages']             = '검색, 사이트맵 및 다른 자동 색인에서 이 정규 표현식과 일치하는 문서 숨기기';
$lang['useacl']                = '접근 제어 목록 (ACL) 사용';
$lang['autopasswd']            = '자동 생성 비밀번호';
$lang['authtype']              = '인증 백엔드';
$lang['passcrypt']             = '비밀번호 암호화 방법';
$lang['defaultgroup']          = '기본 그룹, 모든 새 사용자는 이 그룹에 속하게 됩니다';
$lang['superuser']             = '슈퍼유저 - ACL 설정과 상관없이 모든 문서와 기능에 완전히 접근할 수 있는 그룹, 사용자 또는 쉼표로 구분된 목록 사용자1,@그룹1,사용자2';
$lang['manager']               = '관리자 - 특정 관리 기능에 접근할 수 있는 그룹, 사용자 또는 쉼표로 구분된 목록 사용자1,@그룹1,사용자2';
$lang['profileconfirm']        = '프로필을 바꿀 때 비밀번호로 확인';
$lang['rememberme']            = '영구적으로 로그인 쿠키 허용 (기억하기)';
$lang['disableactions']        = '도쿠위키 동작 비활성화';
$lang['disableactions_check']  = '검사';
$lang['disableactions_subscription'] = '구독/구독 취소';
$lang['disableactions_wikicode'] = '원본 보기/원본 내보내기';
$lang['disableactions_profile_delete'] = '자신의 계정 삭제';
$lang['disableactions_other']  = '다른 동작 (쉼표로 구분)';
$lang['disableactions_rss']    = 'XML 신디케이션 (RSS)';
$lang['auth_security_timeout'] = '인증 보안 시간 초과 (초)';
$lang['securecookie']          = 'HTTPS를 통해 설정된 쿠키는 HTTPS를 통해서만 보내져야 합니까? 위키 로그인에만 SSL로 보호하고 위키를 둘러보는 것에는 보호하지 않게 하려면 이 옵션을 비활성화하세요.';
$lang['remote']                = '원격 API 시스템 활성화. 다른 어플리케이션이 XML-RPC 또는 다른 메커니즘을 통해 위키에 접근할 수 있습니다.';
$lang['remoteuser']            = '여기에 입력한 쉼표로 구분된 그룹 또는 사용자에게 원격 API 접근을 제한합니다. 모두에게 접근 권한을 주려면 비워 두세요.';
$lang['usewordblock']          = '낱말 목록을 바탕으로 스팸 막기';
$lang['relnofollow']           = '바깥 링크에 rel="nofollow" 사용';
$lang['indexdelay']            = '색인 전 지연 시간 (초)';
$lang['mailguard']             = '이메일 주소를 알아볼 수 없게 하기';
$lang['iexssprotect']          = '올린 파일의 악성 자바스크립트, HTML 코드 가능성 여부를 검사';
$lang['usedraft']              = '편집하는 동안 자동으로 초안 저장';
$lang['locktime']              = '파일 잠그기에 대한 최대 시간 (초)';
$lang['cachetime']             = '캐시에 대한 최대 시간 (초)';
$lang['target____wiki']        = '안쪽 링크에 대한 타겟 창';
$lang['target____interwiki']   = '인터위키 링크에 대한 타겟 창';
$lang['target____extern']      = '바깥 링크에 대한 타겟 창';
$lang['target____media']       = '미디어 링크에 대한 타겟 창';
$lang['target____windows']     = 'Windows 링크에 대한 타겟 창';
$lang['mediarevisions']        = '미디어 판을 활성화하겠습니까?';
$lang['refcheck']              = '미디어 파일을 삭제하기 전에 아직 사용하고 있는지 검사';
$lang['gdlib']                 = 'GD 라이브러리 버전';
$lang['im_convert']            = 'ImageMagick의 변환 도구의 경로';
$lang['jpg_quality']           = 'JPG 압축 품질 (0-100)';
$lang['fetchsize']             = 'fetch.php가 바깥 URL에서 다운로드할 수 있는 최대 크기 (바이트), 예를 들어 바깥 그림을 캐시하고 크기 조절할 때.';
$lang['subscribers']           = '사용자가 이메일로 문서 바뀜을 구독할 수 있도록 하기';
$lang['subscribe_time']        = '구독 목록과 요약이 보내질 경과 시간 (초); recent_days에 지정된 시간보다 작아야 합니다.';
$lang['notify']                = '항상 이 이메일 주소로 바뀜 알림을 보냄';
$lang['registernotify']        = '항상 이 이메일 주소로 새로 등록한 사용자의 정보를 보냄';
$lang['mailfrom']              = '자동으로 보내는 메일에 사용할 보내는 사람 이메일 주소';
$lang['mailreturnpath']        = '배달 불가 안내를 위한 수신자 메일 주소';
$lang['mailprefix']            = '자동으로 보내는 메일에 사용할 이메일 제목 접두어. 위키 제목을 사용하려면 비워 두세요';
$lang['htmlmail']              = '보기에는 더 좋지만 크키가 조금 더 큰 HTML 태그가 포함된 이메일을 보내기. 일반 텍스트만으로 된 메일을 보내려면 비활성화하세요.';
$lang['sitemap']               = 'Google 사이트맵 생성 날짜 빈도 (일). 비활성화하려면 0';
$lang['rss_type']              = 'XML 피드 형식';
$lang['rss_linkto']            = 'XML 피드 링크 정보';
$lang['rss_content']           = 'XML 피드 항목에 보여주는 내용은 무엇입니까?';
$lang['rss_update']            = 'XML 피드 업데이트 간격 (초)';
$lang['rss_show_summary']      = 'XML 피드의 제목에서 요악 보여주기';
$lang['rss_media']             = '어떤 규격으로 XML 피드에 바뀜을 나열해야 합니까?';
$lang['rss_media_o_both']      = '양방향';
$lang['rss_media_o_pages']     = '쪽';
$lang['rss_media_o_media']     = '미디어';
$lang['updatecheck']           = '업데이트와 보안 경고를 검사할까요? 도쿠위키는 이 기능을 위해 update.dokuwiki.org에 연결이 필요합니다.';
$lang['userewrite']            = '멋진 URL 사용';
$lang['useslash']              = 'URL에서 이름공간 구분자로 슬래시 사용';
$lang['sepchar']               = '문서 이름 낱말 구분자';
$lang['canonical']             = '완전한 canonical URL 사용';
$lang['fnencode']              = 'ASCII가 아닌 파일 이름을 인코딩하는 방법.';
$lang['autoplural']            = '링크에서 복수형 검사';
$lang['compression']           = '첨부 파일의 압축 방법';
$lang['gzip_output']           = 'xhtml에 대해 gzip 내용 인코딩 사용';
$lang['compress']              = 'CSS 및 자바스크립트를 압축하여 출력';
$lang['cssdatauri']            = 'CSS 파일에서 그림이 참조되는 최대 바이트 크기를 스타일시트에 규정해야 HTTP 요청 헤더 오버헤드 크기를 줄일 수 있습니다. <code>400</code>에서 <code>600</code> 바이트 정도면 좋은 효율을 가져옵니다. 비활성화하려면 <code>0</code>으로 설정하세요.';
$lang['send404']               = '존재하지 않는 문서에 "HTTP 404/페이지를 찾을 수 없습니다" 보내기';
$lang['broken_iua']            = '시스템에서 ignore_user_abort 함수에 문제가 있습니까? 문제가 있다면 검색 색인이 동작하지 않는 원인이 됩니다. 이 함수가 IIS+PHP/CGI에서 문제가 있는 것으로 알려져 있습니다. 자세한 정보는 <a href="http://bugs.dokuwiki.org/?do=details&amp;task_id=852">버그 852</a>를 참조하시기 바랍니다.';
$lang['xsendfile']             = '웹 서버가 정적 파일을 제공할 수 있도록 X-Sendfile 헤더를 사용하겠습니까? 웹 서버가 이 기능을 지원해야 합니다.';
$lang['renderer_xhtml']        = '주요 (xhtml) 위키 출력에 사용할 렌더러';
$lang['renderer__core']        = '%s (도쿠위키 코어)';
$lang['renderer__plugin']      = '%s (플러그인)';
$lang['search_nslimit']        = '검색을 현재 X 네임스페이스로 제한하십시오. 더 깊은 네임스페이스 내의 페이지에서 검색을 실행하면 첫 번째 X 네임스페이스가 필터로 추가됩니다.';
$lang['search_fragment_o_exact'] = '정확한';
$lang['dnslookups']            = '도쿠위키가 문서를 편집하는 사용자의 원격 IP 주소에 대한 호스트 이름을 조회합니다. 서버가 느리거나 DNS 서버를 작동하지 않거나 이 기능을 원하지 않으면, 이 옵션을 비활성화하세요';
$lang['jquerycdn']             = '제이쿼리(jQuery)와 제이쿼리UI 스크립트 파일을 컨텐츠전송네트워크(CDN)에서 불러와야만 합니까? 이것은 추가적인 HTTP요청을 합니다. 하지만 파일이 빨리 불러지고 캐쉬에 저장되게 할 수 있습니다.';
$lang['jquerycdn_o_0']         = '컨텐츠전송네트워크(CDN) 사용 안 함. 로컬 전송만 함';
$lang['jquerycdn_o_jquery']    = '\'code.jquery.com\' 의 컨텐츠전송네트워크(CDN) 사용';
$lang['jquerycdn_o_cdnjs']     = '\'cdnjs.com\' 의 컨텐츠전송네트워크(CDN) 사용';
$lang['proxy____host']         = '프록시 서버 이름';
$lang['proxy____port']         = '프록시 포트';
$lang['proxy____user']         = '프록시 사용자 이름';
$lang['proxy____pass']         = '프록시 비밀번호';
$lang['proxy____ssl']          = '프록시로 연결하는 데 SSL 사용';
$lang['proxy____except']       = '프록시가 건너뛰어야 할 일치하는 URL의 정규 표현식.';
$lang['license_o_']            = '선택하지 않음';
$lang['typography_o_0']        = '없음';
$lang['typography_o_1']        = '작은따옴표를 제외';
$lang['typography_o_2']        = '작은따옴표를 포함 (항상 동작하지 않을 수도 있음)';
$lang['userewrite_o_0']        = '없음';
$lang['userewrite_o_1']        = '.htaccess';
$lang['userewrite_o_2']        = '도쿠위키 내부';
$lang['deaccent_o_0']          = '끄기';
$lang['deaccent_o_1']          = '악센트 제거';
$lang['deaccent_o_2']          = '로마자화';
$lang['gdlib_o_0']             = 'GD 라이브러리를 사용할 수 없음';
$lang['gdlib_o_1']             = '버전 1.x';
$lang['gdlib_o_2']             = '자동 감지';
$lang['rss_type_o_rss']        = 'RSS 0.91';
$lang['rss_type_o_rss1']       = 'RSS 1.0';
$lang['rss_type_o_rss2']       = 'RSS 2.0';
$lang['rss_type_o_atom']       = 'Atom 0.3';
$lang['rss_type_o_atom1']      = 'Atom 1.0';
$lang['rss_content_o_abstract'] = '개요';
$lang['rss_content_o_diff']    = '통합 차이';
$lang['rss_content_o_htmldiff'] = 'HTML 형식의 차이 표';
$lang['rss_content_o_html']    = '전체 HTML 페이지 내용';
$lang['rss_linkto_o_diff']     = '차이 보기';
$lang['rss_linkto_o_page']     = '개정된 문서';
$lang['rss_linkto_o_rev']      = '판의 목록';
$lang['rss_linkto_o_current']  = '현재 문서';
$lang['compression_o_0']       = '없음';
$lang['compression_o_gz']      = 'gzip';
$lang['compression_o_bz2']     = 'bz2';
$lang['xsendfile_o_0']         = '사용하지 않음';
$lang['xsendfile_o_1']         = '사유 lighttpd 헤더 (릴리스 1.5 이전)';
$lang['xsendfile_o_2']         = '표준 X-Sendfile 헤더';
$lang['xsendfile_o_3']         = '사유 Nginx X-Accel-Redirect 헤더';
$lang['showuseras_o_loginname'] = '로그인 이름';
$lang['showuseras_o_username'] = '사용자의 실명';
$lang['showuseras_o_username_link'] = '인터위키 사용자 링크로 된 사용자의 실명';
$lang['showuseras_o_email']    = '사용자의 이메일 주소 (메일 주소 설정에 따라 안보일 수 있음)';
$lang['showuseras_o_email_link'] = 'mailto: 링크로 된 사용자의 이메일 주소';
$lang['useheading_o_0']        = '전혀 없음';
$lang['useheading_o_navigation'] = '둘러보기에만';
$lang['useheading_o_content']  = '위키 내용에만';
$lang['useheading_o_1']        = '항상';
$lang['readdircache']          = 'readdir 캐시의 최대 시간 (초)';
