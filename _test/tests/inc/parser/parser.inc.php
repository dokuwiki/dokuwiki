<?php

use easywiki\Parsing\Parser;

require_once WIKI_INC . 'inc/parser/parser.php';
require_once WIKI_INC . 'inc/parser/handler.php';
if (!defined('WIKI_PARSER_EOL')) define('WIKI_PARSER_EOL', "\n");   // add this to make handling test cases simpler

abstract class TestOfWiki_Parser extends EasyWikiTest {

    /** @var  Parser */
    protected $P;
    /** @var  Wiki_Handler */
    protected $H;

    function setUp() : void {
        parent::setUp();
        $this->H = new Wiki_Handler();
        $this->P = new Parser($this->H);
    }

    function tearDown() : void {
        unset($this->P);
        unset($this->H);
    }
}

function stripByteIndex($call) {
    unset($call[2]);
    if ($call[0] == "nest") {
      $call[1][0] = array_map('stripByteIndex',$call[1][0]);
    }
    return $call;
}
