<?php

use dokuwiki\Utf8\Sort;

/**
 * Based on sort_with_collator.test.php.
 * @author Moisés Braga Ribeiro <moisesbr@gmail.com>
 */
class sort_without_collator_test extends DokuWikiTest {

    /**
     * Actually a wrong collation for Esperanto.
     * @return string
     */
    public function collation() {
        // this would be the correct collation
        // return 'a b c ĉ d e f g ĝ h ĥ i j ĵ k l m n o p r s ŝ t u ŭ v z';

        // this collation is WRONG in practice!
        // fallback sort doesn't recognize the Esperanto letters
        return 'a b c d e f g h i j k l m n o p r s t u v z ĉ ĝ ĥ ĵ ŝ ŭ';
    }

    /*
     * Dependency for tests that forbid "intl" extension.
     */
    public function test_no_intl_extension() {
        if (class_exists('Collator')) {
            $this->markTestSkipped('Skipping all sort tests without collator, as they forbid "intl" extension');
        }
        $this->assertTrue(true); // avoid being marked as risky for having no assertion
    }

    /**
     * Pairs for fallback intl_strcmp().
     * @return array
     */
    public function pairs() {
        return array(
            // fallback sort doesn't recognize the Esperanto letters
            array('celo',     'ĉapo'     ),
            // array('ĉokolado', 'dento'    ), // c ĉ d
            array('glacio',   'ĝirafo'   ),
            // array('ĝojo',     'haro'     ),
            array('horo',     'ĥameleono'),
            // array('ĥoro',     'iam'      ), // g ĝ h ĥ i
            array('jes',      'ĵaŭdo'    ),
            // array('ĵurnalo',  'kapo'     ), // j ĵ k
            array('seka',     'ŝako'     ),
            // array('ŝuo',      'tablo'    ), // s ŝ t
            array('urso',     'ŭaŭ'      ),
            // array('ŭo',       'vino'     ), // u ŭ v

            // all Esperanto letters are put after z (actually a wrong collation)
            array('zorio',   'ĉokolado'),
            array('ĉerizo',  'ĝojo'),
            array('ĝangalo', 'ĥoro'),
            array('ĥaoso',   'ĵurnalo'),
            array('ĵipo',    'ŝuo'),
            array('ŝafo',    'ŭo'),

            // natural sort
            array('paĝo 2',   'paĝo 10'  ),
            array('paĝo 51',  'paĝo 100' )
        );
    }

    /**
     * @depends test_no_intl_extension
     * @dataProvider pairs
     * @param $str1
     * @param $str2
     */
    public function test_intl_strcmp($str1, $str2) {
        $this->assertLessThan(0, Sort::strcmp($str1, $str2));
    }

    /**
     * @depends test_no_intl_extension
     */
    public function test_intl_sort() {
        $sorted = explode(' ', $this->collation());
        $random = explode(' ', $this->collation());
        shuffle($random);
        Sort::sort($random);
        $this->assertEquals(array_values($random), array_values($sorted));
    }

    /**
     * @depends test_no_intl_extension
     */
    public function test_intl_ksort() {
        $sorted = array_flip(explode(' ', $this->collation()));
        $random = explode(' ', $this->collation());
        shuffle($random);
        $random = array_flip($random);
        Sort::ksort($random);
        $this->assertEquals(array_keys($random), array_keys($sorted));
    }

    /**
     * @depends test_no_intl_extension
     */
    public function test_intl_asort() {
        $sorted = explode(' ', $this->collation());
        $keys = array_keys($sorted);
        shuffle($keys);
        foreach($keys as $key) $random[$key] = $sorted[$key];
        Sort::asort($random);
        $this->assertEquals(array_values($random), array_values($sorted));
        $this->assertEquals(array_keys($random), array_keys($sorted));
    }

    /**
     * @depends test_no_intl_extension
     */
    public function test_intl_asortFN_url() {
        global $conf;
        $conf['fnencode'] = 'url';

        $sorted = explode('+', urlencode($this->collation()));
        $keys = array_keys($sorted);
        shuffle($keys);
        foreach($keys as $key) $random[$key] = $sorted[$key];
        Sort::asortFN($random);
        $this->assertEquals(array_values($random), array_values($sorted));
        $this->assertEquals(array_keys($random), array_keys($sorted));
    }

    /**
     * @depends test_no_intl_extension
     */
    public function test_intl_asortFN_safe() {
        global $conf;
        $conf['fnencode'] = 'safe';

        $sorted = explode(' ', $this->collation());
        foreach(array_keys($sorted) as $key) $sorted[$key] = SafeFN::encode($sorted[$key]);
        $keys = array_keys($sorted);
        shuffle($keys);
        foreach($keys as $key) $random[$key] = $sorted[$key];
        Sort::asortFN($random);
        $this->assertEquals(array_values($random), array_values($sorted));
        $this->assertEquals(array_keys($random), array_keys($sorted));
    }

    /**
     * @depends test_no_intl_extension
     */
    public function test_intl_asortFN_utf8() {
        global $conf;
        $conf['fnencode'] = 'utf-8';

        $sorted = explode(' ', $this->collation());
        $keys = array_keys($sorted);
        shuffle($keys);
        foreach($keys as $key) $random[$key] = $sorted[$key];
        Sort::asortFN($random);
        $this->assertEquals(array_values($random), array_values($sorted));
        $this->assertEquals(array_keys($random), array_keys($sorted));
    }
}
