<?php
/**
 * Renderer output base class
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 * @author Andreas Gohr <andi@splitbrain.org>
 */
if(!defined('DOKU_INC')) die('meh.');
require_once DOKU_INC . 'inc/plugin.php';
require_once DOKU_INC . 'inc/pluginutils.php';

/**
 * An empty renderer, produces no output
 *
 * Inherits from DokuWiki_Plugin for giving additional functions to render plugins
 */
class Doku_Renderer extends DokuWiki_Plugin {
    var $info = array(
        'cache' => true, // may the rendered result cached?
        'toc'   => true, // render the TOC?
    );

    // keep some config options
    var $acronyms = array();
    var $smileys = array();
    var $badwords = array();
    var $entities = array();
    var $interwiki = array();

    // allows renderer to be used again, clean out any per-use values
    function reset() {
    }

    function nocache() {
        $this->info['cache'] = false;
    }

    function notoc() {
        $this->info['toc'] = false;
    }

    /**
     * Returns the format produced by this renderer.
     *
     * Has to be overidden by decendend classes
     */
    function getFormat(){
        trigger_error('getFormat() not implemented in '.get_class($this), E_USER_WARNING);
    }

    /**
     * Allow the plugin to prevent DokuWiki from reusing an instance
     *
     * @return bool   false if the plugin has to be instantiated
     */
    function isSingleton() {
        return false;
    }


    //handle plugin rendering
    function plugin($name,$data){
        $plugin =& plugin_load('syntax',$name);
        if($plugin != null){
            $plugin->render($this->getFormat(),$this,$data);
        }
    }

    /**
     * handle nested render instructions
     * this method (and nest_close method) should not be overloaded in actual renderer output classes
     */
    function nest($instructions) {

      foreach ( $instructions as $instruction ) {
        // execute the callback against ourself
        if (method_exists($this,$instruction[0])) {
          call_user_func_array(array($this, $instruction[0]),$instruction[1]);
        }
      }
    }

    // dummy closing instruction issued by Doku_Handler_Nest, normally the syntax mode should
    // override this instruction when instantiating Doku_Handler_Nest - however plugins will not
    // be able to - as their instructions require data.
    function nest_close() {}

    function document_start() {}

    function document_end() {}

    function render_TOC() { return ''; }

    function toc_additem($id, $text, $level) {}

    function header($text, $level, $pos) {}

    function section_open($level) {}

    function section_close() {}

    function cdata($text) {}

    function p_open() {}

    function p_close() {}

    function linebreak() {}

    function hr() {}

    function strong_open() {}

    function strong_close() {}

    function emphasis_open() {}

    function emphasis_close() {}

    function underline_open() {}

    function underline_close() {}

    function monospace_open() {}

    function monospace_close() {}

    function subscript_open() {}

    function subscript_close() {}

    function superscript_open() {}

    function superscript_close() {}

    function deleted_open() {}

    function deleted_close() {}

    function footnote_open() {}

    function footnote_close() {}

    function listu_open() {}

    function listu_close() {}

    function listo_open() {}

    function listo_close() {}

    function listitem_open($level) {}

    function listitem_close() {}

    function listcontent_open() {}

    function listcontent_close() {}

    function unformatted($text) {}

    function php($text) {}

    function phpblock($text) {}

    function html($text) {}

    function htmlblock($text) {}

    function preformatted($text) {}

    function quote_open() {}

    function quote_close() {}

    function file($text, $lang = null, $file = null ) {}

    function code($text, $lang = null, $file = null ) {}

    function acronym($acronym) {}

    function smiley($smiley) {}

    function wordblock($word) {}

    function entity($entity) {}

    // 640x480 ($x=640, $y=480)
    function multiplyentity($x, $y) {}

    function singlequoteopening() {}

    function singlequoteclosing() {}

    function apostrophe() {}

    function doublequoteopening() {}

    function doublequoteclosing() {}

    // $link like 'SomePage'
    function camelcaselink($link) {}

    function locallink($hash, $name = NULL) {}

    // $link like 'wiki:syntax', $title could be an array (media)
    function internallink($link, $title = NULL) {}

    // $link is full URL with scheme, $title could be an array (media)
    function externallink($link, $title = NULL) {}

    function rss ($url,$params) {}

    // $link is the original link - probably not much use
    // $wikiName is an indentifier for the wiki
    // $wikiUri is the URL fragment to append to some known URL
    function interwikilink($link, $title = NULL, $wikiName, $wikiUri) {}

    // Link to file on users OS, $title could be an array (media)
    function filelink($link, $title = NULL) {}

    // Link to a Windows share, , $title could be an array (media)
    function windowssharelink($link, $title = NULL) {}

//  function email($address, $title = NULL) {}
    function emaillink($address, $name = NULL) {}

    function internalmedia ($src, $title=NULL, $align=NULL, $width=NULL,
                            $height=NULL, $cache=NULL, $linking=NULL) {}

    function externalmedia ($src, $title=NULL, $align=NULL, $width=NULL,
                            $height=NULL, $cache=NULL, $linking=NULL) {}

    function internalmedialink (
        $src,$title=NULL,$align=NULL,$width=NULL,$height=NULL,$cache=NULL
        ) {}

    function externalmedialink(
        $src,$title=NULL,$align=NULL,$width=NULL,$height=NULL,$cache=NULL
        ) {}

    function table_open($maxcols = null, $numrows = null, $pos = null){}

    function table_close($pos = null){}

    function tablerow_open(){}

    function tablerow_close(){}

    function tableheader_open($colspan = 1, $align = NULL, $rowspan = 1){}

    function tableheader_close(){}

    function tablecell_open($colspan = 1, $align = NULL, $rowspan = 1){}

    function tablecell_close(){}


    // util functions follow, you probably won't need to reimplement them


    /**
     * Removes any Namespace from the given name but keeps
     * casing and special chars
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _simpleTitle($name){
        global $conf;

        //if there is a hash we use the ancor name only
        list($name,$hash) = explode('#',$name,2);
        if($hash) return $hash;

        $name = strtr($name,';',':');
        if($conf['useslash']){
            $name = strtr($name,'/',':');
        }

        return noNSorNS($name);
    }

    /**
     * Resolve an interwikilink
     */
    function _resolveInterWiki(&$shortcut,$reference){
        //get interwiki URL
        if ( isset($this->interwiki[$shortcut]) ) {
            $url = $this->interwiki[$shortcut];
        } else {
            // Default to Google I'm feeling lucky
            $url = 'http://www.google.com/search?q={URL}&amp;btnI=lucky';
            $shortcut = 'go';
        }

        //split into hash and url part
        list($reference,$hash) = explode('#',$reference,2);

        //replace placeholder
        if(preg_match('#\{(URL|NAME|SCHEME|HOST|PORT|PATH|QUERY)\}#',$url)){
            //use placeholders
            $url = str_replace('{URL}',rawurlencode($reference),$url);
            $url = str_replace('{NAME}',$reference,$url);
            $parsed = parse_url($reference);
            if(!$parsed['port']) $parsed['port'] = 80;
            $url = str_replace('{SCHEME}',$parsed['scheme'],$url);
            $url = str_replace('{HOST}',$parsed['host'],$url);
            $url = str_replace('{PORT}',$parsed['port'],$url);
            $url = str_replace('{PATH}',$parsed['path'],$url);
            $url = str_replace('{QUERY}',$parsed['query'],$url);
        }else{
            //default
            $url = $url.rawurlencode($reference);
        }
        if($hash) $url .= '#'.rawurlencode($hash);

        return $url;
    }
}


//Setup VIM: ex: et ts=4 :
