<?php
/**
 * Info Plugin: Displays information about various DokuWiki internals
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_info extends DokuWiki_Syntax_Plugin {

    /**
     * What kind of syntax are we?
     */
    function getType(){
        return 'substition';
    }
   
    /**
     * Where to sort in?
     */ 
    function getSort(){
        return 155;
    }


    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('~~INFO:\w+~~',$mode,'plugin_info');
    }


    /**
     * Handle the match
     */
    function handle($match, $state, $pos, &$handler){
        $match = substr($match,7,-2); //strip ~~INFO: from start and ~~ from end
        return array(strtolower($match));
    }

    /**
     * Create output
     */
    function render($mode, &$renderer, $data) {
        if($mode == 'xhtml'){
            //handle various info stuff
            switch ($data[0]){
                case 'version';
                    $renderer->doc .= getVersion();
                    break;
                case 'syntaxmodes';
                    $renderer->doc .= $this->_syntaxmodes_xhtml();
                    break;
                case 'syntaxtypes';
                    $renderer->doc .= $this->_syntaxtypes_xhtml();
                    break;
                default:
                    $renderer->doc .= "no info about ".htmlspecialchars($data[0]);
            }
            return true;
        }
        return false;
    }

    /**
     * lists all known syntax types and their registered modes
     */
    function _syntaxtypes_xhtml(){
        global $PARSER_MODES;
        $doc  = '';

        $doc .= '<table class="inline"><tbody>';
        foreach($PARSER_MODES as $mode => $modes){
            $doc .= '<tr>';
            $doc .= '<td class="leftalign">';
            $doc .= $mode;
            $doc .= '</td>';
            $doc .= '<td class="leftalign">';
            $doc .= join(', ',$modes);
            $doc .= '</td>';
            $doc .= '</tr>';
        }
        $doc .= '</tbody></table>';
        return $doc;
    }

    /**
     * lists all known syntax modes and their sorting value
     */
    function _syntaxmodes_xhtml(){
        $modes = p_get_parsermodes();
        $doc  = '';

        foreach ($modes as $mode){
            $doc .= $mode['mode'].' ('.$mode['sort'].'), ';
        }
        return $doc;
    }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
