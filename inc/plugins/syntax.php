<?php
/**
 * Syntax Plugin Prototype
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'inc/plugins/');
require_once(DOKU_INC.'inc/parser/parser.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class DokuWiki_Syntax_Plugin extends Doku_Parser_Mode {

    /**
     * Needs to return one of the mode types defined in $PARSER_MODES in parser.php
     */
    function getType(){
        trigger_error('getType() not implemented in '.get_class($this), E_USER_WARNING);
    }

    /**
     * Handler to prepare matched data for the rendering process
     *
     * Usually you should only need the $match param.
     *
     * @param   $match   string    The text matched by the patterns
     * @param   $state   int       The lexer state for the match
     * @param   $pos     int       The character position of the matched text
     * @param   $handler ref       Reference to the Doku_Handler object
     * @return  array              Return an array with all data you want to use in render
     */
    function handle($match, $state, $pos, &$handler){
        trigger_error('handle() not implemented in '.get_class($this), E_USER_WARNING);
    }

    /**
     * Handles the actual output creation.
     *
     * The function should always check for the given mode and return false
     * when a mode isn't supported.
     *
     * $renderer contains a reference to the renderer object which is
     * currently handling the rendering. You need to use it for writing
     * the output. How this is done depends on the renderer used (specified
     * by $mode
     *
     * The contents of the $data array depends on what the handler() function above
     * created
     *
     * @param   $mode     string   current Rendermode
     * @param   $renderer ref      reference to the current renderer object
     * @param   $data     array    data created by handler()
     * @return  boolean            rendered correctly?
     */
    function render($mode, &$renderer, $data) {
        trigger_error('render() not implemented in '.get_class($this), E_USER_WARNING);
    }

}

//Setup VIM: ex: et ts=4 enc=utf-8 :
