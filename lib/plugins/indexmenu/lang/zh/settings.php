<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Zhenzhe Huang <1991419264@qq.com>
 * @author 昊林 <tzbkk@outlook.com>
 * @author FENG.JIE <ahx@qq.com>
 * @author lempel <riverlempel@hotmail.com>
 * @author super_ZED <funing@renrentou.com>
 */
$lang['checkupdate']           = '定期检查更新';
$lang['only_admins']           = '仅允许管理员使用indexmenu语法。
请注意，由非管理员用户编辑的页面将丢失所有包含的indexmenu导航树。';
$lang['aclcache']              = '优化Indexmenu针对ACL的缓存（仅适用于请求的根命名空间）<br>方法的选择只会影响索引菜单树上节点的可视化，而不会影响页面授权<ul><li>None：标准。这是一种更快的方法，它不会创建更多的缓存文件，但权限被拒绝的节点可能不会显示给授权用户，反之亦然。当您不拒绝ACL访问页面或不关心树的显示方式时，建议使用此选项<li>User：按登录的用户。较慢的方法，它会创建大量缓存文件，但它总是正确隐藏拒绝的页面。当您有依赖于用户登录的页面ACL时，建议使用<li>Groups：按组成员身份。在前面的方法之间进行了很好的折中，但如果您拒绝向属于具有读取 acl 身份验证的组的用户读取ACL，那么他无论如何都可以在树中显示该节点。 当您的整个站点 ACL取决于组成员身份时推荐使用。</ul>';
$lang['headpage']              = 'Headpage 方法：从何处检索命名空间的标题和链接的页面。<br>可以是以下任意值：<ul><li>全局起始页面。<li>包含命名空间名称的页面。<li>具有命名空间名称且处于同一级别的页面。<li>自定义名称页面。<li>以逗号分隔的页面名称列表。</ul>';
$lang['hide_headpage']         = '隐藏标题';
$lang['page_index']            = '此页面将代替 dokuwiki 的主页索引。创建它并插入 indexmenu 语法。如果已经存在带有导航的侧边栏，使用<code>id#random</code>。我的建议是<code>{{indexmenu>..|js navbar nocookie id#random}}</code>。';
$lang['empty_msg']             = '当树为空时显示的消息。请使用 Dokuwiki 语法，而不是    html 代码。<code>{{ns}}</code> 是导向指定名称空间的快捷方式。';
$lang['skip_index']            = '要跳过的命名空间ID。使用正则表达式格式。例如：<code> /（sidebars | private：myns）/ </ code>';
$lang['skip_file']             = '要跳过的页面ID。使用正则表达式格式。示例<code> /（:: start $ | ^ public：newstart $）/ </ code>';
$lang['show_sort']             = '显示给管理员索引菜单的排序号作为页面顶部的注释';
$lang['themes_url']            = '通过url地址下载js主题';
$lang['be_repo']               = '让其他人通过你的网站下载主题';
$lang['defaultoptions']        = '索引菜单选项列表，以空格分隔。这些选项默认情况下将应用于每个索引菜单，并且可以通过插件语法中的反向命令撤消';
