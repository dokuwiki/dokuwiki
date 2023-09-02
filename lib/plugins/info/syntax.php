<?php

use dokuwiki\Extension\SyntaxPlugin;
use dokuwiki\Extension\PluginInterface;

/**
 * Info Plugin: Displays information about various DokuWiki internals
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Esther Brunner <wikidesign@gmail.com>
 */
class syntax_plugin_info extends SyntaxPlugin
{
    /**
     * What kind of syntax are we?
     */
    public function getType()
    {
        return 'substition';
    }

    /**
     * What about paragraphs?
     */
    public function getPType()
    {
        return 'block';
    }

    /**
     * Where to sort in?
     */
    public function getSort()
    {
        return 155;
    }

    /**
     * Connect pattern to lexer
     */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern('~~INFO:\w+~~', $mode, 'plugin_info');
    }

    /**
     * Handle the match
     *
     * @param string $match The text matched by the patterns
     * @param int $state The lexer state for the match
     * @param int $pos The character position of the matched text
     * @param Doku_Handler $handler The Doku_Handler object
     * @return  array Return an array with all data you want to use in render
     */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        $match = substr($match, 7, -2); //strip ~~INFO: from start and ~~ from end
        return [strtolower($match)];
    }

    /**
     * Create output
     *
     * @param string $format string     output format being rendered
     * @param Doku_Renderer $renderer the current renderer object
     * @param array $data data created by handler()
     * @return  boolean                 rendered correctly?
     */
    public function render($format, Doku_Renderer $renderer, $data)
    {
        if ($format == 'xhtml') {
            /** @var Doku_Renderer_xhtml $renderer */
            //handle various info stuff
            switch ($data[0]) {
                case 'syntaxmodes':
                    $renderer->doc .= $this->renderSyntaxModes();
                    break;
                case 'syntaxtypes':
                    $renderer->doc .= $this->renderSyntaxTypes();
                    break;
                case 'syntaxplugins':
                    $this->renderPlugins('syntax', $renderer);
                    break;
                case 'adminplugins':
                    $this->renderPlugins('admin', $renderer);
                    break;
                case 'actionplugins':
                    $this->renderPlugins('action', $renderer);
                    break;
                case 'rendererplugins':
                    $this->renderPlugins('renderer', $renderer);
                    break;
                case 'helperplugins':
                    $this->renderPlugins('helper', $renderer);
                    break;
                case 'authplugins':
                    $this->renderPlugins('auth', $renderer);
                    break;
                case 'remoteplugins':
                    $this->renderPlugins('remote', $renderer);
                    break;
                case 'helpermethods':
                    $this->renderHelperMethods($renderer);
                    break;
                case 'hooks':
                    $this->renderHooks($renderer);
                    break;
                case 'datetime':
                    $renderer->doc .= date('r');
                    break;
                default:
                    $renderer->doc .= "no info about " . htmlspecialchars($data[0]);
            }
            return true;
        }
        return false;
    }

    /**
     * list all installed plugins
     *
     * uses some of the original renderer methods
     *
     * @param string $type
     * @param Doku_Renderer $renderer
     */
    protected function renderPlugins($type, Doku_Renderer $renderer)
    {
        global $lang;
        $plugins = plugin_list($type);
        $plginfo = [];

        // remove subparts
        foreach ($plugins as $p) {
            $po = plugin_load($type, $p);
            if (! $po instanceof PluginInterface) continue;
            [$name, /* part */] = explode('_', $p, 2);
            $plginfo[$name] = $po->getInfo();
        }

        // list them
        $renderer->listu_open();
        foreach ($plginfo as $info) {
            $renderer->listitem_open(1);
            $renderer->listcontent_open();
            $renderer->externallink($info['url'], $info['name']);
            $renderer->cdata(' ');
            $renderer->emphasis_open();
            $renderer->cdata($info['date']);
            $renderer->emphasis_close();
            $renderer->cdata(' ' . $lang['by'] . ' ');
            $renderer->emaillink($info['email'], $info['author']);
            $renderer->linebreak();
            $renderer->cdata($info['desc']);
            $renderer->listcontent_close();
            $renderer->listitem_close();
        }
        $renderer->listu_close();
    }

    /**
     * list all installed plugins
     *
     * uses some of the original renderer methods
     *
     * @param Doku_Renderer_xhtml $renderer
     */
    protected function renderHelperMethods(Doku_Renderer_xhtml $renderer)
    {
        $plugins = plugin_list('helper');
        foreach ($plugins as $p) {
            $po = plugin_load('helper', $p);
            if (!$po instanceof PluginInterface) continue;

            if (!method_exists($po, 'getMethods')) continue;
            $methods = $po->getMethods();
            $info = $po->getInfo();

            $hid = $this->addToToc($info['name'], 2, $renderer);
            $doc = '<h2><a name="' . $hid . '" id="' . $hid . '">' . hsc($info['name']) . '</a></h2>';
            $doc .= '<div class="level2">';
            $doc .= '<p>' . strtr(hsc($info['desc']), ["\n" => "<br />"]) . '</p>';
            $doc .= '<pre class="code">$' . $p . " = plugin_load('helper', '" . $p . "');</pre>";
            $doc .= '</div>';
            foreach ($methods as $method) {
                $title = '$' . $p . '->' . $method['name'] . '()';
                $hid = $this->addToToc($title, 3, $renderer);
                $doc .= '<h3><a name="' . $hid . '" id="' . $hid . '">' . hsc($title) . '</a></h3>';
                $doc .= '<div class="level3">';
                $doc .= '<div class="table"><table class="inline"><tbody>';
                $doc .= '<tr><th>Description</th><td colspan="2">' . $method['desc'] .
                    '</td></tr>';
                if ($method['params']) {
                    $c = count($method['params']);
                    $doc .= '<tr><th rowspan="' . $c . '">Parameters</th><td>';
                    $params = [];
                    foreach ($method['params'] as $desc => $type) {
                        $params[] = hsc($desc) . '</td><td>' . hsc($type);
                    }
                    $doc .= implode('</td></tr><tr><td>', $params) . '</td></tr>';
                }
                if ($method['return']) {
                    $doc .= '<tr><th>Return value</th><td>' . hsc(key($method['return'])) .
                        '</td><td>' . hsc(current($method['return'])) . '</td></tr>';
                }
                $doc .= '</tbody></table></div>';
                $doc .= '</div>';
            }
            unset($po);

            $renderer->doc .= $doc;
        }
    }

    /**
     * lists all known syntax types and their registered modes
     *
     * @return string
     */
    protected function renderSyntaxTypes()
    {
        global $PARSER_MODES;
        $doc = '';

        $doc .= '<div class="table"><table class="inline"><tbody>';
        foreach ($PARSER_MODES as $mode => $modes) {
            $doc .= '<tr>';
            $doc .= '<td class="leftalign">';
            $doc .= $mode;
            $doc .= '</td>';
            $doc .= '<td class="leftalign">';
            $doc .= implode(', ', $modes);
            $doc .= '</td>';
            $doc .= '</tr>';
        }
        $doc .= '</tbody></table></div>';
        return $doc;
    }

    /**
     * lists all known syntax modes and their sorting value
     *
     * @return string
     */
    protected function renderSyntaxModes()
    {
        $modes = p_get_parsermodes();

        $compactmodes = [];
        foreach ($modes as $mode) {
            $compactmodes[$mode['sort']][] = $mode['mode'];
        }
        $doc = '';
        $doc .= '<div class="table"><table class="inline"><tbody>';

        foreach ($compactmodes as $sort => $modes) {
            $rowspan = '';
            if (count($modes) > 1) {
                $rowspan = ' rowspan="' . count($modes) . '"';
            }

            foreach ($modes as $index => $mode) {
                $doc .= '<tr>';
                $doc .= '<td class="leftalign">';
                $doc .= $mode;
                $doc .= '</td>';

                if ($index === 0) {
                    $doc .= '<td class="rightalign" ' . $rowspan . '>';
                    $doc .= $sort;
                    $doc .= '</td>';
                }
                $doc .= '</tr>';
            }
        }

        $doc .= '</tbody></table></div>';
        return $doc;
    }

    /**
     * Render all currently registered event handlers
     *
     * @param Doku_Renderer $renderer
     */
    protected function renderHooks(Doku_Renderer $renderer)
    {
        global $EVENT_HANDLER;

        $list = $EVENT_HANDLER->getEventHandlers();
        ksort($list);

        $renderer->listu_open();
        foreach ($list as $event => $handlers) {
            $renderer->listitem_open(1);
            $renderer->listcontent_open();
            $renderer->cdata($event);
            $renderer->listcontent_close();

            $renderer->listo_open();
            foreach ($handlers as $sequence) {
                foreach ($sequence as $handler) {
                    $renderer->listitem_open(2);
                    $renderer->listcontent_open();
                    $renderer->cdata(get_class($handler[0]) . '::' . $handler[1] . '()');
                    $renderer->listcontent_close();
                    $renderer->listitem_close();
                }
            }
            $renderer->listo_close();
            $renderer->listitem_close();
        }
        $renderer->listu_close();
    }

    /**
     * Adds a TOC item
     *
     * @param string $text
     * @param int $level
     * @param Doku_Renderer_xhtml $renderer
     * @return string
     */
    protected function addToToc($text, $level, Doku_Renderer_xhtml $renderer)
    {
        global $conf;

        $hid = '';
        if (($level >= $conf['toptoclevel']) && ($level <= $conf['maxtoclevel'])) {
            $hid = $renderer->_headerToLink($text, true);
            $renderer->toc[] = [
                'hid' => $hid,
                'title' => $text,
                'type' => 'ul',
                'level' => $level - $conf['toptoclevel'] + 1
            ];
        }
        return $hid;
    }
}
