<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Reidar Mosvold <Reidar.Mosvold@hit.no>
 * @author Jorge Barrera Grandon <jorge@digitalwolves.org>
 * @author Thomas Nygreen <nygreen@gmail.com>
 * @author Arild Burud <arildb@met.no>
 * @author Torkill Bruland <torkar-b@online.no>
 * @author Rune M. Andersen <rune.andersen@gmail.com>
 * @author Jakob Vad Nielsen (me@jakobnielsen.net)
 * @author Kjell Tore Næsgaard <kjell.t.nasgaard@ntnu.no>
 * @author Knut Staring <knutst@gmail.com>
 * @author Lisa Ditlefsen <lisa@vervesearch.com>
 * @author Erik Pedersen <erik.pedersen@shaw.ca>
 * @author Erik Bjørn Pedersen <erik.pedersen@shaw.ca>
 * @author Rune Rasmussen syntaxerror.no@gmail.com
 * @author Jon Bøe <jonmagneboe@hotmail.com>
 * @author Egil Hansen <egil@rosetta.no>
 */
$lang['admin_acl']             = 'Administrasjon av lister for adgangskontroll (ACL)';
$lang['acl_group']             = 'Gruppe:';
$lang['acl_user']              = 'Bruker:';
$lang['acl_perms']             = 'Rettigheter for';
$lang['page']                  = 'Side';
$lang['namespace']             = 'Navnerom';
$lang['btn_select']            = 'Velg';
$lang['p_user_id']             = 'Bruker <b class="acluser">%s</b> har for tiden følgende tillatelser i for siden  <b class="aclpage">%s</b>: <i>%s</i>.';
$lang['p_user_ns']             = 'Bruker <b class="acluser">%s</b> har for tiden følgende tillatelser i navnerom <b class="aclns">%s</b>: <i>%s</i>.';
$lang['p_group_id']            = 'Medlemmer av gruppe <b class="aclgroup">%s</b> har for tiden følgende tillatelser i for siden <b class="aclpage">%s</b>: <i>%s</i>.';
$lang['p_group_ns']            = 'Medlemmer av gruppe <b class="aclgroup">%s</b> har for tiden følgende tillatelser i navnerom <b class="aclns">%s</b>: <i>%s</i>.';
$lang['p_choose_id']           = '<b>Før inn en bruker eller gruppe</b> i skjemaet over for å vise eller redigere tillatelser satt for siden <b class="aclpage">%s</b>.';
$lang['p_choose_ns']           = '<b>Før inn en bruker eller gruppe</b> i skjemaet over for å vise eller redigere tillatelser satt for navnerommet <b class="aclns">%s</b>.';
$lang['p_inherited']           = 'Merk: Disse tillatelser ble ikke eksplisitt satt, men ble arvet fra andre grupper eller høyere navnerom.';
$lang['p_isadmin']             = 'Merk: Den valgte gruppen eller bruker har altid fulle tillatelser fordi vedkommende er konfigurert som superbruker.';
$lang['p_include']             = 'Høyere tillgangsrettigheter inkluderer lavere. Rettigheter for å opprette, laste opp og slette gjelder bare  for navnerom, ikke enkeltsider.';
$lang['current']               = 'Gjeldende ACL-regler';
$lang['where']                 = 'Side/Navnerom';
$lang['who']                   = 'Bruker/Gruppe';
$lang['perm']                  = 'Rettigheter';
$lang['acl_perm0']             = 'Ingen';
$lang['acl_perm1']             = 'Lese';
$lang['acl_perm2']             = 'Redigere';
$lang['acl_perm4']             = 'Opprette';
$lang['acl_perm8']             = 'Laste opp';
$lang['acl_perm16']            = 'Slette';
$lang['acl_new']               = 'Legg til ny oppføring';
$lang['acl_mod']               = 'Endre oppføring';
