<?php
/**
 * french language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Guy Brand <gb@isis.u-strasbg.fr>
 */

// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu']       = 'Paramètres de configuration'; 

$lang['error']      = 'Paramètres non modifiés en raison d\'une valeur non valide, vérifiez vos réglages et réessayez.
                       <br />La valeur(s) erronée(s) est entourée d\'une bordure rouge.';
$lang['updated']    = 'Paramètres mis à jour avec succès.';
$lang['nochoice']   = '(aucun autre choix possible)';
$lang['locked']     = 'Le fichier des paramètres ne peut être modifié, si ceci n\'est pas intentionnel, <br />
                       vérifiez que le nom et les droits du fichier sont corrects.';

// settings prompts
$lang['lang']        = 'langue';                                 //your language
$lang['basedir']     = 'répertoire de base';                     //absolute dir from serveroot - blank for autodetection
$lang['baseurl']     = 'url de base';                            //URL to server including protocol - blank for autodetect
$lang['savedir']     = 'répertoire de stockage';                 //where to store all the files
$lang['start']       = 'nom de la page d\'accueil';              //name of start page
$lang['title']       = 'titre du wiki';                          //what to show in the title
$lang['template']    = 'template';                               //see tpl directory
$lang['fullpath']    = 'utiliser le chemin complet';             //show full path of the document or relative to datadir only? 0|1
$lang['recent']      = 'derniers changements';                   //how many entries to show in recent
$lang['breadcrumbs'] = 'breadcrumbs';                            //how many recent visited pages to show
$lang['typography']  = 'typographie';                            //convert quotes, dashes and stuff to typographic equivalents? 0|1
$lang['htmlok']      = 'permettre contenu html';                 //may raw HTML be embedded? This may break layout and XHTML validity 0|1
$lang['phpok']       = 'permettre contenu php';                  //may PHP code be embedded? Never do this on the internet! 0|1
$lang['dformat']     = 'format de date';                         //dateformat accepted by PHPs date() function
$lang['signature']   = 'signature';                              //signature see wiki:langig for details
$lang['toptoclevel'] = 'niveau supérieur pour figurer en toc';   //Level starting with and below to include in AutoTOC (max. 5)
$lang['maxtoclevel'] = 'niveau maxi pour figurer en toc';        //Up to which level include into AutoTOC (max. 5)
$lang['maxseclevel'] = 'niveau maxi pour éditer des sections';   //Up to which level create editable sections (max. 5)
$lang['camelcase']   = 'utiliser camelcase pour les liens';      //Use CamelCase for linking? (I don't like it) 0|1
$lang['deaccent']    = 'retirer accents dans les noms de pages'; //convert accented chars to unaccented ones in pagenames?
$lang['useheading']  = 'utiliser le titre de premier niveau';    //use the first heading in a page as its name
$lang['refcheck']    = 'vérifier les références à media';        //check for references before deleting media files
$lang['refshow']     = 'montrer les références à media';         //how many references should be shown, 5 is a good value
$lang['allowdebug']  = 'permettre déboguage (désactivez !)';     //make debug possible, disable after install! 0|1

$lang['usewordblock']= 'bloquer spam selon les mots utilisés';   //block spam based on words? 0|1
$lang['indexdelay']  = 'délai avant l\'indexation';              //allow indexing after this time (seconds) default is 5 days
$lang['relnofollow'] = 'utiliser rel="nofollow"';                //use rel="nofollow" for external links?
$lang['mailguard']   = 'cacher les adresses de courriel';        //obfuscate email addresses against spam harvesters?

/* Authentication Options - read http://www.splitbrain.org/dokuwiki/wiki:acl */
$lang['useacl']      = 'utiliser les ACLs';                      //Use Access Control Lists to restrict access?
$lang['openregister']= 'enregistrement ouvert';                  //Should users to be allowed to register?
$lang['autopasswd']  = 'autogénérer les mots de passe';          //autogenerate passwords and email them to user
$lang['resendpasswd']= 'permettre le renvoi du mot de passe';    //allow resend password function?
$lang['authtype']    = 'backend d\'authentification';            //which authentication backend should be used
$lang['passcrypt']   = 'cryptage des mots de passe';             //Used crypt method (smd5,md5,sha1,ssha,crypt,mysql,my411)
$lang['defaultgroup']= 'groupe par défaut';                      //Default groups new Users are added to
$lang['superuser']   = 'super-utilisateur';                      //The admin can be user or @group
$lang['profileconfirm'] = 'confirmer le profil';                 //Require current password to langirm changes to user profile

/* Advanced Options */
$lang['userewrite']  = 'URLs esthétiques';                       //this makes nice URLs: 0: off 1: .htaccess 2: internal
$lang['useslash']    = 'utiliser slash';                         //use slash instead of colon? only when rewrite is on
$lang['sepchar']     = 'séparateur de nom de page';              //word separator character in page names; may be a
$lang['canonical']   = 'utiliser des URLs canoniques';           //Should all URLs use full canonical http://... style?
$lang['autoplural']  = 'auto-pluriel';                           //try (non)plural form of nonexisting files?
$lang['usegzip']     = 'utiliser gzip (pour attic)';             //gzip old revisions?
$lang['cachetime']   = 'âge maxi dans le cache (sec)';           //maximum age for cachefile in seconds (defaults to a day)
$lang['purgeonadd']  = 'purger le cache à l\'ajout';             //purge cache when a new file is added (needed for up to date links)
$lang['locktime']    = 'âge maxi des fichiers verrous (sec)';    //maximum age for lockfiles (defaults to 15 minutes)
$lang['notify']      = 'notifier adresse de courriel';           //send change info to this email (leave blank for nobody)
$lang['mailfrom']    = 'expéditeur des notifications du wiki';   //use this email when sending mails
$lang['gdlib']       = 'version GD Lib';                         //the GDlib version (0, 1 or 2) 2 tries to autodetect
$lang['im_convert']  = 'chemin vers imagemagick';                //path to ImageMagicks convert (will be used instead of GD)
$lang['spellchecker']= 'activer la correction d\'orthographe';   //enable Spellchecker (needs PHP >= 4.3.0 and aspell installed)
$lang['subscribers'] = 'activer l\'abonnement aux pages';        //enable change notice subscription support
$lang['compress']    = 'compresser fichiers CSS & javascript';   //Strip whitespaces and comments from Styles and JavaScript? 1|0
$lang['hidepages']   = 'cacher pages correspondant à (regex)';   //Regexp for pages to be skipped from RSS, Search and Recent Changes
$lang['send404']     = 'renvoyer "HTTP 404/Page Not Found"';     //Send a HTTP 404 status for non existing pages?
$lang['sitemap']     = 'générer carte google du site (days)';    //Create a google sitemap? How often? In days.

$lang['rss_type']    = 'type de flux rss';                       //type of RSS feed to provide, by default:
$lang['rss_linkto']  = 'lien rss vers';                          //what page RSS entries link to:

//Set target to use when creating links - leave empty for same window
$lang['target____wiki']      = 'target pour liens internes';
$lang['target____interwiki'] = 'target pour liens interwiki';
$lang['target____extern']    = 'target pour liens externes';
$lang['target____media']     = 'target pour liens media';
$lang['target____windows']   = 'target pour liens windows';

//Proxy setup - if your Server needs a proxy to access the web set these
$lang['proxy____host'] = 'proxy - hôte';
$lang['proxy____port'] = 'proxy - port';
$lang['proxy____user'] = 'proxy - identifiant';
$lang['proxy____pass'] = 'proxy - mot de passe';
$lang['proxy____ssl']  = 'proxy - ssl';

/* Safemode Hack */
$lang['safemodehack'] = 'activer l\'option safemode';  //read http://wiki.splitbrain.org/wiki:safemodehack !
$lang['ftp____host'] = 'ftp - hôte';
$lang['ftp____port'] = 'ftp - port';
$lang['ftp____user'] = 'ftp - identifiant';
$lang['ftp____pass'] = 'ftp - mot de passe';
$lang['ftp____root'] = 'ftp - répertoire racine';

/* userewrite options */
$lang['userewrite_o_0'] = 'aucun';
$lang['userewrite_o_1'] = 'htaccess';
$lang['userewrite_o_2'] = 'dokuwiki';

/* gdlib options */
$lang['gdlib_o_0'] = 'GD Lib non disponible';
$lang['gdlib_o_1'] = 'version 1.x';
$lang['gdlib_o_2'] = 'autodétecté';

/* rss_type options */
$lang['rss_type_o_rss']  = 'RSS 0.91';
$lang['rss_type_o_rss1'] = 'RSS 1.0';
$lang['rss_type_o_rss2'] = 'RSS 2.0';
$lang['rss_type_o_atom'] = 'Atom 0.3';

/* rss_linkto options */
$lang['rss_linkto_o_diff']    = 'liste des différences';
$lang['rss_linkto_o_page']    = 'page révisée';
$lang['rss_linkto_o_rev']     = 'liste des révisions';
$lang['rss_linkto_o_current'] = 'page actuelle';

