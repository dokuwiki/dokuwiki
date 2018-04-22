#!/usr/bin/php
<?php

use splitbrain\phpcli\CLI;
use splitbrain\phpcli\Options;

if(!defined('DOKU_INC')) define('DOKU_INC', realpath(dirname(__FILE__) . '/../') . '/');
define('NOSESSION', 1);
require_once(DOKU_INC . 'inc/init.php');

/**
 * A simple commandline tool to render some DokuWiki syntax with a given
 * renderer.
 *
 * This may not work for plugins that expect a certain environment to be
 * set up before rendering, but should work for most or even all standard
 * DokuWiki markup
 *
 * @license GPL2
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
class RenderCLI extends CLI {

    /**
     * Register options and arguments on the given $options object
     *
     * @param Options $options
     * @return void
     */
    protected function setup(Options $options) {
        $options->setHelp(
            'A simple commandline tool to render some DokuWiki syntax with a given renderer.' .
            "\n\n" .
            'This may not work for plugins that expect a certain environment to be ' .
            'set up before rendering, but should work for most or even all standard ' .
            'DokuWiki markup'
        );
        $options->registerOption('renderer', 'The renderer mode to use. Defaults to xhtml', 'r', 'mode');
    }

    /**
     * Your main program
     *
     * Arguments and options have been parsed when this is run
     *
     * @param Options $options
     * @throws DokuCLI_Exception
     * @return void
     */
    protected function main(Options $options) {
        $renderer = $options->getOpt('renderer', 'xhtml');

        // do the action
        $source = stream_get_contents(STDIN);
        $info = array();
        $result = p_render($renderer, p_get_instructions($source), $info);
        if(is_null($result)) throw new DokuCLI_Exception("No such renderer $renderer");
        echo $result;
    }
}

// Main
$cli = new RenderCLI();
$cli->run();
