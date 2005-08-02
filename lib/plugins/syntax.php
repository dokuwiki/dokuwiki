<?php
/**
 * Syntax Plugin Prototype
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_INC.'inc/parser/parser.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class DokuWiki_Syntax_Plugin extends Doku_Parser_Mode {

    var $allowedModesSetup = false;
    
    /**
     * General Info
     *
     * Needs to return a associative array with the following values:
     *
     * author - Author of the plugin
     * email  - Email address to contact the author
     * date   - Last modified date of the plugin in YYYY-MM-DD format
     * name   - Name of the plugin
     * desc   - Short description of the plugin (Text only)
     * url    - Website with more information on the plugin (eg. syntax description)
     */
    function getInfo(){
        trigger_error('getType() not implemented in '.get_class($this), E_USER_WARNING);
    }

    /**
     * Syntax Type
     *
     * Needs to return one of the mode types defined in $PARSER_MODES in parser.php
     */
    function getType(){
        trigger_error('getType() not implemented in '.get_class($this), E_USER_WARNING);
    }
    
    /**
     * Allowed Mode Types
     *
     * Defines the mode types for other dokuwiki markup that maybe nested within the 
     * plugin's own markup. Needs to return an array of one or more of the mode types 
     * defined in $PARSER_MODES in parser.php
     */
    function getAllowedTypes() {
        return array();
    }

    /**
     * Paragraph Type
     *
     * Defines how this syntax is handled regarding paragraphs. This is important
     * for correct XHTML nesting. Should return one of the following:
     *
     * 'normal' - The plugin can be used inside paragraphs



     * 'block'  - Open paragraphs need to be closed before plugin output
     * 'stack'  - Special case. Plugin wraps other paragraphs.
     *
     * @see Doku_Handler_Block
     */
    function getPType(){
        return 'normal';
    }

    /**
     * Handler to prepare matched data for the rendering process
     *
     * This function can only pass data to render() via its return value - render()
     * may be not be run during the object's current life.
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
     * The function must not assume any other of the classes methods have been run
     * during the object's current life. The only reliable data it receives are its
     * parameters.
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
    
    /**
     *  There should be no need to override this function
     */
    function accepts($mode) {

        if (!$this->allowedModesSetup) {
            global $PARSER_MODES;

            $allowedModeTypes = $this->getAllowedTypes();
            foreach($allowedModeTypes as $mt) {
                $this->allowedModes = array_merge($this->allowedModes, $PARSER_MODES[$mt]);
            }        
                
            unset($this->allowedModes[array_search(substr(get_class($this), 7), $this->allowedModes)]);
            $this->allowedModesSetup = true;
        }
        
        return parent::accepts($mode);            
    }

}
//Setup VIM: ex: et ts=4 enc=utf-8 :