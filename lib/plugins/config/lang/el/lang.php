<?php
/**
 * Greek language file
 *
 * Based on DokuWiki Version rc2007-05-24 english language file
 * Original english language file contents included for reference
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 * @author     Thanos Massias <tm@thriasio.gr>
 */

// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
//  $lang['menu']                   = 'Configuration Settings';
    $lang['menu']                   = 'Ρυθμίσεις';

//  $lang['error']                  = 'Settings not updated due to an invalid value, please review your changes and resubmit.
//                                    <br />The incorrect value(s) will be shown surrounded by a red border.';
    $lang['error']                  = 'Οι ρυθμίσεις σας δεν έγιναν δεκτές λόγω λανθασμένης τιμής κάποιας ρύθμισης. Διορθώστε την λάθος τιμή και προσπαθήστε ξανά.
                                      <br />Η λανθασμένη τιμή υποδεικνύεται με κόκκινο πλαίσιο.';
//  $lang['updated']                = 'Settings updated successfully.';
    $lang['updated']                = 'Επιτυχής τροποποίση ρυθμίσεων.';
//  $lang['nochoice']               = '(no other choices available)';
    $lang['nochoice']               = '(δεν υπάρχουν άλλες διαθέσιμες επιλογές)';
//  $lang['locked']                 = 'The settings file can not be updated, if this is unintentional, <br />
//                                    ensure the local settings file name and permissions are correct.';
    $lang['locked']                 = 'Το αρχείο ρυθμίσεων δεν μπορεί να τροποποιηθεί.
                                      <br />Εάν αυτό δεν είναι επιθυμητό, διορθώστε τα δικαιώματα πρόσβασης του αρχείου ρυθμίσεων';


/* --- Config Setting Headers --- */
//  $lang['_configuration_manager'] = 'Configuration Manager'; //same as heading in intro.txt
    $lang['_configuration_manager'] = 'Ρυθμίσεις'; //same as heading in intro.txt
//  $lang['_header_dokuwiki']       = 'DokuWiki Settings';
    $lang['_header_dokuwiki']       = 'Ρυθμίσεις DokuWiki';
//  $lang['_header_plugin']         = 'Plugin Settings';
    $lang['_header_plugin']         = 'Ρυθμίσεις Επεκτάσεων';
//  $lang['_header_template']       = 'Template Settings';
    $lang['_header_template']       = 'Ρυθμίσεις Προτύπων παρουσίασης';
//  $lang['_header_undefined']      = 'Undefined Settings';
    $lang['_header_undefined']      = 'Διάφορες Ρυθμίσεις';


/* --- Config Setting Groups --- */
//  $lang['_basic']                 = 'Basic Settings';
    $lang['_basic']                 = 'Βασικές Ρυθμίσεις';
//  $lang['_display']               = 'Display Settings';
    $lang['_display']               = 'Ρυθμίσεις Εμφάνισης';
//  $lang['_authentication']        = 'Authentication Settings';
    $lang['_authentication']        = 'Ρυθμίσεις Ασφαλείας';
//  $lang['_anti_spam']             = 'Anti-Spam Settings';
    $lang['_anti_spam']             = 'Ρυθμίσεις Anti-Spam';
//  $lang['_editing']               = 'Editing Settings';
    $lang['_editing']               = 'Ρυθμίσεις Σύνταξης σελίδων';
//  $lang['_links']                 = 'Link Settings';
    $lang['_links']                 = 'Ρυθμίσεις Συνδέσμων';
//  $lang['_media']                 = 'Media Settings';
    $lang['_media']                 = 'Ρυθμίσεις Αρχείων';
//  $lang['_advanced']              = 'Advanced Settings';
    $lang['_advanced']              = 'Ρυθμίσεις για Προχωρημένους';
//  $lang['_network']               = 'Network Settings';
    $lang['_network']               = 'Ρυθμίσεις Δικτύου';
// The settings group name for plugins and templates can be set with
// plugin_settings_name and template_settings_name respectively. If one
// of these lang properties is not set, the group name will be generated
// from the plugin or template name and the localized suffix.
//  $lang['_plugin_sufix']          = 'Plugin Settings';
    $lang['_plugin_sufix']          = 'Ρυθμίσεις Επεκτάσεων';
//  $lang['_template_sufix']        = 'Template Settings';
    $lang['_template_sufix']        = 'Ρυθμίσεις Προτύπων παρουσίασης';


/* --- Undefined Setting Messages --- */
//  $lang['_msg_setting_undefined']     = 'No setting metadata.';
    $lang['_msg_setting_undefined']     = 'Δεν έχουν οριστεί metadata.';
//  $lang['_msg_setting_no_class']      = 'No setting class.';
    $lang['_msg_setting_no_class']      = 'Δεν έχει οριστεί κλάση.';
//  $lang['_msg_setting_no_default']    = 'No default value.';
    $lang['_msg_setting_no_default']    = 'Δεν υπάρχει τιμή εξ ορισμού.';


/* -------------------- Config Options --------------------------- */

//  $lang['fmode']                  = 'File creation mode';
    $lang['fmode']                  = 'Δικαιώματα πρόσβασης δημιουργούμενων αρχείων';
//  $lang['dmode']                  = 'Directory creation mode';
    $lang['dmode']                  = 'Δικαιώματα πρόσβασης δημιουργούμενων φακέλων';
//  $lang['lang']                   = 'Language';
    $lang['lang']                   = 'Γλώσσα';
//  $lang['basedir']                = 'Base directory';
    $lang['basedir']                = 'Αρχικός Φάκελος';
//  $lang['baseurl']                = 'Base URL';
    $lang['baseurl']                = 'Αρχικό URL';
//  $lang['savedir']                = 'Directory for saving data';
    $lang['savedir']                = 'Φάκελος για την αποθήκευση δεδομένων';
//  $lang['start']                  = 'Start page name';
    $lang['start']                  = 'Ονομασία αρχικής σελίδας';
//  $lang['title']                  = 'Wiki title';
    $lang['title']                  = 'Τίτλος Wiki';
//  $lang['template']               = 'Template';
    $lang['template']               = 'Πρότυπο προβολής';
//  $lang['fullpath']               = 'Reveal full path of pages in the footer';
    $lang['fullpath']               = 'Εμφάνιση πλήρους διαδρομής σελίδας στην υποκεφαλίδα';
//  $lang['recent']                 = 'Recent changes';
    $lang['recent']                 = 'Αριθμός πρόσφατων αλλαγών ανά σελίδα';
//  $lang['breadcrumbs']            = 'Number of breadcrumbs';
    $lang['breadcrumbs']            = 'Αριθμός συνδέσμων ιστορικού';
//  $lang['youarehere']             = 'Hierarchical breadcrumbs';
    $lang['youarehere']             = 'Εμφάνιση ιεραρχικής προβολής τρέχουσας σελίδας';
//  $lang['typography']             = 'Do typographical replacements';
    $lang['typography']             = 'Μετατροπή ειδικών χαρακτήρων στο τυπογραφικό ισοδύναμό τους';
//  $lang['htmlok']                 = 'Allow embedded HTML';
    $lang['htmlok']                 = 'Να επιτρέπεται η ενσωμάτωση HTML';
//  $lang['phpok']                  = 'Allow embedded PHP';
    $lang['phpok']                  = 'Να επιτρέπεται η ενσωμάτωση PHP';
//  $lang['dformat']                = 'Date format (see PHP\'s <a href           ="http://www.php.net/date">date</a> function)';
    $lang['dformat']                = 'Μορφή ημερομηνίας (βλέπε την <a href="http://www.php.net/date">date</a> function της PHP)';
//  $lang['signature']              = 'Signature';
    $lang['signature']              = 'Υπογραφή';
//  $lang['toptoclevel']            = 'Top level for table of contents';
    $lang['toptoclevel']            = 'Ανώτατο επίπεδο πίνακα περιεχομένων σελίδας';
//  $lang['maxtoclevel']            = 'Maximum level for table of contents';
    $lang['maxtoclevel']            = 'Μέγιστο επίπεδο για πίνακα περιεχομένων σελίδας';
//  $lang['maxseclevel']            = 'Maximum section edit level';
    $lang['maxseclevel']            = 'Μέγιστο επίπεδο για εμφάνιση της επιλογής τροποποίησης επιπέδου';
//  $lang['camelcase']              = 'Use CamelCase for links';
    $lang['camelcase']              = 'Χρήση CamelCase στους συνδέσμους';
//  $lang['deaccent']               = 'Clean pagenames';
    $lang['deaccent']               = 'Αφαίρεση σημείων στίξης από ονόματα σελίδων';
//  $lang['useheading']             = 'Use first heading for pagenames';
    $lang['useheading']             = 'Χρήση κεφαλίδας πρώτου επιπέδου σαν τίτλο σθνδέσμων';
//  $lang['refcheck']               = 'Media reference check';
    $lang['refcheck']               = 'Πριν τη διαγραφή ενός αρχείου να ελέγχεται η ύπαρξη σελίδων που το χρησιμοποιούν';
//  $lang['refshow']                = 'Number of media references to show';
    $lang['refshow']                = 'Εμφανιζόμενος αριθμός σελίδων που χρησιμοποιούν ένα αρχείο';
//  $lang['allowdebug']             = 'Allow debug <b>disable if not needed!</b>';
    $lang['allowdebug']             = 'Δεδομένα εκσφαλμάτωσης (debug) <b>απενεργοποιήστε τα εάν δεν τα έχετε ανάγκη!</b>';


//  $lang['usewordblock']           = 'Block spam based on wordlist';
    $lang['usewordblock']           = 'Χρήστη λίστα απαγορευμένων λέξεων για καταπολέμηση του spam';
//  $lang['indexdelay']             = 'Time delay before indexing (sec)';
    $lang['indexdelay']             = 'Χρόνος αναμονής πρωτού επιταπεί σε μηχανές αναζήτησης να ευρετηριάσουν μια τροποποιημένη σελίδα (sec)';
//  $lang['relnofollow']            = 'Use rel           ="nofollow" on external links';
    $lang['relnofollow']            = 'Χρήση rel="nofollow"';
//  $lang['mailguard']              = 'Obfuscate email addresses';
    $lang['mailguard']              = 'Κωδικοποίηση e-mail διευθύνσεων';
//  $lang['iexssprotect']           = 'Check uploaded files for possibly malicious JavaScript or HTML code';
    $lang['iexssprotect']           = 'Έλεγχος μεταφορτώσεων για πιθανώς επικίνδυνο κώδικα JavaScript ή HTML';


/* Authentication Options */
//  $lang['useacl']                 = 'Use access control lists';
    $lang['useacl']                 = 'Χρήση Λίστας Δικαιωμάτων Πρόσβασης (ACL)';
//  $lang['autopasswd']             = 'Autogenerate passwords';
    $lang['autopasswd']             = 'Αυτόματη δημιουργία κωδικού χρήστη';
//  $lang['authtype']               = 'Authentication backend';
    $lang['authtype']               = 'Τύπος πιστοποίησης στοιχείων χρήστη';
//  $lang['passcrypt']              = 'Password encryption method';
    $lang['passcrypt']              = 'Μέθοδος κρυπτογράφησης κωδικού χρήστη';
//  $lang['defaultgroup']           = 'Default group';
    $lang['defaultgroup']           = 'Προεπιλεγμένη ομάδα χρηστών';
//  $lang['superuser']              = 'Superuser - a group or user with full access to all pages and functions regardless of the ACL settings';
    $lang['superuser']              = 'Υπερ-χρήστης - μία ομάδα ή ένας χρήστης με πλήρη δικαιώματα πρόσβασης σε όλες τις σελίδες και όλες τις λειτουργίες ανεξάρτητα από τις ρυθμίσης των Λιστών Δικαιωμάτων Πρόσβασης (ACL)';
//  $lang['manager']                = 'Manager - a group or user with access to certain management functions';
    $lang['manager']                = 'Διαχειριστής - μία ομάδα ή ένας χρήστης με δικαιώματα πρόσβασης σε ορισμένες από τις λειτουργίες της εφαρμογής';
//  $lang['profileconfirm']         = 'Confirm profile changes with password';
    $lang['profileconfirm']         = 'Να απαιτείται ο κωδικός χρήστη για την επιβεβαίωση αλλαγών στο προφίλ χρήστη';
//  $lang['disableactions']                 = 'Disable DokuWiki actions';
    $lang['disableactions']                 = 'Απενεργοποίηση λειτουργιών DokuWiki';
//  $lang['disableactions_check']           = 'Check';
    $lang['disableactions_check']           = 'Έλεγχος';
//  $lang['disableactions_subscription']    = 'Subscribe/Unsubscribe';
    $lang['disableactions_subscription']    = 'Εγγραφή/Διαγραφή χρήστη';
//  $lang['disableactions_wikicode']        = 'View source/Export Raw';
    $lang['disableactions_wikicode']        = 'Προβολή κώδικα σελίδας';
//  $lang['disableactions_other']   = 'Other actions (comma separated)';
    $lang['disableactions_other']   = 'Άλλες λειτουργίες (διαχωρίστε τις με κόμμα)';
//  $lang['sneaky_index']           = 'By default, DokuWiki will show all namespaces in the index view. Enabling this option will hide those where the user doesn\'t have read permissions. This might result in hiding of accessable subnamespaces. This may make the index unusable with certain ACL setups.';
    $lang['sneaky_index']           = 'Εξ ορισμού, η εφαρμογή DokuWiki δείχνει όλους τους φακέλους στην προβολή Καταλόγου.
                                      Ενεργοποιώντας αυτή την επιλογή, δεν θα εμφανίζονται οι φάκελοι για τους οποίους ο χρήστης δεν έχει δικαιώματα ανάγνωσης αλλά και οι υπο-φάκελοί τους ανεξαρτήτως δικαιωμάτων πρόσβασης.';


/* Advanced Options */
//  $lang['updatecheck']            = 'Check for updates and security warnings? DokuWiki needs to contact splitbrain.org for this feature.';
    $lang['updatecheck']            = 'Έλεγχος για ύπαρξη νέων εκδόσεων και ενημερώσεων ασφαλείας της εφαρμογής? Απαιτείται η σύνδεση με το splitbrain.org για να λειτουργήσει σωστά αυτή η επιλογή.';
//  $lang['userewrite']             = 'Use nice URLs';
    $lang['userewrite']             = 'Χρήση ωραίων URLs';
//  $lang['useslash']               = 'Use slash as namespace separator in URLs';
    $lang['useslash']               = 'Χρήση slash σαν διαχωριστικό φακέλων στα URLs';
//  $lang['usedraft']               = 'Automatically save a draft while editing';
    $lang['usedraft']               = 'Αυτόματη αποθήκευση αντιγράφων κατά την τροποποίηση σελίδων';
//  $lang['sepchar']                = 'Page name word separator';
    $lang['sepchar']                = 'Διαχωριστικός χαρακτήρας για κανονικοποίση ονόματος σελίδας';
//  $lang['canonical']              = 'Use fully canonical URLs';
    $lang['canonical']              = 'Πλήρη και κανονικοποιημένα URLs';
//  $lang['autoplural']             = 'Check for plural forms in links';
    $lang['autoplural']             = 'Ταίριασμα πληθυντικού στους συνδέσμους';
//  $lang['compression']            = 'Compression method for attic files';
    $lang['compression']            = 'Μέθοδος συμπίεσης για αρχεία attic';
//  $lang['cachetime']              = 'Maximum age for cache (sec)';
    $lang['cachetime']              = 'Μέγιστη ηλικία cache (sec)';
//  $lang['locktime']               = 'Maximum age for lock files (sec)';
    $lang['locktime']               = 'Μέγιστος χρόνος κλειδώματος αρχείου υπό τροποποίηση (sec)';
//  $lang['fetchsize']              = 'Maximum size (bytes) fetch.php may download from extern';
    $lang['fetchsize']              = 'Μέγιστο μέγεθος (σε bytes) εξωτερικού αρχείου που επιτρέπεται να μεταφέρει η fetch.php';
//  $lang['notify']                 = 'Send change notifications to this email address';
    $lang['notify']                 = 'Αποστολή ενημέρωσης για αλλαγές σε αυτή την e-mail διεύθυνση';
//  $lang['registernotify']         = 'Send info on newly registered users to this email address';
    $lang['registernotify']         = 'Αποστολή ενημερωτικών μυνημάτων σε αυτή την e-mail διεύθυνση κατά την εγγραφή νέων χρηστών';
//  $lang['mailfrom']               = 'Email address to use for automatic mails';
    $lang['mailfrom']               = 'e-mail διεύθυνση αποστολέα για μηνύματα από την εφαρμογή';
//  $lang['gzip_output']            = 'Use gzip Content-Encoding for xhtml';
    $lang['gzip_output']            = 'Χρήση gzip Content-Encoding για την xhtml';
//  $lang['gdlib']                  = 'GD Lib version';
    $lang['gdlib']                  = 'Έκδοση βιβλιοθήκης GD';
//  $lang['im_convert']             = 'Path to ImageMagick\'s convert tool';
    $lang['im_convert']             = 'Διαδρομή προς το εργαλείο μετατροπής εικόνων του ImageMagick';
//  $lang['jpg_quality']            = 'JPG compression quality (0-100)';
    $lang['jpg_quality']            = 'Ποιότητα συμπίεσης JPG (0-100)';
//  $lang['spellchecker']           = 'Enable spellchecker';
    $lang['spellchecker']           = 'Ενεργοποίηση ελέγχου ορθογραφίας';
//  $lang['subscribers']            = 'Enable page subscription support';
    $lang['subscribers']            = 'Να επιτρέπεται η εγγραφή στην ενημέρωση αλλαγών σελίδας';
//  $lang['compress']               = 'Compact CSS and javascript output';
    $lang['compress']               = 'Συμπίεση αρχείων CSS και javascript';
//  $lang['hidepages']              = 'Hide matching pages (regular expressions)';
    $lang['hidepages']              = 'Φίλτρο απόκρυψης σελίδων (regular expressions)';
//  $lang['send404']                = 'Send "HTTP 404/Page Not Found" for non existing pages';
    $lang['send404']                = 'Αποστολή "HTTP 404/Page Not Found" για σελίδες που δεν υπάρχουν';
//  $lang['sitemap']                = 'Generate Google sitemap (days)';
    $lang['sitemap']                = 'Δημιουργία Google sitemap (ημέρες)';
//  $lang['broken_iua']             = 'Is the ignore_user_abort function broken on your system? This could cause a non working search index. IIS+PHP/CGI is known to be broken. See <a href="http://bugs.splitbrain.org/?do=details&amp;task_id=852">Bug 852</a> for more info.';
    $lang['broken_iua']             = 'Η συνάρτηση ignore_user_abort δεν λειτουργεί σωστά στο σύστημά σας? Σε αυτή την περίπτωση μπορεί να μην δουλεύει σωστά η λειτουργία Καταλόγου. Ο συνδυασμός IIS+PHP/CGI είναι γνωστό ότι έχει τέτοιο πρόβλημα. Δείτε και <a href="http://bugs.splitbrain.org/?do=details&amp;task_id=852">Bug 852</a> για λεπτομέρειες.';


//  $lang['rss_type']               = 'XML feed type';
    $lang['rss_type']               = 'Τύπος XML feed';
//  $lang['rss_linkto']             = 'XML feed links to';
    $lang['rss_linkto']             = 'Τύπος συνδέσμων στο XML feed';
//  $lang['rss_update']             = 'XML feed update interval (sec)';
    $lang['rss_update']             = 'Χρόνος ανανέωσης XML feed (sec)';
//  $lang['recent_days']            = 'How many recent changes to keep (days)';
    $lang['recent_days']            = 'Πόσο παλιές αλλαγές να εμφανίζονται (ημέρες)';
//  $lang['rss_show_summary']       = 'XML feed show summary in title';
    $lang['rss_show_summary']       = 'Να εμφανίζεται σύνοψη του XML feed στον τίτλο';


/* Target options */
//  $lang['target____wiki']         = 'Target window for internal links';
    $lang['target____wiki']         = 'Παράθυρο-στόχος για εσωτερικούς συνδέσμους';
//  $lang['target____interwiki']    = 'Target window for interwiki links';
    $lang['target____interwiki']    = 'Παράθυρο-στόχος για συνδέσμους interwiki';
//  $lang['target____extern']       = 'Target window for external links';
    $lang['target____extern']       = 'Παράθυρο-στόχος για εξωτερικούς σθνδέσμους';
//  $lang['target____media']        = 'Target window for media links';
    $lang['target____media']        = 'Παράθυρο-στόχος για συνδέσμους αρχείων';
//  $lang['target____windows']      = 'Target window for windows links';
    $lang['target____windows']      = 'Παράθυρο-στόχος για συνδέσμους σε Windows shares';


/* Proxy Options */
//  $lang['proxy____host']          = 'Proxy servername';
    $lang['proxy____host']          = 'Διακομιστής Proxy';
//  $lang['proxy____port']          = 'Proxy port';
    $lang['proxy____port']          = 'Θύρα Proxy';
//  $lang['proxy____user']          = 'Proxy user name';
    $lang['proxy____user']          = 'Όνομα χρήστη Proxy';
//  $lang['proxy____pass']          = 'Proxy password';
    $lang['proxy____pass']          = 'Κωδικός χρήστη Proxy';
//  $lang['proxy____ssl']           = 'Use ssl to connect to Proxy';
    $lang['proxy____ssl']           = 'Χρήση ssl για σύνδεση με διακομιστή Proxy';


/* Safemode Hack */
//  $lang['safemodehack']           = 'Enable safemode hack';
    $lang['safemodehack']           = 'Ενεργοποίηση safemode hack';
//  $lang['ftp____host']            = 'FTP server for safemode hack';
    $lang['ftp____host']            = 'Διακομιστής FTP για safemode hack';
//  $lang['ftp____port']            = 'FTP port for safemode hack';
    $lang['ftp____port']            = 'Θύρα FTP για safemode hack';
//  $lang['ftp____user']            = 'FTP user name for safemode hack';
    $lang['ftp____user']            = 'Όνομα χρήστη FTP για safemode hack';
//  $lang['ftp____pass']            = 'FTP password for safemode hack';
    $lang['ftp____pass']            = 'Κωδικός χρήστη FTP για safemode hack';
//  $lang['ftp____root']            = 'FTP root directory for safemode hack';
    $lang['ftp____root']            = 'Αρχικός φάκελος FTP για safemode hack';


/* userewrite options */
//  $lang['userewrite_o_0']         = 'none';
    $lang['userewrite_o_0']         = 'όχι';
//  $lang['userewrite_o_1']         = '.htaccess';
    $lang['userewrite_o_1']         = '.htaccess';
//  $lang['userewrite_o_2']         = 'DokuWiki internal';
    $lang['userewrite_o_2']         = 'από DokuWiki';


/* deaccent options */
//  $lang['deaccent_o_0']           = 'off';
    $lang['deaccent_o_0']           = 'όχι';
//  $lang['deaccent_o_1']           = 'remove accents';
    $lang['deaccent_o_1']           = 'Αφαίρεση σημείων στίξης';
//  $lang['deaccent_o_2']           = 'romanize';
    $lang['deaccent_o_2']           = 'Λατινοποίηση';


/* gdlib options */
//  $lang['gdlib_o_0']              = 'GD Lib not available';
    $lang['gdlib_o_0']              = 'Δεν υπάρχει βιβλιοθήκη GD στο σύστημα';
//  $lang['gdlib_o_1']              = 'Version 1.x';
    $lang['gdlib_o_1']              = 'Έκδοση 1.x';
//  $lang['gdlib_o_2']              = 'Autodetection';
    $lang['gdlib_o_2']              = 'Αυτόματος εντοπισμός';


/* rss_type options */
    $lang['rss_type_o_rss']         = 'RSS 0.91';
    $lang['rss_type_o_rss1']        = 'RSS 1.0';
    $lang['rss_type_o_rss2']        = 'RSS 2.0';
    $lang['rss_type_o_atom']        = 'Atom 0.3';


/* rss_linkto options */
//  $lang['rss_linkto_o_diff']      = 'difference view';
    $lang['rss_linkto_o_diff']      = 'προβολή αλλαγών';
//  $lang['rss_linkto_o_page']      = 'the revised page';
    $lang['rss_linkto_o_page']      = 'τροποποιημένη σελίδα';
//  $lang['rss_linkto_o_rev']       = 'list of revisions';
    $lang['rss_linkto_o_rev']       = 'εκδόσεις σελίδας';
//  $lang['rss_linkto_o_current']   = 'the current page';
    $lang['rss_linkto_o_current']   = 'τρέχουσα σελίδα';


/* compression options */
    $lang['compression_o_0']        = 'none';
    $lang['compression_o_gz']       = 'gzip';
    $lang['compression_o_bz2']      = 'bz2';

