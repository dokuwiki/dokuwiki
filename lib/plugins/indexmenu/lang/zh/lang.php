<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author 昊林 <tzbkk@outlook.com>
 * @author FENG.JIE <ahx@qq.com>
 * @author lempel <riverlempel@hotmail.com>
 * @author oott123 <ip.192.168.1.1@qq.com>
 * @author super_ZED <funing@renrentou.com>
 * @author kuma <kuma000@qq.com>
 */
$lang['menu']                  = '索引菜单实用功能';
$lang['fetch']                 = '显示';
$lang['install']               = '安装';
$lang['delete']                = '删除';
$lang['check']                 = '检查';
$lang['no_repos']              = '没有配置主题库 URL。';
$lang['disabled']              = '禁用';
$lang['conn_err']              = '连接错误';
$lang['dir_err']               = '无法创建用于下载主题的临时文件夹';
$lang['down_err']              = '无法下载主题';
$lang['zip_err']               = 'zip 文件创建或解压错误';
$lang['install_ok']            = '主题安装成功。新的主题可以在工具栏或编辑页面中找到，或者使用<code>js#theme_name option</code>代码。';
$lang['install_no']            = '链接错误，但您可以<a href="http://samuele.netsons.org/dokuwiki/lib/plugins/indexmenu/upload/">在这里</a>手动上传你的主题。';
$lang['delete_ok']             = '主题成功删除。';
$lang['delete_no']             = '删除主题时出错';
$lang['upload']                = '分享';
$lang['checkupdates']          = '插件更新';
$lang['noupdates']             = 'Indexmenu 现在是最新版本，无需更新。';
$lang['infos']                 = '你可以自己创建自己的主题，参见<a href="https://www.dokuwiki.org/plugin:indexmenu#theme_tutorial">主题教程</a>页面。<br />
如果你将你的主题分享给其他人，会让更多人高兴的~请点击主题下面的“分享”将你的主题分享到公共主题库。';
$lang['showsort']              = '索引菜单排序编号:';
$lang['donation_text']         = 'Indexmenu 插件并未被任何人赞助，但是我在空闲时间一直在免费的开发和提供支持。如果你想感谢或者支持它的开发，你可以捐助我。';
$lang['js']['indexmenuwizard'] = 'Indexmenu 向导';
$lang['js']['index']           = '索引';
$lang['js']['options']         = '选项';
$lang['js']['navigation']      = '导航';
$lang['js']['sort']            = '分类';
$lang['js']['filter']          = '过滤';
$lang['js']['performance']     = '性能表现';
$lang['js']['namespace']       = '命名空间';
$lang['js']['nsdepth']         = '深度';
$lang['js']['js']              = '导航树由JS渲染，你可以定义你自己的主题';
$lang['js']['theme']           = '主题';
$lang['js']['navbar']          = '这导航树形图打开在当前的名称空间';
$lang['js']['context']         = '显示当前wiki名称空间环境的树形图';
$lang['js']['nocookie']        = '不记得 打开/关闭 节点在用户导航';
$lang['js']['noscroll']        = '防止滚动树在不适合它的容器宽度时';
$lang['js']['notoc']           = '禁用toc预览功能';
$lang['js']['tsort']           = '按标题';
$lang['js']['dsort']           = '按日期';
$lang['js']['msort']           = '按meta标签';
$lang['js']['nsort']           = '排序名称空间';
$lang['js']['hsort']           = '首页排序';
$lang['js']['rsort']           = '反向排序页面';
$lang['js']['nons']            = '只显示页面';
$lang['js']['nopg']            = '只显示名称空间';
$lang['js']['max']             = '打开节点时要用ajax渲染多少层级。此外还有使用AJAX而不是一次性获取低于该级别的子层级的数量。';
$lang['js']['maxjs']           = '打开节点时要在客户端浏览器中呈现多少层';
$lang['js']['id']              = '此索引菜单的自定义cookie ID';
$lang['js']['insert']          = '插入索引菜单';
$lang['js']['metanum']         = 'Meta数进行排序';
$lang['js']['insertmetanum']   = '插入元数mate';
$lang['js']['page']            = '页面';
$lang['js']['revs']            = '修订版';
$lang['js']['tocpreview']      = 'TOC预览';
$lang['js']['editmode']        = '编辑模式';
$lang['js']['insertdwlink']    = '插入为 DWlink';
$lang['js']['insertdwlinktooltip'] = '将此页面的链接插入到光标位置的编辑框中';
$lang['js']['ns']              = '命名空间';
$lang['js']['search']          = '搜索...';
$lang['js']['searchtooltip']   = '在此命名空间中搜索页面';
$lang['js']['create']          = '创建';
$lang['js']['more']            = '更多';
$lang['js']['headpage']        = '首页';
$lang['js']['headpagetooltip'] = '在此页面下创建一个新的首页';
$lang['js']['startpage']       = '起始页';
$lang['js']['startpagetooltip'] = '在此页面下创建一个新的开始页';
$lang['js']['custompage']      = '自定义页面…';
$lang['js']['custompagetooltip'] = '在此页面下创建一个新页面（通过弹出窗口输入名称）';
$lang['js']['acls']            = '访问控制列表ACL';
$lang['js']['purgecache']      = '清除缓存';
$lang['js']['exporthtml']      = '导出为HTML';
$lang['js']['exporttext']      = '导出为文本';
$lang['js']['headpagehere']    = '头版请输入这里';
$lang['js']['headpageheretooltip'] = '在该命名空间内创建一个新的首页';
$lang['js']['newpage']         = '新页面…';
$lang['js']['newpagetooltip']  = '在此名称空间内创建一个新页面（通过弹出窗口输入名称）';
$lang['js']['newpagehere']     = '新页面在这里';
$lang['js']['insertkeywords']  = '插入关键字以在此名称空间中搜索';
$lang['js']['insertpagename']  = '插入页面名称以创建新的';
$lang['js']['edit']            = '编辑';
$lang['js']['loading']         = '加载';
