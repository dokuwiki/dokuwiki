<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Jens Hyllegaard <jens.hyllegaard@gmail.com>
 * @author soer9648 <soer9648@eucl.dk>
 * @author Jacob Palm <mail@jacobpalm.dk>
 */
$lang['server']                = 'Din LDAP server. Enten værtsnavn (<code>localhost</code>) eller fuld kvalificeret URL (<code>ldap://server.tld:389</code>)';
$lang['port']                  = 'LDAP server port, hvis der ikke er angivet en komplet URL ovenfor.';
$lang['usertree']              = 'Hvor findes brugerkonti. F.eks. <code>ou=Personer, dc=server, dc=tld</code>';
$lang['grouptree']             = 'Hvor findes brugergrupper. F.eks. <code>ou=Grupper, dc=server, dc=tld</code>';
$lang['userfilter']            = 'LDAP filter der benyttes til at søge efter brugerkonti. F.eks. <code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>';
$lang['groupfilter']           = 'LDAP filter tder benyttes til at søge efter grupper. F.eks. <code>(&amp;(objectClass=posixGroup)(|(gidNumber=%{gid})(memberUID=%{user})))</code>';
$lang['version']               = 'Protokol version der skal benyttes. Det er muligvis nødvendigt at sætte denne til <code>3</code>';
$lang['starttls']              = 'Benyt TLS forbindelser?';
$lang['bindpw']                = 'Kodeord til ovenstående bruger';
$lang['modPass']               = 'Kan LDAP adgangskoden skiftes via DokuWiki?';
$lang['debug']                 = 'Vis yderligere debug output ved fejl';
