<?php

namespace tests\inc\File;

use dokuwiki\File\PageResolver;

/**
 * @todo tests that make use of revisions might be wanted
 */
class PageResolverTest extends \DokuWikiTest
{
    /**
     * @return \Generator|array
     * @see testResolveID
     */
    public function provideResolveData()
    {
        $data = [
            // relative current in root
            ['context', 'page', 'page'],
            ['context', '.page', 'page'],
            ['context', '.:page', 'page'],

            // relative current in namespace
            ['lev1:lev2:context', 'page', 'lev1:lev2:page'],
            ['lev1:lev2:context', '.page', 'lev1:lev2:page'],
            ['lev1:lev2:context', '.:page', 'lev1:lev2:page'],

            // relative upper in root
            ['context', '..page', 'page'],
            ['context', '..:page', 'page'],

            // relative upper in namespace
            ['lev1:lev2:context', '..page', 'lev1:page'],
            ['lev1:lev2:context', '..:page', 'lev1:page'],
            ['lev1:lev2:context', '..:..:page', 'page'],
            ['lev1:lev2:context', '..:..:..:page', 'page'],

            // deeper nesting
            ['lev1:lev2:lev3:context', '..page', 'lev1:lev2:page'],
            ['lev1:lev2:lev3:context', '..:page', 'lev1:lev2:page'],
            ['lev1:lev2:lev3:context', '..:..page', 'lev1:page'],
            ['lev1:lev2:lev3:context', '..:..:page', 'lev1:page'],
            ['lev1:lev2:lev3:context', '..:..:..page', 'page'],
            ['lev1:lev2:lev3:context', '..:..:..:page', 'page'],
            ['lev1:lev2:lev3:context', '..:..:..:..page', 'page'],
            ['lev1:lev2:lev3:context', '..:..:..:..:page', 'page'],

            // strange and broken ones
            ['lev1:lev2:context', '....:....:page', 'lev1:lev2:page'],
            ['lev1:lev2:context', '..:..:lev3:page', 'lev3:page'],
            ['lev1:lev2:context', '..:..:lev3:..:page', 'page'],
            ['lev1:lev2:context', '..:..:lev3:..:page:....:...', 'page'],

            // relative to current page
            ['context', '~page', 'context:page'],
            ['context', '~:page', 'context:page'],
            ['lev1:lev2:context', '~page', 'lev1:lev2:context:page'],
            ['lev1:lev2:context', '~:page', 'lev1:lev2:context:page'],

            // start pages
            ['context', '.:', 'start'],
            ['foo:context', '.:', 'foo:start'],
            ['context', 'foo:', 'foo:start'],
            ['foo:context', 'foo:', 'foo:start'],
            ['context', '~foo:', 'context:foo:start'],
            ['foo:context', '~foo:', 'foo:context:foo:start'],

            // empty page links to itself
            ['context', '', 'context'],
            ['foo:context', '', 'foo:context'],
        ];

        // run each test without a hash
        foreach ($data as $row) {
            yield $row;
        }

        // run each test with a hash
        foreach ($data as $row) {
            $row[1] .= '#somehash';
            $row[2] .= '#somehash';
            yield $row;
        }
    }

    /**
     * @dataProvider provideResolveData
     * @param string $context
     * @param string $id
     * @param string $expected
     */
    public function testResolveID($context, $id, $expected)
    {
        $resolver = new PageResolver($context);
        $this->assertEquals($expected, $resolver->resolveId($id));
    }

    /**
     * Tilde start page bahaviour
     *
     * Please note that a ~ alone is the same as ~:
     */
    public function testTildeStartPage() {
        $context = 'foo:context';
        $resolver = new PageResolver($context);

        // the $context page itself does not exist
        // a link like that is usually not possible, but we fall back to standard start
        // page behaviour
        $this->assertEquals("$context:start", $resolver->resolveId('~:'));
        $this->assertEquals("$context:start", $resolver->resolveId('~'));

        // now $context has become the start page
        saveWikiText($context, 'test', 'test');
        $this->assertEquals($context, $resolver->resolveId('~:'));

        // now we have a startpage named like the namespace
        saveWikiText("$context:context", 'test', 'test');
        $this->assertEquals("$context:context", $resolver->resolveId('~:'));
        $this->assertEquals("$context:context", $resolver->resolveId('~'));

        // now we have a dedicated start page
        saveWikiText("$context:start", 'test', 'test');
        $this->assertEquals("$context:start", $resolver->resolveId('~:'));
        $this->assertEquals("$context:start", $resolver->resolveId('~'));
    }

    public function testResolveStartPage() {

        $resolver = new PageResolver('arbitrary');

        $expect = 'foo:start';
        $actual = $this->callInaccessibleMethod($resolver, 'resolveStartPage', ['foo:', false, false]);
        $this->assertEquals($expect, $actual, 'default non-existing');

        saveWikiText('foo', 'test', 'test');
        $expect = 'foo';
        $actual = $this->callInaccessibleMethod($resolver, 'resolveStartPage', ['foo:', false, false]);
        $this->assertEquals($expect, $actual, 'page like namespace outside');

        saveWikiText('foo:foo', 'test', 'test');
        $expect = 'foo:foo';
        $actual = $this->callInaccessibleMethod($resolver, 'resolveStartPage', ['foo:', false, false]);
        $this->assertEquals($expect, $actual, 'page like namespace inside');

        saveWikiText('foo:start', 'test', 'test');
        $expect = 'foo:start';
        $actual = $this->callInaccessibleMethod($resolver, 'resolveStartPage', ['foo:', false, false]);
        $this->assertEquals($expect, $actual, 'default existing');
    }

    /**
     * @return array
     * @see testResolveRelatives
     */
    public function provideResolveRelatives() {
        return [
            ['foo', 'foo'],
            ['foo:bar', 'foo:bar'],
            ['foo:..:bar', 'bar'],
            ['foo:..:..:bar', 'bar'],
            ['foo:.:bar', 'foo:bar'],
            ['foo:.:..:.:bar', 'bar'],
            ['foo:.:.:.:bar', 'foo:bar'],
            ['foo::::bar', 'foo:bar'],
            ['foo::::bar:', 'foo:bar:'],
            ['foo:bar:', 'foo:bar:'],
        ];
    }

    /**
     * @dataProvider provideResolveRelatives
     * @param string $input
     * @param string $expected
     */
    public function testResolveRelatives($input, $expected) {
        $resolver = new PageResolver('arbitrary');

        $actual = $this->callInaccessibleMethod($resolver, 'resolveRelatives', [$input]);
        $this->assertEquals($expected, $actual);
    }

    public function testAutoPlural()
    {
        $resolver = new PageResolver('arbitrary');

        $singular = 'some:page';
        $plural = 'some:pages';


        $actual = $this->callInaccessibleMethod($resolver, 'resolveAutoPlural', [$singular, '', false]);
        $this->assertEquals($singular, $actual); // no pages exist

        saveWikiText($plural, 'plural', 'plural');
        $actual = $this->callInaccessibleMethod($resolver, 'resolveAutoPlural', [$singular, '', false]);
        $this->assertEquals($plural, $actual); // plural exists

        saveWikiText($singular, 'singular', 'singular');
        $actual = $this->callInaccessibleMethod($resolver, 'resolveAutoPlural', [$singular, '', false]);
        $this->assertEquals($singular, $actual); // requested singular has preference
    }
}
