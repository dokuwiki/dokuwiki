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
require_once DOKU_INC . 'inc/html.php';

/**
 * The Renderer
 */
class Doku_Renderer_xhtml extends Doku_Renderer {

    // @access public
    var $doc = '';        // will contain the whole document
    var $toc = array();   // will contain the Table of Contents


    var $headers = array();
    var $footnotes = array();
    var $lastsec = 0;
    var $store = '';

    function getFormat(){
        return 'xhtml';
    }


    function document_start() {
        //reset some internals
        $this->toc     = array();
        $this->headers = array();
    }

    function document_end() {
        if ( count ($this->footnotes) > 0 ) {
            $this->doc .= '<div class="footnotes">'.DOKU_LF;

            $id = 0;
            foreach ( $this->footnotes as $footnote ) {
                $id++;   // the number of the current footnote

                // check its not a placeholder that indicates actual footnote text is elsewhere
                if (substr($footnote, 0, 5) != "@@FNT") {

                    // open the footnote and set the anchor and backlink
                    $this->doc .= '<div class="fn">';
                    $this->doc .= '<a href="#fnt__'.$id.'" id="fn__'.$id.'" name="fn__'.$id.'" class="fn_bot">';
                    $this->doc .= $id.')</a> '.DOKU_LF;

                    // get any other footnotes that use the same markup
                    $alt = array_keys($this->footnotes, "@@FNT$id");

                    if (count($alt)) {
                      foreach ($alt as $ref) {
                        // set anchor and backlink for the other footnotes
                        $this->doc .= ', <a href="#fnt__'.($ref+1).'" id="fn__'.($ref+1).'" name="fn__'.($ref+1).'" class="fn_bot">';
                        $this->doc .= ($ref+1).')</a> '.DOKU_LF;
                      }
                    }

                    // add footnote markup and close this footnote
                    $this->doc .= $footnote;
                    $this->doc .= '</div>' . DOKU_LF;
                }
            }
            $this->doc .= '</div>'.DOKU_LF;
        }

        // prepend the TOC
        if($this->info['toc']){
            $this->doc = $this->render_TOC($this->toc).$this->doc;
        }

        // make sure there are no empty paragraphs
        $this->doc = preg_replace('#<p>\s*</p>#','',$this->doc);
    }

    /**
     * Return the TOC rendered to XHTML
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function render_TOC($toc=null){
        if(is_null($toc) && is_array($this->toc)) $toc = $this->toc;

        if(count($toc) < 3) return '';
        global $lang;
        $out  = '<div class="toc">'.DOKU_LF;
        $out .= '<div class="tocheader toctoggle" id="toc__header">';
        $out .= $lang['toc'];
        $out .= '</div>'.DOKU_LF;
        $out .= '<div id="toc__inside">'.DOKU_LF;
        $out .= html_buildlist($toc,'toc',array(__CLASS__,'_tocitem'));
        $out .= '</div>'.DOKU_LF.'</div>'.DOKU_LF;
        return $out;
    }

    /**
     * Callback for html_buildlist
     */
    function _tocitem($item){
        return '<span class="li"><a href="#'.$item['hid'].'" class="toc">'.
               Doku_Renderer_xhtml::_xmlEntities($item['title']).'</a></span>';
    }

    function toc_additem($id, $text, $level) {
        global $conf;

        //handle TOC
        if($level >= $conf['toptoclevel'] && $level <= $conf['maxtoclevel']){
            // the TOC is one of our standard ul list arrays ;-)
            $this->toc[] = array( 'hid'   => $id,
                                  'title' => $text,
                                  'type'  => 'ul',
                                  'level' => $level-$conf['toptoclevel']+1);
        }
		}

    function header($text, $level, $pos) {

        $hid = $this->_headerToLink($text,true);

        //only add items within configured levels
        $this->toc_additem($hid, $text, $level);

        // write the header
        $this->doc .= DOKU_LF.'<h'.$level.'><a name="'.$hid.'" id="'.$hid.'">';
        $this->doc .= $this->_xmlEntities($text);
        $this->doc .= "</a></h$level>".DOKU_LF;
    }

     /**
     * Section edit marker is replaced by an edit button when
     * the page is editable. Replacement done in 'inc/html.php#html_secedit'
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Ben Coburn   <btcoburn@silicodon.net>
     */
    function section_edit($start, $end, $level, $name) {
        global $conf;

        if ($start!=-1 && $level<=$conf['maxseclevel']) {
            $name = str_replace('"', '', $name);
            $this->doc .= '<!-- SECTION "'.$name.'" ['.$start.'-'.(($end===0)?'':$end).'] -->';
        }
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
        $this->doc .= '<hr />'.DOKU_LF;
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
        $this->doc .= '<em class="u">';
    }

    function underline_close() {
        $this->doc .= '</em>';
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

        // move current content to store and record footnote
        $this->store = $this->doc;
        $this->doc   = '';
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

        // recover footnote into the stack and restore old content
        $footnote = $this->doc;
        $this->doc = $this->store;
        $this->store = '';

        // check to see if this footnote has been seen before
        $i = array_search($footnote, $this->footnotes);

        if ($i === false) {
            // its a new footnote, add it to the $footnotes array
            $id = count($this->footnotes)+1;
            $this->footnotes[count($this->footnotes)] = $footnote;
        } else {
            // seen this one before, translate the index to an id and save a placeholder
            $i++;
            $id = count($this->footnotes)+1;
            $this->footnotes[count($this->footnotes)] = "@@FNT".($i);
        }

        // output the footnote reference and link
        $this->doc .= '<a href="#fn__'.$id.'" name="fnt__'.$id.'" id="fnt__'.$id.'" class="fn_top">'.$id.')</a>';
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
        $this->doc .= '<div class="li">';
    }

    function listcontent_close() {
        $this->doc .= '</div>'.DOKU_LF;
    }

    function unformatted($text) {
        $this->doc .= $this->_xmlEntities($text);
    }

    /**
     * Execute PHP code if allowed
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function php($text) {
        global $conf;
        if($conf['phpok']){
            ob_start();
            eval($text);
            $this->doc .= ob_get_contents();
            ob_end_clean();
        }else{
            $this->file($text);
        }
    }

    /**
     * Insert HTML if allowed
     *
     * @author Andreas Gohr <andi@splitbrain.org>
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

    function quote_open() {
        $this->doc .= '<blockquote><div class="no">'.DOKU_LF;
    }

    function quote_close() {
        $this->doc .= '</div></blockquote>'.DOKU_LF;
    }

    /**
     * Callback for code text
     *
     * Uses GeSHi to highlight language syntax
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function code($text, $language = NULL) {
        global $conf;

        if ( is_null($language) ) {
            $this->preformatted($text);
        } else {
            //strip leading and trailing blank line
            $text = preg_replace('/^\s*?\n/','',$text);
            $text = preg_replace('/\s*?\n$/','',$text);
            $this->doc .= p_xhtml_cached_geshi($text, $language);
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

    function smiley($smiley) {
        if ( array_key_exists($smiley, $this->smileys) ) {
            $title = $this->_xmlEntities($this->smileys[$smiley]);
            $this->doc .= '<img src="'.DOKU_BASE.'lib/images/smileys/'.$this->smileys[$smiley].
                '" class="middle" alt="'.
                    $this->_xmlEntities($smiley).'" />';
        } else {
            $this->doc .= $this->_xmlEntities($smiley);
        }
    }

    /*
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
        global $lang;
        $this->doc .= $lang['singlequoteopening'];
    }

    function singlequoteclosing() {
        global $lang;
        $this->doc .= $lang['singlequoteclosing'];
    }

    function apostrophe() {
        global $lang;
        $this->doc .= $lang['apostrophe'];
    }

    function doublequoteopening() {
        global $lang;
        $this->doc .= $lang['doublequoteopening'];
    }

    function doublequoteclosing() {
        global $lang;
        $this->doc .= $lang['doublequoteclosing'];
    }

    /**
    */
    function camelcaselink($link) {
      $this->internallink($link,$link);
    }


    function locallink($hash, $name = NULL){
        global $ID;
        $name  = $this->_getLinkTitle($name, $hash, $isImage);
        $hash  = $this->_headerToLink($hash);
        $title = $ID.' &crarr;';
        $this->doc .= '<a href="#'.$hash.'" title="'.$title.'" class="wikilink1">';
        $this->doc .= $name;
        $this->doc .= '</a>';
    }

    /**
     * Render an internal Wiki Link
     *
     * $search and $returnonly are not for the renderer but are used
     * elsewhere - no need to implement them in other renderers
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function internallink($id, $name = NULL, $search=NULL,$returnonly=false) {
        global $conf;
        global $ID;
        // default name is based on $id as given
        $default = $this->_simpleTitle($id);

        // now first resolve and clean up the $id
        resolve_pageid(getNS($ID),$id,$exists);
        $name = $this->_getLinkTitle($name, $default, $isImage, $id);
        if ( !$isImage ) {
            if ( $exists ) {
                $class='wikilink1';
            } else {
                $class='wikilink2';
            }
        } else {
            $class='media';
        }

        //keep hash anchor
        list($id,$hash) = explode('#',$id,2);
        if(!empty($hash)) $hash = $this->_headerToLink($hash);

        //prepare for formating
        $link['target'] = $conf['target']['wiki'];
        $link['style']  = '';
        $link['pre']    = '';
        $link['suf']    = '';
        // highlight link to current page
        if ($id == $ID) {
            $link['pre']    = '<span class="curid">';
            $link['suf']    = '</span>';
        }
        $link['more']   = '';
        $link['class']  = $class;
        $link['url']    = wl($id);
        $link['name']   = $name;
        $link['title']  = $id;
        //add search string
        if($search){
            ($conf['userewrite']) ? $link['url'].='?s=' : $link['url'].='&amp;s=';
            $link['url'] .= rawurlencode($search);
        }

        //keep hash
        if($hash) $link['url'].='#'.$hash;

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
        $link['more']   = '';
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
        $link['more']   = '';
        $link['name']   = $this->_getLinkTitle($name, $wikiUri, $isImage);

        //get interwiki URL
        $url = $this-> _resolveInterWiki($wikiName,$wikiUri);

        if ( !$isImage ) {
            $class = preg_replace('/[^_\-a-z0-9]+/i','_',$wikiName);
            $link['class'] = "interwiki iw_$class";
        } else {
            $link['class'] = 'media';
        }

        //do we stay at the same server? Use local target
        if( strpos($url,DOKU_URL) === 0 ){
            $link['target'] = $conf['target']['wiki'];
        }

        $link['url'] = $url;
        $link['title'] = htmlspecialchars($link['url']);

        //output formatted
        $this->doc .= $this->_formatLink($link);
    }

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
                        str_replace('\\\\n','\\n',addslashes($lang['nosmblinks'])).
                        '\');}" onkeypress="if(document.all == null){alert(\''.
                        str_replace('\\\\n','\\n',addslashes($lang['nosmblinks'])).'\');}"';

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

        $name = $this->_getLinkTitle($name, '', $isImage);
        if ( !$isImage ) {
            $link['class']='mail JSnocheck';
        } else {
            $link['class']='media JSnocheck';
        }

        $address = $this->_xmlEntities($address);
        $address = obfuscate($address);
        $title   = $address;

        if(empty($name)){
            $name = $address;
        }
#elseif($isImage{
#            $name = $this->_xmlEntities($name);
#        }

        if($conf['mailguard'] == 'visible') $address = rawurlencode($address);

        $link['url']   = 'mailto:'.$address;
        $link['name']  = $name;
        $link['title'] = $title;

        //output formatted
        $this->doc .= $this->_formatLink($link);
    }

    function internalmedia ($src, $title=NULL, $align=NULL, $width=NULL,
                            $height=NULL, $cache=NULL, $linking=NULL) {
        global $conf;
        global $ID;
        resolve_mediaid(getNS($ID),$src, $exists);

        $link = array();
        $link['class']  = 'media';
        $link['style']  = '';
        $link['pre']    = '';
        $link['suf']    = '';
        $link['more']   = '';
        $link['target'] = $conf['target']['media'];
        $noLink = false;

        $link['title']  = $this->_xmlEntities($src);
        list($ext,$mime) = mimetype($src);
        if(substr($mime,0,5) == 'image'){
             $link['url'] = ml($src,array('id'=>$ID,'cache'=>$cache),($linking=='direct'));
         }elseif($mime == 'application/x-shockwave-flash'){
             // don't link flash movies
             $noLink = true;
         }else{
             // add file icons
             $class = preg_replace('/[^_\-a-z0-9]+/i','_',$ext);
             $link['class'] .= ' mediafile mf_'.$class;
             $link['url'] = ml($src,array('id'=>$ID,'cache'=>$cache),true);
         }
         $link['name']   = $this->_media ($src, $title, $align, $width, $height, $cache);

         //output formatted
         if ($linking == 'nolink' || $noLink) $this->doc .= $link['name'];
         else $this->doc .= $this->_formatLink($link);
    }

    /**
     * @todo don't add link for flash
     */
    function externalmedia ($src, $title=NULL, $align=NULL, $width=NULL,
                            $height=NULL, $cache=NULL, $linking=NULL) {
        global $conf;

        $link = array();
        $link['class']  = 'media';
        $link['style']  = '';
        $link['pre']    = '';
        $link['suf']    = '';
        $link['more']   = '';
        $link['target'] = $conf['target']['media'];

        $link['title']  = $this->_xmlEntities($src);
        $link['url']    = ml($src,array('cache'=>$cache));
        $link['name']   = $this->_media ($src, $title, $align, $width, $height, $cache);
        $noLink = false;

        list($ext,$mime) = mimetype($src);
        if(substr($mime,0,5) == 'image'){
             // link only jpeg images
             // if ($ext != 'jpg' && $ext != 'jpeg') $noLink = true;
        }elseif($mime == 'application/x-shockwave-flash'){
             // don't link flash movies
             $noLink = true;
        }else{
             // add file icons
             $link['class'] .= ' mediafile mf_'.$ext;
         }

        //output formatted
        if ($linking == 'nolink' || $noLink) $this->doc .= $link['name'];
        else $this->doc .= $this->_formatLink($link);
    }

    /**
     * Renders an RSS feed
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function rss ($url,$params){
        global $lang;
        global $conf;

        require_once(DOKU_INC.'inc/FeedParser.php');
        $feed = new FeedParser();
        $feed->feed_url($url);

        //disable warning while fetching
        if (!defined('DOKU_E_LEVEL')) { $elvl = error_reporting(E_ERROR); }
        $rc = $feed->init();
        if (!defined('DOKU_E_LEVEL')) { error_reporting($elvl); }

        //decide on start and end
        if($params['reverse']){
            $mod = -1;
            $start = $feed->get_item_quantity()-1;
            $end   = $start - ($params['max']);
            $end   = ($end < -1) ? -1 : $end;
        }else{
            $mod   = 1;
            $start = 0;
            $end   = $feed->get_item_quantity();
            $end   = ($end > $params['max']) ? $params['max'] : $end;;
        }

        $this->doc .= '<ul class="rss">';
        if($rc){
            for ($x = $start; $x != $end; $x += $mod) {
                $item = $feed->get_item($x);
                $this->doc .= '<li><div class="li">';
                $this->externallink($item->get_permalink(),
                                    $item->get_title());
                if($params['author']){
                    $author = $item->get_author(0);
                    if($author){
                        $name = $author->get_name();
                        if(!$name) $name = $author->get_email();
                        if($name) $this->doc .= ' '.$lang['by'].' '.$name;
                    }
                }
                if($params['date']){
                    $this->doc .= ' ('.$item->get_date($conf['dformat']).')';
                }
                if($params['details']){
                    $this->doc .= '<div class="detail">';
                    if($htmlok){
                        $this->doc .= $item->get_description();
                    }else{
                        $this->doc .= strip_tags($item->get_description());
                    }
                    $this->doc .= '</div>';
                }

                $this->doc .= '</div></li>';
            }
        }else{
            $this->doc .= '<li><div class="li">';
            $this->doc .= '<em>'.$lang['rssfailed'].'</em>';
            $this->externallink($url);
            if($conf['allowdebug']){
                $this->doc .= '<!--'.hsc($feed->error).'-->';
            }
            $this->doc .= '</div></li>';
        }
        $this->doc .= '</ul>';
    }

    // $numrows not yet implemented
    function table_open($maxcols = NULL, $numrows = NULL){
        $this->doc .= '<table class="inline">'.DOKU_LF;
    }

    function table_close(){
        $this->doc .= '</table>'.DOKU_LF;
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
     * Build a link
     *
     * Assembles all parts defined in $link returns HTML for the link
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

        // be sure there are no bad chars in url or title
        // (we can't do this for name because it can contain an img tag)
        $link['url']   = strtr($link['url'],array('>'=>'%3E','<'=>'%3C','"'=>'%22'));
        $link['title'] = strtr($link['title'],array('>'=>'&gt;','<'=>'&lt;','"'=>'&quot;'));

        $ret  = '';
        $ret .= $link['pre'];
        $ret .= '<a href="'.$link['url'].'"';
        if(!empty($link['class']))  $ret .= ' class="'.$link['class'].'"';
        if(!empty($link['target'])) $ret .= ' target="'.$link['target'].'"';
        if(!empty($link['title']))  $ret .= ' title="'.$link['title'].'"';
        if(!empty($link['style']))  $ret .= ' style="'.$link['style'].'"';
        if(!empty($link['more']))   $ret .= ' '.$link['more'];
        $ret .= '>';
        $ret .= $link['name'];
        $ret .= '</a>';
        $ret .= $link['suf'];
        return $ret;
    }

    /**
     * Renders internal and external media
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _media ($src, $title=NULL, $align=NULL, $width=NULL,
                      $height=NULL, $cache=NULL) {

        $ret = '';

        list($ext,$mime) = mimetype($src);
        if(substr($mime,0,5) == 'image'){
            //add image tag
            $ret .= '<img src="'.ml($src,array('w'=>$width,'h'=>$height,'cache'=>$cache)).'"';
            $ret .= ' class="media'.$align.'"';

            if (!is_null($title)) {
                $ret .= ' title="'.$this->_xmlEntities($title).'"';
                $ret .= ' alt="'.$this->_xmlEntities($title).'"';
            }elseif($ext == 'jpg' || $ext == 'jpeg'){
                //try to use the caption from IPTC/EXIF
                require_once(DOKU_INC.'inc/JpegMeta.php');
                $jpeg =& new JpegMeta(mediaFN($src));
                if($jpeg !== false) $cap = $jpeg->getTitle();
                if($cap){
                    $ret .= ' title="'.$this->_xmlEntities($cap).'"';
                    $ret .= ' alt="'.$this->_xmlEntities($cap).'"';
                }else{
                    $ret .= ' alt=""';
                }
            }else{
                $ret .= ' alt=""';
            }

            if ( !is_null($width) )
                $ret .= ' width="'.$this->_xmlEntities($width).'"';

            if ( !is_null($height) )
                $ret .= ' height="'.$this->_xmlEntities($height).'"';

            $ret .= ' />';

        }elseif($mime == 'application/x-shockwave-flash'){
            $ret .= '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"'.
                    ' codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0"';
            if ( !is_null($width) ) $ret .= ' width="'.$this->_xmlEntities($width).'"';
            if ( !is_null($height) ) $ret .= ' height="'.$this->_xmlEntities($height).'"';
            $ret .= '>'.DOKU_LF;
            $ret .= '<param name="movie" value="'.ml($src).'" />'.DOKU_LF;
            $ret .= '<param name="quality" value="high" />'.DOKU_LF;
            $ret .= '<embed src="'.ml($src).'"'.
                    ' quality="high"';
            if ( !is_null($width) ) $ret .= ' width="'.$this->_xmlEntities($width).'"';
            if ( !is_null($height) ) $ret .= ' height="'.$this->_xmlEntities($height).'"';
            $ret .= ' type="application/x-shockwave-flash"'.
                    ' pluginspage="http://www.macromedia.com/go/getflashplayer"></embed>'.DOKU_LF;
            $ret .= '</object>'.DOKU_LF;

        }elseif($title){
            // well at least we have a title to display
            $ret .= $this->_xmlEntities($title);
        }else{
            // just show the sourcename
            $ret .= $this->_xmlEntities(basename(noNS($src)));
        }

        return $ret;
    }

    function _xmlEntities($string) {
        return htmlspecialchars($string,ENT_QUOTES,'UTF-8');
    }

    /**
     * Creates a linkid from a headline
     *
     * @param string  $title   The headline title
     * @param boolean $create  Create a new unique ID?
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _headerToLink($title,$create=false) {
        $title = str_replace(':','',cleanID($title));
        $title = ltrim($title,'0123456789._-');
        if(empty($title)) $title='section';

        if($create){
            // make sure tiles are unique
            $num = '';
            while(in_array($title.$num,$this->headers)){
                ($num) ? $num++ : $num = 1;
            }
            $title = $title.$num;
            $this->headers[] = $title;
        }

        return $title;
    }

    /**
     * Construct a title and handle images in titles
     *
     * @author Harry Fuecks <hfuecks@gmail.com>
     */
    function _getLinkTitle($title, $default, & $isImage, $id=NULL) {
        global $conf;

        $isImage = false;
        if ( is_null($title) ) {
            if ($conf['useheading'] && $id) {
                $heading = p_get_first_heading($id,true);
                if ($heading) {
                    return $this->_xmlEntities($heading);
                }
            }
            return $this->_xmlEntities($default);
        } else if ( is_string($title) ) {
            return $this->_xmlEntities($title);
        } else if ( is_array($title) ) {
            $isImage = true;
            return $this->_imageTitle($title);
        }
    }

    /**
     * Returns an HTML code for images used in link titles
     *
     * @todo Resolve namespace on internal images
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _imageTitle($img) {
        return $this->_media($img['src'],
                              $img['title'],
                              $img['align'],
                              $img['width'],
                              $img['height'],
                              $img['cache']);
    }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
