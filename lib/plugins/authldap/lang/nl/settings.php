<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author PBU <pbu@xs4all.nl>
 * @author Gerrit Uitslag <klapinklapin@gmail.com>
 * @author Remon <no@email.local>
 * @author Johan Wijnker <johan@wijnker.eu>
 */
$lang['server']                = 'Je LDAP server. Of de servernaam (<code>localhost</code>) of de volledige URL (<code>ldap://server.tld:389</code>)';
$lang['port']                  = 'LDAP server poort als bij de entry hierboven geen volledige URL is opgegeven';
$lang['usertree']              = 'Locatie van de gebruikersaccounts. Bijv. <code>ou=Personen,dc=server,dc=tld</code>';
$lang['grouptree']             = 'Locatie van de gebruikersgroepen. Bijv. <code>ou=Group,dc=server,dc=tld</code>';
$lang['userfilter']            = 'LDAP gebruikersfilter. Bijv. <code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>';
$lang['groupfilter']           = 'LDAP groepsfilter. Bijv. <code>(&amp;(objectClass=posixGroup)(|(gidNumber=%{gid})(memberUID=%{user})))</code>';
$lang['version']               = 'Te gebruiken protocolversie. Mogelijk dat dit ingesteld moet worden op <code>3</code>';
$lang['starttls']              = 'Gebruik maken van TLS verbindingen?';
$lang['referrals']             = 'Moeten verwijzingen worden gevolgd?';
$lang['deref']                 = 'Hoe moeten de verwijzing van aliases worden bepaald?';
$lang['binddn']                = 'DN van een optionele bind gebruiker als anonieme bind niet genoeg is. Bijv. <code>cn=beheer, dc=mijn, dc=thuis</code>';
$lang['bindpw']                = 'Wachtwoord van bovenstaande gebruiker';
$lang['attributes']            = 'Welke onderdelen moeten in LDAP gezocht worden';
$lang['userscope']             = 'Beperken scope van zoekfuncties voor gebruikers';
$lang['groupscope']            = 'Beperken scope van zoekfuncties voor groepen';
$lang['userkey']               = 'Attribuut aanduiding van de gebruikersnaam; moet consistent zijn met userfilter.';
$lang['groupkey']              = 'Groepslidmaatschap van enig gebruikersattribuut (in plaats van standaard AD groepen), bijv. groep van afdeling of telefoonnummer';
$lang['modPass']               = 'Kan het LDAP wachtwoord worden gewijzigd met DokuWiki?';
$lang['debug']                 = 'Tonen van aanvullende debuginformatie bij fouten';
$lang['deref_o_0']             = 'LDAP_DEREF_NEVER';
$lang['deref_o_1']             = 'LDAP_DEREF_SEARCHING';
$lang['deref_o_2']             = 'LDAP_DEREF_FINDING';
$lang['deref_o_3']             = 'LDAP_DEREF_ALWAYS';
$lang['referrals_o_-1']        = 'gebruik standaard';
$lang['referrals_o_0']         = 'volg verwijzing niet';
$lang['referrals_o_1']         = 'volg verwijzing';
