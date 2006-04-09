<?php
//FIXME!! remove the line ignoring the 'wiki' export mode from
// inc/parserutils.php#p_renderer() when this file works.

/**
* Basis for converting to Dokuwiki syntax
* This is not yet complete but useable for converting
* phpWiki syntax.
* Main issues lie with lists, quote and tables
*/
class Doku_Renderer_Wiki extends Doku_Renderer {

    var $doc = '';

    // This should be eliminated
    var $listMarker = '*';

    function document_start() {
        ob_start();
    }

    function document_end() {

        $this->doc .= ob_get_contents();
        ob_end_clean();

    }

    function header($text, $level) {
        $levels = array(
            1=>'======',
            2=>'=====',
            3=>'====',
            4=>'===',
            5=>'==',
        );

        if ( isset($levels[$level]) ) {
            $token = $levels[$level];
        } else {
            $token = $levels[1];
        }
        echo "\n{$token} ";
        echo trim($text);
        echo " {$token}\n";
    }

    function cdata($text) {
        echo $text;
    }

    function linebreak() {
        echo '\\\\ ';
    }

    function hr() {
        echo "\n----\n";
    }

    function strong_open() {
        echo '**';
    }

    function strong_close() {
        echo '**';
    }

    function emphasis_open() {
        echo '//';
    }

    function emphasis_close() {
        echo '//';
    }

    function underline_open() {
        echo '__';
    }

    function underline_close() {
        echo '__';
    }

    function monospace_open() {
        echo "''";
    }

    function monospace_close() {
        echo "''";
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
        echo '((';
    }

    function footnote_close() {
        echo '))';
    }

    function listu_open() {
        $this->listMarker = '*';
        echo "\n";
    }

    function listo_open() {
        $this->listMarker = '-';
        echo "\n";
    }

    /**
    * @TODO Problem here with nested lists
    */
    function listitem_open($level) {
        echo str_repeat('  ', $level).$this->listMarker;
    }

    function listitem_close() {
        echo "\n";
    }

    function unformatted($text) {
        echo '%%'.$text.'%%';
    }

    function php($text) {
        echo "\n<php>\n$text\n</php>\n";
    }

    function html($text) {
        echo "\n<html>\n$text\n</html>\n";
    }

    /**
    * Indent?
    */
    function preformatted($text) {
        echo "\n<code>\n$text\n</code>\n";
    }

    function file($text) {
        echo "\n<file>\n$text\n</file>\n";
    }

    /**
    * Problem here with nested quotes
    */
    function quote_open() {
        echo '>';
    }

    function quote_close() {
        echo "\n";
    }

    function code($text, $lang = NULL) {
        if ( !$lang ) {
            echo "\n<code>\n$text\n</code>\n";
        } else {
            echo "\n<code $lang>\n$text\n</code>\n";
        }

    }

    function acronym($acronym) {
        echo $acronym;
    }

    function smiley($smiley) {
        echo $smiley;
    }

    function wordblock($word) {
        echo $word;
    }

    function entity($entity) {
        echo $entity;
    }

    // 640x480 ($x=640, $y=480)
    function multiplyentity($x, $y) {
        echo "{$x}x{$y}";
    }

    function singlequoteopening() {
        echo "'";
    }

    function singlequoteclosing() {
        echo "'";
    }

    function doublequoteopening() {
        echo '"';
    }

    function doublequoteclosing() {
        echo '"';
    }

    // $link like 'SomePage'
    function camelcaselink($link) {
        echo $link;
    }

    // $link like 'wikie:syntax', $title could be an array (media)
    function internallink($link, $title = NULL) {
        if ( $title ) {
            echo '[['.$link.'|'.$title.']]';
        } else {
            echo '[['.$link.']]';
        }
    }

    // $link is full URL with scheme, $title could be an array (media)
    function externallink($link, $title = NULL) {
        if ( $title ) {
            echo '[['.$link.'|'.$title.']]';
        } else {
            echo '[['.$link.']]';
        }
    }

    // $link is the original link - probably not much use
    // $wikiName is an indentifier for the wiki
    // $wikiUri is the URL fragment to append to some known URL
    function interwikilink($link, $title = NULL, $wikiName, $wikiUri) {
        if ( $title ) {
            echo '[['.$link.'|'.$title.']]';
        } else {
            echo '[['.$link.']]';
        }
    }

    // Link to file on users OS, $title could be an array (media)
    function filelink($link, $title = NULL) {
        if ( $title ) {
            echo '[['.$link.'|'.$title.']]';
        } else {
            echo '[['.$link.']]';
        }
    }

    // Link to a Windows share, , $title could be an array (media)
    function windowssharelink($link, $title = NULL) {
        if ( $title ) {
            echo '[['.$link.'|'.$title.']]';
        } else {
            echo '[['.$link.']]';
        }
    }

    function email($address, $title = NULL) {
        if ( $title ) {
            echo '[['.$address.'|'.$title.']]';
        } else {
            echo '[['.$address.']]';
        }
    }

    // @TODO
    function internalmedialink (
        $src,$title=NULL,$align=NULL,$width=NULL,$height=NULL,$cache=NULL
        ) {

    }

    // @TODO
    function externalmedialink(
        $src,$title=NULL,$align=NULL,$width=NULL,$height=NULL,$cache=NULL
        ) {
        if ( $title ) {
            echo '{{'.$src.'|'.$title.'}}';
        } else {
            echo '{{'.$src.'}}';
        }
    }

    function table_open($maxcols = NULL, $numrows = NULL){}

    function table_close(){}

    function tablerow_open(){}

    function tablerow_close(){}

    function tableheader_open($colspan = 1, $align = NULL){}

    function tableheader_close(){}

    function tablecell_open($colspan = 1, $align = NULL){}

    function tablecell_close(){}

}


//Setup VIM: ex: et ts=2 enc=utf-8 :
