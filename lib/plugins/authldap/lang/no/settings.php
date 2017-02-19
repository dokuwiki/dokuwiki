<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Christopher Schive <chschive@frisurf.no>
 * @author Patrick <spill.p@hotmail.com>
 * @author Arne Hanssen <arne.hanssen@getmail.no>
 * @author Arne Hanssen <arnehans@getmail.no>
 */
$lang['server']                = 'Din LDAP-server. Enten  vertsnavnet (<code>localhost</code>) eller hele URL (<code>ldap://server.tld:389</code>)';
$lang['port']                  = 'LDAP serverport dersom ingen full URL var gitt over.';
$lang['usertree']              = 'Hvor en kan finne brukerkontoer. F.eks. Eg. <code>ou=People, dc=server, dc=tld</code>';
$lang['grouptree']             = 'Hvor en kan finne brukergrupper. F.eks. <code>ou=Group, dc=server, dc=tld</code>';
$lang['userfilter']            = 'LDAP-filter for å søke etter brukerkontoer. F.eks. <code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>';
$lang['groupfilter']           = 'LDAP-filter for å søke etter grupper. F.eks.. <code>(&amp;(objectClass=posixGroup)(|(gidNumber=%{gid})(memberUID=%{user})))</code>';
$lang['version']               = 'Protokollversjonen som skal brukes. Mulig du må sette denne til <code>3</code>';
$lang['starttls']              = 'Bruke TLS-forbindelser?';
$lang['referrals']             = 'Skal pekere som henviser til noe følges?';
$lang['deref']                 = 'Hvordan finne hva aliaser refererer til?';
$lang['binddn']                = 'DN (Distinguished Name) til en valgfri bind-bruker, angis dersom annonym bind ikke er tilstrekkelig. f.eks.. <code>cn=admin, dc=my, dc=home</code>';
$lang['bindpw']                = 'Passord til brukeren over';
$lang['userscope']             = 'Begrens søk til brukere';
$lang['groupscope']            = 'Begrens søk til grupper';
$lang['userkey']               = 'Attributt som angir brukernavn; må være konsistent for brukerfiltrering.';
$lang['groupkey']              = 'Gruppemedlemskap fra brukerattributt (i stedet for standard AD-grupper) f.eks gruppe fra avdeling, eller telefonnummer';
$lang['modPass']               = 'Kan LDAP-passordet endres via DokuWiki?';
$lang['debug']                 = 'Ved feil, vis tilleggsinformasjon for feilsøking';
$lang['deref_o_0']             = 'LDAP_DEREF_NEVER';
$lang['deref_o_1']             = 'LDAP_DEREF_SEARCHING';
$lang['deref_o_2']             = 'LDAP_DEREF_FINDING';
$lang['deref_o_3']             = 'LDAP_DEREF_ALWAYS';
$lang['referrals_o_-1']        = 'bruk standard';
$lang['referrals_o_0']         = 'ikke følg referanser';
$lang['referrals_o_1']         = 'følg referanser';
