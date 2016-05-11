<?php
$lang['server']      = 'Eich gweinydd LDAP. Naill ai enw\'r gweinydd (<code>localhost</code>) neu\'r URL llawn (<code>ldap://server.tld:389</code>)';
$lang['port']        = 'Porth gweinydd LDAP os nac oes URL llawn wedi\'i gyflwyno uchod';
$lang['usertree']    = 'Ble i ddarganfod cyfrifon defnyddwyr. Ee. <code>ou=People, dc=server, dc=tld</code>';
$lang['grouptree']   = 'Ble i ddarganfod y grwpiau defnyddiwr. Eg. <code>ou=Group, dc=server, dc=tld</code>';
$lang['userfilter']  = 'Hidlydd LDAP i ddarganfod cyfrifon defnyddwyr. Eg. <code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>';
$lang['groupfilter'] = 'Hidlydd LDAP i chwilio am grwpiau. Eg. <code>(&amp;(objectClass=posixGroup)(|(gidNumber=%{gid})(memberUID=%{user})))</code>';
$lang['version']     = 'Y fersiwn protocol i\'w ddefnyddio. Efallai bydd angen gosod hwn i <code>3</code>';
$lang['starttls']    = 'Defnyddio cysylltiadau TLS?';
$lang['referrals']   = 'Dilyn cyfeiriadau (referrals)?';
$lang['deref']       = 'Sut i ddadgyfeirio alias?'; //alias - enw arall?
$lang['binddn']      = 'DN rhwymiad defnyddiwr opsiynol os ydy rhwymiad anhysbys yn annigonol. Ee. <code>cn=admin, dc=my, dc=home</code>';
$lang['bindpw']      = 'Cyfrinair y defnyddiwr uchod';
$lang['userscope']   = 'Cyfyngu sgôp chwiliadau ar gyfer chwiliad defnyddwyr';
$lang['groupscope']  = 'Cyfyngu sgôp chwiliadau ar gyfer chwiliad grwpiau';
$lang['userkey']     = 'Priodoledd yn denodi\'r defnyddair; rhaid iddo fod yn gyson i \'r hidlydd defnyddwyr.';
$lang['groupkey']    = 'Aelodaeth grŵp o unrhyw briodoledd defnyddiwr (yn hytrach na grwpiau AD safonol) e.e. grŵp o adran neu rif ffôn';
$lang['modPass']     = 'Gall cyfrinair LDAP gael ei newid gan DokuWiki?';
$lang['debug']       = 'Dangos gwybodaeth dadfygio ychwanegol gyda gwallau';


$lang['deref_o_0']   = 'LDAP_DEREF_NEVER';
$lang['deref_o_1']   = 'LDAP_DEREF_SEARCHING';
$lang['deref_o_2']   = 'LDAP_DEREF_FINDING';
$lang['deref_o_3']   = 'LDAP_DEREF_ALWAYS';

$lang['referrals_o_-1'] = 'defnyddio\'r diofyn';
$lang['referrals_o_0']  = 'peidio dilyn cyfeiriadau';
$lang['referrals_o_1']  = 'dilyn cyfeiriadau';