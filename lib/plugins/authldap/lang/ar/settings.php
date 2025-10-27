<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author talal <ksa76@hotmail.com>
 * @author alhajr <alhajr300@gmail.com>
 */
$lang['server']                = 'خادم LDAP. إما اسم المضيف  (<code>localhost</code>) أو عنوان URL مؤهل بالكامل (<code>ldap://server.tld:389</code>)';
$lang['port']                  = 'LDAP المنفذ الملقم إذا لم يعط أي عنوان URL كامل أعلاه';
$lang['usertree']              = 'أين يمكن العثور على حسابات المستخدمين. مثل. <code>ou=People, dc=server, dc=tld</code>';
$lang['grouptree']             = 'أين يمكن العثور على مجموعات المستخدمين. مثل. <code>ou=Group, dc=server, dc=tld</code>';
$lang['userfilter']            = 'فلتر LDAP للبحث عن حسابات المستخدمين. مثل. <<code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>';
$lang['groupfilter']           = 'فلتر LDAP للبحث عن المجموعات. مثل. <code>(&amp;(objectClass=posixGroup)(|(gidNumber=%{gid})(memberUID=%{user})))</code>';
$lang['version']               = 'إصدار نسخة البروتوكول الستخدامه. قد تحتاج لتعيين هذه القيمة إلى <code>3</code>';
$lang['starttls']              = 'استخدام اتصالات TLS؟';
$lang['referrals']             = 'يتبع الإحالات؟';
$lang['deref']                 = 'كيفية إلغاء مرجعية الأسماء المستعارة؟';
$lang['binddn']                = 'الاسم المميز لمستخدم الربط الاختياري إذا لم يكن الربط المجهول كافيا. مثل. < الرمز> cn = admin ، dc = my ، dc = home< / code>	';
$lang['bindpw']                = 'كلمة مرور المستخدم أعلاه';
$lang['attributes']            = 'السمات المطلوب استردادها باستخدام بحث LDAP.	';
$lang['userscope']             = 'تقييد نطاق البحث لبحث المستخدم	';
$lang['groupscope']            = 'تقييد نطاق البحث للبحث الجماعي	';
$lang['userkey']               = 'السمة التي تشير إلى اسم المستخدم ؛ يجب أن تكون متسقة مع فلتر المستخدم.	';
$lang['groupkey']              = 'عضوية المجموعة من أي سمة مستخدم (بدلا من مجموعات AD القياسية) على سبيل المثال مجموعة من القسم أو رقم الهاتف	';
$lang['modPass']               = 'هل يمكن تغيير كلمة مرور LDAP عبر دوكوويكي؟	';
$lang['debug']                 = 'عرض معلومات تصحيح الأخطاء الإضافية حول الأخطاء	';
$lang['deref_o_0']             = 'LDAP_DEREF_NEVER	';
$lang['deref_o_1']             = 'LDAP_DEREF_SEARCHING	';
$lang['deref_o_2']             = 'LDAP_DEREF_FINDING	';
$lang['deref_o_3']             = 'LDAP_DEREF_ALWAYS	';
$lang['referrals_o_-1']        = 'الافتراضي';
$lang['referrals_o_0']         = 'لا تتبع الإحالات	';
$lang['referrals_o_1']         = 'متابعة الإحالات	';
