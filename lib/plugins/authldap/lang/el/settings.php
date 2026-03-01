<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Aikaterini Katapodi <extragold1234@hotmail.com>
 */
$lang['server']                = 'Ο διακομιστής σας LDAP. Είτε το κύριο όνομα ή ολόκληρο το URL ';
$lang['port']                  = 'Η πύλη διακομιστή LDAP αν δεν εδόθη ολόκληρο το URL ';
$lang['usertree']              = 'Πού μπορούν να βρεθούν οι λογαριασμοί χρήστη.. Π.χ . <code>ou=Κοινό , dc=server, dc=tld</code>	';
$lang['grouptree']             = 'Πού μπορούν να βρεθούν οι ομάδες χρήστη. Πχ. <code>ou=Group, dc=server, dc=tld</code>	';
$lang['userfilter']            = 'LDAP φίλτρο προς αναζήτηση λογαριασμών χρήστη Πχ . <code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>	';
$lang['groupfilter']           = 'LDAP φίλτρο προς αναζήτηση ομάδων . Πχ. <code>(&amp;(objectClass=posixGroup)(|(gidNumber=%{gid})(memberUID=%{user})))</code>	';
$lang['version']               = 'Η έκδοση πρωτοκόλλου προς χρήση. Μπορεί να χρειαστείτε να τοποθετήσετε αυτό στον <κωδικό> 3 </κωδικός>';
$lang['starttls']              = 'Να γίνει χρήση συνδέσεων TLS?';
$lang['referrals']             = 'Να ακολουθηθούν τα μέλη αναφοράς?';
$lang['deref']                 = 'Πώς να σβηστεί η αναφορά aliases?';
$lang['binddn']                = 'To DN ενός προαιρετικού επίσημου χρήστη αν ο ανώνυμος σύνδεσμος  δεν είναι επαρκής Πχ. <code>cn=admin, dc=my, dc=home</code>	';
$lang['bindpw']                = 'Ο κωδικός πρόσβασης του άνω χρήστη';
$lang['userscope']             = 'Περιορισμός του εύρους αναζήτησης χρήστη';
$lang['groupscope']            = 'Περιορίστε το εύρος της αναζήτησης για αναζήτηση ομάδας';
$lang['userkey']               = ' Η Καταχώρηση του ονόματος χρήστη πρέπει να είναι σύμφωνα με την ανάλυση (=φίλτρο) χρήστη.';
$lang['groupkey']              = 'Εγγραφή ομάδας ως μέλους από οιαδήποτε κατηγορία χρήστη (αντί των στάνταρ ομάδων AD) πχ ομάδα τμήματος ή αριθμός τηλεφώνου';
$lang['modPass']               = 'Μπορεί ο κωδικός πρόσβασης LDAP να αλλάξει μέσω του dokuwiki?';
$lang['debug']                 = 'Προβολή επιπλέον πληροφοριών για την ανεύρεση σφαλμάτων';
$lang['deref_o_0']             = 'LDAP_DEREF_NEVER	';
$lang['deref_o_1']             = 'LDAP_DEREF_SEARCHING';
$lang['deref_o_2']             = 'LDAP_DEREF_FINDING	';
$lang['deref_o_3']             = 'LDAP_DEREF_ALWAYS	';
$lang['referrals_o_-1']        = 'προεπιλογή χρήσης';
$lang['referrals_o_0']         = 'μην ακολουθείτε τα νέα μέλη αναφοράς';
$lang['referrals_o_1']         = 'ακολουθείστε τα νέα μέλη αναφοράς ';
