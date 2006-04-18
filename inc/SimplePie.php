<?php
/****************************************************
SIMPLE PIE
A Simple PHP-Based RSS/Atom Parser
Simplifies the process of displaying the values of commonly used feed tags.

Version: 1.0 Beta
Updated: 29 January 2006
Copyright: 2004-2006 Ryan Parman, Geoffrey Sneddon
http://www.simplepie.org

LICENSE:
Creative Commons Attribution License 2.5
http://www.creativecommons.org/licenses/by/2.5/

You are free:
    - to copy, distribute, display, and perform the work
    - to make derivative works
    - to make commercial use of the work

Under the following conditions:
    - Attribution: You must attribute the work in the manner specified by the author or licensor.

For any reuse or distribution, you must make clear to others the license terms of this work.  Any
of these conditions can be waived if you get permission from the copyright holder.  Your fair use
and other rights are in no way affected by the above.

SPECIFICS OF REQUIRED ATTRIBUTION:
You can use this software for anything you want, but this entire notice must always remain in place.

Please submit all bug reports and feature requests to the SimplePie forums.
http://support.simplepie.org

****************************************************/


class SimplePie {

    // SimplePie Information
    var $name = 'SimplePie';
    var $version = '1.0 Beta';
    var $build = '20060129';
    var $url = 'http://www.simplepie.org/';
    var $useragent;
    var $linkback;

    // Run-time Variables
    var $rss_url;
    var $encoding;
    var $xml_dump = false;
    var $caching = true;
    var $max_minutes = 60;
    var $cache_location = './cache';
    var $replace_headers = false;

    // RSS Auto-Discovery Variables
    var $parsed_url;
    var $local;
    var $elsewhere;

    // XML Parsing Variables
    var $xml;
    var $tagName;
    var $insideItem;
    var $insideChannel;
    var $insideImage;
    var $insideAuthor;
    var $descriptionPriority;
    var $lastDescription;
    var $useGuid = false;
    var $itemNumber = 0;
    var $data = false;




    /****************************************************
    CONSTRUCTOR
    Initiates a couple of variables
    ****************************************************/
    function SimplePie() {
        $this->useragent = $this->name . '/' . $this->version . ' (Feed Parser; ' . $this->url . '; Allow like Gecko) Build/' . $this->build;
        $this->linkback = '<a href="' . $this->url . '" title="' . $this->name . ' ' . $this->version . '">' . $this->name . ' ' . $this->version . '</a>';
    }




    /****************************************************
    CONFIGURE OPTIONS
    Set various options (feed URL, XML dump, caching, etc.)
    ****************************************************/
    // Feed URL
    function feed_url($url) {
        $url = $this->fix_protocol($url, 1);
        $this->rss_url = $url;
        return true;
    }

    // XML Dump
    function enable_xmldump($enable) {
        $this->xml_dump = $enable;
        return true;
    }

    // Caching
    function enable_caching($enable) {
        $this->caching = $enable;
        return true;
    }

    // Cache Timeout
    function cache_max_minutes($minutes) {
        $this->max_minutes = (int) $minutes;
        return true;
    }

    // Cache Location
    function cache_location($location) {
        $this->cache_location = (string) $location;
        return true;
    }

    // Replace H1, H2, and H3 tags with the less important H4 tags.
    function replace_headers($enable) {
        $this->replace_headers = (bool) $enable;
        return true;
    }





    /****************************************************
    MAIN SIMPLEPIE FUNCTION
    Rewrites the feed so that it actually resembles XML, processes the XML,
    and builds an array from the feed.
    ****************************************************/
    function init() {

        // If this is a .Mac Photocast, change it to the real URL.
        if (stristr($this->rss_url, 'http://photocast.mac.com')) {
            $this->rss_url = preg_replace('%http://photocast.mac.com%i', 'http://web.mac.com', $this->rss_url);
        }

        // Return the User-Agent string to the website's logs.
        ini_set('user_agent', $this->useragent);

        // Clear all outdated cache from the server's cache folder
        $this->clear_cache($this->cache_location, $this->max_minutes);

        if ($this->rss_url) {
            // Read the XML file for processing.
            $cache_filename = $this->cache_location . '/' . urlencode($this->rss_url) . '.spc';
            if ($this->caching && !$this->xml_dump && substr($this->rss_url, 0, 7) == 'http://' && file_exists($cache_filename)) {
                if ($fp = fopen($cache_filename, 'r')) {
                    $data = '';
                    while (!feof($fp)) {
                        $data .= fread($fp, 2048);
                    }
                    fclose($fp);
                    $mp_rss = unserialize($data);
                    if (empty($mp_rss)) {
                        $this->caching = false;
                        return $this->init();
                    } elseif (isset($mp_rss['feed_url'])) {
                        $this->rss_url = $mp_rss['feed_url'];
                        return $this->init();
                    } else {
                        $this->data = $mp_rss;
                        return true;
                    }
                } else {
                    $this->caching = false;
                    return $this->init();
                }
            } else {
                // Get the file
                $mp_rss = $this->get_file($this->rss_url);

                // Check if file is a feed or a webpage
                // If it's a webpage, auto-discover the feed and re-pass it to init()
                $discovery = $this->rss_locator($mp_rss, $this->rss_url);
                if ($discovery) {
                    if ($discovery == 'nofeed') {
                        return false;
                    } else {
                        $this->rss_url = $discovery;
                        if ($this->caching && substr($this->rss_url, 0, 7) == 'http://') {
                            if ($this->is_writeable_createable($cache_filename)) {
                                $fp = fopen($cache_filename, 'w');
                                fwrite($fp, serialize(array('feed_url' => $discovery)));
                                fclose($fp);
                            } else trigger_error("$cache_filename is not writeable", E_USER_WARNING);
                        }
                        return $this->init();
                    }
                }

                // Trim out any whitespace at the beginning or the end of the file
                $mp_rss = trim($mp_rss);

                // Get encoding
                // Support everything from http://www.php.net/manual/en/ref.mbstring.php#mbstring.supported-encodings
                $use_mbstring = false;
                if (preg_match('/encoding=["](.*)["]/Ui', $mp_rss, $encoding)) {
                    switch (strtolower($encoding[1])) {

                        // UCS-4
                        case 'ucs-4':
                        case 'ucs4':
                        case 'utf-32':
                        case 'utf32':
                            $encoding = 'UCS-4';
                            $use_mbstring = true;
                            break;

                        // UCS-4BE
                        case 'ucs-4be':
                        case 'ucs4be':
                        case 'utf-32be':
                        case 'utf32be':
                            $encoding = 'UCS-4BE';
                            $use_mbstring = true;
                            break;

                        // UCS-4LE
                        case 'ucs-4le':
                        case 'ucs4le':
                        case 'utf-32le':
                        case 'utf32le':
                            $encoding = 'UCS-4LE';
                            $use_mbstring = true;
                            break;

                        // UCS-2
                        case 'ucs-2':
                        case 'ucs2':
                        case 'utf-16':
                        case 'utf16':
                            $encoding = 'UCS-2';
                            $use_mbstring = true;
                            break;

                        // UCS-2BE
                        case 'ucs-2be':
                        case 'ucs2be':
                        case 'utf-16be':
                        case 'utf16be':
                            $encoding = 'UCS-2BE';
                            $use_mbstring = true;
                            break;

                        // UCS-2LE
                        case 'ucs-2le':
                        case 'ucs2le':
                        case 'utf-16le':
                        case 'utf16le':
                            $encoding = 'UCS-2LE';
                            $use_mbstring = true;
                            break;

                        // UCS-32
                        case 'ucs-32':
                        case 'ucs32':
                            $encoding = 'UCS-32';
                            $use_mbstring = true;
                            break;

                        // UCS-32BE
                        case 'ucs-32be':
                        case 'ucs32be':
                            $encoding = 'UCS-32BE';
                            $use_mbstring = true;
                            break;

                        // UCS-32LE
                        case 'ucs-32le':
                        case 'ucs32le':
                            $encoding = 'UCS-32LE';
                            $use_mbstring = true;
                            break;

                        // UCS-16
                        case 'ucs-16':
                        case 'ucs16':
                            $encoding = 'UCS-16';
                            $use_mbstring = true;
                            break;

                        // UCS-16BE
                        case 'ucs-16be':
                        case 'ucs16be':
                            $encoding = 'UCS-16BE';
                            $use_mbstring = true;
                            break;

                        // UCS-16LE
                        case 'ucs-16le':
                        case 'ucs16le':
                            $encoding = 'UCS-16LE';
                            $use_mbstring = true;
                            break;

                        // UTF-7
                        case 'utf-7':
                        case 'utf7':
                            $encoding = 'UTF-7';
                            $use_mbstring = true;
                            break;

                        // UTF7-IMAP
                        case 'utf-7-imap':
                        case 'utf7-imap':
                        case 'utf7imap':
                            $encoding = 'UTF7-IMAP';
                            $use_mbstring = true;
                            break;

                        // ASCII
                        case 'us-ascii':
                        case 'ascii':
                            $encoding = 'US-ASCII';
                            $use_mbstring = true;
                            break;

                        // EUC-JP
                        case 'euc-jp':
                        case 'eucjp':
                            $encoding = 'EUC-JP';
                            $use_mbstring = true;
                            break;

                        // EUCJP-win
                        case 'euc-jp-win':
                        case 'eucjp-win':
                        case 'eucjpwin':
                            $encoding = 'EUCJP-win';
                            $use_mbstring = true;
                            break;

                        // Shift_JIS
                        case 'shift_jis':
                        case 'sjis':
                        case '932':
                            $encoding = 'Shift_JIS';
                            $use_mbstring = true;
                            break;

                        // SJIS-win
                        case 'sjis-win':
                        case 'sjiswin':
                        case 'shift_jis-win':
                            $encoding = 'SJIS-win';
                            $use_mbstring = true;
                            break;

                        // ISO-2022-JP
                        case 'iso-2022-jp':
                        case 'iso2022-jp':
                        case 'iso2022jp':
                            $encoding = 'ISO-2022-JP';
                            $use_mbstring = true;
                            break;

                        // JIS
                        case 'jis':
                            $encoding = 'JIS';
                            $use_mbstring = true;
                            break;

                        // ISO-8859-1
                        case 'iso-8859-1':
                        case 'iso8859-1':
                            $encoding = 'ISO-8859-1';
                            $use_mbstring = true;
                            break;

                        // ISO-8859-2
                        case 'iso-8859-2':
                        case 'iso8859-2':
                            $encoding = 'ISO-8859-2';
                            $use_mbstring = true;
                            break;

                        // ISO-8859-3
                        case 'iso-8859-3':
                        case 'iso8859-3':
                            $encoding = 'ISO-8859-3';
                            $use_mbstring = true;
                            break;

                        // ISO-8859-4
                        case 'iso-8859-4':
                        case 'iso8859-4':
                            $encoding = 'ISO-8859-4';
                            $use_mbstring = true;
                            break;

                        // ISO-8859-5
                        case 'iso-8859-5':
                        case 'iso8859-5':
                            $encoding = 'ISO-8859-5';
                            $use_mbstring = true;
                            break;

                        // ISO-8859-6
                        case 'iso-8859-6':
                        case 'iso8859-6':
                            $encoding = 'ISO-8859-6';
                            $use_mbstring = true;
                            break;

                        // ISO-8859-7
                        case 'iso-8859-7':
                        case 'iso8859-7':
                            $encoding = 'ISO-8859-7';
                            $use_mbstring = true;
                            break;

                        // ISO-8859-8
                        case 'iso-8859-8':
                        case 'iso8859-8':
                            $encoding = 'ISO-8859-8';
                            $use_mbstring = true;
                            break;

                        // ISO-8859-9
                        case 'iso-8859-9':
                        case 'iso8859-9':
                            $encoding = 'ISO-8859-9';
                            $use_mbstring = true;
                            break;

                        // ISO-8859-10
                        case 'iso-8859-10':
                        case 'iso8859-10':
                            $encoding = 'ISO-8859-10';
                            $use_mbstring = true;
                            break;

                        // mbstring functions don't appear to support 11 & 12

                        // ISO-8859-13
                        case 'iso-8859-13':
                        case 'iso8859-13':
                            $encoding = 'ISO-8859-13';
                            $use_mbstring = true;
                            break;

                        // ISO-8859-14
                        case 'iso-8859-14':
                        case 'iso8859-14':
                            $encoding = 'ISO-8859-14';
                            $use_mbstring = true;
                            break;

                        // ISO-8859-15
                        case 'iso-8859-15':
                        case 'iso8859-15':
                            $encoding = 'ISO-8859-15';
                            $use_mbstring = true;
                            break;

                        // byte2be
                        case 'byte2be':
                            $encoding = 'byte2be';
                            $use_mbstring = true;
                            break;

                        // byte2le
                        case 'byte2le':
                            $encoding = 'byte2le';
                            $use_mbstring = true;
                            break;

                        // byte4be
                        case 'byte4be':
                            $encoding = 'byte4be';
                            $use_mbstring = true;
                            break;

                        // byte4le
                        case 'byte4le':
                            $encoding = 'byte4le';
                            $use_mbstring = true;
                            break;

                        // BASE64
                        case 'base64':
                        case 'base-64':
                            $encoding = 'BASE64';
                            $use_mbstring = true;
                            break;

                        // HTML-ENTITIES
                        case 'html-entities':
                        case 'htmlentities':
                            $encoding = 'HTML-ENTITIES';
                            $use_mbstring = true;
                            break;

                        // 7bit
                        case '7bit':
                        case '7-bit':
                            $encoding = '7bit';
                            $use_mbstring = true;
                            break;

                        // 8bit
                        case '8bit':
                        case '8-bit':
                            $encoding = '8bit';
                            $use_mbstring = true;
                            break;

                        // EUC-CN
                        case 'euc-cn':
                        case 'euccn':
                            $encoding = 'EUC-CN';
                            $use_mbstring = true;
                            break;

                        // EUC-TW
                        case 'euc-tw':
                        case 'euctw':
                            $encoding = 'EUC-TW';
                            $use_mbstring = true;
                            break;

                        // EUC-KR
                        case 'euc-kr':
                        case 'euckr':
                            $encoding = 'EUC-KR';
                            $use_mbstring = true;
                            break;

                        // Traditional Chinese, mainly used in Taiwan
                        case 'big5':
                        case '950':
                            $encoding = 'BIG5';
                            $use_mbstring = true;
                            break;

                        // Simplified Chinese, national standard character set
                        case 'gb2312':
                        case '936':
                            $encoding = 'GB2312';
                            $use_mbstring = true;
                            break;

                        // Big5 with Hong Kong extensions, Traditional Chinese
                        case 'big5-hkscs':
                            $encoding = 'BIG5-HKSCS';
                            $use_mbstring = true;
                            break;

                        // Windows-specific Cyrillic
                        case 'cp1251':
                        case 'windows-1251':
                        case 'win-1251':
                        case '1251':
                            $encoding = 'Windows-1251';
                            $use_mbstring = true;
                            break;

                        // Windows-specific Western Europe
                        case 'cp1252':
                        case 'windows-1252':
                        case '1252':
                            $encoding = 'Windows-1252';
                            $use_mbstring = true;
                            break;

                        // Russian
                        case 'koi8-r':
                        case 'koi8-ru':
                        case 'koi8r':
                            $encoding = 'KOI8-R';
                            $use_mbstring = true;
                            break;

                        // HZ
                        case 'hz':
                            $encoding = 'HZ';
                            $use_mbstring = true;
                            break;

                        // ISO-2022-KR
                        case 'iso-2022-kr':
                        case 'iso2022-kr':
                        case 'iso2022kr':
                            $encoding = 'ISO-2022-KR';
                            $use_mbstring = true;
                            break;

                        // DOS-specific Cyrillic
                        case 'cp866':
                        case 'ibm866':
                        case '866':
                            $encoding = 'cp866';
                            $use_mbstring = true;
                            break;

                        // DOS-specific Cyrillic
                        case 'cp936':
                        case 'ibm936':
                        case '936':
                            $encoding = 'cp936';
                            $use_mbstring = true;
                            break;

                        // DOS-specific Cyrillic
                        case 'cp959':
                        case 'ibm959':
                        case '959':
                            $encoding = 'cp959';
                            $use_mbstring = true;
                            break;

                        // DOS-specific Cyrillic
                        case 'cp949':
                        case 'ibm949':
                        case '949':
                        case 'uhc':
                            $encoding = 'cp949';
                            $use_mbstring = true;
                            break;

                        // Default to UTF-8
                        default:
                            $encoding = 'UTF-8';
                            break;
                    }
                } else {
                    $encoding = 'UTF-8';
                }
                $this->encoding = $encoding;

                // If function is available, convert characters to UTF-8, and overwrite $this->encoding
                if (function_exists('mb_convert_encoding') && ($use_mbstring)) {
                    $mp_rss = mb_convert_encoding($mp_rss, 'UTF-8', $encoding);
                    $this->encoding = 'UTF-8';
                }

                // Encode entities within CDATA
                $mp_rss = preg_replace_callback('/<!\[CDATA\[(.*)\]\]>/Uis', array(&$this, 'cdata_encode'), $mp_rss);

                // Strip out all CDATA tags
                $mp_rss = str_replace('<![CDATA[', '', $mp_rss);
                $mp_rss = str_replace(']]>', '', $mp_rss);

                // Replace any other brackets with their entities
                $mp_rss = str_replace('[', '&#91;', $mp_rss); // [ character -- problems with CDATA
                $mp_rss = str_replace(']', '&#93;', $mp_rss); // ] character -- problems with CDATA

                // Fix tags inside code and pre tags.
                $mp_rss = preg_replace_callback('/<code[ .*]?>(.*)<\/code>/Uis', array(&$this, 'code_encode'), $mp_rss);
                $mp_rss = preg_replace_callback('/<pre[ .*]?>(.*)<\/pre>/Uis', array(&$this, 'code_encode'), $mp_rss);

                // Create an array of all of the elements that SimplePie supports the parsing of.
                $sp_elements = array(
                    // These elements are supported by SimplePie (alphabetical)
                    'category',
                    'content',
                    'copyright',
                    'dc:creator',
                    'dc:date',
                    'dc:description',
                    'dc:language',
                    'dc:subject',
                    'description',
                    'guid',
                    'id',
                    'height',
                    'issued',
                    'language',
                    'logo',
                    'name',
                    'pubDate',
                    'published',
                    'subtitle',
                    'summary',
                    'tagline',
                    'title',
                    'url',
                    'width',

                    // These elements are not currently supported by SimplePie
                    // We'll just CDATA them to be safe.
                    'comments',
                    'dc:contributor',
                    'dc:coverage',
                    'dc:format',
                    'dc:identifier',
                    'dc:publisher',
                    'dc:relation',
                    'dc:rights',
                    'dc:source',
                    'dc:title',
                    'dc:type',
                    'docs',
                    'generator',
                    'icon',
                    'itunes:author',
                    'itunes:duration',
                    'itunes:email',
                    'itunes:explicit',
                    'itunes:keywords',
                    'itunes:name',
                    'itunes:subtitle',
                    'itunes:summary',
                    'lastBuildDate',
                    'managingEditor',
                    'media:credit',
                    'media:text',
                    'rating',
                    'rights',
                    'sy:updatePeriod',
                    'sy:updateFrequency',
                    'sy:updateBase',
                    'ttl',
                    'updated',
                    'webMaster'
                );

                // Store the number of elements in the above array.
                // Helps execution time in JavaScript, why not PHP?
                $sp_elements_size = sizeof($sp_elements);

                $mp_rss = str_replace('content:encoded', 'content', $mp_rss);
                $mp_rss = preg_replace("%<!DOCTYPE(.*)>%i", '', $mp_rss); // Strip out the DOCTYPE since we don't use it anyways.

                for ($i=0; $i < $sp_elements_size; $i++) {
                    $full = $sp_elements[$i];
                    $short = substr($full, 0, -1);

                    $mp_rss = preg_replace('%<' . $short . "[^>/]+((\"[^\"]*\")|(\'[^\']*\')|([^>/]*))((\s*)?|([^\s]))/>%i", '<' . $full . '></' . $full . '>', $mp_rss);
                    $mp_rss = preg_replace('%<' . $full . '(.|\s)*?>%i', '<' . $full . '\\0<![CDATA[', $mp_rss);
                    $mp_rss = preg_replace('%<' . $full . '<' . $full . '%i', '<' . $full, $mp_rss);
                    $mp_rss = preg_replace('%</' . $full . '(.|\s)*?>%i', ']]></' . $full . '>', $mp_rss);
                }

                // Separate rules for some tags.
                if (preg_match('/<rdf:rdf/i', $mp_rss) || preg_match('/<rss/i', $mp_rss)) {
                    // <author>
                    $mp_rss = preg_replace("%<autho[^>/]+((\"[^\"]*\")|(\'[^\']*\')|([^>/]*))((\s*)|([^\s]))/>%i", '<author></author>', $mp_rss);
                    $mp_rss = preg_replace('%<author(.|\s)*?>%i', '<author\\0<![CDATA[', $mp_rss);
                    $mp_rss = preg_replace('%<author<author%i', '<author', $mp_rss);
                    $mp_rss = preg_replace('%</author(.|\s)*?>%i', ']]></author>',$mp_rss);

                    // <link>
                    $mp_rss = preg_replace('%<link(.|\s)*?>%i', '<link\\0<![CDATA[', $mp_rss);
                    $mp_rss = preg_replace('%<link<link%i', '<link', $mp_rss);
                    $mp_rss = preg_replace('%</link(.|\s)*?>%i', ']]></link>', $mp_rss);
                }

                // Strip out HTML tags that might cause various security problems.
                // Based on recommendations by Mark Pilgrim at:
                // http://diveintomark.org/archives/2003/06/12/how_to_consume_rss_safely
                $tags_to_strip = array(
                    'html',
                    'body',
                    'script',
                    'noscript',
                    'embed',
                    'object',
                    'frameset',
                    'frame',
                    'iframe',
                    'meta',
                    'style',
                    'param',
                    'doctype',
                    'form',
                    'input',
                    'blink',
                    'marquee',
                    'font'
                );
                foreach ($tags_to_strip as $tag) {
                    $mp_rss = preg_replace('/<\/?' . $tag . '(.|\s)*?>/i', '', $mp_rss);
                }

                // Strip out HTML attributes that might cause various security problems.
                // Based on recommendations by Mark Pilgrim at:
                // http://diveintomark.org/archives/2003/06/12/how_to_consume_rss_safely
                $stripAttrib = '\' (style|id|class)="(.*?)"\'i';
                $mp_rss = preg_replace($stripAttrib, '', $mp_rss);

                // Swap out problematic characters.
                $mp_rss = str_replace('﻿', '', $mp_rss); // UTF-8 BOM
                $mp_rss = preg_replace("/�|�|–|—/", '--', $mp_rss); // em/en dash
                $mp_rss = preg_replace("/�|�|’|‘/", "'", $mp_rss); // single-quotes
                $mp_rss = preg_replace("/�|�|“|”/", '"', $mp_rss); // double-quotes
                $mp_rss = preg_replace("/�/", '', $mp_rss); // bad character

                // Swap out funky characters with their named entities.
                // Code is from Feedsplitter at chxo.com
                $mp_rss = preg_replace(array('/\&([a-z\d\#]+)\;/i',
                    '/\&/',
                    '/\#\|\|([a-z\d\#]+)\|\|\#/i',
                    '/(\=\"\-\/\%\?\!\'\(\)\[\\{\}\ \#\+\,\@_])/e'
                    ),
                    array('#||\\1||#',
                    '&amp;',
                    '&\\1;',
                    "'&#'.ord('\\1').';'"
                    ),
                    $mp_rss
                );

                // Get rid of invalid UTF-8 characters
                // Code is from chregu at blog.bitflux.ch
                if (function_exists('iconv'))
                    $mp_rss = iconv('UTF-8', 'UTF-8//IGNORE', $mp_rss);

                if ($this->replace_headers) {
                    // Replace H1, H2, and H3 tags with the less important H4 tags.
                    // This is because on a site, the more important headers might make sense,
                    // but it most likely doesn't fit in the context of RSS-in-a-webpage.
                    $mp_rss = preg_replace('/<h[1-3](.|\s)*?>/i', '<h4>', $mp_rss);
                    $mp_rss = preg_replace('/<\/h[1-3](.|\s)*?>/i', '</h4>', $mp_rss);
                }

                // Find the domain name of the feed being read.
                $feed_path = parse_url($this->rss_url);
                if (isset($feed_path['host'])) {
                    $feed_host = $feed_path['host'];

                    // Change certain types of relative URL's into absolute URL's
                    $mp_rss = str_replace('href="/', 'href="http://' . $feed_host . '/', $mp_rss);
                    $mp_rss = str_replace('href=&quot;/', 'href="http://' . $feed_host . '/', $mp_rss);
                    $mp_rss = str_replace('src="/', 'src="http://' . $feed_host . '/', $mp_rss);
                    $mp_rss = str_replace('src=&quot;/', 'src="http://' . $feed_host . '/', $mp_rss);
                }

                // If XML Dump is enabled, send feed to the page and quit.
                if ($this->xml_dump) {
                    header("Content-type: text/xml; charset=" . $this->encoding);
                    echo $mp_rss;
                    exit;
                }

                $this->xml = xml_parser_create($this->encoding);
                xml_parser_set_option($this->xml, XML_OPTION_SKIP_WHITE, 1);
                xml_set_object($this->xml, $this);
                xml_set_character_data_handler($this->xml, 'dataHandler');
                xml_set_element_handler($this->xml, 'startHandler', 'endHandler');
                if (xml_parse($this->xml, $mp_rss))
                {
                    $this->data['feedinfo']['encoding'] = $this->encoding;
                    if ($this->caching && substr($this->rss_url, 0, 7) == 'http://') {
                        if ($this->is_writeable_createable($cache_filename)) {
                            $fp = fopen($cache_filename, 'w');
                            fwrite($fp, serialize($this->data));
                            fclose($fp);
                        }
                        else trigger_error("$cache_filename is not writeable", E_USER_WARNING);
                    }
                    return true;
                }
                else
                {
                    trigger_error(sprintf('XML error: %s at line %d, column %d', xml_error_string(xml_get_error_code($this->xml)), xml_get_current_line_number($this->xml), xml_get_current_column_number($this->xml)), E_USER_WARNING);
                    $this->data = array();
                    xml_parser_free($this->xml);
                    return false;
                }
            }
        }
        else return false;
    }




    /****************************************************
    GET FEED ENCODING
    ****************************************************/
    function get_encoding() {
        if (isset($this->encoding)) {
            return $this->encoding;
        } else if (isset($this->data['feedinfo']['encoding'])) {
            return $this->data['feedinfo']['encoding'];
        } else return false;
    }




    /****************************************************
    GET FEED VERSION NUMBER
    ****************************************************/
    function get_version() {
        if (isset($this->data['feedinfo'])) {
            return (isset($this->data['feedinfo']['version'])) ? $this->data['feedinfo']['type'] . ' ' . $this->data['feedinfo']['version'] : $this->data['feedinfo']['type'];
        }
        else return false;
    }




    /****************************************************
    SUBSCRIPTION URLS
    This allows people to subscribe to the feed in various services.
    ****************************************************/
    function subscribe_url() {
        if (isset($this->rss_url)) {
            $temp = $this->fix_protocol($this->rss_url, 1);
            if (strstr($temp, '../')) {
                $retVal = substr_replace($temp, '', 0, 3);
                return $retVal;
            }
            else return $temp;
        }
        else return false;
    }

    function subscribe_feed() {
        if (isset($this->rss_url)) {
            return $this->fix_protocol($this->rss_url, 2);
        }
        else return false;
    }

    function subscribe_podcast() {
        if (isset($this->rss_url)) {
            return $this->fix_protocol($this->rss_url, 3);
        }
        else return false;
    }

    function subscribe_aol() {
        return 'http://feeds.my.aol.com/add.jsp?url=' . rawurlencode($this->subscribe_url());
    }

    function subscribe_bloglines() {
        return 'http://www.bloglines.com/sub/' . rawurlencode($this->subscribe_url());
    }

    function subscribe_google() {
        return 'http://fusion.google.com/add?feedurl=' . rawurlencode($this->subscribe_url());
    }

    function subscribe_msn() {
        return 'http://my.msn.com/addtomymsn.armx?id=rss&amp;ut=' . rawurlencode($this->subscribe_url()) . '&amp;ru=' . rawurlencode($this->get_feed_link());
    }

    function subscribe_newsburst() {
        return 'http://www.newsburst.com/Source/?add=' . rawurlencode($this->subscribe_url());
    }

    function subscribe_newsgator() {
        return 'http://www.newsgator.com/ngs/subscriber/subext.aspx?url=' . rawurlencode($this->subscribe_url());
    }

    function subscribe_odeo() {
        return 'http://www.odeo.com/listen/subscribe?feed=' . rawurlencode($this->subscribe_url());
    }

    function subscribe_pluck() {
        return 'http://client.pluck.com/pluckit/prompt.aspx?GCID=C12286x053&amp;a=' . rawurlencode($this->subscribe_url());
    }

    function subscribe_podnova() {
        return 'http://www.podnova.com/index_your_podcasts.srf?action=add&url=' . rawurlencode($this->subscribe_url());
    }

    function subscribe_rojo() {
        return 'http://www.rojo.com/add-subscription?resource=' . rawurlencode($this->subscribe_url());
    }

    function subscribe_yahoo() {
        return 'http://add.my.yahoo.com/rss?url=' . rawurlencode($this->subscribe_url());
    }




    /****************************************************
    "ADD TO" LINKS
    Allows people to easily add news postings to social bookmarking sites.
    ****************************************************/
    function add_to_delicious($gitArrayValue) {
        return 'http://del.icio.us/post/?v=3&amp;url=' . rawurlencode($this->get_item_permalink($gitArrayValue)) . '&amp;title=' . rawurlencode($this->get_item_title($gitArrayValue));
    }

    function add_to_digg($gitArrayValue) {
        return 'http://digg.com/submit?phase=2&URL=' . rawurlencode($this->get_item_permalink($gitArrayValue));
    }

    function add_to_furl($gitArrayValue) {
        return 'http://www.furl.net/storeIt.jsp?u=' . rawurlencode($this->get_item_permalink($gitArrayValue)) . '&amp;t=' . rawurlencode($this->get_item_title($gitArrayValue));
    }

    function add_to_myweb20($gitArrayValue) {
        return 'http://myweb2.search.yahoo.com/myresults/bookmarklet?u=' . rawurlencode($this->get_item_permalink($gitArrayValue)) . '&amp;t=' . rawurlencode($this->get_item_title($gitArrayValue));
    }

    function add_to_newsvine($gitArrayValue) {
        return 'http://www.newsvine.com/_wine/save?u=' . rawurlencode($this->get_item_permalink($gitArrayValue)) . '&amp;h=' . rawurlencode($this->get_item_title($gitArrayValue));
    }

    function add_to_reddit($gitArrayValue) {
        return 'http://reddit.com/submit?url=' . rawurlencode($this->get_item_permalink($gitArrayValue)) . '&amp;title=' . rawurlencode($this->get_item_title($gitArrayValue));
    }

    function add_to_spurl($gitArrayValue) {
        return 'http://www.spurl.net/spurl.php?v=3&amp;url=' . rawurlencode($this->get_item_permalink($gitArrayValue)) . '&amp;title=' . rawurlencode($this->get_item_title($gitArrayValue));
    }




    /****************************************************
    SEARCHES
    Metadata searches
    ****************************************************/
    function search_technorati($gitArrayValue) {
        return 'http://www.technorati.com/search/' . rawurlencode($this->get_item_permalink($gitArrayValue));
    }




    /****************************************************
    PARSE OUT GENERAL FEED-RELATED DATA
    ****************************************************/
    // Reads the feed's title
    function get_feed_title() {
        if (isset($this->data['info']['name']))
            return $this->prep_output($this->data['info']['name']);
        else return false;
    }

    // Reads the feed's link (URL)
    function get_feed_link() {
        if (isset($this->data['info']['link']))
            return $this->prep_output($this->data['info']['link']);
        else return false;
    }

    // Reads the feed's description
    function get_feed_description() {
        if (isset($this->data['info']['description']))
            return $this->prep_output($this->data['info']['description']);
        else return false;
    }

    // Reads the feed's copyright information.
    function get_feed_copyright() {
        if (isset($this->data['info']['copyright']))
            return $this->prep_output($this->data['info']['copyright']);
        else return false;
    }

    // Reads the feed's language
    function get_feed_language() {
        if (isset($this->data['info']['language']))
            return $this->prep_output($this->data['info']['language']);
        else return false;
    }




    /****************************************************
    PARSE OUT IMAGE-RELATED DATA
    Apparently Atom doesn't have feed images.
    ****************************************************/
    // Check if an image element exists (returns true/false)
    function get_image_exist() {
        return (isset($this->data['info']['image']['url'])) ? true : false;
    }

    // Get the image title (to be used in alt and/or title)
    function get_image_title() {
        if (isset($this->data['info']['image']['title']))
            return $this->prep_output($this->data['info']['image']['title']);
        else return false;
    }

    // The path to the actual image
    function get_image_url() {
        return (isset($this->data['info']['image']['url'])) ? $this->data['info']['image']['url'] : false;
    }

    // The URL that the image is supposed to link to.
    function get_image_link() {
        return (isset($this->data['info']['image']['link'])) ? $this->data['info']['image']['link'] : false;
    }

    // Get the image width
    function get_image_width() {
        return (isset($this->data['info']['image']['width'])) ? $this->data['info']['image']['width'] : false;
    }

    // Get the image height
    function get_image_height() {
        return (isset($this->data['info']['image']['height'])) ? $this->data['info']['image']['height'] : false;
    }




    /****************************************************
    PARSE OUT ITEM-RELATED DATA
    Most of these have one parameter: position in array.
    ****************************************************/
    // Get the size of the array of items (for use in a for-loop)
    function get_item_quantity() {
        return (isset($this->data['items'])) ? sizeof($this->data['items']) : 0;
    }

    // Get the title of the item
    function get_item_title($gitArrayValue) {
        if (isset($this->data['items'][$gitArrayValue]['title']))
            return $this->prep_output($this->data['items'][$gitArrayValue]['title']);
        else return false;
    }

    // Get the description of the item
    function get_item_description($gitArrayValue) {
        if (isset($this->data['items'][$gitArrayValue]['description']))
            return $this->prep_output($this->data['items'][$gitArrayValue]['description']);
        else return false;
    }

    // Get the category of the item
    function get_item_category($gitArrayValue) {
        if (isset($this->data['items'][$gitArrayValue]['category']))
            return $this->prep_output($this->data['items'][$gitArrayValue]['category']);
        else return false;
    }

    // Get the author of the item
    function get_item_author($gitArrayValue) {
        if (isset($this->data['items'][$gitArrayValue]['author']))
            return $this->prep_output(implode(', ', $this->data['items'][$gitArrayValue]['author']));
        else return false;
    }

    // Get the date of the item
    // Also, allow users to set the format of how dates are displayed on a webpage.
    function get_item_date($gitArrayValue, $date_format = 'j F Y, g:i a') {
        return (isset($this->data['items'][$gitArrayValue]['date'])) ? date($date_format, $this->data['items'][$gitArrayValue]['date']) : false;
    }

    // Get the Permalink of the item (checks for link, then guid)
    function get_item_permalink($gitArrayValue) {
        // If there is a link, take it. Fine.
        if (isset($this->data['items'][$gitArrayValue]['link'])) {
            return $this->data['items'][$gitArrayValue]['link'];
        }

        // If there isn't, check for a guid.
        else if (isset($this->data['items'][$gitArrayValue]['guid'])) {
            return $this->data['items'][$gitArrayValue]['guid'];
        }

        // If there isn't, check for an enclosure, if that exists, give that.
        else if ($this->get_item_enclosure($gitArrayValue)) {
            return $this->get_item_enclosure($gitArrayValue);
        }
        else return false;
    }

    // Get the enclosure of the item
    function get_item_enclosure($gitArrayValue) {
        return (isset($this->data['items'][$gitArrayValue]['enclosure'])) ? $this->data['items'][$gitArrayValue]['enclosure'] : false;
    }




    /****************************************************
    FIX PROTOCOL
    Convert feed:// and no-protocol URL's to http://
    Feed is allowed to have no protocol.  Local files are toggled in init().
    This is an internal function and is not intended to be used publically.

    $http=1, http://www.domain.com/feed.xml (absolute)
    $http=2, feed://www.domain.com/feed.xml (absolute)
    $http=3, podcast://www.domain.com/feed.xml (absolute)
    ****************************************************/
    function fix_protocol($mp_feed_proto, $http = 1) {
        $url = $mp_feed_proto;

        // Swap out feed://http:// for http://-only
        if (stristr($mp_feed_proto, 'feed://http://')) {
            $url = substr_replace($mp_feed_proto, 'http://', 0, 14);
        }

        // Swap out feed:http:// for http://
        else if (stristr($mp_feed_proto, 'feed:http://' )) {
            $url = substr_replace($mp_feed_proto, 'http://', 0, 12);
        }

        // Swap out feed:// protocols in favor of http:// protocols
        else if (stristr($mp_feed_proto, 'feed://' )) {
            $url = substr_replace($mp_feed_proto, 'http://', 0, 7);
        }

        // Swap out feed:www. for http://www.
        else if (stristr($mp_feed_proto, 'feed:')) {
            $url = substr_replace($mp_feed_proto, 'http://', 0, 5);
        }

        // If it doesn't have http:// in it, and doesn't exist locally, add http://
        else if (!stristr($mp_feed_proto, 'http://') && !file_exists($mp_feed_proto)) {
            $url = "http://$url";
        }

        if ($http == 1) return $url;
        else if ($http == 2) {
            if (strstr($url, 'http://')) {
                $url = substr_replace($url, 'feed', 0, 4);
                return $url;
            }
            else return $url;
        }
        else if ($http == 3) {
            if (strstr($url, 'http://')) {
                $url = substr_replace($url, 'podcast', 0, 4);
                return $url;
            }
            else return $url;
        }
    }




    /****************************************************
    AUTO DISCOVERY
    Based upon http://diveintomark.org/archives/2002/08/15/ultraliberal_rss_locator
    This function enables support for RSS auto-discovery.
    ****************************************************/
    function rss_locator($data, $url) {

        $this->url = $url;
        $this->parsed_url = parse_url($url);
        if (!isset($this->parsed_url['path'])) {
            $this->parsed_url['path'] = '/';
        }

        // Check is the URL we're given is a feed
        if ($this->is_feed($data, false)) {
            return false;
        }

        // Feeds pointed to by LINK tags in the header of the page (autodiscovery)
        $stage1 = $this->check_link_elements($data);
        if ($stage1) {
            return $stage1;
        }

        // Grab all the links in the page, and put them into two arrays (local, and external)
        if ($this->get_links($data)) {

            // <A> links to feeds on the same server ending in ".rss", ".rdf", ".xml", or ".atom"
            $stage2 = $this->check_link_extension($this->local);
            if ($stage2) {
                return $stage2;
            }

            // <A> links to feeds on the same server containing "rss", "rdf", "xml", or "atom"
            $stage3 = $this->check_link_body($this->local);
            if ($stage3) {
                return $stage3;
            }

            // <A> links to feeds on external servers ending in ".rss", ".rdf", ".xml", or ".atom"
            $stage4 = $this->check_link_extension($this->elsewhere);
            if ($stage4) {
                return $stage4;
            }

            // <A> links to feeds on external servers containing "rss", "rdf", "xml", or "atom"
            $stage5 = $this->check_link_body($this->elsewhere);
            if ($stage5) {
                return $stage5;
            }
        }

        return 'nofeed';
    }

    function check_link_elements($data) {
        if (preg_match_all('/<link (.*)>/siU', $data, $matches)) {
            foreach($matches[1] as $match) {
                if (preg_match('/type=[\'|"](application\/rss\+xml|application\/atom\+xml|application\/rdf\+xml|application\/xml\+rss|application\/xml\+atom|application\/xml\+rdf|application\/xml|application\/x\.atom\+xml|text\/xml)[\'|"]/iU', $match, $type)) {
                    if (preg_match('/href=[\'|"](.*)[\'|"]/iU', $match, $href)) {
                        $href = $this->absolutize_url($href[1]);
                        if ($this->is_feed($href)) {
                            return $href;
                        }
                    }
                }
            }
        } else return false;
    }

    function check_link_extension($array) {
        foreach ($array as $value) {
            $parsed = @parse_url($value);
            if (isset($parsed['path'])) {
                $ext = strtolower(pathinfo($parsed['path'], PATHINFO_EXTENSION));
                if (($ext == 'rss' || $ext == 'rdf' || $ext == 'xml' || $ext == 'atom') && $this->is_feed($value)) {
                    return $this->absolutize_url($value);
                }
            }
        }
        return false;
    }

    function check_link_body($array) {
        foreach ($array as $value) {
            $value2 = parse_url($value);
            if (!empty($value2['path'])) {
                if (strlen(pathinfo($value2['path'], PATHINFO_EXTENSION)) > 0) {
                    $value3 = substr_replace($value, '', strpos($value, $value2['path'])+strpos($value2['path'], pathinfo($value2['path'], PATHINFO_EXTENSION))-1, strlen(pathinfo($value2['path'], PATHINFO_EXTENSION))+1);
                } else {
                    $value3 = $value;
                }
                if ((stristr($value3, 'rss') || stristr($value3, 'rdf') || stristr($value3, 'xml') || stristr($value3, 'atom') || stristr($value3, 'feed')) && $this->is_feed($value)) {
                    return $this->absolutize_url($value);
                }
            }
        }
        return false;
    }

    function get_links($data) {
        if (preg_match_all('/href=["](.*)["]/iU', $data, $matches)) {
            $this->parse_links($matches);
        }
        if (preg_match_all('/href=[\'](.*)[\']/iU', $data, $matches)) {
            $this->parse_links($matches);
        }
        if (preg_match_all('/href=(.*)[ |>]/iU', $data, $matches)) {
            foreach ($matches[1] as $key => $value) {
                if (substr($value, 0, 1) == '"' || substr($value, 0, 1) == "'") {
                    unset($matches[1][$key]);
                }
            }
            $this->parse_links($matches);
        }
        if (!empty($this->local) || !empty($this->elsewhere)) {
            $this->local = array_unique($this->local);
            $this->elsewhere = array_unique($this->elsewhere);
            return true;
        } else return false;
    }

    function parse_links($matches) {
        foreach ($matches[1] as $match) {
            if (strtolower(substr($match, 0, 11)) != 'javascript:') {
                $parsed = parse_url($match);
                if (!isset($parsed['host']) || $parsed['host'] == $this->parsed_url['host']) {
                    $this->local[] = $this->absolutize_url($match);
                } else {
                    $this->elsewhere[] = $this->absolutize_url($match);
                }
            }
        }
    }

    function is_feed($data, $is_url = true) {
        if ($is_url) {
            $data = $this->get_file($data);
        }
        if (stristr($data, '<html') || stristr($data, '<head') || stristr($data, '<body')) {
            return false;
        } else if (stristr($data, '<rss') || stristr($data, '<rdf:rdf') || stristr($data, '<feed')) {
            return true;
        } else {
            return false;
        }
    }

    function absolutize_url($href) {
        if (stristr($href, 'http://') !== false) {
            return $href;
        } else {
            $full_url = $this->parsed_url['scheme'] . '://' . $this->parsed_url['host'];
            if (isset($this->parsed_url['port'])) {
                $full_url .= ':' . $this->parsed_url['port'];
            }
            if ($href{0} != '/') {
                $full_url .= dirname($this->parsed_url['path']);
                if (substr($full_url, -1) != '/') {
                    $full_url .= '/';
                }
            }
            $full_url .= $href;
            return $full_url;
        }
    }




    /****************************************************
    DISPLAY IMAGES
    Some websites have a setting that blocks images from being loaded
    into other pages.  This gets around those blocks by spoofing the referrer.
    ****************************************************/
    function display_image($image_url) {
        $image = $this->get_file($image_url);
        $suffix = explode('.', $image_url);
        $suffix = array_pop($suffix);

        switch($suffix) {
            case 'bmp':
                $mime='image/bmp';
                break;
            case 'gif':
                $mime='image/gif';
                break;
            case 'ico':
                $mime='image/icon';
                break;
            case 'jpe':
            case 'jpg':
            case 'jpeg':
                $mime='image/jpeg';
                break;
            case 'jfif':
                $mime='image/pipeg';
                break;
            case 'png':
                $mime='image/png';
                break;
            case 'tif':
            case 'tiff':
                $mime='image/tiff';
                break;
            default:
                $mime='image';
        }

        header('Content-type: ' . $mime);
        echo $image;
    }




    /****************************************************
    DELETE OUTDATED CACHE FILES
    By adam[at]roomvoter[dot]com
    This function deletes cache files that have not been used in a hour.
    ****************************************************/
    function clear_cache($path, $max_minutes=60) {
        if (is_dir($path) ) {
            $handle = opendir($path);

            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'spc') {
                    $diff = (time() - filemtime("$path/$file"))/60;
                    if ($diff > $max_minutes) unlink("$path/$file");
                }
            }
            closedir($handle);
        }
    }




    /****************************************************
    SEES IF A FILE IS WRITEABLE, OR CREATEABLE
    ****************************************************/
    function is_writeable_createable($file) {
        if (file_exists($file))
            return is_writeable($file);
        else
            return is_writeable(dirname($file));
    }




    /****************************************************
    OPENS A FILE, WITH EITHER FOPEN OR cURL
    ****************************************************/
    function get_file($url) {
        if (substr($url, 0, 7) == 'http://' && extension_loaded('curl')) {
            $gch = curl_init();
            curl_setopt ($gch, CURLOPT_URL, $url);
            curl_setopt ($gch, CURLOPT_HEADER, 0);
            curl_setopt ($gch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt ($gch, CURLOPT_TIMEOUT, 10);
            curl_setopt ($gch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt ($gch, CURLOPT_REFERER, $url);
            curl_setopt ($gch, CURLOPT_USERAGENT, $this->useragent);
            $data = curl_exec ($gch);
            curl_close ($gch);
        } else {
            if ($fp = fopen($url, 'r')) {
                stream_set_blocking($fp, false);
                stream_set_timeout($fp, 10);
                $status = socket_get_status($fp);
                $data = '';
                while (!feof($fp) && !$status['timed_out']) {
                    $data .= fread($fp, 2048);
                    $status = socket_get_status($fp);
                }
                if ($status['timed_out'])
                    return false;
                fclose($fp);
            } else return false;
        }
        return $data;
    }




    /****************************************************
    GET DATA READY TO BE OUTPUTTED
    ****************************************************/
    function prep_output($data) {
        $data = trim($data);
        if ($this->get_encoding() && $this->get_encoding() == 'UTF-8') {
            // Simple htmlspecialchars_decode for less than PHP 5.1 RC1
            $data = strtr($data, array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
        } else {
            $data = html_entity_decode($data);
        }
        return $data;
    }




    /****************************************************
    CALLBACK FUNCTIONS FOR PREG_REPLACE_CALLBACK
    ****************************************************/
    function code_encode($regs) {
        return str_replace($regs[1], htmlspecialchars($regs[1]), $regs[0]);
    }

    function cdata_encode($regs) {
        if (isset($this->encoding) && $this->encoding == 'UTF-8')
            return str_replace($regs[1], htmlspecialchars($regs[1]), $regs[0]);
        else
            return str_replace($regs[1], htmlentities($regs[1]), $regs[0]);
    }




    /****************************************************
    FUNCTIONS FOR XML_PARSE
    ****************************************************/
    function startHandler($parser, $name, $attribs) {
        $this->tagName = $name;
        switch ($name) {
            case 'ITEM':
            case 'ENTRY':
                $this->insideItem = true;
                $this->descriptionPriority = 1000;
                $this->lastDescription = 1000;
                break;

            case 'CHANNEL':
                $this->insideChannel = true;
                break;

            case 'RSS':
                $this->data['feedinfo']['type'] = 'RSS';
                if (!empty($attribs['VERSION'])) {
                    $this->data['feedinfo']['version'] = trim($attribs['VERSION']);
                }
                break;

            case 'RDF:RDF':
                $this->data['feedinfo']['type'] = 'RSS';
                $this->data['feedinfo']['version'] = 1;
                break;

            case 'FEED':
                $this->data['feedinfo']['type'] = 'Atom';
                if (!empty($attribs['VERSION'])) {
                    $this->data['feedinfo']['version'] = trim($attribs['VERSION']);
                }
                if (!empty($attribs['XML:LANG'])) {
                    $this->data['info']['language'] = $attribs['XML:LANG'];
                }
                break;

            case 'IMAGE':
                if ($this->insideChannel) $this->insideImage = true;
                break;

            case 'GUID':
                if (isset($attribs['ISPERMALINK']) && $attribs['ISPERMALINK'] == 'false') $this->useGuid = false;
                else $this->useGuid = true;
                break;
        }

        if ($this->data['feedinfo']['type'] == 'Atom') {
            switch ($name) {
                case 'AUTHOR':
                    $this->insideAuthor = true;
                    break;

                case 'LINK':
                    if (!empty($attribs['HREF'])) {
                        if ($this->insideItem) $this->data['items'][$this->itemNumber]['link'] = $attribs['HREF'];
                        else $this->data['info']['link'] = $attribs['HREF'];
                    }
                    break;
            }
        }

        if ($this->insideItem) {
            if ($name == 'ENCLOSURE' || ($name == 'LINK' && isset($attribs['REL']) && strtolower($attribs['REL']) == 'enclosure')) {
                if (isset($attribs['TYPE']) && strpos($attribs['TYPE'], 'audio/') === 0 || strpos($attribs['TYPE'], 'video/') === 0) $use = true;
                else $use = false;

                if (!isset($attribs['TYPE']) || !$use) {
                    if (isset($attribs['URL'])) $ext = pathinfo($attribs['URL'], PATHINFO_EXTENSION);
                    else if (isset($attribs['HREF'])) $ext = pathinfo($attribs['HREF'], PATHINFO_EXTENSION);

                    if (isset($ext)) {
                        switch (strtolower($ext['extension'])) {
                            case 'aif':
                            case 'aifc':
                            case 'aiff':
                            case 'au':
                            case 'funk':
                            case 'gsd':
                            case 'gsm':
                            case 'it':
                            case 'jam':
                            case 'kar':
                            case 'la':
                            case 'lam':
                            case 'lma':
                            case 'm2a':
                            case 'm3u':
                            case 'mid':
                            case 'midi':
                            case 'mjf':
                            case 'mod':
                            case 'mp2':
                            case 'mp3':
                            case 'mpa':
                            case 'mpg':
                            case 'mpga':
                            case 'my':
                            case 'pfunk':
                            case 'qcp':
                            case 'ra':
                            case 'ram':
                            case 'rm':
                            case 'rmm':
                            case 'rmp':
                            case 'rpm':
                            case 's3m':
                            case 'sid':
                            case 'snd':
                            case 'tsi':
                            case 'tsp':
                            case 'voc':
                            case 'vox':
                            case 'vqe':
                            case 'vqf':
                            case 'vql':
                            case 'wav':
                            case 'xm':
                                $use = true;
                                break;

                            default:
                                $use = false;
                                break;
                        }
                    }
                }
                if (isset($use) && $use) {
                    if (isset($attribs['URL'])) $this->data['items'][$this->itemNumber]['enclosure'] = $attribs['URL'];
                    else if (isset($attribs['HREF'])) $this->data['items'][$this->itemNumber]['enclosure'] = $attribs['HREF'];
                }
            }
        }
    }

    function dataHandler($parser, $data) {
        if ($this->insideItem) {
            switch ($this->tagName) {
                case 'TITLE':
                    $this->data['items'][$this->itemNumber]['title'] = $data;
                break;

                case 'CONTENT':
                    if ($this->descriptionPriority > 0) {
                        if ($this->lastDescription > 0)
                            $this->data['items'][$this->itemNumber]['description'] = $data;
                        else
                            $this->data['items'][$this->itemNumber]['description'] .= $data;
                        $this->lastDescription = 0;
                    }
                break;

                case 'SUMMARY':
                    if ($this->descriptionPriority > 1) {
                        if ($this->lastDescription > 1)
                            $this->data['items'][$this->itemNumber]['description'] = $data;
                        else
                            $this->data['items'][$this->itemNumber]['description'] .= $data;
                        $this->lastDescription = 1;
                    }
                break;

                case 'DC:DESCRIPTION':
                    if ($this->descriptionPriority > 2) {
                        if ($this->lastDescription > 2)
                            $this->data['items'][$this->itemNumber]['description'] = $data;
                        else
                            $this->data['items'][$this->itemNumber]['description'] .= $data;
                        $this->lastDescription = 2;
                    }
                break;

                case 'LONGDESC':
                    if ($this->descriptionPriority > 3) {
                        if ($this->lastDescription > 3)
                            $this->data['items'][$this->itemNumber]['description'] = $data;
                        else
                            $this->data['items'][$this->itemNumber]['description'] .= $data;
                        $this->lastDescription = 3;
                    }
                break;

                case 'DESCRIPTION':
                    if ($this->descriptionPriority > 4) {
                        if ($this->lastDescription > 4)
                            $this->data['items'][$this->itemNumber]['description'] = $data;
                        else
                            $this->data['items'][$this->itemNumber]['description'] .= $data;
                        $this->lastDescription = 4;
                    }
                break;

                case 'LINK':
                    $this->data['items'][$this->itemNumber]['link'] = $data;
                break;

                case 'GUID':
                    if ($this->useGuid) $this->data['items'][$this->itemNumber]['guid'] = $data;
                break;

                case 'PUBDATE':
                case 'DC:DATE':
                case 'ISSUED':
                case 'PUBLISHED':
                    $this->data['items'][$this->itemNumber]['date'] = (stristr($data, '-')) ? strtotime(preg_replace('%(\-|\+)[0-1][0-9](:?)[0-9][0-9]%', '', str_replace('Z', '', trim($data)))) : strtotime(trim($data));
                break;

                case 'CATEGORY':
                case 'DC:SUBJECT':
                    $this->data['items'][$this->itemNumber]['category'] = $data;
                break;

                case 'DC:CREATOR':
                    $this->data['items'][$this->itemNumber]['author'][] = $data;
                break;

                case 'AUTHOR':
                    if ($this->data['feedinfo']['type'] != 'Atom') $this->data['items'][$this->itemNumber]['author'][] = $data;
                break;
            }

            if ($this->insideAuthor) {
                if ($this->tagName == 'NAME') $this->data['items'][$this->itemNumber]['author'][] = $data;
            }
        }

        else if ($this->insideChannel) {
            switch ($this->tagName) {
                case 'TITLE':
                    $this->data['info']['name'] = $data;
                break;

                case 'LINK':
                    $this->data['info']['link'] = $data;
                break;

                case 'DESCRIPTION':
                case 'TAGLINE':
                case 'SUBTITLE':
                    $this->data['info']['description'] = $data;
                break;

                case 'COPYRIGHT':
                    $this->data['info']['copyright'] = $data;
                break;

                case 'LANGUAGE':
                case 'DC:LANGUAGE':
                    $this->data['info']['language'] = $data;
                break;
            }
            if ($this->insideImage) {
                switch ($this->tagName) {
                    case 'TITLE':
                        $this->data['info']['image']['title'] = $data;
                    break;

                    case 'URL':
                        $this->data['info']['image']['url'] = $data;
                    break;

                    case 'LINK':
                        $this->data['info']['image']['link'] = $data;
                    break;

                    case 'WIDTH':
                        $this->data['info']['image']['width'] = $data;
                    break;

                    case 'HEIGHT':
                        $this->data['info']['image']['height'] = $data;
                    break;
                }
            }
        }

        else if (!$this->insideItem && $this->data['feedinfo']['type'] == 'Atom') {
            switch ($this->tagName) {
                case 'TITLE':
                    $this->data['info']['name'] = $data;
                break;

                case 'TAGLINE':
                    $this->data['info']['description'] = $data;
                break;

                case 'COPYRIGHT':
                    $this->data['info']['copyright'] = $data;
                break;
            }
        }
    }

    function endHandler($parser, $name) {
        $this->tagName = '';
        switch ($name) {
            case 'CONTENT':
                $this->descriptionPriority = 0;
            break;
            case 'SUMMARY':
                $this->descriptionPriority = 1;
            break;
            case 'DC:DESCRIPTION':
                $this->descriptionPriority = 2;
            break;
            case 'LONGDESC':
                $this->descriptionPriority = 3;
            break;
            case 'DESCRIPTION':
                $this->descriptionPriority = 4;
            break;
            case 'ITEM':
            case 'ENTRY':
                $this->insideItem = false;
                $this->itemNumber++;
            break;

            case 'CHANNEL':
                $this->insideChannel = false;
            break;

            case 'IMAGE':
                if ($this->insideChannel) $this->insideImage = false;
            break;

            case 'AUTHOR':
                if ($this->data['feedinfo']['type'] == 'Atom') $this->insideAuthor = false;
            break;
        }
    }
}

?>
