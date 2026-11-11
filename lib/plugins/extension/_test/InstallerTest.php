<?php

namespace dokuwiki\plugin\extension\test;

use dokuwiki\plugin\extension\Exception;
use dokuwiki\plugin\extension\Extension;
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
    protected $pluginsEnabled = ['extension'];

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
            'plugin3' => ['plugin3', ['foobar' => 'plugin']],
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

    /**
     * Provide data for testEnsurePhpCompatibilityMax
     *
     * @return array[] phpmax value, PHP version, expected compatibility
     */
    public function provideMaxPhpData()
    {
        return [
            'no max allows 9.0.0' => ['', '9.0.0', true],
            'max 8 allows 8.5.10' => ['8', '8.5.10', true],
            'max 8 refuses 9.0.0' => ['8', '9.0.0', false],
            'max 8.5 allows 8.5.0' => ['8.5', '8.5.0', true],
            'max 8.5 allows 8.5.10' => ['8.5', '8.5.10', true],
            'max 8.5 allows 8.5.10-1ubuntu1' => ['8.5', '8.5.10-1ubuntu1', true],
            'max 8.5 refuses 8.6.0' => ['8.5', '8.6.0', false],
            'max 8.4 refuses 8.5.0' => ['8.4', '8.5.0', false],
            'max 8.5.10 allows 8.5.8' => ['8.5.10', '8.5.8', true],
            'max 8.5.10 allows 8.5.10' => ['8.5.10', '8.5.10', true],
            'max 8.5.10 allows 8.5.10-1ubuntu1' => ['8.5.10', '8.5.10-1ubuntu1', true],
            'max 8.5.3 refuses 8.5.10' => ['8.5.3', '8.5.10', false],
            'max 8.5.3 refuses 8.6.0' => ['8.5.3', '8.6.0', false],
        ];
    }

    /**
     * A Major.Minor phpmax includes all patch releases of that version (#4812)
     *
     * @dataProvider provideMaxPhpData
     * @param string $max the phpmax value of the extension
     * @param string $php the PHP version to check against
     * @param bool $expected true if the extension is expected to be compatible
     */
    public function testEnsurePhpCompatibilityMax($max, $php, $expected)
    {
        $extension = Extension::createFromRemoteData(['plugin' => 'phpmaxtest', 'phpmax' => $max]);
        try {
            Installer::ensurePhpCompatibility($extension, $php);
            $compatible = true;
        } catch (Exception) {
            $compatible = false;
        }
        $this->assertSame($expected, $compatible);
    }
}
