<?php
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');

class Doku_Handler {

    var $Renderer = NULL;

    var $CallWriter = NULL;

    var $calls = array();

    var $meta = array(
        'section' => FALSE,
    );

    var $rewriteBlocks = TRUE;

    function Doku_Handler() {
        $this->CallWriter = & new Doku_Handler_CallWriter($this);
    }

    function _addCall($handler, $args, $pos) {
        $call = array($handler,$args, $pos);
        $this->CallWriter->writeCall($call);
    }

    function _finalize(){
        if ( $this->meta['section'] ) {
            $S = & new Doku_Handler_Section();
            $this->calls = $S->process($this->calls);
        }

        if ( $this->rewriteBlocks ) {
            $B = & new Doku_Handler_Block();
            $this->calls = $B->process($this->calls);
        }

        array_unshift($this->calls,array('document_start',array(),0));
        $last_call = end($this->calls);
        array_push($this->calls,array('document_end',array(),$last_call[2]));
    }

    function fetch() {
        $call = each($this->calls);
        if ( $call ) {
            return $call['value'];
        }
        return FALSE;
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
        $this->_addCall('plugin',array($pluginname,$data,$pos),$pos);
        return TRUE;
    }

    function base($match, $state, $pos) {
        switch ( $state ) {
            case DOKU_LEXER_UNMATCHED:
                $this->_addCall('cdata',array($match), $pos);
                return TRUE;
            break;

        }
    }

    function header($match, $state, $pos) {
        $match = trim($match);
        $levels = array(
            '======'=>1,
            '====='=>2,
            '===='=>3,
            '==='=>4,
            '=='=>5,
        );
        $hsplit = preg_split( '/(={2,})/u', $match,-1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );

        // Locate the level - default to level 1 if no match (title contains == signs)
        if ( isset($hsplit[0]) && array_key_exists($hsplit[0], $levels) ) {
            $level = $levels[$hsplit[0]];
        } else {
            $level = 1;
        }

        // Strip markers and whitespaces
        $title = trim($match,'=');
        $title = trim($title,' ');

        $this->_addCall('header',array($title,$level,$pos), $pos);
        $this->meta['section'] = TRUE;
        return TRUE;
    }

    function notoc($match, $state, $pos) {
        $this->_addCall('notoc',array(),$pos);
        return TRUE;
    }

    function nocache($match, $state, $pos) {
        $this->_addCall('nocache',array(),$pos);
        return TRUE;
    }

    function linebreak($match, $state, $pos) {
        $this->_addCall('linebreak',array(),$pos);
        return TRUE;
    }

    function eol($match, $state, $pos) {
        $this->_addCall('eol',array(),$pos);
        return TRUE;
    }

    function hr($match, $state, $pos) {
        $this->_addCall('hr',array(),$pos);
        return TRUE;
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
        return TRUE;
    }

    function emphasis($match, $state, $pos) {
        $this->_nestingTag($match, $state, $pos, 'emphasis');
        return TRUE;
    }

    function underline($match, $state, $pos) {
        $this->_nestingTag($match, $state, $pos, 'underline');
        return TRUE;
    }

    function monospace($match, $state, $pos) {
        $this->_nestingTag($match, $state, $pos, 'monospace');
        return TRUE;
    }

    function subscript($match, $state, $pos) {
        $this->_nestingTag($match, $state, $pos, 'subscript');
        return TRUE;
    }

    function superscript($match, $state, $pos) {
        $this->_nestingTag($match, $state, $pos, 'superscript');
        return TRUE;
    }

    function deleted($match, $state, $pos) {
        $this->_nestingTag($match, $state, $pos, 'deleted');
        return TRUE;
    }


    function footnote($match, $state, $pos) {
        $this->_nestingTag($match, $state, $pos, 'footnote');
        return TRUE;
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
        return TRUE;
    }

    function unformatted($match, $state, $pos) {
        if ( $state == DOKU_LEXER_UNMATCHED ) {
            $this->_addCall('unformatted',array($match), $pos);
        }
        return TRUE;
    }

    function php($match, $state, $pos) {
        global $conf;
        if ( $state == DOKU_LEXER_UNMATCHED ) {
            if ($conf['phpok']) {
                $this->_addCall('php',array($match), $pos);
            } else {
                $this->_addCall('file',array($match), $pos);
            }
        }
        return TRUE;
    }

    function html($match, $state, $pos) {
        global $conf;
        if ( $state == DOKU_LEXER_UNMATCHED ) {
            if($conf['htmlok']){
                $this->_addCall('html',array($match), $pos);
            } else {
                $this->_addCall('file',array($match), $pos);
            }
        }
        return TRUE;
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

        return TRUE;
    }

    function file($match, $state, $pos) {
        if ( $state == DOKU_LEXER_UNMATCHED ) {
            $this->_addCall('file',array($match), $pos);
        }
        return TRUE;
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

        return TRUE;
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
        return TRUE;
    }

    function acronym($match, $state, $pos) {
        $this->_addCall('acronym',array($match), $pos);
        return TRUE;
    }

    function smiley($match, $state, $pos) {
        $this->_addCall('smiley',array($match), $pos);
        return TRUE;
    }

    function wordblock($match, $state, $pos) {
        $this->_addCall('wordblock',array($match), $pos);
        return TRUE;
    }

    function entity($match, $state, $pos) {
        $this->_addCall('entity',array($match), $pos);
        return TRUE;
    }

    function multiplyentity($match, $state, $pos) {
        preg_match_all('/\d+/',$match,$matches);
        $this->_addCall('multiplyentity',array($matches[0][0],$matches[0][1]), $pos);
        return TRUE;
    }

    function singlequoteopening($match, $state, $pos) {
        $this->_addCall('singlequoteopening',array(), $pos);
        return TRUE;
    }

    function singlequoteclosing($match, $state, $pos) {
        $this->_addCall('singlequoteclosing',array(), $pos);
        return TRUE;
    }

    function doublequoteopening($match, $state, $pos) {
        $this->_addCall('doublequoteopening',array(), $pos);
        return TRUE;
    }

    function doublequoteclosing($match, $state, $pos) {
        $this->_addCall('doublequoteclosing',array(), $pos);
        return TRUE;
    }

    function camelcaselink($match, $state, $pos) {
        $this->_addCall('camelcaselink',array($match), $pos);
        return TRUE;
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

        if ( preg_match('/^[a-zA-Z\.]+>{1}.*$/u',$link[0]) ) {
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
        }elseif ( preg_match('#([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i',$link[0]) ) {
        // E-Mail
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

        return TRUE;
    }

    function filelink($match, $state, $pos) {
        $this->_addCall('filelink',array($match, NULL), $pos);
        return TRUE;
    }

    function windowssharelink($match, $state, $pos) {
        $this->_addCall('windowssharelink',array($match, NULL), $pos);
        return TRUE;
    }

    function media($match, $state, $pos) {
        $p = Doku_Handler_Parse_Media($match);

        $this->_addCall(
              $p['type'],
              array($p['src'], $p['title'], $p['align'], $p['width'],
                     $p['height'], $p['cache'], $p['linking']),
              $pos
             );
        return TRUE;
    }

    function rss($match, $state, $pos) {
        $link = preg_replace(array('/^\{\{rss>/','/\}\}$/'),'',$match);
        $this->_addCall('rss',array($link),$pos);
        return TRUE;
    }

    function externallink($match, $state, $pos) {
        // Prevent use of multibyte strings in URLs
        // See: http://www.boingboing.net/2005/02/06/shmoo_group_exploit_.html
        // Not worried about other charsets so long as page is output as UTF-8
        /*if ( strlen($match) != utf8_strlen($match) ) {
            $this->_addCall('cdata',array($match), $pos);
        } else {*/

            $this->_addCall('externallink',array($match, NULL), $pos);
        //}
        return TRUE;
    }

    function emaillink($match, $state, $pos) {
        $email = preg_replace(array('/^</','/>$/'),'',$match);
        $this->_addCall('emaillink',array($email, NULL), $pos);
        return TRUE;
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
        return TRUE;
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
}

//------------------------------------------------------------------------
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
        $this->CallWriter->writeCalls($this->calls);
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
        $closeContent = TRUE;

        while ( $list = array_pop($this->listStack) ) {
            if ( $closeContent ) {
                $this->listCalls[] = array('listcontent_close',array(),$call[2]);
                $closeContent = FALSE;
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
        $this->CallWriter->writeCalls($this->calls);
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
                    $this->CallWriter->writeCall(array('preformatted',array($this->text),$this->pos));
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
        $this->CallWriter->writeCalls($this->calls);
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
    var $firstCell = FALSE;
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
        $this->CallWriter->writeCalls($this->calls);
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
        $this->firstCell = TRUE;
    }

    function tableEnd($call) {
        $this->tableCalls[] = array('table_close',array(),$call[2]);
        $this->finalizeTable();
    }

    function tableRowOpen($call) {
        $this->tableCalls[] = $call;
        $this->currentCols = 0;
        $this->firstCell = TRUE;
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
            $this->firstCell = FALSE;

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

                $this->tableCalls[$key-1][1][0] = FALSE;

                for($i = $key-2; $i > $lastRow; $i--) {

                    if ( $this->tableCalls[$i][0] == 'tablecell_open' || $this->tableCalls[$i][0] == 'tableheader_open' ) {

                        if ( FALSE !== $this->tableCalls[$i][1][0] ) {
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
        $inSection = FALSE;

        foreach ( $calls as $call ) {

            if ( $call[0] == 'header' ) {

                if ( $inSection ) {
                    $sectionCalls[] = array('section_close',array(), $call[2]);
                }

                $sectionCalls[] = $call;
                $sectionCalls[] = array('section_open',array($call[1][1]), $call[2]);
                $inSection = TRUE;

            } else {
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

    var $inParagraph = FALSE;
    var $atStart = TRUE;
    var $skipEolKey = -1;

    // Blocks these should not be inside paragraphs
    var $blockOpen = array(
            'header',
            'listu_open','listo_open','listitem_open','listcontent_open',
            'table_open','tablerow_open','tablecell_open','tableheader_open',
            'quote_open',
            'section_open', // Needed to prevent p_open between header and section_open
            'code','file','hr','preformatted',
        );

    var $blockClose = array(
            'header',
            'listu_close','listo_close','listitem_close','listcontent_close',
            'table_close','tablerow_close','tablecell_close','tableheader_close',
            'quote_close',
            'section_close', // Needed to prevent p_close after section_close
            'code','file','hr','preformatted',
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
        if(!is_array($DOKU_PLUGINS['syntax'])) return;
        foreach($DOKU_PLUGINS['syntax'] as $n => $p){
            $ptype = $p->getPType();
            if($ptype == 'block'){
                $this->blockOpen[]  = 'plugin_'.$n;
                $this->blockOpen[]  = 'plugin_'.$n.'_open';
                $this->blockClose[] = 'plugin_'.$n;
                $this->blockClose[] = 'plugin_'.$n.'_close';
            }elseif($ptype == 'stack'){
                $this->stackOpen[]  = 'plugin_'.$n;
                $this->stackOpen[]  = 'plugin_'.$n.'_open';
                $this->stackClose[] = 'plugin_'.$n;
                $this->stackClose[] = 'plugin_'.$n.'_close';
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
            $this->calls[] = array('p_close',array(), $pos);
        }
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
            if($cname == 'plugin') $cname='plugin_'.$call[1][0];

            // Process blocks which are stack like... (contain linefeeds)
            if ( in_array($cname,$this->stackOpen ) ) {
                /*
                if ( $this->atStart ) {
                    $this->calls[] = array('p_open',array(), $call[2]);
                    $this->atStart = FALSE;
                    $this->inParagraph = TRUE;
                }
                */
                $this->calls[] = $call;

                // Hack - footnotes shouldn't immediately contain a p_open
                if ( $cname != 'footnote_open' ) {
                    $this->addToStack();
                } else {
                    $this->addToStack(FALSE);
                }
                continue;
            }

            if ( in_array($cname,$this->stackClose ) ) {

                if ( $this->inParagraph ) {
                    //$this->calls[] = array('p_close',array(), $call[2]);
                    $this->closeParagraph($call[2]);
                }
                $this->calls[] = $call;
                $this->removeFromStack();
                continue;
            }

            if ( !$this->atStart ) {

                if ( $cname == 'eol' ) {


                    /* XXX
                    if ( $this->inParagraph ) {
                        $this->calls[] = array('p_close',array(), $call[2]);
                    }
                    $this->calls[] = array('p_open',array(), $call[2]);
                    $this->inParagraph = TRUE;
                    */

                    # Check this isn't an eol instruction to skip...
                    if ( $this->skipEolKey != $key ) {
                         # Look to see if the next instruction is an EOL
                        if ( isset($calls[$key+1]) && $calls[$key+1][0] == 'eol' ) {

                            if ( $this->inParagraph ) {
                                //$this->calls[] = array('p_close',array(), $call[2]);
                                $this->closeParagraph($call[2]);
                            }

                            $this->calls[] = array('p_open',array(), $call[2]);
                            $this->inParagraph = TRUE;


                            # Mark the next instruction for skipping
                            $this->skipEolKey = $key+1;

                        }else{
                            //if this is just a single eol make a space from it
                            $this->calls[] = array('cdata',array(" "), $call[2]);
                        }
                    }


                } else {

                    $storeCall = TRUE;
                    if ( $this->inParagraph && in_array($cname, $this->blockOpen) ) {
                        //$this->calls[] = array('p_close',array(), $call[2]);
                        $this->closeParagraph($call[2]);
                        $this->inParagraph = FALSE;
                        $this->calls[] = $call;
                        $storeCall = FALSE;
                    }

                    if ( in_array($cname, $this->blockClose) ) {
                        if ( $this->inParagraph ) {
                            //$this->calls[] = array('p_close',array(), $call[2]);
                            $this->closeParagraph($call[2]);
                            $this->inParagraph = FALSE;
                        }
                        if ( $storeCall ) {
                            $this->calls[] = $call;
                            $storeCall = FALSE;
                        }

                        // This really sucks and suggests this whole class sucks but...
                        if ( isset($calls[$key+1])
                            &&
                            !in_array($calls[$key+1][0], $this->blockOpen)
                            &&
                            !in_array($calls[$key+1][0], $this->blockClose)
                            ) {

                            $this->calls[] = array('p_open',array(), $call[2]);
                            $this->inParagraph = TRUE;
                        }
                    }

                    if ( $storeCall ) {
                        $this->calls[] = $call;
                    }

                }


            } else {

                // Unless there's already a block at the start, start a paragraph
                if ( !in_array($cname,$this->blockOpen) ) {
                    $this->calls[] = array('p_open',array(), $call[2]);
                    if ( $call[0] != 'eol' ) {
                        $this->calls[] = $call;
                    }
                    $this->atStart = FALSE;
                    $this->inParagraph = TRUE;
                } else {
                    $this->calls[] = $call;
                    $this->atStart = FALSE;
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

    function addToStack($newStart = TRUE) {
        $this->blockStack[] = array($this->atStart, $this->inParagraph);
        $this->atStart = $newStart;
        $this->inParagraph = FALSE;
    }

    function removeFromStack() {
        $state = array_pop($this->blockStack);
        $this->atStart = $state[0];
        $this->inParagraph = $state[1];
    }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
