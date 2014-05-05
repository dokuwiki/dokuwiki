<?php

class parserutils_set_metadata_during_rendering_test extends DokuWikiTest {
    // the id used for this test case
    private $id;
    // if the test case is currently running
    private $active = false;
    // the original plugin controller
    private $plugin_controller;

    // the actual test
    function test_p_set_metadata_during_rendering() {
        global $EVENT_HANDLER;
        $this->id = 'test:p_set_metadata_during_rendering';
        $this->active = true;

        // write the wiki page so it exists and needs to be rendered
        saveWikiText($this->id, 'Test '.time(), 'Test data setup');

        $EVENT_HANDLER->register_hook('PARSER_METADATA_RENDER', 'BEFORE', $this, 'helper_set_metadata', array('test_before_set' => 'test'));
        $EVENT_HANDLER->register_hook('PARSER_METADATA_RENDER', 'AFTER', $this, 'helper_set_metadata', array('test_after_set' => 'test'));
        $EVENT_HANDLER->register_hook('PARSER_HANDLER_DONE', 'BEFORE', $this, 'helper_inject_test_instruction');

        // Change the global plugin controller so this test can be a fake syntax plugin
        global $plugin_controller;
        $this->plugin_controller = $plugin_controller;
        $plugin_controller = $this;

        // the actual rendering, all hooks should be executed here
        $newMeta = p_get_metadata($this->id);

        // restore the plugin controller
        $plugin_controller = $this->plugin_controller;

        // assert that all three calls to p_set_metadata have been successful
        $this->assertEquals($newMeta['test_before_set'], 'test');
        $this->assertEquals($newMeta['test_after_set'], 'test');
        $this->assertEquals($newMeta['test_during_rendering'], 'test');

        // clean up
        $this->active = false;

        // make sure the saved metadata is the one that has been rendered
        $this->assertEquals($newMeta, p_get_metadata($this->id));

        saveWikiText($this->id, '', 'Test data remove');
    }

    // helper for the action plugin part of the test, tries executing p_set_metadata during rendering
    function helper_set_metadata($event, $meta) {
        if ($this->active) {
            p_set_metadata($this->id, $meta, false, true);
            $keys = array_keys($meta);
            $key = array_pop($keys);
            $this->assertTrue(is_string($meta[$key])); // ensure we really have a key
            // ensure that the metadata property hasn't been set previously
            $this->assertNotEquals($meta[$key], p_get_metadata($this->id, $key));
        }
    }

    // helper for injecting an instruction for this test case
    function helper_inject_test_instruction($event) {
        if ($this->active)
            $event->data->calls[] = array('plugin', array('parserutils_test', array()));
    }

    // fake syntax plugin rendering method that tries calling p_set_metadata during the actual rendering process
    function render($format, &$renderer, $data) {
        if ($this->active) {
            $key = 'test_during_rendering';
            p_set_metadata($this->id, array($key => 'test'), false, true);
            // ensure that the metadata property hasn't been set previously
            $this->assertNotEquals($key, p_get_metadata($this->id, $key));
        }
    }

    // wrapper function for the fake plugin controller
    function getList($type='',$all=false){
        return $this->plugin_controller->getList();
    }

    // wrapper function for the fake plugin controller, return $this for the fake syntax of this test
    function load($type,$name,$new=false,$disabled=false){
        if ($name == 'parserutils_test') {
            return $this;
        } else {
            return $this->plugin_controller->load($type, $name, $new, $disabled);
        }
    }
}

// vim:ts=4:sw=4:et:
