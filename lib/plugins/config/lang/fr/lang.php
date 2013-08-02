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
 * @author Yannick Aure <yannick.aure@gmail.com>
 * @author Olivier DUVAL <zorky00@gmail.com>
 * @author Anael Mobilia <contrib@anael.eu>
 * @author Bruno Veilleux <bruno.vey@gmail.com>
 */
$lang['menu']                  = 'Paramètres de configuration';
$lang['error']                 = 'Paramètres non modifiés en raison d\'une valeur invalide, vérifiez vos réglages puis réessayez. <br />Les valeurs erronées sont entourées d\'une bordure rouge.';
$lang['updated']               = 'Paramètres mis à jour avec succès.';
$lang['nochoice']              = '(aucun autre choix possible)';
$lang['locked']                = 'Le fichier des paramètres ne peut être modifié, si ceci n\'est pas intentionnel, <br /> vérifiez que le nom et les autorisations du fichier sont correctes.';
$lang['danger']                = 'Danger : modifier cette option pourrait rendre inaccessibles votre wiki et son menu de configuration.';
$lang['warning']               = 'Attention : modifier cette option pourrait engendrer un comportement indésirable.';
$lang['security']              = 'Avertissement de sécurité : modifier cette option pourrait induire un risque de sécurité.';
$lang['_configuration_manager'] = 'Gestionnaire de configuration';
$lang['_header_dokuwiki']      = 'Paramètres de DokuWiki';
$lang['_header_plugin']        = 'Paramètres des extensions';
$lang['_header_template']      = 'Paramètres des modèles';
$lang['_header_undefined']     = 'Paramètres indéfinis';
$lang['_basic']                = 'Paramètres de base';
$lang['_display']              = 'Paramètres d\'affichage';
$lang['_authentication']       = 'Paramètres d\'authentification';
$lang['_anti_spam']            = 'Paramètres anti-spam';
$lang['_editing']              = 'Paramètres d\'édition';
$lang['_links']                = 'Paramètres des liens';
$lang['_media']                = 'Paramètres des médias';
$lang['_notifications']        = 'Paramètres de notification';
$lang['_syndication']          = 'Paramètres de syndication';
$lang['_advanced']             = 'Paramètres avancés';
$lang['_network']              = 'Paramètres réseaux';
$lang['_msg_setting_undefined'] = 'Pas de définition de métadonnées';
$lang['_msg_setting_no_class'] = 'Pas de définition de paramètres.';
$lang['_msg_setting_no_default'] = 'Pas de valeur par défaut.';
$lang['title']                 = 'Titre du wiki (nom du wiki)';
$lang['start']                 = 'Nom de la page d\'accueil à utiliser pour toutes les catégories';
$lang['lang']                  = 'Langue de l\'interface';
$lang['template']              = 'Modèle (rendu visuel du wiki)';
$lang['tagline']               = 'Descriptif du site (si le modèle supporte cette fonctionnalité)';
$lang['sidebar']               = 'Nom du panneau latéral (si le modèle supporte cette fonctionnalité). Laisser le champ vide désactive le panneau latéral.';
$lang['license']               = 'Sous quelle licence doit-être placé le contenu ?';
$lang['savedir']               = 'Répertoire d\'enregistrement des données';
$lang['basedir']               = 'Répertoire de base du serveur (par exemple : <code>/dokuwiki/</code>). Laisser vide pour une détection automatique.';
$lang['baseurl']               = 'URL de base du site (par exemple <code>http://www.example.com</code>). Laisser vide pour une détection automatique.';
$lang['cookiedir']             = 'Chemin des cookies. Laissez vide pour utiliser l\'URL de base.';
$lang['dmode']                 = 'Mode de création des répertoires';
$lang['fmode']                 = 'Mode de création des fichiers';
$lang['allowdebug']            = 'Debug (<strong>Ne l\'activez que si vous en avez besoin !</strong>)';
$lang['recent']                = 'Nombre de lignes à afficher - par page - pour les derniers changements';
$lang['recent_days']           = 'Signaler les pages modifiées depuis (en jours)';
$lang['breadcrumbs']           = 'Nombre de traces à afficher. 0 désactive cette fonctionnalité.';
$lang['youarehere']            = 'Utiliser des traces hiérarchiques (vous voulez probablement désactiver l\'option ci-dessus)';
$lang['fullpath']              = 'Afficher le chemin complet des pages dans le pied de page';
$lang['typography']            = 'Effectuer des améliorations typographiques';
$lang['dformat']               = 'Format de date (cf. fonction <a href="http://fr.php.net/strftime">strftime</a> de PHP)';
$lang['signature']             = 'Données à insérer lors de l\'utilisation du bouton « signature » dans l\'éditeur';
$lang['showuseras']            = 'Données à afficher concernant le dernier utilisateur ayant modifié une page';
$lang['toptoclevel']           = 'Niveau le plus haut à afficher dans la table des matières';
$lang['tocminheads']           = 'Nombre minimum de titres pour qu\'une table des matières soit affichée';
$lang['maxtoclevel']           = 'Niveau maximum pour figurer dans la table des matières';
$lang['maxseclevel']           = 'Niveau maximum pour modifier des sections';
$lang['camelcase']             = 'Utiliser l\'affichage «CamelCase » pour les liens';
$lang['deaccent']              = 'Retirer les accents dans les noms de pages';
$lang['useheading']            = 'Utiliser le titre de premier niveau pour le nom de la page';
$lang['sneaky_index']          = 'Par défaut, DokuWiki affichera toutes les catégories dans la vue par index. Activer cette option permet de cacher les catégories pour lesquelles l\'utilisateur n\'a pas l\'autorisation de lecture. Il peut en résulter le masquage de sous-catégories accessibles. Ceci peut rendre l\'index inutilisable avec certains contrôles d\'accès.';
$lang['hidepages']             = 'Cacher les pages correspondant à (expression régulière)';
$lang['useacl']                = 'Utiliser les listes de contrôle d\'accès (ACL)';
$lang['autopasswd']            = 'Auto-générer les mots de passe';
$lang['authtype']              = 'Mécanisme d\'authentification';
$lang['passcrypt']             = 'Méthode de chiffrement des mots de passe';
$lang['defaultgroup']          = 'Groupe par défaut : tous les nouveaux utilisateurs y seront affectés';
$lang['superuser']             = 'Super-utilisateur : groupe, utilisateur ou liste séparée par des virgules utilisateur1,@groupe1,utilisateur2 ayant un accès complet à toutes les pages quelque soit le paramétrage des contrôle d\'accès';
$lang['manager']               = 'Manager:- groupe, utilisateur ou liste séparée par des virgules utilisateur1,@groupe1,utilisateur2 ayant accès à certaines fonctionnalités de gestion';
$lang['profileconfirm']        = 'Confirmer les modifications de profil par la saisie du mot de passe ';
$lang['rememberme']            = 'Permettre de conserver de manière permanente les cookies de connexion (mémoriser)';
$lang['disableactions']        = 'Actions à désactiver dans DokuWiki';
$lang['disableactions_check']  = 'Vérifier';
$lang['disableactions_subscription'] = 'Abonnement aux pages';
$lang['disableactions_wikicode'] = 'Afficher le texte source';
$lang['disableactions_other']  = 'Autres actions (séparées par des virgules)';
$lang['auth_security_timeout'] = 'Délai d\'expiration de sécurité (secondes)';
$lang['securecookie']          = 'Les cookies définis via HTTPS doivent-ils n\'être envoyé par le navigateur que via HTTPS ? Désactivez cette option lorsque seule la connexion à votre wiki est sécurisée avec SSL et que la navigation sur le wiki est effectuée de manière non sécurisée.';
$lang['remote']                = 'Active l\'API système distante. Ceci permet à d\'autres applications d\'accéder au wiki via XML-RPC ou d\'autres mécanismes.';
$lang['remoteuser']            = 'Restreindre l\'accès à l\'API à une liste de groupes ou d\'utilisateurs (séparés par une virgule). Laisser vide pour donner l\'accès tout le monde.';
$lang['usewordblock']          = 'Bloquer le spam selon les mots utilisés';
$lang['relnofollow']           = 'Utiliser l\'attribut « rel="nofollow" » sur les liens extérieurs';
$lang['indexdelay']            = 'Délai avant l\'indexation (secondes)';
$lang['mailguard']             = 'Cacher les adresses de courriel';
$lang['iexssprotect']          = 'Vérifier, dans les fichiers envoyés, la présence de code JavaScript ou HTML malveillant';
$lang['usedraft']              = 'Enregistrer automatiquement un brouillon pendant l\'édition';
$lang['htmlok']                = 'Permettre l\'utilisation de code HTML dans les pages';
$lang['phpok']                 = 'Permettre l\'utilisation de code PHP dans les pages';
$lang['locktime']              = 'Âge maximum des fichiers de blocage (secondes)';
$lang['cachetime']             = 'Âge maximum d\'un fichier en cache (secondes)';
$lang['target____wiki']        = 'Cible pour liens internes';
$lang['target____interwiki']   = 'Cible pour liens interwiki';
$lang['target____extern']      = 'Cible pour liens externes';
$lang['target____media']       = 'Cible pour liens média';
$lang['target____windows']     = 'Cible pour liens vers partages Windows';
$lang['mediarevisions']        = 'Activer les révisions (gestion de versions) des médias';
$lang['refcheck']              = 'Vérifier si un média est toujours utilisé avant de le supprimer';
$lang['gdlib']                 = 'Version de la librairie GD';
$lang['im_convert']            = 'Chemin vers l\'outil de conversion ImageMagick';
$lang['jpg_quality']           = 'Qualité de la compression JPEG (0-100)';
$lang['fetchsize']             = 'Taille maximale (en octets) que fetch.php peut télécharger depuis une URL tierce (par exemple pour conserver en cache et redimensionner une image tierce)';
$lang['subscribers']           = 'Activer l\'abonnement aux pages';
$lang['subscribe_time']        = 'Délai après lequel les listes d\'abonnement et résumés sont expédiés (en secondes). Devrait être plus petit que le délai précisé dans recent_days.';
$lang['notify']                = 'Notifier systématiquement les modifications à cette adresse de courriel';
$lang['registernotify']        = 'Notifier systématiquement les nouveaux utilisateurs enregistrés à cette adresse de courriel';
$lang['mailfrom']              = 'Adresse de courriel de l\'expéditeur des notifications par courriel du wiki';
$lang['mailprefix']            = 'Préfixe à utiliser dans les objets des courriels automatiques. Laisser vide pour utiliser le titre du wiki';
$lang['htmlmail']              = 'Envoyer des courriel HTML multipart (visuellement plus agréable, mais plus lourd). Désactiver pour utiliser uniquement des courriel plain text';
$lang['sitemap']               = 'Fréquence de génération du sitemap Google (jours). 0 pour désactiver';
$lang['rss_type']              = 'Type de flux XML (RSS)';
$lang['rss_linkto']            = 'Lien du flux XML vers';
$lang['rss_content']           = 'Quel contenu afficher dans le flux XML?';
$lang['rss_update']            = 'Fréquence de mise à jour du flux XML (secondes)';
$lang['rss_show_summary']      = 'Le flux XML affiche le résumé dans le titre';
$lang['rss_media']             = 'Quels types de changements doivent être listés dans le flux XML?';
$lang['updatecheck']           = 'Vérifier les mises à jour et alertes de sécurité? DokuWiki doit pouvoir contacter update.dokuwiki.org';
$lang['userewrite']            = 'Utiliser des URL esthétiques';
$lang['useslash']              = 'Utiliser « / » comme séparateur de catégories dans les URL';
$lang['sepchar']               = 'Séparateur de mots dans les noms de page';
$lang['canonical']             = 'Utiliser des URL canoniques';
$lang['fnencode']              = 'Méthode pour l\'encodage des fichiers non-ASCII';
$lang['autoplural']            = 'Rechercher les formes plurielles dans les liens';
$lang['compression']           = 'Méthode de compression pour les fichiers attic';
$lang['gzip_output']           = 'Utiliser gzip pour le Content-Encoding du XHTML';
$lang['compress']              = 'Compresser les flux CSS et JavaScript';
$lang['cssdatauri']            = 'Taille maximale en octets pour inclure dans les feuilles de styles CSS les images qui y sont référencées. Cette technique réduit le nombre de requêtes HTTP. Cette fonctionnalité ne fonctionne qu\'à partir de la version 8 d\'Internet Explorer! Nous recommandons une valeur entre <code>400</code> et <code>600</code>. <code>0</code> pour désactiver.';
$lang['send404']               = 'Renvoyer « HTTP 404/Page Not Found » pour les pages inexistantes';
$lang['broken_iua']            = 'La fonction ignore_user_abort est-elle opérationnelle sur votre système ? Ceci peut empêcher le fonctionnement de l\'index de recherche. IIS+PHP/
CGI dysfonctionne. Voir le <a href="http://bugs.splitbrain.org/?do=details&amp;task_id=852">bug 852</a> pour plus d\'informations.';
$lang['xsendfile']             = 'Utiliser l\'en-tête X-Sendfile pour permettre au serveur web de délivrer les fichiers statiques ? Votre serveur web doit supporter cette fonctionnalité.';
$lang['renderer_xhtml']        = 'Moteur de rendu du format de sortie principal (XHTML)';
$lang['renderer__core']        = '%s (cœur de DokuWiki)';
$lang['renderer__plugin']      = '%s (extension)';
$lang['dnslookups']            = 'DokuWiki effectuera une résolution du nom d\'hôte sur les adresses IP des utilisateurs modifiant des pages. Si vous ne possédez pas de serveur DNS, que ce dernier est lent ou que vous ne souhaitez pas utiliser cette fonctionnalité : désactivez-la.';
$lang['proxy____host']         = 'Mandataire (proxy) - Hôte';
$lang['proxy____port']         = 'Mandataire - Port';
$lang['proxy____user']         = 'Mandataire - Identifiant';
$lang['proxy____pass']         = 'Mandataire - Mot de passe';
$lang['proxy____ssl']          = 'Mandataire - Utilisation de SSL';
$lang['proxy____except']       = 'Mandataire - Expression régulière de test des URLs pour lesquelles le mandataire (proxy) ne doit pas être utilisé.';
$lang['safemodehack']          = 'Activer l\'option Mode sans échec';
$lang['ftp____host']           = 'FTP / Mode sans échec - Serveur hôte';
$lang['ftp____port']           = 'FTP / Mode sans échec - Port';
$lang['ftp____user']           = 'FTP / Mode sans échec - Identifiant';
$lang['ftp____pass']           = 'FTP / Mode sans échec - Mot de passe';
$lang['ftp____root']           = 'FTP / Mode sans échec - Répertoire racine';
$lang['license_o_']            = 'Aucune choisie';
$lang['typography_o_0']        = 'aucun';
$lang['typography_o_1']        = 'guillemets uniquement';
$lang['typography_o_2']        = 'tout signe typographique (peut ne pas fonctionner)';
$lang['userewrite_o_0']        = 'aucun';
$lang['userewrite_o_1']        = 'Fichier .htaccess';
$lang['userewrite_o_2']        = 'Interne à DokuWiki';
$lang['deaccent_o_0']          = 'off';
$lang['deaccent_o_1']          = 'supprimer les accents';
$lang['deaccent_o_2']          = 'convertir en caractères latins';
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
