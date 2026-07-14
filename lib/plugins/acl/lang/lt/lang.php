<?php

/**
 * Lithuanian language file
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Linas Valiukas <shirshegsm@gmail.com>
 * @author Donatas Glodenis <dgvirtual@gmail.com>
 * @author audrius.klevas <audrius.klevas@gmail.com>
 * @author Arunas Vaitekunas <aras@fan.lt>
 */
$lang['admin_acl']   = 'Prieigos valdymo sąrašų tvarkymas';
$lang['acl_group']   = 'Grupė:';
$lang['acl_user']    = 'Vartotojas:';
$lang['acl_perms']   = 'Leidimai skirti';
$lang['page']        = 'Puslapis';
$lang['namespace']   = 'Vardų erdvė';

$lang['btn_select']  = 'Rinktis';

$lang['p_user_id']   = 'Vartotojas <b class="acluser">%s</b> šiuo metu turi šiuos leidimus puslapyje <b class="aclpage">%s</b>: <i>%s</i>.';
$lang['p_user_ns']   = 'Vartotojas <b class="acluser">%s</b> šiuo metu turi šiuos leidimus vardų erdvėje <b class="aclns">%s</b>: <i>%s</i>.';
$lang['p_group_id']  = 'Grupės <b class="aclgroup">%s</b> nariai šiuo metu turi šiuos leidimus puslapyje <b class="aclpage">%s</b>: <i>%s</i>.';
$lang['p_group_ns']  = 'Grupės <b class="aclgroup">%s</b> nariai šiuo metu turi šiuos leidimus vardų erdvėje <b class="aclns">%s</b>: <i>%s</i>.';

$lang['p_choose_id'] = 'Norėdami peržiūrėti ar keisti leidimus, nustatytus puslapiui <b class="aclpage">%s</b>, viršuje esančioje formoje <b>įveskite vartotoją arba grupę</b>.';
$lang['p_choose_ns'] = 'Norėdami peržiūrėti ar keisti leidimus, nustatytus vardų erdvei <b class="aclpage">%s</b>, viršuje esančioje formoje <b>įveskite vartotoją arba grupę</b>.';

$lang['p_inherited'] = 'Pastaba: Šie leidimai nebuvo nustatyti tiesiogiai, bet paveldėti iš kitų grupių ar aukštesnių vardų erdvių.';
$lang['p_isadmin']   = 'Pastaba: Pasirinkta grupė ar vartotojas visada turi pilną leidimų komplektą, nes jie yra konfigūruoti kaip super vartotojai.';
$lang['p_include']   = 'Aukštesni leidimai apima žemesnius. Leidimai „Kurti“, „Įkelti“ ir „Trinti“ taikomi tik vardų erdvėms, ne puslapiams.';

$lang['current']     = 'Esamos prieigos taisyklės';
$lang['where']       = 'Puslapis/vardų erdvė';
$lang['who']         = 'Vartotojas/grupė';
$lang['perm']        = 'Leidimai';

$lang['acl_perm0']   = 'Nėra';
$lang['acl_perm1']   = 'Skaityti';
$lang['acl_perm2']   = 'Redaguoti';
$lang['acl_perm4']   = 'Kurti';
$lang['acl_perm8']   = 'Įkelti';
$lang['acl_perm16']  = 'Trinti';
$lang['acl_new']     = 'Pridėti naują įrašą';
$lang['acl_mod']     = 'Redaguoti įrašą';
