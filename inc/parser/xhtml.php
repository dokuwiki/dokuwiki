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
class Doku_Renderer_XHTML extends Doku_Renderer {

    var $doc = '';
    
    var $headers = array();
    
    var $footnotes = array();
    
    var $footnoteIdStack = array();
    
    var $acronyms = array();
    var $smileys = array();
    var $badwords = array();
    var $entities = array();
    var $interwiki = array();

    var $lastsec = 0;

    function document_start() {
        ob_start();
    }
    
    function document_end() {
        // add button for last section if any
        if($this->lastsec) $this->__secedit($this->lastsec,'');
        
        if ( count ($this->footnotes) > 0 ) {
            echo '<div class="footnotes">'.DOKU_LF;
            foreach ( $this->footnotes as $footnote ) {
                echo $footnote;
            }
            echo '</div>'.DOKU_LF;
        }
        
        $this->doc .= ob_get_contents();
        ob_end_clean();

    }
    
    function toc_open() {
        echo '<div class="toc">'.DOKU_LF;
        echo '<div class="tocheader">Table of Contents <script type="text/javascript">showTocToggle("+","-")</script></div>'.DOKU_LF;
        echo '<div id="tocinside">'.DOKU_LF;
    }
    
    function tocbranch_open($level) {
        echo '<ul class="toc">'.DOKU_LF;
    }
    
    function tocitem_open($level, $empty = FALSE) {
        if ( !$empty ) {
            echo '<li class="level'.$level.'">';
        } else {
            echo '<li class="clear">';
        }
    }
    
    function tocelement($level, $title) {
        echo '<span class="li"><a href="#'.$this->__headerToLink($title).'" class="toc">';
        echo $this->__xmlEntities($title);
        echo '</a></span>';
    }
    
    function tocitem_close($level) {
        echo '</li>'.DOKU_LF;
    }
    
    function tocbranch_close($level) {
        echo '</ul>'.DOKU_LF;
    }
    
    function toc_close() {
        echo '</div>'.DOKU_LF.'</div>'.DOKU_LF;
    }
    
    function header($text, $level, $pos) {
        global $conf;
        //handle section editing 
        if($level <= $conf['maxseclevel']){
            // add button for last section if any
            if($this->lastsec) $this->__secedit($this->lastsec,$pos-1);
            // remember current position
            $this->lastsec = $pos;
        }

        echo DOKU_LF.'<a name="'.$this->__headerToLink($text).'"></a><h'.$level.'>';
        echo $this->__xmlEntities($text);
        echo "</h$level>".DOKU_LF;
    }
    
    function section_open($level) {
        echo "<div class=\"level$level\">".DOKU_LF;
    }
    
    function section_close() {
        echo DOKU_LF.'</div>'.DOKU_LF;
    }
    
    function cdata($text) {
        echo $this->__xmlEntities($text);
    }
    
    function p_open() {
        echo DOKU_LF.'<p>'.DOKU_LF;
    }
    
    function p_close() {
        echo DOKU_LF.'</p>'.DOKU_LF;
    }
    
    function linebreak() {
        echo '<br/>'.DOKU_LF;
    }
    
    function hr() {
        echo '<hr noshade="noshade" size="1" />'.DOKU_LF;
    }
    
    function strong_open() {
        echo '<strong>';
    }
    
    function strong_close() {
        echo '</strong>';
    }
    
    function emphasis_open() {
        echo '<em>';
    }
    
    function emphasis_close() {
        echo '</em>';
    }
    
    function underline_open() {
        echo '<u>';
    }
    
    function underline_close() {
        echo '</u>';
    }
    
    function monospace_open() {
        echo '<code>';
    }
    
    function monospace_close() {
        echo '</code>';
    }
    
    function subscript_open() {
        echo '<sub>';
    }
    
    function subscript_close() {
        echo '</sub>';
    }
    
    function superscript_open() {
        echo '<sup>';
    }
    
    function superscript_close() {
        echo '</sup>';
    }
    
    function deleted_open() {
        echo '<del>';
    }
    
    function deleted_close() {
        echo '</del>';
    }
    
    function footnote_open() {
        $id = $this->__newFootnoteId();
        echo '<a href="#fn'.$id.'" name="fnt'.$id.'" class="fn_top">'.$id.')</a>';
        $this->footnoteIdStack[] = $id;
        ob_start();
    }
    
    function footnote_close() {
        $contents = ob_get_contents();
        ob_end_clean();
        $id = array_pop($this->footnoteIdStack);
        
        $contents = '<div class="fn"><a href="#fnt'.
            $id.'" name="fn'.$id.'" class="fn_bot">'.
                $id.')</a> ' .DOKU_LF .$contents. "\n" . '</div>' . DOKU_LF;
        $this->footnotes[$id] = $contents;
    }
    
    function listu_open() {
        echo '<ul>'.DOKU_LF;
    }
    
    function listu_close() {
        echo '</ul>'.DOKU_LF;
    }
    
    function listo_open() {
        echo '<ol>'.DOKU_LF;
    }
    
    function listo_close() {
        echo '</ol>'.DOKU_LF;
    }
    
    function listitem_open($level) {
        echo '<li class="level'.$level.'">';
    }
    
    function listitem_close() {
        echo '</li>'.DOKU_LF;
    }
    
    function listcontent_open() {
        echo '<span class="li">';
    }
    
    function listcontent_close() {
        echo '</span>'.DOKU_LF;
    }
     
    function unformatted($text) {
        echo $this->__xmlEntities($text);
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
          echo $text;
        }else{
          $this->file($text);
        }
    }
    
    function preformatted($text) {
        echo '<pre class="code">' . $this->__xmlEntities($text) . '</pre>'. DOKU_LF;
    }
    
    function file($text) {
        echo '<pre class="file">' . $this->__xmlEntities($text). '</pre>'. DOKU_LF;
    }
    
    /**
    * @TODO Shouldn't this output <blockquote??
    */
    function quote_open() {
        echo '<div class="quote">'.DOKU_LF;
    }
    
    /**
    * @TODO Shouldn't this output </blockquote>?
    */
    function quote_close() {
        echo '</div>'.DOKU_LF;
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
            echo $text;
        }
    }
    
    function acronym($acronym) {
        
        if ( array_key_exists($acronym, $this->acronyms) ) {
            
            $title = $this->__xmlEntities($this->acronyms[$acronym]);
            
            echo '<acronym title="'.$title
                .'">'.$this->__xmlEntities($acronym).'</acronym>';
                
        } else {
            echo $this->__xmlEntities($acronym);
        }
    }
    
    /**
    */
    function smiley($smiley) {
        if ( array_key_exists($smiley, $this->smileys) ) {
            $title = $this->__xmlEntities($this->smileys[$smiley]);
            echo '<img src="'.DOKU_BASE.'smileys/'.$this->smileys[$smiley].
                '" align="middle" alt="'.
                    $this->__xmlEntities($smiley).'" />';
        } else {
            echo $this->__xmlEntities($smiley);
        }
    }
    
    /**
    * not used
    function wordblock($word) {
        if ( array_key_exists($word, $this->badwords) ) {
            echo '** BLEEP **';
        } else {
            echo $this->__xmlEntities($word);
        }
    }
    */
    
    function entity($entity) {
        if ( array_key_exists($entity, $this->entities) ) {
            echo $this->entities[$entity];
        } else {
            echo $this->__xmlEntities($entity);
        }
    }
    
    function multiplyentity($x, $y) {
        echo "$x&times;$y";
    }
    
    function singlequoteopening() {
        echo "&lsquo;";
    }
    
    function singlequoteclosing() {
        echo "&rsquo;";
    }
    
    function doublequoteopening() {
        echo "&ldquo;";
    }
    
    function doublequoteclosing() {
        echo "&rdquo;";
    }
    
    /**
    */
    function camelcaselink($link) {
      $this->internallink($link,$link); 
    }
    
    function internallink($id, $name = NULL) {
        global $conf;

        $name = $this->__getLinkTitle($name, $this->__simpleTitle($id), $isImage);
        resolve_pageid($id,$exists);

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

        //output formatted
        echo $this->__formatLink($link);
    }
    
    function externallink($url, $name = NULL) {
        global $conf;

        $name = $this->__getLinkTitle($name, $url, $isImage);
        
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
        $link['title']  = $this->__xmlEntities($url);
        if($conf['relnofollow']) $link['more'] .= ' rel="nofollow"';

        //output formatted
        echo $this->__formatLink($link);
    }
    
    /**
    * @TODO Remove hard coded link to splitbrain.org on style
    */
    function interwikilink($match, $name = NULL, $wikiName, $wikiUri) {
        global $conf;
        
        $link = array();
        $link['target'] = $conf['target']['interwiki'];
        $link['pre']    = '';
        $link['suf']    = '';
        $link['more']   = 'onclick="return svchk()" onkeypress="return svchk()"';
        $link['name']   = $this->__getLinkTitle($name, $wikiUri, $isImage);

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
        echo $this->__formatLink($link);
    }
    
    /**
     * @deprecated not used!!!
     * @TODO Correct the CSS class for files? (not windows)
     * @TODO Remove hard coded URL to splitbrain.org
     */
    function filelink($link, $title = NULL) {
        echo '<a';
        
        $title = $this->__getLinkTitle($title, $link, $isImage);
        
        if ( !$isImage ) {
            echo ' class="windows"';
        } else {
            echo ' class="media"';
        }
        
        echo ' href="'.$this->__xmlEntities($link).'"';
        
        echo ' style="background: transparent url(http://wiki.splitbrain.org/images/windows.gif) 0px 1px no-repeat;"';
        
        echo ' onclick="return svchk()" onkeypress="return svchk()">';
        
        echo $title;
        
        echo '</a>';
    }
    
    /**
    * @TODO Remove hard coded URL to splitbrain.org
    * @TODO Add error message for non-IE users
    */
    function windowssharelink($link, $title = NULL) {
        echo '<a';
        
        $title = $this->__getLinkTitle($title, $link, $isImage);
        
        if ( !$isImage ) {
            echo ' class="windows"';
        } else {
            echo ' class="media"';
        }
        
        $link = str_replace('\\','/',$link);
        $link = 'file:///'.$link;
        echo ' href="'.$this->__xmlEntities($link).'"';
        
        echo ' style="background: transparent url(http://wiki.splitbrain.org/images/windows.gif) 0px 1px no-repeat;"';
        
        echo ' onclick="return svchk()" onkeypress="return svchk()">';
        
        echo $title;
        
        echo '</a>';
    }
    
    /**
    * @TODO Protect email address from harvesters
    * @TODO Remove hard coded link to splitbrain.org
    */
    function email($address, $title = NULL) {
        echo '<a';
        
        $title = $this->__getLinkTitle($title, $address, $isImage);
        
        if ( !$isImage ) {
            echo ' class="mail"';
        } else {
            echo ' class="media"';
        }
        
        echo ' href="mailto:'.$this->__xmlEntities($address).'"';
        
        echo ' style="background: transparent url(http://wiki.splitbrain.org/images/mail_icon.gif) 0px 1px no-repeat;"';
        
        echo ' onclick="return svchk()" onkeypress="return svchk()">';
        
        echo $title;
        
        echo '</a>';
        
    }
    
    /**
     * @todo don't add link for flash
     */
    function internalmedia ($src, $title=NULL, $align=NULL, $width=NULL,
                            $height=NULL, $cache=NULL) {
        
        resolve_mediaid($src, $exists);

        $this->internallink($src, $title =
                                    array( 'type'   => 'internalmedia',
                                           'src'    => $src,
                                           'title'  => $title,
                                           'align'  => $align,
                                           'width'  => $width,
                                           'height' => $height,
                                           'cache'  => $cache,
                                           'link'   => $link ));
    }
    
    /**
     * @todo don't add link for flash
     */
    function externalmedia ($src, $title=NULL, $align=NULL, $width=NULL,
                            $height=NULL, $cache=NULL) {

        $this->externallink($src, $title =
                                    array( 'type'   => 'externalmedia',
                                           'src'    => $src,
                                           'title'  => $title,
                                           'align'  => $align,
                                           'width'  => $width,
                                           'height' => $height,
                                           'cache'  => $cache,
                                           'link'   => $link ));
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

        print '<ul class="rss">';
        if($rss){
            foreach ($rss->items as $item ) {
                print '<li>';
                $this->externallink($item['link'],$item['title']);
                print '</li>';
            }
        }else{
            print '<li>';
            print '<em>'.$lang['rssfailed'].'</em>';
            $this->externallink($url);
            print '</li>';
        }
        print '</ul>';
    }

    /**
     * Renders internal and external media
     * 
     * @author Andreas Gohr <andi@splitbrain.org>
     * @todo   handle center align
     * @todo   move to bottom
     */
    function __media ($src, $title=NULL, $align=NULL, $width=NULL,
                      $height=NULL, $cache=NULL) {

        $ret = '';

        list($ext,$mime) = mimetype($src);
        if(substr($mime,0,5) == 'image'){
            //add image tag
            $ret .= '<img class="media" src="'.
                    DOKU_BASE.'fetch.php?w='.$width.'&amp;h='.$height.
                    '&amp;cache='.$cache.'&amp;media='.urlencode($src).'"';
        
            if (!is_null($title))
                $ret .= ' title="'.$this->__xmlEntities($title).'"';
            
        
            if (!is_null($align))
                $ret .= ' align="'.$align.'"'; #FIXME use class!
          
            if ( !is_null($width) )
                $ret .= ' width="'.$this->__xmlEntities($width).'"';
        
            if ( !is_null($height) )
                $ret .= ' height="'.$this->__xmlEntities($height).'"';

            $ret .= ' />'; 

        }elseif($mime == 'application/x-shockwave-flash'){
            //FIXME default to a higher flash version?

            $ret .= '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"'.
                    ' codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=5,0,0,0"';
            if ( !is_null($width) ) $ret .= ' width="'.$this->__xmlEntities($width).'"';
            if ( !is_null($height) ) $ret .= ' height="'.$this->__xmlEntities($height).'"';
            $ret .= '>'.DOKU_LF;
            $ret .= '<param name="movie" value="'.DOKU_BASE.'fetch.php?media='.urlencode($src).'" />'.DOKU_LF;
            $ret .= '<param name="quality" value="high" />'.DOKU_LF;
            $ret .= '<embed src="'.DOKU_BASE.'fetch.php?media='.urlencode($src).'"'.
                    ' quality="high" bgcolor="#000000"';
            if ( !is_null($width) ) $ret .= ' width="'.$this->__xmlEntities($width).'"';
            if ( !is_null($height) ) $ret .= ' height="'.$this->__xmlEntities($height).'"';
            $ret .= ' type="application/x-shockwave-flash"'.
                    ' pluginspage="http://www.macromedia.com/shockwave/download/index.cgi'.
                    '?P1_Prod_Version=ShockwaveFlash"></embed>'.DOKU_LF;
            $ret .= '</object>'.DOKU_LF;

        }elseif(!is_null($title)){
            // well at least we have a title to display
            $ret .= $this->__xmlEntities($title);
        }else{
            // just show the source
            $ret .= $this->__xmlEntities($src);
        }

        return $ret;
    }
    
    // $numrows not yet implemented
    function table_open($maxcols = NULL, $numrows = NULL){
        echo '<table class="inline">'.DOKU_LF;
    }
    
    function table_close(){
        echo '</table>'.DOKU_LF.'<br />'.DOKU_LF;
    }
    
    function tablerow_open(){
        echo DOKU_TAB . '<tr>' . DOKU_LF . DOKU_TAB . DOKU_TAB;
    }
    
    function tablerow_close(){
        echo DOKU_LF . DOKU_TAB . '</tr>' . DOKU_LF;
    }
    
    function tableheader_open($colspan = 1, $align = NULL){
        echo '<th';
        if ( !is_null($align) ) {
            echo ' class="'.$align.'align"';
        }
        if ( $colspan > 1 ) {
            echo ' colspan="'.$colspan.'"';
        }
        echo '>';
    }
    
    function tableheader_close(){
        echo '</th>';
    }
    
    function tablecell_open($colspan = 1, $align = NULL){
        echo '<td';
        if ( !is_null($align) ) {
            echo ' class="'.$align.'align"';
        }
        if ( $colspan > 1 ) {
            echo ' colspan="'.$colspan.'"';
        }
        echo '>';
    }
    
    function tablecell_close(){
        echo '</td>';
    }
    
    //----------------------------------------------------------
    // Utils

    /**
     * Assembles all parts defined by the link formater below
     * Returns HTML for the link
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function __formatLink($link){
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
    function __simpleTitle($name){
        global $conf;
        if($conf['useslash']){
            $nssep = '[:;/]';
        }else{
            $nssep = '[:;]';
        }
        return preg_replace('!.*'.$nssep.'!','',$name);
    }

    
    function __newFootnoteId() {
        static $id = 1;
        return $id++;
    }
    
    function __xmlEntities($string) {
        return htmlspecialchars($string);
    }
    
    /**
    * @TODO Tuning needed - e.g. utf8 strtolower ? 
    */
    function __headerToLink($title) {
        return preg_replace('/\W/','_',trim($title));
    }

    /**
     * Adds code for section editing button
     */
    function __secedit($f, $t){
        print '<!-- SECTION ['.$f.'-'.$t.'] -->';
    }
    
    function __getLinkTitle($title, $default, & $isImage) {
        $isImage = FALSE;
        
        if ( is_null($title) ) {
            return $this->__xmlEntities($default);
            
        } else if ( is_string($title) ) {
            
            return $this->__xmlEntities($title);
            
        } else if ( is_array($title) ) {
            
            $isImage = TRUE;
            return $this->__imageTitle($title);
        
        }
    }
    
    /**
     * @TODO Resolve namespace on internal images
     */
    function __imageTitle($img) {

        //FIXME resolve internal links

        return $this->__media($img['src'],
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
            
            $imgStr = '<img class="media" src="http://wiki.splitbrain.org/media/wiki/'.$this->__xmlEntities($src).'"';
            
        } else {
            
            $imgStr = '<img class="media" src="'.$this->__xmlEntities($img['src']).'"';
            
        }
        
        if ( !is_null($img['title']) ) {
            $imgStr .= ' alt="'.$this->__xmlEntities($img['title']).'"';
        } else {
            $imgStr .= ' alt=""';
        }
        
        if ( !is_null($img['align']) ) {
            $imgStr .= ' align="'.$img['align'].'"';
        }
        
        if ( !is_null($img['width']) ) {
            $imgStr .= ' width="'.$this->__xmlEntities($img['width']).'"';
        }
        
        if ( !is_null($img['height']) ) {
            $imgStr .= ' height="'.$this->__xmlEntities($img['height']).'"';
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
