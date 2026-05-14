<?php

use dokuwiki\Extension\SyntaxPlugin;

/**
 * Info Indexmenu tag: Tag a page with a sort number.
 *
 * @license     GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author      Samuele Tognini <samuele@samuele.netsons.org>
 *
 */
/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_indexmenu_tag extends SyntaxPlugin
{
    /**
     * What kind of syntax are we?
     */
    public function getType()
    {
        return 'substition';
    }

    /**
     * Where to sort in?
     */
    public function getSort()
    {
        return 139;
    }

    /**
     * Connect pattern to lexer
     *
     * @param string $mode
     */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern('{{indexmenu_n>.+?}}', $mode, 'plugin_indexmenu_tag');
    }

    /**
     * Handle the match
     *
     * @param   string       $match The text matched by the patterns
     * @param   int          $state The lexer state for the match
     * @param   int          $pos The character position of the matched text
     * @param   Doku_Handler $handler The Doku_Handler object
     * @return  array Return an array with all data you want to use in render
     */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        $match = substr($match, 14, -2);
        $match = str_replace("\xE2\x80\x8B", "", $match);
        return [$match];
    }

    /**
     * Render output
     *
     * @param string        $format output format being rendered
     * @param Doku_Renderer $renderer the current renderer object
     * @param array         $data data created by handler()
     */
    public function render($format, Doku_Renderer $renderer, $data)
    {
        if ($format == 'metadata') {
            /** @var Doku_Renderer_metadata $renderer */
            if (is_numeric($data[0])) {
                $renderer->meta['indexmenu_n'] = $data[0];
            }
        }
    }
}
