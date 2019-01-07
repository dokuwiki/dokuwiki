<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Phy <dokuwiki@phy25.com>
 * @author Aaron Zhou <iradio@163.com>
 */
$lang['debug']                 = '打印详细的错误信息。应该在设定完成后禁用。';
$lang['dsn']                   = '连接到数据库的DSN';
$lang['user']                  = '以上数据库连接的用户名（sqlite 留空）';
$lang['pass']                  = '以上数据库连接的密码 （sqlite 留空）';
$lang['select-user']           = '选择单一用户数据的SQL语句';
$lang['select-user-groups']    = '选择单一用户所有用户组的SQL语句';
$lang['select-groups']         = '选择所有有效组的SQL语句';
$lang['insert-user']           = '向数据库插入一个新用户的SQL语句';
$lang['delete-user']           = '从数据库中移除单个用户的SQL语句';
$lang['list-users']            = '列出与筛选条件匹配用户的SQL语句';
$lang['count-users']           = '统计与筛选条件匹配的用户数量的SQL语句';
$lang['update-user-info']      = '更新单一用户全名和email地址的SQL语句';
$lang['update-user-login']     = '更新单一用户登录名的SQL语句';
$lang['update-user-pass']      = '更新单一用户密码的SQL语句';
$lang['insert-group']          = '向数据库中插入一个新组的SQL语句';
$lang['join-group']            = '把用户增加到现有用户组的 SQL 语句';
$lang['leave-group']           = '把用户移除出现有用户组的 SQL 语句';
$lang['check-pass']            = '查询用户密码的 SQL 语句（如密码在 select-user 查询时已经获取，则本设置可留空）';
