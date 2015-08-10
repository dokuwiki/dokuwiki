<?php
/**
 * Renderer for XHTML output
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 * @author Andreas Gohr <andi@splitbrain.org>
 */
if(!defined('DOKU_INC')) die('meh.');

if(!defined('DOKU_LF')) {
    // Some whitespace to help View > Source
    define ('DOKU_LF', "\n");
}

if(!defined('DOKU_TAB')) {
    // Some whitespace to help View > Source
    define ('DOKU_TAB', "\t");
}

/**
 * The XHTML Renderer
 *
 * This is DokuWiki's main renderer used to display page content in the wiki
 */
class Doku_Renderer_xhtml extends Doku_Renderer {
    /** @var array store the table of contents */
    public $toc = array();

    /** @var array A stack of section edit data */
    protected $sectionedits = array();
    var $date_at = '';    // link pages and media against this revision

    /** @var int last section edit id, used by startSectionEdit */
    protected $lastsecid = 0;

    /** @var array the list of headers used to create unique link ids */
    protected $headers = array();

    /** @var array a list of footnotes, list starts at 1! */
    protected $footnotes = array();

    /** @var int current section level */
    protected $lastlevel = 0;
    /** @var array section node tracker */
    protected $node = array(0, 0, 0, 0, 0);

    /** @var string temporary $doc store */
    protected $store = '';

    /** @var array global counter, for table classes etc. */
    protected $_counter = array(); //

    /** @var int counts the code and file blocks, used to provide download links */
    protected $_codeblock = 0;

    /** @var array list of allowed URL schemes */
    protected $schemes = null;

    /**
     * Register a new edit section range
     *
     * @param $type  string The section type identifier
     * @param $title string The section title
     * @param $start int    The byte position for the edit start
     * @return string A marker class for the starting HTML element
     * @author Adrian Lang <lang@cosmocode.de>
     */
    public function startSectionEdit($start, $type, $title = null) {
        $this->sectionedits[] = array(++$this->lastsecid, $start, $type, $title);
        return 'sectionedit'.$this->lastsecid;
    }

    /**
     * Finish an edit section range
     *
     * @param $end     int The byte position for the edit end; null for the rest of
     *                 the page
     * @author Adrian Lang <lang@cosmocode.de>
     */
    public function finishSectionEdit($end = null) {
        list($id, $start, $type, $title) = array_pop($this->sectionedits);
        if(!is_null($end) && $end <= $start) {
            return;
        }
        $this->doc .= "<!-- EDIT$id ".strtoupper($type).' ';
        if(!is_null($title)) {
            $this->doc .= '"'.str_replace('"', '', $title).'" ';
        }
        $this->doc .= "[$start-".(is_null($end) ? '' : $end).'] -->';
    }

    /**
     * Returns the format produced by this renderer.
     *
     * @return string always 'xhtml'
     */
    function getFormat() {
        return 'xhtml';
    }

    /**
     * Initialize the document
     */
    function document_start() {
        //reset some internals
        $this->toc     = array();
        $this->headers = array();
    }

    /**
     * Finalize the document
     */
    function document_end() {
        // Finish open section edits.
        while(count($this->sectionedits) > 0) {
            if($this->sectionedits[count($this->sectionedits) - 1][1] <= 1) {
                // If there is only one section, do not write a section edit
                // marker.
                array_pop($this->sectionedits);
            } else {
                $this->finishSectionEdit();
            }
        }

        if(count($this->footnotes) > 0) {
            $this->doc .= '<div class="footnotes">'.DOKU_LF;

            foreach($this->footnotes as $id => $footnote) {
                // check its not a placeholder that indicates actual footnote text is elsewhere
                if(substr($footnote, 0, 5) != "@@FNT") {

                    // open the footnote and set the anchor and backlink
                    $this->doc .= '<div class="fn">';
                    $this->doc .= '<sup><a href="#fnt__'.$id.'" id="fn__'.$id.'" class="fn_bot">';
                    $this->doc .= $id.')</a></sup> '.DOKU_LF;

                    // get any other footnotes that use the same markup
                    $alt = array_keys($this->footnotes, "@@FNT$id");

                    if(count($alt)) {
                        foreach($alt as $ref) {
                            // set anchor and backlink for the other footnotes
                            $this->doc .= ', <sup><a href="#fnt__'.($ref).'" id="fn__'.($ref).'" class="fn_bot">';
                            $this->doc .= ($ref).')</a></sup> '.DOKU_LF;
                        }
                    }

                    // add footnote markup and close this footnote
                    $this->doc .= $footnote;
                    $this->doc .= '</div>'.DOKU_LF;
                }
            }
            $this->doc .= '</div>'.DOKU_LF;
        }

        // Prepare the TOC
        global $conf;
        if($this->info['toc'] && is_array($this->toc) && $conf['tocminheads'] && count($this->toc) >= $conf['tocminheads']) {
            global $TOC;
            $TOC = $this->toc;
        }

        // make sure there are no empty paragraphs
        $this->doc = preg_replace('#<p>\s*</p>#', '', $this->doc);
    }

    /**
     * Add an item to the TOC
     *
     * @param string $id       the hash link
     * @param string $text     the text to display
     * @param int    $level    the nesting level
     */
    function toc_additem($id, $text, $level) {
        global $conf;

        //handle TOC
        if($level >= $conf['toptoclevel'] && $level <= $conf['maxtoclevel']) {
            $this->toc[] = html_mktocitem($id, $text, $level - $conf['toptoclevel'] + 1);
        }
    }

    /**
     * Render a heading
     *
     * @param string $text  the text to display
     * @param int    $level header level
     * @param int    $pos   byte position in the original source
     */
    function header($text, $level, $pos) {
        global $conf;

        if(!$text) return; //skip empty headlines

        $hid = $this->_headerToLink($text, true);

        //only add items within configured levels
        $this->toc_additem($hid, $text, $level);

        // adjust $node to reflect hierarchy of levels
        $this->node[$level - 1]++;
        if($level < $this->lastlevel) {
            for($i = 0; $i < $this->lastlevel - $level; $i++) {
                $this->node[$this->lastlevel - $i - 1] = 0;
            }
        }
        $this->lastlevel = $level;

        if($level <= $conf['maxseclevel'] &&
            count($this->sectionedits) > 0 &&
            $this->sectionedits[count($this->sectionedits) - 1][2] === 'section'
        ) {
            $this->finishSectionEdit($pos - 1);
        }

        // write the header
        $this->doc .= DOKU_LF.'<h'.$level;
        if($level <= $conf['maxseclevel']) {
            $this->doc .= ' class="'.$this->startSectionEdit($pos, 'section', $text).'"';
        }
        $this->doc .= ' id="'.$hid.'">';
        $this->doc .= $this->_xmlEntities($text);
        $this->doc .= "</h$level>".DOKU_LF;
    }

    /**
     * Open a new section
     *
     * @param int $level section level (as determined by the previous header)
     */
    function section_open($level) {
        $this->doc .= '<div class="level'.$level.'">'.DOKU_LF;
    }

    /**
     * Close the current section
     */
    function section_close() {
        $this->doc .= DOKU_LF.'</div>'.DOKU_LF;
    }

    /**
     * Render plain text data
     *
     * @param $text
     */
    function cdata($text) {
        $this->doc .= $this->_xmlEntities($text);
    }

    /**
     * Open a paragraph
     */
    function p_open() {
        $this->doc .= DOKU_LF.'<p>'.DOKU_LF;
    }

    /**
     * Close a paragraph
     */
    function p_close() {
        $this->doc .= DOKU_LF.'</p>'.DOKU_LF;
    }

    /**
     * Create a line break
     */
    function linebreak() {
        $this->doc .= '<br/>'.DOKU_LF;
    }

    /**
     * Create a horizontal line
     */
    function hr() {
        $this->doc .= '<hr />'.DOKU_LF;
    }

    /**
     * Start strong (bold) formatting
     */
    function strong_open() {
        $this->doc .= '<strong>';
    }

    /**
     * Stop strong (bold) formatting
     */
    function strong_close() {
        $this->doc .= '</strong>';
    }

    /**
     * Start emphasis (italics) formatting
     */
    function emphasis_open() {
        $this->doc .= '<em>';
    }

    /**
     * Stop emphasis (italics) formatting
     */
    function emphasis_close() {
        $this->doc .= '</em>';
    }

    /**
     * Start underline formatting
     */
    function underline_open() {
        $this->doc .= '<em class="u">';
    }

    /**
     * Stop underline formatting
     */
    function underline_close() {
        $this->doc .= '</em>';
    }

    /**
     * Start monospace formatting
     */
    function monospace_open() {
        $this->doc .= '<code>';
    }

    /**
     * Stop monospace formatting
     */
    function monospace_close() {
        $this->doc .= '</code>';
    }

    /**
     * Start a subscript
     */
    function subscript_open() {
        $this->doc .= '<sub>';
    }

    /**
     * Stop a subscript
     */
    function subscript_close() {
        $this->doc .= '</sub>';
    }

    /**
     * Start a superscript
     */
    function superscript_open() {
        $this->doc .= '<sup>';
    }

    /**
     * Stop a superscript
     */
    function superscript_close() {
        $this->doc .= '</sup>';
    }

    /**
     * Start deleted (strike-through) formatting
     */
    function deleted_open() {
        $this->doc .= '<del>';
    }

    /**
     * Stop deleted (strike-through) formatting
     */
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
        /** @var $fnid int takes track of seen footnotes, assures they are unique even across multiple docs FS#2841 */
        static $fnid = 0;
        // assign new footnote id (we start at 1)
        $fnid++;

        // recover footnote into the stack and restore old content
        $footnote    = $this->doc;
        $this->doc   = $this->store;
        $this->store = '';

        // check to see if this footnote has been seen before
        $i = array_search($footnote, $this->footnotes);

        if($i === false) {
            // its a new footnote, add it to the $footnotes array
            $this->footnotes[$fnid] = $footnote;
        } else {
            // seen this one before, save a placeholder
            $this->footnotes[$fnid] = "@@FNT".($i);
        }

        // output the footnote reference and link
        $this->doc .= '<sup><a href="#fn__'.$fnid.'" id="fnt__'.$fnid.'" class="fn_top">'.$fnid.')</a></sup>';
    }

    /**
     * Open an unordered list
     */
    function listu_open() {
        $this->doc .= '<ul>'.DOKU_LF;
    }

    /**
     * Close an unordered list
     */
    function listu_close() {
        $this->doc .= '</ul>'.DOKU_LF;
    }

    /**
     * Open an ordered list
     */
    function listo_open() {
        $this->doc .= '<ol>'.DOKU_LF;
    }

    /**
     * Close an ordered list
     */
    function listo_close() {
        $this->doc .= '</ol>'.DOKU_LF;
    }

    /**
     * Open a list item
     *
     * @param int $level the nesting level
     */
    function listitem_open($level) {
        $this->doc .= '<li class="level'.$level.'">';
    }

    /**
     * Close a list item
     */
    function listitem_close() {
        $this->doc .= '</li>'.DOKU_LF;
    }

    /**
     * Start the content of a list item
     */
    function listcontent_open() {
        $this->doc .= '<div class="li">';
    }

    /**
     * Stop the content of a list item
     */
    function listcontent_close() {
        $this->doc .= '</div>'.DOKU_LF;
    }

    /**
     * Output unformatted $text
     *
     * Defaults to $this->cdata()
     *
     * @param string $text
     */
    function unformatted($text) {
        $this->doc .= $this->_xmlEntities($text);
    }

    /**
     * Execute PHP code if allowed
     *
     * @param  string $text      PHP code that is either executed or printed
     * @param  string $wrapper   html element to wrap result if $conf['phpok'] is okff
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function php($text, $wrapper = 'code') {
        global $conf;

        if($conf['phpok']) {
            ob_start();
            eval($text);
            $this->doc .= ob_get_contents();
            ob_end_clean();
        } else {
            $this->doc .= p_xhtml_cached_geshi($text, 'php', $wrapper);
        }
    }

    /**
     * Output block level PHP code
     *
     * If $conf['phpok'] is true this should evaluate the given code and append the result
     * to $doc
     *
     * @param string $text The PHP code
     */
    function phpblock($text) {
        $this->php($text, 'pre');
    }

    /**
     * Insert HTML if allowed
     *
     * @param  string $text      html text
     * @param  string $wrapper   html element to wrap result if $conf['htmlok'] is okff
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function html($text, $wrapper = 'code') {
        global $conf;

        if($conf['htmlok']) {
            $this->doc .= $text;
        } else {
            $this->doc .= p_xhtml_cached_geshi($text, 'html4strict', $wrapper);
        }
    }

    /**
     * Output raw block-level HTML
     *
     * If $conf['htmlok'] is true this should add the code as is to $doc
     *
     * @param string $text The HTML
     */
    function htmlblock($text) {
        $this->html($text, 'pre');
    }

    /**
     * Start a block quote
     */
    function quote_open() {
        $this->doc .= '<blockquote><div class="no">'.DOKU_LF;
    }

    /**
     * Stop a block quote
     */
    function quote_close() {
        $this->doc .= '</div></blockquote>'.DOKU_LF;
    }

    /**
     * Output preformatted text
     *
     * @param string $text
     */
    function preformatted($text) {
        $this->doc .= '<pre class="code">'.trim($this->_xmlEntities($text), "\n\r").'</pre>'.DOKU_LF;
    }

    /**
     * Display text as file content, optionally syntax highlighted
     *
     * @param string $text     text to show
     * @param string $language programming language to use for syntax highlighting
     * @param string $filename file path label
     */
    function file($text, $language = null, $filename = null) {
        $this->_highlight('file', $text, $language, $filename);
    }

    /**
     * Display text as code content, optionally syntax highlighted
     *
     * @param string $text     text to show
     * @param string $language programming language to use for syntax highlighting
     * @param string $filename file path label
     */
    function code($text, $language = null, $filename = null) {
        $this->_highlight('code', $text, $language, $filename);
    }

    /**
     * Use GeSHi to highlight language syntax in code and file blocks
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @param string $type     code|file
     * @param string $text     text to show
     * @param string $language programming language to use for syntax highlighting
     * @param string $filename file path label
     */
    function _highlight($type, $text, $language = null, $filename = null) {
        global $ID;
        global $lang;

        if($filename) {
            // add icon
            list($ext) = mimetype($filename, false);
            $class = preg_replace('/[^_\-a-z0-9]+/i', '_', $ext);
            $class = 'mediafile mf_'.$class;

            $this->doc .= '<dl class="'.$type.'">'.DOKU_LF;
            $this->doc .= '<dt><a href="'.exportlink($ID, 'code', array('codeblock' => $this->_codeblock)).'" title="'.$lang['download'].'" class="'.$class.'">';
            $this->doc .= hsc($filename);
            $this->doc .= '</a></dt>'.DOKU_LF.'<dd>';
        }

        if($text{0} == "\n") {
            $text = substr($text, 1);
        }
        if(substr($text, -1) == "\n") {
            $text = substr($text, 0, -1);
        }

        if(is_null($language)) {
            $this->doc .= '<pre class="'.$type.'">'.$this->_xmlEntities($text).'</pre>'.DOKU_LF;
        } else {
            $class = 'code'; //we always need the code class to make the syntax highlighting apply
            if($type != 'code') $class .= ' '.$type;

            $this->doc .= "<pre class=\"$class $language\">".p_xhtml_cached_geshi($text, $language, '').'</pre>'.DOKU_LF;
        }

        if($filename) {
            $this->doc .= '</dd></dl>'.DOKU_LF;
        }

        $this->_codeblock++;
    }

    /**
     * Format an acronym
     *
     * Uses $this->acronyms
     *
     * @param string $acronym
     */
    function acronym($acronym) {

        if(array_key_exists($acronym, $this->acronyms)) {

            $title = $this->_xmlEntities($this->acronyms[$acronym]);

            $this->doc .= '<abbr title="'.$title
                .'">'.$this->_xmlEntities($acronym).'</abbr>';

        } else {
            $this->doc .= $this->_xmlEntities($acronym);
        }
    }

    /**
     * Format a smiley
     *
     * Uses $this->smiley
     *
     * @param string $smiley
     */
    function smiley($smiley) {
        if(array_key_exists($smiley, $this->smileys)) {
            $this->doc .= '<img src="'.DOKU_BASE.'lib/images/smileys/'.$this->smileys[$smiley].
                '" class="icon" alt="'.
                $this->_xmlEntities($smiley).'" />';
        } else {
            $this->doc .= $this->_xmlEntities($smiley);
        }
    }

    /**
     * Format an entity
     *
     * Entities are basically small text replacements
     *
     * Uses $this->entities
     *
     * @param string $entity
     */
    function entity($entity) {
        if(array_key_exists($entity, $this->entities)) {
            $this->doc .= $this->entities[$entity];
        } else {
            $this->doc .= $this->_xmlEntities($entity);
        }
    }

    /**
     * Typographically format a multiply sign
     *
     * Example: ($x=640, $y=480) should result in "640×480"
     *
     * @param string|int $x first value
     * @param string|int $y second value
     */
    function multiplyentity($x, $y) {
        $this->doc .= "$x&times;$y";
    }

    /**
     * Render an opening single quote char (language specific)
     */
    function singlequoteopening() {
        global $lang;
        $this->doc .= $lang['singlequoteopening'];
    }

    /**
     * Render a closing single quote char (language specific)
     */
    function singlequoteclosing() {
        global $lang;
        $this->doc .= $lang['singlequoteclosing'];
    }

    /**
     * Render an apostrophe char (language specific)
     */
    function apostrophe() {
        global $lang;
        $this->doc .= $lang['apostrophe'];
    }

    /**
     * Render an opening double quote char (language specific)
     */
    function doublequoteopening() {
        global $lang;
        $this->doc .= $lang['doublequoteopening'];
    }

    /**
     * Render an closinging double quote char (language specific)
     */
    function doublequoteclosing() {
        global $lang;
        $this->doc .= $lang['doublequoteclosing'];
    }

    /**
     * Render a CamelCase link
     *
     * @param string $link The link name
     * @see http://en.wikipedia.org/wiki/CamelCase
     */
    function camelcaselink($link) {
        $this->internallink($link, $link);
    }

    /**
     * Render a page local link
     *
     * @param string $hash hash link identifier
     * @param string $name name for the link
     */
    function locallink($hash, $name = null) {
        global $ID;
        $name  = $this->_getLinkTitle($name, $hash, $isImage);
        $hash  = $this->_headerToLink($hash);
        $title = $ID.' ↵';
        $this->doc .= '<a href="#'.$hash.'" title="'.$title.'" class="wikilink1">';
        $this->doc .= $name;
        $this->doc .= '</a>';
    }

    /**
     * Render an internal Wiki Link
     *
     * $search,$returnonly & $linktype are not for the renderer but are used
     * elsewhere - no need to implement them in other renderers
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @param string      $id         pageid
     * @param string|null $name       link name
     * @param string|null $search     adds search url param
     * @param bool        $returnonly whether to return html or write to doc attribute
     * @param string      $linktype   type to set use of headings
     * @return void|string writes to doc attribute or returns html depends on $returnonly
     */
    function internallink($id, $name = null, $search = null, $returnonly = false, $linktype = 'content') {
        global $conf;
        global $ID;
        global $INFO;

        $params = '';
        $parts  = explode('?', $id, 2);
        if(count($parts) === 2) {
            $id     = $parts[0];
            $params = $parts[1];
        }

        // For empty $id we need to know the current $ID
        // We need this check because _simpleTitle needs
        // correct $id and resolve_pageid() use cleanID($id)
        // (some things could be lost)
        if($id === '') {
            $id = $ID;
        }

        // default name is based on $id as given
        $default = $this->_simpleTitle($id);

        // now first resolve and clean up the $id
        resolve_pageid(getNS($ID), $id, $exists, $this->date_at, true);

        $name = $this->_getLinkTitle($name, $default, $isImage, $id, $linktype);
        if(!$isImage) {
            if($exists) {
                $class = 'wikilink1';
            } else {
                $class       = 'wikilink2';
                $link['rel'] = 'nofollow';
            }
        } else {
            $class = 'media';
        }

        //keep hash anchor
        @list($id, $hash) = explode('#', $id, 2);
        if(!empty($hash)) $hash = $this->_headerToLink($hash);

        //prepare for formating
        $link['target'] = $conf['target']['wiki'];
        $link['style']  = '';
        $link['pre']    = '';
        $link['suf']    = '';
        // highlight link to current page
        if($id == $INFO['id']) {
            $link['pre'] = '<span class="curid">';
            $link['suf'] = '</span>';
        }
        $link['more']   = '';
        $link['class']  = $class;
        if($this->date_at) {
            $params['at'] = $this->date_at;
        }
        $link['url']    = wl($id, $params);
        $link['name']   = $name;
        $link['title']  = $id;
        //add search string
        if($search) {
            ($conf['userewrite']) ? $link['url'] .= '?' : $link['url'] .= '&amp;';
            if(is_array($search)) {
                $search = array_map('rawurlencode', $search);
                $link['url'] .= 's[]='.join('&amp;s[]=', $search);
            } else {
                $link['url'] .= 's='.rawurlencode($search);
            }
        }

        //keep hash
        if($hash) $link['url'] .= '#'.$hash;

        //output formatted
        if($returnonly) {
            return $this->_formatLink($link);
        } else {
            $this->doc .= $this->_formatLink($link);
        }
    }

    /**
     * Render an external link
     *
     * @param string       $url  full URL with scheme
     * @param string|array $name name for the link, array for media file
     */
    function externallink($url, $name = null) {
        global $conf;

        $name = $this->_getLinkTitle($name, $url, $isImage);

        // url might be an attack vector, only allow registered protocols
        if(is_null($this->schemes)) $this->schemes = getSchemes();
        list($scheme) = explode('://', $url);
        $scheme = strtolower($scheme);
        if(!in_array($scheme, $this->schemes)) $url = '';

        // is there still an URL?
        if(!$url) {
            $this->doc .= $name;
            return;
        }

        // set class
        if(!$isImage) {
            $class = 'urlextern';
        } else {
            $class = 'media';
        }

        //prepare for formating
        $link['target'] = $conf['target']['extern'];
        $link['style']  = '';
        $link['pre']    = '';
        $link['suf']    = '';
        $link['more']   = '';
        $link['class']  = $class;
        $link['url']    = $url;

        $link['name']  = $name;
        $link['title'] = $this->_xmlEntities($url);
        if($conf['relnofollow']) $link['more'] .= ' rel="nofollow"';

        //output formatted
        $this->doc .= $this->_formatLink($link);
    }

    /**
     * Render an interwiki link
     *
     * You may want to use $this->_resolveInterWiki() here
     *
     * @param string       $match     original link - probably not much use
     * @param string|array $name      name for the link, array for media file
     * @param string       $wikiName  indentifier (shortcut) for the remote wiki
     * @param string       $wikiUri   the fragment parsed from the original link
     */
    function interwikilink($match, $name = null, $wikiName, $wikiUri) {
        global $conf;

        $link           = array();
        $link['target'] = $conf['target']['interwiki'];
        $link['pre']    = '';
        $link['suf']    = '';
        $link['more']   = '';
        $link['name']   = $this->_getLinkTitle($name, $wikiUri, $isImage);

        //get interwiki URL
        $exists = null;
        $url    = $this->_resolveInterWiki($wikiName, $wikiUri, $exists);

        if(!$isImage) {
            $class         = preg_replace('/[^_\-a-z0-9]+/i', '_', $wikiName);
            $link['class'] = "interwiki iw_$class";
        } else {
            $link['class'] = 'media';
        }

        //do we stay at the same server? Use local target
        if(strpos($url, DOKU_URL) === 0 OR strpos($url, DOKU_BASE) === 0) {
            $link['target'] = $conf['target']['wiki'];
        }
        if($exists !== null && !$isImage) {
            if($exists) {
                $link['class'] .= ' wikilink1';
            } else {
                $link['class'] .= ' wikilink2';
                $link['rel'] = 'nofollow';
            }
        }

        $link['url']   = $url;
        $link['title'] = htmlspecialchars($link['url']);

        //output formatted
        $this->doc .= $this->_formatLink($link);
    }

    /**
     * Link to windows share
     *
     * @param string       $url  the link
     * @param string|array $name name for the link, array for media file
     */
    function windowssharelink($url, $name = null) {
        global $conf;

        //simple setup
        $link['target'] = $conf['target']['windows'];
        $link['pre']    = '';
        $link['suf']    = '';
        $link['style']  = '';

        $link['name'] = $this->_getLinkTitle($name, $url, $isImage);
        if(!$isImage) {
            $link['class'] = 'windows';
        } else {
            $link['class'] = 'media';
        }

        $link['title'] = $this->_xmlEntities($url);
        $url           = str_replace('\\', '/', $url);
        $url           = 'file:///'.$url;
        $link['url']   = $url;

        //output formatted
        $this->doc .= $this->_formatLink($link);
    }

    /**
     * Render a linked E-Mail Address
     *
     * Honors $conf['mailguard'] setting
     *
     * @param string       $address Email-Address
     * @param string|array $name    name for the link, array for media file
     */
    function emaillink($address, $name = null) {
        global $conf;
        //simple setup
        $link           = array();
        $link['target'] = '';
        $link['pre']    = '';
        $link['suf']    = '';
        $link['style']  = '';
        $link['more']   = '';

        $name = $this->_getLinkTitle($name, '', $isImage);
        if(!$isImage) {
            $link['class'] = 'mail';
        } else {
            $link['class'] = 'media';
        }

        $address = $this->_xmlEntities($address);
        $address = obfuscate($address);
        $title   = $address;

        if(empty($name)) {
            $name = $address;
        }

        if($conf['mailguard'] == 'visible') $address = rawurlencode($address);

        $link['url']   = 'mailto:'.$address;
        $link['name']  = $name;
        $link['title'] = $title;

        //output formatted
        $this->doc .= $this->_formatLink($link);
    }

    /**
     * Render an internal media file
     *
     * @param string $src       media ID
     * @param string $title     descriptive text
     * @param string $align     left|center|right
     * @param int    $width     width of media in pixel
     * @param int    $height    height of media in pixel
     * @param string $cache     cache|recache|nocache
     * @param string $linking   linkonly|detail|nolink
     * @param bool   $return    return HTML instead of adding to $doc
     * @return void|string
     */
    function internalmedia($src, $title = null, $align = null, $width = null,
                           $height = null, $cache = null, $linking = null, $return = false) {
        global $ID;
        list($src, $hash) = explode('#', $src, 2);
        resolve_mediaid(getNS($ID), $src, $exists, $this->date_at, true);

        $noLink = false;
        $render = ($linking == 'linkonly') ? false : true;
        $link   = $this->_getMediaLinkConf($src, $title, $align, $width, $height, $cache, $render);

        list($ext, $mime) = mimetype($src, false);
        if(substr($mime, 0, 5) == 'image' && $render) {
            $link['url'] = ml($src, array('id' => $ID, 'cache' => $cache, 'rev'=>$this->_getLastMediaRevisionAt($src)), ($linking == 'direct'));
        } elseif(($mime == 'application/x-shockwave-flash' || media_supportedav($mime)) && $render) {
            // don't link movies
            $noLink = true;
        } else {
            // add file icons
            $class = preg_replace('/[^_\-a-z0-9]+/i', '_', $ext);
            $link['class'] .= ' mediafile mf_'.$class;
            $link['url'] = ml($src, array('id' => $ID, 'cache' => $cache , 'rev'=>$this->_getLastMediaRevisionAt($src)), true);
            if($exists) $link['title'] .= ' ('.filesize_h(filesize(mediaFN($src))).')';
        }

        if($hash) $link['url'] .= '#'.$hash;

        //markup non existing files
        if(!$exists) {
            $link['class'] .= ' wikilink2';
        }

        //output formatted
        if($return) {
            if($linking == 'nolink' || $noLink) return $link['name'];
            else return $this->_formatLink($link);
        } else {
            if($linking == 'nolink' || $noLink) $this->doc .= $link['name'];
            else $this->doc .= $this->_formatLink($link);
        }
    }

    /**
     * Render an external media file
     *
     * @param string $src     full media URL
     * @param string $title   descriptive text
     * @param string $align   left|center|right
     * @param int    $width   width of media in pixel
     * @param int    $height  height of media in pixel
     * @param string $cache   cache|recache|nocache
     * @param string $linking linkonly|detail|nolink
     * @param bool   $return  return HTML instead of adding to $doc
     */
    function externalmedia($src, $title = null, $align = null, $width = null,
                           $height = null, $cache = null, $linking = null, $return = false) {
        list($src, $hash) = explode('#', $src, 2);
        $noLink = false;
        $render = ($linking == 'linkonly') ? false : true;
        $link   = $this->_getMediaLinkConf($src, $title, $align, $width, $height, $cache, $render);

        $link['url'] = ml($src, array('cache' => $cache));

        list($ext, $mime) = mimetype($src, false);
        if(substr($mime, 0, 5) == 'image' && $render) {
            // link only jpeg images
            // if ($ext != 'jpg' && $ext != 'jpeg') $noLink = true;
        } elseif(($mime == 'application/x-shockwave-flash' || media_supportedav($mime)) && $render) {
            // don't link movies
            $noLink = true;
        } else {
            // add file icons
            $class = preg_replace('/[^_\-a-z0-9]+/i', '_', $ext);
            $link['class'] .= ' mediafile mf_'.$class;
        }

        if($hash) $link['url'] .= '#'.$hash;

        //output formatted
        if($return) {
            if($linking == 'nolink' || $noLink) return $link['name'];
            else return $this->_formatLink($link);
        } else {
            if($linking == 'nolink' || $noLink) $this->doc .= $link['name'];
            else $this->doc .= $this->_formatLink($link);
        }
    }

    /**
     * Renders an RSS feed
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function rss($url, $params) {
        global $lang;
        global $conf;

        require_once(DOKU_INC.'inc/FeedParser.php');
        $feed = new FeedParser();
        $feed->set_feed_url($url);

        //disable warning while fetching
        if(!defined('DOKU_E_LEVEL')) {
            $elvl = error_reporting(E_ERROR);
        }
        $rc = $feed->init();
        if(isset($elvl)) {
            error_reporting($elvl);
        }

        //decide on start and end
        if($params['reverse']) {
            $mod   = -1;
            $start = $feed->get_item_quantity() - 1;
            $end   = $start - ($params['max']);
            $end   = ($end < -1) ? -1 : $end;
        } else {
            $mod   = 1;
            $start = 0;
            $end   = $feed->get_item_quantity();
            $end   = ($end > $params['max']) ? $params['max'] : $end;
        }

        $this->doc .= '<ul class="rss">';
        if($rc) {
            for($x = $start; $x != $end; $x += $mod) {
                $item = $feed->get_item($x);
                $this->doc .= '<li><div class="li">';
                // support feeds without links
                $lnkurl = $item->get_permalink();
                if($lnkurl) {
                    // title is escaped by SimplePie, we unescape here because it
                    // is escaped again in externallink() FS#1705
                    $this->externallink(
                        $item->get_permalink(),
                        html_entity_decode($item->get_title(), ENT_QUOTES, 'UTF-8')
                    );
                } else {
                    $this->doc .= ' '.$item->get_title();
                }
                if($params['author']) {
                    $author = $item->get_author(0);
                    if($author) {
                        $name = $author->get_name();
                        if(!$name) $name = $author->get_email();
                        if($name) $this->doc .= ' '.$lang['by'].' '.$name;
                    }
                }
                if($params['date']) {
                    $this->doc .= ' ('.$item->get_local_date($conf['dformat']).')';
                }
                if($params['details']) {
                    $this->doc .= '<div class="detail">';
                    if($conf['htmlok']) {
                        $this->doc .= $item->get_description();
                    } else {
                        $this->doc .= strip_tags($item->get_description());
                    }
                    $this->doc .= '</div>';
                }

                $this->doc .= '</div></li>';
            }
        } else {
            $this->doc .= '<li><div class="li">';
            $this->doc .= '<em>'.$lang['rssfailed'].'</em>';
            $this->externallink($url);
            if($conf['allowdebug']) {
                $this->doc .= '<!--'.hsc($feed->error).'-->';
            }
            $this->doc .= '</div></li>';
        }
        $this->doc .= '</ul>';
    }

    /**
     * Start a table
     *
     * @param int $maxcols maximum number of columns
     * @param int $numrows NOT IMPLEMENTED
     * @param int $pos     byte position in the original source
     */
    function table_open($maxcols = null, $numrows = null, $pos = null) {
        // initialize the row counter used for classes
        $this->_counter['row_counter'] = 0;
        $class                         = 'table';
        if($pos !== null) {
            $class .= ' '.$this->startSectionEdit($pos, 'table');
        }
        $this->doc .= '<div class="'.$class.'"><table class="inline">'.
            DOKU_LF;
    }

    /**
     * Close a table
     *
     * @param int $pos byte position in the original source
     */
    function table_close($pos = null) {
        $this->doc .= '</table></div>'.DOKU_LF;
        if($pos !== null) {
            $this->finishSectionEdit($pos);
        }
    }

    /**
     * Open a table header
     */
    function tablethead_open() {
        $this->doc .= DOKU_TAB.'<thead>'.DOKU_LF;
    }

    /**
     * Close a table header
     */
    function tablethead_close() {
        $this->doc .= DOKU_TAB.'</thead>'.DOKU_LF;
    }

    /**
     * Open a table row
     */
    function tablerow_open() {
        // initialize the cell counter used for classes
        $this->_counter['cell_counter'] = 0;
        $class                          = 'row'.$this->_counter['row_counter']++;
        $this->doc .= DOKU_TAB.'<tr class="'.$class.'">'.DOKU_LF.DOKU_TAB.DOKU_TAB;
    }

    /**
     * Close a table row
     */
    function tablerow_close() {
        $this->doc .= DOKU_LF.DOKU_TAB.'</tr>'.DOKU_LF;
    }

    /**
     * Open a table header cell
     *
     * @param int    $colspan
     * @param string $align left|center|right
     * @param int    $rowspan
     */
    function tableheader_open($colspan = 1, $align = null, $rowspan = 1) {
        $class = 'class="col'.$this->_counter['cell_counter']++;
        if(!is_null($align)) {
            $class .= ' '.$align.'align';
        }
        $class .= '"';
        $this->doc .= '<th '.$class;
        if($colspan > 1) {
            $this->_counter['cell_counter'] += $colspan - 1;
            $this->doc .= ' colspan="'.$colspan.'"';
        }
        if($rowspan > 1) {
            $this->doc .= ' rowspan="'.$rowspan.'"';
        }
        $this->doc .= '>';
    }

    /**
     * Close a table header cell
     */
    function tableheader_close() {
        $this->doc .= '</th>';
    }

    /**
     * Open a table cell
     *
     * @param int    $colspan
     * @param string $align left|center|right
     * @param int    $rowspan
     */
    function tablecell_open($colspan = 1, $align = null, $rowspan = 1) {
        $class = 'class="col'.$this->_counter['cell_counter']++;
        if(!is_null($align)) {
            $class .= ' '.$align.'align';
        }
        $class .= '"';
        $this->doc .= '<td '.$class;
        if($colspan > 1) {
            $this->_counter['cell_counter'] += $colspan - 1;
            $this->doc .= ' colspan="'.$colspan.'"';
        }
        if($rowspan > 1) {
            $this->doc .= ' rowspan="'.$rowspan.'"';
        }
        $this->doc .= '>';
    }

    /**
     * Close a table cell
     */
    function tablecell_close() {
        $this->doc .= '</td>';
    }

    #region Utility functions

    /**
     * Build a link
     *
     * Assembles all parts defined in $link returns HTML for the link
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _formatLink($link) {
        //make sure the url is XHTML compliant (skip mailto)
        if(substr($link['url'], 0, 7) != 'mailto:') {
            $link['url'] = str_replace('&', '&amp;', $link['url']);
            $link['url'] = str_replace('&amp;amp;', '&amp;', $link['url']);
        }
        //remove double encodings in titles
        $link['title'] = str_replace('&amp;amp;', '&amp;', $link['title']);

        // be sure there are no bad chars in url or title
        // (we can't do this for name because it can contain an img tag)
        $link['url']   = strtr($link['url'], array('>' => '%3E', '<' => '%3C', '"' => '%22'));
        $link['title'] = strtr($link['title'], array('>' => '&gt;', '<' => '&lt;', '"' => '&quot;'));

        $ret = '';
        $ret .= $link['pre'];
        $ret .= '<a href="'.$link['url'].'"';
        if(!empty($link['class'])) $ret .= ' class="'.$link['class'].'"';
        if(!empty($link['target'])) $ret .= ' target="'.$link['target'].'"';
        if(!empty($link['title'])) $ret .= ' title="'.$link['title'].'"';
        if(!empty($link['style'])) $ret .= ' style="'.$link['style'].'"';
        if(!empty($link['rel'])) $ret .= ' rel="'.$link['rel'].'"';
        if(!empty($link['more'])) $ret .= ' '.$link['more'];
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
     * @param string $src       media ID
     * @param string $title     descriptive text
     * @param string $align     left|center|right
     * @param int    $width     width of media in pixel
     * @param int    $height    height of media in pixel
     * @param string $cache     cache|recache|nocache
     * @param bool   $render    should the media be embedded inline or just linked
     * @return string
     */
    function _media($src, $title = null, $align = null, $width = null,
                    $height = null, $cache = null, $render = true) {

        $ret = '';

        list($ext, $mime) = mimetype($src);
        if(substr($mime, 0, 5) == 'image') {
            // first get the $title
            if(!is_null($title)) {
                $title = $this->_xmlEntities($title);
            } elseif($ext == 'jpg' || $ext == 'jpeg') {
                //try to use the caption from IPTC/EXIF
                require_once(DOKU_INC.'inc/JpegMeta.php');
                $jpeg = new JpegMeta(mediaFN($src));
                if($jpeg !== false) $cap = $jpeg->getTitle();
                if(!empty($cap)) {
                    $title = $this->_xmlEntities($cap);
                }
            }
            if(!$render) {
                // if the picture is not supposed to be rendered
                // return the title of the picture
                if(!$title) {
                    // just show the sourcename
                    $title = $this->_xmlEntities(utf8_basename(noNS($src)));
                }
                return $title;
            }
            //add image tag
            $ret .= '<img src="'.ml($src, array('w' => $width, 'h' => $height, 'cache' => $cache, 'rev'=>$this->_getLastMediaRevisionAt($src))).'"';
            $ret .= ' class="media'.$align.'"';

            if($title) {
                $ret .= ' title="'.$title.'"';
                $ret .= ' alt="'.$title.'"';
            } else {
                $ret .= ' alt=""';
            }

            if(!is_null($width))
                $ret .= ' width="'.$this->_xmlEntities($width).'"';

            if(!is_null($height))
                $ret .= ' height="'.$this->_xmlEntities($height).'"';

            $ret .= ' />';

        } elseif(media_supportedav($mime, 'video') || media_supportedav($mime, 'audio')) {
            // first get the $title
            $title = !is_null($title) ? $this->_xmlEntities($title) : false;
            if(!$render) {
                // if the file is not supposed to be rendered
                // return the title of the file (just the sourcename if there is no title)
                return $title ? $title : $this->_xmlEntities(utf8_basename(noNS($src)));
            }

            $att          = array();
            $att['class'] = "media$align";
            if($title) {
                $att['title'] = $title;
            }

            if(media_supportedav($mime, 'video')) {
                //add video
                $ret .= $this->_video($src, $width, $height, $att);
            }
            if(media_supportedav($mime, 'audio')) {
                //add audio
                $ret .= $this->_audio($src, $att);
            }

        } elseif($mime == 'application/x-shockwave-flash') {
            if(!$render) {
                // if the flash is not supposed to be rendered
                // return the title of the flash
                if(!$title) {
                    // just show the sourcename
                    $title = utf8_basename(noNS($src));
                }
                return $this->_xmlEntities($title);
            }

            $att          = array();
            $att['class'] = "media$align";
            if($align == 'right') $att['align'] = 'right';
            if($align == 'left') $att['align'] = 'left';
            $ret .= html_flashobject(
                ml($src, array('cache' => $cache), true, '&'), $width, $height,
                array('quality' => 'high'),
                null,
                $att,
                $this->_xmlEntities($title)
            );
        } elseif($title) {
            // well at least we have a title to display
            $ret .= $this->_xmlEntities($title);
        } else {
            // just show the sourcename
            $ret .= $this->_xmlEntities(utf8_basename(noNS($src)));
        }

        return $ret;
    }

    /**
     * Escape string for output
     *
     * @param $string
     * @return string
     */
    function _xmlEntities($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Creates a linkid from a headline
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @param string  $title   The headline title
     * @param boolean $create  Create a new unique ID?
     * @return string
     */
    function _headerToLink($title, $create = false) {
        if($create) {
            return sectionID($title, $this->headers);
        } else {
            $check = false;
            return sectionID($title, $check);
        }
    }

    /**
     * Construct a title and handle images in titles
     *
     * @author Harry Fuecks <hfuecks@gmail.com>
     * @param string|array $title    either string title or media array
     * @param string       $default  default title if nothing else is found
     * @param bool         $isImage  will be set to true if it's a media file
     * @param null|string  $id       linked page id (used to extract title from first heading)
     * @param string       $linktype content|navigation
     * @return string      HTML of the title, might be full image tag or just escaped text
     */
    function _getLinkTitle($title, $default, &$isImage, $id = null, $linktype = 'content') {
        $isImage = false;
        if(is_array($title)) {
            $isImage = true;
            return $this->_imageTitle($title);
        } elseif(is_null($title) || trim($title) == '') {
            if(useHeading($linktype) && $id) {
                $heading = p_get_first_heading($id);
                if($heading) {
                    return $this->_xmlEntities($heading);
                }
            }
            return $this->_xmlEntities($default);
        } else {
            return $this->_xmlEntities($title);
        }
    }

    /**
     * Returns HTML code for images used in link titles
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @param string $img
     * @return string HTML img tag or similar
     */
    function _imageTitle($img) {
        global $ID;

        // some fixes on $img['src']
        // see internalmedia() and externalmedia()
        list($img['src']) = explode('#', $img['src'], 2);
        if($img['type'] == 'internalmedia') {
            resolve_mediaid(getNS($ID), $img['src'], $exists ,$this->date_at, true);
        }

        return $this->_media(
            $img['src'],
            $img['title'],
            $img['align'],
            $img['width'],
            $img['height'],
            $img['cache']
        );
    }

    /**
     * helperfunction to return a basic link to a media
     *
     * used in internalmedia() and externalmedia()
     *
     * @author   Pierre Spring <pierre.spring@liip.ch>
     * @param string $src       media ID
     * @param string $title     descriptive text
     * @param string $align     left|center|right
     * @param int    $width     width of media in pixel
     * @param int    $height    height of media in pixel
     * @param string $cache     cache|recache|nocache
     * @param bool   $render    should the media be embedded inline or just linked
     * @return array associative array with link config
     */
    function _getMediaLinkConf($src, $title, $align, $width, $height, $cache, $render) {
        global $conf;

        $link           = array();
        $link['class']  = 'media';
        $link['style']  = '';
        $link['pre']    = '';
        $link['suf']    = '';
        $link['more']   = '';
        $link['target'] = $conf['target']['media'];
        $link['title']  = $this->_xmlEntities($src);
        $link['name']   = $this->_media($src, $title, $align, $width, $height, $cache, $render);

        return $link;
    }

    /**
     * Embed video(s) in HTML
     *
     * @author Anika Henke <anika@selfthinker.org>
     *
     * @param string $src         - ID of video to embed
     * @param int    $width       - width of the video in pixels
     * @param int    $height      - height of the video in pixels
     * @param array  $atts        - additional attributes for the <video> tag
     * @return string
     */
    function _video($src, $width, $height, $atts = null) {
        // prepare width and height
        if(is_null($atts)) $atts = array();
        $atts['width']  = (int) $width;
        $atts['height'] = (int) $height;
        if(!$atts['width']) $atts['width'] = 320;
        if(!$atts['height']) $atts['height'] = 240;

        $posterUrl = '';
        $files = array();
        $isExternal = media_isexternal($src);

        if ($isExternal) {
            // take direct source for external files
            list(/*ext*/, $srcMime) = mimetype($src);
            $files[$srcMime] = $src;
        } else {
            // prepare alternative formats
            $extensions   = array('webm', 'ogv', 'mp4');
            $files        = media_alternativefiles($src, $extensions);
            $poster       = media_alternativefiles($src, array('jpg', 'png'), true);
            if(!empty($poster)) {
                $posterUrl = ml(reset($poster), '', true, '&');
            }
        }

        $out = '';
        // open video tag
        $out .= '<video '.buildAttributes($atts).' controls="controls"';
        if($posterUrl) $out .= ' poster="'.hsc($posterUrl).'"';
        $out .= '>'.NL;
        $fallback = '';

        // output source for each alternative video format
        foreach($files as $mime => $file) {
            if ($isExternal) {
                $url = $file;
                $linkType = 'externalmedia';
            } else {
                $url = ml($file, '', true, '&');
                $linkType = 'internalmedia';
            }
            $title = $atts['title'] ? $atts['title'] : $this->_xmlEntities(utf8_basename(noNS($file)));

            $out .= '<source src="'.hsc($url).'" type="'.$mime.'" />'.NL;
            // alternative content (just a link to the file)
            $fallback .= $this->$linkType($file, $title, null, null, null, $cache = null, $linking = 'linkonly', $return = true);
        }

        // finish
        $out .= $fallback;
        $out .= '</video>'.NL;
        return $out;
    }

    /**
     * Embed audio in HTML
     *
     * @author Anika Henke <anika@selfthinker.org>
     *
     * @param string $src       - ID of audio to embed
     * @param array  $atts      - additional attributes for the <audio> tag
     * @return string
     */
    function _audio($src, $atts = null) {
        $files = array();
        $isExternal = media_isexternal($src);

        if ($isExternal) {
            // take direct source for external files
            list(/*ext*/, $srcMime) = mimetype($src);
            $files[$srcMime] = $src;
        } else {
            // prepare alternative formats
            $extensions   = array('ogg', 'mp3', 'wav');
            $files        = media_alternativefiles($src, $extensions);
        }

        $out = '';
        // open audio tag
        $out .= '<audio '.buildAttributes($atts).' controls="controls">'.NL;
        $fallback = '';

        // output source for each alternative audio format
        foreach($files as $mime => $file) {
            if ($isExternal) {
                $url = $file;
                $linkType = 'externalmedia';
            } else {
                $url = ml($file, '', true, '&');
                $linkType = 'internalmedia';
            }
            $title = $atts['title'] ? $atts['title'] : $this->_xmlEntities(utf8_basename(noNS($file)));

            $out .= '<source src="'.hsc($url).'" type="'.$mime.'" />'.NL;
            // alternative content (just a link to the file)
            $fallback .= $this->$linkType($file, $title, null, null, null, $cache = null, $linking = 'linkonly', $return = true);
        }

        // finish
        $out .= $fallback;
        $out .= '</audio>'.NL;
        return $out;
    }
    
    /**
     * _getLastMediaRevisionAt is a helperfunction to internalmedia() and _media()
     * which returns an existing media revision less or equal to rev or date_at
     *
     * @author lisps
     * @param string $media_id
     * @access protected
     * @return string revision ('' for current)
     */
    function _getLastMediaRevisionAt($media_id){
        if(!$this->date_at || media_isexternal($media_id)) return '';
        $pagelog = new MediaChangeLog($media_id);
        return $pagelog->getLastRevisionAt($this->date_at);
    }

    #endregion
}

//Setup VIM: ex: et ts=4 :
