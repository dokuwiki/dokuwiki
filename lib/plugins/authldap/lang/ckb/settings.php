<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author qezwan <qezwan@gmail.com>
 */
$lang['server']                = 'ڕاژەکاری LDAP ی تۆ. یان ناوی خانەخوێ (<code>ناوخۆیی</code>) یان URLی تەواو شیاو (<code>ldap://server.tld:389</code>)';
$lang['port']                  = 'دەرگای سێرڤەری LDAP ئەگەر هیچ URLێکی تەواو لە سەرەوە نەدرابێت';
$lang['usertree']              = 'لەکوێ ئەژمێرەکانی بەکارهێنەر بدۆزیتەوە. ئێگ <code>ou=People, dc=server, dc=tld</code>';
$lang['grouptree']             = 'لەکوێ گروپەکانی بەکارهێنەر بدۆزیتەوە. ئێگ <code>ou=Group, dc=server, dc=tld</code>';
$lang['userfilter']            = 'فلتەری LDAP بۆ گەڕان بۆ ئەژمێرەکانی بەکارهێنەر. ئێگ <code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>';
$lang['groupfilter']           = 'فلتەری LDAP بۆ گەڕان بۆ گرووپەکان. ئێگ <code>(&amp;(objectClass=posixGroup)(|) gidNumber=%{gid})(memberUID=%{user})))</code>';
$lang['version']               = 'وەشانی پرۆتۆکۆل بۆ بەکارهێنان. لەوانەیە پێویستت بەوە بێت ئەمە ڕێک بخەیت بۆ <code>3</code>';
$lang['starttls']              = 'بەکارهێنانی گرێدانەکانی TLS؟';
$lang['referrals']             = 'ئایا بەدوای دا بەهاوکردەوەکان دەکەون؟';
$lang['deref']                 = 'چۆن نازناوەکان بسڕنەوە؟';
$lang['binddn']                = 'DN ی بەکارهێنەری بەخۆوە ی بەستێنەر ئەگەر بەنادیارە بەینببەی بەش ناکات. ئێگ <code>cn=admin, dc=my, dc=home</code>';
$lang['bindpw']                = 'تێپەڕوشە بەکارهێنەری سەرەوە';
$lang['attributes']            = 'تایبەتمەندیەکان بۆ هێنانەوە لەگەڵ گەڕانی LDAP.';
$lang['userscope']             = 'سنووری بواری گەڕان بۆ گەڕانی بەکارهێنەر';
$lang['groupscope']            = 'سنووری بواری گەڕان بۆ گەڕانی گروپ';
$lang['userkey']               = 'تایبەتمەندی سڕینەوەی ناوی بەکارهێنەر; پێویستە هاوتەربێت لەگەڵ پاڵێوی بەکارهێنەر.';
$lang['groupkey']              = 'ئەندامێتی گرووپ لە هەر تایبەتمەندییەکی بەکارهێنەر (لەجیاتی گروپەستانداردەکانی AD) بۆ وێنە لە گرووپ لە بەشی یان ژمارەی تەلەفۆن';
$lang['modPass']               = 'ئایا دەکرێت تێپەڕوشەی LDAP بگۆڕدرێت لە لایەن دۆکوویکی ؟';
$lang['debug']                 = 'پیشاندانی زانیاری زیاتری ڕاستکردنەوەی هەڵە لەسەر هەڵەکان';
$lang['deref_o_0']             = 'LDAP_DEREF_NEVER';
$lang['deref_o_1']             = 'LDAP_DEREF_SEARCHING';
$lang['deref_o_2']             = 'LDAP_DEREF_FINDING';
$lang['deref_o_3']             = 'LDAP_DEREF_ALWAYS';
$lang['referrals_o_-1']        = 'بەکارهێنانی بنەڕەت';
$lang['referrals_o_0']         = 'بەدوای ئاماژەکان مەبە';
$lang['referrals_o_1']         = 'بە دوای ئاماژەکان بکەوە';
