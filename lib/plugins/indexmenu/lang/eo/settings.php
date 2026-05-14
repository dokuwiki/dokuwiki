<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Robert Bogenschneider <bogi@uea.org>
 */
$lang['checkupdate']           = 'Kontroli ripete por aktualigoj.';
$lang['only_admins']           = 'Permesi indeksmenu-sintakson nur al administrantoj.<br>Notu, ke paĝo modifata de ne-administranto perdas ĉiujn enajn indeksmenu-arbojn.';
$lang['aclcache']              = 'Optimumigu la indexmenu-kaŝmemoron por alirkontrolo (funkcias nur por nomspacoj demandataj de root).<br>La elekto de la metodo influas nur la montron de nodoj en la indexmenu-arbo, ne la paĝ-rajtigojn.<ul><li>Neniu: standardo. Tio estas pli rapida metodo kaj ne kreas aldonajn kaŝmemor-dosierojn, sed nodoj sen alirrajtoj povus esti montrataj al nerajtigitoj kaj inverse. Rekomendita se vi ne malpermesas aliron aŭ ne gravas, kiel la arbo estas montrata.<li>Uzanto: po-uzanta ensalutado. Malpli rapida metodo, kiu kreas multajn kaŝmemor-dosierojn, sed ĉiam ĝuste kaŝas malpermesitajn paĝojn. Rekomendita se vi uzas paĝalirkontrolon, kiu dependas je ensalutado de uzantoj.<li>Grupoj: Per grupa membreco. Bona kompromiso inter la intaŭaj metodoj, sed kaze ke uzanto apartenas al grupo kun lego-permeso, li tamen povas legi ĉion en la nodo. Rekomendita se la alirkontrolo de via retejo dependas de grupmembreco.</ul>';
$lang['headpage']              = 'Metodo por la kapopaĝo: la paĝo, de kiu preni la titolon kaj ligilon al nomspaco.<br>Povas esti iu el tiuj:<ul><li>La ĉefa startpaĝo.<li>Paĝo ene de nomspaco kaj ties enhavo.<li>Samnivela nomspaco kaj ties enhavo.<li>Mem kreita paĝnomo.<li>Komodisigita listo de paĝnomoj.</ul>';
$lang['hide_headpage']         = 'Kaŝi ĉefpaĝojn.';
$lang['page_index']            = 'La paĝo, kiu anstataŭos la ĉefan dokuwiki-indekson. Kreu ĝin kaj enmetu la indexmenu-kodon. Uzu <code>id#random</code>, se vi jam havas flankan indexmenu-strion kun navbar-opcio. Mi proponas <code>{{indexmenu>..|js navbar nocookie id#random}}</code>.';
$lang['empty_msg']             = 'Mesaĝo, kiam arbo estas malplena. Uzi DokuWiki-sintakson, ne HTML-kodon. La <code>{{ns}}</code>>-variablo estas mallongigo por la koncerna nomspaco.';
$lang['skip_index']            = 'Nomspacoj por pretersalti. Uzu regulan esprimon. Ekzemplo:  <code>/(sidebars|private:myns)/</code>';
$lang['skip_file']             = 'Paĝoj por pretersalti. Uzu regulan esprimon. Ekzemplo: <code>/(:start$|^public:newstart$)/</code>';
$lang['show_sort']             = 'Montri la indexmenu-numeron al administrantoj kiel noto ĉe la paĝokomenco';
$lang['themes_url']            = 'HTTP-URL, de kiu elŝuti js-temojn.';
$lang['be_repo']               = 'Permesi al aliaj elŝuti temojn de via paĝaro.';
