<?php
/**
 * Syntax Plugin Prototype
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class DokuWiki_Syntax_Plugin extends Doku_Parser_Mode_Plugin {

    var $allowedModesSetup = false;

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
     * @param   string       $match   The text matched by the patterns
     * @param   int          $state   The lexer state for the match
     * @param   int          $pos     The character position of the matched text
     * @param   Doku_Handler $handler The Doku_Handler object
     * @return  array Return an array with all data you want to use in render
     */
    function handle($match, $state, $pos, Doku_Handler $handler){
        trigger_error('handle() not implemented in '.get_class($this), E_USER_WARNING);
    }

    /**
     * Handles the actual output creation.
     *
     * The function must not assume any other of the classes methods have been run
     * during the object's current life. The only reliable data it receives are its
     * parameters.
     *
     * The function should always check for the given output format and return false
     * when a format isn't supported.
     *
     * $renderer contains a reference to the renderer object which is
     * currently handling the rendering. You need to use it for writing
     * the output. How this is done depends on the renderer used (specified
     * by $format
     *
     * The contents of the $data array depends on what the handler() function above
     * created
     *
     * @param   $format   string        output format being rendered
     * @param   $renderer Doku_Renderer the current renderer object
     * @param   $data     array         data created by handler()
     * @return  boolean                 rendered correctly?
     */
    function render($format, Doku_Renderer $renderer, $data) {
        trigger_error('render() not implemented in '.get_class($this), E_USER_WARNING);

    }

    /**
     *  There should be no need to override these functions
     */
    function accepts($mode) {

        if (!$this->allowedModesSetup) {
            global $PARSER_MODES;

            $allowedModeTypes = $this->getAllowedTypes();
            foreach($allowedModeTypes as $mt) {
                $this->allowedModes = array_merge($this->allowedModes, $PARSER_MODES[$mt]);
            }

            $idx = array_search(substr(get_class($this), 7), (array) $this->allowedModes);
            if ($idx !== false) {
                unset($this->allowedModes[$idx]);
            }
            $this->allowedModesSetup = true;
        }

        return parent::accepts($mode);
    }
}
//Setup VIM: ex: et ts=4 :
