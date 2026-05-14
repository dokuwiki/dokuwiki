<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Samuele Tognini <samuele@samuele.netsons.org>
 * @author Gerrit Uitslag <klapinklapin@gmail.com>
 */
$lang['checkupdate']           = 'Check periodically for updates.';
$lang['only_admins']           = 'Allow indexmenu syntax only to admins.<br>Note that a page edited by a no-admin user will lost every contained indexmenu tree.';
$lang['aclcache']              = 'Optimize the indexmenu cache for acl (works only for root requested namespaces).<br>The choice of the method affects only the visualization of nodes on the indexmenu tree, not the page authorizations.<ul><li>None: Standard. It is the faster method and it does not create further cache files, but the nodes with denied permission could be showed to no-authorized users or viceversa. Recommended when you don\'t deny pages access by acl or you don\'t care how the tree is displayed.<li>User: Per-User login. Slower method and it creates a lot of cache files, but it always hides correctly denied pages. Recommended when you have page acls that depend on users login.<li>Groups: Per-groups membership. Good compromise between the previous methods, but in case that you deny the read acl to a user which belongs to a group with a read acl auth, then he could anyway displays that nodes in the tree. Recommended when your whole site acls depend on groups membership.</ul>';
$lang['headpage']              = 'Headpage method: the page from which retrive the title and link of a namespace.<br>Can be any of this value:<ul><li>The global start page.<li>A page with the namespace name and that is inside it.<li>A page with the namespace name and that is at its same level.<li>A custom name page.<li>A comma separated list of page names.</ul>';
$lang['hide_headpage']         = 'Hide headpages.';
$lang['page_index']            = 'The page that will replace the main dokuwiki index. Create it and insert the indexmenu syntax. Use <code>id#random</code> if you already have an indexmenu  sidebar with navbar option. My suggestion is <code>{{indexmenu>..|js navbar nocookie id#random}}</code>.';
$lang['empty_msg']             = 'Message to show when tree is empty. Use the Dokuwiki syntax, not the html code. The <code>{{ns}}</code> variable is a shortcut for the requested namespace.';
$lang['skip_index']            = 'Namespaces id to skip. Use the Regular Expression format. Example: <code>/(sidebars|private:myns)/</code>';
$lang['skip_file']             = 'Pages id to skip. Use the Regular Expression format. Example <code>/(:start$|^public:newstart$)/</code>';
$lang['show_sort']             = 'Show to admins the indexmenu sort number as top of page note';
$lang['themes_url']            = 'Download js themes from this http url.';
$lang['be_repo']               = 'Let others download themes from you site.';
$lang['defaultoptions']               = 'List of indexmenu options separated by spaces. These options will be applied by default to every indexmenu and can be undone with a reverse command in the plugin syntax';
