<?php
/**
 * Info Plugin: Displays information about various DokuWiki internals
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Esther Brunner <wikidesign@gmail.com>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

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
            'date'   => '2008-09-12',
            'name'   => 'Info Plugin',
            'desc'   => 'Displays information about various DokuWiki internals',
            'url'    => 'http://dokuwiki.org/plugin:info',
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
                    $this->_plugins_xhtml('syntax', $renderer);
                    break;
                case 'adminplugins':
                    $this->_plugins_xhtml('admin', $renderer);
                    break;
                case 'actionplugins':
                    $this->_plugins_xhtml('action', $renderer);
                    break;
                case 'rendererplugins':
                    $this->_plugins_xhtml('renderer', $renderer);
                    break;
                case 'helperplugins':
                    $this->_plugins_xhtml('helper', $renderer);
                    break;
                case 'helpermethods':
                    $this->_helpermethods_xhtml($renderer);
                    break;
                default:
                    $renderer->doc .= "no info about ".htmlspecialchars($data[0]);
            }
            return true;
        }
        return false;
    }

    /**
     * list all installed plugins
     *
     * uses some of the original renderer methods
     */
    function _plugins_xhtml($type, &$renderer){
        global $lang;
        $renderer->doc .= '<ul>';

        $plugins = plugin_list($type);
        $plginfo = array();

        // remove subparts
        foreach($plugins as $p){
            if (!$po =& plugin_load($type,$p)) continue;
            list($name,$part) = explode('_',$p,2);
            $plginfo[$name] = $po->getInfo();
        }

        // list them
        foreach($plginfo as $info){
            $renderer->doc .= '<li><div class="li">';
            $renderer->externallink($info['url'],$info['name']);
            $renderer->doc .= ' ';
            $renderer->doc .= '<em>'.$info['date'].'</em>';
            $renderer->doc .= ' ';
            $renderer->doc .= $lang['by'];
            $renderer->doc .= ' ';
            $renderer->emaillink($info['email'],$info['author']);
            $renderer->doc .= '<br />';
            $renderer->doc .= strtr(hsc($info['desc']),array("\n"=>"<br />"));
            $renderer->doc .= '</div></li>';
            unset($po);
        }

        $renderer->doc .= '</ul>';
    }

    /**
     * list all installed plugins
     *
     * uses some of the original renderer methods
     */
    function _helpermethods_xhtml(&$renderer){
        global $lang;

        $plugins = plugin_list('helper');
        foreach($plugins as $p){
            if (!$po =& plugin_load('helper',$p)) continue;

            if (!method_exists($po, 'getMethods')) continue;
            $methods = $po->getMethods();
            $info = $po->getInfo();

            $hid = $this->_addToTOC($info['name'], 2, $renderer);
            $doc = '<h2><a name="'.$hid.'" id="'.$hid.'">'.hsc($info['name']).'</a></h2>';
            $doc .= '<div class="level2">';
            $doc .= '<p>'.strtr(hsc($info['desc']), array("\n"=>"<br />")).'</p>';
            $doc .= '<pre class="code">$'.$p." =& plugin_load('helper', '".$p."');</pre>";
            $doc .= '</div>';
            foreach ($methods as $method){
                $title = '$'.$p.'->'.$method['name'].'()';
                $hid = $this->_addToTOC($title, 3, $renderer);
                $doc .= '<h3><a name="'.$hid.'" id="'.$hid.'">'.hsc($title).'</a></h3>';
                $doc .= '<div class="level3">';
                $doc .= '<table class="inline"><tbody>';
                $doc .= '<tr><th>Description</th><td colspan="2">'.$method['desc'].
                    '</td></tr>';
                if ($method['params']){
                    $c = count($method['params']);
                    $doc .= '<tr><th rowspan="'.$c.'">Parameters</th><td>';
                    $params = array();
                    foreach ($method['params'] as $desc => $type){
                        $params[] = hsc($desc).'</td><td>'.hsc($type);
                    }
                    $doc .= join($params, '</td></tr><tr><td>').'</td></tr>';
                }
                if ($method['return']){
                    $doc .= '<tr><th>Return value</th><td>'.hsc(key($method['return'])).
                        '</td><td>'.hsc(current($method['return'])).'</td></tr>';
                }
                $doc .= '</tbody></table>';
                $doc .= '</div>';
            }
            unset($po);

            $renderer->doc .= $doc;
        }
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

    /**
     * Adds a TOC item
     */
    function _addToTOC($text, $level, &$renderer){
        global $conf;

        if (($level >= $conf['toptoclevel']) && ($level <= $conf['maxtoclevel'])){
            $hid  = $renderer->_headerToLink($text, 'true');
            $renderer->toc[] = array(
                'hid'   => $hid,
                'title' => $text,
                'type'  => 'ul',
                'level' => $level - $conf['toptoclevel'] + 1
            );
        }
        return $hid;
    }
}

//Setup VIM: ex: et ts=4 :
