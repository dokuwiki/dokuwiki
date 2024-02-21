<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * Lithuanian language file
 *
 * @author Donatas Glodenis <dgvirtual@gmail.com>
 * @author audrius.klevas <audrius.klevas@gmail.com>
 * @author Arunas Vaitekunas <aras@fan.lt>
 */
$lang['menu']  = 'Konfigūracijos nustatymai';
$lang['error'] = 'Nustatymai nebuvo atnaujinti dėl neteisingos reikšmės, prašome peržiūrėti pakeitimus ir iš naujo pateikti.
                       <br />Neteisingos reikšmės bus paryškintos raudonu rėmu.';
$lang['updated']  = 'Nustatymai sėkmingai atnaujinti.';
$lang['nochoice'] = '(nėra kitų variantų)';
$lang['locked']   = 'Nustatymų failas negali būti atnaujintas, jei tai neatsitiko atsitiktinai, <br />
                       įsitikinkite, kad vietinių nustatymų failo pavadinimas ir teisės yra teisingos.';
$lang['danger']                        = 'Pavojus: pakeitus šį parametrą gali būti neįmanoma pasiekti jūsų wiki ir konfigūracijos meniu.';
$lang['warning']                       = 'Įspėjimas: pakeitus šį parametrą gali pasireikšti nenumatyta elgsena.';
$lang['security']                      = 'Saugos įspėjimas: pakeitus šį parametrą gali atsirasti saugumo pavojus.';

/* --- Config Setting Headers --- */
$lang['_configuration_manager']        = 'Konfigūracijos tvarkytuvė';
$lang['_header_dokuwiki']              = 'DokuWiki';
$lang['_header_plugin']                = 'Įskiepis';
$lang['_header_template']              = 'Šablonas';
$lang['_header_undefined']             = 'Neapibrėžti nustatymai';

/* --- Config Setting Groups --- */
$lang['_basic']                        = 'Pagrindiniai';
$lang['_display']                      = 'Rodymas';
$lang['_authentication']               = 'Autentifikacija';
$lang['_anti_spam']                    = 'Prieš šlamštą';
$lang['_editing']                      = 'Redagavimas';
$lang['_links']                        = 'Nuorodos';
$lang['_media']                        = 'Failai';
$lang['_notifications']                = 'Pranešimai';
$lang['_syndication']                  = 'Sindikavimas (RSS)';
$lang['_advanced']                     = 'Papildomi';
$lang['_network']                      = 'Tinklas';

/* --- Undefined Setting Messages --- */
$lang['_msg_setting_undefined']        = 'Nėra nustatymų metaduomenų.';
$lang['_msg_setting_no_class']         = 'Nėra nustatymų klasės.';
$lang['_msg_setting_no_known_class']   = 'Nustatymų klasė neprieinama.';
$lang['_msg_setting_no_default']       = 'Nėra numatytosios reikšmės.';

/* -------------------- Config Options --------------------------- */

/* Basic Settings */
$lang['title']                         = 'Wiki pavadinimas, t.y. Jūsų wiki pavadinimas';
$lang['start']                         = 'Puslapio pavadinimas, kurį naudoti kaip pradinį tašką kiekvienoje vardų erdvėje';
$lang['lang']                          = 'Sąsajos kalba';
$lang['template']                      = 'Šablonas, arba wiki dizainas';
$lang['tagline']                       = 'Šūkis (jei šablonas palaiko)';
$lang['sidebar']                       = 'Šoninio stulpelio puslapio pavadinimas (jei šablonas tai palaiko), tuščias laukas išjungia šoninį stulpelį';
$lang['license']                       = 'Licencija, su kuria skelbiamas Jūsų turinys';
$lang['savedir']                       = 'Katalogas duomenų saugojimui';
$lang['basedir']                       = 'Serverio kelio pavadinimas (pvz., <code>/dokuwiki/</code>). Palikite tuščią automatiniam aptikimui.';
$lang['baseurl']                       = 'Serverio URL (pvz., <code>http://www.jususerveris.lt</code>). Palikite tuščią automatiniam aptikimui.';
$lang['cookiedir']                     = 'Slapuko kelio pavadinimas. Palikite tuščią, jei naudojamas baseurl.';
$lang['dmode']                         = 'Katalogo kūrimo režimas';
$lang['fmode']                         = 'Failo kūrimo režimas';
$lang['allowdebug']                    = 'Leisti derinimo ir klaidų pranešimus. <b>Išjunkite, jei nereikia!</b>';

/* Display Settings */
$lang['recent']                        = 'Neseniai atliktų taisymų sąrašo įrašų skaičius puslapyje';
$lang['recent_days']                   = 'Kiek neseniai atliktų pakeitimų laikyti (dienomis)';
$lang['breadcrumbs']                   = '„Sekos“ įrašų skaičius. Norėdami išjungti nustatykite 0.';
$lang['youarehere']                    = 'Naudoti hierarchinį „takelį“ (tai tikriausiai bus pageidautina, jei išjungtas pirmiau nurodytas parametras)';
$lang['fullpath']                      = 'Atskleisti pilną puslapių kelią poraštėje';
$lang['typography']                    = 'Atlikti tipografinius pakeitimus';
$lang['dformat']                       = 'Datos formatas (žr. PHP <a href="http://php.net/strftime">strftime</a> funkciją)';
$lang['signature']                     = 'Ką įterpti naudojant parašo mygtuką redaktoriuje';
$lang['showuseras']                    = 'Ką rodyti nurodant vartotoją, kuris paskutinį kartą redagavo puslapį';
$lang['toptoclevel']                   = 'Viršutinis puslapio turinio lygmuo';
$lang['tocminheads']                   = 'Minimalus antraščių skaičius, kuriam esant puslapio turinys turi būti sukurtas';
$lang['maxtoclevel']                   = 'Didžiausias turinio lygmuo';
$lang['maxseclevel']                   = 'Mažiausias antraštės lygmuo, kuriam įjungiamas sekcijos redagavimas';
$lang['camelcase']                     = 'Naudoti CamelCase nuorodoms';
$lang['deaccent']                      = 'Kaip valyti puslapių pavadinimus';
$lang['useheading']                    = 'Naudoti pirmąją antraštę puslapių pavadinimui';
$lang['sneaky_index']                  = 'Pagal nutylėjimą DokuWiki svetainės žemėlapyje rodo visas vardų erdves. Įjungus šią parinktį bus rodomos tik tos, kurios naudotojas gali skaityti. Tai gali lemti, kad kai kurios aukščiau medyje esančios vardų erdvės, kurias vartotojas gali skaityti, bus jam nerodomos, todėl gali būti, kad sąrašas taps nenaudingas su tam tikromis prieigos valdymo sąrankos nuostatomis.';
$lang['hidepages']                     = 'Slėpti puslapius, atitinkančius šią reguliariąją išraišką, iš paieškos, žemėlapio ir kitų automatiškai sukurtų indeksų';

/* Authentication Settings */
$lang['useacl']                        = 'Naudoti prieigos valdymo sąrašus';
$lang['autopasswd']                    = 'Automatiškai generuoti slaptažodžius';
$lang['authtype']                      = 'Autentifikacijos sprendimas';
$lang['passcrypt']                     = 'Slaptažodžių šifravimo metodas';
$lang['defaultgroup']                  = 'Numatytasis grupė, visi nauji vartotojai bus įtraukti į šią grupę';
$lang['superuser']                     = 'Superuser - grupė, vartotojas arba kableliu atskirtas vartotojų sąrašas user1,@group1,user2 su visomis teisėmis peržiūrėti visus puslapius ir funkcijas, nepaisant ACL nustatymų';
$lang['manager']                       = 'Vadybininkas - grupė, vartotojas arba kableliu atskirtas vartotojų sąrašas user1,@group1,user2 su prieiga prie tam tikrų tvarkymo funkcijų';
$lang['profileconfirm']                = 'Patvirtinti profilio pakeitimus slaptažodžiu';
$lang['rememberme']                    = 'Leisti nuolatinį prisijungimo slapuką (prisimink mane)';
$lang['disableactions']                = 'Išjungti DokuWiki veiksmus';
$lang['disableactions_check']          = 'Tikrinti';
$lang['disableactions_subscription']   = 'Prenumeruoti/atsisakyti prenumeratos';
$lang['disableactions_wikicode']       = 'Peržiūrėti šaltinį/eksportuoti neapdorotą';
$lang['disableactions_profile_delete'] = 'Ištrinti savo paskyrą';
$lang['disableactions_other']          = 'Kiti veiksmai (kableliu atskirti)';
$lang['disableactions_rss']            = 'XML Sinchronizavimas (RSS)';
$lang['auth_security_timeout']         = 'Autentifikacijos saugumo laiko limitas (sekundėmis)';
$lang['securecookie']                  = 'Ar slapukai, nustatyti per HTTPS, turėtų būti siunčiami naršyklėms tik per HTTPS? Išjunkite šią parinktį, jei prisijungimas prie Jūsų wiki apsaugotas SSL, bet naršymas wiki yra atliekamas be SSL apsaugos.';
$lang['samesitecookie']                = 'Naudoti samesite slapuko atributą. Palikite tuščią, kad naršyklė nustatytų samesite politiką.';
$lang['remote']                        = 'Įjungti nuotolinę API sistemą. Tai leidžia kitoms programoms pasiekti wiki per XML-RPC ar kitus mechanizmus.';
$lang['remoteuser']                    = 'Apriboti nuotolinės API prieigą tik prie tam tikrų grupių arba vartotojų, nurodytų čia, kableliu atskirti. Palikite tuščią, jei norite suteikti prieigą visiems.';
$lang['remotecors']                    = 'Įjungti kryžminio kilmės išteklių bendrinimą (CORS) nuotolinėms sąsajoms. Žvaigždutė (*) leis visiems kilmėms. Palikite tuščią, jei norite atmesti CORS.';

/* Anti-Spam Settings */
$lang['usewordblock']                  = 'Blokuoti šiukšlių pagrįstus žodžių sąrašus';
$lang['relnofollow']                   = 'Naudojant rel="ugc nofollow" ant išorinių nuorodų';
$lang['indexdelay']                    = 'Laiko delsa prieš indeksavimą (sekundėmis)';
$lang['mailguard']                     = 'Užtemdyti el. pašto adresus';
$lang['iexssprotect']                  = 'Patikrinti įkeltus failus dėl galimai kenksmingo JavaScript ar HTML kodo';

/* Editing Settings */
$lang['usedraft']                      = 'Automatiškai išsaugoti juodraštį redaguojant';
$lang['locktime']                      = 'Maksimalus užrakto failų amžius (sekundėmis)';
$lang['cachetime']                     = 'Maksimalus podėlio amžius (sekundėmis)';

/* Link settings */
$lang['target____wiki']                = 'Vidinių nuorodų langas taikinys';
$lang['target____interwiki']           = 'Interwiki nuorodų langas taikinys';
$lang['target____extern']              = 'Išorinių nuorodų langas taikinys';
$lang['target____media']               = 'Medijos nuorodų langas taikinys';
$lang['target____windows']             = 'Windows nuorodų langas taikinys';

/* Media Settings */
$lang['mediarevisions']                = 'Įjungti failų revizijų saugojimą?';
$lang['refcheck']                      = 'Patikrinti, ar prieš trinant failą jis vis dar naudojamas';
$lang['gdlib']                         = 'GD Lib versija';
$lang['im_convert']                    = 'Kelias iki ImageMagick konvertavimo įrankio';
$lang['jpg_quality']                   = 'JPG suspaudimo kokybė (0-100)';
$lang['fetchsize']                     = 'Maksimalus dydis (baitais), kurį fetch.php gali atsisiųsti iš išorinių URL, pvz., kad talpintų ir keistų išorinius paveikslėlius.';

/* Notification Settings */
$lang['subscribers']                   = 'Leisti vartotojams prenumeruoti puslapio pakeitimus el. paštu';
$lang['subscribe_time']                = 'Laikas, per kurį siunčiami prenumeratos sąrašai ir santraukos (sekundėmis); tai turėtų būti mažiau nei laikas, nurodytas neseniai_dienuose.';
$lang['notify']                        = 'Visada siųsti pakeitimų pranešimus į šį el. pašto adresą';
$lang['registernotify']                = 'Visada siųsti informaciją apie naujus prisijungusius vartotojus į šį el. pašto adresą';
$lang['mailfrom']                      = 'Siuntėjo el. pašto adresas, skirtas automatiniams laiškams naudoti';
$lang['mailreturnpath']                = 'Gavėjo el. pašto adresas nepristatymo pranešimams';
$lang['mailprefix']                    = 'El. pašto subjekto priesaga, skirta automatiniams laiškams naudoti. Palikite tuščią, jei norite naudoti wiki pavadinimą';
$lang['htmlmail']                      = 'Siųsti gražiau atrodančius, bet didesnius dydžius turinčius HTML daugialypius laiškus. Išjunkite tekstinius laiškus tik tekstui.';
$lang['dontlog']                       = 'Išjungti visų įvykių protokolą. Tai gali padidinti saugumą ir užkrauti mažiau sistemos išteklių, bet taip pat reikės daugiau problemų sprendimo laiko.';
$lang['logretain']                     = 'Kiek dienų laikyti žurnalą.';

/* Syndication Settings */
$lang['sitemap']                       = 'Generuoti Google sitemap tiek kartų (dienomis). 0 išjungti';
$lang['rss_type']                      = 'XML duomenų srauto tipas';
$lang['rss_linkto']                    = 'Nuorodos į XML duomenų srautą';
$lang['rss_content']                   = 'Ką rodyti XML duomenų srauto elementuose?';
$lang['rss_update']                    = 'XML duomenų srauto atnaujinimo intervalas (sek)';
$lang['rss_show_summary']              = 'XML duomenų srauto rodyti santrauką pavadinime';
$lang['rss_show_deleted']              = 'XML duomenų srauto rodyti ištrintus įrašus';
$lang['rss_media']                     = 'Kokios rūšies pakeitimai turėtų būti sąrašuojami XML duomenų sraute?';
$lang['rss_media_o_both']              = 'abi';
$lang['rss_media_o_pages']             = 'puslapiai';
$lang['rss_media_o_media']             = 'media';

/* Advanced Options */
$lang['updatecheck']                   = 'Tikrinti naujinimus ir saugumo pranešimus? DokuWiki turi susisiekti su update.dokuwiki.org šiai funkcijai.';
$lang['userewrite']                    = 'Naudoti gražius URL';
$lang['useslash']                      = 'Naudoti pasvirą brūkšnį kaip tarpiklio skirtuką URL';
$lang['sepchar']                       = 'Puslapio pavadinimo žodžių skirtukas';
$lang['canonical']                     = 'Naudoti visiškai kanoniškus URL';
$lang['fnencode']                      = 'Ne ASCII failų pavadinimų kodavimo metodas.';
$lang['autoplural']                    = 'Patikrinti daugiskaitos formų nuorodose';
$lang['compression']                   = 'Suspaudimo metodas attico failams';
$lang['gzip_output']                   = 'Naudoti gzip Content-Encoding xhtml';
$lang['compress']                      = 'Sutraiškyti CSS ir javascript išvestį';
$lang['cssdatauri']                    = 'Dydis baitais, iki kurio įterpti į CSS failus nuorodą į vaizdą tiesiai į stilių lapą, kad sumažintumėte HTTP užklausos antraštės naštą. <code>400</code> iki <code>600</code> baitų yra gera vertė. Nustatykite <code>0</code> norėdami išjungti.';
$lang['send404']                       = 'Siųsti "HTTP 404/Puslapis nerastas" už neegzistuojančius puslapius';
$lang['broken_iua']                    = 'Ar ignore_user_abort funkcija sugadinta jūsų sistemoje? Tai gali sukelti neveikiantį paieškos indeksą. IIS+PHP/CGI žinoma sugadinta.';
$lang['xsendfile']                     = 'Naudoti X-Sendfile antraštę, leidžianti serveriui pristatyti statinius failus? Jūsų serveris turi palaikyti šią funkciją.';
$lang['renderer_xhtml']                = 'Naudojamas pagrindiniam (xhtml) wiki išvesties modeliui';
$lang['renderer__core']                = '%s (dokuwiki pagrindinė)';
$lang['renderer__plugin']              = '%s (įskiepis)';
$lang['search_nslimit']                = 'Paieškos ribojimas iki dabartinio X tarpiklio. Kai paieška vykdoma iš puslapio gilesniame tarpinyje, pirmi X tarpiniais bus pridėti kaip filtras';
$lang['search_fragment']               = 'Nurodykite numatytąjį fragmento paieškos elgesį';
$lang['search_fragment_o_exact']       = 'tikslus';
$lang['search_fragment_o_starts_with'] = 'prasideda';
$lang['search_fragment_o_ends_with']   = 'baigiasi';
$lang['search_fragment_o_contains']    = 'turi';
$lang['trustedproxy']                  = 'Pasitikite peradresavimo serveriais, atitinkančiais šią reguliariąją išraišką apie tikrąjį kliento IP, kurį jie praneša. Numatytasis atitinka vietinius tinklus. Palikite tuščią, kad nepasitikėtumėte jokiu įgaliotu.';
$lang['_feature_flags']                = 'Funkcijų vėliavos';
$lang['defer_js']                      = 'Atidėti javascript vykdymą po puslapio HTML analizės. Gerina suvokiamą puslapio greitį, bet gali sugadinti nedidelę įskiepių dalį.';
$lang['hidewarnings']                  = 'Nerodyti jokių PHP išvestuose įspėjimų. Tai gali palengvinti perėjimą prie PHP8+. Įspėjimai vis tiek bus užregistruoti klaidų žurnale ir turėtų būti pranešti.';

/* Network Options */
$lang['dnslookups']                    = 'DokuWiki ieškos vartotojų, redaguojančių puslapius, nuotolinių IP adresų vardų. Jei turite lėtą arba neveikiantį DNS serverį arba nenorite šios funkcijos, išjunkite šią parinktį.';

/* jQuery CDN options */
$lang['jquerycdn']                     = 'Ar jQuery ir jQuery UI skriptų failai turi būti įkelti iš CDN? Tai prideda papildomų HTTP užklausų, bet failai gali įsikelti greičiau, ir naudotojai gali juos jau turėti pakeistus.';
$lang['jquerycdn_o_0']                 = 'Nėra CDN, tik vietinis pristatymas';
$lang['jquerycdn_o_jquery']            = 'CDN code.jquery.com';
$lang['jquerycdn_o_cdnjs']             = 'CDN cdnjs.com';

/* Proxy Options */
$lang['proxy____host']                 = 'Įgaliotojo serverio pavadinimas';
$lang['proxy____port']                 = 'Įgaliotojo serverio prievadas';
$lang['proxy____user']                 = 'Įgaliotojo serverio naudotojo vardas';
$lang['proxy____pass']                 = 'Įgaliotojo serverio slaptažodis';
$lang['proxy____ssl']                  = 'Naudoti SSL prisijungimui prie įgaliotojo serverio';
$lang['proxy____except']               = 'Reguliari išraiška, atitinkanti URL, kuriems įgaliotojo serverio reikia praleisti.';

/* License Options */
$lang['license_o_']                    = 'Nepasirinkta';

/* typography options */
$lang['typography_o_0']                = 'nėra';
$lang['typography_o_1']                = 'išskyrus viengubas kabutes';
$lang['typography_o_2']                = 'įskaitant viengubas kabutes (gali ne visada veikti)';

/* userewrite options */
$lang['userewrite_o_0']                = 'nėra';
$lang['userewrite_o_1']                = '.htaccess';
$lang['userewrite_o_2']                = 'DokuWiki vidinis';

/* deaccent options */
$lang['deaccent_o_0']                  = 'išjungta';
$lang['deaccent_o_1']                  = 'pašalinti kirčius';
$lang['deaccent_o_2']                  = 'romanizuoti';

/* gdlib options */
$lang['gdlib_o_0']                     = 'GD Lib nėra prieinamas';
$lang['gdlib_o_1']                     = 'Versija 1.x';
$lang['gdlib_o_2']                     = 'Automatinis aptikimas';

/* rss_type options */
$lang['rss_type_o_rss']                = 'RSS 0.91';
$lang['rss_type_o_rss1']               = 'RSS 1.0';
$lang['rss_type_o_rss2']               = 'RSS 2.0';
$lang['rss_type_o_atom']               = 'Atom 0.3';
$lang['rss_type_o_atom1']              = 'Atom 1.0';

/* rss_content options */
$lang['rss_content_o_abstract']        = 'Santrauka';
$lang['rss_content_o_diff']            = 'Vieningas skirtumas';
$lang['rss_content_o_htmldiff']        = 'HTML formatuotas skirtumo lentelė';
$lang['rss_content_o_html']            = 'Visas HTML puslapio turinys';

/* rss_linkto options */
$lang['rss_linkto_o_diff']             = 'skirtumo rodinys';
$lang['rss_linkto_o_page']             = 'pakeistas puslapis';
$lang['rss_linkto_o_rev']              = 'reikalų sąrašas';
$lang['rss_linkto_o_current']          = 'esamas puslapis';

/* compression options */
$lang['compression_o_0']               = 'nėra';
$lang['compression_o_gz']              = 'gzip';
$lang['compression_o_bz2']             = 'bz2';

/* xsendfile header */
$lang['xsendfile_o_0']                 = 'nenaudoti';
$lang['xsendfile_o_1']                 = 'Originali lighthttpd antraštė (iki 1.5 versijos išleidimo)';
$lang['xsendfile_o_2']                 = 'Standartinė X-Sendfile antraštė';
$lang['xsendfile_o_3']                 = 'Originali Nginx X-Accel-Redirect antraštė';

/* Display user info */
$lang['showuseras_o_loginname']        = 'Prisijungimo vardas';
$lang['showuseras_o_username']         = 'Vartotojo pilnas vardas';
$lang['showuseras_o_username_link']    = 'Vartotojo pilnas vardas kaip tarpinio vartotojo nuoroda';
$lang['showuseras_o_email']            = 'Vartotojo el. pašto adresas (pašto apsaugos požiūriu netinkamas)';
$lang['showuseras_o_email_link']       = 'Vartotojo el. pašto adresas kaip mailto: nuoroda';

/* useheading options */
$lang['useheading_o_0']                = 'Niekada';
$lang['useheading_o_navigation']       = 'Tik navigacija';
$lang['useheading_o_content']          = 'Tik wiki turinys';
$lang['useheading_o_1']                = 'Visada';
$lang['readdircache']                  = 'Maksimalus amžius readdir cache (sek)';
