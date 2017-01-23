<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Myeongjin <aranet100@gmail.com>
 * @author Garam <rowain8@gmail.com>
 */
$lang['account_suffix']        = '계정 접미어. 예를 들어 <code>@my.domain.org</code>';
$lang['base_dn']               = '기본 DN. 예를 들어 <code>DC=my,DC=domain,DC=org</code>';
$lang['domain_controllers']    = '도메인 컨트롤러의 쉼표로 구분한 목록. 예를 들어 <code>srv1.domain.org,srv2.domain.org</code>';
$lang['admin_username']        = '다른 모든 사용자의 데이터에 접근할 수 있는 권한이 있는 Active Directory 사용자. 선택적이지만 구독 메일을 보내는 등의 특정 작업에 필요합니다.';
$lang['admin_password']        = '위 사용자의 비밀번호.';
$lang['sso']                   = 'Kerberos나 NTLM을 통해 Single-Sign-On을 사용해야 합니까?';
$lang['sso_charset']           = '당신의 웹서버의 문자집합은 Kerberos나 NTLM 사용자 이름으로 전달됩니다. UTF-8이나 라린-1이 비어 있습니다. icov 확장 기능이 필요합니다.';
$lang['real_primarygroup']     = '실제 기본 그룹은 "도메인 사용자"를 가정하는 대신 해결될 것입니다. (느림)';
$lang['use_ssl']               = 'SSL 연결을 사용합니까? 사용한다면 아래 TLS을 활성화하지 마세요.';
$lang['use_tls']               = 'TLS 연결을 사용합니까? 사용한다면 위 SSL을 활성화하지 마세요.';
$lang['debug']                 = '오류에 대한 추가적인 디버그 정보를 보이겠습니까?';
$lang['expirywarn']            = '미리 비밀번호 만료를 사용자에게 경고할 날짜. 0일 경우 비활성화합니다.';
$lang['additional']            = '사용자 데이터에서 가져올 추가적인 AD 속성의 쉼표로 구분한 목록. 일부 플러그인이 사용합니다.';
$lang['update_name']           = '사용자가 자신의 AD 표시 이름을 업데이트할 수 있도록 하겠습니까?';
$lang['update_mail']           = '사용자가 자신의 이메일 주소를 업데이트할 수 있도록 하겠습니까?';
