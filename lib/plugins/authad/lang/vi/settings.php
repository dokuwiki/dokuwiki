<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Thien Hau <thienhau.9a14@gmail.com>
 */
$lang['account_suffix']        = 'Hậu tố tài khoản của bạn. VD. <code>@my.domain.org</code>';
$lang['base_dn']               = 'DN cơ sở của bạn. VD. <code>DC=my,DC=domain,DC=org</code>';
$lang['domain_controllers']    = 'Một danh sách các bộ điều khiển miền được phân tách bằng dấu phẩy. VD. <code>srv1.domain.org,srv2.domain.org</code>';
$lang['admin_username']        = 'Thành viên Active Directory đặc quyền có quyền truy cập vào tất cả dữ liệu của thành viên khác. Tùy chọn, nhưng cần thiết cho một số hành động nhất định như gửi thư đăng ký.';
$lang['admin_password']        = 'Mật khẩu của thành viên trên.';
$lang['sso']                   = 'Nên đăng nhập một lần qua Kerberos hoặc NTLM?';
$lang['sso_charset']           = 'Bộ ký tự máy chủ web của bạn sẽ chuyển tên người dùng Kerberos hoặc NTLM. Để trống cho UTF-8 hoặc latin-1. Yêu cầu phần mở rộng iconv.';
$lang['real_primarygroup']     = 'Nên giải quyết nhóm chính thực sự thay vì giả sử "Tên miền thành viên" (chậm hơn).';
$lang['use_ssl']               = 'Sử dụng kết nối SSL? Nếu được sử dụng, không kích hoạt TLS bên dưới.';
$lang['use_tls']               = 'Sử dụng kết nối TLS? Nếu được sử dụng, không kích hoạt SSL ở trên.';
$lang['debug']                 = 'Hiển thị đầu ra gỡ lỗi bổ sung về lỗi?';
$lang['expirywarn']            = 'Báo trước ngày để cảnh báo thành viên về việc hết hạn mật khẩu. Đặt thành 0 để vô hiệu hóa.';
$lang['additional']            = 'Một danh sách được phân tách bằng dấu phẩy của các thuộc tính AD bổ sung để tìm nạp dữ liệu thành viên. Được sử dụng bởi một số plugin.';
$lang['update_name']           = 'Cho phép thành viên cập nhật tên hiển thị AD?';
$lang['update_mail']           = 'Cho phép thành viên cập nhật địa chỉ thư điện tử?';
$lang['recursive_groups']      = 'Giải quyết những nhóm lồng nhau cho các thành viên tương ứng (chậm hơn).';
