<?php

use dokuwiki\Handler\Block;
use dokuwiki\Handler\CallWriter;
use dokuwiki\Handler\Lists;
use dokuwiki\Handler\Nest;
use dokuwiki\Handler\Preformatted;
use dokuwiki\Handler\Quote;
use dokuwiki\Handler\Table;

if (!defined('DOKU_PARSER_EOL')) define('DOKU_PARSER_EOL', "\n");   // add this to make handling test cases simpler

class Doku_Handler {

    var $Renderer = null;

    var $CallWriter = null;

    var $calls = array();

    var $status = array(
        'section' => false,
        'doublequote' => 0,
    );

    var $rewriteBlocks = true;

    function __construct() {
        $this->CallWriter = new CallWriter($this);
    }

    /**
     * @param string $handler
     * @param mixed $args
     * @param integer|string $pos
     */
    function _addCall($handler, $args, $pos) {
        $call = array($handler,$args, $pos);
        $this->CallWriter->writeCall($call);
    }

    function addPluginCall($plugin, $args, $state, $pos, $match) {
        $call = array('plugin',array($plugin, $args, $state, $match), $pos);
        $this->CallWriter->writeCall($call);
    }

    function _finalize(){

        $this->CallWriter->finalise();

        if ( $this->status['section'] ) {
            $last_call = end($this->calls);
            array_push($this->calls,array('section_close',array(), $last_call[2]));
        }

        if ( $this->rewriteBlocks ) {
            $B = new Block();
            $this->calls = $B->process($this->calls);
        }

        trigger_event('PARSER_HANDLER_DONE',$this);

        array_unshift($this->calls,array('document_start',array(),0));
        $last_call = end($this->calls);
        array_push($this->calls,array('document_end',array(),$last_call[2]));
    }

    /**
     * fetch the current call and advance the pointer to the next one
     *
     * @return bool|mixed
     */
    function fetch() {
        $call = current($this->calls);
        if($call !== false) {
            next($this->calls); //advance the pointer
            return $call;
        }
        return false;
    }


    /**
     * Special plugin handler
     *
     * This handler is called for all modes starting with 'plugin_'.
     * An additional parameter with the plugin name is passed
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     * @param string|integer $match
     * @param string|integer $state
     * @param integer $pos
     * @param $pluginname
     *
     * @return bool
     */
    function plugin($match, $state, $pos, $pluginname){
        $data = array($match);
        /** @var DokuWiki_Syntax_Plugin $plugin */
        $plugin = plugin_load('syntax',$pluginname);
        if($plugin != null){
            $data = $plugin->handle($match, $state, $pos, $this);
        }
        if ($data !== false) {
            $this->addPluginCall($pluginname,$data,$state,$pos,$match);
        }
        return true;
    }

    function base($match, $state, $pos) {
        switch ( $state ) {
            case DOKU_LEXER_UNMATCHED:
                $this->_addCall('cdata',array($match), $pos);
                return true;
            break;
        }
    }

    function header($match, $state, $pos) {
        // get level and title
        $title = trim($match);
        $level = 7 - strspn($title,'=');
        if($level < 1) $level = 1;
        $title = trim($title,'=');
        $title = trim($title);

        if ($this->status['section']) $this->_addCall('section_close',array(),$pos);

        $this->_addCall('header',array($title,$level,$pos), $pos);

        $this->_addCall('section_open',array($level),$pos);
        $this->status['section'] = true;
        return true;
    }

    function notoc($match, $state, $pos) {
        $this->_addCall('notoc',array(),$pos);
        return true;
    }

    function nocache($match, $state, $pos) {
        $this->_addCall('nocache',array(),$pos);
        return true;
    }

    function linebreak($match, $state, $pos) {
        $this->_addCall('linebreak',array(),$pos);
        return true;
    }

    function eol($match, $state, $pos) {
        $this->_addCall('eol',array(),$pos);
        return true;
    }

    function hr($match, $state, $pos) {
        $this->_addCall('hr',array(),$pos);
        return true;
    }

    /**
     * @param string|integer $match
     * @param string|integer $state
     * @param integer $pos
     * @param string $name
     */
    function _nestingTag($match, $state, $pos, $name) {
        switch ( $state ) {
            case DOKU_LEXER_ENTER:
                $this->_addCall($name.'_open', array(), $pos);
            break;
            case DOKU_LEXER_EXIT:
                $this->_addCall($name.'_close', array(), $pos);
            break;
            case DOKU_LEXER_UNMATCHED:
                $this->_addCall('cdata',array($match), $pos);
            break;
        }
    }

    function strong($match, $state, $pos) {
        $this->_nestingTag($match, $state, $pos, 'strong');
        return true;
    }

    function emphasis($match, $state, $pos) {
        $this->_nestingTag($match, $state, $pos, 'emphasis');
        return true;
    }

    function underline($match, $state, $pos) {
        $this->_nestingTag($match, $state, $pos, 'underline');
        return true;
    }

    function monospace($match, $state, $pos) {
        $this->_nestingTag($match, $state, $pos, 'monospace');
        return true;
    }

    function subscript($match, $state, $pos) {
        $this->_nestingTag($match, $state, $pos, 'subscript');
        return true;
    }

    function superscript($match, $state, $pos) {
        $this->_nestingTag($match, $state, $pos, 'superscript');
        return true;
    }

    function deleted($match, $state, $pos) {
        $this->_nestingTag($match, $state, $pos, 'deleted');
        return true;
    }


    function footnote($match, $state, $pos) {
        if (!isset($this->_footnote)) $this->_footnote = false;

        switch ( $state ) {
            case DOKU_LEXER_ENTER:
                // footnotes can not be nested - however due to limitations in lexer it can't be prevented
                // we will still enter a new footnote mode, we just do nothing
                if ($this->_footnote) {
                    $this->_addCall('cdata',array($match), $pos);
                    break;
                }
                $this->_footnote = true;

                $this->CallWriter = new Nest($this->CallWriter,'footnote_close');
                $this->_addCall('footnote_open', array(), $pos);
            break;
            case DOKU_LEXER_EXIT:
                // check whether we have already exitted the footnote mode, can happen if the modes were nested
                if (!$this->_footnote) {
                    $this->_addCall('cdata',array($match), $pos);
                    break;
                }

                $this->_footnote = false;
                $this->_addCall('footnote_close', array(), $pos);

                /** @var Nest $reWriter */
                $reWriter = $this->CallWriter;
                $this->CallWriter = $reWriter->process();
            break;
            case DOKU_LEXER_UNMATCHED:
                $this->_addCall('cdata', array($match), $pos);
            break;
        }
        return true;
    }

    function listblock($match, $state, $pos) {
        switch ( $state ) {
            case DOKU_LEXER_ENTER:
                $this->CallWriter = new Lists($this->CallWriter);
                $this->_addCall('list_open', array($match), $pos);
            break;
            case DOKU_LEXER_EXIT:
                $this->_addCall('list_close', array(), $pos);
                /** @var Lists $reWriter */
                $reWriter = $this->CallWriter;
                $this->CallWriter = $reWriter->process();
            break;
            case DOKU_LEXER_MATCHED:
                $this->_addCall('list_item', array($match), $pos);
            break;
            case DOKU_LEXER_UNMATCHED:
                $this->_addCall('cdata', array($match), $pos);
            break;
        }
        return true;
    }

    function unformatted($match, $state, $pos) {
        if ( $state == DOKU_LEXER_UNMATCHED ) {
            $this->_addCall('unformatted',array($match), $pos);
        }
        return true;
    }

    function php($match, $state, $pos) {
        global $conf;
        if ( $state == DOKU_LEXER_UNMATCHED ) {
            $this->_addCall('php',array($match), $pos);
        }
        return true;
    }

    function phpblock($match, $state, $pos) {
        global $conf;
        if ( $state == DOKU_LEXER_UNMATCHED ) {
            $this->_addCall('phpblock',array($match), $pos);
        }
        return true;
    }

    function html($match, $state, $pos) {
        global $conf;
        if ( $state == DOKU_LEXER_UNMATCHED ) {
            $this->_addCall('html',array($match), $pos);
        }
        return true;
    }

    function htmlblock($match, $state, $pos) {
        global $conf;
        if ( $state == DOKU_LEXER_UNMATCHED ) {
            $this->_addCall('htmlblock',array($match), $pos);
        }
        return true;
    }

    function preformatted($match, $state, $pos) {
        switch ( $state ) {
            case DOKU_LEXER_ENTER:
                $this->CallWriter = new Preformatted($this->CallWriter);
                $this->_addCall('preformatted_start',array(), $pos);
            break;
            case DOKU_LEXER_EXIT:
                $this->_addCall('preformatted_end',array(), $pos);
                /** @var Preformatted $reWriter */
                $reWriter = $this->CallWriter;
                $this->CallWriter = $reWriter->process();
            break;
            case DOKU_LEXER_MATCHED:
                $this->_addCall('preformatted_newline',array(), $pos);
            break;
            case DOKU_LEXER_UNMATCHED:
                $this->_addCall('preformatted_content',array($match), $pos);
            break;
        }

        return true;
    }

    function quote($match, $state, $pos) {

        switch ( $state ) {

            case DOKU_LEXER_ENTER:
                $this->CallWriter = new Quote($this->CallWriter);
                $this->_addCall('quote_start',array($match), $pos);
            break;

            case DOKU_LEXER_EXIT:
                $this->_addCall('quote_end',array(), $pos);
                /** @var Lists $reWriter */
                $reWriter = $this->CallWriter;
                $this->CallWriter = $reWriter->process();
            break;

            case DOKU_LEXER_MATCHED:
                $this->_addCall('quote_newline',array($match), $pos);
            break;

            case DOKU_LEXER_UNMATCHED:
                $this->_addCall('cdata',array($match), $pos);
            break;

        }

        return true;
    }

    /**
     * Internal function for parsing highlight options.
     * $options is parsed for key value pairs separated by commas.
     * A value might also be missing in which case the value will simple
     * be set to true. Commas in strings are ignored, e.g. option="4,56"
     * will work as expected and will only create one entry.
     *
     * @param string $options space separated list of key-value pairs,
     *                        e.g. option1=123, option2="456"
     * @return array|null     Array of key-value pairs $array['key'] = 'value';
     *                        or null if no entries found
     */
    protected function parse_highlight_options ($options) {
        $result = array();
        preg_match_all('/(\w+(?:="[^"]*"))|(\w+(?:=[^\s]*))|(\w+[^=\s\]])(?:\s*)/', $options, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $equal_sign = strpos($match [0], '=');
            if ($equal_sign === false) {
                $key = trim($match[0]);
                $result [$key] = 1;
            } else {
                $key = substr($match[0], 0, $equal_sign);
                $value = substr($match[0], $equal_sign+1);
                $value = trim($value, '"');
                if (strlen($value) > 0) {
                    $result [$key] = $value;
                } else {
                    $result [$key] = 1;
                }
            }
        }

        // Check for supported options
        $result = array_intersect_key(
            $result,
            array_flip(array(
                'enable_line_numbers',
                'start_line_numbers_at',
                'highlight_lines_extra',
                'enable_keyword_links')
            )
        );

        // Sanitize values
        if(isset($result['enable_line_numbers'])) {
            if($result['enable_line_numbers'] === 'false') {
                $result['enable_line_numbers'] = false;
            }
            $result['enable_line_numbers'] = (bool) $result['enable_line_numbers'];
        }
        if(isset($result['highlight_lines_extra'])) {
            $result['highlight_lines_extra'] = array_map('intval', explode(',', $result['highlight_lines_extra']));
            $result['highlight_lines_extra'] = array_filter($result['highlight_lines_extra']);
            $result['highlight_lines_extra'] = array_unique($result['highlight_lines_extra']);
        }
        if(isset($result['start_line_numbers_at'])) {
            $result['start_line_numbers_at'] = (int) $result['start_line_numbers_at'];
        }
        if(isset($result['enable_keyword_links'])) {
            if($result['enable_keyword_links'] === 'false') {
                $result['enable_keyword_links'] = false;
            }
            $result['enable_keyword_links'] = (bool) $result['enable_keyword_links'];
        }
        if (count($result) == 0) {
            return null;
        }

        return $result;
    }

    function file($match, $state, $pos) {
        return $this->code($match, $state, $pos, 'file');
    }

    function code($match, $state, $pos, $type='code') {
        if ( $state == DOKU_LEXER_UNMATCHED ) {
            $matches = explode('>',$match,2);
            // Cut out variable options enclosed in []
            preg_match('/\[.*\]/', $matches[0], $options);
            if (!empty($options[0])) {
                $matches[0] = str_replace($options[0], '', $matches[0]);
            }
            $param = preg_split('/\s+/', $matches[0], 2, PREG_SPLIT_NO_EMPTY);
            while(count($param) < 2) array_push($param, null);
            // We shortcut html here.
            if ($param[0] == 'html') $param[0] = 'html4strict';
            if ($param[0] == '-') $param[0] = null;
            array_unshift($param, $matches[1]);
            if (!empty($options[0])) {
                $param [] = $this->parse_highlight_options ($options[0]);
            }
            $this->_addCall($type, $param, $pos);
        }
        return true;
    }

    function acronym($match, $state, $pos) {
        $this->_addCall('acronym',array($match), $pos);
        return true;
    }

    function smiley($match, $state, $pos) {
        $this->_addCall('smiley',array($match), $pos);
        return true;
    }

    function wordblock($match, $state, $pos) {
        $this->_addCall('wordblock',array($match), $pos);
        return true;
    }

    function entity($match, $state, $pos) {
        $this->_addCall('entity',array($match), $pos);
        return true;
    }

    function multiplyentity($match, $state, $pos) {
        preg_match_all('/\d+/',$match,$matches);
        $this->_addCall('multiplyentity',array($matches[0][0],$matches[0][1]), $pos);
        return true;
    }

    function singlequoteopening($match, $state, $pos) {
        $this->_addCall('singlequoteopening',array(), $pos);
        return true;
    }

    function singlequoteclosing($match, $state, $pos) {
        $this->_addCall('singlequoteclosing',array(), $pos);
        return true;
    }

    function apostrophe($match, $state, $pos) {
        $this->_addCall('apostrophe',array(), $pos);
        return true;
    }

    function doublequoteopening($match, $state, $pos) {
        $this->_addCall('doublequoteopening',array(), $pos);
        $this->status['doublequote']++;
        return true;
    }

    function doublequoteclosing($match, $state, $pos) {
        if ($this->status['doublequote'] <= 0) {
            $this->doublequoteopening($match, $state, $pos);
        } else {
            $this->_addCall('doublequoteclosing',array(), $pos);
            $this->status['doublequote'] = max(0, --$this->status['doublequote']);
        }
        return true;
    }

    function camelcaselink($match, $state, $pos) {
        $this->_addCall('camelcaselink',array($match), $pos);
        return true;
    }

    /*
    */
    function internallink($match, $state, $pos) {
        // Strip the opening and closing markup
        $link = preg_replace(array('/^\[\[/','/\]\]$/u'),'',$match);

        // Split title from URL
        $link = explode('|',$link,2);
        if ( !isset($link[1]) ) {
            $link[1] = null;
        } else if ( preg_match('/^\{\{[^\}]+\}\}$/',$link[1]) ) {
            // If the title is an image, convert it to an array containing the image details
            $link[1] = Doku_Handler_Parse_Media($link[1]);
        }
        $link[0] = trim($link[0]);

        //decide which kind of link it is

        if ( link_isinterwiki($link[0]) ) {
            // Interwiki
            $interwiki = explode('>',$link[0],2);
            $this->_addCall(
                'interwikilink',
                array($link[0],$link[1],strtolower($interwiki[0]),$interwiki[1]),
                $pos
                );
        }elseif ( preg_match('/^\\\\\\\\[^\\\\]+?\\\\/u',$link[0]) ) {
            // Windows Share
            $this->_addCall(
                'windowssharelink',
                array($link[0],$link[1]),
                $pos
                );
        }elseif ( preg_match('#^([a-z0-9\-\.+]+?)://#i',$link[0]) ) {
            // external link (accepts all protocols)
            $this->_addCall(
                    'externallink',
                    array($link[0],$link[1]),
                    $pos
                    );
        }elseif ( preg_match('<'.PREG_PATTERN_VALID_EMAIL.'>',$link[0]) ) {
            // E-Mail (pattern above is defined in inc/mail.php)
            $this->_addCall(
                'emaillink',
                array($link[0],$link[1]),
                $pos
                );
        }elseif ( preg_match('!^#.+!',$link[0]) ){
            // local link
            $this->_addCall(
                'locallink',
                array(substr($link[0],1),$link[1]),
                $pos
                );
        }else{
            // internal link
            $this->_addCall(
                'internallink',
                array($link[0],$link[1]),
                $pos
                );
        }

        return true;
    }

    function filelink($match, $state, $pos) {
        $this->_addCall('filelink',array($match, null), $pos);
        return true;
    }

    function windowssharelink($match, $state, $pos) {
        $this->_addCall('windowssharelink',array($match, null), $pos);
        return true;
    }

    function media($match, $state, $pos) {
        $p = Doku_Handler_Parse_Media($match);

        $this->_addCall(
              $p['type'],
              array($p['src'], $p['title'], $p['align'], $p['width'],
                     $p['height'], $p['cache'], $p['linking']),
              $pos
             );
        return true;
    }

    function rss($match, $state, $pos) {
        $link = preg_replace(array('/^\{\{rss>/','/\}\}$/'),'',$match);

        // get params
        list($link,$params) = explode(' ',$link,2);

        $p = array();
        if(preg_match('/\b(\d+)\b/',$params,$match)){
            $p['max'] = $match[1];
        }else{
            $p['max'] = 8;
        }
        $p['reverse'] = (preg_match('/rev/',$params));
        $p['author']  = (preg_match('/\b(by|author)/',$params));
        $p['date']    = (preg_match('/\b(date)/',$params));
        $p['details'] = (preg_match('/\b(desc|detail)/',$params));
        $p['nosort']  = (preg_match('/\b(nosort)\b/',$params));

        if (preg_match('/\b(\d+)([dhm])\b/',$params,$match)) {
            $period = array('d' => 86400, 'h' => 3600, 'm' => 60);
            $p['refresh'] = max(600,$match[1]*$period[$match[2]]);  // n * period in seconds, minimum 10 minutes
        } else {
            $p['refresh'] = 14400;   // default to 4 hours
        }

        $this->_addCall('rss',array($link,$p),$pos);
        return true;
    }

    function externallink($match, $state, $pos) {
        $url   = $match;
        $title = null;

        // add protocol on simple short URLs
        if(substr($url,0,3) == 'ftp' && (substr($url,0,6) != 'ftp://')){
            $title = $url;
            $url   = 'ftp://'.$url;
        }
        if(substr($url,0,3) == 'www' && (substr($url,0,7) != 'http://')){
            $title = $url;
            $url = 'http://'.$url;
        }

        $this->_addCall('externallink',array($url, $title), $pos);
        return true;
    }

    function emaillink($match, $state, $pos) {
        $email = preg_replace(array('/^</','/>$/'),'',$match);
        $this->_addCall('emaillink',array($email, null), $pos);
        return true;
    }

    function table($match, $state, $pos) {
        switch ( $state ) {

            case DOKU_LEXER_ENTER:

                $this->CallWriter = new Table($this->CallWriter);

                $this->_addCall('table_start', array($pos + 1), $pos);
                if ( trim($match) == '^' ) {
                    $this->_addCall('tableheader', array(), $pos);
                } else {
                    $this->_addCall('tablecell', array(), $pos);
                }
            break;

            case DOKU_LEXER_EXIT:
                $this->_addCall('table_end', array($pos), $pos);
                /** @var Table $reWriter */
                $reWriter = $this->CallWriter;
                $this->CallWriter = $reWriter->process();
            break;

            case DOKU_LEXER_UNMATCHED:
                if ( trim($match) != '' ) {
                    $this->_addCall('cdata',array($match), $pos);
                }
            break;

            case DOKU_LEXER_MATCHED:
                if ( $match == ' ' ){
                    $this->_addCall('cdata', array($match), $pos);
                } else if ( preg_match('/:::/',$match) ) {
                    $this->_addCall('rowspan', array($match), $pos);
                } else if ( preg_match('/\t+/',$match) ) {
                    $this->_addCall('table_align', array($match), $pos);
                } else if ( preg_match('/ {2,}/',$match) ) {
                    $this->_addCall('table_align', array($match), $pos);
                } else if ( $match == "\n|" ) {
                    $this->_addCall('table_row', array(), $pos);
                    $this->_addCall('tablecell', array(), $pos);
                } else if ( $match == "\n^" ) {
                    $this->_addCall('table_row', array(), $pos);
                    $this->_addCall('tableheader', array(), $pos);
                } else if ( $match == '|' ) {
                    $this->_addCall('tablecell', array(), $pos);
                } else if ( $match == '^' ) {
                    $this->_addCall('tableheader', array(), $pos);
                }
            break;
        }
        return true;
    }
}

//------------------------------------------------------------------------
function Doku_Handler_Parse_Media($match) {

    // Strip the opening and closing markup
    $link = preg_replace(array('/^\{\{/','/\}\}$/u'),'',$match);

    // Split title from URL
    $link = explode('|',$link,2);

    // Check alignment
    $ralign = (bool)preg_match('/^ /',$link[0]);
    $lalign = (bool)preg_match('/ $/',$link[0]);

    // Logic = what's that ;)...
    if ( $lalign & $ralign ) {
        $align = 'center';
    } else if ( $ralign ) {
        $align = 'right';
    } else if ( $lalign ) {
        $align = 'left';
    } else {
        $align = null;
    }

    // The title...
    if ( !isset($link[1]) ) {
        $link[1] = null;
    }

    //remove aligning spaces
    $link[0] = trim($link[0]);

    //split into src and parameters (using the very last questionmark)
    $pos = strrpos($link[0], '?');
    if($pos !== false){
        $src   = substr($link[0],0,$pos);
        $param = substr($link[0],$pos+1);
    }else{
        $src   = $link[0];
        $param = '';
    }

    //parse width and height
    if(preg_match('#(\d+)(x(\d+))?#i',$param,$size)){
        !empty($size[1]) ? $w = $size[1] : $w = null;
        !empty($size[3]) ? $h = $size[3] : $h = null;
    } else {
        $w = null;
        $h = null;
    }

    //get linking command
    if(preg_match('/nolink/i',$param)){
        $linking = 'nolink';
    }else if(preg_match('/direct/i',$param)){
        $linking = 'direct';
    }else if(preg_match('/linkonly/i',$param)){
        $linking = 'linkonly';
    }else{
        $linking = 'details';
    }

    //get caching command
    if (preg_match('/(nocache|recache)/i',$param,$cachemode)){
        $cache = $cachemode[1];
    }else{
        $cache = 'cache';
    }

    // Check whether this is a local or remote image or interwiki
    if (media_isexternal($src) || link_isinterwiki($src)){
        $call = 'externalmedia';
    } else {
        $call = 'internalmedia';
    }

    $params = array(
        'type'=>$call,
        'src'=>$src,
        'title'=>$link[1],
        'align'=>$align,
        'width'=>$w,
        'height'=>$h,
        'cache'=>$cache,
        'linking'=>$linking,
    );

    return $params;
}



//Setup VIM: ex: et ts=4 :
