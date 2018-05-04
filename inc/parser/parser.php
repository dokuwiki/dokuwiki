<?php

use dokuwiki\Lexer\Lexer;
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

/**
 * Sets up the Lexer with modes and points it to the Handler
 * For an intro to the Lexer see: wiki:parser
 */
class Doku_Parser {

    /** @var Doku_Handler */
    protected $handler;

    /** @var Lexer $lexer */
    protected $lexer;

    /** @var ModeInterface[] $modes */
    protected $modes = array();

    /** @var bool mode connections may only be set up once */
    protected $connected = false;

    /**
     * Doku_Parser constructor.
     *
     * @param Doku_Handler $handler
     */
    public function __construct(Doku_Handler $handler) {
        $this->handler = $handler;
    }

    /**
     * Adds the base mode and initialized the lexer
     *
     * @param Base $BaseMode
     */
    protected function addBaseMode($BaseMode) {
        $this->modes['base'] = $BaseMode;
        if ( !$this->lexer ) {
            $this->lexer = new Lexer($this->handler, 'base', true);
        }
        $this->modes['base']->Lexer = $this->lexer;
    }

    /**
     * Add a new syntax element (mode) to the parser
     *
     * PHP preserves order of associative elements
     * Mode sequence is important
     *
     * @param string $name
     * @param ModeInterface $Mode
     */
    public function addMode($name, ModeInterface $Mode) {
        if ( !isset($this->modes['base']) ) {
            $this->addBaseMode(new Base());
        }
        $Mode->Lexer = $this->lexer; // FIXME should be done by setter
        $this->modes[$name] = $Mode;
    }

    /**
     * Connect all modes with each other
     *
     * This is the last step before actually parsing.
     */
    protected function connectModes() {

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

    /**
     * Parses wiki syntax to instructions
     *
     * @param string $doc the wiki syntax text
     * @return array instructions
     */
    public function parse($doc) {
        $this->connectModes();
        // Normalize CRs and pad doc
        $doc = "\n".str_replace("\r\n","\n",$doc)."\n";
        $this->lexer->parse($doc);
        $this->handler->_finalize();
        return $this->handler->calls;
    }

}
