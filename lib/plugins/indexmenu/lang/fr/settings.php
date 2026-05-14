<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Samuele Tognini <samuele@samuele.netsons.org>
 * @author Johan Binard <johan.binard@gmail.com>
 * @author Schplurtz le Déboulonné <schplurtz@laposte.net>
 */
$lang['checkupdate']           = 'Vérifier périodiquement les mises à jour.';
$lang['only_admins']           = 'N\'autoriser la syntaxe d\'indexmenu qu\'aux admins.<br>Notez qu\'une page éditée par un utilisateur non-admin perdra tout arborescence contenue à l\'intérieur.';
$lang['aclcache']              = 'Optimiser le cache d\'indexmenu pour les ACL (fonctionne uniquement pour la racine des catégories demandées).<br>Le choix de la méthode affecte seulement la visualisation des noeuds dans l\'arborescence, pas les autorisations de page.<ul><li>None: Standard. C\'est la méthode la plus rapide et elle ne crée pas d\'autre fichier de cache, mais les noeuds avec une permission refusée peuvent être montrés aux utilisateurs non-autorisés et vice-versa. Recommandée lorsque vous ne refusez l\'accès à aucune page par ACL ou si vous ne vous souciez pas de la façon dont l\'arbre est affiché.<li>User: Connexion par utilisateur. Méthode plus lente et qui crée un grand nombre de fichiers de cache, mais elle cache toujours correctement les page refusées. Recommandée quand vous avez des ACL de page qui dépendent du login de l\'utilisateur.<li>Groups: Par groupes de membres. Bon compromis entre les précédentes méthodes, mais si vous interdisez l\'accès ACL en lecture à un utilisateur appartenant à un groupe ayant les droits ACL en lecture, il pourra alors toujours voir ce noeud dans l\'arbre. Recommandée quand l\'ensemble des ACLs de votre site dépendent de groupes.</ul>';
$lang['headpage']              = 'Méthode d\'en-tête : la page à partir de laquelle récupérer le titre et le lien d\'une catégorie.<br> Il peut s\'agir de n\'importe laquelle de ces valeurs:<ul><li>La page d\'accueil globale.<li>Une page avec le nom de la catégorie et qui se trouve à l\'intérieur.<li>Une page avec le nom de la catégorie et qui est au même niveau.<li>Une page de nom personnalisée.<li>Une liste de noms de pages séparées par des virgules.</ul>';
$lang['hide_headpage']         = 'Cacher les en-têtes.';
$lang['page_index']            = 'La page qui remplacera l\'index principal de Dokuwiki. Créez-la et insérez la syntaxe d\'indexmenu. Utilisez id#random si vous avez déjà une barre latérale indexmenu avec l\'option navbar. Je vous suggère "{{indexmenu>..|js navbar nocookie id#random}}".';
$lang['empty_msg']             = 'Message à afficher lorsqu\'un arbre est vide. Utilisez la syntaxe Dokuwiki, pas le code html. La variable {{ns}} est un raccourci pour la catégorie demandée.';
$lang['skip_index']            = 'Id des catégories à ignorer. Utilisez le format d\'expression régulière. Exemple: /(sidebars|private:myns)/';
$lang['skip_file']             = 'Id des pages à ignorer. Utilisez le format d\'expression régulière. Exemple /(:start$|^public:newstart$)/';
$lang['show_sort']             = 'Montrer aux admins le numéro de tri d\'indexmenu en haut des notes de page';
$lang['themes_url']            = 'Télécharger des thèmes js à partir de cette url.';
$lang['be_repo']               = 'Laisser les autres télécharger des thèmes à partir de votre site.';
$lang['defaultoptions']        = 'Liste séparée par des espaces d\'options indexmenu. Ce seront les options par défaut de chaque indexmenu. Elles peuvent être individuellement inversées par une commande dans la syntaxe de l\'extension. ';
