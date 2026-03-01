<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author talal <ksa76@hotmail.com>
 * @author Khalid <khalid.aljahil@gmail.com>
 * @author alhajr <alhajr300@gmail.com>
 */
$lang['account_suffix']        = 'لاحقة الحساب الخاص بك. على سبيل المثال. <code>@my.domain.org</code>';
$lang['base_dn']               = 'DN الأساسي الخاص بك. على سبيل المثال.<code>DC=my,DC=domain,DC=org</code>';
$lang['domain_controllers']    = 'قائمة مفصولة بفواصل من وحدات التحكم بالمجال. على سبيل المثال. <code>srv1.domain.org,srv2.domain.org</code>';
$lang['admin_username']        = 'مستخدم Active Directory متميز لديه حق الوصول إلى جميع بيانات المستخدم الآخر. اختياري ، ولكنه مطلوب لإجراءات معينة مثل إرسال رسائل الاشتراك.';
$lang['admin_password']        = 'كلمة المرور للمستخدم أعلاه.';
$lang['sso']                   = 'استخدام Kerberos أم NTLM لتسجيل الدخول الموحد؟';
$lang['sso_charset']           = 'ستقوم مجموعة الأحرف الخاصة بك بتمرير اسم مستخدم Kerberos أو NTLM فيها. فارغة ل UTF-8 أو اللاتينية -1. يتطلب ملحق iconv.';
$lang['real_primarygroup']     = 'ينبغي أن تحل المجموعة الأساسية الحقيقية بدلاً من افتراض "Domain Users" (أبطأ).';
$lang['use_ssl']               = 'استخدام الاتصال المشفر (SSL)؟ في حال استخدامه الرجاء عدم تفعيل (TLS) أسفله.';
$lang['use_tls']               = 'هل تستخدم اتصال TLS؟ إذا تم استخدامها ، فلا تقم بتمكين SSL أعلاه.';
$lang['debug']                 = 'عرض إخراج تصحيح الأخطاء الإضافية على الأخطاء؟	';
$lang['expirywarn']            = 'عدد الأيام المقدمة لتحذير المستخدم حول كلمة مرور منتهية الصلاحية. (0) للتعطيل.';
$lang['additional']            = 'قائمة مفصولة بفواصل لسمات AD الإضافية لجلبها من بيانات المستخدم. تستخدم من قبل بعض الإضافات.';
$lang['update_name']           = 'هل تسمح للمستخدمين بتحديث اسم عرض الإعلانات الخاص بهم؟';
$lang['update_mail']           = 'السماح للمستخدمين بتحديث عناوين بريدهم الإلكتروني؟';
$lang['update_pass']           = 'هل تسمح للمستخدمين بتحديث كلمة المرور الخاصة بهم؟ يتطلب SSL أو TLS أعلاه.';
$lang['recursive_groups']      = 'حل المجموعات المتداخلة لأعضائها المعنيين (أبطأ).';
