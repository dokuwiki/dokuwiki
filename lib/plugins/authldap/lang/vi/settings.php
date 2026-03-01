<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Thien Hau <thienhau.9a14@gmail.com>
 */
$lang['server']                = 'Máy chủ LDAP của bạn. Tên máy chủ (<code>localhost</code>) hoặc URL đầy đủ (<code>ldap://server.tld:389</code>)';
$lang['port']                  = 'Cổng máy chủ LDAP nếu không có URL đầy đủ được đưa ra bên trên';
$lang['usertree']              = 'Nơi tìm tài khoản thành viên. VD. <code>ou=People, dc=server, dc=tld</code>';
$lang['grouptree']             = 'Nơi tìm nhóm thành viên. VD. <code>ou=Group, dc=server, dc=tld</code>';
$lang['userfilter']            = 'Bộ lọc LDAP để tìm kiếm tài khoản thành viên. VD. <code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>';
$lang['groupfilter']           = 'Bộ lọc LDAP để tìm kiếm nhóm thành viên. VD. <code>(&amp;(objectClass=posixGroup)(|(gidNumber=%{gid})(memberUID=%{user})))</code>';
$lang['version']               = 'Phiên bản giao thức sử dụng. Bạn có thể cần đặt cái này thành <code>3</code>';
$lang['starttls']              = 'Sử dụng kết nối TLS?';
$lang['referrals']             = 'Theo dõi Shall referrals?';
$lang['deref']                 = 'Làm thế nào để xóa bỏ bí danh?';
$lang['binddn']                = 'DN của một thành viên liên kết tùy chọn nếu liên kết ẩn danh là không đủ. VD. <code>cn=admin, dc=my, dc=home</code>';
$lang['bindpw']                = 'Mật khẩu của thành viên trên';
$lang['attributes']            = 'Các thuộc tính truy xuất với tìm kiếm LDAP.';
$lang['userscope']             = 'Giới hạn phạm vi tìm kiếm cho tìm kiếm thành viên';
$lang['groupscope']            = 'Giới hạn phạm vi tìm kiếm cho tìm kiếm nhóm';
$lang['userkey']               = 'Thuộc tính biểu thị tên thành viên; phải phù hợp với bộ lọc thành viên.';
$lang['groupkey']              = 'Thành viên nhóm từ bất kỳ thuộc tính thành viên nào (thay vì các nhóm AD tiêu chuẩn), VD. nhóm từ bộ phận hoặc số điện thoại';
$lang['modPass']               = 'Có thể thay đổi mật khẩu LDAP qua dokuwiki không?';
$lang['debug']                 = 'Hiển thị thông tin gỡ lỗi bổ sung về lỗi';
$lang['deref_o_0']             = 'LDAP_DEREF_NEVER';
$lang['deref_o_1']             = 'LDAP_DEREF_SEARCHING';
$lang['deref_o_2']             = 'LDAP_DEREF_FINDING';
$lang['deref_o_3']             = 'LDAP_DEREF_ALWAYS';
$lang['referrals_o_-1']        = 'sử dụng mặc định';
$lang['referrals_o_0']         = 'không theo dõi referrals';
$lang['referrals_o_1']         = 'theo dõi referrals';
