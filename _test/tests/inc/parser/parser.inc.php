<?php

require_once DOKU_INC . 'inc/parser/parser.php';
require_once DOKU_INC . 'inc/parser/handler.php';

abstract class TestOfDoku_Parser extends DokuWikiTest {

    var $P;
    var $H;

    function setUp() {
        parent::setUp();
        $this->P = new Doku_Parser();
        $this->H = new Doku_Handler();
        $this->P->Handler = & $this->H;
    }

    function tearDown() {
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
