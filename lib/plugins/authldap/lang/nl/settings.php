<?php
/**
 * Dutch language file
 *
 */
$lang['server']                = 'Je LDAP server. Ofwel servernaam (<code>localhost</code>) of volledige URL (<code>ldap://server.tld:389</code>)';
$lang['port']                  = 'LDAP server poort als hiervoor geen volledige URL is opgegeven';
$lang['usertree']              = 'Locatie van de gebruikersaccounts. Bijv. <code>ou=Personen,dc=server,dc=tld</code>';
$lang['grouptree']             = 'Locatie van de gebruikersgroepen. Bijv. <code>ou=Group,dc=server,dc=tld</code>';
$lang['userfilter']            = 'LDAP gebruikersfilter. Bijv. <code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>';
$lang['groupfilter']           = 'LDAP groepsfilter. Bijv. <code>(&amp;(objectClass=posixGroup)(|(gidNumber=%{gid})(memberUID=%{user})))</code>';
$lang['version']               = 'Te gebruiken protocolversie. Je zou het moeten kunnen instellen op <code>3</code>';
$lang['starttls']              = 'Gebruiken TLS verbindingen';
$lang['referrals']             = 'Moeten verwijzingen worden gevolg';
$lang['binddn']                = 'DN van een optionele bind gebruiker als anonieme bind niet genoeg is. Bijv. <code>cn=beheer, dc=mijn, dc=thuis</code>';
$lang['bindpw']                = 'Wachtwoord van bovenstaande gebruiker';
$lang['userscope']             = 'Beperken scope van zoekfuncties voor gebruikers';
$lang['groupscope']            = 'Beperken scope van zoekfuncties voor groepen';
$lang['groupkey']              = 'Groepslidmaatschap van enig gebruikersattribuut (in plaats van standaard AD groepen), bijv. groep van afdeling of telefoonnummer';
$lang['debug']                 = 'Tonen van aanvullende debuginformatie bij fouten';
