<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Tor Härnqvist <tor@harnqvist.se>
 */
$lang['checkupdate']           = 'Sök återkommande efter uppdateringar.';
$lang['only_admins']           = 'Tillåt indexmeny-syntax endast för administratörer.<br>Notera att en sida som redigeras av en icke-administratör kommer att förlora alla inkluderade indexmeny-träd.';
$lang['hide_headpage']         = 'Göm huvudsidor.';
$lang['page_index']            = 'Sidan kommer att ersätta ordinarie DokuWiki-index. Skapa den och lägg till indexmeny-syntax. Använd code>id#random</code> om du redan har en sidofält för indexmeny med navbar-alternativ. Jag föreslår att du använder <code>{{indexmenu>..|js navbar nocookie id#random}}</code>.';
$lang['empty_msg']             = 'Meddelande att visa när trädet är tomt. Använd DokuWiki-syntax, inte html-kod. <code>{{ns}}</code>-variabeln är en förkortning för den begärda namnrymden.';
$lang['skip_index']            = 'Namnsrymd-ID att hoppa över. Använd formatet för reguljära uttryck. Exempel <code>/(:start$|^public:newstart$)/</code>';
$lang['skip_file']             = 'Sid-ID att hoppa över. Använd formatet för reguljära uttryck. Exempel <code>/(:start$|^public:newstart$)/</code>';
$lang['show_sort']             = 'Visa för administratörer indexmenyns sorteringsnummer som en anteckning överst på sidan';
$lang['themes_url']            = 'Ladda ner js-templat från denna http-url';
$lang['be_repo']               = 'Låt andra ladda ner templat från din webbplats.';
$lang['defaultoptions']        = 'Lista på indexmeny-alternativ separerade med blanksteg. Dessa alternativ kommer att appliceras som standard till alla indexmenyer och kan inaktiveras med reverseringskommande i pluginets syntax.';
