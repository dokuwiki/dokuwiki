<?php

class init_resolve_pageid_test extends DokuWikiTest
{

    /**
     * @see test1
     */
    public function provider()
    {
        return [
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

            // strange and broken ones
            ['lev1:lev2:context', '....:....:page', 'lev1:lev2:page'],
            ['lev1:lev2:context', '..:..:lev3:page', 'lev3:page'],
            ['lev1:lev2:context', '..:..:lev3:..:page', 'page'],
            ['lev1:lev2:context', '..:..:lev3:..:page:....:...', 'page'],

            // now some tests with existing and none existing files
            ['context', '.:', 'start'],
            ['foo:context', '.:', 'foo:start'],
            ['context', 'foo:', 'foo:start'],
            ['foo:context', 'foo:', 'foo:start'],

            // empty $page
            ['my:space', '', 'my:space'],
        ];
    }

    /**
     * @param $context
     * @param $page
     * @param $expect
     * @dataProvider provider
     */
    function test1($context, $page, $expect)
    {
        global $conf;
        global $ID;
        $ID = 'my:space';
        $conf['start'] = 'start';

        $resolover = new \dokuwiki\File\PageResolver($context);
            $this->assertEquals($expect, $resolover->resolveId($page));
    }
}
