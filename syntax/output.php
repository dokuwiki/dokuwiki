<?php
/**
 * DokuWiki Plugin struct (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr, Michael Große <dokuwiki@cosmocode.de>
 */

// must be run within Dokuwiki
use plugin\struct\meta\Assignments;
use plugin\struct\meta\SchemaData;

if (!defined('DOKU_INC')) die();

class syntax_plugin_struct_list extends DokuWiki_Syntax_Plugin {
    /**
     * @return string Syntax mode type
     */
    public function getType() {
        return 'substition';
    }
    /**
     * @return string Paragraph type
     */
    public function getPType() {
        return 'block';
    }
    /**
     * @return int Sort order - Low numbers go before high numbers
     */
    public function getSort() {
        return 155;
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('~~STRUCT~~',$mode,'plugin_struct_list');
    }


    /**
     * Handle matches of the struct syntax
     *
     * @param string $match The match of the syntax
     * @param int    $state The state of the handler
     * @param int    $pos The position in the document
     * @param Doku_Handler    $handler The handler
     * @return array Data for the renderer
     */
    public function handle($match, $state, $pos, Doku_Handler $handler){
        $data = array();

        return $data;
    }

    /**
     * Render xhtml output or metadata
     *
     * @param string         $mode      Renderer mode (supported modes: xhtml)
     * @param Doku_Renderer  $R         The renderer
     * @param array          $data      The data from the handler() function
     * @return bool If rendering was successful.
     */
    public function render($mode, Doku_Renderer $R, $data) {
        global $ID;
        global $REV;

        $assignments = new Assignments();
        $tables = $assignments->getPageAssignments($ID);
        if(!$tables) return true;

        $R->table_open();
        $R->tabletbody_open();
        foreach($tables as $table) {
            $schemadata = new SchemaData($table, $ID, $REV);
            $data = $schemadata->getData();

            foreach($data as $field) {
                $R->tablerow_open();
                $R->tableheader_open();
                $R->cdata($field->getColumn()->getLabel());
                $R->tableheader_close();
                $R->tablecell_open();
                $field->render($R, $mode);
                $R->tablecell_close();
                $R->tablerow_close();
            }
        }
        $R->tabletbody_close();
        $R->table_close();



        if($mode != 'xhtml') return false;

        return true;
    }
}

// vim:ts=4:sw=4:et:
