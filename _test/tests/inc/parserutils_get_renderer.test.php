<?php

class parserutils_get_renderer_test extends DokuWikiTest {

    private $plugin_controller;

    // test default behaviour / usual settings
    function test_p_get_renderer_normal() {
        global $conf;

        $old_conf = $conf;
        $conf['renderer_xhtml'] = 'xhtml';

        $this->assertInstanceOf('Doku_Renderer_xhtml', p_get_renderer('xhtml'));

        $conf = $old_conf;
    }

    // test get a renderer plugin
    function test_p_get_renderer_plugin() {
        global $conf;
        global $plugin_controller;

        $old_conf = $conf;
        $conf['renderer_xhtml'] = 'get_renderer_test';
        $this->plugin_controller = $plugin_controller;
        $plugin_controller = $this;

        $this->assertInstanceOf('renderer_plugin_test', p_get_renderer('xhtml'));

        $conf = $old_conf;
        $plugin_controller = $this->plugin_controller;
    }

    // test fallback succeeds
    function test_p_get_renderer_fallback() {
        global $conf;

        $old_conf = $conf;
        $conf['renderer_xhtml'] = 'badvalue';

        $this->assertInstanceOf('Doku_Renderer_xhtml', p_get_renderer('xhtml'));

        $conf = $old_conf;
    }

    // test fallback fails
    function test_p_get_renderer_fallback_fail() {
        global $conf;

        $old_conf = $conf;
        $conf['renderer_junk'] = 'badvalue';

        $this->assertNull(p_get_renderer('junk'));

        $conf = $old_conf;
    }

    // wrapper function for the fake plugin controller, return $this for the fake syntax of this test
    function load($type,$name,$new=false,$disabled=false){
        if ($name == 'get_renderer_test') {
            return new renderer_plugin_test();
        } else {
            return $this->plugin_controller->load($type, $name, $new, $disabled);
        }
    }
 }

require_once DOKU_INC . 'inc/parser/xhtml.php';

class renderer_plugin_test extends Doku_Renderer_xhtml {

    function canRender($format) {
      return ($format=='xhtml');
    }

}

// vim:ts=4:sw=4:et:
