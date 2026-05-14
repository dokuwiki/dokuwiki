<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Gerrit Uitslag <klapinklapin@gmail.com>
 * @author Joachim David <joa_david@hotmail.com>
 */
$lang['checkupdate']           = 'Controleer regelmatig op updates';
$lang['only_admins']           = 'Sta indexmenu alleen toe voor beheerders. <br> Merk op dat een pagina bewerkt door een niet-beheerder de indexmenu\'s de indexmenu\'s kwijtraakt.';
$lang['aclcache']              = 'Optimaliseer de indexmenu cache voor ACL (werkt alleen voor namespaces opgevraagd uit de root).<br>De van de methode heeft alleen gevolgen voor de weergave van de knooppunten van de indexmenu, niet voor de paginatoegangscontrole.<ul><li>None: Standaard. Het is de snellere methode en het creëert geen extra bufferbestanden, maar de knooppunten met geen toegang kunnen worden weergegeven aan niet-geautoriseerde gebruikers of je maakt je niet druk om hoe de boom wordt weergegeven.<li>User: per-gebruiker login. Langzame methode en creëert veel bufferbestanden, maar het verbergt altijd de juiste pagina\'s. Aanbevolen voor wanneer je pagina\'s hebt met toegangsrechten per gebruiker.<li>Groups: Per-groep lidmaatschap. Goed compromis tussen de vorige methodes, maar in geval je een gebruiker geen leesrechten geeft, maar een groep waar die bij hoort wel, dan zal hij de knooppunten toch kunnen zien. Aanbevolen wanneer je hele site toegangsrechten gebruikt die afhangen van de groepslidmaatschappen.</ul>';
$lang['headpage']              = 'Startpagina methode: de pagina waarvan de titel en de link moet worden gebruikt voor de namespace-knooppunt.<br>Kan een van deze waardes zijn: <ul><li>De globale startpagina.<li>Een pagina met de namespace naam en die zich daarbinnen bevindt.<li>Een pagina met de namespace naam en op het zelfde niveau.<li>Een zelf opgegeven paginanaam.<li>Een komma gescheiden lijst van paginanamen.</ul>';
$lang['hide_headpage']         = 'Verberg startpagina\'s';
$lang['page_index']            = 'De pagina die de hoofdindex van DokuWiki vervangt. Maak het en plaats er indexmenu syntax. Gebruik <code>id#random</code> als je al een indexmenu in de zijbalk hebt staan met de navbar optie. Mijn suggestie is <code>{{indexmenu>..|js navbar nocookie id#random}}</code>.';
$lang['empty_msg']             = 'Bericht dat wordt weergegeven als de indexmenu leeg is. Gebruik de DokuWiki-syntax, geen html code. De <code>{{ns}}</code> variabele geeft de gevraagde namespace naam weer.';
$lang['skip_index']            = 'Namespace-id\'s om over te slaan. Gebruik het Regular Expression formaat. Voorbeeld <code>/(sidebars|private:myns)/</code>';
$lang['skip_file']             = 'Pagina-id\'s om over te slaan. Gebruik het Regular Expression formaat. Voorbeeld <code>/(:start$|^public:newstart$)/</code>';
$lang['show_sort']             = 'Laat beheerders het indexmenu sorteernummer zien als melding bovenaan de pagina.';
$lang['themes_url']            = 'Download de javascript-thema\'s van deze http url.';
$lang['be_repo']               = 'Laat anderen thema\'s downloaden van jouw site.';
$lang['defaultoptions']        = 'Lijst van indexmenu-opties gescheiden door spaties. De opties zullen automatisch worden toegekend aan elk indexmenu en kunnen ongedaan gemaakt worden met een tegengesteld commando in de plugin syntax.';
