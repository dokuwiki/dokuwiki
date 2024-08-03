<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Jacob Palm <mail@jacobpalm.dk>
 * @author koeppe <koeppe@kazur.dk>
 * @author Jon Bendtsen <bendtsen@diku.dk>
 * @author Lars NÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ¦sbye Christensen <larsnaesbye@stud.ku.dk>
 * @author Kalle Sommer Nielsen <kalle@php.net>
 * @author Esben Laursen <hyber@hyber.dk>
 * @author Harith <haj@berlingske.dk>
 * @author Daniel Ejsing-Duun <dokuwiki@zilvador.dk>
 * @author Erik BjÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ¸rn Pedersen <erik.pedersen@shaw.ca>
 * @author rasmus <rasmus@kinnerup.com>
 * @author Mikael Lyngvig <mikael@lyngvig.org>
 */
$lang['admin_acl']             = 'Rettighedsadministration';
$lang['acl_group']             = 'Gruppe:';
$lang['acl_user']              = 'Bruger:';
$lang['acl_perms']             = 'Rettigheder for';
$lang['page']                  = 'Dokument';
$lang['namespace']             = 'Navnerum';
$lang['btn_select']            = 'VÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ¦lg';
$lang['p_user_id']             = 'Bruger <b class="acluser">%s</b> har fÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ¸lgende adgang pÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ¥ siden <b class="aclpage">%s</b>: <i>%s</i>';
$lang['p_user_ns']             = 'Bruger <b class="acluser">%s</b> har forelÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ¸big fÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ¸lgende tilladelse i navnerummet <b class="aclns">%s</b>: <i>%s</i>.';
$lang['p_group_id']            = 'Medlemmerne af gruppen <b class="aclgroup">%s</b> har forelÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ¸bigt de fÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ¸lgende tilladelser pÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ¥ siden <b class="aclpage">%s</b>: <i>%s</i>.';
$lang['p_group_ns']            = 'Medlemmerne af gruppen <b class="aclgroup">%s</b> har forelÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ¸bigt de fÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ¸lgende tilladelser i navnerummet <b class="aclns">%s</b>: <i>%s</i>.';
$lang['p_choose_id']           = 'Venligst <b>udfyld en bruger eller gruppe</b> i ovennÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ¦vnte formular for at se eller redigere tilladelserne for denne side<b class="aclpage">%s</b>.';
$lang['p_choose_ns']           = 'Venligst <b>udfyld en bruger eller gruppe</b> i ovennÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ¦vnte formular for at se eller redigere tilladelserne for navnerummet <b class="aclns">%s</b>.';
$lang['p_inherited']           = 'BemÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ¦rk: Disse tilladelser var ikke lagt entydigt ind, men var arvet fra andre grupper eller hÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ¸jere navnerum.';
$lang['p_isadmin']             = 'BemÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ¦rk: Den valgte gruppe eller bruger har altid fuld adgang, fordi den er sat til at vÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ¦re en supergruppe eller -bruger';
$lang['p_include']             = 'HÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ¸jere tilladelse inkluderer ogsÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ¥ lavere. Tilladelser til at oprette, lÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ¦gge filer op og slette gÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ¦lder kun for navnerum, ikke sider.';
$lang['current']               = 'Aktuelle ACL-regler';
$lang['where']                 = 'Side/navnerum';
$lang['who']                   = 'Bruger/gruppe';
$lang['perm']                  = 'Rettigheder';
$lang['acl_perm_none']             = 'Ingen';
$lang['acl_perm_read']             = 'LÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ¦s';
$lang['acl_perm_edit']             = 'Skriv';
$lang['acl_perm_create']             = 'Opret';
$lang['acl_perm_upload']             = 'OverfÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ¸r';
$lang['acl_perm_delete']            = 'Slet';
$lang['acl_new']               = 'TilfÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ¸j ny post';
$lang['acl_mod']               = 'RedigÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ©r post';
