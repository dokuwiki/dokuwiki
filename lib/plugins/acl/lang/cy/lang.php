<?php
/**
 * welsh language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Anika Henke <anika@selfthinker.org>
 * @author     Matthias Grimm <matthiasgrimm@users.sourceforge.net>
 * @author     Alan Davies <ben.brynsadler@gmail.com>
 */

$lang['admin_acl']  = 'Rheolaeth Rhestr Rheoli Mynediad';
$lang['acl_group']  = 'Grŵp:';
$lang['acl_user']   = 'Defnyddiwr:';
$lang['acl_perms']  = 'Hawliau';
$lang['page']       = 'Tudalen';
$lang['namespace']  = 'Namespace'; //namespace

$lang['btn_select']  = 'Dewis';

$lang['p_user_id']    = 'Mae gan y defnyddiwr <b class="acluser">%s</b> yr hawliau canlynol yn bresennol ar dudalen <b class="aclpage">%s</b>: <i>%s</i>.';
$lang['p_user_ns']    = 'Mae gan y defnyddiwr <b class="acluser">%s</b> yr hawliau canlynol yn bresennol mewn namespace <b class="aclns">%s</b>: <i>%s</i>.';//namespace
$lang['p_group_id']   = 'Mae gan aelodau grŵp <b class="aclgroup">%s</b> yr hawliau canlynol yn bresennol ar dudalen <b class="aclpage">%s</b>: <i>%s</i>.';
$lang['p_group_ns']   = 'Mae gan aelodau grŵp <b class="aclgroup">%s</b> yr hawliau canlynol yn bresennol mewn namespace <b class="aclns">%s</b>: <i>%s</i>.';//namespace

$lang['p_choose_id']  = 'Rhowch <b>ddefnyddiwr neu grŵp</b> yn y ffurflen uchod i weld neu golugu\'r hawliau sydd wedi\'u gosod ar gyfer y dudalen <b class="aclpage">%s</b>.';
$lang['p_choose_ns']  = 'Rhowch <b>ddefnyddiwr neu grŵp</b> yn y ffurflen uchod i weld neu golugu\'r hawliau sydd wedi\'u gosod ar gyfer y namespace <b class="aclns">%s</b>.';//namespace


$lang['p_inherited']  = 'Sylw: Doedd yr hawliau hynny heb eu gosod yn uniongyrchol ond cawsant eu hetifeddu o grwpiau eraill neu namespaces uwch.';//namespace
$lang['p_isadmin']    = 'Sylw: Mae gan y grŵp neu\'r defnyddiwr hawliau llawn oherwydd mae wedi\'i ffurfweddu fel uwchddefnyddiwr.';
$lang['p_include']    = 'Mae hawliau uwch yn cynnwys rhai is. Mae Creu, Lanlwytho a Dileu yn berthnasol i namespaces yn unig, nid tudalennau.';//namespace

$lang['current'] = 'Rheolau ACL Cyfredol';
$lang['where'] = 'Tudalen/Namespace';//namespace
$lang['who']   = 'Defnyddiwr/Grŵp';
$lang['perm']  = 'Hawliau';

$lang['acl_perm0']  = 'Dim';
$lang['acl_perm1']  = 'Darllen';
$lang['acl_perm2']  = 'Golygu';
$lang['acl_perm4']  = 'Creu';
$lang['acl_perm8']  = 'Lanlwytho';
$lang['acl_perm16'] = 'Dileu';
$lang['acl_new']    = 'Ychwanegu Cofnod Newydd';
$lang['acl_mod']    = 'Newid Cofnod';
//Setup VIM: ex: et ts=2 :
