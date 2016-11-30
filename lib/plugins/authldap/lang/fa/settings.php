<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Mohammad Sadegh <msdn2013@gmail.com>
 * @author Omid Hezaveh <hezpublic@gmail.com>
 * @author Mohmmad Razavi <sepent@gmail.com>
 * @author Masoud Sadrnezhaad <masoud@sadrnezhaad.ir>
 * @author sam01 <m.sajad079@gmail.com>
 */
$lang['server']                = 'سرور LDAP شما. چه به صورت ';
$lang['port']                  = 'درگاه سرور LDAP اگر که URL کامل در بالا نوشته نشده';
$lang['usertree']              = 'محل حساب‌های کاربری. برای مثال <code>ou=People, dc=server, dc=tld</code>';
$lang['grouptree']             = 'محل گروه‌های کاربری. برای مثال <code>ou=Group, dc=server, dc=tld</code>';
$lang['userfilter']            = 'فیتلرهای LDAP برای جستجوی حساب‌های کاربری. برای مثال <code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>';
$lang['groupfilter']           = 'فیلتر LDAP برای جستجوی گروه‌ها. برای مثال <code>(&amp;(objectClass=posixGroup)(|(gidNumber=%{gid})(memberUID=%{user})))</code>';
$lang['version']               = 'نسخهٔ پروتوکل برای استفاده. احتمالا این را باید <code>3</code> وارد کنید.';
$lang['starttls']              = 'از تی‌ال‌اس (TLS) استفاده می‌کنید؟';
$lang['referrals']             = 'آیا ارجاعات باید دنبال شوند؟';
$lang['deref']                 = 'نام‌های مستعار چطور ارجاع یابی شوند؟';
$lang['binddn']                = ' DN برای کاربر اتصال اگر اتصال ناشناخته کافی نیست. مثال
<code>cn=admin, dc=my, dc=home</code>';
$lang['bindpw']                = 'رمزعبور کاربر بالا';
$lang['userscope']             = 'محدود کردن محدودهٔ جستجو به جستجوی کاربر';
$lang['groupscope']            = 'محدود کردن محدودهٔ جستجو به جستجوی گروه';
$lang['userkey']               = 'صفتی که نشان‌دهندهٔ نام کاربر است؛ باید با userfilter نامتناقض باشد.';
$lang['groupkey']              = 'عضویت در گروه برمبنای هر کدام از صفات کاربر (به جای گروه‌های استاندارد AD) برای مثال گروه برمبنای دپارتمان یا شماره تلفن';
$lang['modPass']               = 'آیا پسورد LDAP می‌تواند توسط داکو ویکی تغییر کند؟';
$lang['debug']                 = 'نمایش اطلاعات بیشتر برای خطایابی در ارورها';
$lang['deref_o_0']             = 'LDAP_DEREF_NEVER';
$lang['deref_o_1']             = 'LDAP_DEREF_SEARCHING';
$lang['deref_o_2']             = 'LDAP_DEREF_FINDING';
$lang['deref_o_3']             = 'LDAP_DEREF_ALWAYS';
$lang['referrals_o_-1']        = 'استفاده از پیشفرض';
$lang['referrals_o_0']         = 'ارجاعات را دنبال نکن';
$lang['referrals_o_1']         = 'ارجاعات را دنبال کن';
