<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author QoStik <gene.umb@gmail.com>
 * @author Johan Binard <johan.binard@gmail.com>
 * @author Schplurtz le Déboulonné <schplurtz@laposte.net>
 */
$lang['menu']                  = 'Utilitaires Indexmenu';
$lang['fetch']                 = 'Montrer';
$lang['install']               = 'Installer';
$lang['delete']                = 'Supprimer';
$lang['check']                 = 'Vérifier';
$lang['no_repos']              = 'Aucune Url de répertoire de thèmes configurée.';
$lang['disabled']              = 'Désactivé';
$lang['conn_err']              = 'Erreur de connexion';
$lang['dir_err']               = 'Impossible de créer un répertoire temporaire pour recevoir le thème';
$lang['down_err']              = 'Impossible de recevoir le thème';
$lang['zip_err']               = 'Erreur de création ou d\'extraction de Zip';
$lang['install_ok']            = 'Thème installé avec succès. Le nouveau thème est disponible dans la barre d\'outils de l\'éditeur de page ou avec l\'option <code>js#nom_du_theme</code>.';
$lang['install_no']            = 'Erreur de connexion. Cependant, vous pouvez essayer d\'envoyer manuellement votre thème depuis <a href="http://samuele.netsons.org/dokuwiki/lib/plugins/indexmenu/upload/">ici</a>.';
$lang['delete_ok']             = 'Thème supprimé avec sucès.';
$lang['delete_no']             = 'Une erreur s\'est produite lors de la suppression du thème';
$lang['upload']                = 'Partager';
$lang['checkupdates']          = 'Mises à jour de l\'extension';
$lang['noupdates']             = 'Indexmenu n\'a pas besoin d\'être mis à jour. Vous avez déjà la dernière version:';
$lang['infos']                 = 'Vous pouvez créer votre thème en suivant les instructions de la page <a href="http://wiki.splitbrain.org/plugin:indexmenu#theme_tutorial">Tutoriel de thème</a>. <br />Vous pouvez ensuite rendre plus de gens heureux :-) en l\'envoyant au répertoire public d\'indexmenu avec le bouton "Partager" sous ce thème.';
$lang['showsort']              = 'Numéro de tri d\'indexmenu: ';
$lang['donation_text']         = 'Le greffon indexmenu n\'est pas sponsorisé mais je le développe et le maintiens gratuitement durant mon temps libre. Si vous gagnez quelque chose grâce à lui ou si vous voulez soutenir son développement, vous pouvez envisager de faire un don.';
$lang['js']['indexmenuwizard'] = 'Assistant d\'Indexmenu';
$lang['js']['index']           = 'Index';
$lang['js']['options']         = 'Options';
$lang['js']['navigation']      = 'Navigation';
$lang['js']['sort']            = 'Tri';
$lang['js']['filter']          = 'Filtre';
$lang['js']['performance']     = 'Performances';

$lang['js']['namespace']       = 'Catégorie';
$lang['js']['nsdepth']         = 'Profondeur';
$lang['js']['js']              = 'Arbre généré par Javascript, vous pouvez définir votre propre thème';
$lang['js']['theme']           = 'Thème';
$lang['js']['navbar']          = 'L\'arbre s\'ouvre dans la catégorie actuelle';
$lang['js']['context']         = 'Affiche l\'arborescence du contexte de la catégorie actuelle';
$lang['js']['nocookie']        = 'Ne pas se souvenir des noeuds ouverts/fermés pendant la navigation de l\'utilisateur';
$lang['js']['noscroll']        = 'Empêche de faire défiler l\'arbre quand il ne tient pas dans la largeur de son conteneur';
$lang['js']['notoc']           = 'Désactive la fonction de prévisualisation de la table des matières';
$lang['js']['tsort']           = 'Par titre';
$lang['js']['dsort']           = 'Par date';
$lang['js']['msort']           = 'Par meta-tag';
$lang['js']['nsort']           = 'Trier aussi les catégories';
$lang['js']['hsort']           = 'Trier par page d\'accueil';
$lang['js']['rsort']           = 'Inverser le tri des pages';
$lang['js']['nons']            = 'Montrer seulement les pages';
$lang['js']['nopg']            = 'Montrer seulement les catégories';
$lang['js']['max']             = 'Nombre de niveaux à afficher avec AJAX quand on ouvre un noeud. La seconde valeur correspond au nombre de sous-niveaux récupérés avec AJAX plutôt que d\'un seul coup.';
$lang['js']['maxjs']           = 'Nombre de niveaux à afficher dans le navigateur client quand un noeud est ouvert';
$lang['js']['id']              = 'Id de cookie auto-défini pour ce menu';
$lang['js']['insert']          = 'Insérer le menu';
$lang['js']['metanum']         = 'Meta-nombre pour le tri';
$lang['js']['insertmetanum']   = 'Insérer le méta-nombre';

/* contextmenu.js */
$lang['js']['page']            = 'Page';
$lang['js']['revs']            = 'Révisions';
$lang['js']['tocpreview']      = 'Prévisualisation de la TDM';
$lang['js']['editmode']        = 'Mode d\'édition';
$lang['js']['insertdwlink']    = 'Insérer en lien DW';
$lang['js']['insertdwlinktooltip'] = 'Insérer le lien de cette page à la position du curseur dans la boîte d\'édition';
$lang['js']['ns']              = 'Catégorie';
$lang['js']['search']          = 'Recherche...';
$lang['js']['searchtooltip']   = 'Chercher les pages de cette catégorie';
$lang['js']['create']          = 'Créer';
$lang['js']['more']            = 'Plus';
$lang['js']['headpage']        = 'Page d\'accueil';
$lang['js']['headpagetooltip'] = 'Nouvelle page d\'accueil sous cette page';
$lang['js']['startpage']       = 'Page de démarrage';
$lang['js']['startpagetooltip'] = 'Nouvelle page de démarrage sous cette page';
$lang['js']['custompage']      = 'Page personnalisée';
$lang['js']['custompagetooltip'] = 'Nouvelle page personnalisée sous cette page';
$lang['js']['acls']            = 'ACL';
$lang['js']['purgecache']      = 'vider le cache';
$lang['js']['exporthtml']      = 'Exporter en HTML';
$lang['js']['exporttext']      = 'Exporter en texte';
$lang['js']['headpagehere']    = 'Nouvelle page d\'accueil ici';
$lang['js']['headpageheretooltip'] = 'Nouvelle page personnalisée dans cette catégorie';
$lang['js']['newpage']         = 'Nouvelle page';
$lang['js']['newpagetooltip']  = 'Nouvelle page dans cette catégorie';
$lang['js']['newpagehere']     = 'Nouvelle page ici';
$lang['js']['insertkeywords']  = 'Mots-clefs à chercher dans cette catégorie';
$lang['js']['insertpagename']  = 'Nom de la page à créer';
$lang['js']['edit']            = 'Modifier';
$lang['js']['loading']         = 'Chargement...';
