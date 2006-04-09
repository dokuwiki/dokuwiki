<?php
/**
 * Info Plugin: Displays information about various DokuWiki internals
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_info extends DokuWiki_Syntax_Plugin {

    /**
     * return some info
     */
    function getInfo(){
        return array(
            'author' => 'Andreas Gohr',
            'email'  => 'andi@splitbrain.org',
            'date'   => '2005-08-03',
            'name'   => 'Info Plugin',
            'desc'   => 'Displays information about various DokuWiki internals',
            'url'    => 'http://wiki.splitbrain.org/plugin:info',
        );
    }

    /**
     * What kind of syntax are we?
     */
    function getType(){
        return 'substition';
    }
   
    /**
     * What about paragraphs?
     */
    function getPType(){
        return 'block';
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
    function render($format, &$renderer, $data) {
        if($format == 'xhtml'){
            //handle various info stuff
            switch ($data[0]){
                case 'version':
                    $renderer->doc .= getVersion();
                    break;
                case 'syntaxmodes':
                    $renderer->doc .= $this->_syntaxmodes_xhtml();
                    break;
                case 'syntaxtypes':
                    $renderer->doc .= $this->_syntaxtypes_xhtml();
                    break;
                case 'syntaxplugins':
                    $this->_syntaxplugins_xhtml($renderer);
                    break;
                default:
                    $renderer->doc .= "no info about ".htmlspecialchars($data[0]);
            }
            return true;
        }
        return false;
    }

    /**
     * list all installed syntax plugins
     *
     * uses some of the original renderer methods
     */
    function _syntaxplugins_xhtml(& $renderer){
        global $lang;
        $renderer->doc .= '<ul>';

        $plugins = plugin_list('syntax');
        foreach($plugins as $p){
            if (!$po =& plugin_load('syntax',$p)) continue;
            $info = $po->getInfo();

            $renderer->doc .= '<li><div class="li">';
            $renderer->externallink($info['url'],$info['name']);
            $renderer->doc .= ' ';
            $renderer->doc .= '<em>'.$info['date'].'</em>';
            $renderer->doc .= ' ';
            $renderer->doc .= $lang['by'];
            $renderer->doc .= ' ';
            $renderer->emaillink($info['email'],$info['author']);
            $renderer->doc .= '<br />';
            $renderer->doc .= strtr(htmlspecialchars($info['desc']),array("\n"=>"<br />"));
            $renderer->doc .= '</div></li>';
            unset($po);
        }

        $renderer->doc .= '</ul>';
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
