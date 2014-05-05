<?php
/**
 * Renderer for metadata
 *
 * @author Esther Brunner <wikidesign@gmail.com>
 */
if(!defined('DOKU_INC')) die('meh.');

if ( !defined('DOKU_LF') ) {
    // Some whitespace to help View > Source
    define ('DOKU_LF',"\n");
}

if ( !defined('DOKU_TAB') ) {
    // Some whitespace to help View > Source
    define ('DOKU_TAB',"\t");
}

require_once DOKU_INC . 'inc/parser/renderer.php';

/**
 * The Renderer
 */
class Doku_Renderer_metadata extends Doku_Renderer {

    var $doc  = '';
    var $meta = array();
    var $persistent = array();

    var $headers = array();
    var $capture = true;
    var $store   = '';
    var $firstimage = '';

    function getFormat(){
        return 'metadata';
    }

    function document_start(){
        global $ID;

        $this->headers = array();

        // external pages are missing create date
        if(!$this->persistent['date']['created']){
            $this->persistent['date']['created'] = filectime(wikiFN($ID));
        }
        if(!isset($this->persistent['user'])){
            $this->persistent['user'] = '';
        }
        if(!isset($this->persistent['creator'])){
            $this->persistent['creator'] = '';
        }
        // reset metadata to persistent values
        $this->meta = $this->persistent;
    }

    function document_end(){
        global $ID;

        // store internal info in metadata (notoc,nocache)
        $this->meta['internal'] = $this->info;

        if (!isset($this->meta['description']['abstract'])){
            // cut off too long abstracts
            $this->doc = trim($this->doc);
            if (strlen($this->doc) > 500)
                $this->doc = utf8_substr($this->doc, 0, 500).'…';
            $this->meta['description']['abstract'] = $this->doc;
        }

        $this->meta['relation']['firstimage'] = $this->firstimage;

        if(!isset($this->meta['date']['modified'])){
            $this->meta['date']['modified'] = filemtime(wikiFN($ID));
        }

    }

    function toc_additem($id, $text, $level) {
        global $conf;

        //only add items within configured levels
        if($level >= $conf['toptoclevel'] && $level <= $conf['maxtoclevel']){
            // the TOC is one of our standard ul list arrays ;-)
            $this->meta['description']['tableofcontents'][] = array(
              'hid'   => $id,
              'title' => $text,
              'type'  => 'ul',
              'level' => $level-$conf['toptoclevel']+1
            );
        }

    }

    function header($text, $level, $pos) {
        if (!isset($this->meta['title'])) $this->meta['title'] = $text;

        // add the header to the TOC
        $hid = $this->_headerToLink($text,'true');
        $this->toc_additem($hid, $text, $level);

        // add to summary
        if ($this->capture && ($level > 1)) $this->doc .= DOKU_LF.$text.DOKU_LF;
    }

    function section_open($level){}
    function section_close(){}

    function cdata($text){
      if ($this->capture) $this->doc .= $text;
    }

    function p_open(){
      if ($this->capture) $this->doc .= DOKU_LF;
    }

    function p_close(){
        if ($this->capture){
            if (strlen($this->doc) > 250) $this->capture = false;
            else $this->doc .= DOKU_LF;
        }
    }

    function linebreak(){
        if ($this->capture) $this->doc .= DOKU_LF;
    }

    function hr(){
        if ($this->capture){
            if (strlen($this->doc) > 250) $this->capture = false;
            else $this->doc .= DOKU_LF.'----------'.DOKU_LF;
        }
    }

    /**
     * Callback for footnote start syntax
     *
     * All following content will go to the footnote instead of
     * the document. To achieve this the previous rendered content
     * is moved to $store and $doc is cleared
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function footnote_open() {
        if ($this->capture){
            // move current content to store and record footnote
            $this->store = $this->doc;
            $this->doc   = '';
        }
    }

    /**
     * Callback for footnote end syntax
     *
     * All rendered content is moved to the $footnotes array and the old
     * content is restored from $store again
     *
     * @author Andreas Gohr
     */
    function footnote_close() {
        if ($this->capture){
            // restore old content
            $this->doc = $this->store;
            $this->store = '';
        }
    }

    function listu_open(){
        if ($this->capture) $this->doc .= DOKU_LF;
    }

    function listu_close(){
        if ($this->capture && (strlen($this->doc) > 250)) $this->capture = false;
    }

    function listo_open(){
        if ($this->capture) $this->doc .= DOKU_LF;
    }

    function listo_close(){
        if ($this->capture && (strlen($this->doc) > 250)) $this->capture = false;
    }

    function listitem_open($level){
        if ($this->capture) $this->doc .= str_repeat(DOKU_TAB, $level).'* ';
    }

    function listitem_close(){
        if ($this->capture) $this->doc .= DOKU_LF;
    }

    function listcontent_open(){}
    function listcontent_close(){}

    function unformatted($text){
        if ($this->capture) $this->doc .= $text;
    }

    function preformatted($text){
        if ($this->capture) $this->doc .= $text;
    }

    function file($text, $lang = null, $file = null){
        if ($this->capture){
            $this->doc .= DOKU_LF.$text;
            if (strlen($this->doc) > 250) $this->capture = false;
            else $this->doc .= DOKU_LF;
        }
    }

    function quote_open(){
        if ($this->capture) $this->doc .= DOKU_LF.DOKU_TAB.'"';
    }

    function quote_close(){
        if ($this->capture){
            $this->doc .= '"';
            if (strlen($this->doc) > 250) $this->capture = false;
            else $this->doc .= DOKU_LF;
        }
    }

    function code($text, $language = null, $file = null){
        if ($this->capture){
            $this->doc .= DOKU_LF.$text;
            if (strlen($this->doc) > 250) $this->capture = false;
            else $this->doc .= DOKU_LF;
      }
    }

    function acronym($acronym){
        if ($this->capture) $this->doc .= $acronym;
    }

    function smiley($smiley){
        if ($this->capture) $this->doc .= $smiley;
    }

    function entity($entity){
        if ($this->capture) $this->doc .= $entity;
    }

    function multiplyentity($x, $y){
        if ($this->capture) $this->doc .= $x.'×'.$y;
    }

    function singlequoteopening(){
        global $lang;
        if ($this->capture) $this->doc .= $lang['singlequoteopening'];
    }

    function singlequoteclosing(){
        global $lang;
        if ($this->capture) $this->doc .= $lang['singlequoteclosing'];
    }

    function apostrophe() {
        global $lang;
        if ($this->capture) $this->doc .= $lang['apostrophe'];
    }

    function doublequoteopening(){
        global $lang;
        if ($this->capture) $this->doc .= $lang['doublequoteopening'];
    }

    function doublequoteclosing(){
        global $lang;
        if ($this->capture) $this->doc .= $lang['doublequoteclosing'];
    }

    function camelcaselink($link) {
        $this->internallink($link, $link);
    }

    function locallink($hash, $name = null){
        if(is_array($name)) {
            $this->_firstimage($name['src']);
            if ($name['type'] == 'internalmedia') $this->_recordMediaUsage($name['src']);
        }
    }

    /**
     * keep track of internal links in $this->meta['relation']['references']
     */
    function internallink($id, $name = null){
        global $ID;

        if(is_array($name)) {
            $this->_firstimage($name['src']);
            if ($name['type'] == 'internalmedia') $this->_recordMediaUsage($name['src']);
        }

        $parts = explode('?', $id, 2);
        if (count($parts) === 2) {
            $id = $parts[0];
        }

        $default = $this->_simpleTitle($id);

        // first resolve and clean up the $id
        resolve_pageid(getNS($ID), $id, $exists);
        list($page, $hash) = explode('#', $id, 2);

        // set metadata
        $this->meta['relation']['references'][$page] = $exists;
        // $data = array('relation' => array('isreferencedby' => array($ID => true)));
        // p_set_metadata($id, $data);

        // add link title to summary
        if ($this->capture){
            $name = $this->_getLinkTitle($name, $default, $id);
            $this->doc .= $name;
        }
    }

    function externallink($url, $name = null){
        if(is_array($name)) {
            $this->_firstimage($name['src']);
            if ($name['type'] == 'internalmedia') $this->_recordMediaUsage($name['src']);
        }

        if ($this->capture){
            $this->doc .= $this->_getLinkTitle($name, '<' . $url . '>');
        }
    }

    function interwikilink($match, $name = null, $wikiName, $wikiUri){
        if(is_array($name)) {
            $this->_firstimage($name['src']);
            if ($name['type'] == 'internalmedia') $this->_recordMediaUsage($name['src']);
        }

        if ($this->capture){
            list($wikiUri, $hash) = explode('#', $wikiUri, 2);
            $name = $this->_getLinkTitle($name, $wikiUri);
            $this->doc .= $name;
        }
    }

    function windowssharelink($url, $name = null){
        if(is_array($name)) {
            $this->_firstimage($name['src']);
            if ($name['type'] == 'internalmedia') $this->_recordMediaUsage($name['src']);
        }

        if ($this->capture){
            if ($name) $this->doc .= $name;
            else $this->doc .= '<'.$url.'>';
        }
    }

    function emaillink($address, $name = null){
        if(is_array($name)) {
            $this->_firstimage($name['src']);
            if ($name['type'] == 'internalmedia') $this->_recordMediaUsage($name['src']);
        }

        if ($this->capture){
            if ($name) $this->doc .= $name;
            else $this->doc .= '<'.$address.'>';
        }
    }

    function internalmedia($src, $title=null, $align=null, $width=null,
                           $height=null, $cache=null, $linking=null){
        if ($this->capture && $title) $this->doc .= '['.$title.']';
        $this->_firstimage($src);
        $this->_recordMediaUsage($src);
    }

    function externalmedia($src, $title=null, $align=null, $width=null,
                           $height=null, $cache=null, $linking=null){
        if ($this->capture && $title) $this->doc .= '['.$title.']';
        $this->_firstimage($src);
    }

    function rss($url,$params) {
        $this->meta['relation']['haspart'][$url] = true;

        $this->meta['date']['valid']['age'] =
              isset($this->meta['date']['valid']['age']) ?
                  min($this->meta['date']['valid']['age'],$params['refresh']) :
                  $params['refresh'];
    }

    //----------------------------------------------------------
    // Utils

    /**
     * Removes any Namespace from the given name but keeps
     * casing and special chars
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _simpleTitle($name){
        global $conf;

        if(is_array($name)) return '';

        if($conf['useslash']){
            $nssep = '[:;/]';
        }else{
            $nssep = '[:;]';
        }
        $name = preg_replace('!.*'.$nssep.'!','',$name);
        //if there is a hash we use the anchor name only
        $name = preg_replace('!.*#!','',$name);
        return $name;
    }

    /**
     * Creates a linkid from a headline
     *
     * @param string  $title   The headline title
     * @param boolean $create  Create a new unique ID?
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _headerToLink($title, $create=false) {
        if($create){
            return sectionID($title,$this->headers);
        }else{
            $check = false;
            return sectionID($title,$check);
        }
    }

    /**
     * Construct a title and handle images in titles
     *
     * @author Harry Fuecks <hfuecks@gmail.com>
     */
    function _getLinkTitle($title, $default, $id=null) {
        global $conf;

        $isImage = false;
        if (is_array($title)){
            if($title['title']) return '['.$title['title'].']';
        } else if (is_null($title) || trim($title)==''){
            if (useHeading('content') && $id){
                $heading = p_get_first_heading($id,METADATA_DONT_RENDER);
                if ($heading) return $heading;
            }
            return $default;
        } else {
            return $title;
        }
    }

    function _firstimage($src){
        if($this->firstimage) return;
        global $ID;

        list($src,$hash) = explode('#',$src,2);
        if(!media_isexternal($src)){
            resolve_mediaid(getNS($ID),$src, $exists);
        }
        if(preg_match('/.(jpe?g|gif|png)$/i',$src)){
            $this->firstimage = $src;
        }
    }

    function _recordMediaUsage($src) {
        global $ID;

        list ($src, $hash) = explode('#', $src, 2);
        if (media_isexternal($src)) return;
        resolve_mediaid(getNS($ID), $src, $exists);
        $this->meta['relation']['media'][$src] = $exists;
    }
}

//Setup VIM: ex: et ts=4 :
