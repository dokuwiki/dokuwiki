<?php
/**
 * Greek language file
 *
 * Based on DokuWiki Version rc2007-05-24 english language file
 * Original english language file contents included for reference
 *
 * @author     Thanos Massias <tm@thriasio.gr>
 */

// settings must be present and set appropriately for the language
    $lang['encoding']   = 'utf-8';
    $lang['direction']  = 'ltr';

// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
//  $lang['menu'] = 'Revert Manager';
    $lang['menu'] = 'Αποκατάσταση κακόβουλων αλλαγών σελίδων';

// custom language strings for the plugin

//  $lang['filter']   = 'Search spammy pages';
    $lang['filter']   = 'Αναζήτηση σελίδων που περιέχουν spam';
//  $lang['revert']   = 'Revert selected pages';
    $lang['revert']   = 'Επαναφορά παλαιότερων εκδόσεων των επιλεγμένων σελίδων';
//  $lang['reverted'] = '%s reverted to revision %s';
    $lang['reverted'] = 'Η σελίδα %s επεναφέρθηκε στην έκδοση %s';
//  $lang['removed']  = '%s removed';
    $lang['removed']  = 'Η σελίδα %s διαγράφηκε';
//  $lang['revstart'] = 'Reversion process started. This can take a long time. If the
//                       script times out before finishing, you need to revert in smaller
//                       chunks.';
    $lang['revstart'] = 'Η διαδικασία αποκατάστασης άρχισε. Αυτό ίσως πάρει αρκετό χρόνο. 
                         Εάν η εφαρμογή υπερβεί το διαθέσιμο χρονικό όριο και τερματιστεί 
                         πριν τελειώσει, θα χρειαστεί να επαναλάβετε αυτή τη διαδικασία για μικρότερα τμήματα.';
//  $lang['revstop']  = 'Reversion process finished successfully.';
    $lang['revstop']  = 'Η διαδικασία αποκατάστασης ολοκληρώθηκε με επιτυχία.';
//  $lang['note1']    = 'Note: this search is case sensitive';
    $lang['note1']    = '<br />Σημείωση: η αναζήτηση επηρεάζεται από το εάν οι χαρακτήρες είναι πεζοί ή κεφαλαίοι';
//  $lang['note2']    = 'Note: the page will be reverted to the last version not containing the given spam term <i>%s</i>.';
    $lang['note2']    = '<br />Σημείωση: η σελίδα θα επαναφερθεί στην πλέον πρόσφατη έκδοση που δεν περιέχει τον όρο <i>%s</i>.';

//Setup VIM: ex: et ts=4 enc=utf-8 :
