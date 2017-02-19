<?php
/**
 * welsh language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 * @author     Matthias Schulte <dokuwiki@lupo49.de>
 * @author     Alan Davies <ben.brynsadler@gmail.com>
 */

// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu']       = 'Gosodiadau Ffurwedd';

$lang['error']      = 'Gosodiadau heb eu diweddaru oherwydd gwerth annilys, gwiriwch eich newidiadau ac ailgyflwyno.
                       <br />Caiff y gwerth(oedd) anghywir ei/eu dangos gydag ymyl coch.';
$lang['updated']    = 'Diweddarwyd gosodiadau\'n llwyddiannus.';
$lang['nochoice']   = '(dim dewisiadau eraill ar gael)';
$lang['locked']     = '\'Sdim modd diweddaru\'r ffeil osodiadau, os ydy hyn yn anfwriadol, <br />
                       sicrhewch fod enw\'r ffeil osodiadau a\'r hawliau lleol yn gywir.';

$lang['danger']     = 'Perygl: Gall newid yr opsiwn hwn wneud eich wici a\'r ddewislen ffurfwedd yn anghyraeddadwy.';
$lang['warning']    = 'Rhybudd: Gall newid yr opsiwn hwn achosi ymddygiad anfwriadol.';
$lang['security']   = 'Rhybudd Diogelwch: Gall newid yr opsiwn hwn achosi risg diogelwch.';

/* --- Config Setting Headers --- */
$lang['_configuration_manager'] = 'Rheolwr Ffurfwedd'; //same as heading in intro.txt
$lang['_header_dokuwiki'] = 'DokuWiki';
$lang['_header_plugin'] = 'Ategyn';
$lang['_header_template'] = 'Templed';
$lang['_header_undefined'] = 'Gosodiadau Amhenodol';

/* --- Config Setting Groups --- */
$lang['_basic'] = 'Sylfaenol';
$lang['_display'] = 'Dangos';
$lang['_authentication'] = 'Dilysiad';
$lang['_anti_spam'] = 'Gwrth-Sbam';
$lang['_editing'] = 'Yn Golygu';
$lang['_links'] = 'Dolenni';
$lang['_media'] = 'Cyfrwng';
$lang['_notifications'] = 'Hysbysiad';
$lang['_syndication']   = 'Syndication (RSS)'; //angen newid
$lang['_advanced'] = 'Uwch';
$lang['_network'] = 'Rhwydwaith';

/* --- Undefined Setting Messages --- */
$lang['_msg_setting_undefined'] = 'Dim gosodiad metadata.';
$lang['_msg_setting_no_class'] = 'Dim gosodiad dosbarth.';
$lang['_msg_setting_no_default'] = 'Dim gwerth diofyn.';

/* -------------------- Config Options --------------------------- */

/* Basic Settings */
$lang['title']       = 'Teitl y wici h.y. enw\'ch wici';
$lang['start']       = 'Enw\'r dudalen i\'w defnyddio fel man cychwyn ar gyfer pob namespace'; //namespace
$lang['lang']        = 'Iaith y rhyngwyneb';
$lang['template']    = 'Templed h.y. dyluniad y wici.';
$lang['tagline']     = 'Taglinell (os yw\'r templed yn ei gynnal)';
$lang['sidebar']     = 'Enw tudalen y bar ochr (os yw\'r templed yn ei gynnal), Mae maes gwag yn analluogi\'r bar ochr';
$lang['license']     = 'O dan ba drwydded dylai\'ch cynnwys gael ei ryddhau?';
$lang['savedir']     = 'Ffolder ar gyfer cadw data';
$lang['basedir']     = 'Llwybr y gweinydd (ee. <code>/dokuwiki/</code>). Gadewch yn wag ar gyfer awtoddatgeliad.';
$lang['baseurl']     = 'URL y gweinydd (ee. <code>http://www.yourserver.com</code>). Gadewch yn wag ar gyfer awtoddatgeliad.';
$lang['cookiedir']   = 'Llwybr cwcis. Gadewch yn wag i ddefnyddio \'baseurl\'.';
$lang['dmode']       = 'Modd creu ffolderi';
$lang['fmode']       = 'Modd creu ffeiliau';
$lang['allowdebug']  = 'Caniatáu dadfygio. <b>Analluogwch os nac oes angen hwn!</b>';

/* Display Settings */
$lang['recent']      = 'Nifer y cofnodion y dudalen yn y newidiadau diweddar';
$lang['recent_days'] = 'Sawl newid diweddar i\'w cadw (diwrnodau)';
$lang['breadcrumbs'] = 'Nifer y briwsion "trywydd". Gosodwch i 0 i analluogi.';
$lang['youarehere']  = 'Defnyddiwch briwsion hierarchaidd (byddwch chi yn debygol o angen analluogi\'r opsiwn uchod wedyn)';
$lang['fullpath']    = 'Datgelu llwybr llawn y tudalennau yn y troedyn';
$lang['typography']  = 'Gwnewch amnewidiadau argraffyddol';
$lang['dformat']     = 'Fformat dyddiad (gweler swyddogaeth <a href="http://php.net/strftime">strftime</a> PHP)';
$lang['signature']   = 'Yr hyn i\'w mewnosod gyda\'r botwm llofnod yn y golygydd';
$lang['showuseras']  = 'Yr hyn i\'w harddangos wrth ddangos y defnyddiwr a wnaeth olygu\'r dudalen yn olaf';
$lang['toptoclevel'] = 'Lefel uchaf ar gyfer tabl cynnwys';
$lang['tocminheads'] = 'Isafswm y penawdau sy\'n penderfynu os ydy\'r tabl cynnwys yn cael ei adeiladu';
$lang['maxtoclevel'] = 'Lefel uchaf ar gyfer y tabl cynnwys';
$lang['maxseclevel'] = 'Lefel uchaf adran olygu';
$lang['camelcase']   = 'Defnyddio CamelCase ar gyfer dolenni';
$lang['deaccent']    = 'Sut i lanhau enwau tudalennau';
$lang['useheading']  = 'Defnyddio\'r pennawd cyntaf ar gyfer enwau tudalennau';
$lang['sneaky_index'] = 'Yn ddiofyn, bydd DokuWiki yn dangos pob namespace yn y map safle. Bydd galluogi yr opsiwn hwn yn cuddio\'r rheiny lle \'sdim hawliau darllen gan y defnyddiwr. Gall hwn achosi cuddio subnamespaces cyraeddadwy a fydd yn gallu peri\'r indecs i beidio â gweithio gyda gosodiadau ACL penodol.'; //namespace
$lang['hidepages']   = 'Cuddio tudalennau sy\'n cydweddu gyda\'r mynegiad rheolaidd o\'r chwiliad, y map safle ac indecsau awtomatig eraill';

/* Authentication Settings */
$lang['useacl']      = 'Defnyddio rhestrau rheoli mynediad';
$lang['autopasswd']  = 'Awtogeneradu cyfrineiriau';
$lang['authtype']    = 'Ôl-brosesydd dilysu';
$lang['passcrypt']   = 'Dull amgryptio cyfrineiriau';
$lang['defaultgroup']= 'Grŵp diofyn, caiff pob defnyddiwr newydd ei osod yn y grŵp hwn';
$lang['superuser']   = 'Uwchddefnyddiwr - grŵp, defnyddiwr neu restr gwahanwyd gan goma defnyddiwr1,@group1,defnyddiwr2 gyda mynediad llawn i bob tudalen beth bynnag y gosodiadau ACL';
$lang['manager']     = 'Rheolwr - grŵp, defnyddiwr neu restr gwahanwyd gan goma defnyddiwr1,@group1,defnyddiwr2 gyda mynediad i swyddogaethau rheoli penodol';
$lang['profileconfirm'] = 'Cadrnhau newidiadau proffil gyda chyfrinair';
$lang['rememberme'] = 'Caniatáu cwcis mewngofnodi parhaol (cofio fi)';
$lang['disableactions'] = 'Analluogi gweithredoedd DokuWiki';
$lang['disableactions_check'] = 'Gwirio';
$lang['disableactions_subscription'] = 'Tanysgrifio/Dad-tanysgrifio';
$lang['disableactions_wikicode'] = 'Dangos ffynhonnell/Allforio Crai';
$lang['disableactions_profile_delete'] = 'Dileu Cyfrif Eu Hunain';
$lang['disableactions_other'] = 'Gweithredoedd eraill (gwahanu gan goma)';
$lang['disableactions_rss'] = 'XML Syndication (RSS)'; //angen newid hwn
$lang['auth_security_timeout'] = 'Terfyn Amser Diogelwch Dilysiad (eiliadau)';
$lang['securecookie'] = 'A ddylai cwcis sydd wedi cael eu gosod gan HTTPS gael eu hanfon trwy HTTPS yn unig gan y porwr? Analluogwch yr opsiwn hwn dim ond pan fydd yr unig mewngofnodiad i\'ch wici wedi\'i ddiogelu gydag SSL ond mae pori\'r wici yn cael ei wneud heb ddiogelu.';
$lang['remote']      = 'Galluogi\'r system API pell. Mae hwn yn galluogi apps eraill i gael mynediad i\'r wici trwy XML-RPC neu fecanweithiau eraill.';
$lang['remoteuser']  = 'Cyfyngu mynediad API pell i grwpiau neu ddefnydwyr wedi\'u gwahanu gan goma yma. Gadewch yn wag i roi mynediad i bawb.';

/* Anti-Spam Settings */
$lang['usewordblock']= 'Blocio sbam wedi selio ar restr eiriau';
$lang['relnofollow'] = 'Defnyddio rel="nofollow" ar ddolenni allanol';
$lang['indexdelay']  = 'Oediad cyn indecsio (eil)';
$lang['mailguard']   = 'Tywyllu cyfeiriadau ebost';
$lang['iexssprotect']= 'Gwirio ffeiliau a lanlwythwyd am JavaScript neu god HTML sydd efallai\'n faleisis';

/* Editing Settings */
$lang['usedraft']    = 'Cadw drafft yn awtomatig wrth olygu';
$lang['htmlok']      = 'Caniatáu HTML wedi\'i fewnosod';
$lang['phpok']       = 'Caniatáu PHP wedi\'i fewnosod';
$lang['locktime']    = 'Oed mwyaf ar gyfer cloi ffeiliau (eil)';
$lang['cachetime']   = 'Oed mwyaf ar gyfer y storfa (eil)';

/* Link settings */
$lang['target____wiki']      = 'Ffenestr darged ar gyfer dolenni mewnol';
$lang['target____interwiki'] = 'Ffenestr darged ar gyfer dolenni interwiki';
$lang['target____extern']    = 'Ffenestr darged ar gyfer dolenni allanol';
$lang['target____media']     = 'Ffenestr darged ar gyfer dolenni cyfrwng';
$lang['target____windows']   = 'Ffenestr darged ar gyfer dolenni ffenestri';

/* Media Settings */
$lang['mediarevisions'] = 'Galluogi Mediarevisions?';
$lang['refcheck']    = 'Gwirio os ydy ffeil gyfrwng yn dal yn cael ei defnydio cyn ei dileu hi';
$lang['gdlib']       = 'Fersiwn GD Lib';
$lang['im_convert']  = 'Llwybr i declyn trosi ImageMagick';
$lang['jpg_quality'] = 'Ansawdd cywasgu JPG (0-100)';
$lang['fetchsize']   = 'Uchafswm maint (beit) gall fetch.php lawlwytho o URL allanol, ee. i storio ac ailfeintio delweddau allanol.';

/* Notification Settings */
$lang['subscribers'] = 'Caniatáu defnyddwyr i danysgrifio i newidiadau tudalen gan ebost';
$lang['subscribe_time'] = 'Yr amser cyn caiff rhestrau tanysgrifio a chrynoadau eu hanfon (eil); Dylai hwn fod yn llai na\'r amser wedi\'i gosod mewn recent_days.';
$lang['notify']      = 'Wastad anfon hysbysiadau newidiadau i\'r cyfeiriad ebost hwn';
$lang['registernotify'] = 'Wastad anfon gwybodaeth ar ddefnyddwyr newydd gofrestru i\'r cyfeiriad ebost hwn';
$lang['mailfrom']    = 'Cyfeiriad anfon ebyst i\'w ddefnyddio ar gyfer pyst awtomatig';
$lang['mailprefix']  = 'Rhagddodiad testun ebyst i\'w ddefnyddio ar gyfer pyst awtomatig. Gadewch yn wag i ddefnyddio teitl y wici';
$lang['htmlmail']    = 'Anfonwch ebyst aml-ddarn HTML sydd yn edrych yn well, ond sy\'n fwy mewn maint. Analluogwch ar gyfer pyst testun plaen yn unig.';

/* Syndication Settings */
$lang['sitemap']     = 'Generadu map safle Google mor aml â hyn (mewn diwrnodau). 0 i anallogi';
$lang['rss_type']    = 'Math y ffrwd XML';
$lang['rss_linkto']  = 'Ffrwd XML yn cysylltu â';
$lang['rss_content'] = 'Beth i\'w ddangos mewn eitemau\'r ffrwd XML?';
$lang['rss_update']  = 'Cyfnod diwedaru ffrwd XML (eil)';
$lang['rss_show_summary'] = 'Dangos crynodeb mewn teitl y ffrwd XML';
$lang['rss_media']   = 'Pa fath newidiadau a ddylai cael eu rhestru yn y ffrwd XML??';

/* Advanced Options */
$lang['updatecheck'] = 'Gwirio am ddiweddariadau a rhybuddion diogelwch? Mae\'n rhaid i DokuWiki gysylltu ag update.dokuwiki.org ar gyfer y nodwedd hon.';
$lang['userewrite']  = 'Defnyddio URLs pert';
$lang['useslash']    = 'Defnyddio slaes fel gwahanydd namespace mewn URL';
$lang['sepchar']     = 'Gwanahydd geiriau mewn enw tudalennau';
$lang['canonical']   = 'Defnyddio URLs canonaidd llawn';
$lang['fnencode']    = 'Dull amgodio enw ffeiliau \'non-ASCII\'.';
$lang['autoplural']  = 'Gwirio am ffurfiau lluosog mewn dolenni';
$lang['compression'] = 'Dull cywasgu ar gyfer ffeiliau llofft (hen adolygiadau)';
$lang['gzip_output'] = 'Defnyddio gzip Content-Encoding ar gyfer xhtml'; //pwy a wyr
$lang['compress']    = 'Cywasgu allbwn CSS a javascript';
$lang['cssdatauri']  = 'Uchafswm maint mewn beitiau ar gyfer delweddau i\'w cyfeirio atynt mewn ffeiliau CSS a ddylai cael eu mewnosod i\'r ddalen arddull i leihau gorbenion pennyn cais HTTP. Mae <code>400</code> i <code>600</code> beit yn werth da. Gosodwch i <code>0</code> i\'w analluogi.';
$lang['send404']     = 'Anfon "HTTP 404/Page Not Found" ar gyfer tudalennau sy ddim yn bodoli';
$lang['broken_iua']  = 'Ydy\'r swyddogaeth ignore_user_abort wedi torri ar eich system? Gall hwn achosi\'r indecs chwilio i beidio â gweithio. Rydym yn gwybod bod IIS+PHP/CGI wedi torri. Gweler <a href="http://bugs.dokuwiki.org/?do=details&amp;task_id=852">Bug 852</a> am wybodaeth bellach.';
$lang['xsendfile']   = 'Defnyddio\'r pennyn X-Sendfile i ganiatáu\'r gweinydd gwe i ddanfon ffeiliau statig? Mae\'n rhaid bod eich gweinydd gwe yn caniatáu hyn.';
$lang['renderer_xhtml']   = 'Cyflwynydd i ddefnyddio ar gyfer prif allbwn (xhtml) y wici';
$lang['renderer__core']   = '%s (craidd dokuwiki)';
$lang['renderer__plugin'] = '%s (ategyn)';

/* Network Options */
$lang['dnslookups'] = 'Bydd DokuWiki yn edrych i fyny enwau gwesteiwyr ar gyfer cyfeiriadau IP pell y defnyddwyr hynny sy\'n golygu tudalennau. Os oes gweinydd DNS sy\'n araf neu sy ddim yn gweithio \'da chi neu \'dych chi ddim am ddefnyddio\'r nodwedd hon, analluogwch yr opsiwn hwn.';

/* Proxy Options */
$lang['proxy____host']    = 'Enw\'r gweinydd procsi';
$lang['proxy____port']    = 'Porth procsi';
$lang['proxy____user']    = 'Defnyddair procsi';
$lang['proxy____pass']    = 'Cyfrinair procsi';
$lang['proxy____ssl']     = 'Defnyddio SSL i gysylltu â\'r procsi';
$lang['proxy____except']  = 'Mynegiad rheolaidd i gydweddu URL ar gyfer y procsi a ddylai cael eu hanwybyddu.';

/* Safemode Hack */
$lang['safemodehack'] = 'Galluogi safemode hack';
$lang['ftp____host'] = 'Gweinydd FTP safemode hack';
$lang['ftp____port'] = 'Porth FTP safemode hack';
$lang['ftp____user'] = 'Defnyddair FTP safemode hack';
$lang['ftp____pass'] = 'Cyfrinair FTP safemode hack';
$lang['ftp____root'] = 'Gwraiddffolder FTP safemode hack';

/* License Options */
$lang['license_o_'] = 'Dim wedi\'i ddewis';

/* typography options */
$lang['typography_o_0'] = 'dim';
$lang['typography_o_1'] = 'eithrio dyfynodau sengl';
$lang['typography_o_2'] = 'cynnwys dyfynodau sengl (efallai ddim yn gweithio pob tro)';

/* userewrite options */
$lang['userewrite_o_0'] = 'dim';
$lang['userewrite_o_1'] = '.htaccess';
$lang['userewrite_o_2'] = 'DokuWiki mewnol';

/* deaccent options */
$lang['deaccent_o_0'] = 'bant';
$lang['deaccent_o_1'] = 'tynnu acenion';
$lang['deaccent_o_2'] = 'rhufeinio';

/* gdlib options */
$lang['gdlib_o_0'] = 'GD Lib ddim ar gael';
$lang['gdlib_o_1'] = 'Fersiwn 1.x';
$lang['gdlib_o_2'] = 'Awtoddatgeliad';

/* rss_type options */
$lang['rss_type_o_rss']   = 'RSS 0.91';
$lang['rss_type_o_rss1']  = 'RSS 1.0';
$lang['rss_type_o_rss2']  = 'RSS 2.0';
$lang['rss_type_o_atom']  = 'Atom 0.3';
$lang['rss_type_o_atom1'] = 'Atom 1.0';

/* rss_content options */
$lang['rss_content_o_abstract'] = 'Crynodeb';
$lang['rss_content_o_diff']     = 'Gwahan. Unedig';
$lang['rss_content_o_htmldiff'] = 'Gwahaniaethau ar ffurf tabl HTML';
$lang['rss_content_o_html']     = 'Cynnwys tudalen HTML llawn';

/* rss_linkto options */
$lang['rss_linkto_o_diff']    = 'golwg gwahaniaethau';
$lang['rss_linkto_o_page']    = 'y dudalen a adolygwyd';
$lang['rss_linkto_o_rev']     = 'rhestr adolygiadau';
$lang['rss_linkto_o_current'] = 'y dudalen gyfredol';

/* compression options */
$lang['compression_o_0']   = 'dim';
$lang['compression_o_gz']  = 'gzip';
$lang['compression_o_bz2'] = 'bz2';

/* xsendfile header */
$lang['xsendfile_o_0'] = "peidio â defnyddio";
$lang['xsendfile_o_1'] = 'Pennyn perchnogol lighttpd (cyn rhyddhad 1.5)';
$lang['xsendfile_o_2'] = 'Pennyn safonol X-Sendfile';
$lang['xsendfile_o_3'] = 'Pennyn perchnogol Nginx X-Accel-Redirect';

/* Display user info */
$lang['showuseras_o_loginname']     = 'Enw mewngofnodi';
$lang['showuseras_o_username']      = "Enw llawn y defnyddiwr";
$lang['showuseras_o_username_link'] = "Enw llawn y defnyddiwr fel dolen defnyddiwr interwiki";
$lang['showuseras_o_email']         = "Cyfeiriad e-bost y defnyddiwr (tywyllu yn ôl gosodiad mailguard)";
$lang['showuseras_o_email_link']    = "Cyfeiriad e-bost y defnyddiwr fel dolen mailto:";

/* useheading options */
$lang['useheading_o_0'] = 'Byth';
$lang['useheading_o_navigation'] = 'Llywio yn Unig';
$lang['useheading_o_content'] = 'Cynnwys Wici yn Unig';
$lang['useheading_o_1'] = 'Wastad';

$lang['readdircache'] = 'Uchafswm amser ar gyfer storfa readdir (eil)';
