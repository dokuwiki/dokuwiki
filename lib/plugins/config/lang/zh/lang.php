<?php
/**
 * Chinese(Simplified) language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
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
 */
$lang['menu']                  = '配置设置';
$lang['error']                 = '由于非法参数，设置没有更新。请检查您做的改动并重新提交。
                       <br />非法参数会用红框包围显示。';
$lang['updated']               = '设置更新成功。';
$lang['nochoice']              = '（没有其他可用选项）';
$lang['locked']                = '设置文件无法更新。如果这是您没有意料到的，<br />
                       请确保本地设置文件的名称和权限设置正确。';
$lang['danger']                = '危险：更改这个选项可能会使用你的Wiki页面和配置菜单无法进入。';
$lang['warning']               = '注意：更改这个选项可能会造成未知结果。';
$lang['security']              = '安全提示：更改这个选项可能会有安全隐患。';
$lang['_configuration_manager'] = '配置管理器';
$lang['_header_dokuwiki']      = 'DokuWiki 设置';
$lang['_header_plugin']        = '插件设置';
$lang['_header_template']      = '模板设置';
$lang['_header_undefined']     = '其他设置';
$lang['_basic']                = '基本设置';
$lang['_display']              = '显示设置';
$lang['_authentication']       = '认证设置';
$lang['_anti_spam']            = '反垃圾邮件/评论设置';
$lang['_editing']              = '编辑设置';
$lang['_links']                = '链接设置';
$lang['_media']                = '媒体设置';
$lang['_notifications']        = '通知设置';
$lang['_syndication']          = '聚合设置';
$lang['_advanced']             = '高级设置';
$lang['_network']              = '网络设置';
$lang['_msg_setting_undefined'] = '设置的元数据不存在。';
$lang['_msg_setting_no_class'] = '设置的分类不存在。';
$lang['_msg_setting_no_default'] = '设置的默认值不存在。';
$lang['title']                 = '维基站点的标题';
$lang['start']                 = '开始页面的名称';
$lang['lang']                  = '语言';
$lang['template']              = '模版';
$lang['tagline']               = '副标题 （如果模板支持此功能）';
$lang['sidebar']               = '侧边栏的页面名称 （如果模板支持此功能），留空以禁用侧边栏';
$lang['license']               = '您愿意让你贡献的内容在何种许可方式下发布？';
$lang['savedir']               = '保存数据的目录';
$lang['basedir']               = '根目录';
$lang['baseurl']               = '根路径（URL）';
$lang['cookiedir']             = 'Cookie 路径。留空以使用 baseurl。';
$lang['dmode']                 = '文件夹的创建模式';
$lang['fmode']                 = '文件的创建模式';
$lang['allowdebug']            = '允许调试 <b>如果您不需要调试，请勿勾选！</b>';
$lang['recent']                = '最近更新';
$lang['recent_days']           = '保留多少天的最近更改（天）';
$lang['breadcrumbs']           = '显示“足迹”的数量';
$lang['youarehere']            = '显示“您在这里”';
$lang['fullpath']              = '在页面底部显示完整路径';
$lang['typography']            = '进行字符替换';
$lang['dformat']               = '日期格式（参见 PHP 的 <a href="http://www.php.net/strftime">strftime</a> 功能）';
$lang['signature']             = '签名样式';
$lang['showuseras']            = '显示用户为';
$lang['toptoclevel']           = '目录的最顶层';
$lang['tocminheads']           = '头条数目的最小数目，这将用于决定是否创建目录列表（TOC）';
$lang['maxtoclevel']           = '目录的最多层次';
$lang['maxseclevel']           = '段落编辑的最多层次';
$lang['camelcase']             = '对链接使用 CamelCase';
$lang['deaccent']              = '清理页面名称';
$lang['useheading']            = '使用“标题 H1”作为页面名称';
$lang['sneaky_index']          = '默认情况下，DokuWiki 在索引页会显示所有 namespace。启用该选项能隐藏那些用户没有权限阅读的页面。但也可能将用户能够阅读的子页面一并隐藏。这有可能导致在特定 ACL 设置下，索引功能不可用。';
$lang['hidepages']             = '隐藏匹配的界面（正则表达式）';
$lang['useacl']                = '使用访问控制列表（ACL）';
$lang['autopasswd']            = '自动生成密码';
$lang['authtype']              = '认证后台管理方式';
$lang['passcrypt']             = '密码加密方法';
$lang['defaultgroup']          = '默认组';
$lang['superuser']             = '超级用户 - 不论 ACL 如何设置，都能访问所有页面与功能的用户组/用户';
$lang['manager']               = '管理员 - 能访问相应管理功能的用户组/用户';
$lang['profileconfirm']        = '更新个人信息时需要输入当前密码';
$lang['rememberme']            = '允许在本地机长期保留登录cookies信息（记住我）';
$lang['disableactions']        = '停用 DokuWiki 功能';
$lang['disableactions_check']  = '检查';
$lang['disableactions_subscription'] = '订阅/退订';
$lang['disableactions_wikicode'] = '查看源文件/导出源文件';
$lang['disableactions_other']  = '其他功能（用英文逗号分隔）';
$lang['auth_security_timeout'] = '认证安全超时（秒）';
$lang['securecookie']          = '要让浏览器须以HTTPS方式传送在HTTPS会话中设置的cookies吗？请只在登录过程为SSL加密而浏览维基为明文的情况下打开此选项。';
$lang['remote']                = '激活远程 API 系统。这允许其他程序通过 XML-RPC 或其他机制来访问维基。';
$lang['remoteuser']            = '将远程 API 的访问权限限制在指定的组或用户中，以逗号分隔。留空则允许任何人访问。';
$lang['usewordblock']          = '根据 wordlist 阻止垃圾评论';
$lang['relnofollow']           = '对外部链接使用 rel="nofollow" 标签';
$lang['indexdelay']            = '构建索引前的时间延滞（秒）';
$lang['mailguard']             = '弄乱邮件地址（保护用户的邮件地址）';
$lang['iexssprotect']          = '检验上传的文件以避免可能存在的恶意 JavaScript 或 HTML 代码';
$lang['usedraft']              = '编辑时自动保存一份草稿';
$lang['htmlok']                = '允许嵌入式 HTML';
$lang['phpok']                 = '允许嵌入式 PHP';
$lang['locktime']              = '独有编辑权/文件锁定的最长时间（秒）';
$lang['cachetime']             = '缓存的最长时间（秒）';
$lang['target____wiki']        = '内部链接的目标窗口';
$lang['target____interwiki']   = 'Interwiki 链接的目标窗口';
$lang['target____extern']      = '外部链接的目标窗口';
$lang['target____media']       = '媒体文件链接的目标窗口';
$lang['target____windows']     = 'Windows 链接的目标窗口';
$lang['mediarevisions']        = '激活媒体修订历史？';
$lang['refcheck']              = '检查媒体与页面的挂钩情况';
$lang['gdlib']                 = 'GD 库版本';
$lang['im_convert']            = 'ImageMagick 转换工具的路径';
$lang['jpg_quality']           = 'JPG 压缩质量（0-100）';
$lang['fetchsize']             = 'fetch.php 能从外部下载的最大文件大小（字节）';
$lang['subscribers']           = '启用页面订阅支持';
$lang['subscribe_time']        = '订阅列表和摘要发送的时间间隔（秒）；这应当小于指定的最近更改保留时间（recent_days）。
';
$lang['notify']                = '发送更改通知给这个邮件地址';
$lang['registernotify']        = '发送新注册用户的信息给这个邮件地址';
$lang['mailfrom']              = '自动发送邮件时使用的邮件地址';
$lang['mailprefix']            = '自动发送邮件时使用的邮件地址前缀';
$lang['htmlmail']              = '发送更加美观，但体积更大的 HTML 多部分邮件。禁用则发送纯文本邮件。';
$lang['sitemap']               = '生成 Google sitemap（天）';
$lang['rss_type']              = 'XML feed 类型';
$lang['rss_linkto']            = 'XML feed 链接到';
$lang['rss_content']           = 'XML feed 项目中显示什么呢？';
$lang['rss_update']            = 'XML feed 升级间隔（秒）';
$lang['rss_show_summary']      = 'XML feed 在标题中显示摘要';
$lang['rss_media']             = '在 XML 源中应该列出何种类型的更改？';
$lang['updatecheck']           = '自动检查更新并接收安全警告吗？开启该功能后 DokuWiki 将自动访问 splitbrain.org。';
$lang['userewrite']            = '使用更整洁的 URL';
$lang['useslash']              = '在 URL 中使用斜杠作为命名空间的分隔符';
$lang['sepchar']               = '页面名称中的单词分隔符';
$lang['canonical']             = '使用完全标准的 URL';
$lang['fnencode']              = '非 ASCII 文件名的编码方法。';
$lang['autoplural']            = '在链接中检查多种格式';
$lang['compression']           = 'attic 文件的压缩方式';
$lang['gzip_output']           = '对 xhtml 使用 gzip 内容编码';
$lang['compress']              = '使 CSS 和 javascript 的输出更紧密';
$lang['cssdatauri']            = '字节数。CSS 文件引用的图片若小于该字节，则被直接嵌入样式表中来减少 HTTP 请求头的开销。这个技术在 IE 中不起作用。<code>400</code> 到 <code>600</code> 字节是不错的值。设置为 <code>0</code> 则禁用。';
$lang['send404']               = '发送 "HTTP 404/页面没有找到" 错误信息给不存在的页面';
$lang['broken_iua']            = 'ignore_user_abort 功能失效了？这有可能导致搜索索引不可用。IIS+PHP/CGI 已损坏。请参阅 <a href="http://bugs.splitbrain.org/?do=details&amp;task_id=852">Bug 852</a> 获取更多信息。';
$lang['xsendfile']             = '使用 X-Sendfile 头让服务器发送状态文件？您的服务器需要支持该功能。';
$lang['renderer_xhtml']        = '主维基页面 (xhtml) 输出使用的渲染';
$lang['renderer__core']        = '%s（DokuWiki 内核）';
$lang['renderer__plugin']      = '%s（插件）';
$lang['dnslookups']            = 'Dokuwiki 将会查询用户编辑页面的远程 IP 地址的主机名。如果您的 DNS 服务器比较缓慢或者不工作，或者您不想要这个功能，请禁用此选项。';
$lang['proxy____host']         = '代理服务器的名称';
$lang['proxy____port']         = '代理服务器的端口';
$lang['proxy____user']         = '代理服务器的用户名';
$lang['proxy____pass']         = '代理服务器的密码';
$lang['proxy____ssl']          = '使用 SSL 连接到代理服务器';
$lang['proxy____except']       = '用来匹配代理应跳过的地址的正则表达式。';
$lang['safemodehack']          = '启用 Safemode Hack';
$lang['ftp____host']           = 'Safemode Hack 的 FTP 服务器';
$lang['ftp____port']           = 'Safemode Hack 的 FTP 端口';
$lang['ftp____user']           = 'Safemode Hack 的 FTP 用户名';
$lang['ftp____pass']           = 'Safemode Hack 的 FTP 密码';
$lang['ftp____root']           = 'Safemode Hack 的 FTP 根路径';
$lang['license_o_']            = '什么都没有选';
$lang['typography_o_0']        = '无';
$lang['typography_o_1']        = '仅限双引号';
$lang['typography_o_2']        = '所有引号（不一定能正常运行）';
$lang['userewrite_o_0']        = '无';
$lang['userewrite_o_1']        = '.htaccess';
$lang['userewrite_o_2']        = 'DokuWiki 内部控制';
$lang['deaccent_o_0']          = '关闭';
$lang['deaccent_o_1']          = '移除重音符号';
$lang['deaccent_o_2']          = '用罗马字拼写';
$lang['gdlib_o_0']             = 'GD 库不可用';
$lang['gdlib_o_1']             = '1.x 版';
$lang['gdlib_o_2']             = '自动检测';
$lang['rss_type_o_rss']        = 'RSS 0.91';
$lang['rss_type_o_rss1']       = 'RSS 1.0';
$lang['rss_type_o_rss2']       = 'RSS 2.0';
$lang['rss_type_o_atom']       = 'Atom 0.3';
$lang['rss_type_o_atom1']      = 'Atom 1.0';
$lang['rss_content_o_abstract'] = '摘要';
$lang['rss_content_o_diff']    = '统一差异';
$lang['rss_content_o_htmldiff'] = 'HTML 格式化的差异表';
$lang['rss_content_o_html']    = '完整的 hTML 页面内容';
$lang['rss_linkto_o_diff']     = '差别查看';
$lang['rss_linkto_o_page']     = '已修订的页面';
$lang['rss_linkto_o_rev']      = '修订列表';
$lang['rss_linkto_o_current']  = '当前页面';
$lang['compression_o_0']       = '无';
$lang['compression_o_gz']      = 'gzip';
$lang['compression_o_bz2']     = 'bz2';
$lang['xsendfile_o_0']         = '不要使用';
$lang['xsendfile_o_1']         = '专有 lighttpd 头（1.5 发布前）';
$lang['xsendfile_o_2']         = '标准 X-Sendfile 头';
$lang['xsendfile_o_3']         = '专有 Nginx X-Accel-Redirect 头';
$lang['showuseras_o_loginname'] = '登录名';
$lang['showuseras_o_username'] = '用户全名';
$lang['showuseras_o_email']    = '用户的电子邮箱（按邮箱保护设置加扰）';
$lang['showuseras_o_email_link'] = '以mailto：形式显示用户的电子邮箱';
$lang['useheading_o_0']        = '从不';
$lang['useheading_o_navigation'] = '仅限导航';
$lang['useheading_o_content']  = '仅限维基内容内';
$lang['useheading_o_1']        = '一直';
$lang['readdircache']          = 'readdir缓存的最长寿命（秒）';
