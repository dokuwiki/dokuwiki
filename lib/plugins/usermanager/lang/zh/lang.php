<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author ZDYX <zhangduyixiong@gmail.com>
 * @author http://www.chinese-tools.com/tools/converter-tradsimp.html
 * @author George Sheraton guxd@163.com
 * @author Simon zhan <simonzhan@21cn.com>
 * @author mr.jinyi@gmail.com
 * @author ben <ben@livetom.com>
 * @author lainme <lainme993@gmail.com>
 * @author caii <zhoucaiqi@gmail.com>
 * @author Hiphen Lee <jacob.b.leung@gmail.com>
 * @author caii, patent agent in China <zhoucaiqi@gmail.com>
 * @author lainme993@gmail.com
 * @author Shuo-Ting Jian <shoting@gmail.com>
 * @author Rachel <rzhang0802@gmail.com>
 * @author Yangyu Huang <yangyu.huang@gmail.com>
 */
$lang['menu']                  = '用户管理器';
$lang['noauth']                = '（用户认证不可用）';
$lang['nosupport']             = '（用户管理不支持）';
$lang['badauth']               = '非法的认证结构';
$lang['user_id']               = '用户名';
$lang['user_pass']             = '密码';
$lang['user_name']             = '真实姓名';
$lang['user_mail']             = 'Email';
$lang['user_groups']           = '组 *';
$lang['field']                 = '栏目';
$lang['value']                 = '值';
$lang['add']                   = '添加';
$lang['delete']                = '删除';
$lang['delete_selected']       = '删除选中的';
$lang['edit']                  = '编辑';
$lang['edit_prompt']           = '编辑该用户';
$lang['modify']                = '保存更改';
$lang['search']                = '搜索';
$lang['search_prompt']         = '进行搜索';
$lang['clear']                 = '重置搜索过滤器';
$lang['filter']                = '过滤器';
$lang['export_all']            = '导出所有用户（CSV）';
$lang['export_filtered']       = '导出已筛选的用户列表（CSV）';
$lang['import']                = '请输入新用户名';
$lang['line']                  = '行号';
$lang['error']                 = '信息错误';
$lang['summary']               = '找到 %3$d 名用户，显示其中第 %1$d 至 %2$d 位用户。数据库中共有 %4$d 名用户。';
$lang['nonefound']             = '没有找到用户。数据库中共有 %d 名用户。';
$lang['delete_ok']             = '用户 %d 已删除';
$lang['delete_fail']           = '用户 %d 删除失败。';
$lang['update_ok']             = '用户更新成功';
$lang['update_fail']           = '用户更新失败';
$lang['update_exists']         = '用户名更改失败，您指定的用户名（%s）已存在（其他更改将立即生效）。';
$lang['start']                 = '第一页';
$lang['prev']                  = '前一页';
$lang['next']                  = '后一页';
$lang['last']                  = '最后一页';
$lang['edit_usermissing']      = '您指定的用户没有找到，可能用户已被删除或用户名已更改。';
$lang['user_notify']           = '通知用户';
$lang['note_notify']           = '通知邮件只有在用户获得新密码时才会发送。';
$lang['note_group']            = '* 如果没有指定组，新用户将被添加到默认的组（%s）中。';
$lang['note_pass']             = '如果输入框留空则自动生成口令，并会通知用户。';
$lang['add_ok']                = '用户添加成功';
$lang['add_fail']              = '用户添加失败';
$lang['notify_ok']             = '通知邮件已发送';
$lang['notify_fail']           = '通知邮件无法发送';
$lang['import_userlistcsv']    = '用户列表文件（CSV）';
$lang['import_header']         = '最近一次导入 - 失败';
$lang['import_success_count']  = '用户导入：找到了 %d 个用户，%d 个用户被成功导入。';
$lang['import_failure_count']  = '用户导入：%d 个用户导入失败。下面列出了失败的用户。';
$lang['import_error_fields']   = '域的数目不足，发现 %d 个，需要 4 个。';
$lang['import_error_baduserid'] = '用户ID丢失';
$lang['import_error_badname']  = '名称错误';
$lang['import_error_badmail']  = '邮件地址错误';
$lang['import_error_upload']   = '导入失败。CSV 文件无法上传或是空的。';
$lang['import_error_readfail'] = '导入失败。无法读取上传的文件。';
$lang['import_error_create']   = '不能创建新用户';
$lang['import_notify_fail']    = '通知消息无法发送到导入的用户 %s，电子邮件地址是 %s。';
