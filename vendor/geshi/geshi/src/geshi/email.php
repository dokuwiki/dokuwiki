<?php
/*************************************************************************************
 * email.php
 * ---------------
 * Author: Benny Baumann (BenBE@geshi.org)
 * Copyright: (c) 2008 Benny Baumann (http://qbnz.com/highlighter/)
 * Release Version: 1.0.9.1
 * Date Started: 2008/10/19
 *
 * Email (mbox \ eml \ RFC format) language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2008/10/19 (1.0.8.1)
 *   -  First Release
 *
 * TODO (updated 2008/10/19)
 * -------------------------
 * * Better checks when a header field should be expected
 * * Fix the bound checks for kw groups 2 and 3, as well as rx group 1
 *
 *************************************************************************************
 *
 *     This file is part of GeSHi.
 *
 *   GeSHi is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   GeSHi is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with GeSHi; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 ************************************************************************************/

$language_data = array (
    'LANG_NAME' => 'eMail (mbox)',
    'COMMENT_SINGLE' => array(),
    'COMMENT_MULTI' => array(),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array('"'),
    'ESCAPE_CHAR' => '',
    'KEYWORDS' => array(
        1 => array(
            'HTTP', 'SMTP', 'ASMTP', 'ESMTP'
            ),
        2 => array(
            'Original-Recipient','Accept-Language','Alternate-Recipient',
            'Archived-At','Authentication-Results','Auto-Submitted',
            'Autoforwarded','Autosubmitted','Base','Comments',
            'Content-Alternative','Content-Base','Content-Description',
            'Content-Disposition','Content-Duration','Content-features',
            'Content-ID','Content-Identifier','Content-Language',
            'Content-Location','Content-MD5','Content-Return',
            'Content-Transfer-Encoding','Content-Type','Conversion',
            'Conversion-With-Loss','Deferred-Delivery','Delivery-Date',
            'Discarded-X400-IPMS-Extensions','Discarded-X400-MTS-Extensions',
            'Disclose-Recipients','Disposition-Notification-Options',
            'Disposition-Notification-To','DKIM-Signature',
            'DL-Expansion-History','Downgraded-Bcc','Downgraded-Cc',
            'Downgraded-Disposition-Notification-To',
            'Downgraded-Final-Recipient','Downgraded-From',
            'Downgraded-In-Reply-To','Downgraded-Mail-From',
            'Downgraded-Message-Id','Downgraded-Original-Recipient',
            'Downgraded-Rcpt-To','Downgraded-References',
            'Downgraded-Reply-To','Downgraded-Resent-Bcc',
            'Downgraded-Resent-Cc','Downgraded-Resent-From',
            'Downgraded-Resent-Reply-To','Downgraded-Resent-Sender',
            'Downgraded-Resent-To','Downgraded-Return-Path',
            'Downgraded-Sender','Downgraded-To','Encoding','Encrypted','Expires',
            'Expiry-Date','Generate-Delivery-Report','Importance','In-Reply-To',
            'Incomplete-Copy','Keywords','Language','Latest-Delivery-Time',
            'List-Archive','List-Help','List-ID','List-Owner','List-Post',
            'List-Subscribe','List-Unsubscribe','List-Unsubscribe-Post',
            'Message-Context','Message-ID','Message-Type','MIME-Version',
            'MMHS-Acp127-Message-Identifier','MMHS-Codress-Message-Indicator',
            'MMHS-Copy-Precedence','MMHS-Exempted-Address',
            'MMHS-Extended-Authorisation-Info','MMHS-Handling-Instructions',
            'MMHS-Message-Instructions','MMHS-Message-Type',
            'MMHS-Originator-PLAD','MMHS-Originator-Reference',
            'MMHS-Other-Recipients-Indicator-CC',
            'MMHS-Other-Recipients-Indicator-To','MMHS-Primary-Precedence',
            'MMHS-Subject-Indicator-Codes','MT-Priority','Obsoletes',
            'Organization','Original-Encoded-Information-Types','Original-From',
            'Original-Message-ID','Original-Subject','Originator-Return-Address',
            'PICS-Label','Prevent-NonDelivery-Report','Priority','Received',
            'Received-SPF','References','Reply-By','Reply-To',
            'Require-Recipient-Valid-Since','Resent-Bcc','Resent-Cc',
            'Resent-Date','Resent-From','Resent-Message-ID','Resent-Reply-To',
            'Resent-Sender','Resent-To','Return-Path','Sensitivity',
            'Solicitation','Supersedes','VBR-Info','X400-Content-Identifier',
            'X400-Content-Return','X400-Content-Type','X400-MTS-Identifier',
            'X400-Originator','X400-Received','X400-Recipients','X400-Trace'
            ),
        3 => array(
            'Bcc','CC','Date','From','Sender','Subject','To'
            ),
        4 => array(
            'by', 'for', 'from', 'id', 'with'
            )
        ),
    'SYMBOLS' => array(
        ':', ';', '<', '>', '[', ']'
        ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => true,
        2 => false,
        3 => false,
        4 => true
        ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #0000FF; font-weight: bold;',
            2 => 'color: #000000; font-weight: bold;',
            3 => 'color: #800000; font-weight: bold;',
            4 => 'font-weight: bold;',
            ),
        'COMMENTS' => array(
            ),
        'ESCAPE_CHAR' => array(
            0 => 'color: #000099; font-weight: bold;'
            ),
        'BRACKETS' => array(
            0 => 'color: #66cc66;'
            ),
        'STRINGS' => array(
            0 => 'color: #ff0000;'
            ),
        'NUMBERS' => array(
            0 => 'color: #cc66cc;'
            ),
        'METHODS' => array(
            ),
        'SYMBOLS' => array(
            0 => 'color: #66cc66;'
            ),
        'SCRIPT' => array(
            0 => 'color: #000040;',
            1 => 'color: #002040;',
            ),
        'REGEXPS' => array(
            1 => 'color: #000000; font-weight: bold;',
            2 => 'color: #0000FF;',
            3 => 'color: #008000;',
            4 => 'color: #0000FF; font-weight: bold;',
            5 => 'font-weight: bold;',
            6 => 'color: #400080;'
            )
        ),
    'URLS' => array(
        1 => '',
        2 => '',
        3 => '',
        4 => ''
        ),
    'OOLANG' => false,
    'OBJECT_SPLITTERS' => array(
        ),
    'REGEXPS' => array(
        // Non-Standard-Header
        1 => array(
            GESHI_SEARCH => "(?<=\A\x20|\n)x-[a-z0-9\-]*(?=\s*:|\s*<)",
            GESHI_REPLACE => "\\0",
            GESHI_MODIFIERS => "smi",
            GESHI_BEFORE => "",
            GESHI_AFTER => ""
            ),
        //Email-Adresses or Mail-IDs
        2 => array(
            GESHI_SEARCH => "\b(?<!\\/)(?P<q>\"?)[\w\.\-]+\k<q>@(?!-)[\w\-]+(?<!-)(?:(?:\.(?!-)[\w\-]+(?<!-))*)?",
            GESHI_REPLACE => "\\0",
            GESHI_MODIFIERS => "mi",
            GESHI_BEFORE => "",
            GESHI_AFTER => ""
            ),
        //Date values in RFC format
        3 => array(
            GESHI_SEARCH => "\b(?:Mon|Tue|Wed|Thu|Fri|Sat|Sun),\s+\d\d?\s+" .
                "(?:Jan|Feb|Mar|apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+" .
                "\d{4}\s+\d\d?:\d\d:\d\d\s+[+\-]\d{4}(?:\s+\(\w+\))?",
            GESHI_REPLACE => "\\0",
            GESHI_MODIFIERS => "mi",
            GESHI_BEFORE => "",
            GESHI_AFTER => ""
            ),
        //IP addresses
        4 => array(
            GESHI_SEARCH => "(?<=\s)\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}(?=\s)|".
                "(?<=\[)\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}(?=\])|".
                "(?<==)\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}(?=<)|".

                "(?<=\s)(?:[a-f\d]{1,4}\:)+(?:[a-f\d]{0,4})?(?:\:[a-f\d]{1,4})+(?=\s)|".
                "(?<=\[)(?:[a-f\d]{1,4}\:)+(?:[a-f\d]{0,4})?(?:\:[a-f\d]{1,4})+(?=\])|".
                "(?<==)(?:[a-f\d]{1,4}\:)+(?:[a-f\d]{0,4})?(?:\:[a-f\d]{1,4})+(?=<)|".

                "(?<=\s)\:(?:\:[a-f\d]{1,4})+(?=\s)|".
                "(?<=\[)\:(?:\:[a-f\d]{1,4})+(?=\])|".
                "(?<==)\:(?:\:[a-f\d]{1,4})+(?=<)|".

                "(?<=\s)(?:[a-f\d]{1,4}\:)+\:(?=\s)|".
                "(?<=\[)(?:[a-f\d]{1,4}\:)+\:(?=\])|".
                "(?<==)(?:[a-f\d]{1,4}\:)+\:(?=<)",
            GESHI_REPLACE => "\\0",
            GESHI_MODIFIERS => "i",
            GESHI_BEFORE => "",
            GESHI_AFTER => ""
            ),
        //Field-Assignments
        5 => array(
            GESHI_SEARCH => "(?<=\s)[A-Z0-9\-\.]+(?==(?:$|\s$|[^\s=]))",
            GESHI_REPLACE => "\\0",
            GESHI_MODIFIERS => "mi",
            GESHI_BEFORE => "",
            GESHI_AFTER => ""
            ),
        //MIME type
        6 => array(
            GESHI_SEARCH => "(?<=\s)(?:audio|application|image|multipart|text|".
                "video|x-[a-z0-9\-]+)\/[a-z0-9][a-z0-9\-]*(?=\s|<|$)",
            GESHI_REPLACE => "\\0",
            GESHI_MODIFIERS => "m",
            GESHI_BEFORE => "",
            GESHI_AFTER => ""
            )
        ),
    'STRICT_MODE_APPLIES' => GESHI_ALWAYS,
    'SCRIPT_DELIMITERS' => array(
        0 => "/(?P<start>^)[A-Za-z][a-zA-Z0-9\-]*\s*:\s*(?:.|(?=\n\s)\n)*(?P<end>$)/m",
        1 => "/(?P<start>^)--[a-zA-Z0-9_=\-]+[a-zA-Z0-9_](?:--)?(?P<end>$)/m"
    ),
    'HIGHLIGHT_STRICT_BLOCK' => array(
        0 => true,
        1 => false
        ),
    'TAB_WIDTH' => 4,
    'PARSER_CONTROL' => array(
        'KEYWORDS' => array(
            2 => array(
                'DISALLOWED_BEFORE' => '(?<=\A\x20|\n)',
                'DISALLOWED_AFTER' => '(?=\s*:)',
            ),
            3 => array(
                'DISALLOWED_BEFORE' => '(?<=\A\x20|\n)',
                'DISALLOWED_AFTER' => '(?=\s*:)',
            ),
            4 => array(
                'DISALLOWED_BEFORE' => '(?<=\s)',
                'DISALLOWED_AFTER' => '(?=\s|\b)',
            )
        ),
        'ENABLE_FLAGS' => array(
            'BRACKETS' => GESHI_NEVER,
            'COMMENTS' => GESHI_NEVER,
            'NUMBERS' => GESHI_NEVER
        )
    )
);
