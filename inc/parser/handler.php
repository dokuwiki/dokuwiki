<?php
if(!defined('DOKU_INC')) define('DOKU_INC',fullpath(dirname(__FILE__).'/../../').'/');

if (!defined('DOKU_PARSER_EOL')) define('DOKU_PARSER_EOL',"\n");   // add this to make handling test cases simpler

class Doku_Handler {

    var $Renderer = NULL;

    var $CallWriter = NULL;

    var $calls = array();

    var $status = array(
        'section' => false,
        'section_edit_start' => -1,
        'section_edit_level' => 1,
        'section_edit_title' => ''
    );

    var $rewriteBlocks = true;

    function Doku_Handler() {
        $this->CallWriter = & new Doku_Handler_CallWriter($this);
    }

    function _addCall($handler, $args, $pos) {
        $call = array($handler,$args, $pos);
        $this->CallWriter->writeCall($call);
    }

    function addPluginCall($plugin, $args, $state, $pos) {
        $call = array('plugin',array($plugin, $args, $state), $pos);
        $this->CallWriter->writeCall($call);
    }

    function _finalize(){

        $this->CallWriter->finalise();

        if ( $this->status['section'] ) {
           $last_call = end($this->calls);
           array_push($this->calls,array('section_close',array(), $last_call[2]));
           if ($this->status['section_edit_start']>1) {
               // ignore last edit section if there is only one header
               array_push($this->calls,array('section_edit',array($this->status['section_edit_start'], 0, $this->status['section_edit_level'], $this->status['section_edit_title']), $last_call[2]));
           }
        }

        if ( $this->rewriteBlocks ) {
            $B = & new Doku_Handler_Block();
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
        $plugin =& plugin_load('syntax',$pluginname);
        if($plugin != null){
            $data = $plugin->handle($match, $state, $pos, $this);
        }
        $this->addPluginCall($pluginname,$data,$state,$pos);
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
        global $conf;

        // get level and title
        $title = trim($match);
        $level = 7 - strspn($title,'=');
        if($level < 1) $level = 1;
        $title = trim($title,'=');
        $title = trim($title);

        if ($this->status['section']) $this->_addCall('section_close',array(),$pos);

        if ($level<=$conf['maxseclevel']) {
            $this->_addCall('section_edit',array($this->status['section_edit_start'], $pos-1, $this->status['section_edit_level'], $this->status['section_edit_title']), $pos);
            $this->status['section_edit_start'] = $pos;
            $this->status['section_edit_level'] = $level;
            $this->status['section_edit_title'] = $title;
        }

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

                $ReWriter = & new Doku_Handler_Nest($this->CallWriter,'footnote_close');
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
                $ReWriter = & new Doku_Handler_List($this->CallWriter);
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
                $ReWriter = & new Doku_Handler_Preformatted($this->CallWriter);
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

    function file($match, $state, $pos) {
        if ( $state == DOKU_LEXER_UNMATCHED ) {
            $this->_addCall('file',array($match), $pos);
        }
        return true;
    }

    function quote($match, $state, $pos) {

        switch ( $state ) {

            case DOKU_LEXER_ENTER:
                $ReWriter = & new Doku_Handler_Quote($this->CallWriter);
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

    function code($match, $state, $pos) {
        switch ( $state ) {
            case DOKU_LEXER_UNMATCHED:
                $matches = preg_split('/>/u',$match,2);
                $matches[0] = trim($matches[0]);
                if ( trim($matches[0]) == '' ) {
                    $matches[0] = NULL;
                }
                # $matches[0] contains name of programming language
                # if available, We shortcut html here.
                if($matches[0] == 'html') $matches[0] = 'html4strict';
                $this->_addCall(
                        'code',
                        array($matches[1],$matches[0]),
                        $pos
                    );
            break;
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
        $link = preg_split('/\|/u',$link,2);
        if ( !isset($link[1]) ) {
            $link[1] = NULL;
        } else if ( preg_match('/^\{\{[^\}]+\}\}$/',$link[1]) ) {
            // If the title is an image, convert it to an array containing the image details
            $link[1] = Doku_Handler_Parse_Media($link[1]);
        }
        $link[0] = trim($link[0]);

        //decide which kind of link it is

        if ( preg_match('/^[a-zA-Z0-9\.]+>{1}.*$/u',$link[0]) ) {
        // Interwiki
            $interwiki = preg_split('/>/u',$link[0]);
            $this->_addCall(
                'interwikilink',
                array($link[0],$link[1],strtolower($interwiki[0]),$interwiki[1]),
                $pos
                );
        }elseif ( preg_match('/^\\\\\\\\[\w.:?\-;,]+?\\\\/u',$link[0]) ) {
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
        $this->_addCall('filelink',array($match, NULL), $pos);
        return true;
    }

    function windowssharelink($match, $state, $pos) {
        $this->_addCall('windowssharelink',array($match, NULL), $pos);
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
        $this->_addCall('emaillink',array($email, NULL), $pos);
        return true;
    }

    function table($match, $state, $pos) {
        switch ( $state ) {

            case DOKU_LEXER_ENTER:

                $ReWriter = & new Doku_Handler_Table($this->CallWriter);
                $this->CallWriter = & $ReWriter;

                $this->_addCall('table_start', array(), $pos);
                //$this->_addCall('table_row', array(), $pos);
                if ( trim($match) == '^' ) {
                    $this->_addCall('tableheader', array(), $pos);
                } else {
                    $this->_addCall('tablecell', array(), $pos);
                }
            break;

            case DOKU_LEXER_EXIT:
                $this->_addCall('table_end', array(), $pos);
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
    $link = preg_split('/\|/u',$link,2);


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
        $align = NULL;
    }

    // The title...
    if ( !isset($link[1]) ) {
        $link[1] = NULL;
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
        ($size[1]) ? $w = $size[1] : $w = NULL;
        ($size[3]) ? $h = $size[3] : $h = NULL;
    } else {
        $w = NULL;
        $h = NULL;
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
    if ( preg_match('#^(https?|ftp)#i',$src) ) {
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
        return count(explode('  ',str_replace("\t",'  ',$match)));
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
    }

    //------------------------------------------------------------------------
    function process() {
        foreach ( $this->calls as $call ) {
            switch ( $call[0] ) {
                case 'table_start':
                    $this->tableStart($call);
                break;
                case 'table_row':
                    $this->tableRowClose(array('tablerow_close',$call[1],$call[2]));
                    $this->tableRowOpen(array('tablerow_open',$call[1],$call[2]));
                break;
                case 'tableheader':
                case 'tablecell':
                    $this->tableCell($call);
                break;
                case 'table_end':
                    $this->tableRowClose(array('tablerow_close',$call[1],$call[2]));
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
        $this->tableCalls[] = array('table_open',array(),$call[2]);
        $this->tableCalls[] = array('tablerow_open',array(),$call[2]);
        $this->firstCell = true;
    }

    function tableEnd($call) {
        $this->tableCalls[] = array('table_close',array(),$call[2]);
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

                // Its a spanning element - put it back and close it
                if ( $discard[1][0] > 1 ) {

                    $this->tableCalls[] = $discard;
                    if ( strstr($discard[0],'cell') ) {
                        $name = 'tablecell';
                    } else {
                        $name = 'tableheader';
                    }
                    $this->tableCalls[] = array($name.'_close',array(),$call[2]);
                }

                break;
            }
        }
        $this->tableCalls[] = $call;

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
            $this->tableCalls[] = array($call[0].'_open',array(1,NULL),$call[2]);
            $this->lastCellType = $call[0];

        } else {

            $this->tableCalls[] = array($call[0].'_open',array(1,NULL),$call[2]);
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
        } else {
            trigger_error('First element in table call list is not table_open');
        }

        $lastRow = 0;
        $lastCell = 0;
        $toDelete = array();

        // Look for the colspan elements and increment the colspan on the
        // previous non-empty opening cell. Once done, delete all the cells
        // that contain colspans
        foreach ( $this->tableCalls as $key => $call ) {

            if ( $call[0] == 'tablerow_open' ) {

                $lastRow = $key;

            } else if ( $call[0] == 'tablecell_open' || $call[0] == 'tableheader_open' ) {

                $lastCell = $key;

            } else if ( $call[0] == 'table_align' ) {

                // If the previous element was a cell open, align right
                if ( $this->tableCalls[$key-1][0] == 'tablecell_open' || $this->tableCalls[$key-1][0] == 'tableheader_open' ) {
                    $this->tableCalls[$key-1][1][1] = 'right';

                // If the next element if the close of an element, align either center or left
                } else if ( $this->tableCalls[$key+1][0] == 'tablecell_close' || $this->tableCalls[$key+1][0] == 'tableheader_close' ) {
                    if ( $this->tableCalls[$lastCell][1][1] == 'right' ) {
                        $this->tableCalls[$lastCell][1][1] = 'center';
                    } else {
                        $this->tableCalls[$lastCell][1][1] = 'left';
                    }

                }

                // Now convert the whitespace back to cdata
                $this->tableCalls[$key][0] = 'cdata';

            } else if ( $call[0] == 'colspan' ) {

                $this->tableCalls[$key-1][1][0] = false;

                for($i = $key-2; $i > $lastRow; $i--) {

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

//------------------------------------------------------------------------
class Doku_Handler_Section {

    function process($calls) {

        $sectionCalls = array();
        $inSection = false;

        foreach ( $calls as $call ) {

            if ( $call[0] == 'header' ) {

                if ( $inSection ) {
                    $sectionCalls[] = array('section_close',array(), $call[2]);
                }

                $sectionCalls[] = $call;
                $sectionCalls[] = array('section_open',array($call[1][1]), $call[2]);
                $inSection = true;

            } else {

                if ($call[0] == 'section_open' )  {
                    $inSection = true;
                } else if ($call[0] == 'section_open' ) {
                    $inSection = false;
                }
                $sectionCalls[] = $call;
            }
        }

        if ( $inSection ) {
            $sectionCalls[] = array('section_close',array(), $call[2]);
        }

        return $sectionCalls;
    }

}

/**
 * Handler for paragraphs
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 */
class Doku_Handler_Block {

    var $calls = array();

    var $blockStack = array();

    var $inParagraph = false;
    var $atStart = true;
    var $skipEolKey = -1;

    // Blocks these should not be inside paragraphs
    var $blockOpen = array(
            'header',
            'listu_open','listo_open','listitem_open','listcontent_open',
            'table_open','tablerow_open','tablecell_open','tableheader_open',
            'quote_open',
            'section_open', // Needed to prevent p_open between header and section_open
            'code','file','hr','preformatted','rss',
            'htmlblock','phpblock',
        );

    var $blockClose = array(
            'header',
            'listu_close','listo_close','listitem_close','listcontent_close',
            'table_close','tablerow_close','tablecell_close','tableheader_close',
            'quote_close',
            'section_close', // Needed to prevent p_close after section_close
            'code','file','hr','preformatted','rss',
            'htmlblock','phpblock',
        );

    // Stacks can contain paragraphs
    var $stackOpen = array(
        'footnote_open','section_open',
        );

    var $stackClose = array(
        'footnote_close','section_close',
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

    /**
     * Close a paragraph if needed
     *
     * This function makes sure there are no empty paragraphs on the stack
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function closeParagraph($pos){
        // look back if there was any content - we don't want empty paragraphs
        $content = '';
        for($i=count($this->calls)-1; $i>=0; $i--){
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
            array_splice($this->calls,$i);
        }else{
            if ($this->calls[count($this->calls)-1][0] == 'section_edit') {
                $tmp = array_pop($this->calls);
                $this->calls[] = array('p_close',array(), $pos);
                $this->calls[] = $tmp;
            } else {
                $this->calls[] = array('p_close',array(), $pos);
            }
        }

        $this->inParagraph = false;
    }

    /**
     * Processes the whole instruction stack to open and close paragraphs
     *
     * @author Harry Fuecks <hfuecks@gmail.com>
     * @author Andreas Gohr <andi@splitbrain.org>
     * @todo   This thing is really messy and should be rewritten
     */
    function process($calls) {
        foreach ( $calls as $key => $call ) {
            $cname = $call[0];
            if($cname == 'plugin') {
                $cname='plugin_'.$call[1][0];

                $plugin = true;
                $plugin_open = (($call[1][2] == DOKU_LEXER_ENTER) || ($call[1][2] == DOKU_LEXER_SPECIAL));
                $plugin_close = (($call[1][2] == DOKU_LEXER_EXIT) || ($call[1][2] == DOKU_LEXER_SPECIAL));
            } else {
                $plugin = false;
            }

            // Process blocks which are stack like... (contain linefeeds)
            if ( in_array($cname,$this->stackOpen ) && (!$plugin || $plugin_open) ) {

                $this->calls[] = $call;

                // Hack - footnotes shouldn't immediately contain a p_open
                if ( $cname != 'footnote_open' ) {
                    $this->addToStack();
                } else {
                    $this->addToStack(false);
                }
                continue;
            }

            if ( in_array($cname,$this->stackClose ) && (!$plugin || $plugin_close)) {

                if ( $this->inParagraph ) {
                    $this->closeParagraph($call[2]);
                }
                $this->calls[] = $call;
                $this->removeFromStack();
                continue;
            }

            if ( !$this->atStart ) {

                if ( $cname == 'eol' ) {

                    // Check this isn't an eol instruction to skip...
                    if ( $this->skipEolKey != $key ) {
                        // Look to see if the next instruction is an EOL
                        if ( isset($calls[$key+1]) && $calls[$key+1][0] == 'eol' ) {

                            if ( $this->inParagraph ) {
                                //$this->calls[] = array('p_close',array(), $call[2]);
                                $this->closeParagraph($call[2]);
                            }

                            $this->calls[] = array('p_open',array(), $call[2]);
                            $this->inParagraph = true;


                            // Mark the next instruction for skipping
                            $this->skipEolKey = $key+1;

                        }else{
                            //if this is just a single eol make a space from it
                            $this->addCall(array('cdata',array(DOKU_PARSER_EOL), $call[2]));
                        }
                    }


                } else {

                    $storeCall = true;
                    if ( $this->inParagraph && (in_array($cname, $this->blockOpen) && (!$plugin || $plugin_open))) {
                        $this->closeParagraph($call[2]);
                        $this->calls[] = $call;
                        $storeCall = false;
                    }

                    if ( in_array($cname, $this->blockClose) && (!$plugin || $plugin_close)) {
                        if ( $this->inParagraph ) {
                            $this->closeParagraph($call[2]);
                        }
                        if ( $storeCall ) {
                            $this->calls[] = $call;
                            $storeCall = false;
                        }

                        // This really sucks and suggests this whole class sucks but...
                        if ( isset($calls[$key+1])) {
                            $cname_plusone = $calls[$key+1][0];
                            if ($cname_plusone == 'plugin') {
                                $cname_plusone = 'plugin'.$calls[$key+1][1][0];
                                
                                // plugin test, true if plugin has a state which precludes it requiring blockOpen or blockClose
                                $plugin_plusone = true;
                                $plugin_test = ($call[$key+1][1][2] == DOKU_LEXER_MATCHED) || ($call[$key+1][1][2] == DOKU_LEXER_MATCHED);
                            } else {
                                $plugin_plusone = false;
                            }
                            if ((!in_array($cname_plusone, $this->blockOpen) && !in_array($cname_plusone, $this->blockClose)) ||
                                ($plugin_plusone && $plugin_test)
                                ) {

                                $this->calls[] = array('p_open',array(), $call[2]);
                                $this->inParagraph = true;
                            }
                        }
                    }

                    if ( $storeCall ) {
                        $this->addCall($call);
                    }

                }


            } else {

                // Unless there's already a block at the start, start a paragraph
                if ( !in_array($cname,$this->blockOpen) ) {
                    $this->calls[] = array('p_open',array(), $call[2]);
                    if ( $call[0] != 'eol' ) {
                        $this->calls[] = $call;
                    }
                    $this->atStart = false;
                    $this->inParagraph = true;
                } else {
                    $this->addCall($call);
                    $this->atStart = false;
                }

            }

        }

        if ( $this->inParagraph ) {
            if ( $cname == 'p_open' ) {
                // Ditch the last call
                array_pop($this->calls);
            } else if ( !in_array($cname, $this->blockClose) ) {
                //$this->calls[] = array('p_close',array(), $call[2]);
                $this->closeParagraph($call[2]);
            } else {
                $last_call = array_pop($this->calls);
                //$this->calls[] = array('p_close',array(), $call[2]);
                $this->closeParagraph($call[2]);
                $this->calls[] = $last_call;
            }
        }

        return $this->calls;
    }

    function addToStack($newStart = true) {
        $this->blockStack[] = array($this->atStart, $this->inParagraph);
        $this->atStart = $newStart;
        $this->inParagraph = false;
    }

    function removeFromStack() {
        $state = array_pop($this->blockStack);
        $this->atStart = $state[0];
        $this->inParagraph = $state[1];
    }

    function addCall($call) {
        $key = count($this->calls);
        if ($key and ($call[0] == 'cdata') and ($this->calls[$key-1][0] == 'cdata')) {
            $this->calls[$key-1][1][0] .= $call[1][0];
        } else {
            $this->calls[] = $call;
        }
    }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
