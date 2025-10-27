<?php

namespace dokuwiki\plugin\extension\test;

use dokuwiki\plugin\extension\Installer;
use DokuWikiTest;

/**
 * Installer tests for the extension plugin
 *
 * @group plugin_extension
 * @group plugins
 */
class InstallerTest extends DokuWikiTest
{

    /**
     * Provide data for testFindExtensions
     */
    public function provideFindExtensionsData()
    {
        return [
            'either1' => ['either1', ['either1' => 'plugin']],
            'eithersub2' => ['eithersub2', ['either2' => 'plugin']],
            'plgfoo5' => ['plgfoo5', ['plugin5' => 'plugin']],
            'plgsub3' => ['plgsub3', ['plugin3' => 'plugin']],
            'plgsub4' => ['plgsub4', ['plugin4' => 'plugin']],
            'plgsub6' => ['plgsub6', ['plugin6' => 'plugin']],
            'plugin1' => ['plugin1', ['plugin1' => 'plugin']],
            'plugin2' => ['plugin2', ['plugin2' => 'plugin']],
            'template1' => ['template1', ['template1' => 'template']],
            'tplfoo5' => ['tplfoo5', ['template5' => 'template']],
            'tplsub3' => ['tplsub3', ['template3' => 'template']],
            'tplsub4' => ['tplsub4', ['template4' => 'template']],
            'tplsub6' => ['tplsub6', ['template6' => 'template']],
            'multi' => ['multi', [
                'multi1' => 'plugin',
                'multi2' => 'plugin',
                'multi3' => 'plugin',
                'multi4' => 'template',
                'multi5' => 'template'
            ]],
        ];
    }

    /**
     * Test finding extensions in given directories
     *
     * @dataProvider provideFindExtensionsData
     * @param string $dir
     * @param array $expected
     */
    public function testFindExtensions($dir, $expected)
    {
        $installer = new Installer();
        $list = $this->callInaccessibleMethod($installer, 'findExtensions', [__DIR__ . '/testdata/' . $dir]);
        $this->assertEquals(count($expected), count($list), 'number of extensions found');

        foreach ($list as $ext) {
            $base = $ext->getBase();
            $this->assertArrayHasKey($base, $expected, 'extension found');
            $this->assertEquals($expected[$base], $ext->getType(), 'extension type');
        }
    }
}
