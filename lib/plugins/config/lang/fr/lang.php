<?php
/**
 * french language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Guy Brand <gb@unistra.fr>
 * @author Delassaux Julien <julien@delassaux.fr>
 * @author Maurice A. LeBlanc <leblancma@cooptel.qc.ca>
 * @author stephane.gully@gmail.com
 * @author Guillaume Turri <guillaume.turri@gmail.com>
 * @author Erik Pedersen <erik.pedersen@shaw.ca>
 * @author olivier duperray <duperray.olivier@laposte.net>
 * @author Vincent Feltz <psycho@feltzv.fr>
 * @author Philippe Bajoit <philippe.bajoit@gmail.com>
 * @author Florian Gaub <floriang@floriang.net>
 * @author Samuel Dorsaz samuel.dorsaz@novelion.net
 * @author Johan Guilbaud <guilbaud.johan@gmail.com>
 * @author schplurtz@laposte.net
 * @author skimpax@gmail.com
 */
$lang['menu']                  = 'Paramètres de configuration';
$lang['error']                 = 'Paramètres non modifiés en raison d\'une valeur non valide, vérifiez vos réglages et réessayez. <br />Les valeurs erronées sont entourées d\'une bordure rouge.';
$lang['updated']               = 'Paramètres mis à jour avec succès.';
$lang['nochoice']              = '(aucun autre choix possible)';
$lang['locked']                = 'Le fichier des paramètres ne peut être modifié, si ceci n\'est pas intentionnel, <br /> vérifiez que le nom et les droits du fichier sont corrects.';
$lang['danger']                = 'Danger : Modifier cette option pourrait rendre inaccessible votre wiki et son menu de configuration.';
$lang['warning']               = 'Attention : Modifier cette option pourrait engendrer un comportement indésirable.';
$lang['security']              = 'Avertissement de sécurité : Modifier cette option pourrait induire un risque de sécurité.';
$lang['_configuration_manager'] = 'Gestionnaire de configuration';
$lang['_header_dokuwiki']      = 'Paramètres de DokuWiki';
$lang['_header_plugin']        = 'Paramètres des modules externes';
$lang['_header_template']      = 'Paramètres des modèles';
$lang['_header_undefined']     = 'Paramètres indéfinis';
$lang['_basic']                = 'Paramètres de base';
$lang['_display']              = 'Paramètres d\'affichage';
$lang['_authentication']       = 'Paramètres d\'authentification';
$lang['_anti_spam']            = 'Paramètres anti-spam';
$lang['_editing']              = 'Paramètres d\'édition';
$lang['_links']                = 'Paramètres des liens';
$lang['_media']                = 'Paramètres média';
$lang['_advanced']             = 'Paramètres avancés';
$lang['_network']              = 'Paramètres réseaux';
$lang['_plugin_sufix']         = 'Paramètres de module';
$lang['_template_sufix']       = 'Paramètres de modèle';
$lang['_msg_setting_undefined'] = 'Pas de métadonnée de paramètres.';
$lang['_msg_setting_no_class'] = 'Pas de classe de paramètres.';
$lang['_msg_setting_no_default'] = 'Pas de valeur par défaut.';
$lang['fmode']                 = 'Mode de création des fichiers';
$lang['dmode']                 = 'Mode de création des répertoires';
$lang['lang']                  = 'Langue';
$lang['basedir']               = 'Répertoire de base (ex. : <code>/dokuwiki/</code>). Laisser vide pour une détection automatique.';
$lang['baseurl']               = 'URL de base. Laisser vide pour une détection automatique.';
$lang['savedir']               = 'Répertoire de stockage';
$lang['cookiedir']             = 'Chemin des cookies. Laissez vide pour utiliser l\'URL de base.';
$lang['start']                 = 'Nom de la page d\'accueil';
$lang['title']                 = 'Titre du wiki';
$lang['template']              = 'Modèle';
$lang['license']               = 'Sous quelle licence doit être placé le contenu ?';
$lang['fullpath']              = 'Utiliser le chemin complet dans le pied de page';
$lang['recent']                = 'Nombre de derniers changements à afficher';
$lang['breadcrumbs']           = 'Nombre de traces à afficher';
$lang['youarehere']            = 'Traces hiérarchiques';
$lang['typography']            = 'Effectuer des améliorations typographiques';
$lang['htmlok']                = 'Permettre HTML dans les pages';
$lang['phpok']                 = 'Permettre PHP dans les pages';
$lang['dformat']               = 'Format de date (cf. fonction <a href="http://fr.php.net/strftime">strftime</a> de PHP)';
$lang['signature']             = 'Signature';
$lang['toptoclevel']           = 'Niveau le plus haut à afficher dans la table des matières';
$lang['tocminheads']           = 'Nombre minimum de titres pour qu\'une table des matières soit construite';
$lang['maxtoclevel']           = 'Niveau maximum pour figurer dans la table des matières';
$lang['maxseclevel']           = 'Niveau maximum pour modifier des sections';
$lang['camelcase']             = 'Utiliser CamelCase pour les liens';
$lang['deaccent']              = 'Retirer les accents dans les noms de pages';
$lang['useheading']            = 'Utiliser le titre de premier niveau';
$lang['refcheck']              = 'Vérifier les références de média';
$lang['refshow']               = 'Nombre de références de média à montrer';
$lang['allowdebug']            = 'Debug (<strong>Ne l\'activez que si vous en avez besoin !</strong>)';
$lang['mediarevisions']        = 'Activer les révisions (gestion de versions) des médias';
$lang['usewordblock']          = 'Bloquer le spam selon les mots utilisés';
$lang['indexdelay']            = 'Délai avant l\'indexation (en secondes)';
$lang['relnofollow']           = 'Utiliser rel="nofollow" sur les liens extérieurs';
$lang['mailguard']             = 'Brouiller les adresses de courriel';
$lang['iexssprotect']          = 'Vérifier la présence de code JavaScript ou HTML malveillant dans les fichiers envoyés';
$lang['showuseras']            = 'Qu\'afficher en montrant les utilisateurs qui ont récemment modifié la page';
$lang['useacl']                = 'Utiliser les listes de contrôle d\'accès (ACL)';
$lang['autopasswd']            = 'Auto-générer les mots de passe';
$lang['authtype']              = 'Mécanisme d\'authentification';
$lang['passcrypt']             = 'Méthode de chiffrement des mots de passe';
$lang['defaultgroup']          = 'Groupe par défaut';
$lang['superuser']             = 'Superuser - groupe, utilisateur ou liste séparée par des virgules user1,@group1,user2 ayant un accès complet à toutes les pages quelque soit le paramétrage des ACL';
$lang['manager']               = 'Manager - groupe, utilisateur ou liste séparée par des virgules user1,@group1,user2 ayant accès à certaines fonctions de gestion';
$lang['profileconfirm']        = 'Confirmer par mot de passe les modifications de profil';
$lang['disableactions']        = 'Actions à désactiver dans DokuWiki';
$lang['disableactions_check']  = 'Vérifier';
$lang['disableactions_subscription'] = 'Abonnement aux pages';
$lang['disableactions_wikicode'] = 'Afficher le texte source';
$lang['disableactions_other']  = 'Autres actions (séparées par des virgules)';
$lang['sneaky_index']          = 'Par défaut, DokuWiki affichera toutes les catégories dans la vue par index. Activer cette option permet de cacher celles pour lesquelles l\'utilisateur n\'a pas la permission de lecture. Il peut en résulter le masquage de sous-catégories accessibles. Ceci peut rendre l\'index inutilisable avec certaines ACL.';
$lang['auth_security_timeout'] = 'Délai d\'expiration de sécurité (secondes)';
$lang['securecookie']          = 'Les cookies mis via HTTPS doivent-ils n\'être envoyé par le navigateur que via HTTPS ? Ne désactivez cette option que si la connexion à votre wiki est sécurisée avec SSL mais que la navigation sur le wiki n\'est pas sécurisée.';
$lang['xmlrpc']                = 'Activer l\'interface XML-RPC.';
$lang['xmlrpcuser']            = 'Restreindre l\'accès à XML-RPC aux groupes et utilisateurs indiqués ici. Laisser vide afin que tout le monde y ait accès.';
$lang['updatecheck']           = 'Vérifier les mises à jour ? DokuWiki doit pouvoir contacter update.dokuwiki.org.';
$lang['userewrite']            = 'URL esthétiques';
$lang['useslash']              = 'Utiliser « / » comme séparateur de catégorie dans les URL';
$lang['usedraft']              = 'Enregistrer automatiquement un brouillon pendant l\'édition';
$lang['sepchar']               = 'Séparateur de mots dans les noms de page';
$lang['canonical']             = 'Utiliser des URL canoniques';
$lang['fnencode']              = 'Méthode pour l\'encodage des fichiers non-ASCII';
$lang['autoplural']            = 'Rechercher les formes plurielles dans les liens';
$lang['compression']           = 'Méthode de compression pour les fichiers dans attic';
$lang['cachetime']             = 'Âge maximum d\'un fichier en cache (en secondes)';
$lang['locktime']              = 'Âge maximum des fichiers verrous (en secondes)';
$lang['fetchsize']             = 'Taille maximale (en octets) du fichier que fetch.php peut télécharger';
$lang['notify']                = 'Notifier les modifications à cette adresse de courriel';
$lang['registernotify']        = 'Envoyer un courriel annonçant les nouveaux utilisateurs enregistrés à cette adresse';
$lang['mailfrom']              = 'Expéditeur des notifications par courriel du wiki';
$lang['mailprefix']            = 'Préfixe à utiliser dans les objets des courriels automatiques';
$lang['gzip_output']           = 'Utiliser Content-Encoding gzip pour XHTML';
$lang['gdlib']                 = 'Version de GD Lib';
$lang['im_convert']            = 'Chemin vers l\'outil de conversion d\'ImageMagick';
$lang['jpg_quality']           = 'Qualité de la compression JPEG (0-100)';
$lang['subscribers']           = 'Activer l\'abonnement aux pages';
$lang['subscribe_time']        = 'Délai après lequel les listes d\'abonnement et résumés sont envoyés (en secondes). Devrait être plus petit que le délai précisé dans recent_days.';
$lang['compress']              = 'Compresser CSS et JavaScript';
$lang['cssdatauri']            = 'Taille maximale en octets pour inclure dans les feuilles de styles CSS, les images qui y sont référencées. Cette technique minimise les requêtes HTTP. Pour IE, ceci ne fonctionne qu\'à partir de la version 8 !  Valeurs correctes entre <code>400</code> et <code>600</code>. <code>0</code> pour désactiver.';
$lang['hidepages']             = 'Cacher les pages correspondant à (expression régulière)';
$lang['send404']               = 'Renvoyer "HTTP 404/Page Non Trouvée" pour les pages introuvables';
$lang['sitemap']               = 'Fréquence de génération une carte Google du site (en jours)';
$lang['broken_iua']            = 'La fonction ignore_user_abort est-elle opérationnelle sur votre système ? Ceci peut empêcher le fonctionnement de l\'index de recherche. IIS+PHP/
CGI dysfonctionne. Voir le <a href="http://bugs.splitbrain.org/?do=details&amp;task_id=852">bug 852</a> pour plus d\'info.';
$lang['xsendfile']             = 'Utiliser l\'en-tête X-Sendfile pour permettre au serveur Web de délivrer des fichiers statiques ? Votre serveur Web doit supporter cette fonctionnalité.';
$lang['renderer_xhtml']        = 'Moteur de rendu du format de sortie principal (XHTML)';
$lang['renderer__core']        = '%s (cœur de dokuwiki)';
$lang['renderer__plugin']      = '%s (module externe)';
$lang['rememberme']            = 'Permettre de conserver de manière permanente les cookies de connexion (mémoriser)';
$lang['rss_type']              = 'Type de flux RSS';
$lang['rss_linkto']            = 'Lien du flux RSS vers';
$lang['rss_content']           = 'Quel contenu afficher dans le flux RSS ?';
$lang['rss_update']            = 'Fréquence de mise à jour du flux RSS (en secondes)';
$lang['recent_days']           = 'Signaler les pages modifiées depuis (en jours)';
$lang['rss_show_summary']      = 'Le flux XML affiche le résumé dans le titre';
$lang['target____wiki']        = 'Cible pour liens internes';
$lang['target____interwiki']   = 'Cible pour liens interwiki';
$lang['target____extern']      = 'Cible pour liens externes';
$lang['target____media']       = 'Cible pour liens média';
$lang['target____windows']     = 'Cible pour liens vers partages Windows';
$lang['proxy____host']         = 'Proxy - Serveur hôte';
$lang['proxy____port']         = 'Proxy - Numéro de port';
$lang['proxy____user']         = 'Proxy - Identifiant';
$lang['proxy____pass']         = 'Proxy - Mot de passe';
$lang['proxy____ssl']          = 'Proxy - Utilisation de SSL';
$lang['proxy____except']       = 'Expression régulière de test des URLs pour lesquelles le proxy ne devrait pas être utilisé.';
$lang['safemodehack']          = 'Activer l\'option Mode sans échec';
$lang['ftp____host']           = 'FTP - Serveur hôte pour Mode sans échec';
$lang['ftp____port']           = 'FTP - Numéro de port pour Mode sans échec';
$lang['ftp____user']           = 'FTP - Identifiant pour Mode sans échec';
$lang['ftp____pass']           = 'FTP - Mot de passe pour Mode sans échec';
$lang['ftp____root']           = 'FTP - Répertoire racine pour Mode sans échec';
$lang['license_o_']            = 'Aucune choisie';
$lang['typography_o_0']        = 'aucun';
$lang['typography_o_1']        = 'guillemets uniquement';
$lang['typography_o_2']        = 'tout signe typographique (peut ne pas fonctionner)';
$lang['userewrite_o_0']        = 'aucun';
$lang['userewrite_o_1']        = 'Fichier .htaccess';
$lang['userewrite_o_2']        = 'Interne à DokuWiki';
$lang['deaccent_o_0']          = 'off';
$lang['deaccent_o_1']          = 'supprimer les accents';
$lang['deaccent_o_2']          = 'convertir en roman';
$lang['gdlib_o_0']             = 'Librairie GD non disponible';
$lang['gdlib_o_1']             = 'version 1.x';
$lang['gdlib_o_2']             = 'auto-détectée';
$lang['rss_type_o_rss']        = 'RSS 0.91';
$lang['rss_type_o_rss1']       = 'RSS 1.0';
$lang['rss_type_o_rss2']       = 'RSS 2.0';
$lang['rss_type_o_atom']       = 'Atom 0.3';
$lang['rss_type_o_atom1']      = 'Atom 1.0';
$lang['rss_content_o_abstract'] = 'Résumé';
$lang['rss_content_o_diff']    = 'Diff. unifié';
$lang['rss_content_o_htmldiff'] = 'Diff. formaté en table HTML';
$lang['rss_content_o_html']    = 'page complète au format HTML';
$lang['rss_linkto_o_diff']     = 'liste des différences';
$lang['rss_linkto_o_page']     = 'page révisée';
$lang['rss_linkto_o_rev']      = 'liste des révisions';
$lang['rss_linkto_o_current']  = 'page actuelle';
$lang['compression_o_0']       = 'aucune';
$lang['compression_o_gz']      = 'gzip';
$lang['compression_o_bz2']     = 'bz2';
$lang['xsendfile_o_0']         = 'ne pas utiliser';
$lang['xsendfile_o_1']         = 'Entête propriétaire lighttpd (avant la version 1.5)';
$lang['xsendfile_o_2']         = 'Entête standard X-Sendfile';
$lang['xsendfile_o_3']         = 'En-tête propriétaire Nginx X-Accel-Redirect';
$lang['showuseras_o_loginname'] = 'Identifiant de l\'utilisateur';
$lang['showuseras_o_username'] = 'Nom de l\'utilisateur';
$lang['showuseras_o_email']    = 'Courriel de l\'utilisateur (brouillé suivant les paramètres de brouillage sélectionnés)';
$lang['showuseras_o_email_link'] = 'Courriel de l\'utilisateur en tant que lien mailto:';
$lang['useheading_o_0']        = 'Jamais';
$lang['useheading_o_navigation'] = 'Navigation seulement';
$lang['useheading_o_content']  = 'Contenu du wiki seulement';
$lang['useheading_o_1']        = 'Toujours';
$lang['readdircache']          = 'Durée de vie maximale du cache pour readdir (sec)';
