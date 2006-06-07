<?php
/****************************************************
SIMPLEPIE
A PHP-Based RSS and Atom Feed Framework
Takes the hard work out of managing a complete RSS/Atom solution.

Version: 1.0 Beta 2
Updated: 30 May 2006
Copyright: 2004-2006 Ryan Parman, Geoffrey Sneddon
http://simplepie.org

*****************************************************
LICENSE:

GNU Lesser General Public License 2.1 (LGPL)
http://creativecommons.org/licenses/LGPL/2.1/

*****************************************************
Please submit all bug reports and feature requests to the SimplePie forums.
http://simplepie.org/support/

****************************************************/

class SimplePie {

    // SimplePie Information
    var $name = 'SimplePie';
    var $version = '1.0 Beta 2';
    var $build = '20060530';
    var $url = 'http://simplepie.org/';
    var $useragent;
    var $linkback;

    // Run-time Variables
    var $rss_url;
    var $encoding;
    var $xml_dump = false;
    var $caching = true;
    var $max_minutes = 60;
    var $cache_location = './cache';
    var $bypass_image_hotlink = 'i';
    var $bypass_image_hotlink_page = false;
    var $replace_headers = false;
    var $remove_div = true;
    var $order_by_date = true;
    var $strip_ads = false;
    var $strip_htmltags = 'blink,body,doctype,embed,font,form,frame,frameset,html,iframe,input,marquee,meta,noscript,object,param,script,style';
    var $strip_attributes = 'class,id,style,onclick,onmouseover,onmouseout,onfocus,onblur';
    var $encode_instead_of_strip = false;

    // RSS Auto-Discovery Variables
    var $parsed_url;
    var $local = array();
    var $elsewhere = array();

    // XML Parsing Variables
    var $xml;
    var $tagName;
    var $insideItem;
    var $insideChannel;
    var $insideImage;
    var $insideAuthor;
    var $itemNumber = 0;
    var $authorNumber = 0;
    var $categoryNumber = 0;
    var $enclosureNumber = 0;
    var $linkNumber = 0;
    var $itemLinkNumber = 0;
    var $data = false;
    var $attribs;
    var $xmldata;
    var $feed_xmlbase;
    var $item_xmlbase;
    var $xhtml_prefix;




    /****************************************************
    CONSTRUCTOR
    Initiates a couple of variables.  Accepts feed_url, cache_location,
    and cache_max_minutes.
    ****************************************************/
    function SimplePie($feed_url = null, $cache_location = null, $cache_max_minutes = null) {
        $this->useragent = $this->name . '/' . $this->version . ' (Feed Parser; ' . $this->url . '; Allow like Gecko) Build/' . $this->build;
        $this->linkback = '<a href="' . $this->url . '" title="' . $this->name . ' ' . $this->version . '">' . $this->name . '</a>';

        if (!is_null($feed_url)) {
            $this->feed_url($feed_url);
        }

        if (!is_null($cache_location)) {
            $this->cache_location($cache_location);
        }

        if (!is_null($cache_max_minutes)) {
            $this->cache_max_minutes($cache_max_minutes);
        }

        if (!is_null($feed_url)) {
            return $this->init();
        }

        // If we've passed an xmldump variable in the URL, snap into XMLdump mode
        if (isset($_GET['xmldump'])) {
            $this->enable_xmldump($_GET['xmldump']);
        }
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
        $this->xml_dump = (bool) $enable;
        return true;
    }

    // Bypass Image Hotlink
    function bypass_image_hotlink($getvar='i') {
        $this->bypass_image_hotlink = (string) $getvar;
        return true;
    }

    // Bypass Image Hotlink Page
    function bypass_image_hotlink_page($page = false) {
        $this->bypass_image_hotlink_page = (string) $page;
        return true;
    }

    // Caching
    function enable_caching($enable) {
        $this->caching = (bool) $enable;
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

    // Remove outer div in XHTML content within Atom
    function remove_div($enable) {
        $this->remove_div = (bool) $enable;
        return true;
    }

    // Order the items by date
    function order_by_date($enable) {
        $this->order_by_date = (bool) $enable;
        return true;
    }

    // Strip out certain well-known ads
    function strip_ads($enable) {
        $this->strip_ads = (bool) $enable;
        return true;
    }

    // Strip out potentially dangerous tags
    function strip_htmltags($tags, $encode=false) {
        $this->strip_htmltags = (string) $tags;
        $this->encode_instead_of_strip = (bool) $encode;
        return true;
    }

    // Encode dangerous tags instead of stripping them
    function encode_instead_of_strip($encode=true) {
        $this->encode_instead_of_strip = (bool) $encode;
        return true;
    }

    // Strip out potentially dangerous attributes
    function strip_attributes($attrib) {
        $this->strip_attributes = (string) $attrib;
        return true;
    }




    /****************************************************
    MAIN INITIALIZATION FUNCTION
    Rewrites the feed so that it actually resembles XML, processes the XML,
    and builds an array from the feed.
    ****************************************************/
    function init() {
        // If Bypass Image Hotlink is enabled, send image to the page and quit.
        if ($this->bypass_image_hotlink) {
            if (isset($_GET[$this->bypass_image_hotlink]) && !empty($_GET[$this->bypass_image_hotlink])) {
                $this->display_image($_GET[$this->bypass_image_hotlink]);
                exit;
            }
        }

        // If Bypass Image Hotlink is enabled, send image to the page and quit.
        if (isset($_GET['js'])) {

            // JavaScript for the Odeo Player
            $embed='';
            $embed.='function embed_odeo(link) {';
            $embed.='document.writeln(\'';
            $embed.='<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" ';
            $embed.='    codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" ';
            $embed.='    width="440" ';
            $embed.='    height="80" ';
            $embed.='    align="middle">';
            $embed.='<param name="movie" value="http://odeo.com/flash/audio_player_fullsize.swf" />';
            $embed.='<param name="allowScriptAccess" value="any" />';
            $embed.='<param name="quality" value="high">';
            $embed.='<param name="wmode" value="transparent">';
            $embed.='<param name="flashvars" value="valid_sample_rate=true&external_url=\'+link+\'" />';
            $embed.='<embed src="http://odeo.com/flash/audio_player_fullsize.swf" ';
            $embed.='    pluginspage="http://www.macromedia.com/go/getflashplayer" ';
            $embed.='    type="application/x-shockwave-flash" ';
            $embed.='    quality="high" ';
            $embed.='    width="440" ';
            $embed.='    height="80" ';
            $embed.='    wmode="transparent" ';
            $embed.='    allowScriptAccess="any" ';
            $embed.='    flashvars="valid_sample_rate=true&external_url=\'+link+\'">';
            $embed.='</embed>';
            $embed.='</object>';
            $embed.='\');';
            $embed.='}';

            $embed.="\r\n";

            $embed.='function embed_quicktime(type, bgcolor, width, height, link, placeholder, loop) {';
            $embed.='document.writeln(\'';
            $embed.='<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" ';
            $embed.='    style="cursor:hand; cursor:pointer;" ';
            $embed.='    type="\'+type+\'" ';
            $embed.='    codebase="http://www.apple.com/qtactivex/qtplugin.cab" ';
            $embed.='    bgcolor="\'+bgcolor+\'" ';
            $embed.='    width="\'+width+\'" ';
            $embed.='    height="\'+height+\'">';
            $embed.='<param name="href" value="\'+link+\'" />';
            $embed.='<param name="src" value="\'+placeholder+\'" />';
            $embed.='<param name="autoplay" value="false" />';
            $embed.='<param name="target" value="myself" />';
            $embed.='<param name="controller" value="false" />';
            $embed.='<param name="loop" value="\'+loop+\'" />';
            $embed.='<param name="scale" value="aspect" />';
            $embed.='<param name="bgcolor" value="\'+bgcolor+\'">';
            $embed.='<embed type="\'+type+\'" ';
            $embed.='    style="cursor:hand; cursor:pointer;" ';
            $embed.='    href="\'+link+\'" ';
            $embed.='    src="\'+placeholder+\'"';
            $embed.='    width="\'+width+\'" ';
            $embed.='    height="\'+height+\'" ';
            $embed.='    autoplay="false" ';
            $embed.='    target="myself" ';
            $embed.='    controller="false" ';
            $embed.='    loop="\'+loop+\'" ';
            $embed.='    scale="aspect" ';
            $embed.='    bgcolor="\'+bgcolor+\'" ';
            $embed.='    pluginspage="http://www.apple.com/quicktime/download/">';
            $embed.='</embed>';
            $embed.='</object>';
            $embed.='\');';
            $embed.='}';

            $embed.="\r\n";

            $embed.='function embed_flash(bgcolor, width, height, link, loop, type) {';
            $embed.='document.writeln(\'';
            $embed.='<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" ';
            $embed.='    codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" ';
            $embed.='    bgcolor="\'+bgcolor+\'" ';
            $embed.='    width="\'+width+\'" ';
            $embed.='    height="\'+height+\'">';
            $embed.='<param name="movie" value="\'+link+\'">';
            $embed.='<param name="quality" value="high">';
            $embed.='<param name="loop" value="\'+loop+\'">';
            $embed.='<param name="bgcolor" value="\'+bgcolor+\'">';
            $embed.='<embed src="\'+link+\'" ';
            $embed.='    pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash" ';
            $embed.='    type="\'+type+\'" ';
            $embed.='    quality="high" ';
            $embed.='    width="\'+width+\'" ';
            $embed.='    height="\'+height+\'" ';
            $embed.='    bgcolor="\'+bgcolor+\'" ';
            $embed.='    loop="\'+loop+\'">';
            $embed.='</embed>';
            $embed.='</object>';
            $embed.='\');';
            $embed.='}';

            $embed.="\r\n";

            $embed.='function embed_wmedia(width, height, link) {';
            $embed.='document.writeln(\'';
            $embed.='<object classid="CLSID:22D6F312-B0F6-11D0-94AB-0080C74C7E95"';
            $embed.='    type="application/x-oleobject"';
            $embed.='    width="\'+width+\'"';
            $embed.='    height="\'+height+\'"';
            $embed.='    standby="Loading Windows Media Player...">';
            $embed.='<param name="FileName" value="\'+link+\'">';
            $embed.='<param name="autosize" value="true">';
            $embed.='<param name="ShowControls" value="true">';
            $embed.='<param name="ShowStatusBar" value="false">';
            $embed.='<param name="ShowDisplay" value="false">';
            $embed.='<param name="autostart" value="false">';
            $embed.='<embed type="application/x-mplayer2" ';
            $embed.='    src="\'+link+\'" ';
            $embed.='    autosize="1" ';
            $embed.='    width="\'+width+\'" ';
            $embed.='    height="\'+height+\'" ';
            $embed.='    showcontrols="1" ';
            $embed.='    showstatusbar="0" ';
            $embed.='    showdisplay="0" ';
            $embed.='    autostart="0">';
            $embed.='</embed>';
            $embed.='</object>';
            $embed.='\');';
            $embed.='}';

            $embed.="\r\n";

            // enable gzip compression
            ob_start ("ob_gzhandler");
            header("Content-type: text/javascript; charset: UTF-8");
            header("Cache-Control: must-revalidate");
            header("Expires: " .  gmdate("D, d M Y H:i:s", time() + 60*60*24) . " GMT");
            echo $embed;
            exit;
        }

        // If this is a .Mac Photocast, change it to the real URL.
        if (stristr($this->rss_url, 'http://photocast.mac.com')) {
            $this->rss_url = preg_replace('%http://photocast.mac.com%i', 'http://web.mac.com', $this->rss_url);
        }

        // Clear all outdated cache from the server's cache folder
        $this->clear_cache($this->cache_location, $this->max_minutes);

        if (!empty($this->rss_url)) {
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
                    } else if (isset($mp_rss['feed_url'])) {
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
                    if ($discovery != $this->rss_url) {
                        $this->rss_url = $discovery;
                        if ($this->caching && substr($this->rss_url, 0, 7) == 'http://') {
                            if ($this->is_writeable_createable($cache_filename)) {
                                $fp = fopen($cache_filename, 'w');
                                fwrite($fp, serialize(array('feed_url' => $discovery)));
                                fclose($fp);
                            }
                            else trigger_error("$cache_filename is not writeable", E_USER_WARNING);
                        }
                        return $this->init();
                    }
                } else {
                    $this->sp_error("A feed could not be found at $this->rss_url", E_USER_WARNING, __FILE__, __LINE__);
                    return false;
                }

                // Trim out any whitespace at the beginning or the end of the file
                $mp_rss = trim($mp_rss);

                // Get encoding
                // Attempt to support everything from libiconv (http://www.gnu.org/software/libiconv/)
                // Support everything from mbstring (http://www.php.net/manual/en/ref.mbstring.php#mbstring.supported-encodings)
                $use_iconv = false;
                $use_mbstring = false;
                $utf8_fail = true;
                if (preg_match('/encoding=["|\'](.*)["|\']/Ui', $mp_rss, $encoding)) {
                    $match = $encoding;
                    switch (strtolower($encoding[1])) {

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

                        // ARMSCII-8
                        case 'armscii-8':
                        case 'armscii':
                            $encoding = 'ARMSCII-8';
                            $use_iconv = true;
                            break;

                        // ASCII
                        case 'us-ascii':
                        case 'ascii':
                            $encoding = 'US-ASCII';
                            $use_iconv = true;
                            $use_mbstring = true;
                            $utf8_fail = false;
                            break;

                        // BASE64
                        case 'base64':
                        case 'base-64':
                            $encoding = 'BASE64';
                            $use_mbstring = true;
                            break;

                        // Big5 - Traditional Chinese, mainly used in Taiwan
                        case 'big5':
                        case '950':
                            $encoding = 'BIG5';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // Big5 with Hong Kong extensions, Traditional Chinese
                        case 'big5-hkscs':
                            $encoding = 'BIG5-HKSCS';
                            $use_iconv = true;
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

                        // EUC-CN
                        case 'euc-cn':
                        case 'euccn':
                            $encoding = 'EUC-CN';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // EUC-JISX0213
                        case 'euc-jisx0213':
                        case 'eucjisx0213':
                            $encoding = 'EUC-JISX0213';
                            $use_iconv = true;
                            break;

                        // EUC-JP
                        case 'euc-jp':
                        case 'eucjp':
                            $encoding = 'EUC-JP';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // EUCJP-win
                        case 'euc-jp-win':
                        case 'eucjp-win':
                        case 'eucjpwin':
                            $encoding = 'EUCJP-win';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // EUC-KR
                        case 'euc-kr':
                        case 'euckr':
                            $encoding = 'EUC-KR';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // EUC-TW
                        case 'euc-tw':
                        case 'euctw':
                            $encoding = 'EUC-TW';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // GB18030 - Simplified Chinese, national standard character set
                        case 'gb18030-2000':
                        case 'gb18030':
                            $encoding = 'GB18030';
                            $use_iconv = true;
                            break;

                        // GB2312 - Simplified Chinese, national standard character set
                        case 'gb2312':
                        case '936':
                            $encoding = 'GB2312';
                            $use_mbstring = true;
                            break;

                        // GBK
                        case 'gbk':
                            $encoding = 'GBK';
                            $use_iconv = true;
                            break;

                        // Georgian-Academy
                        case 'georgian-academy':
                            $encoding = 'Georgian-Academy';
                            $use_iconv = true;
                            break;

                        // Georgian-PS
                        case 'georgian-ps':
                            $encoding = 'Georgian-PS';
                            $use_iconv = true;
                            break;

                        // HTML-ENTITIES
                        case 'html-entities':
                        case 'htmlentities':
                            $encoding = 'HTML-ENTITIES';
                            $use_mbstring = true;
                            break;

                        // HZ
                        case 'hz':
                            $encoding = 'HZ';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // ISO-2022-CN
                        case 'iso-2022-cn':
                        case 'iso2022-cn':
                        case 'iso2022cn':
                            $encoding = 'ISO-2022-CN';
                            $use_iconv = true;
                            break;

                        // ISO-2022-CN-EXT
                        case 'iso-2022-cn-ext':
                        case 'iso2022-cn-ext':
                        case 'iso2022cn-ext':
                        case 'iso2022cnext':
                            $encoding = 'ISO-2022-CN';
                            $use_iconv = true;
                            break;

                        // ISO-2022-JP
                        case 'iso-2022-jp':
                        case 'iso2022-jp':
                        case 'iso2022jp':
                            $encoding = 'ISO-2022-JP';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // ISO-2022-JP-1
                        case 'iso-2022-jp-1':
                        case 'iso2022-jp-1':
                        case 'iso2022jp-1':
                        case 'iso2022jp1':
                            $encoding = 'ISO-2022-JP-1';
                            $use_iconv = true;
                            break;

                        // ISO-2022-JP-2
                        case 'iso-2022-jp-2':
                        case 'iso2022-jp-2':
                        case 'iso2022jp-2':
                        case 'iso2022jp2':
                            $encoding = 'ISO-2022-JP-2';
                            $use_iconv = true;
                            break;

                        // ISO-2022-JP-3
                        case 'iso-2022-jp-3':
                        case 'iso2022-jp-3':
                        case 'iso2022jp-3':
                        case 'iso2022jp3':
                            $encoding = 'ISO-2022-JP-3';
                            $use_iconv = true;
                            break;

                        // ISO-2022-KR
                        case 'iso-2022-kr':
                        case 'iso2022-kr':
                        case 'iso2022kr':
                            $encoding = 'ISO-2022-KR';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // ISO-8859-1
                        case 'iso-8859-1':
                        case 'iso8859-1':
                            $encoding = 'ISO-8859-1';
                            $use_iconv = true;
                            $use_mbstring = true;
                            $utf8_fail = false;
                            break;

                        // ISO-8859-2
                        case 'iso-8859-2':
                        case 'iso8859-2':
                            $encoding = 'ISO-8859-2';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // ISO-8859-3
                        case 'iso-8859-3':
                        case 'iso8859-3':
                            $encoding = 'ISO-8859-3';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // ISO-8859-4
                        case 'iso-8859-4':
                        case 'iso8859-4':
                            $encoding = 'ISO-8859-4';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // ISO-8859-5
                        case 'iso-8859-5':
                        case 'iso8859-5':
                            $encoding = 'ISO-8859-5';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // ISO-8859-6
                        case 'iso-8859-6':
                        case 'iso8859-6':
                            $encoding = 'ISO-8859-6';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // ISO-8859-7
                        case 'iso-8859-7':
                        case 'iso8859-7':
                            $encoding = 'ISO-8859-7';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // ISO-8859-8
                        case 'iso-8859-8':
                        case 'iso8859-8':
                            $encoding = 'ISO-8859-8';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // ISO-8859-9
                        case 'iso-8859-9':
                        case 'iso8859-9':
                            $encoding = 'ISO-8859-9';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // ISO-8859-10
                        case 'iso-8859-10':
                        case 'iso8859-10':
                            $encoding = 'ISO-8859-10';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // mbstring/iconv functions don't appear to support 11 & 12

                        // ISO-8859-13
                        case 'iso-8859-13':
                        case 'iso8859-13':
                            $encoding = 'ISO-8859-13';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // ISO-8859-14
                        case 'iso-8859-14':
                        case 'iso8859-14':
                            $encoding = 'ISO-8859-14';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // ISO-8859-15
                        case 'iso-8859-15':
                        case 'iso8859-15':
                            $encoding = 'ISO-8859-15';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // ISO-8859-16
                        case 'iso-8859-16':
                        case 'iso8859-16':
                            $encoding = 'ISO-8859-16';
                            $use_iconv = true;
                            break;

                        // JIS
                        case 'jis':
                            $encoding = 'JIS';
                            $use_mbstring = true;
                            break;

                        // JOHAB - Korean
                        case 'johab':
                            $encoding = 'JOHAB';
                            $use_iconv = true;
                            break;

                        // Russian
                        case 'koi8-r':
                        case 'koi8r':
                            $encoding = 'KOI8-R';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // Turkish
                        case 'koi8-t':
                        case 'koi8t':
                            $encoding = 'KOI8-T';
                            $use_iconv = true;
                            break;

                        // Ukrainian
                        case 'koi8-u':
                        case 'koi8u':
                            $encoding = 'KOI8-U';
                            $use_iconv = true;
                            break;

                        // Russian+Ukrainian
                        case 'koi8-ru':
                        case 'koi8ru':
                            $encoding = 'KOI8-RU';
                            $use_iconv = true;
                            break;

                        // Macintosh (Mac OS Classic)
                        case 'macintosh':
                            $encoding = 'Macintosh';
                            $use_iconv = true;
                            break;

                        // MacArabic (Mac OS Classic)
                        case 'macarabic':
                            $encoding = 'MacArabic';
                            $use_iconv = true;
                            break;

                        // MacCentralEurope (Mac OS Classic)
                        case 'maccentraleurope':
                            $encoding = 'MacCentralEurope';
                            $use_iconv = true;
                            break;

                        // MacCroatian (Mac OS Classic)
                        case 'maccroatian':
                            $encoding = 'MacCroatian';
                            $use_iconv = true;
                            break;

                        // MacCyrillic (Mac OS Classic)
                        case 'maccyrillic':
                            $encoding = 'MacCyrillic';
                            $use_iconv = true;
                            break;

                        // MacGreek (Mac OS Classic)
                        case 'macgreek':
                            $encoding = 'MacGreek';
                            $use_iconv = true;
                            break;

                        // MacHebrew (Mac OS Classic)
                        case 'machebrew':
                            $encoding = 'MacHebrew';
                            $use_iconv = true;
                            break;

                        // MacIceland (Mac OS Classic)
                        case 'maciceland':
                            $encoding = 'MacIceland';
                            $use_iconv = true;
                            break;

                        // MacRoman (Mac OS Classic)
                        case 'macroman':
                            $encoding = 'MacRoman';
                            $use_iconv = true;
                            break;

                        // MacRomania (Mac OS Classic)
                        case 'macromania':
                            $encoding = 'MacRomania';
                            $use_iconv = true;
                            break;

                        // MacThai (Mac OS Classic)
                        case 'macthai':
                            $encoding = 'MacThai';
                            $use_iconv = true;
                            break;

                        // MacTurkish (Mac OS Classic)
                        case 'macturkish':
                            $encoding = 'MacTurkish';
                            $use_iconv = true;
                            break;

                        // MacUkraine (Mac OS Classic)
                        case 'macukraine':
                            $encoding = 'MacUkraine';
                            $use_iconv = true;
                            break;

                        // MuleLao-1
                        case 'mulelao-1':
                        case 'mulelao1':
                            $encoding = 'MuleLao-1';
                            $use_iconv = true;
                            break;

                        // Shift_JIS
                        case 'shift_jis':
                        case 'sjis':
                        case '932':
                            $encoding = 'Shift_JIS';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // Shift_JISX0213
                        case 'shift-jisx0213':
                        case 'shiftjisx0213':
                            $encoding = 'Shift_JISX0213';
                            $use_iconv = true;
                            break;

                        // SJIS-win
                        case 'sjis-win':
                        case 'sjiswin':
                        case 'shift_jis-win':
                            $encoding = 'SJIS-win';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // TCVN - Vietnamese
                        case 'tcvn':
                            $encoding = 'TCVN';
                            $use_iconv = true;
                            break;

                        // TDS565 - Turkish
                        case 'tds565':
                            $encoding = 'TDS565';
                            $use_iconv = true;
                            break;

                        // TIS-620 Thai
                        case 'tis-620':
                        case 'tis620':
                            $encoding = 'TIS-620';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // UCS-2
                        case 'ucs-2':
                        case 'ucs2':
                        case 'utf-16':
                        case 'utf16':
                            $encoding = 'UCS-2';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // UCS-2BE
                        case 'ucs-2be':
                        case 'ucs2be':
                        case 'utf-16be':
                        case 'utf16be':
                            $encoding = 'UCS-2BE';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // UCS-2LE
                        case 'ucs-2le':
                        case 'ucs2le':
                        case 'utf-16le':
                        case 'utf16le':
                            $encoding = 'UCS-2LE';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // UCS-2-INTERNAL
                        case 'ucs-2-internal':
                        case 'ucs2internal':
                            $encoding = 'UCS-2-INTERNAL';
                            $use_iconv = true;
                            break;

                        // UCS-4
                        case 'ucs-4':
                        case 'ucs4':
                        case 'utf-32':
                        case 'utf32':
                            $encoding = 'UCS-4';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // UCS-4BE
                        case 'ucs-4be':
                        case 'ucs4be':
                        case 'utf-32be':
                        case 'utf32be':
                            $encoding = 'UCS-4BE';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // UCS-4LE
                        case 'ucs-4le':
                        case 'ucs4le':
                        case 'utf-32le':
                        case 'utf32le':
                            $encoding = 'UCS-4LE';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // UCS-4-INTERNAL
                        case 'ucs-4-internal':
                        case 'ucs4internal':
                            $encoding = 'UCS-4-INTERNAL';
                            $use_iconv = true;
                            break;

                        // UCS-16
                        case 'ucs-16':
                        case 'ucs16':
                            $encoding = 'UCS-16';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // UCS-16BE
                        case 'ucs-16be':
                        case 'ucs16be':
                            $encoding = 'UCS-16BE';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // UCS-16LE
                        case 'ucs-16le':
                        case 'ucs16le':
                            $encoding = 'UCS-16LE';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // UCS-32
                        case 'ucs-32':
                        case 'ucs32':
                            $encoding = 'UCS-32';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // UCS-32BE
                        case 'ucs-32be':
                        case 'ucs32be':
                            $encoding = 'UCS-32BE';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // UCS-32LE
                        case 'ucs-32le':
                        case 'ucs32le':
                            $encoding = 'UCS-32LE';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // UTF-7
                        case 'utf-7':
                        case 'utf7':
                            $encoding = 'UTF-7';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // UTF7-IMAP
                        case 'utf-7-imap':
                        case 'utf7-imap':
                        case 'utf7imap':
                            $encoding = 'UTF7-IMAP';
                            $use_mbstring = true;
                            break;

                        // VISCII - Vietnamese ASCII
                        case 'viscii':
                            $encoding = 'VISCII';
                            $use_iconv = true;
                            break;

                        // Windows-specific Central & Eastern Europe
                        case 'cp1250':
                        case 'windows-1250':
                        case 'win-1250':
                        case '1250':
                            $encoding = 'Windows-1250';
                            $use_iconv = true;
                            break;

                        // Windows-specific Cyrillic
                        case 'cp1251':
                        case 'windows-1251':
                        case 'win-1251':
                        case '1251':
                            $encoding = 'Windows-1251';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // Windows-specific Western Europe
                        case 'cp1252':
                        case 'windows-1252':
                        case '1252':
                            $encoding = 'Windows-1252';
                            $use_iconv = true;
                            $use_mbstring = true;
                            break;

                        // Windows-specific Greek
                        case 'cp1253':
                        case 'windows-1253':
                        case '1253':
                            $encoding = 'Windows-1253';
                            $use_iconv = true;
                            break;

                        // Windows-specific Turkish
                        case 'cp1254':
                        case 'windows-1254':
                        case '1254':
                            $encoding = 'Windows-1254';
                            $use_iconv = true;
                            break;

                        // Windows-specific Hebrew
                        case 'cp1255':
                        case 'windows-1255':
                        case '1255':
                            $encoding = 'Windows-1255';
                            $use_iconv = true;
                            break;

                        // Windows-specific Arabic
                        case 'cp1256':
                        case 'windows-1256':
                        case '1256':
                            $encoding = 'Windows-1256';
                            $use_iconv = true;
                            break;

                        // Windows-specific Baltic
                        case 'cp1257':
                        case 'windows-1257':
                        case '1257':
                            $encoding = 'Windows-1257';
                            $use_iconv = true;
                            break;

                        // Windows-specific Vietnamese
                        case 'cp1258':
                        case 'windows-1258':
                        case '1258':
                            $encoding = 'Windows-1258';
                            $use_iconv = true;
                            break;

                        // Default to UTF-8
                        default:
                            $encoding = 'UTF-8';
                            break;
                    }
                } else {
                    $mp_rss = preg_replace ('/<\?xml(.*)( standalone="no")(.*)\?>/msiU', '<?xml\\1\\3?>', $mp_rss, 1);
                    $mp_rss = preg_replace ('/<\?xml(.*)\?>/msiU', '<?xml\\1 encoding="UTF-8"?>', $mp_rss, 1);
                    preg_match('/encoding=["|\'](.*)["|\']/Ui', $mp_rss, $match);
                    $use_iconv = true;
                    $use_mbstring = true;
                    $utf8_fail = false;
                    $encoding = 'UTF-8';
                }
                $this->encoding = $encoding;

                // If function is available and able, convert characters to UTF-8, and overwrite $this->encoding
                if (function_exists('iconv') && $use_iconv && iconv($encoding, 'UTF-8', $mp_rss)) {
                    $mp_rss = iconv($encoding, 'UTF-8//TRANSLIT', $mp_rss);
                    $mp_rss = str_replace ($match[0], 'encoding="UTF-8"', $mp_rss);
                    $this->encoding = 'UTF-8';
                }
                else if (function_exists('mb_convert_encoding') && $use_mbstring) {
                    $mp_rss = mb_convert_encoding($mp_rss, 'UTF-8', $encoding);
                    $mp_rss = str_replace ($match[0], 'encoding="UTF-8"', $mp_rss);
                    $this->encoding = 'UTF-8';
                }
                else if (($use_mbstring || $use_iconv) && $utf8_fail) {
                        $this->encoding = 'UTF-8';
                        $mp_rss = str_replace ($match[0], 'encoding="UTF-8"', $mp_rss);
                }
                $mp_rss = preg_replace('/<(.*)>[\s]*<\!\[CDATA\[/msiU', '<\\1 spencoded="false"><![CDATA[', $mp_rss); // Add an internal attribute to CDATA sections
                $mp_rss = str_replace(']] spencoded="false">', ']]>', $mp_rss); // Remove it when we're on the end of a CDATA block (therefore making it ill-formed)

                // If we're RSS
                if (preg_match('/<rdf:rdf/i', $mp_rss) || preg_match('/<rss/i', $mp_rss)) {
                    $sp_elements = array(
                        'author',
                        'link',
                    );
                // Or if we're Atom
                } else {
                    $sp_elements = array(
                        'content',
                        'copyright',
                        'name',
                        'subtitle',
                        'summary',
                        'tagline',
                        'title',
                    );
                }
                foreach ($sp_elements as $full) {
                    // The (<\!\[CDATA\[)? never matches any CDATA block, therefore the CDATA gets added, but never replaced
                    $mp_rss = preg_replace("/<$full(.*)>[\s]*(<\!\[CDATA\[)?(.*)(]]>)?[\s]*<\/$full>/msiU", "<$full\\1><![CDATA[\\3]]></$full>", $mp_rss);
                    // The following line is a work-around for the above bug
                    $mp_rss = preg_replace("/<$full(.*)><\!\[CDATA\[[\s]*<\!\[CDATA\[/msiU", "<$full\\1><![CDATA[", $mp_rss);
                    // Deal with CDATA within CDATA (this can be caused by us inserting CDATA above)
                    $mp_rss = preg_replace_callback("/<($full)(.*)><!\[CDATA\[(.*)\]\]><\/$full>/msiU", array(&$this, 'cdata_in_cdata'), $mp_rss);
                }

                // If XML Dump is enabled, send feed to the page and quit.
                if ($this->xml_dump) {
                    header("Content-type: text/xml; charset=" . $this->encoding);
                    echo $mp_rss;
                    exit;
                }

                $this->xml = xml_parser_create_ns($this->encoding);
                $this->namespaces = array('xml' => 'HTTP://WWW.W3.ORG/XML/1998/NAMESPACE', 'atom' => 'ATOM', 'rss2' => 'RSS', 'rdf' => 'RDF', 'rss1' => 'RSS', 'dc' => 'DC', 'xhtml' => 'XHTML', 'content' => 'CONTENT');
                xml_parser_set_option($this->xml, XML_OPTION_SKIP_WHITE, 1);
                xml_set_object($this->xml, $this);
                xml_set_character_data_handler($this->xml, 'dataHandler');
                xml_set_element_handler($this->xml, 'startHandler', 'endHandler');
                xml_set_start_namespace_decl_handler($this->xml, 'startNameSpace');
                xml_set_end_namespace_decl_handler($this->xml, 'endNameSpace');
                if (xml_parse($this->xml, $mp_rss))
                {
                    xml_parser_free($this->xml);
                    $this->parse_xml_data_array();
                    $this->data['feedinfo']['encoding'] = $this->encoding;
                    if ($this->order_by_date && !empty($this->data['items'])) {
                        usort($this->data['items'], create_function('$a,$b', 'if ($a->date == $b->date) return 0; return ($a->date < $b->date) ? 1 : -1;'));
                    }
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
                    $this->sp_error(sprintf('XML error: %s at line %d, column %d', xml_error_string(xml_get_error_code($this->xml)), xml_get_current_line_number($this->xml), xml_get_current_column_number($this->xml)), E_USER_WARNING, __FILE__, __LINE__);
                    xml_parser_free($this->xml);
                    $this->data = array();
                    return false;
                }
            }
        }
        else {
            return false;
        }
    }




    /****************************************************
    SIMPLEPIE ERROR (internal function)
    ****************************************************/
    function sp_error($message, $level, $file, $line) {
        $this->error = $message;
        switch ($level) {
            case E_USER_ERROR:
                $note = 'PHP Error';
                break;
            case E_USER_WARNING:
                $note = 'PHP Warning';
                break;
            case E_USER_NOTICE:
                $note = 'PHP Notice';
                break;
            default:
                $note = 'Unknown Error';
                break;
        }
        error_log("$note: $message in $file on line $line", 0);
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

    function handle_content_type($mime='text/html') {
        if (!headers_sent() && $this->get_encoding()) header('Content-type: ' . $mime . '; charset=' . $this->get_encoding());
    }




    /****************************************************
    GET FEED VERSION NUMBER
    ****************************************************/
    function get_version() {
        if (isset($this->data['feedinfo'])) {
            return (isset($this->data['feedinfo']['version'])) ? $this->data[feedinfo][type] . ' ' . $this->data[feedinfo][version] : $this->data['feedinfo']['type'];
        }
        else return false;
    }




    /****************************************************
    SUBSCRIPTION URLS
    This allows people to subscribe to the feed in various services.
    ****************************************************/
    function subscribe_url() {
        return (empty($this->rss_url)) ? false : $this->fix_protocol($this->rss_url, 1);
    }

    function subscribe_feed() {
        return (empty($this->rss_url)) ? false : $this->fix_protocol($this->rss_url, 2);
    }

    function subscribe_podcast() {
        return (empty($this->rss_url)) ? false : $this->fix_protocol($this->rss_url, 3);
    }

    function subscribe_aol() {
        return (empty($this->rss_url)) ? false : 'http://feeds.my.aol.com/add.jsp?url=' . rawurlencode($this->subscribe_url());
    }

    function subscribe_bloglines() {
        return (empty($this->rss_url)) ? false : 'http://www.bloglines.com/sub/' . rawurlencode($this->subscribe_url());
    }

    function subscribe_google() {
        return (empty($this->rss_url)) ? false : 'http://fusion.google.com/add?feedurl=' . rawurlencode($this->subscribe_url());
    }

    function subscribe_msn() {
        return (empty($this->rss_url)) ? false : 'http://my.msn.com/addtomymsn.armx?id=rss&amp;ut=' . rawurlencode($this->subscribe_url()) . '&amp;ru=' . rawurlencode($this->get_feed_link());
    }

    function subscribe_netvibes() {
        return (empty($this->rss_url)) ? false : 'http://www.netvibes.com/subscribe.php?url=' . rawurlencode($this->subscribe_url());
    }

    function subscribe_newsburst() {
        return (empty($this->rss_url)) ? false : 'http://www.newsburst.com/Source/?add=' . rawurlencode($this->subscribe_url());
    }

    function subscribe_newsgator() {
        return (empty($this->rss_url)) ? false : 'http://www.newsgator.com/ngs/subscriber/subext.aspx?url=' . rawurlencode($this->subscribe_url());
    }

    function subscribe_odeo() {
        return (empty($this->rss_url)) ? false : 'http://www.odeo.com/listen/subscribe?feed=' . rawurlencode($this->subscribe_url());
    }

    function subscribe_pluck() {
        return (empty($this->rss_url)) ? false : 'http://client.pluck.com/pluckit/prompt.aspx?GCID=C12286x053&amp;a=' . rawurlencode($this->subscribe_url());
    }

    function subscribe_podnova() {
        return (empty($this->rss_url)) ? false : 'http://www.podnova.com/index_your_podcasts.srf?action=add&url=' . rawurlencode($this->subscribe_url());
    }

    function subscribe_rojo() {
        return (empty($this->rss_url)) ? false : 'http://www.rojo.com/add-subscription?resource=' . rawurlencode($this->subscribe_url());
    }

    function subscribe_yahoo() {
        return (empty($this->rss_url)) ? false : 'http://add.my.yahoo.com/rss?url=' . rawurlencode($this->subscribe_url());
    }




    /****************************************************
    PARSE OUT GENERAL FEED-RELATED DATA
    ****************************************************/
    // Reads the feed's title
    function get_feed_title() {
        return (isset($this->data['info']['title'])) ? $this->data['info']['title'] : false;
    }

    // Reads the feed's link (URL)
    function get_feed_link() {
        return (isset($this->data['info']['link'][0])) ? $this->data['info']['link'][0] : false;
    }

    // Reads the feed's link (URL)
    function get_feed_links($key) {
        return (isset($this->data['info']['link'][$key])) ? $this->data['info']['link'][$key] : false;
    }

    // Reads the feed's description
    function get_feed_description() {
        return (isset($this->data['info']['description'])) ? $this->data['info']['description'] : false;
    }

    // Reads the feed's copyright information.
    function get_feed_copyright() {
        return (isset($this->data['info']['copyright'])) ? $this->data['info']['copyright'] : false;
    }

    // Reads the feed's language
    function get_feed_language() {
        return (isset($this->data['info']['language'])) ? $this->data['info']['language'] : false;
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
        return (isset($this->data['info']['image']['title'])) ? $this->data['info']['image']['title'] : false;
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
    ****************************************************/
    // Get the size of the array of items (for use in a for-loop)
    function get_item_quantity($max=0) {
        $qty = (isset($this->data['items'])) ? sizeof($this->data['items']) : 0;
        if ($max != 0) return ($qty > $max) ? $max : $qty;
        else return $qty;
    }

    function get_item($key) {
        return $this->data['items'][$key];
    }

    function get_items($start = 0, $end = 0) {
        return ($end == 0) ? array_slice($this->data['items'], $start) : array_slice($this->data['items'], $start, $end);
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

        $url = preg_replace('/feed:(\/\/)?(http:\/\/)?/i', 'http://', $url);
        $url = preg_replace('/(feed:)?(\/\/)?https:\/\//i', 'https://', $url);
        $url = preg_replace('/p(od)?cast:(\/\/)?(http:\/\/)?/i', 'http://', $url);
        $url = preg_replace('/(p(od)?cast:)?(\/\/)?https:\/\//i', 'https://', $url);
        if (!stristr($url, 'http://') && !stristr($url, 'https://') && !file_exists($url)) $url = "http://$url";

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
    ULTRA-LIBERAL FEED LOCATOR
    Based upon http://diveintomark.org/archives/2002/08/15/ultraliberal_rss_locator
    This function enables support for RSS auto-discovery-on-crack.
    ****************************************************/
    function rss_locator($data, $url) {

        $this->url = $url;
        $this->parsed_url = parse_url($url);
        if (!isset($this->parsed_url['path'])) {
            $this->parsed_url['path'] = '/';
        }

        // Check is the URL we're given is a feed
        if ($this->is_feed($data, false)) {
            return $url;
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

        return false;
    }

    function check_link_elements($data) {
        if (preg_match_all('/<link (.*)>/siU', $data, $matches)) {
            foreach($matches[1] as $match) {
                if (preg_match('/type=[\'|"]?(application\/rss\+xml|application\/atom\+xml|application\/rdf\+xml|application\/xml\+rss|application\/xml\+atom|application\/xml\+rdf|application\/xml|application\/x\.atom\+xml|text\/xml)[\'|"]?/iU', $match, $type)) {
                    $href = $this->get_attribute($match, 'href');
                    if (!empty($href[1])) {
                        $href = $this->absolutize_url($href[1], $this->parsed_url);
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
                    return $this->absolutize_url($value, $this->parsed_url);
                }
            }
        }
        return false;
    }

    function check_link_body($array) {
        foreach ($array as $value) {
            $value2 = @parse_url($value);
            if (!empty($value2['path'])) {
                if (strlen(pathinfo($value2['path'], PATHINFO_EXTENSION)) > 0) {
                    $value3 = substr_replace($value, '', strpos($value, $value2['path'])+strpos($value2['path'], pathinfo($value2['path'], PATHINFO_EXTENSION))-1, strlen(pathinfo($value2['path'], PATHINFO_EXTENSION))+1);
                } else {
                    $value3 = $value;
                }
                if ((stristr($value3, 'rss') || stristr($value3, 'rdf') || stristr($value3, 'xml') || stristr($value3, 'atom') || stristr($value3, 'feed')) && $this->is_feed($value)) {
                    return $this->absolutize_url($value, $this->parsed_url);
                }
            }
        }
        return false;
    }

    function get_links($data) {
        if (preg_match_all('/href="(.*)"/iU', $data, $matches)) {
            $this->parse_links($matches);
        }
        if (preg_match_all('/href=\'(.*)\'/iU', $data, $matches)) {
            $this->parse_links($matches);
        }
        if (preg_match_all('/href=(.*)[ |\/|>]/iU', $data, $matches)) {
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
                $parsed = @parse_url($match);
                if (!isset($parsed['host']) || $parsed['host'] == $this->parsed_url['host']) {
                    $this->local[] = $this->absolutize_url($match, $this->parsed_url);
                } else {
                    $this->elsewhere[] = $this->absolutize_url($match, $this->parsed_url);
                }
            }
        }
    }

    function is_feed($data, $is_url = true) {
        if ($is_url) {
            $data = $this->get_file($data);
        }
        if (stristr($data, '<!DOCTYPE HTML')) {
            return false;
        }
        else if (stristr($data, '<rss') || stristr($data, '<rdf:RDF') || preg_match('/<([a-z0-9]+\:)?feed/mi', $data)) {
            return true;
        } else {
            return false;
        }
    }

    function absolutize_url($href, $location) {
        @$href_parts = parse_url($href);
        if (!empty($href_parts['scheme'])) {
            return $href;
        } else {
            if (isset($location['host'])) {
                $full_url = $location['scheme'] . '://' . $location['host'];
            } else {
                $full_url = '';
            }
            if (isset($location['port'])) {
                $full_url .= ':' . $location['port'];
            }
            if (!empty($href_parts['path'])) {
                if (substr($href_parts['path'], 0, 1) == '/') {
                    $full_url .= $href_parts['path'];
                } else if (!empty($location['path'])) {
                    $full_url .= dirname($location['path'] . 'a') . '/' . $href_parts['path'];
                } else {
                    $full_url .= $href_parts['path'];
                }
            } else if (!empty($location['path'])) {
                $full_url .= $location['path'];
            } else {
                $full_url .= '/';
            }
            if (!empty($href_parts['query'])) {
                $full_url .= '?' . $href_parts['query'];
            } else if (!empty($location['query'])) {
                $full_url .= '?' . $location['query'];
            }
            if (!empty($href_parts['fragment'])) {
                $full_url .= '#' . $href_parts['fragment'];
            } else if (!empty($location['fragment'])) {
                $full_url .= '#' . $location['fragment'];
            }
            return $full_url;
        }
    }




    /****************************************************
    DISPLAY IMAGES
    Some websites have a setting that blocks images from being loaded
    into other pages.  This gets around those blocks by spoofing the referrer.
    ****************************************************/
    function display_image($image_url) {
        $image = $this->get_file(urldecode($image_url));
        $suffix = pathinfo($image_url, PATHINFO_EXTENSION);

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
        //echo $image_url;
        exit;
    }




    /****************************************************
    DELETE OUTDATED CACHE FILES
    Copyright 2004 by "adam at roomvoter dot com". This material
    may be distributed only subject to the terms and conditions set
    forth in the Open Publication License, v1.0 or later (the latest
    version is presently available at http://www.opencontent.org/openpub/).
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
    OPENS A FILE, WITH EITHER FOPEN OR CURL
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
            $old_ua = ini_get('user_agent');
            ini_set('user_agent', $this->useragent);
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
            ini_set('user_agent', $old_ua);
        }
        return $data;
    }




    /****************************************************
    CHECKS IF A FILE IS WRITEABLE, OR CREATEABLE
    ****************************************************/
    function is_writeable_createable($file) {
        if (file_exists($file))
            return is_writeable($file);
        else
            return is_writeable(dirname($file));
    }




    /****************************************************
    GET ATTRIBUTE
    ****************************************************/
    function get_attribute($data, $attribute_name) {
        if (preg_match("/$attribute_name='(.*)'/iU", $data, $match)) {
            return $match;
        } else if (preg_match("/$attribute_name=\"(.*)\"/iU", $data, $match)) {
            return $match;
        } else if (preg_match("/$attribute_name=(.*)[ |\/|>]/iU", $data, $match)) {
            return $match;
        }
    }




    /****************************************************
    CALLBACK FUNCTION TO DEAL WITH CDATA WITHIN CDATA
    ****************************************************/
    function cdata_in_cdata($match) {
        $match[3] = preg_replace_callback('/<!\[CDATA\[(.*)\]\]>/msiU', array(&$this, 'real_cdata_in_cdata'), $match[3]);
        return "<$match[1]$match[2]><![CDATA[$match[3]]]></$match[1]>";
    }




    /****************************************************
    CALLBACK FUNCTION TO REALLY DEAL WITH CDATA WITHIN CDATA
    ****************************************************/
    function real_cdata_in_cdata($match) {
        return htmlentities($match[1], ENT_NOQUOTES, $this->encoding);
    }




    /****************************************************
    ADD DATA TO XMLDATA
    ****************************************************/
    function do_add_content(&$array, $data) {
        if ($this->is_first) {
            $array['data'] = $data;
            $array['attribs'] = $this->attribs;
        } else $array['data'] .= $data;
    }




    /****************************************************
    PARSE XMLDATA
    ****************************************************/
    function parse_xml_data_array() {
        // Feed level xml:base
        if (!empty($this->xmldata['feeddata']['attribs']['XML:BASE'])) {
            $this->feed_xmlbase = parse_url($this->xmldata['feeddata']['attribs']['XML:BASE']);
        }
        else if (!empty($this->xmldata['feeddata']['attribs'][$this->namespaces['xml'] . ':BASE'])) {
            $this->feed_xmlbase = parse_url($this->xmldata['feeddata']['attribs'][$this->namespaces['xml'] . ':BASE']);
        }
        else if (substr($this->rss_url, 0, 28) == 'http://feeds.feedburner.com/' && !empty($this->xmldata['info']['link'][0]['data'])) {
            $this->feed_xmlbase = parse_url($this->xmldata['info']['link'][0]['data']);
        } else {
            $this->feed_xmlbase = $this->parsed_url;
        }

        // Feed Info
        if (isset($this->xmldata['feedinfo']['type'])) {
            $this->data['feedinfo'] = $this->xmldata['feedinfo'];
        }

        // Feed Title
        if (!empty($this->xmldata['info']['title']['data'])) {
            $this->data['info']['title'] = $this->sanitise($this->xmldata['info']['title']['data'], $this->xmldata['info']['title']['attribs']);
        }

        // Feed Link(s)
        if (!empty($this->xmldata['info']['link'])) {
            foreach ($this->xmldata['info']['link'] as $num => $link) {
                if (empty($link['attribs']['REL']) || $link['attribs']['REL'] == 'alternate') {
                    if (empty($link['data'])) {
                        $this->data['info']['link'][$num] = $this->sanitise($link['attribs']['HREF'], $link['attribs'], true);
                    } else {
                        $this->data['info']['link'][$num] = $this->sanitise($link['data'], $link['attribs'], true);
                    }
                }
            }
        }

        // Feed Description
        if (!empty($this->xmldata['info']['description']['data'])) {
            $this->data['info']['description'] = $this->sanitise($this->xmldata['info']['description']['data'], $this->xmldata['info']['description']['attribs']);
        }
        else if (!empty($this->xmldata['info']['dc:description']['data'])) {
            $this->data['info']['description'] = $this->sanitise($this->xmldata['info']['dc:description']['data'], $this->xmldata['info']['dc:description']['attribs']);
        }
        else if (!empty($this->xmldata['info']['tagline']['data'])) {
            $this->data['info']['description'] = $this->sanitise($this->xmldata['info']['tagline']['data'], $this->xmldata['info']['tagline']['attribs']);
        }
        else if (!empty($this->xmldata['info']['subtitle']['data'])) {
            $this->data['info']['description'] = $this->sanitise($this->xmldata['info']['subtitle']['data'], $this->xmldata['info']['subtitle']['attribs']);
        }

        // Feed Language
        if (!empty($this->xmldata['info']['language']['data'])) {
            $this->data['info']['language'] = $this->sanitise($this->xmldata['info']['language']['data'], $this->xmldata['info']['language']['attribs']);
        }

        // Feed Copyright
        if (!empty($this->xmldata['info']['copyright']['data'])) {
            $this->data['info']['copyright'] = $this->sanitise($this->xmldata['info']['copyright']['data'], $this->xmldata['info']['copyright']['attribs']);
        }

        // Feed Image
        if (!empty($this->xmldata['info']['image']['title']['data'])) {
            $this->data['info']['image']['title'] = $this->sanitise($this->xmldata['info']['image']['title']['data'], $this->xmldata['info']['image']['title']['attribs']);
        }
        if (!empty($this->xmldata['info']['image']['url']['data'])) {
            $this->data['info']['image']['url'] = $this->sanitise($this->xmldata['info']['image']['url']['data'], $this->xmldata['info']['image']['url']['attribs'], true);
        }
        else if (!empty($this->xmldata['info']['logo']['data'])) {
            $this->data['info']['image']['url'] = $this->sanitise($this->xmldata['info']['logo']['data'], $this->xmldata['info']['logo']['attribs'], true);
        }
        if (!empty($this->xmldata['info']['image']['link']['data'])) {
            $this->data['info']['image']['link'] = $this->sanitise($this->xmldata['info']['image']['link']['data'], $this->xmldata['info']['image']['link']['attribs'], true);
        }
        if (!empty($this->xmldata['info']['image']['width']['data'])) {
            $this->data['info']['image']['width'] = $this->sanitise($this->xmldata['info']['image']['width']['data'], $this->xmldata['info']['image']['width']['attribs']);
        }
        if (!empty($this->xmldata['info']['image']['height']['data'])) {
            $this->data['info']['image']['height'] = $this->sanitise($this->xmldata['info']['image']['height']['data'], $this->xmldata['info']['image']['height']['attribs']);
        }

        // Items
        if (!empty($this->xmldata['items'])) {
            foreach ($this->xmldata['items'] as $num => $item) {
                // Item level xml:base
                if (!empty($item['attribs']['XML:BASE'])) {
                    $this->item_xmlbase = parse_url($this->absolutize_url($item['attribs']['XML:BASE'], $this->feed_xmlbase));
                }
                else if (!empty($item['attribs'][$this->namespaces['xml'] . ':BASE'])) {
                    $this->item_xmlbase = parse_url($this->absolutize_url($item['attribs'][$this->namespaces['xml'] . ':BASE'], $this->feed_xmlbase));
                }

                // Clear vars
                $id = null;
                $title = null;
                $description = null;
                $categories = array();
                $author = array();
                $date = null;
                $links = array();
                $enclosures = array();

                // Title
                if (!empty($item['title']['data'])) {
                    $title = $this->sanitise($item['title']['data'], $item['title']['attribs']);
                }
                else if (!empty($item['dc:title']['data'])) {
                    $title = $this->sanitise($item['dc:title']['data'], $item['dc:title']['attribs']);
                }

                // Description
                if (!empty($item['content']['data'])) {
                    $description = $this->sanitise($item['content']['data'], $item['content']['attribs']);
                }
                else if (!empty($item['encoded']['data'])) {
                    $description = $this->sanitise($item['encoded']['data'], $item['encoded']['attribs']);
                }
                else if (!empty($item['summary']['data'])) {
                    $description = $this->sanitise($item['summary']['data'], $item['summary']['attribs']);
                }
                else if (!empty($item['description']['data'])) {
                    $description = $this->sanitise($item['description']['data'], $item['description']['attribs']);
                }
                else if (!empty($item['dc:description']['data'])) {
                    $description = $this->sanitise($item['dc:description']['data'], $item['dc:description']['attribs']);
                }
                else if (!empty($item['longdesc']['data'])) {
                    $description = $this->sanitise($item['longdesc']['data'], $item['longdesc']['attribs']);
                }

                // Link
                if (!empty($item['link'])) {
                    foreach ($item['link'] as $link) {
                        if (empty($link['attribs']['REL']) || $link['attribs']['REL'] == 'alternate') {
                            if (empty($link['data'])) {
                                $links[sizeof($links)] = $this->sanitise($link['attribs']['HREF'], $link['attribs'], true);
                            } else {
                                $links[sizeof($links)] = $this->sanitise($link['data'], $link['attribs'], true);
                            }
                        } else if ($link['attribs']['REL'] == 'enclosure' && !empty($link['attribs']['HREF'])) {
                            $href = null;
                            $type = null;
                            $length = null;
                            $href = $this->sanitise($link['attribs']['HREF'], $link['attribs'], true);
                            if (!empty($link['attribs']['TYPE'])) {
                                $type = $this->sanitise($link['attribs']['TYPE'], $link['attribs']);
                            }
                            if (!empty($link['attribs']['LENGTH'])) {
                                $length = $this->sanitise($link['attribs']['LENGTH'], $link['attribs']);
                            }
                            $enclosures[] = new SimplePie_Enclosure($href, $type, $length);
                        }
                    }
                }

                // Enclosures
                if (!empty($item['enclosure'])) {
                    foreach ($item['enclosure'] as $enclosure) {
                        if (!empty($enclosure['attribs']['URL'])) {
                            $href = null;
                            $type = null;
                            $length = null;
                            $href = $this->sanitise($enclosure['attribs']['URL'], $enclosure['attribs'], true);
                            if (!empty($enclosure['attribs']['TYPE'])) {
                                $type = $this->sanitise($enclosure['attribs']['TYPE'], $enclosure['attribs']);
                            }
                            if (!empty($enclosure['attribs']['LENGTH'])) {
                                $length = $this->sanitise($enclosure['attribs']['LENGTH'], $enclosure['attribs']);
                            }
                            $enclosures[] = new SimplePie_Enclosure($href, $type, $length);
                        }
                    }
                }

                // ID
                if (!empty($item['guid']['data'])) {
                    if (empty($item['guid']['attribs']['ISPERMALINK']) || strtolower($item['guid']['attribs']['ISPERMALINK']) != 'false') {
                        $links[sizeof($links)] = $this->sanitise($item['guid']['data'], $item['guid']['attribs']);
                    }
                    $id = $this->sanitise($item['guid']['data'], $item['guid']['attribs']);
                }
                else if (!empty($item['id']['data'])) {
                    $id = $this->sanitise($item['id']['data'], $item['id']['attribs']);
                }

                // Date
                if (!empty($item['pubdate']['data'])) {
                    $date = $this->parse_date($this->sanitise($item['pubdate']['data'], $item['pubdate']['attribs']));
                }
                else if (!empty($item['dc:date']['data'])) {
                    $date = $this->parse_date($this->sanitise($item['dc:date']['data'], $item['dc:date']['attribs']));
                }
                else if (!empty($item['issued']['data'])) {
                    $date = $this->parse_date($this->sanitise($item['issued']['data'], $item['issued']['attribs']));
                }
                else if (!empty($item['published']['data'])) {
                    $date = $this->parse_date($this->sanitise($item['published']['data'], $item['published']['attribs']));
                }
                else if (!empty($item['modified']['data'])) {
                    $date = $this->parse_date($this->sanitise($item['modified']['data'], $item['modified']['attribs']));
                }
                else if (!empty($item['updated']['data'])) {
                    $date = $this->parse_date($this->sanitise($item['updated']['data'], $item['updated']['attribs']));
                }

                // Categories
                if (!empty($item['category'])) {
                    foreach ($item['category'] as $category) {
                        $categories[sizeof($categories)] = $this->sanitise($category['data'], $category['attribs']);
                    }
                }
                if (!empty($item['subject'])) {
                    foreach ($item['subject'] as $category) {
                        $categories[sizeof($categories)] = $this->sanitise($category['data'], $category['attribs']);
                    }
                }

                // Author
                $authors = array();
                if (!empty($item['creator'])) {
                    foreach($item['creator'] as $creator) {
                        $authors[] = new SimplePie_Author($this->sanitise($creator['data'], $creator['attribs']), null, null);
                    }
                }
                if (!empty($item['author'])) {
                    foreach($item['author'] as $author) {
                        $name = null;
                        $link = null;
                        $email = null;
                        if (!empty($author['name'])) {
                            $name = $this->sanitise($author['name']['data'], $author['name']['attribs']);
                        }
                        if (!empty($author['url'])) {
                            $link = $this->sanitise($author['url']['data'], $author['url']['attribs'], true);
                        }
                        else if (!empty($author['uri'])) {
                            $link = $this->sanitise($author['uri']['data'], $author['uri']['attribs'], true);
                        }
                        else if (!empty($author['homepage'])) {
                            $link = $this->sanitise($author['homepage']['data'], $author['homepage']['attribs'], true);
                        }
                        if (!empty($author['email'])) {
                            $email = $this->sanitise($author['email']['data'], $author['email']['attribs']);
                        }
                        if (!empty($author['rss'])) {
                            $sane = $this->sanitise($author['rss']['data'], $author['rss']['attribs']);
                            if (preg_match('/(.*)@(.*) \((.*)\)/msiU', $sane, $matches)) {
                                $name = trim($matches[3]);
                                $email = trim("$matches[1]@$matches[2]");
                            } else {
                                $email = $sane;
                            }
                        }
                        $authors[] = new SimplePie_Author($name, $link, $email);
                    }
                }
                $this->data['items'][] = new SimplePie_Item($id, $title, $description, array_unique($categories), array_unique($authors), $date, array_unique($links), array_unique($enclosures));
            } // End Items
        }
        unset($this->xmldata);
    } // End parse_xml_data_array();

    function sanitise($data, $attribs, $is_url = false) {
        $this->attribs = $attribs;
        if (isset($this->data['feedinfo']['type']) && $this->data['feedinfo']['type'] == 'Atom') {
            if (!empty($attribs['MODE']) && $attribs['MODE'] == 'base64') {
                $data = base64_decode($data);
            } else if ((!empty($attribs['MODE']) && $attribs['MODE'] == 'escaped' || !empty($attribs['TYPE']) && ($attribs['TYPE'] == 'html' || $attribs['TYPE'] == 'text/html')) && (empty($attribs['SPENCODED']) || $attribs['SPENCODED'] != 'false')) {
                $data = $this->entities_decode($data);
            }
            if (!empty($attribs['TYPE']) && ($attribs['TYPE'] == 'xhtml' || $attribs['TYPE'] == 'application/xhtml+xml')) {
                if ($this->remove_div) {
                    $data = preg_replace('/<div( .*)?>/msiU', '', strrev(preg_replace('/>vid\/</i', '', strrev($data), 1)), 1);
                } else {
                    $data = preg_replace('/<div( .*)?>/msiU', '<div>', $data, 1);
                }
                $data = preg_replace("/<(\/)?$this->xhtml_prefix:/msiU", '<\\1', $data);
            }
        } else {
            if (empty($attribs['SPENCODED']) || $attribs['SPENCODED'] != 'false') {
                $data = $this->entities_decode($data);
            }
        }
        $data = trim($data);
        $data = str_replace(' spencoded="false">', '>', $data);

        // Strip out HTML tags and attributes that might cause various security problems.
        // Based on recommendations by Mark Pilgrim at:
        // http://diveintomark.org/archives/2003/06/12/how_to_consume_rss_safely
        if ($this->strip_htmltags) {
            $tags_to_strip = explode(',', $this->strip_htmltags);
            foreach ($tags_to_strip as $tag) {
                if ($this->encode_instead_of_strip) {
                    // For encoded angled brackets (do these first)
                    $data = preg_replace('/&lt;(!)?(\/)?'. trim($tag) .'(\w|\s|=|-|"|\'|&quot;|:|;|%|\/|\.|\?|&|,|#|!|\+|\(|\))*&gt;/i', '&amp;lt;\\0&amp;gt;', $data);
                    $data = str_replace('&amp;lt;&lt;', '&amp;lt;', $data);
                    $data = str_replace('&gt;&amp;gt;', '&amp;gt;', $data);

                    // For angled brackets
                    $data = preg_replace('/<(!)?(\/)?'. trim($tag) .'(\w|\s|=|-|"|\'|&quot;|:|;|%|\/|\.|\?|&|,|#|!|\+|\(|\))*>/i', '&lt;\\0&gt;', $data);
                    $data = str_replace('&lt;<', '&lt;', $data);
                    $data = str_replace('>&gt;', '&gt;', $data);
                }
                else {
                    $data = preg_replace('/(&lt;|<)(!)?(\/)?'. trim($tag) .'(\w|\s|=|-|"|\'|&quot;|:|;|%|\/|\.|\?|&|,|#|!|\+|\(|\))*(&gt;|>)/i', '', $data);
                }
            }
        }

        if ($this->strip_attributes) {
            $attribs_to_strip = explode(',', $this->strip_attributes);
            foreach ($attribs_to_strip as $attrib) {
                $data = preg_replace('/ '. trim($attrib) .'=(\'|&apos;|"|&quot;)?(\w|\s|=|-|:|;|\/|\.|\?|&|,|#|!|\(|\)|\'|&apos;|<|>|\+|{|})*(\'|&apos;|"|&quot;)?/i', '', $data);
            }
        }

        // Replace H1, H2, and H3 tags with the less important H4 tags.
        // This is because on a site, the more important headers might make sense,
        // but it most likely doesn't fit in the context of RSS-in-a-webpage.
        if ($this->replace_headers) {
            $data = preg_replace('/<h[1-3]( .*)?>/msiU', '<h4>', $data);
            $data = preg_replace('/<\/h[1-3]>/i', '</h4>', $data);
        }

        // If Strip Ads is enabled, strip them.
        if ($this->strip_ads) {
            $data = preg_replace('/(&lt;|<)a(.*)href=(&quot;|")(.*)\\/\/(www\.)?pheedo.com\/(.*).phdo\?s=(.*)(&gt;|>)(\s*|.*)((&lt;|<).*(&gt;|>)|.*)(\s*|.*)(&lt;|<)\/a(&gt;|>)/i', '', $data); // Pheedo links (tested with Dooce.com)
            $data = preg_replace('/(&lt;|<)a(\w|\s|\=|&quot;|")*href=(&quot;|")http:\/\/ad.doubleclick.net\/(.*)(&gt;|>)(\s*|.*)((&lt;|<).*(&gt;|>)|.*)(\s*|.*)(&lt;|<)\/a(&gt;|>)/i', '', $data); // Doubleclick links (tested with InfoWorld.com)
            $data = preg_replace('/(&lt;|<)map(\w|\s|=|-|"|\'|&quot;)*name=(&quot;|\'|")google_ad_map(\w|\s|=|-)*(&quot;|\'|")(\w|\s|=|-|"|\'|&quot;)*(^gt;|>)(.*)(&lt;|<)\/map(&gt;|>)(&lt;|<)img(\w|\s|=|-|"|\'|&quot;)*usemap=(&quot;|\'|")#google_ad_map(\w|\s|=|-)*(&quot;|\'|")(\w|\s|=|-|"|\'|&quot;|:|;|\/|\.|\?|&)*(&gt;|>)/i', '', $data); // Google AdSense for Feeds (tested with tuaw.com).
            // Feedflare, from Feedburner
        }

        if ($is_url) {
            $data = $this->replace_urls($data, true);
        } else {
            $data = preg_replace_callback('/<(.+)>/msiU', array(&$this, 'replace_urls'), $data);
        }

        // If Bypass Image Hotlink is enabled, rewrite all the image tags.
        if ($this->bypass_image_hotlink != false) {
            $data = preg_replace_callback('/src=(&quot;|"|\'|&apos;)?(\w|=|-|:|;|\/|\.|\?|&|,|#|!|\(|\)|\'|&apos;)*(&quot;|"|\'|&apos;)?/', create_function('$m', 'return "src=\"".rawurlencode(str_replace("\"", "", str_replace("src=", "", html_entity_decode($m[0]))))."\"";'), $data);

            if ($this->bypass_image_hotlink_page != false) {
                $data = preg_replace('/<img(\w|\s|=|-|"|\'|&quot;|:|;|\/|\.|\?|&|,|#|!)*src=(&quot;|"|\')?/i', '\\0'.$this->bypass_image_hotlink_page.'?i=', $data);
            }
            else $data = preg_replace('/<img(\w|\s|=|-|"|\'|&quot;|:|;|\/|\.|\?|&|,|#|!)*src=(&quot;|"|\')?/i', '\\0?i=', $data);
        }

        return $data;
    }

    function replace_urls($data, $raw_url = false) {
        if (!empty($this->attribs['XML:BASE'])) {
            if (!empty($this->item_xmlbase)) {
                $attrib_xmlbase = parse_url($this->absolutize_url($this->attribs['XML:BASE'], $this->item_xmlbase));
            } else {
                $attrib_xmlbase = parse_url($this->absolutize_url($this->attribs['XML:BASE'], $this->feed_xmlbase));
            }
        }
        else if (!empty($this->attribs[$this->namespaces['xml'] . ':BASE'])) {
            if (!empty($this->item_xmlbase)) {
                $attrib_xmlbase = parse_url($this->absolutize_url($this->attribs[$this->namespaces['xml'] . ':BASE'], $this->item_xmlbase));
            } else {
                $attrib_xmlbase = parse_url($this->absolutize_url($this->attribs[$this->namespaces['xml'] . ':BASE'], $this->feed_xmlbase));
            }
        }
        if (!empty($attrib_xmlbase)) {
            $xmlbase = $attrib_xmlbase;
        } else if (!empty($this->item_xmlbase)) {
            $xmlbase = $this->item_xmlbase;
        } else {
            $xmlbase = $this->feed_xmlbase;
        }
        if ($raw_url) {
            return $this->absolutize_url($data, $xmlbase);
        } else {
            $attributes = array(
                'background',
                'href',
                'src',
                'longdesc',
                'usemap',
                'codebase',
                'data',
                'classid',
                'cite',
                'action',
                'profile',
                'for'
            );
            foreach ($attributes as $attribute) {
                $attrib = $this->get_attribute($data[0], $attribute);
                $new_tag = str_replace($attrib[1], $this->absolutize_url($attrib[1], $xmlbase), $attrib[0]);
                $data[0] = str_replace($attrib[0], $new_tag, $data[0]);
            }
            return $data[0];
        }
    }

    function entities_decode($data) {
        return preg_replace_callback('/&(#)?(x)?([0-9a-z]+);/mi', array(&$this, 'do_entites_decode'), $data);
    }

    function do_entites_decode($data)
    {
        $entity = "&$data[1]$data[2]$data[3];";
        $entity_html = html_entity_decode($entity, ENT_QUOTES);
        if ($entity == $entity_html) {
            return preg_replace_callback('/&#([0-9a-fx]+);/mi', array(&$this, 'replace_num_entity'), $entity);
        } else {
            return $entity_html;
        }
    }

    /*
     * Escape numeric entities
     * From a PHP Manual note (on html_entity_decode())
     * Copyright (c) 2005 by "php dot net at c dash ovidiu dot tk",
     * "emilianomartinezluque at yahoo dot com" and "hurricane at cyberworldz dot org".
     *
     * This material may be distributed only subject to the terms and conditions set forth in
     * the Open Publication License, v1.0 or later (the latest version is presently available at
     * http://www.opencontent.org/openpub/).
     */
    function replace_num_entity($ord) {
        $ord = $ord[1];
        if (preg_match('/^x([0-9a-f]+)$/i', $ord, $match))
            $ord = hexdec($match[1]);
        else
            $ord = intval($ord);
        $no_bytes = 0;
        $byte = array();
        if ($ord < 128)
            return chr($ord);
        if ($ord < 2048)
            $no_bytes = 2;
        else if ($ord < 65536)
            $no_bytes = 3;
        else if ($ord < 1114112)
            $no_bytes = 4;
        else return;
        switch ($no_bytes) {
            case 2:
                $prefix = array(31, 192);
                break;

            case 3:
                $prefix = array(15, 224);
                break;

            case 4:
            $prefix = array(7, 240);
            break;
        }
        for ($i=0; $i < $no_bytes; ++$i)
            $byte[$no_bytes-$i-1] = (($ord & (63 * pow(2,6*$i))) / pow(2,6*$i)) & 63 | 128;
        $byte[0] = ($byte[0] & $prefix[0]) | $prefix[1];
        $ret = '';
        for ($i=0; $i < $no_bytes; ++$i)
            $ret .= chr($byte[$i]);
        return $ret;
    }

    function parse_date($date) {
        if (preg_match('/([0-9]{2,4})-([0-9][0-9])-([0-9][0-9])T([0-9][0-9]):([0-9][0-9]):([0-9][0-9])(\.[0-9][0-9])?Z/i', $date, $matches)) {
            if (isset($matches[7]) && substr($matches[7], 1) >= 50)
                $matches[6]++;
            return strtotime("$matches[1]-$matches[2]-$matches[3] $matches[4]:$matches[5]:$matches[6] -0000");
        } else if (preg_match('/([0-9]{2,4})-([0-9][0-9])-([0-9][0-9])T([0-9][0-9]):([0-9][0-9]):([0-9][0-9])(\.[0-9][0-9])?(\+|-)([0-9][0-9]):([0-9][0-9])/i', $date, $matches)) {
            if (isset($matches[7]) && substr($matches[7], 1) >= 50)
                $matches[6]++;
            return strtotime("$matches[1]-$matches[2]-$matches[3] $matches[4]:$matches[5]:$matches[6] $matches[8]$matches[9]$matches[10]");
        } else {
            return strtotime($date);
        }
    }




    /****************************************************
    FUNCTIONS FOR XML_PARSE
    ****************************************************/
    function startHandler($parser, $name, $attribs) {
        $this->tagName = $name;
        $this->attribs = $attribs;
        $this->is_first = true;
        switch ($this->tagName) {
            case 'ITEM':
            case $this->namespaces['rss2'] . ':ITEM':
            case $this->namespaces['rss1'] . ':ITEM':
            case 'ENTRY':
            case $this->namespaces['atom'] . ':ENTRY':
                $this->insideItem = true;
                $this->do_add_content($this->xmldata['items'][$this->itemNumber], '');
                break;

            case 'CHANNEL':
            case $this->namespaces['rss2'] . ':CHANNEL':
            case $this->namespaces['rss1'] . ':CHANNEL':
                $this->insideChannel = true;
                break;

            case 'RSS':
            case $this->namespaces['rss2'] . ':RSS':
                $this->xmldata['feedinfo']['type'] = 'RSS';
                $this->do_add_content($this->xmldata['feeddata'], '');
                if (!empty($attribs['VERSION'])) {
                    $this->xmldata['feedinfo']['version'] = trim($attribs['VERSION']);
                }
                break;

            case $this->namespaces['rdf'] . ':RDF':
                $this->xmldata['feedinfo']['type'] = 'RSS';
                $this->do_add_content($this->xmldata['feeddata'], '');
                $this->xmldata['feedinfo']['version'] = 1;
                break;

            case 'FEED':
            case $this->namespaces['atom'] . ':FEED':
                $this->xmldata['feedinfo']['type'] = 'Atom';
                $this->do_add_content($this->xmldata['feeddata'], '');
                if (!empty($attribs['VERSION'])) {
                    $this->xmldata['feedinfo']['version'] = trim($attribs['VERSION']);
                }
                break;

            case 'IMAGE':
            case $this->namespaces['rss2'] . ':IMAGE':
            case $this->namespaces['rss1'] . ':IMAGE':
                if ($this->insideChannel) $this->insideImage = true;
                break;
        }

        if (isset($this->xmldata['feedinfo']['type']) && $this->xmldata['feedinfo']['type'] == 'Atom') {
            switch ($this->tagName) {
                case 'AUTHOR':
                case $this->namespaces['atom'] . ':AUTHOR':
                    $this->insideAuthor = true;
                    break;
            }
        }
        $this->dataHandler($this->xml, '');
    }

    function dataHandler($parser, $data) {
        if ($this->insideItem) {
            switch ($this->tagName) {
                case 'TITLE':
                case $this->namespaces['rss1'] . ':TITLE':
                case $this->namespaces['rss2'] . ':TITLE':
                case $this->namespaces['atom'] . ':TITLE':
                    $this->do_add_content($this->xmldata['items'][$this->itemNumber]['title'], $data);
                    break;

                case $this->namespaces['dc'] . ':TITLE':
                    $this->do_add_content($this->xmldata['items'][$this->itemNumber]['dc:title'], $data);
                    break;

                case 'CONTENT':
                case $this->namespaces['atom'] . ':CONTENT':
                    $this->do_add_content($this->xmldata['items'][$this->itemNumber]['content'], $data);
                    break;

                case $this->namespaces['content'] . ':ENCODED':
                    $this->do_add_content($this->xmldata['items'][$this->itemNumber]['encoded'], $data);
                    break;

                case 'SUMMARY':
                case $this->namespaces['atom'] . ':SUMMARY':
                    $this->do_add_content($this->xmldata['items'][$this->itemNumber]['summary'], $data);
                    break;

                case 'LONGDESC':
                    $this->do_add_content($this->xmldata['items'][$this->itemNumber]['longdesc'], $data);
                    break;

                case 'DESCRIPTION':
                case $this->namespaces['rss1'] . ':DESCRIPTION':
                case $this->namespaces['rss2'] . ':DESCRIPTION':
                    $this->do_add_content($this->xmldata['items'][$this->itemNumber]['description'], $data);
                    break;

                case $this->namespaces['dc'] . ':DESCRIPTION':
                    $this->do_add_content($this->xmldata['items'][$this->itemNumber]['dc:description'], $data);
                    break;

                case 'LINK':
                case $this->namespaces['rss1'] . ':LINK':
                case $this->namespaces['rss2'] . ':LINK':
                case $this->namespaces['atom'] . ':LINK':
                    $this->do_add_content($this->xmldata['items'][$this->itemNumber]['link'][$this->itemLinkNumber], $data);
                    break;

                case 'ENCLOSURE':
                case $this->namespaces['rss1'] . ':ENCLOSURE':
                case $this->namespaces['rss2'] . ':ENCLOSURE':
                    $this->do_add_content($this->xmldata['items'][$this->itemNumber]['enclosure'][$this->enclosureNumber], $data);
                    break;

                case 'GUID':
                case $this->namespaces['rss1'] . ':GUID':
                case $this->namespaces['rss2'] . ':GUID':
                    $this->do_add_content($this->xmldata['items'][$this->itemNumber]['guid'], $data);
                    break;

                case 'ID':
                case $this->namespaces['atom'] . ':ID':
                    $this->do_add_content($this->xmldata['items'][$this->itemNumber]['id'], $data);
                    break;

                case 'PUBDATE':
                case $this->namespaces['rss1'] . ':PUBDATE':
                case $this->namespaces['rss2'] . ':PUBDATE':
                    $this->do_add_content($this->xmldata['items'][$this->itemNumber]['pubdate'], $data);
                    break;

                case $this->namespaces['dc'] . ':DATE':
                    $this->do_add_content($this->xmldata['items'][$this->itemNumber]['dc:date'], $data);
                    break;

                case 'ISSUED':
                case $this->namespaces['atom'] . ':ISSUED':
                    $this->do_add_content($this->xmldata['items'][$this->itemNumber]['issued'], $data);
                    break;

                case 'PUBLISHED':
                case $this->namespaces['atom'] . ':PUBLISHED':
                    $this->do_add_content($this->xmldata['items'][$this->itemNumber]['published'], $data);
                    break;

                case 'MODIFIED':
                case $this->namespaces['atom'] . ':MODIFIED':
                    $this->do_add_content($this->xmldata['items'][$this->itemNumber]['modified'], $data);
                    break;

                case 'UPDATED':
                case $this->namespaces['atom'] . ':UPDATED':
                    $this->do_add_content($this->xmldata['items'][$this->itemNumber]['updated'], $data);
                    break;

                case 'CATEGORY':
                case $this->namespaces['rss1'] . ':CATEGORY':
                case $this->namespaces['rss2'] . ':CATEGORY':
                case $this->namespaces['atom'] . ':CATEGORY':
                    $this->do_add_content($this->xmldata['items'][$this->itemNumber]['category'][$this->categoryNumber], $data);
                    break;

                case $this->namespaces['dc'] . ':SUBJECT':
                    $this->do_add_content($this->xmldata['items'][$this->itemNumber]['subject'][$this->categoryNumber], $data);
                    break;

                case $this->namespaces['dc'] . ':CREATOR':
                    $this->do_add_content($this->xmldata['items'][$this->itemNumber]['creator'][$this->authorNumber], $data);
                    break;

                case 'AUTHOR':
                case $this->namespaces['rss1'] . ':AUTHOR':
                case $this->namespaces['rss2'] . ':AUTHOR':
                    $this->do_add_content($this->xmldata['items'][$this->itemNumber]['author'][$this->authorNumber]['rss'], $data);
                    break;
            }

            if ($this->insideAuthor) {
                switch ($this->tagName) {
                    case 'NAME':
                    case $this->namespaces['atom'] . ':NAME':
                        $this->do_add_content($this->xmldata['items'][$this->itemNumber]['author'][$this->authorNumber]['name'], $data);
                        break;

                    case 'URL':
                    case $this->namespaces['atom'] . ':URL':
                        $this->do_add_content($this->xmldata['items'][$this->itemNumber]['author'][$this->authorNumber]['url'], $data);
                        break;

                    case 'URI':
                    case $this->namespaces['atom'] . ':URI':
                        $this->do_add_content($this->xmldata['items'][$this->itemNumber]['author'][$this->authorNumber]['uri'], $data);
                        break;

                    case 'HOMEPAGE':
                    case $this->namespaces['atom'] . ':HOMEPAGE':
                        $this->do_add_content($this->xmldata['items'][$this->itemNumber]['author'][$this->authorNumber]['homepage'], $data);
                        break;

                    case 'EMAIL':
                    case $this->namespaces['atom'] . ':EMAIL':
                        $this->do_add_content($this->xmldata['items'][$this->itemNumber]['author'][$this->authorNumber]['email'], $data);
                        break;
                }
            }
        }

        else if (($this->insideChannel && !$this->insideImage) || (isset($this->xmldata['feedinfo']['type']) && $this->xmldata['feedinfo']['type'] == 'Atom')) {
            switch ($this->tagName) {
                case 'TITLE':
                case $this->namespaces['rss1'] . ':TITLE':
                case $this->namespaces['rss2'] . ':TITLE':
                case $this->namespaces['atom'] . ':TITLE':
                    $this->do_add_content($this->xmldata['info']['title'], $data);
                    break;

                case 'LINK':
                case $this->namespaces['rss1'] . ':LINK':
                case $this->namespaces['rss2'] . ':LINK':
                case $this->namespaces['atom'] . ':LINK':
                    $this->do_add_content($this->xmldata['info']['link'][$this->linkNumber], $data);
                    break;

                case 'DESCRIPTION':
                case $this->namespaces['rss1'] . ':DESCRIPTION':
                case $this->namespaces['rss2'] . ':DESCRIPTION':
                    $this->do_add_content($this->xmldata['info']['description'], $data);
                    break;

                case $this->namespaces['dc'] . ':DESCRIPTION':
                    $this->do_add_content($this->xmldata['info']['dc:description'], $data);
                    break;

                case 'TAGLINE':
                case $this->namespaces['atom'] . ':TAGLINE':
                    $this->do_add_content($this->xmldata['info']['tagline'], $data);
                    break;

                case 'SUBTITLE':
                case $this->namespaces['atom'] . ':SUBTITLE':
                    $this->do_add_content($this->xmldata['info']['subtitle'], $data);
                    break;

                case 'COPYRIGHT':
                case $this->namespaces['rss1'] . ':COPYRIGHT':
                case $this->namespaces['rss2'] . ':COPYRIGHT':
                case $this->namespaces['atom'] . ':COPYRIGHT':
                    $this->do_add_content($this->xmldata['info']['copyright'], $data);
                    break;

                case 'LANGUAGE':
                case $this->namespaces['rss1'] . ':LANGUAGE':
                case $this->namespaces['rss2'] . ':LANGUAGE':
                    $this->do_add_content($this->xmldata['info']['language'], $data);
                    break;

                case 'LOGO':
                case $this->namespaces['atom'] . ':LOGO':
                    $this->do_add_content($this->xmldata['info']['logo'], $data);
                    break;

            }
        }

        else if ($this->insideChannel && $this->insideImage) {
            switch ($this->tagName) {
                case 'TITLE':
                case $this->namespaces['rss1'] . ':TITLE':
                case $this->namespaces['rss2'] . ':TITLE':
                    $this->do_add_content($this->xmldata['info']['image']['title'], $data);
                    break;

                case 'URL':
                case $this->namespaces['rss1'] . ':URL':
                case $this->namespaces['rss2'] . ':URL':
                    $this->do_add_content($this->xmldata['info']['image']['url'], $data);
                    break;

                case 'LINK':
                case $this->namespaces['rss1'] . ':LINK':
                case $this->namespaces['rss2'] . ':LINK':
                    $this->do_add_content($this->xmldata['info']['image']['link'], $data);
                    break;

                case 'WIDTH':
                case $this->namespaces['rss1'] . ':WIDTH':
                case $this->namespaces['rss2'] . ':WIDTH':
                    $this->do_add_content($this->xmldata['info']['image']['width'], $data);
                    break;

                case 'HEIGHT':
                case $this->namespaces['rss1'] . ':HEIGHT':
                case $this->namespaces['rss2'] . ':HEIGHT':
                    $this->do_add_content($this->xmldata['info']['image']['height'], $data);
                    break;
            }
        }
        $this->is_first = false;
    }

    function endHandler($parser, $name) {
        $this->tagName = '';
        switch ($name) {
            case 'ITEM':
            case $this->namespaces['rss1'] . ':ITEM':
            case $this->namespaces['rss2'] . ':ITEM':
            case 'ENTRY':
            case $this->namespaces['atom'] . ':ENTRY':
                $this->insideItem = false;
                $this->itemNumber++;
                $this->authorNumber = 0;
                $this->categoryNumber = 0;
                $this->enclosureNumber = 0;
                $this->itemLinkNumber = 0;
                break;

            case 'CHANNEL':
            case $this->namespaces['rss1'] . ':CHANNEL':
            case $this->namespaces['rss2'] . ':CHANNEL':
                $this->insideChannel = false;
                break;

            case 'IMAGE':
            case $this->namespaces['rss1'] . ':IMAGE':
            case $this->namespaces['rss2'] . ':IMAGE':
                if ($this->insideChannel) $this->insideImage = false;
                break;

            case 'AUTHOR':
            case $this->namespaces['rss1'] . ':AUTHOR':
            case $this->namespaces['rss2'] . ':AUTHOR':
            case $this->namespaces['atom'] . ':AUTHOR':
                $this->authorNumber++;
                if ($this->xmldata['feedinfo']['type'] == 'Atom') $this->insideAuthor = false;
                break;

            case 'CATEGORY':
            case $this->namespaces['rss1'] . ':CATEGORY':
            case $this->namespaces['rss2'] . ':CATEGORY':
            case $this->namespaces['atom'] . ':CATEGORY':
            case $this->namespaces['dc'] . ':SUBJECT':
                $this->categoryNumber++;
                break;

            case 'ENCLOSURE':
            case $this->namespaces['rss1'] . ':ENCLOSURE':
            case $this->namespaces['rss2'] . ':ENCLOSURE':
                $this->enclosureNumber++;
                break;

            case 'LINK':
            case $this->namespaces['rss1'] . ':LINK':
            case $this->namespaces['rss2'] . ':LINK':
            case $this->namespaces['atom'] . ':LINK':
                if ($this->insideItem)
                    $this->itemLinkNumber++;
                else
                    $this->linkNumber++;
                break;
        }
    }

    function startNameSpace($parser, $prefix, $uri = null) {
        $prefix = strtoupper($prefix);
        $uri = strtoupper($uri);
        if ($prefix == 'ATOM' || $uri == 'HTTP://WWW.W3.ORG/2005/ATOM' || $uri == 'HTTP://PURL.ORG/ATOM/NS#') {
            $this->namespaces['atom'] = $uri;
        }
        else if ($prefix == 'RSS2' || $uri == 'HTTP://BACKEND.USERLAND.COM/RSS2') {
            $this->namespaces['rss2'] = $uri;
        }
        else if ($prefix == 'RDF' || $uri == 'HTTP://WWW.W3.ORG/1999/02/22-RDF-SYNTAX-NS#') {
            $this->namespaces['rdf'] = $uri;
        }
        else if ($prefix == 'RSS' || $uri == 'HTTP://PURL.ORG/RSS/1.0/') {
            $this->namespaces['rss1'] = $uri;
        }
        else if ($prefix == 'DC' || $uri == 'HTTP://PURL.ORG/DC/ELEMENTS/1.1/') {
            $this->namespaces['dc'] = $uri;
        }
        else if ($prefix == 'XHTML' || $uri == 'HTTP://WWW.W3.ORG/1999/XHTML') {
            $this->namespaces['xhtml'] = $uri;
            $this->xhtml_prefix = $prefix;
        }
        else if ($prefix == 'CONTENT' || $uri == 'HTTP://PURL.ORG/RSS/1.0/MODULES/CONTENT/') {
            $this->namespaces['content'] = $uri;
        }
    }

    function endNameSpace($parser, $prefix) {
        if ($key = array_search(strtoupper($prefix), $this->namespaces)) {
            if ($key == 'atom') {
                $this->namespaces['atom'] = 'ATOM';
            }
            else if ($key == 'rss2') {
                $this->namespaces['rss2'] = 'RSS';
            }
            else if ($key == 'rdf') {
                $this->namespaces['rdf'] = 'RDF';
            }
            else if ($key == 'rss1') {
                $this->namespaces['rss1'] = 'RSS';
            }
            else if ($key == 'dc') {
                $this->namespaces['dc'] = 'DC';
            }
            else if ($key == 'xhtml') {
                $this->namespaces['xhtml'] = 'XHTML';
                $this->xhtml_prefix = 'XHTML';
            }
            else if ($key == 'content') {
                $this->namespaces['content'] = 'CONTENT';
            }
        }
    }
}

class SimplePie_Item
{
    function SimplePie_Item($id, $title, $description, $category, $author, $date, $links, $enclosure) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->category = $category;
        $this->author = $author;
        $this->date = $date;
        $this->links = $links;
        $this->enclosure = $enclosure;
    }

    /****************************************************
    PARSE OUT ITEM-RELATED DATA
    ****************************************************/
    // Get the id of the item
    function get_id() {
        return (empty($this->id)) ? false : $this->id;
    }
    // Get the title of the item
    function get_title() {
        return (empty($this->title)) ? false : $this->title;
    }

    // Get the description of the item
    function get_description() {
        return (empty($this->description)) ? false : $this->description;
    }

    // Get the category of the item
    function get_category() {
        return (empty($this->category)) ? false : $this->category;
    }

    // Get the author of the item
    function get_author($key) {
        return (empty($this->author[$key])) ? false : $this->author[$key];
    }

    // Get the author of the item
    function get_authors() {
        return (empty($this->author)) ? false : $this->author;
    }

    // Get the date of the item
    // Also, allow users to set the format of how dates are displayed on a webpage.
    function get_date($date_format = 'j F Y, g:i a') {
        return (empty($this->date)) ? false : date($date_format, $this->date);
    }

    // Get the Permalink of the item
    function get_permalink() {
        // If there is a link, take it. Fine.
        if (!empty($this->links[0])) {
            return $this->links[0];
        }

        // If there isn't, check for an enclosure, if that exists, give that.
        else if ($this->get_enclosure(0)) {
            return $this->get_enclosure(0);
        }
        else return false;
    }

    // Get all links
    function get_links() {
        return (empty($this->links)) ? false : $this->links;
    }

    // Get the enclosure of the item
    function get_enclosure($key) {
        return (empty($this->enclosure[$key])) ? false : $this->enclosure[$key];
    }

    // Get the enclosure of the item
    function get_enclosures() {
        return (empty($this->enclosure)) ? false : $this->enclosure;
    }




    /****************************************************
    "ADD TO" LINKS
    Allows people to easily add news postings to social bookmarking sites.
    ****************************************************/
    function add_to_blinklist() {
        return "http://www.blinklist.com/index.php?Action=Blink/addblink.php&amp;Description=&amp;Url=" . rawurlencode($this->get_permalink()) . "&amp;Title=" . rawurlencode($this->get_title());
    }

    function add_to_delicious() {
        return "http://del.icio.us/post/?v=3&amp;url=" . rawurlencode($this->get_permalink()) . "&amp;title=" . rawurlencode($this->get_title());
    }

    function add_to_digg() {
        return "http://digg.com/submit?phase=2&amp;URL=" . rawurlencode($this->get_permalink());
    }

    function add_to_furl() {
        return "http://www.furl.net/storeIt.jsp?u=" . rawurlencode($this->get_permalink()) . "&amp;t=" . rawurlencode($this->get_title());
    }

    function add_to_magnolia() {
        return "http://ma.gnolia.com/bookmarklet/add?url=" . rawurlencode($this->get_permalink()) . "&amp;title=" . rawurlencode($this->get_title());
    }

    function add_to_myweb20() {
        return "http://myweb2.search.yahoo.com/myresults/bookmarklet?u=" . rawurlencode($this->get_permalink()) . "&amp;t=" . rawurlencode($this->get_title());
    }

    function add_to_newsvine() {
        return "http://www.newsvine.com/_wine/save?u=" . rawurlencode($this->get_permalink()) . "&amp;h=" . rawurlencode($this->get_title());
    }

    function add_to_reddit() {
        return 'http://reddit.com/submit?url=' . rawurlencode($this->get_permalink()) . "&amp;title=" . rawurlencode($this->get_title());
    }

    function add_to_spurl() {
        return "http://www.spurl.net/spurl.php?v=3&amp;url=" . rawurlencode($this->get_permalink()) . "&amp;title=" . rawurlencode($this->get_title());
    }




    /****************************************************
    SEARCHES
    Metadata searches
    ****************************************************/
    function search_technorati() {
        return 'http://www.technorati.com/search/' . rawurlencode($this->get_permalink());
    }
}

class SimplePie_Author
{
    var $name;
    var $link;
    var $email;

    // Constructor, used to input the data
    function SimplePie_Author($name, $link, $email) {
        $this->name = $name;
        $this->link = $link;
        $this->email = $email;
    }

    function get_name() {
        return (empty($this->name)) ? false : $this->name;
    }

    function get_link() {
        return (empty($this->link)) ? false : $this->link;
    }

    function get_email() {
        return (empty($this->email)) ? false : $this->email;
    }
}

class SimplePie_Enclosure
{
    var $link;
    var $type;
    var $length;

    // Constructor, used to input the data
    function SimplePie_Enclosure($link, $type, $length) {
        $this->link = $link;
        $this->type = $type;
        $this->length = $length;
    }

    function get_link() {
        return (empty($this->link)) ? false : $this->link;
    }

    function get_extension() {
        if (!empty($this->link)) {
            return pathinfo($this->link, PATHINFO_EXTENSION);
        } else {
            return false;
        }
    }

    function get_type() {
        return (empty($this->type)) ? false : $this->type;
    }

    function get_length() {
        return (empty($this->length)) ? false : $this->length;
    }

    function get_size() {
        return (empty($this->length)) ? false : round(($this->length/1048576), 2);
    }

    function embed($options) {

        // Set up defaults
        $audio='';
        $video='';
        $alt='';
        $altclass='';
        $loop='false';
        $width='auto';
        $height='auto';
        $bgcolor='#ffffff';
        $embed='';

        // Process options and reassign values as necessary
        $options = explode(',', $options);
        foreach($options as $option) {
            $opt = explode(':', trim($option));
            if ($opt[0] == 'audio') $audio=$opt[1];
            else if ($opt[0] == 'video') $video=$opt[1];
            else if ($opt[0] == 'alt') $alt=$opt[1];
            else if ($opt[0] == 'altclass') $altclass=$opt[1];
            else if ($opt[0] == 'loop') $loop=$opt[1];
            else if ($opt[0] == 'width') $width=$opt[1];
            else if ($opt[0] == 'height') $height=$opt[1];
            else if ($opt[0] == 'bgcolor') $bgcolor=$opt[1];
        }

        // Process values for 'auto'
        if ($width == 'auto') {
            if (stristr($this->type, 'audio/')) $width='100%';
            else if (stristr($this->type, 'video/')) $width='320';
            else $width='100%';
        }
        if ($height == 'auto') {
            if (stristr($this->type, 'audio/')) $height=0;
            else if (stristr($this->type, 'video/')) $height=240;
            else $height=256;
        }

        // Set proper placeholder value
        if (stristr($this->type, 'audio/')) $placeholder=$audio;
        else if (stristr($this->type, 'video/')) $placeholder=$video;

        // Make sure the JS library is included
        // (I know it'll be included multiple times, but I can't think of a better way to do this automatically)
        $embed.='<script type="text/javascript" src="?js"></script>';

        // Odeo Feed MP3's
        if (substr(strtolower($this->link), 0, 15) == 'http://odeo.com') {
            $embed.='<script type="text/javascript">embed_odeo("'.$this->link.'");</script>';
        }

        // QuickTime 7 file types.  Need to test with QuickTime 6.
        else if ($this->type == 'audio/3gpp' || $this->type == 'audio/3gpp2' || $this->type == 'audio/aac' || $this->type == 'audio/x-aac' || $this->type == 'audio/aiff' || $this->type == 'audio/x-aiff' || $this->type == 'audio/mid' || $this->type == 'audio/midi' || $this->type == 'audio/x-midi' || $this->type == 'audio/mpeg' || $this->type == 'audio/x-mpeg' || $this->type == 'audio/mp3' || $this->type == 'x-audio/mp3' || $this->type == 'audio/mp4' || $this->type == 'audio/m4a' || $this->type == 'audio/x-m4a' || $this->type == 'audio/wav' || $this->type == 'audio/x-wav' || $this->type == 'video/3gpp' || $this->type == 'video/3gpp2' || $this->type == 'video/m4v' || $this->type == 'video/x-m4v' || $this->type == 'video/mp4' || $this->type == 'video/mpeg' || $this->type == 'video/x-mpeg' || $this->type == 'video/quicktime' || $this->type == 'video/sd-video') {
            $height+=16;
            $embed.='<script type="text/javascript">embed_quicktime("'.$this->type.'", "'.$bgcolor.'", "'.$width.'", "'.$height.'", "'.$this->link.'", "'.$placeholder.'", "'.$loop.'");</script>';
        }

        // Flash
        else if ($this->type == 'application/x-shockwave-flash' || $this->type == 'application/futuresplash') {
            $embed.='<script type="text/javascript">embed_flash("'.$bgcolor.'", "'.$width.'", "'.$height.'", "'.$this->link.'", "'.$loop.'", "'.$this->type.'");</script>';
        }

        // Windows Media
        else if ($this->type == 'application/asx' || $this->type == 'application/x-mplayer2' || $this->type == 'audio/x-ms-wma' || $this->type == 'audio/x-ms-wax' || $this->type == 'video/x-ms-asf-plugin' || $this->type == 'video/x-ms-asf' || $this->type == 'video/x-ms-wm' || $this->type == 'video/x-ms-wmv' || $this->type == 'video/x-ms-wvx') {
            $height+=45;
            $embed.='<script type="text/javascript">embed_wmedia("'.$width.'", "'.$height.'", "'.$this->link.'");</script>';
        }

        // Everything else
        else $embed.='<a href="' . $this->link . '" class="' . $altclass . '">' . $alt . '</a>';

        return $embed;
    }
}

?>
