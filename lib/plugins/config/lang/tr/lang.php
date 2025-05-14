<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Ekin <ata.ekin@windowslive.com>
 * @author Eren <bosshyapma@protonmail.com>
 * @author Hakan <hakandursun2009@gmail.com>
 * @author mahir <mahirtakak@gmail.com>
 * @author Aydın Coşkuner <aydinweb@gmail.com>
 * @author Cihan Kahveci <kahvecicihan@gmail.com>
 * @author Yavuz Selim <yavuzselim@gmail.com>
 * @author Caleb Maclennan <caleb@alerque.com>
 * @author farukerdemoncel <farukerdemoncel@gmail.com>
 * @author Mete Cuma <mcumax@gmail.com>
 */
$lang['menu']                  = 'Site Ayarları';
$lang['error']                 = 'Ayarlar yanlış bir değer girildiği için güncellenemedi. Lütfen değişikliklerinizi gözden geçirin ve tekrar gönderin.
<br />Yanlış değer(ler) kırmızı çerçeve içinde gösterilecektir.';
$lang['updated']               = 'Ayarlar başarıyla güncellendi.';
$lang['nochoice']              = '(başka seçim bulunmamaktadır)';
$lang['locked']                = 'Ayar dosyası güncellenemedi. <br />
dosya adı ve yetkilerininin doğru olduğuna emin olun.';
$lang['danger']                = 'Tehlike: Bu özelliği değiştirirseniz, wiki\'nize ve konfigürasyon menunüze ulaşamayabilirsiniz.';
$lang['warning']               = 'Uyarı: Bu özelliği değiştirmek istenmeyen davranışa sebep olabilir.';
$lang['security']              = 'Güvenlik Uyarısı: Bu özelliği değiştirmek güvenlik riski çıkartabilir.';
$lang['_configuration_manager'] = 'Site Ayarları Yönetimi';
$lang['_header_dokuwiki']      = 'DokuWiki Ayarları';
$lang['_header_plugin']        = 'Eklenti Ayarları';
$lang['_header_template']      = 'Şablon (Template) Ayarları';
$lang['_header_undefined']     = 'Tanımsız Ayarlar';
$lang['_basic']                = 'Ana Ayarlar';
$lang['_display']              = 'Gösterim Ayarları';
$lang['_authentication']       = 'Onaylama Ayarları';
$lang['_anti_spam']            = 'Spam Engelleme Ayarları';
$lang['_editing']              = 'Sayfa Yazımı Ayarları';
$lang['_links']                = 'Bağlantı Ayarları';
$lang['_media']                = 'Medya Ayarları';
$lang['_notifications']        = 'Bildirim';
$lang['_advanced']             = 'Gelişmiş Ayarlar';
$lang['_network']              = 'Ağ Ayarları';
$lang['_msg_setting_undefined'] = 'Ayar üstverisi yok.';
$lang['_msg_setting_no_class'] = 'Ayar sınıfı yok.';
$lang['_msg_setting_no_default'] = 'Varsayılan değer yok.';
$lang['title']                 = 'Wiki başlığı';
$lang['start']                 = 'Ana sayfa adı';
$lang['lang']                  = 'Dil';
$lang['template']              = 'Şablon (Template)';
$lang['license']               = 'İçeriğinizi hangi lisans altında yayınlansın?';
$lang['savedir']               = 'Verileri kaydetmek için kullanılacak klasör';
$lang['basedir']               = 'Kök dizin';
$lang['baseurl']               = 'Kök URL';
$lang['dmode']                 = 'Klasör oluşturma yetkisi';
$lang['fmode']                 = 'Dosya oluşturma yetkisi';
$lang['allowdebug']            = 'Yanlış ayıklamasına izin ver <b>lazım değilse etkisiz kıl!</b>';
$lang['recent']                = 'En son değiştirilenler';
$lang['breadcrumbs']           = 'Ekmek kırıntıların sayısı';
$lang['youarehere']            = 'hiyerarşik ekmek kırıntıları';
$lang['fullpath']              = 'sayfaların tüm patikasını (full path) göster';
$lang['typography']            = 'Tipografik değiştirmeleri yap';
$lang['dformat']               = 'Tarih biçimi (PHP\'nin <a href="http://php.net/strftime">strftime</a> fonksiyonuna bakın)';
$lang['signature']             = 'İmza';
$lang['showuseras']            = 'Bir sayfayı en son düzenleyen kullanıcıya ne gösterilsin';
$lang['toptoclevel']           = 'İçindekiler için en üst seviye';
$lang['tocminheads']           = 'İçindekilerin oluşturulması için gereken (en az) başlık sayısı';
$lang['maxtoclevel']           = 'İçindekiler için en fazla seviye';
$lang['maxseclevel']           = 'Bölümün azami düzenleme düzeyi';
$lang['camelcase']             = 'Linkler için CamelCase kullan';
$lang['deaccent']              = 'Sayfa adlarınız temizle';
$lang['useheading']            = 'Sayfa isimleri için ilk başlığı kullan';
$lang['useacl']                = 'Erişim kontrol listesini kullan';
$lang['autopasswd']            = 'Parolaları otamatikmen üret';
$lang['authtype']              = 'Kimlik denetleme arka uç';
$lang['passcrypt']             = 'Parola şifreleme metodu';
$lang['defaultgroup']          = 'Varsayılan grup';
$lang['disableactions']        = 'DokuWiki eylemlerini etkisiz kıl';
$lang['disableactions_check']  = 'Kontrol et';
$lang['disableactions_subscription'] = 'Abone ol/Abonelikten vazgeç';
$lang['usewordblock']          = 'Wordlistesine göre spam engelle';
$lang['relnofollow']           = 'Dışsal linkler rel="nofollow" kullan';
$lang['indexdelay']            = 'Indekslemeden evvel zaman gecikmesi (saniye)';
$lang['mailguard']             = 'Email adreslerini karart';
$lang['iexssprotect']          = 'Yüklenmiş dosyaları muhtemel kötu niyetli JavaScript veya HTML koduna kontrol et';
$lang['refcheck']              = 'Araç kaynak denetimi';
$lang['gdlib']                 = 'GD Lib sürümü';
$lang['jpg_quality']           = 'JPG sıkıştırma kalitesi [0-100]';
$lang['mailfrom']              = 'Otomatik e-postalar için kullanılacak e-posta adresi';
$lang['sitemap']               = 'Google site haritası oluştur (gün)';
$lang['rss_content']           = 'XML beslemesinde ne gösterilsin?';
$lang['rss_update']            = 'XML beslemesini güncelleme aralığı';
$lang['rss_show_summary']      = 'XML beslemesinde özeti başlıkta göster';
$lang['canonical']             = 'Tamolarak kurallara uygun URL\'leri kullan';
$lang['renderer__core']        = '%s (dokuwiki çekirdeği)';
$lang['renderer__plugin']      = '%s (eklenti)';
$lang['proxy____host']         = 'Proxy sunucu adı';
$lang['proxy____user']         = 'Proxy kullanıcı adı';
$lang['proxy____pass']         = 'Proxy şifresi';
$lang['proxy____ssl']          = 'Proxy ile bağlanırken ssl kullan';
$lang['license_o_']            = 'Seçilmedi';
$lang['typography_o_0']        = 'Yok';
$lang['userewrite_o_0']        = 'hiçbiri';
$lang['userewrite_o_1']        = '.htaccess';
$lang['userewrite_o_2']        = 'DokuWiki dahili';
$lang['deaccent_o_0']          = 'Kapalı';
$lang['deaccent_o_1']          = 'aksan işaretlerini kaldır';
$lang['deaccent_o_2']          = 'roman harfleri kullan';
$lang['gdlib_o_0']             = 'GD Lib mevcut değil';
$lang['gdlib_o_1']             = 'Versiyon 1.x';
$lang['gdlib_o_2']             = 'Otomatik tesbit';
$lang['rss_type_o_rss']        = 'RSS 0.91';
$lang['rss_type_o_rss1']       = 'RSS 1.0';
$lang['rss_type_o_rss2']       = 'RSS 2.0';
$lang['rss_type_o_atom']       = 'Atom 0.3';
$lang['rss_type_o_atom1']      = 'Atom 1.0';
$lang['rss_content_o_abstract'] = 'Soyut';
$lang['rss_content_o_diff']    = 'Birleştirilmiş Diff';
$lang['rss_content_o_htmldiff'] = 'HTML biçimlendirilmiş diff tablosu';
$lang['rss_content_o_html']    = 'Tüm HTML sayfa içeriği';
$lang['rss_linkto_o_diff']     = 'görünümü değiştir';
$lang['rss_linkto_o_page']     = 'gözden geçirilmiş sayfa';
$lang['rss_linkto_o_rev']      = 'sürümlerin listesi';
$lang['rss_linkto_o_current']  = 'Șu anki sayfa';
$lang['compression_o_0']       = 'hiçbiri';
$lang['compression_o_gz']      = 'gzip';
$lang['compression_o_bz2']     = 'bz2';
$lang['xsendfile_o_0']         = 'kullanma';
$lang['showuseras_o_loginname'] = 'Kullanıcı adı';
$lang['showuseras_o_username'] = 'Kullanıcının tam adı';
$lang['showuseras_o_email']    = 'Kullanıcının mail adresi (mailguard ayarlarına göre karartılıyor)';
$lang['showuseras_o_email_link'] = 'Kullanıcının mail adresi mailto: linki şeklinde';
$lang['useheading_o_0']        = 'Hiçbir zaman';
$lang['useheading_o_navigation'] = 'Sadece Navigasyon';
$lang['useheading_o_content']  = 'Sadece Wiki içeriği';
$lang['useheading_o_1']        = 'Her zaman';
