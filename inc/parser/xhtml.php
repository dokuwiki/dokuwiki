<?php
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
* @TODO Probably useful for have constant for linefeed formatting
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

    function document_start() {
        ob_start();
    }
    
    function document_end() {
        
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
    
    function header($text, $level) {
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
    * @TODO Support optional eval of code depending on conf/dokuwiki.php
    */
    function php($text) {
        $this->preformatted($text);
    }
    
    /**
    * @TODO Support optional echo of HTML depending on conf/dokuwiki.php
    */
    function html($text) {
        $this->file($text);
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
    * @TODO Hook up correctly with Geshi
    */
    function code($text, $language = NULL) {
    
        if ( is_null($language) ) {
            $this->preformatted($text);
        } else {
        
            // Handle with Geshi here (needs tuning)
            require_once(DOKU_INC . 'inc/geshi.php');
            $geshi = new GeSHi($text, strtolower($language), DOKU_INC . 'inc/geshi');
            $geshi->enable_classes();
            $geshi->set_header_type(GESHI_HEADER_PRE);
            $geshi->set_overall_class('code');
            
            // Fix this
            $geshi->set_link_target('_blank');
            
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
    * @TODO Remove hard coded link to splitbrain.org
    */
    function smiley($smiley) {
        
        if ( array_key_exists($smiley, $this->smileys) ) {
            $title = $this->__xmlEntities($this->smileys[$smiley]);
            echo '<img src="http://wiki.splitbrain.org/smileys/'.$this->smileys[$smiley].
                '" align="middle" alt="'.
                    $this->__xmlEntities($smiley).'" />';
        } else {
            echo $this->__xmlEntities($smiley);
        }
    }
    
    /**
    * @TODO localization?
    */
    function wordblock($word) {
        if ( array_key_exists($word, $this->badwords) ) {
            echo '** BLEEP **';
        } else {
            echo $this->__xmlEntities($word);
        }
    }
    
    function entity($entity) {
        if ( array_key_exists($entity, $this->entities) ) {
            echo $this->entities[$entity];
        } else {
            echo $this->__xmlEntities($entity);
        }
    }
    
    function multiplyentity($x, $y) {
        echo "$x&#215;$y";
    }
    
    function singlequoteopening() {
        echo "&#8216;";
    }
    
    function singlequoteclosing() {
        echo "&#8217;";
    }
    
    function doublequoteopening() {
        echo "&#8220;";
    }
    
    function doublequoteclosing() {
        echo "&#8221;";
    }
    
    /**
    */
    function camelcaselink($link) {
    	$this->internallink($link,$link); 
    }
    
    /**
    * @TODO Support media
    * @TODO correct attributes
    */
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
    
    
    /**
    * @TODO Should list assume blacklist check already made?
    * @TODO External link icon
    * @TODO correct attributes
    */
    function externallink($link, $title = NULL) {
        
        echo '<a';
        
        $title = $this->__getLinkTitle($title, $link, $isImage);
        
        if ( !$isImage ) {
            echo ' class="urlextern"';
        } else {
            echo ' class="media"';
        }
        
        echo ' target="_blank" href="'.$this->__xmlEntities($link).'"';
        
        echo ' onclick="return svchk()" onkeypress="return svchk()">';
        
        echo $title;
        
        echo '</a>';
    }
    
    /**
    * @TODO Remove hard coded link to splitbrain.org on style
    */
    function interwikilink($link, $title = NULL, $wikiName, $wikiUri) {
        
        // RESOLVE THE URL
        if ( isset($this->interwiki[$wikiName]) ) {
            
            $wikiUriEnc = urlencode($wikiUri);
            
            if ( strstr($this->interwiki[$wikiName],'{URL}' ) !== FALSE ) {
                
                $url = str_replace('{URL}', $wikiUriEnc, $this->interwiki[$wikiName] );
                
            } else if ( strstr($this->interwiki[$wikiName],'{NAME}' ) !== FALSE ) {
                
                $url = str_replace('{NAME}', $wikiUriEnc, $this->interwiki[$wikiName] );
                
            } else {
                
                $url = $this->interwiki[$wikiName] . urlencode($wikiUri);
                
            }
        
        } else {
            // Default to Google I'm feeling lucky
            $url = 'http://www.google.com/search?q='.urlencode($wikiUri).'&amp;btnI=lucky';
        }
        
        // BUILD THE LINK
        echo '<a';
        
        $title = $this->__getLinkTitle($title, $wikiUri, $isImage);
        
        if ( !$isImage ) {
            echo ' class="interwiki"';
        } else {
            echo ' class="media"';
        }
        
        echo ' href="'.$this->__xmlEntities($url).'"';
        
        if ( FALSE !== ( $type = interwikiImgExists($wikiName) ) ) {
            echo ' style="background: transparent url(http://wiki.splitbrain.org/interwiki/'.
                $wikiName.'.'.$type.') 0px 1px no-repeat;"';
        }
        
        echo ' onclick="return svchk()" onkeypress="return svchk()">';
        
        echo $title;
        
        echo '</a>';
    }
    
    /**
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
    * @TODO Resolve namespaces
    * @TODO Add image caching
    * @TODO Remove hard coded link to splitbrain.org
    */
    function internalmedia (
        $src,$title=NULL,$align=NULL,$width=NULL,$height=NULL,$cache=NULL
        ) {
        
        // Sort out the namespace here...
        if ( strpos($src,':') ) {
            $src = explode(':',$src);
            $src = $src[1];
        }
        echo '<img class="media" src="http://wiki.splitbrain.org/media/wiki/'.$this->__xmlEntities($src).'"';
        
        if ( !is_null($title) ) {
            echo ' title="'.$this->__xmlEntities($title).'"';
        }
        
        if ( !is_null($align) ) {
            echo ' align="'.$align.'"';
        }
        
        if ( !is_null($width) ) {
            echo ' width="'.$this->__xmlEntities($width).'"';
        }
        
        if ( !is_null($height) ) {
            echo ' height="'.$this->__xmlEntities($height).'"';
        }
        
        echo '/>'; 
        
    }
    
    /**
    * @TODO Add image caching
    */
    function externalmedia (
        $src,$title=NULL,$align=NULL,$width=NULL,$height=NULL,$cache=NULL
        ) {
        
        echo '<img class="media" src="'.$this->__xmlEntities($src).'"';
        
        if ( !is_null($title) ) {
            echo ' title="'.$this->__xmlEntities($title).'"';
        }
        
        if ( !is_null($align) ) {
            echo ' align="'.$align.'"';
        }
        
        if ( !is_null($width) ) {
            echo ' width="'.$this->__xmlEntities($width).'"';
        }
        
        if ( !is_null($height) ) {
            echo ' height="'.$this->__xmlEntities($height).'"';
        }
        
        echo '/>'; 
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
    * @TODO Remove hard coded url to splitbrain.org
    * @TODO Image caching
    */
    function __imageTitle($img) {
        
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

