<?php
/**
 * DokuWiki Plugin struct (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr, Michael Große <dokuwiki@cosmocode.de>
 */

use dokuwiki\plugin\struct\meta\LookupTable;

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class syntax_plugin_struct_lookup extends syntax_plugin_struct_table {

    /** @var string which class to use for output */
    protected $tableclass = LookupTable::class;

    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('----+ *struct lookup *-+\n.*?\n----+', $mode, 'plugin_struct_lookup');
    }

    /**
     * Handle matches of the struct syntax
     *
     * @param string $match The match of the syntax
     * @param int $state The state of the handler
     * @param int $pos The position in the document
     * @param Doku_Handler $handler The handler
     * @return array Data for the renderer
     */
    public function handle($match, $state, $pos, Doku_Handler $handler) {
        // usual parsing
        $config = parent::handle($match, $state, $pos, $handler);
        if(is_null($config)) return null;

        // adjust some things for the lookup editor
        $config['cols'] = array('*'); // always select all columns

        return $config;
    }
}
