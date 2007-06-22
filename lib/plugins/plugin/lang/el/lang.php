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

//  $lang['menu']       = 'Manage Plugins';
    $lang['menu']       = 'Διαχείριση Επεκτάσεων'; 

// custom language strings for the plugin
//  $lang['download']           = "Download and install a new plugin";
    $lang['download']           = "Μεταφόρτωση και εγκατάσταση νέας επέκτασης";
//  $lang['manage']             = "Installed Plugins";
    $lang['manage']             = "Εγκατεστημένες επεκτάσεις";

//  $lang['btn_info']           = 'info';
    $lang['btn_info']           = 'πληροφορίες';
//  $lang['btn_update']         = 'update';
    $lang['btn_update']         = 'ενημέρωση';
//  $lang['btn_delete']         = 'delete';
    $lang['btn_delete']         = 'διαγραφή';
//  $lang['btn_settings']       = 'settings';
    $lang['btn_settings']       = 'ρυθμίσεις';
//  $lang['btn_download']       = 'Download';
    $lang['btn_download']       = 'Μεταφόρτωση';
//  $lang['btn_enable']         = 'Save';
    $lang['btn_enable']         = 'Αποθήκευση';

//  $lang['url']                = 'URL';
    $lang['url']                = 'URL';

//  $lang['installed']          = 'Installed:';
    $lang['installed']          = 'Εγκατεστημένη:';
//  $lang['lastupdate']         = 'Last updated:';
    $lang['lastupdate']         = 'Τελευταία ενημέρωση:';
//  $lang['source']             = 'Source:';
    $lang['source']             = 'Προέλευση:';
//  $lang['unknown']            = 'unknown';
    $lang['unknown']            = 'άγνωστο';

// ..ing = header message
// ..ed = success message

//  $lang['updating']           = 'Updating ...';
    $lang['updating']           = 'Σε διαδικασία ενημέρωσης ...';
//  $lang['updated']            = 'Plugin %s updated successfully';
    $lang['updated']            = 'Η επέκταση %s ενημερώθηκε με επιτυχία';
//  $lang['updates']            = 'The following plugins have been updated successfully';
    $lang['updates']            = 'Οι παρακάτω επεκτάσεις ενημερώθηκαν με επιτυχία:';
//  $lang['update_none']        = 'No updates found.';
    $lang['update_none']        = 'Δεν βρέθηκαν ενημερώσεις.';

//  $lang['deleting']           = 'Deleting ...';
    $lang['deleting']           = 'Σε διαδικασία διαγραφής ...';
//  $lang['deleted']            = 'Plugin %s deleted.';
    $lang['deleted']            = 'Η επέκταση %s διαγράφηκε.';

//  $lang['downloading']        = 'Downloading ...';
    $lang['downloading']        = 'Σε διαδικασία μεταφόρτωσης ...';
//  $lang['downloaded']         = 'Plugin %s installed successfully';
    $lang['downloaded']         = 'Η επέκταση %s εγκαταστάθηκε με επιτυχία';
//  $lang['downloads']          = 'The following plugins have been installed successfully:';
    $lang['downloads']          = 'Οι παρακάτω επεκτάσεις εγκαταστάθηκαν με επιτυχία:';
//  $lang['download_none']      = 'No plugins found, or there has been an unknown problem during downloading and installing.';
    $lang['download_none']      = 'Δεν βρέθηκαν επεκτάσεις ή εμφανίστηκε κάποιο πρόβλημα κατά την σχετική διαδικασία.';

// info titles
//  $lang['plugin']             = 'Plugin:';
    $lang['plugin']             = 'Επέκταση:';
//  $lang['components']         = 'Components';
    $lang['components']         = 'Συστατικά';
//  $lang['noinfo']             = 'This plugin returned no information, it may be invalid.';
    $lang['noinfo']             = 'Αυτή η επέκταση δεν επέστρεψε κάποια πληροφορία - η επέκταση μπορεί να μην λειτουργεί κανονικά.';
//  $lang['name']               = 'Name:';
    $lang['name']               = 'Όνομα:';
//  $lang['date']               = 'Date:';
    $lang['date']               = 'Ημερομηνία:';
//  $lang['type']               = 'Type:';
    $lang['type']               = 'Τύπος:';
//  $lang['desc']               = 'Description:';
    $lang['desc']               = 'Περιγραφή:';
//  $lang['author']             = 'Author:';
    $lang['author']             = 'Συγγραφέας:';
//  $lang['www']                = 'Web:';
    $lang['www']                = 'Διεύθυνση στο διαδίκτυο:';

// error messages
//  $lang['error']              = 'An unknown error occurred.';
    $lang['error']              = 'Εμφανίστηκε άγνωστο σφάλμα.';
//  $lang['error_download']     = 'Unable to download the plugin file: %s';
    $lang['error_download']     = 'Δεν είναι δυνατή η μεταφόρτωση του αρχείου: %s';
//  $lang['error_badurl']       = 'Suspect bad url - unable to determine file name from the url';
    $lang['error_badurl']       = 'Το URL είναι μάλλον λανθασμένο - είναι αδύνατον να εξαχθεί το όνομα αρχείου από αυτό το URL';
//  $lang['error_dircreate']    = 'Unable to create temporary folder to receive download';
    $lang['error_dircreate']    = 'Δεν είναι δυνατή η δημιουργία ενός προσωρινού φακέλου αποθήκευσης των μεταφορτώσεων';
//  $lang['error_decompress']   = 'The plugin manager was unable to decompress the downloaded file. '.
//                                'This maybe as a result of a bad download, in which case you should try again; '.
//                                'or the compression format may be unknown, in which case you will need to '.
//                                'download and install the plugin manually.';
    $lang['error_decompress']   = 'Δεν είναι δυνατή η αποσυμπίεση των μεταφορτώσεων. '.
                                  'Αυτό μπορεί να οφείλεται σε μερική λήψη των μεταφορτώσεων, οπότε θα πρέπει να επαναλάβετε την διαδικασία '.
                                  'ή το σύστημά σας δεν μπορεί να διαχειριστεί το συγκεκριμένο είδος συμπίεσης, οπότε θα πρέπει να '.
                                  'εγκαταστήσετε την επέκταση χειροκίνητα.';
//  $lang['error_copy']         = 'There was a file copy error while attempting to install files for plugin '.
//                                '<em>%s</em>: the disk could be full or file access permissions may be incorrect. '.
//                                'This may have resulted in a partially installed plugin and leave your wiki '.
//                                'installation unstable.';
    $lang['error_copy']         = 'Εμφανίστηκε ένα σφάλμα αντιγραφής αρχείων κατά την διάρκεια εγκατάστασης της επέκτασης '.
                                  '<em>%s</em>: ο δίσκος μπορεί να είναι γεμάτος ή να μην είναι σωστά ρυθμισμένα τα δικαιώματα πρόσβασης. '.
                                  'Αυτό το γεγονός μπορεί να οδήγησε σε μερική εγκατάσταση της επέκτασης και άρα η DokuWiki εγκατάστασή σας'.
                                  'να εμφανίσει προβλήματα σταθερότητας.';
//  $lang['error_delete']       = 'There was an error while attempting to delete plugin <em>%s</em>.  '.
//                                'The most probably cause is insufficient file or directory access permissions';
    $lang['error_delete']       = 'Εμφανίστηκε ένα σφάλμα κατά την διαδικασία διαγραφής της επέκτασης <em>%s</em>.  '.
                                  'Η πιθανότερη αιτία είναι να μην είναι σωστά ρυθμισμένα τα δικαιώματα πρόσβασης.';

//Setup VIM: ex: et ts  =4 enc  =utf-8 :
