<?php

namespace dokuwiki\Extension;

use Doku_Handler;
use Doku_Renderer;

/**
 * Syntax Plugin Prototype
 *
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */
abstract class SyntaxPlugin extends \dokuwiki\Parsing\ParserMode\Plugin
{
    use PluginTrait;

    protected $allowedModesSetup = false;

    /**
     * Syntax Type
     *
     * Needs to return one of the mode types defined in $PARSER_MODES in Parser.php
     *
     * @return string
     */
    abstract public function getType();

    /**
     * Allowed Mode Types
     *
     * Defines the mode types for other dokuwiki markup that maybe nested within the
     * plugin's own markup. Needs to return an array of one or more of the mode types
     * defined in $PARSER_MODES in Parser.php
     *
     * @return array
     */
    public function getAllowedTypes()
    {
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
     *
     * @return string
     */
    public function getPType()
    {
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
     * @param   string $match The text matched by the patterns
     * @param   int $state The lexer state for the match
     * @param   int $pos The character position of the matched text
     * @param   Doku_Handler $handler The Doku_Handler object
     * @return  bool|array Return an array with all data you want to use in render, false don't add an instruction
     */
    abstract public function handle($match, $state, $pos, Doku_Handler $handler);

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
     * @param string $format output format being rendered
     * @param Doku_Renderer $renderer the current renderer object
     * @param array $data data created by handler()
     * @return  boolean                 rendered correctly? (however, returned value is not used at the moment)
     */
    abstract public function render($format, Doku_Renderer $renderer, $data);

    /**
     *  There should be no need to override this function
     *
     * @param string $mode
     * @return bool
     */
    public function accepts($mode)
    {

        if (!$this->allowedModesSetup) {
            global $PARSER_MODES;

            $allowedModeTypes = $this->getAllowedTypes();
            foreach ($allowedModeTypes as $mt) {
                $this->allowedModes = array_merge($this->allowedModes, $PARSER_MODES[$mt]);
            }

            $idx = array_search(substr(get_class($this), 7), (array)$this->allowedModes);
            if ($idx !== false) {
                unset($this->allowedModes[$idx]);
            }
            $this->allowedModesSetup = true;
        }

        return parent::accepts($mode);
    }
}
