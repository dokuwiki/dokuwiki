<?php
/**
 * Greek language file
 *
 * Based on DokuWiki Version rc2007-05-24 english language file
 * Original english language file contents included for reference
 *
 * @author Chris Smith <chris@jalakai.co.uk>
 * @author     Thanos Massias <tm@thriasio.gr>
 */

//  $lang['menu'] = 'User Manager';
    $lang['menu'] = 'Διαχείριση Χρηστών'; 

// custom language strings for the plugin
//  $lang['noauth']         = '(user authentication not available)';
    $lang['noauth']         = '(η είσοδος χρηστών δεν είναι δυνατή)';
//  $lang['nosupport']      = '(user management not supported)';
    $lang['nosupport']      = '(δεν υποστηρίζεται η διαχείριση χρηστών)';

//  $lang['badauth']        = 'invalid auth mechanism';     // should never be displayed!
    $lang['badauth']        = 'μη επιτρεπτός μηχανισμός πιστοποίησης';     // should never be displayed!

//  $lang['user_id']        = 'User';
    $lang['user_id']        = 'Χρήστης';
//  $lang['user_pass']      = 'Password';
    $lang['user_pass']      = 'Κωδικός';
//  $lang['user_name']      = 'Real Name';
    $lang['user_name']      = 'Πλήρες όνομα';
//  $lang['user_mail']      = 'Email';
    $lang['user_mail']      = 'e-mail';
//  $lang['user_groups']    = 'Groups';
    $lang['user_groups']    = 'Ομάδες';

//  $lang['field']          = 'Field';
    $lang['field']          = 'Πεδίο';
//  $lang['value']          = 'Value';
    $lang['value']          = 'Τιμή';
//  $lang['add']            = 'Add';
    $lang['add']            = 'Προσθήκη';
//  $lang['delete']         = 'Delete';
    $lang['delete']         = 'Διαγραφή';
//  $lang['delete_selected']= 'Delete Selected';
    $lang['delete_selected']= 'Διαγραφή επιλεγμένων χρηστών';
//  $lang['edit']           = 'Edit';
    $lang['edit']           = 'Τροποποίηση';
//  $lang['edit_prompt']    = 'Edit this user';
    $lang['edit_prompt']    = 'Τροποποίηση χρήστη';
//  $lang['modify']         = 'Save Changes';
    $lang['modify']         = 'Αποθήκευση αλλαγών';
//  $lang['search']         = 'Search';
    $lang['search']         = 'Αναζήτηση';
//  $lang['search_prompt']  = 'Perform search';
    $lang['search_prompt']  = 'Εκκίνηση αναζήτησης';
//  $lang['clear']          = 'Reset Search Filter';
    $lang['clear']          = 'Καθαρισμός φίλτρων';
//  $lang['filter']         = 'Filter';
    $lang['filter']         = 'Φίλτρο';

//  $lang['summary']        = 'Displaying users %1$d-%2$d of %3$d found. %4$d users total.';
    $lang['summary']        = 'Εμφάνιση χρηστών %1$d-%2$d από %3$d σχετικούς. %4$d χρήστες συνολικά.';
//  $lang['nonefound']      = 'No users found. %d users total.';
    $lang['nonefound']      = 'Δεν βρέθηκαν σχετικοί χρήστες. %d χρήστες συνολικά.';
//  $lang['delete_ok']      = '%d users deleted';
    $lang['delete_ok']      = '%d χρήστες διεγράφησαν';
//  $lang['delete_fail']    = '%d failed deleting.';
    $lang['delete_fail']    = '%d χρήστες δεν διεγράφησαν.';
//  $lang['update_ok']      = 'User updated successfully';
    $lang['update_ok']      = 'Επιτυχημένη τροποποίηση προφίλ χρήστη';
//  $lang['update_fail']    = 'User update failed';
    $lang['update_fail']    = 'Αποτυχημένη τροποποίηση προφίλ χρήστη';
//  $lang['update_exists']  = 'User name change failed, the specified user name (%s) already exists (any other changes will be applied).';
    $lang['update_exists']  = 'Η αλλαγή ονόματος χρήστη απέτυχε -- το νέο όνομα χρήστη (%s) ήδη υπάρχει (τυχόν άλλες αλλαγές θα εφαρμοστούν).';

//  $lang['start']          = 'start';
    $lang['start']          = 'αρχή';
//  $lang['prev']           = 'previous';
    $lang['prev']           = 'προηγούμενα';
//  $lang['next']           = 'next';
    $lang['next']           = 'επόμενα';
//  $lang['last']           = 'last';
    $lang['last']           = 'τέλος';

// added after 2006-03-09 release
//  $lang['edit_usermissing']   = 'Selected user not found, the specified user name may have been deleted or changed elsewhere.';
    $lang['edit_usermissing']   = 'Ο επιλεγμένος χρήστης δεν βρέθηκε. Πιθανόν να διαγράφηκε στο μεταξύ.';
//  $lang['user_notify']        = 'Notify user';
    $lang['user_notify']        = 'Ειδοποίηση χρήστη';
//  $lang['note_notify']        = 'Notification emails are only sent if the user is given a new password.';
    $lang['note_notify']        = 'Τα ενημερωτικά e-mails στέλνονται μόνο όταν δίνεται νέος κωδικός στον χρήστη.';
//  $lang['note_group']         = 'New users will be added to the default group (%s) if no group is specified.';
    $lang['note_group']         = 'Οι νέοι χρήστες θα ανήκουν στην ομάδα (%s) αν δεν οριστεί άλλη ομάδα.';
//  $lang['add_ok']             = 'User added successfully';
    $lang['add_ok']             = 'Επιτυχημένη εγγραφή  χρήστη';
//  $lang['add_fail']           = 'User addition failed';
    $lang['add_fail']           = 'Η εγγραφή του χρήστη απέτυχε';
//  $lang['notify_ok']          = 'Notification email sent';
    $lang['notify_ok']          = 'Εστάλη ενημερωτικό e-mail';
//  $lang['notify_fail']        = 'Notification email could not be sent';
    $lang['notify_fail']        = 'Δεν ήταν δυνατή η αποστολή του ενημερωτικού e-mail';

