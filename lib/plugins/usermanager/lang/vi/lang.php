<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Thien Hau <thienhau.9a14@gmail.com>
 */
$lang['menu']                  = 'Quản lý thành viên';
$lang['noauth']                = '(không có sẵn xác thực thành viên)';
$lang['nosupport']             = '(không hỗ trợ quản lý thành viên)';
$lang['badauth']               = 'cơ chế xác thực không hợp lệ';
$lang['user_id']               = 'Thành viên';
$lang['user_pass']             = 'Mật khẩu';
$lang['user_name']             = 'Tên thật';
$lang['user_mail']             = 'Thư điện tử';
$lang['user_groups']           = 'Nhóm';
$lang['field']                 = 'Trường';
$lang['value']                 = 'Giá trị';
$lang['add']                   = 'Thêm';
$lang['delete']                = 'Xóa';
$lang['delete_selected']       = 'Xóa những mục đã chọn';
$lang['edit']                  = 'Sửa đổi';
$lang['edit_prompt']           = 'Sửa đổi thành viên này';
$lang['modify']                = 'Lưu thay đổi';
$lang['search']                = 'Tìm kiếm';
$lang['search_prompt']         = 'Thực hiện tìm kiếm';
$lang['clear']                 = 'Đặt lại bộ lọc tìm kiếm';
$lang['filter']                = 'Bộ lọc';
$lang['export_all']            = 'Xuất tất cả thành viên (CSV)';
$lang['export_filtered']       = 'Xuất danh sách thành viên được lọc (CSV)';
$lang['import']                = 'Nhập thành viên mới';
$lang['line']                  = 'Dòng số';
$lang['error']                 = 'Thông báo lỗi';
$lang['summary']               = 'Hiển thị thành viên %1$d-%2$d trong số %3$d thành viên được tìm thấy. %4$d thành viên tổng cộng.';
$lang['nonefound']             = 'Không tìm thấy thành viên. %d thành viên tổng cộng.';
$lang['delete_ok']             = 'Đã xóa %d thành viên.';
$lang['delete_fail']           = 'Xóa không thành công %d thành viên';
$lang['update_ok']             = 'Cập nhật thành viên thành công';
$lang['update_fail']           = 'Cập nhật thành viên không thành công';
$lang['update_exists']         = 'Thay đổi tên thành viên không thành công, tên thành viên được chỉ định (%s) đã tồn tại (mọi thay đổi khác sẽ được áp dụng).';
$lang['start']                 = 'bắt đầu';
$lang['prev']                  = 'trước';
$lang['next']                  = 'sau';
$lang['last']                  = 'cuối';
$lang['edit_usermissing']      = 'Không tìm thấy thành viên đã chọn, tên thành viên được chỉ định có thể đã bị xóa hoặc thay đổi ở nơi khác.';
$lang['user_notify']           = 'Thông báo cho thành viên';
$lang['note_notify']           = 'Chỉ gửi thư điện tử thông báo nếu thành viên được cung cấp mật khẩu mới.';
$lang['note_group']            = 'Thành viên mới sẽ được thêm vào nhóm mặc định (%s) nếu không có nhóm nào được chỉ định.';
$lang['note_pass']             = 'Mật khẩu sẽ được tự động tạo nếu trường bị bỏ trống và thông báo của thành viên được bật.';
$lang['add_ok']                = 'Đã thêm thành viên thành công';
$lang['add_fail']              = 'Thêm thành viên không thành công';
$lang['notify_ok']             = 'Đã gửi thư điện tử thông báo';
$lang['notify_fail']           = 'Không thể gửi thư điện tử thông báo';
$lang['import_userlistcsv']    = 'Tập tin danh sách thành viên (CSV):';
$lang['import_header']         = 'Nhập gần đây nhất - Không thành công';
$lang['import_success_count']  = 'Nhập thành viên: Đã tìm thấy %d thành viên, đã nhập thành công %d.';
$lang['import_failure_count']  = 'Nhập thành viên: %d không thành công. Thành viên không được nhập thành công được liệt kê dưới đây.';
$lang['import_error_fields']   = 'Không đủ trường, tìm thấy %d, yêu cầu 4.';
$lang['import_error_baduserid'] = 'Thiếu id thành viên';
$lang['import_error_badname']  = 'Tên không đúng';
$lang['import_error_badmail']  = 'Địa chỉ thư điện tử không đúng';
$lang['import_error_upload']   = 'Việc nhập không thành công. Không thể tải lên tập tin csv hoặc trống.';
$lang['import_error_readfail'] = 'Việc nhập không thành công. Không thể đọc tập tin đã tải lên.';
$lang['import_error_create']   = 'Không thể tạo thành viên';
$lang['import_notify_fail']    = 'Không thể gửi tin nhắn thông báo cho thành viên đã nhập, %s với thư điện tử %s.';
$lang['import_downloadfailures'] = 'Tải xuống lỗi dưới dạng CSV để sửa';
$lang['addUser_error_missing_pass'] = 'Vui lòng đặt mật khẩu hoặc kích hoạt thông báo thành viên để cho phép tạo mật khẩu.';
$lang['addUser_error_pass_not_identical'] = 'Mật khẩu đã nhập không giống nhau.';
$lang['addUser_error_modPass_disabled'] = 'Việc sửa đổi mật khẩu hiện đang bị vô hiệu hóa';
$lang['addUser_error_name_missing'] = 'Vui lòng nhập tên cho thành viên mới.';
$lang['addUser_error_modName_disabled'] = 'Việc sửa đổi tên hiện đang bị vô hiệu hóa.';
$lang['addUser_error_mail_missing'] = 'Vui lòng nhập địa chỉ thư điện tử cho thành viên mới.';
$lang['addUser_error_modMail_disabled'] = 'Việc sửa đổi địa chỉ thư điện tử hiện đang bị vô hiệu hóa.';
$lang['addUser_error_create_event_failed'] = 'Một plugin ngăn không cho thành viên mới được thêm vào. Xem lại các tin nhắn khác có thể để biết thêm thông tin.';
