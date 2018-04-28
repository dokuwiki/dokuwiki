<?php

use dokuwiki\ParserMode\Base;
use dokuwiki\ParserMode\ModeInterface;

/**
 * Define various types of modes used by the parser - they are used to
 * populate the list of modes another mode accepts
 */
global $PARSER_MODES;
$PARSER_MODES = array(
    // containers are complex modes that can contain many other modes
    // hr breaks the principle but they shouldn't be used in tables / lists
    // so they are put here
    'container'    => array('listblock','table','quote','hr'),

    // some mode are allowed inside the base mode only
    'baseonly'     => array('header'),

    // modes for styling text -- footnote behaves similar to styling
    'formatting'   => array('strong', 'emphasis', 'underline', 'monospace',
                            'subscript', 'superscript', 'deleted', 'footnote'),

    // modes where the token is simply replaced - they can not contain any
    // other modes
    'substition'   => array('acronym','smiley','wordblock','entity',
                            'camelcaselink', 'internallink','media',
                            'externallink','linebreak','emaillink',
                            'windowssharelink','filelink','notoc',
                            'nocache','multiplyentity','quotes','rss'),

    // modes which have a start and end token but inside which
    // no other modes should be applied
    'protected'    => array('preformatted','code','file','php','html','htmlblock','phpblock'),

    // inside this mode no wiki markup should be applied but lineendings
    // and whitespace isn't preserved
    'disabled'     => array('unformatted'),

    // used to mark paragraph boundaries
    'paragraphs'   => array('eol')
);

//-------------------------------------------------------------------

/**
 * Sets up the Lexer with modes and points it to the Handler
 * For an intro to the Lexer see: wiki:parser
 */
class Doku_Parser {

    var $Handler;

    /**
     * @var Doku_Lexer $Lexer
     */
    var $Lexer;

    var $modes = array();

    var $connected = false;

    /**
     * @param Base $BaseMode
     */
    function addBaseMode($BaseMode) {
        $this->modes['base'] = $BaseMode;
        if ( !$this->Lexer ) {
            $this->Lexer = new Doku_Lexer($this->Handler,'base', true);
        }
        $this->modes['base']->Lexer = $this->Lexer;
    }

    /**
     * PHP preserves order of associative elements
     * Mode sequence is important
     *
     * @param string $name
     * @param ModeInterface $Mode
     */
    function addMode($name, ModeInterface $Mode) {
        if ( !isset($this->modes['base']) ) {
            $this->addBaseMode(new Base());
        }
        $Mode->Lexer = $this->Lexer;
        $this->modes[$name] = $Mode;
    }

    function connectModes() {

        if ( $this->connected ) {
            return;
        }

        foreach ( array_keys($this->modes) as $mode ) {

            // Base isn't connected to anything
            if ( $mode == 'base' ) {
                continue;
            }
            $this->modes[$mode]->preConnect();

            foreach ( array_keys($this->modes) as $cm ) {

                if ( $this->modes[$cm]->accepts($mode) ) {
                    $this->modes[$mode]->connectTo($cm);
                }

            }

            $this->modes[$mode]->postConnect();
        }

        $this->connected = true;
    }

    function parse($doc) {
        if ( $this->Lexer ) {
            $this->connectModes();
            // Normalize CRs and pad doc
            $doc = "\n".str_replace("\r\n","\n",$doc)."\n";
            $this->Lexer->parse($doc);
            $this->Handler->_finalize();
            return $this->Handler->calls;
        } else {
            return false;
        }
    }

}


//Setup VIM: ex: et ts=4 :
