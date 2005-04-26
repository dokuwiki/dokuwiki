<?php
/**
 * Renderer for XHTML output
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 * @author Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');

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
class Doku_Renderer_xhtml extends Doku_Renderer {

    var $doc = '';
    
    var $headers = array();
    
    var $footnotes = array();
    
    var $acronyms = array();
    var $smileys = array();
    var $badwords = array();
    var $entities = array();
    var $interwiki = array();

    var $lastsec = 0;

    var $store = '';


    function document_start() {
    }
    
    function document_end() {
        // add button for last section if any and more than one
        if($this->lastsec > 1) $this->_secedit($this->lastsec,'');
        
        if ( count ($this->footnotes) > 0 ) {
            $this->doc .= '<div class="footnotes">'.DOKU_LF;
            foreach ( $this->footnotes as $footnote ) {
                $this->doc .= $footnote;
            }
            $this->doc .= '</div>'.DOKU_LF;
        }
    }
    
    function toc_open() {
        $this->doc .= '<div class="toc">'.DOKU_LF;
        $this->doc .= '<div class="tocheader">Table of Contents <script type="text/javascript">showTocToggle("+","-")</script></div>'.DOKU_LF;
        $this->doc .= '<div id="tocinside">'.DOKU_LF;
    }
    
    function tocbranch_open($level) {
        $this->doc .= '<ul class="toc">'.DOKU_LF;
    }
    
    function tocitem_open($level, $empty = FALSE) {
        if ( !$empty ) {
            $this->doc .= '<li class="level'.$level.'">';
        } else {
            $this->doc .= '<li class="clear">';
        }
    }
    
    function tocelement($level, $title) {
        $this->doc .= '<span class="li"><a href="#'.$this->_headerToLink($title).'" class="toc">';
        $this->doc .= $this->_xmlEntities($title);
        $this->doc .= '</a></span>';
    }
    
    function tocitem_close($level) {
        $this->doc .= '</li>'.DOKU_LF;
    }
    
    function tocbranch_close($level) {
        $this->doc .= '</ul>'.DOKU_LF;
    }
    
    function toc_close() {
        $this->doc .= '</div>'.DOKU_LF.'</div>'.DOKU_LF;
    }
    
    function header($text, $level, $pos) {
        global $conf;
        //handle section editing 
        if($level <= $conf['maxseclevel']){
            // add button for last section if any
            if($this->lastsec) $this->_secedit($this->lastsec,$pos-1);
            // remember current position
            $this->lastsec = $pos;
        }

        $this->doc .= DOKU_LF.'<a name="'.$this->_headerToLink($text).'"></a><h'.$level.'>';
        $this->doc .= $this->_xmlEntities($text);
        $this->doc .= "</h$level>".DOKU_LF;
    }
    
    function section_open($level) {
        $this->doc .= "<div class=\"level$level\">".DOKU_LF;
    }
    
    function section_close() {
        $this->doc .= DOKU_LF.'</div>'.DOKU_LF;
    }
    
    function cdata($text) {
        $this->doc .= $this->_xmlEntities($text);
    }
    
    function p_open() {
        $this->doc .= DOKU_LF.'<p>'.DOKU_LF;
    }
    
    function p_close() {
        $this->doc .= DOKU_LF.'</p>'.DOKU_LF;
    }
    
    function linebreak() {
        $this->doc .= '<br/>'.DOKU_LF;
    }
    
    function hr() {
        $this->doc .= '<hr noshade="noshade" size="1" />'.DOKU_LF;
    }
    
    function strong_open() {
        $this->doc .= '<strong>';
    }
    
    function strong_close() {
        $this->doc .= '</strong>';
    }
    
    function emphasis_open() {
        $this->doc .= '<em>';
    }
    
    function emphasis_close() {
        $this->doc .= '</em>';
    }
    
    function underline_open() {
        $this->doc .= '<u>';
    }
    
    function underline_close() {
        $this->doc .= '</u>';
    }
    
    function monospace_open() {
        $this->doc .= '<code>';
    }
    
    function monospace_close() {
        $this->doc .= '</code>';
    }
    
    function subscript_open() {
        $this->doc .= '<sub>';
    }
    
    function subscript_close() {
        $this->doc .= '</sub>';
    }
    
    function superscript_open() {
        $this->doc .= '<sup>';
    }
    
    function superscript_close() {
        $this->doc .= '</sup>';
    }
    
    function deleted_open() {
        $this->doc .= '<del>';
    }
    
    function deleted_close() {
        $this->doc .= '</del>';
    }
    
    function footnote_open() {
        $id = $this->_newFootnoteId();
        $this->doc .= '<a href="#fn'.$id.'" name="fnt'.$id.'" class="fn_top">'.$id.')</a>';

        // move current content to store and record footnote
        $this->store = $this->doc;
        $this->doc   = '';

        $this->doc .= '<div class="fn">';
        $this->doc .= '<a href="#fnt'.$id.'" name="fn'.$id.'" class="fn_bot">';
        $this->doc .= $id.')</a> '.DOKU_LF;
    }
    
    function footnote_close() {
       # $contents = ob_get_contents();
       # ob_end_clean();
       # $id = array_pop($this->footnoteIdStack);
        
       # $contents = '<div class="fn"><a href="#fnt'.
       #     $id.'" name="fn'.$id.'" class="fn_bot">'.
       #         $id.')</a> ' .DOKU_LF .$contents. "\n" . '</div>' . DOKU_LF;
       # $this->footnotes[$id] = $contents;


        $this->doc .= '</div>' . DOKU_LF;

        // put recorded footnote into the stack and restore old content
        $this->footnotes[count($this->footnotes)] = $this->doc;
        $this->doc = $this->store;
        $this->store = '';
    }
    
    function listu_open() {
        $this->doc .= '<ul>'.DOKU_LF;
    }
    
    function listu_close() {
        $this->doc .= '</ul>'.DOKU_LF;
    }
    
    function listo_open() {
        $this->doc .= '<ol>'.DOKU_LF;
    }
    
    function listo_close() {
        $this->doc .= '</ol>'.DOKU_LF;
    }
    
    function listitem_open($level) {
        $this->doc .= '<li class="level'.$level.'">';
    }
    
    function listitem_close() {
        $this->doc .= '</li>'.DOKU_LF;
    }
    
    function listcontent_open() {
        $this->doc .= '<span class="li">';
    }
    
    function listcontent_close() {
        $this->doc .= '</span>'.DOKU_LF;
    }
     
    function unformatted($text) {
        $this->doc .= $this->_xmlEntities($text);
    }
    
    /**
    */
    function php($text) {
        global $conf;
        if($conf['phpok']){
            eval($text);
        }else{
            $this->file($text);
        }
    }
    
    /**
    */
    function html($text) {
        global $conf;
        if($conf['htmlok']){
          $this->doc .= $text;
        }else{
          $this->file($text);
        }
    }
    
    function preformatted($text) {
        $this->doc .= '<pre class="code">' . $this->_xmlEntities($text) . '</pre>'. DOKU_LF;
    }
    
    function file($text) {
        $this->doc .= '<pre class="file">' . $this->_xmlEntities($text). '</pre>'. DOKU_LF;
    }
    
    /**
    * @TODO Shouldn't this output <blockquote??
    */
    function quote_open() {
        $this->doc .= '<div class="quote">'.DOKU_LF;
    }
    
    /**
    * @TODO Shouldn't this output </blockquote>?
    */
    function quote_close() {
        $this->doc .= '</div>'.DOKU_LF;
    }
    
    /**
    */
    function code($text, $language = NULL) {
        global $conf;
    
        if ( is_null($language) ) {
            $this->preformatted($text);
        } else {
            // Handle with Geshi here FIXME: strip first beginning newline
            require_once(DOKU_INC . 'inc/geshi.php');
            $geshi = new GeSHi($text, strtolower($language), DOKU_INC . 'inc/geshi');
            $geshi->enable_classes();
            $geshi->set_header_type(GESHI_HEADER_PRE);
            $geshi->set_overall_class('code');
            $geshi->set_link_target($conf['target']['extern']);
            
            $text = $geshi->parse_code();
            $this->doc .= $text;
        }
    }
    
    function acronym($acronym) {
        
        if ( array_key_exists($acronym, $this->acronyms) ) {
            
            $title = $this->_xmlEntities($this->acronyms[$acronym]);
            
            $this->doc .= '<acronym title="'.$title
                .'">'.$this->_xmlEntities($acronym).'</acronym>';
                
        } else {
            $this->doc .= $this->_xmlEntities($acronym);
        }
    }
    
    /**
    */
    function smiley($smiley) {
        if ( array_key_exists($smiley, $this->smileys) ) {
            $title = $this->_xmlEntities($this->smileys[$smiley]);
            $this->doc .= '<img src="'.DOKU_BASE.'smileys/'.$this->smileys[$smiley].
                '" align="middle" alt="'.
                    $this->_xmlEntities($smiley).'" />';
        } else {
            $this->doc .= $this->_xmlEntities($smiley);
        }
    }
    
    /**
    * not used
    function wordblock($word) {
        if ( array_key_exists($word, $this->badwords) ) {
            $this->doc .= '** BLEEP **';
        } else {
            $this->doc .= $this->_xmlEntities($word);
        }
    }
    */
    
    function entity($entity) {
        if ( array_key_exists($entity, $this->entities) ) {
            $this->doc .= $this->entities[$entity];
        } else {
            $this->doc .= $this->_xmlEntities($entity);
        }
    }
    
    function multiplyentity($x, $y) {
        $this->doc .= "$x&times;$y";
    }
    
    function singlequoteopening() {
        $this->doc .= "&lsquo;";
    }
    
    function singlequoteclosing() {
        $this->doc .= "&rsquo;";
    }
    
    function doublequoteopening() {
        $this->doc .= "&ldquo;";
    }
    
    function doublequoteclosing() {
        $this->doc .= "&rdquo;";
    }
    
    /**
    */
    function camelcaselink($link) {
      $this->internallink($link,$link); 
    }
    
    /**
     * $search and $returnonly are not for the renderer but are used
     * elsewhere - no need to implement them in other renderers
     */
    function internallink($id, $name = NULL, $search=NULL,$returnonly=false) {
        global $conf;
        global $ID;

        $name = $this->_getLinkTitle($name, $this->_simpleTitle($id), $isImage, $id);
        resolve_pageid(getNS($ID),$id,$exists);

        if ( !$isImage ) {
            if ( $exists ) {
                $class='wikilink1';
            } else {
                $class='wikilink2';
            }
        } else {
            $class='media';
        }
        
        //prepare for formating
        $link['target'] = $conf['target']['wiki'];
        $link['style']  = '';
        $link['pre']    = '';
        $link['suf']    = '';
        $link['more']   = 'onclick="return svchk()" onkeypress="return svchk()"';
        $link['class']  = $class;
        $link['url']    = wl($id);
        $link['name']   = $name;
        $link['title']  = $id;

        //add search string
        if($search){
            ($conf['userewrite']) ? $link['url'].='?s=' : $link['url'].='&amp;s=';
            $link['url'] .= urlencode($search);
        }

        //output formatted
        if($returnonly){
            return $this->_formatLink($link);
        }else{
            $this->doc .= $this->_formatLink($link);
        }
    }
    
    function externallink($url, $name = NULL) {
        global $conf;

        $name = $this->_getLinkTitle($name, $url, $isImage);
        
        if ( !$isImage ) {
            $class='urlextern';
        } else {
            $class='media';
        }
        
        //prepare for formating
        $link['target'] = $conf['target']['extern'];
        $link['style']  = '';
        $link['pre']    = '';
        $link['suf']    = '';
        $link['more']   = 'onclick="return svchk()" onkeypress="return svchk()"';
        $link['class']  = $class;
        $link['url']    = $url;
        $link['name']   = $name;
        $link['title']  = $this->_xmlEntities($url);
        if($conf['relnofollow']) $link['more'] .= ' rel="nofollow"';

        //output formatted
        $this->doc .= $this->_formatLink($link);
    }
    
    /**
    */
    function interwikilink($match, $name = NULL, $wikiName, $wikiUri) {
        global $conf;
        
        $link = array();
        $link['target'] = $conf['target']['interwiki'];
        $link['pre']    = '';
        $link['suf']    = '';
        $link['more']   = 'onclick="return svchk()" onkeypress="return svchk()"';
        $link['name']   = $this->_getLinkTitle($name, $wikiUri, $isImage);

        if ( !$isImage ) {
            $link['class'] = 'interwiki';
        } else {
            $link['class'] = 'media';
        }

        //get interwiki URL
        if ( isset($this->interwiki[$wikiName]) ) {
            $url = $this->interwiki[$wikiName];
        } else {
            // Default to Google I'm feeling lucky
            $url = 'http://www.google.com/search?q={URL}&amp;btnI=lucky';
            $wikiName = 'go';
        }
       
        if(!$isImage){
            //if ico exists set additional style
            if(@file_exists(DOKU_INC.'interwiki/'.$wikiName.'.png')){
                $link['style']='background: transparent url('.DOKU_BASE.'interwiki/'.$wikiName.'.png) 0px 1px no-repeat;';
            }elseif(@file_exists(DOKU_INC.'interwiki/'.$wikiName.'.gif')){
                $link['style']='background: transparent url('.DOKU_BASE.'interwiki/'.$wikiName.'.gif) 0px 1px no-repeat;';
            }
        }

        //do we stay at the same server? Use local target
        if( strpos($url,DOKU_URL) === 0 ){
            $link['target'] = $conf['target']['wiki'];
        }

        //replace placeholder
        if(preg_match('#\{(URL|NAME|SCHEME|HOST|PORT|PATH|QUERY)\}#',$url)){
            //use placeholders
            $url = str_replace('{URL}',urlencode($wikiUri),$url);
            $url = str_replace('{NAME}',$wikiUri,$url);
            $parsed = parse_url($wikiUri);
            if(!$parsed['port']) $parsed['port'] = 80;
            $url = str_replace('{SCHEME}',$parsed['scheme'],$url);
            $url = str_replace('{HOST}',$parsed['host'],$url);
            $url = str_replace('{PORT}',$parsed['port'],$url);
            $url = str_replace('{PATH}',$parsed['path'],$url);
            $url = str_replace('{QUERY}',$parsed['query'],$url);
            $link['url'] = $url;
        }else{
            //default
            $link['url'] = $url.urlencode($wikiUri);
        }

        $link['title'] = htmlspecialchars($link['url']);

        //output formatted
        $this->doc .= $this->_formatLink($link);
    }
    
    /*
     * @deprecated not used!!!
     * @TODO Correct the CSS class for files? (not windows)
     * @TODO Remove hard coded URL to splitbrain.org
    function filelink($link, $title = NULL) {
        $this->doc .= '<a';
        
        $title = $this->_getLinkTitle($title, $link, $isImage);
        
        if ( !$isImage ) {
            $this->doc .= ' class="windows"';
        } else {
            $this->doc .= ' class="media"';
        }
        
        $this->doc .= ' href="'.$this->_xmlEntities($link).'"';
        
        $this->doc .= ' style="background: transparent url(http://wiki.splitbrain.org/images/windows.gif) 0px 1px no-repeat;"';
        
        $this->doc .= ' onclick="return svchk()" onkeypress="return svchk()">';
        
        $this->doc .= $title;
        
        $this->doc .= '</a>';
    }
    */
    
    /**
    */
    function windowssharelink($url, $name = NULL) {
        global $conf;
        global $lang;
        //simple setup
        $link['target'] = $conf['target']['windows'];
        $link['pre']    = '';
        $link['suf']   = '';
        $link['style']  = '';
        //Display error on browsers other than IE
        $link['more'] = 'onclick="if(document.all == null){alert(\''.
                        $this->_xmlEntities($lang['nosmblinks'],ENT_QUOTES).
                        '\');}" onkeypress="if(document.all == null){alert(\''.
                        $this->_xmlEntities($lang['nosmblinks'],ENT_QUOTES).'\');}"';

        $link['name'] = $this->_getLinkTitle($name, $url, $isImage);
        if ( !$isImage ) {
            $link['class'] = 'windows';
        } else {
            $link['class'] = 'media';
        }


        $link['title'] = $this->_xmlEntities($url);
        $url = str_replace('\\','/',$url);
        $url = 'file:///'.$url;
        $link['url'] = $url;

        //output formatted
        $this->doc .= $this->_formatLink($link);
    }
    
    function emaillink($address, $name = NULL) {
        global $conf;
        //simple setup
        $link = array();
        $link['target'] = '';
        $link['pre']    = '';
        $link['suf']   = '';
        $link['style']  = '';
        $link['more']   = '';
  
        //we just test for image here - we need to encode the title our self
        $this->_getLinkTitle($name, $address, $isImage);
        if ( !$isImage ) {
            $link['class']='mail';
        } else {
            $link['class']='media';
        }

        //shields up
        if($conf['mailguard']=='visible'){
            //the mail name gets some visible encoding
            $address = str_replace('@',' [at] ',$address);
            $address = str_replace('.',' [dot] ',$address);
            $address = str_replace('-',' [dash] ',$address);

            $title   = $this->_xmlEntities($address);
            if(empty($name)){
                $name = $this->_xmlEntities($address);
            }else{
                $name = $this->_xmlEntities($name);
            }
        }elseif($conf['mailguard']=='hex'){
            //encode every char to a hex entity
            for ($x=0; $x < strlen($address); $x++) {
                $encode .= '&#x' . bin2hex($address[$x]).';';
            }
            $address = $encode;
            $title   = $encode;
            if(empty($name)){
                $name = $encode;
            }else{
                $name = $this->_xmlEntities($name);
            }
        }else{
            //keep address as is
            $title   = $this->_xmlEntities($address);
            if(empty($name)){
                $name = $this->_xmlEntities($address);
            }else{
                $name = $this->_xmlEntities($name);
            }
        }
        
        $link['url']   = 'mailto:'.$address;
        $link['name']  = $name;
        $link['title'] = $title;

        //output formatted
        $this->doc .= $this->_formatLink($link);
    }
    
    /**
     * @todo don't add link for flash
     */
    function internalmedia ($src, $title=NULL, $align=NULL, $width=NULL,
                            $height=NULL, $cache=NULL) {
        global $conf;
        global $ID;
        resolve_mediaid(getNS($ID),$src, $exists);

        $link = array();
        $link['class']  = 'media';
        $link['style']  = '';
        $link['pre']    = '';
        $link['suf']    = '';
        $link['more']   = 'onclick="return svchk()" onkeypress="return svchk()"';
        $link['target'] = $conf['target']['media'];

        $link['title']  = $this->_xmlEntities($src);
        $link['url']    = DOKU_BASE.'fetch.php?cache='.$cache.'&amp;media='.urlencode($src);
        $link['name']   = $this->_media ($src, $title, $align, $width, $height, $cache);


        //output formatted
        $this->doc .= $this->_formatLink($link);
    }
    
    /**
     * @todo don't add link for flash
     */
    function externalmedia ($src, $title=NULL, $align=NULL, $width=NULL,
                            $height=NULL, $cache=NULL) {
        global $conf;

        $link = array();
        $link['class']  = 'media';
        $link['style']  = '';
        $link['pre']    = '';
        $link['suf']    = '';
        $link['more']   = 'onclick="return svchk()" onkeypress="return svchk()"';
        $link['target'] = $conf['target']['media'];

        $link['title']  = $this->_xmlEntities($src);
        $link['url']    = DOKU_BASE.'fetch.php?cache='.$cache.'&amp;media='.urlencode($src);
        $link['name']   = $this->_media ($src, $title, $align, $width, $height, $cache);


        //output formatted
        $this->doc .= $this->_formatLink($link);
    }

    /**
     * Renders an RSS feed using magpie
     * 
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function rss ($url){
        global $lang;
        define('MAGPIE_CACHE_ON', false); //we do our own caching
        define('MAGPIE_DIR', DOKU_INC.'inc/magpie/');
        define('MAGPIE_OUTPUT_ENCODING','UTF-8'); //return all feeds as UTF-8
        require_once(MAGPIE_DIR.'/rss_fetch.inc');

        //disable warning while fetching
        $elvl = error_reporting(E_ERROR);
        $rss  = fetch_rss($url);
        error_reporting($elvl);

        $this->doc .= '<ul class="rss">';
        if($rss){
            foreach ($rss->items as $item ) {
                $this->doc .= '<li>';
                $this->externallink($item['link'],$item['title']);
                $this->doc .= '</li>';
            }
        }else{
            $this->doc .= '<li>';
            $this->doc .= '<em>'.$lang['rssfailed'].'</em>';
            $this->externallink($url);
            $this->doc .= '</li>';
        }
        $this->doc .= '</ul>';
    }

    /**
     * Renders internal and external media
     * 
     * @author Andreas Gohr <andi@splitbrain.org>
     * @todo   handle center align
     * @todo   move to bottom
     */
    function _media ($src, $title=NULL, $align=NULL, $width=NULL,
                      $height=NULL, $cache=NULL) {

        $ret = '';

        list($ext,$mime) = mimetype($src);
        if(substr($mime,0,5) == 'image'){
            //add image tag
            $ret .= '<img src="'.DOKU_BASE.'fetch.php?w='.$width.'&amp;h='.$height.
                    '&amp;cache='.$cache.'&amp;media='.urlencode($src).'"';
            
            $ret .= ' class="media'.$align.'"';
        
            if (!is_null($title)) {
                $ret .= ' title="'.$this->_xmlEntities($title).'"';
                $ret .= ' alt="'.$this->_xmlEntities($title).'"';
            }else{
                $ret .= ' alt=""';
            }
            
            if ( !is_null($width) )
                $ret .= ' width="'.$this->_xmlEntities($width).'"';
        
            if ( !is_null($height) )
                $ret .= ' height="'.$this->_xmlEntities($height).'"';

            $ret .= ' />'; 

        }elseif($mime == 'application/x-shockwave-flash'){
            //FIXME default to a higher flash version?

            $ret .= '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"'.
                    ' codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=5,0,0,0"';
            if ( !is_null($width) ) $ret .= ' width="'.$this->_xmlEntities($width).'"';
            if ( !is_null($height) ) $ret .= ' height="'.$this->_xmlEntities($height).'"';
            $ret .= '>'.DOKU_LF;
            $ret .= '<param name="movie" value="'.DOKU_BASE.'fetch.php?media='.urlencode($src).'" />'.DOKU_LF;
            $ret .= '<param name="quality" value="high" />'.DOKU_LF;
            $ret .= '<embed src="'.DOKU_BASE.'fetch.php?media='.urlencode($src).'"'.
                    ' quality="high" bgcolor="#000000"';
            if ( !is_null($width) ) $ret .= ' width="'.$this->_xmlEntities($width).'"';
            if ( !is_null($height) ) $ret .= ' height="'.$this->_xmlEntities($height).'"';
            $ret .= ' type="application/x-shockwave-flash"'.
                    ' pluginspage="http://www.macromedia.com/shockwave/download/index.cgi'.
                    '?P1_Prod_Version=ShockwaveFlash"></embed>'.DOKU_LF;
            $ret .= '</object>'.DOKU_LF;

        }elseif(!is_null($title)){
            // well at least we have a title to display
            $ret .= $this->_xmlEntities($title);
        }else{
            // just show the source
            $ret .= $this->_xmlEntities($src);
        }

        return $ret;
    }
    
    // $numrows not yet implemented
    function table_open($maxcols = NULL, $numrows = NULL){
        $this->doc .= '<table class="inline">'.DOKU_LF;
    }
    
    function table_close(){
        $this->doc .= '</table>'.DOKU_LF.'<br />'.DOKU_LF;
    }
    
    function tablerow_open(){
        $this->doc .= DOKU_TAB . '<tr>' . DOKU_LF . DOKU_TAB . DOKU_TAB;
    }
    
    function tablerow_close(){
        $this->doc .= DOKU_LF . DOKU_TAB . '</tr>' . DOKU_LF;
    }
    
    function tableheader_open($colspan = 1, $align = NULL){
        $this->doc .= '<th';
        if ( !is_null($align) ) {
            $this->doc .= ' class="'.$align.'align"';
        }
        if ( $colspan > 1 ) {
            $this->doc .= ' colspan="'.$colspan.'"';
        }
        $this->doc .= '>';
    }
    
    function tableheader_close(){
        $this->doc .= '</th>';
    }
    
    function tablecell_open($colspan = 1, $align = NULL){
        $this->doc .= '<td';
        if ( !is_null($align) ) {
            $this->doc .= ' class="'.$align.'align"';
        }
        if ( $colspan > 1 ) {
            $this->doc .= ' colspan="'.$colspan.'"';
        }
        $this->doc .= '>';
    }
    
    function tablecell_close(){
        $this->doc .= '</td>';
    }
    
    //----------------------------------------------------------
    // Utils

    /**
     * Assembles all parts defined by the link formater below
     * Returns HTML for the link
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _formatLink($link){
        //make sure the url is XHTML compliant (skip mailto)
        if(substr($link['url'],0,7) != 'mailto:'){
            $link['url'] = str_replace('&','&amp;',$link['url']);
            $link['url'] = str_replace('&amp;amp;','&amp;',$link['url']);
        }
        //remove double encodings in titles
        $link['title'] = str_replace('&amp;amp;','&amp;',$link['title']);

        $ret  = '';
        $ret .= $link['pre'];
        $ret .= '<a href="'.$link['url'].'"';
        if($link['class'])  $ret .= ' class="'.$link['class'].'"';
        if($link['target']) $ret .= ' target="'.$link['target'].'"';
        if($link['title'])  $ret .= ' title="'.$link['title'].'"';
        if($link['style'])  $ret .= ' style="'.$link['style'].'"';
        if($link['more'])   $ret .= ' '.$link['more'];
        $ret .= '>';
        $ret .= $link['name'];
        $ret .= '</a>';
        $ret .= $link['suf'];
        return $ret;
    }

    /**
     * Removes any Namespace from the given name but keeps
     * casing and special chars
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _simpleTitle($name){
        global $conf;
        if($conf['useslash']){
            $nssep = '[:;/]';
        }else{
            $nssep = '[:;]';
        }
        return preg_replace('!.*'.$nssep.'!','',$name);
    }

    
    function _newFootnoteId() {
        static $id = 1;
        return $id++;
    }
    
    function _xmlEntities($string) {
        return htmlspecialchars($string);
    }
    
    /**
    * @TODO Tuning needed - e.g. utf8 strtolower ? 
    */
    function _headerToLink($title) {
        return str_replace(':','',cleanID($title));
    }

    /**
     * Adds code for section editing button
     */
    function _secedit($f, $t){
        $this->doc .= '<!-- SECTION ['.$f.'-'.$t.'] -->';
    }
    
    function _getLinkTitle($title, $default, & $isImage, $id=NULL) {
        global $conf;

        $isImage = FALSE;
        
        if ( is_null($title) ) {
	  if ($conf['useheading'] && $id) {
	    $heading = p_get_first_heading($id);
	    if ($heading) {
	      return $this->_xmlEntities($heading);
	    }
	  }
	  return $this->_xmlEntities($default);
            
        } else if ( is_string($title) ) {
            
            return $this->_xmlEntities($title);
            
        } else if ( is_array($title) ) {
            
            $isImage = TRUE;
            return $this->_imageTitle($title);
        
        }
    }
    
    /**
     * @TODO Resolve namespace on internal images
     */
    function _imageTitle($img) {

        //FIXME resolve internal links

        return $this->_media($img['src'],
                              $img['title'],
                              $img['align'],
                              $img['width'],
                              $img['height'],
                              $img['cache']);

/*        
        if ( $img['type'] == 'internalmedia' ) {
            
            // Resolve here...
            if ( strpos($img['src'],':') ) {
                $src = explode(':',$img['src']);
                $src = $src[1];
            } else {
                $src = $img['src'];
            }
            
            $imgStr = '<img class="media" src="http://wiki.splitbrain.org/media/wiki/'.$this->_xmlEntities($src).'"';
            
        } else {
            
            $imgStr = '<img class="media" src="'.$this->_xmlEntities($img['src']).'"';
            
        }
        
        if ( !is_null($img['title']) ) {
            $imgStr .= ' alt="'.$this->_xmlEntities($img['title']).'"';
        } else {
            $imgStr .= ' alt=""';
        }
        
        if ( !is_null($img['align']) ) {
            $imgStr .= ' align="'.$img['align'].'"';
        }
        
        if ( !is_null($img['width']) ) {
            $imgStr .= ' width="'.$this->_xmlEntities($img['width']).'"';
        }
        
        if ( !is_null($img['height']) ) {
            $imgStr .= ' height="'.$this->_xmlEntities($img['height']).'"';
        }
        
        $imgStr .= '/>';
        
        return $imgStr;
*/
    }
}

/**
* Test whether there's an image to display with this interwiki link
*/
function interwikiImgExists($name) {
    
    static $exists = array();
    
    if ( array_key_exists($name,$exists) ) {
        return $exists[$name];
    }
    
    if( @file_exists( DOKU. 'interwiki/'.$name.'.png') ) {
        $exists[$name] = 'png';
    } else if ( @file_exists( DOKU . 'interwiki/'.$name.'.gif') ) {
        $exists[$name] = 'gif';
    } else {
        $exists[$name] = FALSE;
    }
    
    return $exists[$name];
}

/**
 * For determining whether to use CSS class "wikilink1" or "wikilink2"
 * @todo use configinstead of DOKU_DATA
 * @deprecated -> resolve_pagename should be used
 */
function wikiPageExists($name) {
msg("deprecated wikiPageExists called",-1);    
    static $pages = array();
    
    if ( array_key_exists($name,$pages) ) {
        return $pages[$name];
    }
    
    $file = str_replace(':','/',$name).'.txt';
    
    if ( @file_exists( DOKU_DATA . $file ) ) {
        $pages[$name] = TRUE;
    } else {
        $pages[$name] = FALSE;
    }
    
    return $pages[$name];
}


//Setup VIM: ex: et ts=4 enc=utf-8 :
