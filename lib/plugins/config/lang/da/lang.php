<?php
/**
 * Danish language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Lars Næsbye Christensen <larsnaesbye@stud.ku.dk>
 */

// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu']       = 'Konfigurationsindstillinger'; 

$lang['error']      = 'Indstillingerne blev ikke opdateret på grund af en ugyldig værdi, gennemse venligst dine ændringer og gem dem igen.
                       <br />De(n) ugyldig(e) værdie(r) vil blive rammet ind med rødt.';
$lang['updated']    = 'Indstillingerne blev opdateret korrekt.';
$lang['nochoice']   = '(ingen andre valgmuligheder)';
$lang['locked']     = 'Indstillingsfilen kunne ikke opdateres, hvis dette er en fejl, <br />
                       sørg da for at den lokale indstillingsfils navn og rettigheder er korrekte.';


/* -------------------- Config Options --------------------------- */

/* userewrite options */
$lang['userewrite_o_0'] = 'ingen';
$lang['userewrite_o_1'] = 'htaccess';
$lang['userewrite_o_2'] = 'Dokuwiki intern';

/* deaccent options */
$lang['deaccent_o_0'] = 'fra';
$lang['deaccent_o_1'] = 'fjern accenter';
$lang['deaccent_o_2'] = 'romaniser';

/* gdlib options */
$lang['gdlib_o_0'] = 'GD Lib ikke tilstede';
$lang['gdlib_o_1'] = 'version 1.x';
$lang['gdlib_o_2'] = 'automatisk detektering';

/* rss_type options */
$lang['rss_type_o_rss']  = 'RSS 0.91';
$lang['rss_type_o_rss1'] = 'RSS 1.0';
$lang['rss_type_o_rss2'] = 'RSS 2.0';
$lang['rss_type_o_atom'] = 'Atom 0.3';

/* rss_linkto options */
$lang['rss_linkto_o_diff']    = 'liste over forskelle';
$lang['rss_linkto_o_page']    = 'den redigerede side';
$lang['rss_linkto_o_rev']     = 'liste over ændringer';
$lang['rss_linkto_o_current'] = 'den nuværende side';

