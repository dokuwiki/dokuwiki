<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Myeongjin <aranet100@gmail.com>
 */
$lang['server']                = 'LDAP 서버. 호스트 이름(<code>localhost</code>)이나 전체 자격 URL(<code>ldap://server.tld:389</code>) 중 하나';
$lang['port']                  = '위에 주어진 전체 URL이 없을 때의 LDAP 서버 포트';
$lang['usertree']              = '사용자 계정을 찾을 장소. 예를 들어 <code>ou=People, dc=server, dc=tld</code>';
$lang['grouptree']             = '사용자 그룹을 찾을 장소. 예를 들어 <code>ou=Group, dc=server, dc=tld</code>';
$lang['userfilter']            = '사용자 계정을 찾을 LDAP 필터. 예를 들어 <code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>';
$lang['groupfilter']           = '그룹을 찾을 LDAP 필터. 예를 들어 <code>(&amp;(objectClass=posixGroup)(|(gidNumber=%{gid})(memberUID=%{user})))</code>';
$lang['version']               = '사용할 프로토콜 버전. <code>3</code>으로 설정해야 할 수도 있습니다';
$lang['starttls']              = 'TLS 연결을 사용하겠습니까?';
$lang['referrals']             = '참조(referrals)를 허용하겠습니까? ';
$lang['deref']                 = '어떻게 별명을 간접 참조하겠습니까?';
$lang['binddn']                = '익명 바인드가 충분하지 않으면 선택적인 바인드 사용자의 DN. 예를 들어 <code>cn=admin, dc=my, dc=home</code>';
$lang['bindpw']                = '위 사용자의 비밀번호';
$lang['userscope']             = '사용자 검색에 대한 검색 범위 제한';
$lang['groupscope']            = '그룹 검색에 대한 검색 범위 제한';
$lang['userkey']               = '사용자 이름을 나타내는 특성; 사용자 필터에 일관성이 있어야 합니다.';
$lang['groupkey']              = '(표준 AD 그룹 대신) 사용자 속성에서 그룹 구성원. 예를 들어 부서나 전화에서 그룹';
$lang['modPass']               = 'LDAP 비밀번호를 도쿠위키를 통해 바꿀 수 있습니까?';
$lang['debug']                 = '오류에 대한 추가적인 디버그 정보를 보이기';
$lang['deref_o_0']             = 'LDAP_DEREF_NEVER';
$lang['deref_o_1']             = 'LDAP_DEREF_SEARCHING';
$lang['deref_o_2']             = 'LDAP_DEREF_FINDING';
$lang['deref_o_3']             = 'LDAP_DEREF_ALWAYS';
$lang['referrals_o_-1']        = '기본값 사용';
$lang['referrals_o_0']         = '참조 (referral)를 따르지 않음';
$lang['referrals_o_1']         = '참조 (referral)를 따름';
