<?php

class init_resolve_mediaid_test extends DokuWikiTest
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
        ];
    }

    /**
     * @param $context
     * @param $page
     * @param $expect
     * @dataProvider provider
     */
    public function test($context, $page, $expect)
    {

        $resolver = new \dokuwiki\File\MediaResolver($context);
        $this->assertEquals($expect, $resolver->resolveId($page));
    }

}
//Setup VIM: ex: et ts=4 :
