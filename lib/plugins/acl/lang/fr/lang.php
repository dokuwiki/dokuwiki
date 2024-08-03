<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Olivier Humbert <trebmuh@tuxfamily.org>
 * @author Schplurtz le DÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ©boulonnÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ© <Schplurtz@laposte.net>
 * @author SÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ©bastien Bauer <sebastien.bauer@advalvas.be>
 * @author Antoine Fixary <antoine.fixary@freesbee.fr>
 * @author cumulus <pta-n56@myamail.com>
 * @author Gwenn Gueguen <contact@demisel.net>
 * @author Guy Brand <gb@unistra.fr>
 * @author Fabien Chabreuil <fabien@integralpersonality.com>
 * @author StÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ©phane Chamberland <stephane.chamberland@ec.gc.ca>
 * @author Maurice A. LeBlanc <leblancma@cooptel.qc.ca>
 * @author stephane.gully <stephane.gully@gmail.com>
 * @author Guillaume Turri <guillaume.turri@gmail.com>
 * @author Erik Pedersen <erik.pedersen@shaw.ca>
 * @author olivier duperray <duperray.olivier@laposte.net>
 * @author Vincent Feltz <psycho@feltzv.fr>
 * @author Philippe Bajoit <philippe.bajoit@gmail.com>
 * @author Florian Gaub <floriang@floriang.net>
 * @author Johan Guilbaud <guilbaud.johan@gmail.com>
 * @author Yannick Aure <yannick.aure@gmail.com>
 * @author Olivier DUVAL <zorky00@gmail.com>
 * @author Anael Mobilia <contrib@anael.eu>
 * @author Bruno Veilleux <bruno.vey@gmail.com>
 */
$lang['admin_acl']             = 'Gestion de la liste des contrÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ´les d\'accÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ¨s (ACL)';
$lang['acl_group']             = 'Groupe :';
$lang['acl_user']              = 'Utilisateur :';
$lang['acl_perms']             = 'Autorisations pour';
$lang['page']                  = 'Page';
$lang['namespace']             = 'CatÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ©gorie';
$lang['btn_select']            = 'SÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ©lectionner';
$lang['p_user_id']             = 'Autorisations actuelles de l\'utilisateur <strong class="acluser">%s</strong> sur la page <strong class="aclpage">%s</strong> : <em>%s</em>.';
$lang['p_user_ns']             = 'Autorisations actuelles de l\'utilisateur <strong class="acluser">%s</strong> sur la catÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ©gorie <strong class="aclns">%s</strong> : <em>%s</em>.';
$lang['p_group_id']            = 'Autorisations actuelles des membres du groupe <strong class="aclgroup">%s</strong> sur la page <strong class="aclpage">%s</strong> : <em>%s</em>.';
$lang['p_group_ns']            = 'Autorisations actuelles des membres du groupe <strong class="aclgroup">%s</strong> sur la catÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ©gorie <strong class="aclns">%s</strong> : <em>%s</em>.';
$lang['p_choose_id']           = 'Saisissez un <strong>nom d\'utilisateur ou de groupe</strong> dans le formulaire ci-dessus pour afficher ou ÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ©diter les autorisations relatives ÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ  la page <strong class="aclpage">%s</strong>.';
$lang['p_choose_ns']           = 'Veuillez saisir un <strong>nom d\'utilisateur ou de groupe</strong> dans le formulaire ci-dessus pour afficher ou ÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ©diter les autorisations relatives ÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ  la catÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ©gorie <strong class="aclns">%s</strong>.';
$lang['p_inherited']           = 'Note : ces autorisations n\'ont pas ÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ©tÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ© explicitement dÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ©finies mais sont hÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ©ritÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ©es de groupes ou catÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ©gories supÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ©rieurs.';
$lang['p_isadmin']             = 'Note : le groupe ou l\'utilisateur sÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ©lectionnÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ© dispose toujours de toutes les autorisations car il est paramÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ©trÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ© en tant que super-utilisateur.';
$lang['p_include']             = 'Les autorisations les plus ÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ©levÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ©es incluent les plus faibles. CrÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ©ation, Envoyer et Effacer ne s\'appliquent qu\'aux catÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ©gories, pas aux pages.';
$lang['current']               = 'ContrÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ´les d\'accÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ¨s actuels';
$lang['where']                 = 'Page/CatÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ©gorie';
$lang['who']                   = 'Utilisateur/Groupe';
$lang['perm']                  = 'Autorisations';
$lang['acl_perm_none']             = 'Aucune';
$lang['acl_perm_read']             = 'Lecture';
$lang['acl_perm_edit']             = 'ÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂcriture';
$lang['acl_perm_create']             = 'CrÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ©ation';
$lang['acl_perm_upload']             = 'Envoyer';
$lang['acl_perm_delete']            = 'Effacer';
$lang['acl_new']               = 'Ajouter une nouvelle entrÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ©e';
$lang['acl_mod']               = 'Modifier l\'entrÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂÃÂ©e';
