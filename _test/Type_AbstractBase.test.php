<?php

namespace plugin\struct\test;

use plugin\struct\types\Integer;

spl_autoload_register(array('action_plugin_struct_autoloader', 'autoloader'));

/**
 * Testing the Integer Type
 *
 * @group plugin_struct
 * @group plugins
 */
class Type_AbstractBase_struct_test extends \DokuWikiTest {

    protected $pluginsEnabled = array('struct', 'sqlite');


    protected $preset = array(
        'label' => array(
            'de' => 'german label',
            'zh' => 'chinese label' // always stripped
        ),
        'hint' => array(
            'en' => 'english hint',
            'de' => 'german hint',
            'zh' => 'chinese hint' // always stripped
        )
    );


    /**
     * Translation Init: empty config, no translation plugin
     */
    public function test_trans_empty_noplugin() {
        global $conf;
        $conf['lang'] = 'en';

        $type = new mock\BaseType(null, 'A Label');
        $this->assertEquals(
            array(
                'label' => array(
                    'en' => ''
                ),
                'hint' => array(
                    'en' => ''
                ),
                'visibility' => array('inpage' => true, 'ineditor' => true)
            ),
            $type->getConfig()
        );
        $this->assertEquals('A Label', $type->getTranslatedLabel());
        $this->assertEquals('', $type->getTranslatedHint());
    }

    /**
     * Translation Init: preset config, no translation plugin
     */
    public function test_trans_preset_noplugin() {
        global $conf;
        $conf['lang'] = 'en';

        $type = new mock\BaseType($this->preset, 'A Label');
        $this->assertEquals(
            array(
                'label' => array(
                    'en' => ''
                ),
                'hint' => array(
                    'en' => 'english hint'
                ),
                'visibility' => array('inpage' => true, 'ineditor' => true)
            ),
            $type->getConfig()
        );
        $this->assertEquals('A Label', $type->getTranslatedLabel());
        $this->assertEquals('english hint', $type->getTranslatedHint());
    }

    /**
     * Translation Init: empty config, translation plugin
     */
    public function test_trans_empty_plugin() {
        global $conf;
        $conf['lang'] = 'en';
        $conf['plugin']['translation']['translations'] = 'fr tr it de';


        $type = new mock\BaseType(null, 'A Label');
        $this->assertEquals(
            array(
                'label' => array(
                    'en' => '',
                    'fr' => '',
                    'tr' => '',
                    'it' => '',
                    'de' => '',
                ),
                'hint' => array(
                    'en' => '',
                    'fr' => '',
                    'tr' => '',
                    'it' => '',
                    'de' => '',
                ),
                'visibility' => array('inpage' => true, 'ineditor' => true)
            ),
            $type->getConfig()
        );
        $this->assertEquals('A Label', $type->getTranslatedLabel());
        $this->assertEquals('', $type->getTranslatedHint());
        $conf['lang'] = 'de';
        $this->assertEquals('A Label', $type->getTranslatedLabel());
        $this->assertEquals('', $type->getTranslatedHint());
        $conf['lang'] = 'zh';
        $this->assertEquals('A Label', $type->getTranslatedLabel());
        $this->assertEquals('', $type->getTranslatedHint());
        $conf['lang'] = 'en';
    }

    /**
     * Translation Init: preset config, translation plugin
     */
    public function test_trans_preset_plugin() {
        global $conf;
        $conf['lang'] = 'en';
        $conf['plugin']['translation']['translations'] = 'fr tr it de';


        $type = new mock\BaseType($this->preset, 'A Label');
        $this->assertEquals(
            array(
                'label' => array(
                    'en' => '',
                    'fr' => '',
                    'tr' => '',
                    'it' => '',
                    'de' => 'german label',
                ),
                'hint' => array(
                    'en' => 'english hint',
                    'fr' => '',
                    'tr' => '',
                    'it' => '',
                    'de' => 'german hint',
                ),
                'visibility' => array('inpage' => true, 'ineditor' => true)
            ),
            $type->getConfig()
        );
        $this->assertEquals('A Label', $type->getTranslatedLabel());
        $this->assertEquals('english hint', $type->getTranslatedHint());
        $conf['lang'] = 'de';
        $this->assertEquals('german label', $type->getTranslatedLabel());
        $this->assertEquals('german hint', $type->getTranslatedHint());
        $conf['lang'] = 'zh';
        $this->assertEquals('A Label', $type->getTranslatedLabel()); # falls back to column
        $this->assertEquals('english hint', $type->getTranslatedHint());  # falls back to english
        $conf['lang'] = 'fr';
        $this->assertEquals('A Label', $type->getTranslatedLabel()); # falls back to column
        $this->assertEquals('english hint', $type->getTranslatedHint());  # falls back to english
        $conf['lang'] = 'en';
    }


}
