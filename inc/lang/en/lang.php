<?php
/**
 * english language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Anika Henke <henke@cosmocode.de>
 * @author     Matthias Grimm <matthiasgrimm@users.sourceforge.net>
 */
$lang['encoding']   = 'utf-8';
$lang['direction']  = 'ltr';

$lang['btn_edit']   = 'Edit this page';
$lang['btn_source'] = 'Show pagesource';
$lang['btn_show']   = 'Show page';
$lang['btn_create'] = 'Create this page';
$lang['btn_search'] = 'Search';
$lang['btn_save']   = 'Save';
$lang['btn_preview']= 'Preview';
$lang['btn_top']    = 'Back to top';
$lang['btn_newer']  = '<< more recent';
$lang['btn_older']  = 'less recent >>';
$lang['btn_revs']   = 'Old revisions';
$lang['btn_recent'] = 'Recent changes';
$lang['btn_upload'] = 'Upload';
$lang['btn_cancel'] = 'Cancel';
$lang['btn_index']  = 'Index';
$lang['btn_secedit']= 'Edit';
$lang['btn_login']  = 'Login';
$lang['btn_logout'] = 'Logout';
$lang['btn_admin']  = 'Admin';
$lang['btn_update'] = 'Update';
$lang['btn_delete'] = 'Delete';
$lang['btn_back']   = 'Back';
$lang['btn_backlink']    = "Backlinks";
$lang['btn_backtomedia'] = 'Back to Mediafile Selection';
$lang['btn_subscribe']   = 'Subscribe Changes';
$lang['btn_unsubscribe'] = 'Unsubscribe Changes';
$lang['btn_profile']    = 'Update Profile';
$lang['btn_reset']     = 'Reset';
$lang['btn_resendpwd'] = 'Send new password';

$lang['loggedinas'] = 'Logged in as';
$lang['user']       = 'Username';
$lang['pass']       = 'Password';
$lang['newpass']    = 'New password';
$lang['oldpass']    = 'Confirm current password';
$lang['passchk']    = 'once again';
$lang['remember']   = 'Remember me';
$lang['fullname']   = 'Full name';
$lang['email']      = 'E-Mail';
$lang['register']   = 'Register';
$lang['profile']    = 'User Profile';
$lang['badlogin']   = 'Sorry, username or password was wrong.';
$lang['minoredit']  = 'Minor Changes';

$lang['regmissing'] = 'Sorry, you must fill in all fields.';
$lang['reguexists'] = 'Sorry, a user with this login already exists.';
$lang['regsuccess'] = 'The user has been created and the password was sent by email.';
$lang['regsuccess2']= 'The user has been created.';
$lang['regmailfail']= 'Looks like there was an error on sending the password mail. Please contact the admin!';
$lang['regbadmail'] = 'The given email address looks invalid - if you think this is an error, contact the admin';
$lang['regbadpass'] = 'The two given passwords are not identically, please try again.';
$lang['regpwmail']  = 'Your DokuWiki password';
$lang['reghere']    = 'You don\'t have an account yet? Just get one';

$lang['profna']       = 'This wiki does not support profile modification';
$lang['profnochange'] = 'No changes, nothing to do.';
$lang['profnoempty']  = 'An empty name or email address is not allowed.';
$lang['profchanged']  = 'User profile sucessfully updated.';

$lang['pwdforget'] = 'Forgotten your password? Get a new one';
$lang['resendna']  = 'This wiki does not support password resending.';
$lang['resendpwd'] = 'Send new password for';
$lang['resendpwdmissing'] = 'Sorry, you must fill in all fields.';
$lang['resendpwdnouser']  = 'Sorry, we can\'t find this user in our database.';
$lang['resendpwdsuccess'] = 'Your new password has been sent by email.';

$lang['txt_upload']   = 'Select file to upload';
$lang['txt_filename'] = 'Upload as (optional)';
$lang['txt_overwrt']  = 'Overwrite existing file';
$lang['lockedby']     = 'Currently locked by';
$lang['lockexpire']   = 'Lock expires at';
$lang['willexpire']   = 'Your lock for editing this page is about to expire in a minute.\nTo avoid conflicts use the preview button to reset the locktimer.';

$lang['notsavedyet'] = 'Unsaved changes will be lost.\nReally continue?';
$lang['rssfailed']   = 'An error occured while fetching this feed: ';
$lang['nothingfound']= 'Nothing was found.';

$lang['mediaselect'] = 'Mediafile Selection';
$lang['fileupload']  = 'Mediafile Upload';
$lang['uploadsucc']  = 'Upload successful';
$lang['uploadfail']  = 'Upload failed. Maybe wrong permissions?';
$lang['uploadwrong'] = 'Upload denied. This file extension is forbidden!';
$lang['uploadexist'] = 'File already exists. Nothing done.';
$lang['deletesucc']  = 'The file "%s" has been deleted.';
$lang['deletefail']  = '"%s" couldn\'t be deleted - check permissions.';
$lang['mediainuse']  = 'The file "%s" hasn\'t been deleted - it is still in use.';
$lang['namespaces']  = 'Namespaces';
$lang['mediafiles']  = 'Available files in';

$lang['reference']   = 'References for';
$lang['ref_inuse']   = 'The file can\'t be deleted, because it\'s still used by the following pages:';
$lang['ref_hidden']  = 'Some references  are on pages you don\'t have permission to read';

$lang['hits']       = 'Hits';
$lang['quickhits']  = 'Matching pagenames';
$lang['toc']        = 'Table of Contents';
$lang['current']    = 'current';
$lang['yours']      = 'Your Version';
$lang['diff']       = 'show differences to current version';
$lang['line']       = 'Line';
$lang['breadcrumb'] = 'Trace';
$lang['youarehere'] = 'You are here';
$lang['lastmod']    = 'Last modified';
$lang['by']         = 'by';
$lang['deleted']    = 'removed';
$lang['created']    = 'created';
$lang['restored']   = 'old revision restored';
$lang['summary']    = 'Edit summary';

$lang['mail_newpage'] = 'page added:';
$lang['mail_changed'] = 'page changed:';

$lang['nosmblinks'] = 'Linking to Windows shares only works in Microsoft Internet Explorer.\nYou still can copy and paste the link.';

$lang['qb_alert']   = 'Please enter the text you want to format.\nIt will be appended to the end of the document.';
$lang['qb_bold']    = 'Bold Text';
$lang['qb_italic']  = 'Italic Text';
$lang['qb_underl']  = 'Underlined Text';
$lang['qb_code']    = 'Code Text';
$lang['qb_strike']  = 'Strike-through Text';
$lang['qb_h1']      = 'Level 1 Headline';
$lang['qb_h2']      = 'Level 2 Headline';
$lang['qb_h3']      = 'Level 3 Headline';
$lang['qb_h4']      = 'Level 4 Headline';
$lang['qb_h5']      = 'Level 5 Headline';
$lang['qb_link']    = 'Internal Link';
$lang['qb_extlink'] = 'External Link';
$lang['qb_hr']      = 'Horizontal Rule';
$lang['qb_ol']      = 'Ordered List Item';
$lang['qb_ul']      = 'Unordered List Item';
$lang['qb_media']   = 'Add Images and other files';
$lang['qb_sig']     = 'Insert Signature';
$lang['qb_smileys'] = 'Smileys';
$lang['qb_chars']   = 'Special Chars';

$lang['del_confirm']= 'Really delete selected item(s)?';
$lang['admin_register']= 'Add new user';

$lang['spell_start'] = 'Check Spelling';
$lang['spell_stop']  = 'Resume Editing';
$lang['spell_wait']  = 'Please wait...';
$lang['spell_noerr'] = 'No Mistakes found';
$lang['spell_nosug'] = 'No Suggestions';
$lang['spell_change']= 'Change';

$lang['metaedit']    = 'Edit Metadata';
$lang['metasaveerr'] = 'Writing metadata failed';
$lang['metasaveok']  = 'Metadata saved';
$lang['img_backto']  = 'Back to';
$lang['img_title']   = 'Title';
$lang['img_caption'] = 'Caption';
$lang['img_date']    = 'Date';
$lang['img_fname']   = 'Filename';
$lang['img_fsize']   = 'Size';
$lang['img_artist']  = 'Photographer';
$lang['img_copyr']   = 'Copyright';
$lang['img_format']  = 'Format';
$lang['img_camera']  = 'Camera';
$lang['img_keywords']= 'Keywords';

$lang['subscribe_success']  = 'Added %s to subscription list for %s';
$lang['subscribe_error']    = 'Error adding %s to subscription list for %s';
$lang['subscribe_noaddress']= 'There is no address associated with your login, you cannot be added to the subscription list';
$lang['unsubscribe_success']= 'Removed %s from subscription list for %s';
$lang['unsubscribe_error']  = 'Error removing %s from subscription list for %s';

/* auth.class language support */
$lang['authmodfailed']   = 'Bad user authentication configuration. Please inform your Wiki Admin.';
$lang['authtempfail']    = 'User authentication is temporarily unavailable. If this situation persists, please inform your Wiki Admin.';

//Setup VIM: ex: et ts=2 enc=utf-8 :
