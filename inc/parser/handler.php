<?php
if(!defined('DOKU_INC')) die('meh.');
if (!defined('DOKU_PARSER_EOL')) define('DOKU_PARSER_EOL',"\n");   // add this to make handling test cases simpler

class Doku_Handler {

    var $Renderer = null;

    var $CallWriter = null;

    var $calls = array();

    var $status = array(
        'section' => false,
    );

    var $rewriteBlocks = true;

    function Doku_Handler() {
        $this->CallWriter = new Doku_Handler_CallWriter($this);
    }

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
            $B = new Doku_Handler_Block();
            $this->calls = $B->process($this->calls);
        }

        trigger_event('PARSER_HANDLER_DONE',$this);

        array_unshift($this->calls,array('document_start',array(),0));
        $last_call = end($this->calls);
        array_push($this->calls,array('document_end',array(),$last_call[2]));
    }

    function fetch() {
        $call = each($this->calls);
        if ( $call ) {
            return $call['value'];
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
     */
    function plugin($match, $state, $pos, $pluginname){
        $data = array($match);
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
//        $this->_nestingTag($match, $state, $pos, 'footnote');
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

                $ReWriter = new Doku_Handler_Nest($this->CallWriter,'footnote_close');
                $this->CallWriter = & $ReWriter;
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
                $this->CallWriter->process();
                $ReWriter = & $this->CallWriter;
                $this->CallWriter = & $ReWriter->CallWriter;
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
                $ReWriter = new Doku_Handler_List($this->CallWriter);
                $this->CallWriter = & $ReWriter;
                $this->_addCall('list_open', array($match), $pos);
            break;
            case DOKU_LEXER_EXIT:
                $this->_addCall('list_close', array(), $pos);
                $this->CallWriter->process();
                $ReWriter = & $this->CallWriter;
                $this->CallWriter = & $ReWriter->CallWriter;
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
                $ReWriter = new Doku_Handler_Preformatted($this->CallWriter);
                $this->CallWriter = & $ReWriter;
                $this->_addCall('preformatted_start',array(), $pos);
            break;
            case DOKU_LEXER_EXIT:
                $this->_addCall('preformatted_end',array(), $pos);
                $this->CallWriter->process();
                $ReWriter = & $this->CallWriter;
                $this->CallWriter = & $ReWriter->CallWriter;
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
                $ReWriter = new Doku_Handler_Quote($this->CallWriter);
                $this->CallWriter = & $ReWriter;
                $this->_addCall('quote_start',array($match), $pos);
            break;

            case DOKU_LEXER_EXIT:
                $this->_addCall('quote_end',array(), $pos);
                $this->CallWriter->process();
                $ReWriter = & $this->CallWriter;
                $this->CallWriter = & $ReWriter->CallWriter;
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

    function file($match, $state, $pos) {
        return $this->code($match, $state, $pos, 'file');
    }

    function code($match, $state, $pos, $type='code') {
        if ( $state == DOKU_LEXER_UNMATCHED ) {
            $matches = explode('>',$match,2);

            $param = preg_split('/\s+/', $matches[0], 2, PREG_SPLIT_NO_EMPTY);
            while(count($param) < 2) array_push($param, null);

            // We shortcut html here.
            if ($param[0] == 'html') $param[0] = 'html4strict';
            if ($param[0] == '-') $param[0] = null;
            array_unshift($param, $matches[1]);

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
        return true;
    }

    function doublequoteclosing($match, $state, $pos) {
        $this->_addCall('doublequoteclosing',array(), $pos);
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

        if ( preg_match('/^[a-zA-Z0-9\.]+>{1}.*$/u',$link[0]) ) {
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

                $ReWriter = new Doku_Handler_Table($this->CallWriter);
                $this->CallWriter = & $ReWriter;

                $this->_addCall('table_start', array($pos + 1), $pos);
                if ( trim($match) == '^' ) {
                    $this->_addCall('tableheader', array(), $pos);
                } else {
                    $this->_addCall('tablecell', array(), $pos);
                }
            break;

            case DOKU_LEXER_EXIT:
                $this->_addCall('table_end', array($pos), $pos);
                $this->CallWriter->process();
                $ReWriter = & $this->CallWriter;
                $this->CallWriter = & $ReWriter->CallWriter;
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

    // Check whether this is a local or remote image
    if ( media_isexternal($src) ) {
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

//------------------------------------------------------------------------
class Doku_Handler_CallWriter {

    var $Handler;

    function Doku_Handler_CallWriter(& $Handler) {
        $this->Handler = & $Handler;
    }

    function writeCall($call) {
        $this->Handler->calls[] = $call;
    }

    function writeCalls($calls) {
        $this->Handler->calls = array_merge($this->Handler->calls, $calls);
    }

    // function is required, but since this call writer is first/highest in
    // the chain it is not required to do anything
    function finalise() {
        unset($this->Handler);
    }
}

//------------------------------------------------------------------------
/**
 * Generic call writer class to handle nesting of rendering instructions
 * within a render instruction. Also see nest() method of renderer base class
 *
 * @author    Chris Smith <chris@jalakai.co.uk>
 */
class Doku_Handler_Nest {

    var $CallWriter;
    var $calls = array();

    var $closingInstruction;

    /**
     * constructor
     *
     * @param  object     $CallWriter     the renderers current call writer
     * @param  string     $close          closing instruction name, this is required to properly terminate the
     *                                    syntax mode if the document ends without a closing pattern
     */
    function Doku_Handler_Nest(& $CallWriter, $close="nest_close") {
        $this->CallWriter = & $CallWriter;

        $this->closingInstruction = $close;
    }

    function writeCall($call) {
        $this->calls[] = $call;
    }

    function writeCalls($calls) {
        $this->calls = array_merge($this->calls, $calls);
    }

    function finalise() {
        $last_call = end($this->calls);
        $this->writeCall(array($this->closingInstruction,array(), $last_call[2]));

        $this->process();
        $this->CallWriter->finalise();
        unset($this->CallWriter);
    }

    function process() {
        // merge consecutive cdata
        $unmerged_calls = $this->calls;
        $this->calls = array();

        foreach ($unmerged_calls as $call) $this->addCall($call);

        $first_call = reset($this->calls);
        $this->CallWriter->writeCall(array("nest", array($this->calls), $first_call[2]));
    }

    function addCall($call) {
        $key = count($this->calls);
        if ($key and ($call[0] == 'cdata') and ($this->calls[$key-1][0] == 'cdata')) {
            $this->calls[$key-1][1][0] .= $call[1][0];
        } else if ($call[0] == 'eol') {
            // do nothing (eol shouldn't be allowed, to counter preformatted fix in #1652 & #1699)
        } else {
            $this->calls[] = $call;
        }
    }
}

class Doku_Handler_List {

    var $CallWriter;

    var $calls = array();
    var $listCalls = array();
    var $listStack = array();

    function Doku_Handler_List(& $CallWriter) {
        $this->CallWriter = & $CallWriter;
    }

    function writeCall($call) {
        $this->calls[] = $call;
    }

    // Probably not needed but just in case...
    function writeCalls($calls) {
        $this->calls = array_merge($this->calls, $calls);
#        $this->CallWriter->writeCalls($this->calls);
    }

    function finalise() {
        $last_call = end($this->calls);
        $this->writeCall(array('list_close',array(), $last_call[2]));

        $this->process();
        $this->CallWriter->finalise();
        unset($this->CallWriter);
    }

    //------------------------------------------------------------------------
    function process() {

        foreach ( $this->calls as $call ) {
            switch ($call[0]) {
                case 'list_item':
                    $this->listOpen($call);
                break;
                case 'list_open':
                    $this->listStart($call);
                break;
                case 'list_close':
                    $this->listEnd($call);
                break;
                default:
                    $this->listContent($call);
                break;
            }
        }

        $this->CallWriter->writeCalls($this->listCalls);
    }

    //------------------------------------------------------------------------
    function listStart($call) {
        $depth = $this->interpretSyntax($call[1][0], $listType);

        $this->initialDepth = $depth;
        $this->listStack[] = array($listType, $depth);

        $this->listCalls[] = array('list'.$listType.'_open',array(),$call[2]);
        $this->listCalls[] = array('listitem_open',array(1),$call[2]);
        $this->listCalls[] = array('listcontent_open',array(),$call[2]);
    }

    //------------------------------------------------------------------------
    function listEnd($call) {
        $closeContent = true;

        while ( $list = array_pop($this->listStack) ) {
            if ( $closeContent ) {
                $this->listCalls[] = array('listcontent_close',array(),$call[2]);
                $closeContent = false;
            }
            $this->listCalls[] = array('listitem_close',array(),$call[2]);
            $this->listCalls[] = array('list'.$list[0].'_close', array(), $call[2]);
        }
    }

    //------------------------------------------------------------------------
    function listOpen($call) {
        $depth = $this->interpretSyntax($call[1][0], $listType);
        $end = end($this->listStack);

        // Not allowed to be shallower than initialDepth
        if ( $depth < $this->initialDepth ) {
            $depth = $this->initialDepth;
        }

        //------------------------------------------------------------------------
        if ( $depth == $end[1] ) {

            // Just another item in the list...
            if ( $listType == $end[0] ) {
                $this->listCalls[] = array('listcontent_close',array(),$call[2]);
                $this->listCalls[] = array('listitem_close',array(),$call[2]);
                $this->listCalls[] = array('listitem_open',array($depth-1),$call[2]);
                $this->listCalls[] = array('listcontent_open',array(),$call[2]);

            // Switched list type...
            } else {

                $this->listCalls[] = array('listcontent_close',array(),$call[2]);
                $this->listCalls[] = array('listitem_close',array(),$call[2]);
                $this->listCalls[] = array('list'.$end[0].'_close', array(), $call[2]);
                $this->listCalls[] = array('list'.$listType.'_open', array(), $call[2]);
                $this->listCalls[] = array('listitem_open', array($depth-1), $call[2]);
                $this->listCalls[] = array('listcontent_open',array(),$call[2]);

                array_pop($this->listStack);
                $this->listStack[] = array($listType, $depth);
            }

        //------------------------------------------------------------------------
        // Getting deeper...
        } else if ( $depth > $end[1] ) {

            $this->listCalls[] = array('listcontent_close',array(),$call[2]);
            $this->listCalls[] = array('list'.$listType.'_open', array(), $call[2]);
            $this->listCalls[] = array('listitem_open', array($depth-1), $call[2]);
            $this->listCalls[] = array('listcontent_open',array(),$call[2]);

            $this->listStack[] = array($listType, $depth);

        //------------------------------------------------------------------------
        // Getting shallower ( $depth < $end[1] )
        } else {
            $this->listCalls[] = array('listcontent_close',array(),$call[2]);
            $this->listCalls[] = array('listitem_close',array(),$call[2]);
            $this->listCalls[] = array('list'.$end[0].'_close',array(),$call[2]);

            // Throw away the end - done
            array_pop($this->listStack);

            while (1) {
                $end = end($this->listStack);

                if ( $end[1] <= $depth ) {

                    // Normalize depths
                    $depth = $end[1];

                    $this->listCalls[] = array('listitem_close',array(),$call[2]);

                    if ( $end[0] == $listType ) {
                        $this->listCalls[] = array('listitem_open',array($depth-1),$call[2]);
                        $this->listCalls[] = array('listcontent_open',array(),$call[2]);

                    } else {
                        // Switching list type...
                        $this->listCalls[] = array('list'.$end[0].'_close', array(), $call[2]);
                        $this->listCalls[] = array('list'.$listType.'_open', array(), $call[2]);
                        $this->listCalls[] = array('listitem_open', array($depth-1), $call[2]);
                        $this->listCalls[] = array('listcontent_open',array(),$call[2]);

                        array_pop($this->listStack);
                        $this->listStack[] = array($listType, $depth);
                    }

                    break;

                // Haven't dropped down far enough yet.... ( $end[1] > $depth )
                } else {

                    $this->listCalls[] = array('listitem_close',array(),$call[2]);
                    $this->listCalls[] = array('list'.$end[0].'_close',array(),$call[2]);

                    array_pop($this->listStack);

                }

            }

        }
    }

    //------------------------------------------------------------------------
    function listContent($call) {
        $this->listCalls[] = $call;
    }

    //------------------------------------------------------------------------
    function interpretSyntax($match, & $type) {
        if ( substr($match,-1) == '*' ) {
            $type = 'u';
        } else {
            $type = 'o';
        }
        // Is the +1 needed? It used to be count(explode(...))
        // but I don't think the number is seen outside this handler
        return substr_count(str_replace("\t",'  ',$match), '  ') + 1;
    }
}

//------------------------------------------------------------------------
class Doku_Handler_Preformatted {

    var $CallWriter;

    var $calls = array();
    var $pos;
    var $text ='';



    function Doku_Handler_Preformatted(& $CallWriter) {
        $this->CallWriter = & $CallWriter;
    }

    function writeCall($call) {
        $this->calls[] = $call;
    }

    // Probably not needed but just in case...
    function writeCalls($calls) {
        $this->calls = array_merge($this->calls, $calls);
#        $this->CallWriter->writeCalls($this->calls);
    }

    function finalise() {
        $last_call = end($this->calls);
        $this->writeCall(array('preformatted_end',array(), $last_call[2]));

        $this->process();
        $this->CallWriter->finalise();
        unset($this->CallWriter);
    }

    function process() {
        foreach ( $this->calls as $call ) {
            switch ($call[0]) {
                case 'preformatted_start':
                    $this->pos = $call[2];
                break;
                case 'preformatted_newline':
                    $this->text .= "\n";
                break;
                case 'preformatted_content':
                    $this->text .= $call[1][0];
                break;
                case 'preformatted_end':
                    if (trim($this->text)) {
                        $this->CallWriter->writeCall(array('preformatted',array($this->text),$this->pos));
                    }
                    // see FS#1699 & FS#1652, add 'eol' instructions to ensure proper triggering of following p_open
                    $this->CallWriter->writeCall(array('eol',array(),$this->pos));
                    $this->CallWriter->writeCall(array('eol',array(),$this->pos));
                break;
            }
        }
    }

}

//------------------------------------------------------------------------
class Doku_Handler_Quote {

    var $CallWriter;

    var $calls = array();

    var $quoteCalls = array();

    function Doku_Handler_Quote(& $CallWriter) {
        $this->CallWriter = & $CallWriter;
    }

    function writeCall($call) {
        $this->calls[] = $call;
    }

    // Probably not needed but just in case...
    function writeCalls($calls) {
        $this->calls = array_merge($this->calls, $calls);
    }

    function finalise() {
        $last_call = end($this->calls);
        $this->writeCall(array('quote_end',array(), $last_call[2]));

        $this->process();
        $this->CallWriter->finalise();
        unset($this->CallWriter);
    }

    function process() {

        $quoteDepth = 1;

        foreach ( $this->calls as $call ) {
            switch ($call[0]) {

                case 'quote_start':

                    $this->quoteCalls[] = array('quote_open',array(),$call[2]);

                case 'quote_newline':

                    $quoteLength = $this->getDepth($call[1][0]);

                    if ( $quoteLength > $quoteDepth ) {
                        $quoteDiff = $quoteLength - $quoteDepth;
                        for ( $i = 1; $i <= $quoteDiff; $i++ ) {
                            $this->quoteCalls[] = array('quote_open',array(),$call[2]);
                        }
                    } else if ( $quoteLength < $quoteDepth ) {
                        $quoteDiff = $quoteDepth - $quoteLength;
                        for ( $i = 1; $i <= $quoteDiff; $i++ ) {
                            $this->quoteCalls[] = array('quote_close',array(),$call[2]);
                        }
                    } else {
                        if ($call[0] != 'quote_start') $this->quoteCalls[] = array('linebreak',array(),$call[2]);
                    }

                    $quoteDepth = $quoteLength;

                break;

                case 'quote_end':

                    if ( $quoteDepth > 1 ) {
                        $quoteDiff = $quoteDepth - 1;
                        for ( $i = 1; $i <= $quoteDiff; $i++ ) {
                            $this->quoteCalls[] = array('quote_close',array(),$call[2]);
                        }
                    }

                    $this->quoteCalls[] = array('quote_close',array(),$call[2]);

                    $this->CallWriter->writeCalls($this->quoteCalls);
                break;

                default:
                    $this->quoteCalls[] = $call;
                break;
            }
        }
    }

    function getDepth($marker) {
        preg_match('/>{1,}/', $marker, $matches);
        $quoteLength = strlen($matches[0]);
        return $quoteLength;
    }
}

//------------------------------------------------------------------------
class Doku_Handler_Table {

    var $CallWriter;

    var $calls = array();
    var $tableCalls = array();
    var $maxCols = 0;
    var $maxRows = 1;
    var $currentCols = 0;
    var $firstCell = false;
    var $lastCellType = 'tablecell';

    function Doku_Handler_Table(& $CallWriter) {
        $this->CallWriter = & $CallWriter;
    }

    function writeCall($call) {
        $this->calls[] = $call;
    }

    // Probably not needed but just in case...
    function writeCalls($calls) {
        $this->calls = array_merge($this->calls, $calls);
    }

    function finalise() {
        $last_call = end($this->calls);
        $this->writeCall(array('table_end',array(), $last_call[2]));

        $this->process();
        $this->CallWriter->finalise();
        unset($this->CallWriter);
    }

    //------------------------------------------------------------------------
    function process() {
        foreach ( $this->calls as $call ) {
            switch ( $call[0] ) {
                case 'table_start':
                    $this->tableStart($call);
                break;
                case 'table_row':
                    $this->tableRowClose($call);
                    $this->tableRowOpen(array('tablerow_open',$call[1],$call[2]));
                break;
                case 'tableheader':
                case 'tablecell':
                    $this->tableCell($call);
                break;
                case 'table_end':
                    $this->tableRowClose($call);
                    $this->tableEnd($call);
                break;
                default:
                    $this->tableDefault($call);
                break;
            }
        }
        $this->CallWriter->writeCalls($this->tableCalls);
    }

    function tableStart($call) {
        $this->tableCalls[] = array('table_open',$call[1],$call[2]);
        $this->tableCalls[] = array('tablerow_open',array(),$call[2]);
        $this->firstCell = true;
    }

    function tableEnd($call) {
        $this->tableCalls[] = array('table_close',$call[1],$call[2]);
        $this->finalizeTable();
    }

    function tableRowOpen($call) {
        $this->tableCalls[] = $call;
        $this->currentCols = 0;
        $this->firstCell = true;
        $this->lastCellType = 'tablecell';
        $this->maxRows++;
    }

    function tableRowClose($call) {
        // Strip off final cell opening and anything after it
        while ( $discard = array_pop($this->tableCalls ) ) {

            if ( $discard[0] == 'tablecell_open' || $discard[0] == 'tableheader_open') {
                break;
            }
        }
        $this->tableCalls[] = array('tablerow_close', array(), $call[2]);

        if ( $this->currentCols > $this->maxCols ) {
            $this->maxCols = $this->currentCols;
        }
    }

    function tableCell($call) {
        if ( !$this->firstCell ) {

            // Increase the span
            $lastCall = end($this->tableCalls);

            // A cell call which follows an open cell means an empty cell so span
            if ( $lastCall[0] == 'tablecell_open' || $lastCall[0] == 'tableheader_open' ) {
                 $this->tableCalls[] = array('colspan',array(),$call[2]);

            }

            $this->tableCalls[] = array($this->lastCellType.'_close',array(),$call[2]);
            $this->tableCalls[] = array($call[0].'_open',array(1,null,1),$call[2]);
            $this->lastCellType = $call[0];

        } else {

            $this->tableCalls[] = array($call[0].'_open',array(1,null,1),$call[2]);
            $this->lastCellType = $call[0];
            $this->firstCell = false;

        }

        $this->currentCols++;
    }

    function tableDefault($call) {
        $this->tableCalls[] = $call;
    }

    function finalizeTable() {

        // Add the max cols and rows to the table opening
        if ( $this->tableCalls[0][0] == 'table_open' ) {
            // Adjust to num cols not num col delimeters
            $this->tableCalls[0][1][] = $this->maxCols - 1;
            $this->tableCalls[0][1][] = $this->maxRows;
            $this->tableCalls[0][1][] = array_shift($this->tableCalls[0][1]);
        } else {
            trigger_error('First element in table call list is not table_open');
        }

        $lastRow = 0;
        $lastCell = 0;
        $cellKey = array();
        $toDelete = array();

        // Look for the colspan elements and increment the colspan on the
        // previous non-empty opening cell. Once done, delete all the cells
        // that contain colspans
        for ($key = 0 ; $key < count($this->tableCalls) ; ++$key) {
            $call = $this->tableCalls[$key];

            switch ($call[0]) {
                case 'tablerow_open':

                    $lastRow++;
                    $lastCell = 0;
                    break;

                case 'tablecell_open':
                case 'tableheader_open':

                    $lastCell++;
                    $cellKey[$lastRow][$lastCell] = $key;
                    break;

                case 'table_align':

                    $prev = in_array($this->tableCalls[$key-1][0], array('tablecell_open', 'tableheader_open'));
                    $next = in_array($this->tableCalls[$key+1][0], array('tablecell_close', 'tableheader_close'));
                    // If the cell is empty, align left
                    if ($prev && $next) {
                        $this->tableCalls[$key-1][1][1] = 'left';

                    // If the previous element was a cell open, align right
                    } elseif ($prev) {
                        $this->tableCalls[$key-1][1][1] = 'right';

                    // If the next element is the close of an element, align either center or left
                    } elseif ( $next) {
                        if ( $this->tableCalls[$cellKey[$lastRow][$lastCell]][1][1] == 'right' ) {
                            $this->tableCalls[$cellKey[$lastRow][$lastCell]][1][1] = 'center';
                        } else {
                            $this->tableCalls[$cellKey[$lastRow][$lastCell]][1][1] = 'left';
                        }

                    }

                    // Now convert the whitespace back to cdata
                    $this->tableCalls[$key][0] = 'cdata';
                    break;

                case 'colspan':

                    $this->tableCalls[$key-1][1][0] = false;

                    for($i = $key-2; $i >= $cellKey[$lastRow][1]; $i--) {

                        if ( $this->tableCalls[$i][0] == 'tablecell_open' || $this->tableCalls[$i][0] == 'tableheader_open' ) {

                            if ( false !== $this->tableCalls[$i][1][0] ) {
                                $this->tableCalls[$i][1][0]++;
                                break;
                            }

                        }
                    }

                    $toDelete[] = $key-1;
                    $toDelete[] = $key;
                    $toDelete[] = $key+1;
                    break;

                case 'rowspan':

                    if ( $this->tableCalls[$key-1][0] == 'cdata' ) {
                        // ignore rowspan if previous call was cdata (text mixed with :::) we don't have to check next call as that wont match regex
                        $this->tableCalls[$key][0] = 'cdata';

                    } else {

                        $spanning_cell = null;
                        for($i = $lastRow-1; $i > 0; $i--) {

                            if ( $this->tableCalls[$cellKey[$i][$lastCell]][0] == 'tablecell_open' || $this->tableCalls[$cellKey[$i][$lastCell]][0] == 'tableheader_open' ) {

                                if ($this->tableCalls[$cellKey[$i][$lastCell]][1][2] >= $lastRow - $i) {
                                    $spanning_cell = $i;
                                    break;
                                }

                            }
                        }
                        if (is_null($spanning_cell)) {
                            // No spanning cell found, so convert this cell to
                            // an empty one to avoid broken tables
                            $this->tableCalls[$key][0] = 'cdata';
                            $this->tableCalls[$key][1][0] = '';
                            continue;
                        }
                        $this->tableCalls[$cellKey[$spanning_cell][$lastCell]][1][2]++;

                        $this->tableCalls[$key-1][1][2] = false;

                        $toDelete[] = $key-1;
                        $toDelete[] = $key;
                        $toDelete[] = $key+1;
                    }
                    break;

                case 'tablerow_close':

                    // Fix broken tables by adding missing cells
                    while (++$lastCell < $this->maxCols) {
                        array_splice($this->tableCalls, $key, 0, array(
                               array('tablecell_open', array(1, null, 1), $call[2]),
                               array('cdata', array(''), $call[2]),
                               array('tablecell_close', array(), $call[2])));
                        $key += 3;
                    }

                    break;

            }
        }

        // condense cdata
        $cnt = count($this->tableCalls);
        for( $key = 0; $key < $cnt; $key++){
            if($this->tableCalls[$key][0] == 'cdata'){
                $ckey = $key;
                $key++;
                while($this->tableCalls[$key][0] == 'cdata'){
                    $this->tableCalls[$ckey][1][0] .= $this->tableCalls[$key][1][0];
                    $toDelete[] = $key;
                    $key++;
                }
                continue;
            }
        }

        foreach ( $toDelete as $delete ) {
            unset($this->tableCalls[$delete]);
        }
        $this->tableCalls = array_values($this->tableCalls);
    }
}


/**
 * Handler for paragraphs
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 */
class Doku_Handler_Block {
    var $calls = array();
    var $skipEol = false;
    var $inParagraph = false;

    // Blocks these should not be inside paragraphs
    var $blockOpen = array(
            'header',
            'listu_open','listo_open','listitem_open','listcontent_open',
            'table_open','tablerow_open','tablecell_open','tableheader_open',
            'quote_open',
            'code','file','hr','preformatted','rss',
            'htmlblock','phpblock',
            'footnote_open',
        );

    var $blockClose = array(
            'header',
            'listu_close','listo_close','listitem_close','listcontent_close',
            'table_close','tablerow_close','tablecell_close','tableheader_close',
            'quote_close',
            'code','file','hr','preformatted','rss',
            'htmlblock','phpblock',
            'footnote_close',
        );

    // Stacks can contain paragraphs
    var $stackOpen = array(
        'section_open',
        );

    var $stackClose = array(
        'section_close',
        );


    /**
     * Constructor. Adds loaded syntax plugins to the block and stack
     * arrays
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function Doku_Handler_Block(){
        global $DOKU_PLUGINS;
        //check if syntax plugins were loaded
        if(empty($DOKU_PLUGINS['syntax'])) return;
        foreach($DOKU_PLUGINS['syntax'] as $n => $p){
            $ptype = $p->getPType();
            if($ptype == 'block'){
                $this->blockOpen[]  = 'plugin_'.$n;
                $this->blockClose[] = 'plugin_'.$n;
            }elseif($ptype == 'stack'){
                $this->stackOpen[]  = 'plugin_'.$n;
                $this->stackClose[] = 'plugin_'.$n;
            }
        }
    }

    function openParagraph($pos){
        if ($this->inParagraph) return;
        $this->calls[] = array('p_open',array(), $pos);
        $this->inParagraph = true;
        $this->skipEol = true;
    }

    /**
     * Close a paragraph if needed
     *
     * This function makes sure there are no empty paragraphs on the stack
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function closeParagraph($pos){
        if (!$this->inParagraph) return;
        // look back if there was any content - we don't want empty paragraphs
        $content = '';
        $ccount = count($this->calls);
        for($i=$ccount-1; $i>=0; $i--){
            if($this->calls[$i][0] == 'p_open'){
                break;
            }elseif($this->calls[$i][0] == 'cdata'){
                $content .= $this->calls[$i][1][0];
            }else{
                $content = 'found markup';
                break;
            }
        }

        if(trim($content)==''){
            //remove the whole paragraph
            //array_splice($this->calls,$i); // <- this is much slower than the loop below
            for($x=$ccount; $x>$i; $x--) array_pop($this->calls);
        }else{
            // remove ending linebreaks in the paragraph
            $i=count($this->calls)-1;
            if ($this->calls[$i][0] == 'cdata') $this->calls[$i][1][0] = rtrim($this->calls[$i][1][0],DOKU_PARSER_EOL);
            $this->calls[] = array('p_close',array(), $pos);
        }

        $this->inParagraph = false;
        $this->skipEol = true;
    }

    function addCall($call) {
        $key = count($this->calls);
        if ($key and ($call[0] == 'cdata') and ($this->calls[$key-1][0] == 'cdata')) {
            $this->calls[$key-1][1][0] .= $call[1][0];
        } else {
            $this->calls[] = $call;
        }
    }

    // simple version of addCall, without checking cdata
    function storeCall($call) {
        $this->calls[] = $call;
    }

    /**
     * Processes the whole instruction stack to open and close paragraphs
     *
     * @author Harry Fuecks <hfuecks@gmail.com>
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function process($calls) {
        // open first paragraph
        $this->openParagraph(0);
        foreach ( $calls as $key => $call ) {
            $cname = $call[0];
            if ($cname == 'plugin') {
                $cname='plugin_'.$call[1][0];
                $plugin = true;
                $plugin_open = (($call[1][2] == DOKU_LEXER_ENTER) || ($call[1][2] == DOKU_LEXER_SPECIAL));
                $plugin_close = (($call[1][2] == DOKU_LEXER_EXIT) || ($call[1][2] == DOKU_LEXER_SPECIAL));
            } else {
                $plugin = false;
            }
            /* stack */
            if ( in_array($cname,$this->stackClose ) && (!$plugin || $plugin_close)) {
                $this->closeParagraph($call[2]);
                $this->storeCall($call);
                $this->openParagraph($call[2]);
                continue;
            }
            if ( in_array($cname,$this->stackOpen ) && (!$plugin || $plugin_open) ) {
                $this->closeParagraph($call[2]);
                $this->storeCall($call);
                $this->openParagraph($call[2]);
                continue;
            }
            /* block */
            // If it's a substition it opens and closes at the same call.
            // To make sure next paragraph is correctly started, let close go first.
            if ( in_array($cname, $this->blockClose) && (!$plugin || $plugin_close)) {
                $this->closeParagraph($call[2]);
                $this->storeCall($call);
                $this->openParagraph($call[2]);
                continue;
            }
            if ( in_array($cname, $this->blockOpen) && (!$plugin || $plugin_open)) {
                $this->closeParagraph($call[2]);
                $this->storeCall($call);
                continue;
            }
            /* eol */
            if ( $cname == 'eol' ) {
                // Check this isn't an eol instruction to skip...
                if ( !$this->skipEol ) {
                    // Next is EOL => double eol => mark as paragraph
                    if ( isset($calls[$key+1]) && $calls[$key+1][0] == 'eol' ) {
                        $this->closeParagraph($call[2]);
                        $this->openParagraph($call[2]);
                    } else {
                        //if this is just a single eol make a space from it
                        $this->addCall(array('cdata',array(DOKU_PARSER_EOL), $call[2]));
                    }
                }
                continue;
            }
            /* normal */
            $this->addCall($call);
            $this->skipEol = false;
        }
        // close last paragraph
        $call = end($this->calls);
        $this->closeParagraph($call[2]);
        return $this->calls;
    }
}

//Setup VIM: ex: et ts=4 :
