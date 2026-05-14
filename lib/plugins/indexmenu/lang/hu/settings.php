<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Marina Vladi <deldadam@gmail.com>
 * @author DelD <deldadam@gmail.com>
 */
$lang['checkupdate']           = 'Frissítések keresése szabályos időközönként';
$lang['only_admins']           = 'Az Indexmenü szintaxisa csak adminisztrátorok számára legyen elérhető.<br />Megj.: ha egy indexmenüt tartalmazó oldalt nem adminisztrátor módosít, akkor a menü az oldalról törlődik.';
$lang['aclcache']              = 'Indexmenü-gyorsítótár optimalizálása ACL-hez (csak root-igényű névterekhez működik).<br />A kiválasztott módszer csak az indexmenü menüpontjainak megjelenítésére van hatással, nem pedig az oldalak engedélyezésére.<ul><li>Nincs (none): Alapértelmezett. Ez a leggyorsabb módszer, nem hoz létre további gyorsító fájlokat, de tiltott menüpontok jelenhetnek meg arra jogosulatlan felhasználóknak, vagy fordítva. Akkor javasolt használni, ha az oldalak hozzáférését nem tiltjuk ACL-lel, vagy érdektelen számunkra, hogy a menü miként jelenik meg.<li>Felhasználói (user): Felhasználói bejelentkezés alapján. Lassabb módszer, és sok gyorsítófájlt hoz létre, de mindig helyesen rejti el a tiltott oldalakat. Akkor javasolt használni, ha az oldalaink hozzáférése felhasználói bejelentkezésen alapul.<li>Csoport (groups): Csoporttagságonként. Jó kompromisszum a két, előző módszer között, de abban az esetben, ha egy olyan felhasználótól tagadjuk meg az olvasási jogot, aki egy olvasási joggal rendelkező tagcsoporthoz tartozik, akkor a mégis látni fogja a menüelemet. Akkor javasolt használni, ha a teljes wikink ACL-beállítása csoporttagságon alapul.</ul>';
$lang['headpage']              = 'Bevezetőlap metódus: az oldal, amelyről egy névtér címét és hivatkozását nyerjük.<br />Bármely következő érték lehet:<ul><li>az általános kezdőoldal<li>oldal névtér nevével és ami benne van<li>oldal névtér nevével, és ami azonos szinten van<li>tetszőleges oldalnév<li>vesszővel elválasztott oldalnevek listája</ul>';
$lang['hide_headpage']         = 'Bevezetőoldalak elrejtése';
$lang['page_index']            = 'Az oldal, amely felváltja a DőkuWiki főindexét. Hozzuk létre, és írjuk bele az indexmenü kódját. Használjuk a <code>id#véletlenszám</code> kódot, ha már rendelkezünk olyan oldalmenüvel, amelyhez a navbar opciót használjuk. Javaslat: <code>{{indexmenu>..|js navbar nocookie id#véletlenszám}}</code>.';
$lang['empty_msg']             = 'Megjelenítendő üzenet, ha a menüfa üres. Használjunk DokuWiki-szintaxist, ne pedig HTML-kódot. A <code>{{ns}}</code> változó a kívánt névtér rövidítése.';
$lang['skip_index']            = 'Kihagyni kívánt névterek ID-je (azonosítója). Használjuk a reguláris kifejezések formátumát. Példa: <code>/(oldalmenu|privat:nevterem)/</code>.';
$lang['skip_file']             = 'Kihagyni kívánt oldalak ID-je (azonosítója). Használjuk a reguláris kifejezések formátumát. Példa: <code>/(start$|^nyilvanos:ujstart$)/</code>.';
$lang['show_sort']             = 'Indexmenü rendező számának megjelenítése adminisztrátoroknak oldal tetején megjegyzésként';
$lang['themes_url']            = 'JavaScript-témák letöltése erről az URL-címről.';
$lang['be_repo']               = 'Témák letöltésének engedélyezése másoknak az oldalunkról.';
$lang['defaultoptions']        = 'Az \'indexmenu\' beállításainak listája, szóközzel elválasztva. Ezek a beállítások alapértelmezés szerint minden indexmenu-elemre végrehajtódnak, és nem visszavonhatók a bővítmény parancsaival.';
