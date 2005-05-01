<?
/**
 * danish language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Jon Bendtsen <bendtsen@diku.dk>
 * @author     koeppe <koeppe@kazur.dk>
 */
$lang['encoding']   = 'utf-8';
$lang['direction']  = 'ltr';

$lang['btn_edit']   = 'Rediger dette dokument'; // Edit this page
$lang['btn_source'] = 'Vis kildekode';          // Show pagesource
$lang['btn_show']   = 'Vis dokument';           // Show page
$lang['btn_create'] = 'Opret dette dokument';   // Create this page
$lang['btn_search'] = 'Søg';;                   // Search
$lang['btn_save']   = 'Gem';                    // Save
$lang['btn_preview']= 'Preview';                // Preview (find et bedre ord)
$lang['btn_top']    = 'Tilbage til toppen';     // Back to top
$lang['btn_revs']   = 'Gamle udgaver';          // Old revisions
$lang['btn_recent'] = 'Nye ændringer';          // Recent changes
$lang['btn_upload'] = 'Upload';                 // Upload
$lang['btn_cancel'] = 'Fortryd';                // Cancel
$lang['btn_index']  = 'Indeks';                 // Index
$lang['btn_secedit']= 'Rediger';                // Edit
$lang['btn_login']  = 'Log ind';                  // Login
$lang['btn_logout'] = 'Log ud';                 // Logout

$lang['loggedinas'] = 'Logget på som';                      // Logged in as
$lang['user']       = 'Brugernavn';                         // Username
$lang['pass']       = 'Password';                           // Password
$lang['remember']   = 'Log automatisk på';                  // Remember me
$lang['fullname']   = 'Navn';                               // Full name
$lang['email']      = 'E-mail';                             // E-Mail
$lang['register']   = 'Tilmeld';                            // Register
$lang['badlogin']   = 'Forkert brugernavn eller password.'; // Sorry, username or password was wrong.

// Sorry, you must fill in all fields.
$lang['regmissing'] = 'Du skal udfylde alle felter.';
// Sorry, a user with this login already exists.
$lang['reguexists'] = 'Dette brugernavn er allerede i brug.';
// The user was created. The password was sent by mail.
$lang['regsuccess'] = 'Du er nu oprettet som bruger. Dit password bliver sendt til dig i en e-mail.';
// Looks like there was an error on sending the password mail. Please contact the admin!
$lang['regmailfail']= 'Dit password blev ikke sendt. Kontakt venligst admin';
// The given email address looks invalid - if you think this is an error, contact the admin
$lang['regbadmail'] = 'E-mail-adressen er ugyldig. Kontakt venligst admin, hvis du mener dette er en fejl.';
// Your DokuWiki password
$lang['regpwmail']  = 'Dit DokuWiki password';
// You don\'t have an account yet? Just get one
$lang['reghere']    = 'Opret en DokuWiki-kontor her.';

// Select file to upload
$lang['txt_upload']   = 'Vælg fil til upload';
// Enter wikiname (optional)
$lang['txt_filename'] = 'Indtast wiki navn (valgfri)';
// Currently locked by
$lang['lockedby']     = 'Midlertidigt låst af';
// Lock expires at
$lang['lockexpire']   = 'Lås udløber kl.';
// Your lock for editing this page is about to expire in a minute.\nTo avoid conflicts use the preview button to reset the locktimer.
$lang['willexpire']   = 'Din lås på dette dokument udløber om et minut.\nTryk på'.$lang['btn_preview'].'-knappen for at undgå konflikter.';

// There are unsaved changes, that will be lost.\nReally continue?
$lang['notsavedyet'] = 'Der er lavet ændringer på dokumentet, hvis du fortsætter vil gå ændringerne tabt.\nØnsker du at fortsætte?';
// An error occured while fetching this feed: 
$lang['rssfailed']   = 'Der opstod en fejl ved indhentning af: ';
// Nothing was found.
$lang['nothingfound']= 'Søgningen gav intet resultat.';

$lang['mediaselect'] = 'Vælg mediefil';             // Mediafile Selection
$lang['fileupload']  = 'Upload mediefil';           // Mediafile Upload
$lang['uploadsucc']  = 'Upload var en success';     // Upload successful
$lang['uploadfail']  = 'Upload fejlede. Der er muligvis fejl i rettighederne';  // Upload failed. Maybe wrong permissions?
$lang['uploadwrong'] = 'Upload afvist. Filtyppen er ikke tilladt';              // Upload denied. This file extension is forbidden
$lang['namespaces']  = 'Navnerum';                  // Namespaces
$lang['mediafiles']  = 'Tilgængelige filer i';      // Available files in

$lang['hits']       = 'Hits';                       // Hits
$lang['quickhits']  = 'Tilsvarende dokumentnavne';  // Matching pagenames
$lang['toc']        = 'Indholdsfortegnelse';        // Table of Contents
$lang['current']    = 'nuværende';                  // current
$lang['diff']       = 'vis forskelle i forhold til den nuværende udgave';          // show differences to current version
$lang['line']       = 'Linje';                      // Line
$lang['breadcrumb'] = 'Sti';                        // Sti
$lang['lastmod']    = 'Sidst ændret';               // Last modified
$lang['by']         = 'af';                         // by
$lang['deleted']    = 'fjernet';                    // removed
$lang['created']    = 'oprettet';                   // created
$lang['restored']   = 'gammel udgave reetableret';  // old revision restored
$lang['summary']    = 'Redigerings resume';         // Edit summary

$lang['mail_newpage'] = '[DokuWiki] dokument tilføjet:';    // [DokuWiki] page added:
$lang['mail_changed'] = '[DokuWiki] dokument ændret:';      // [DokuWiki] page changed:

// Linking to Windows shares only works in Microsoft Internet Explorer.\nYou still can copy and paste the link.
$lang['nosmblinks'] = 'Henvisninger til Windows shares virker kun i Microsoft Internet Explorer.\nDu kan stadig kopiere og indsætte linket.';

// Please enter the text you want to format.\nIt will be appended to the end of the document.
$lang['qb_alert']   = 'Skriv den tekst du ønsker at formatere.\nDen vil blive tilføjet i slutningen af dokumentet.';
$lang['qb_bold']    = 'Fed';                            // Bold Text
$lang['qb_italic']  = 'Kursiv';                         // Italic Text
$lang['qb_underl']  = 'Understregning';                 // Underlined Text
$lang['qb_code']    = 'Skrivemaskine tekst';            // Code Text
$lang['qb_h1']      = 'Niveau 1 overskrift';            // Level 1 Headline
$lang['qb_h2']      = 'Niveau 2 overskrift';            // Level 2 Headline
$lang['qb_h3']      = 'Niveau 3 overskrift';            // Level 3 Headline
$lang['qb_h4']      = 'Niveau 4 overskrift';            // Level 4 Headline
$lang['qb_h5']      = 'Niveau 5 overskrift';            // Level 5 Headline
$lang['qb_link']    = 'Internt link';                   // Internal Link
$lang['qb_extlink'] = 'Eksternt link';                  // External Link
$lang['qb_hr']      = 'Vandret linje';                  // Horizontal Rule
$lang['qb_ol']      = 'Nummereret liste element';       // Ordered List Item
$lang['qb_ul']      = 'Unummereret liste element';      // Unordered List Item
$lang['qb_media']   = 'Tilføj billeder og andre filer'; // Add Images and other files
$lang['qb_sig']     = 'Indsæt signatur';                // Insert Signature

//Setup VIM: ex: et ts=2 enc=utf-8 :
