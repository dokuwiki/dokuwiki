<?php
/**
 * Info Plugin: Displays information about various DokuWiki internals
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'inc/plugins/');
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
                case 'foo': 
                    $renderer->doc .= "foo is foo";
                    break;
                default:
                    $renderer->doc .= "no info about ".htmlspecialchars($data[0]);
            }
            return true;
        }
        return false;
    }

}

//Setup VIM: ex: et ts=4 enc=utf-8 :
