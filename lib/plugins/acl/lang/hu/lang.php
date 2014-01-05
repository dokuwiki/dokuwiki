<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Sandor TIHANYI <stihanyi+dw@gmail.com>
 * @author Siaynoq Mage <siaynoqmage@gmail.com>
 * @author schilling.janos@gmail.com
 * @author Szabó Dávid <szabo.david@gyumolcstarhely.hu>
 * @author Sándor TIHANYI <stihanyi+dw@gmail.com>
 * @author David Szabo <szabo.david@gyumolcstarhely.hu>
 * @author Marton Sebok <sebokmarton@gmail.com>
 */
$lang['admin_acl']             = 'Hozzáférési lista (ACL) kezelő';
$lang['acl_group']             = 'Csoport:';
$lang['acl_user']              = 'Felhasználó:';
$lang['acl_perms']             = 'Jogosultság ehhez:';
$lang['page']                  = 'Oldal';
$lang['namespace']             = 'Névtér';
$lang['btn_select']            = 'Kiválaszt';
$lang['p_user_id']             = 'A(z) <b class="acluser">%s</b> felhasználónak jelenleg a következő jogosultsága van ezen az oldalon:  <b class="aclpage">%s</b>: <i>%s</i>.';
$lang['p_user_ns']             = 'A(z) <b class="acluser">%s</b> felhasználónak jelenleg a következő jogosultsága van ebben a névtérben: <b class="aclns">%s</b>: <i>%s</i>.';
$lang['p_group_id']            = 'A(z) <b class="aclgroup">%s</b> csoport tagjainak jelenleg a következő jogosultsága van ezen az oldalon:  <b class="aclpage">%s</b>: <i>%s</i>.';
$lang['p_group_ns']            = 'A(z) <b class="aclgroup">%s</b> csoport tagjainak jelenleg a következő jogosultsága van ebben a névtérben: <b class="aclns">%s</b>: <i>%s</i>.';
$lang['p_choose_id']           = 'A felső űrlapon <b>adjon meg egy felhasználót vagy csoportot</b>, akinek a(z) <b class="aclpage">%s</b> oldalhoz beállított jogosultságait megtekinteni vagy változtatni szeretné.';
$lang['p_choose_ns']           = 'A felső űrlapon <b>adj meg egy felhasználót vagy csoportot</b>, akinek a(z) <b class="aclns">%s</b> névtérhez beállított jogosultságait megtekinteni vagy változtatni szeretnéd.';
$lang['p_inherited']           = 'Megjegyzés: ezek a jogok nem itt lettek explicit beállítva, hanem öröklődtek egyéb csoportokból vagy felsőbb névterekből.';
$lang['p_isadmin']             = 'Megjegyzés: a kiválasztott csoportnak vagy felhasználónak mindig teljes jogosultsága lesz, mert Adminisztrátornak van beállítva.';
$lang['p_include']             = 'A magasabb szintű jogok tartalmazzák az alacsonyabbakat. A Létrehozás, Feltöltés és Törlés jogosultságok csak névterekre alkalmazhatók, az egyes oldalakra nem.';
$lang['current']               = 'Jelenlegi hozzáférési szabályok';
$lang['where']                 = 'Oldal/Névtér';
$lang['who']                   = 'Felhasználó/Csoport';
$lang['perm']                  = 'Jogosultságok';
$lang['acl_perm0']             = 'Semmi';
$lang['acl_perm1']             = 'Olvasás';
$lang['acl_perm2']             = 'Szerkesztés';
$lang['acl_perm4']             = 'Létrehozás';
$lang['acl_perm8']             = 'Feltöltés';
$lang['acl_perm16']            = 'Törlés';
$lang['acl_new']               = 'Új bejegyzés hozzáadása';
$lang['acl_mod']               = 'Bejegyzés módosítása';
