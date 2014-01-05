<?php
/**
 * A simple renderer that allows downloading of code and file snippets
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
if(!defined('DOKU_INC')) die('meh.');
require_once DOKU_INC . 'inc/parser/renderer.php';

class Doku_Renderer_code extends Doku_Renderer {
    var $_codeblock=0;

    /**
     * Send the wanted code block to the browser
     *
     * When the correct block was found it exits the script.
     */
    function code($text, $language = null, $filename='' ) {
        global $INPUT;
        if(!$language) $language = 'txt';
        if(!$filename) $filename = 'snippet.'.$language;
        $filename = utf8_basename($filename);
        $filename = utf8_stripspecials($filename, '_');

        if($this->_codeblock == $INPUT->str('codeblock')){
            header("Content-Type: text/plain; charset=utf-8");
            header("Content-Disposition: attachment; filename=$filename");
            header("X-Robots-Tag: noindex");
            echo trim($text,"\r\n");
            exit;
        }

        $this->_codeblock++;
    }

    /**
     * Wraps around code()
     */
    function file($text, $language = null, $filename='') {
        $this->code($text, $language, $filename);
    }

    /**
     * This should never be reached, if it is send a 404
     */
    function document_end() {
        http_status(404);
        echo '404 - Not found';
        exit;
    }

    /**
     * Return the format of the renderer
     *
     * @returns string 'code'
     */
    function getFormat(){
        return 'code';
    }
}
