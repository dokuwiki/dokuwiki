<?php

/**
 * @author Moisés Braga Ribeiro <moisesbr@gmail.com>
 */
class sort_with_collator_test extends DokuWikiTest {

    private static $lang_before;

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();

        global $conf;
        self::$lang_before = $conf['lang'];
        $conf['lang'] = 'eo'; // Esperanto
        _get_collator(TRUE); // force collator re-creation
    }

    public static function tearDownAfterClass() {
        global $conf;
        $conf['lang'] = self::$lang_before;
        _get_collator(TRUE); // force collator re-creation
    }

    /**
     * Collation for Esperanto.
     * @return string
     */
    public function collation() {
        // if sort by 1st letter fails, sort by 2nd letter gives the opposite result
        return 'a b ci ĉe d e f go ĝi ho ĥa i ju ĵe k l m n o p r se ŝa t us ŭo v z';
    }

    /*
     * Dependency for tests that need "intl" extension.
     */
    public function test_intl_extension() {
        if (!class_exists('Collator')) {
            $this->markTestSkipped('Skipping all sort tests with collator, as they need "intl" extension');
        }
        $this->assertTrue(true); // avoid being marked as risky for having no assertion
    }

    /**
     * Pairs for collator-based intl_strcmp().
     * @return array
     */
    public function pairs() {
        return array(
            // if sort by 1st letter fails, sort by 2nd letter gives the opposite result
            array('celo',     'ĉapo'     ),
            array('ĉokolado', 'dento'    ), // c ĉ d
            array('glacio',   'ĝirafo'   ),
            array('ĝojo',     'haro'     ),
            array('horo',     'ĥameleono'),
            array('ĥoro',     'iam'      ), // g ĝ h ĥ i
            array('jes',      'ĵaŭdo'    ),
            array('ĵurnalo',  'kapo'     ), // j ĵ k
            array('seka',     'ŝako'     ),
            array('ŝuo',      'tablo'    ), // s ŝ t
            array('urso',     'ŭaŭ'      ),
            array('ŭo',       'vino'     ), // u ŭ v

            // natural sort
            array('paĝo 2',   'paĝo 10'  ),
            array('paĝo 51',  'paĝo 100' )
        );
    }

    /**
     * @depends test_intl_extension
     * @dataProvider pairs
     * @param $str1
     * @param $str2
     */
    public function test_intl_strcmp($str1, $str2) {
        $this->assertLessThan(0, intl_strcmp($str1, $str2));
    }

    /**
     * @depends test_intl_extension
     */
    public function test_intl_sort() {
        $sorted = explode(' ', $this->collation());
        $random = explode(' ', $this->collation());
        shuffle($random);
        intl_sort($random);
        $this->assertEquals(array_values($random), array_values($sorted));
    }

    /**
     * @depends test_intl_extension
     */
    public function test_intl_ksort() {
        $sorted = array_flip(explode(' ', $this->collation()));
        $random = explode(' ', $this->collation());
        shuffle($random);
        $random = array_flip($random);
        intl_ksort($random);
        $this->assertEquals(array_keys($random), array_keys($sorted));
    }

    /**
     * @depends test_intl_extension
     */
    public function test_intl_asort() {
        $sorted = explode(' ', $this->collation());
        $keys = array_keys($sorted);
        shuffle($keys);
        foreach($keys as $key) $random[$key] = $sorted[$key];
        intl_asort($random);
        $this->assertEquals(array_values($random), array_values($sorted));
        $this->assertEquals(array_keys($random), array_keys($sorted));
    }

    /**
     * @depends test_intl_extension
     */
    public function test_intl_asortFN_url() {
        global $conf;
        $conf['fnencode'] = 'url';

        $sorted = explode('+', urlencode($this->collation()));
        $keys = array_keys($sorted);
        shuffle($keys);
        foreach($keys as $key) $random[$key] = $sorted[$key];
        intl_asortFN($random);
        $this->assertEquals(array_values($random), array_values($sorted));
        $this->assertEquals(array_keys($random), array_keys($sorted));
    }

    /**
     * @depends test_intl_extension
     */
    public function test_intl_asortFN_safe() {
        global $conf;
        $conf['fnencode'] = 'safe';

        $sorted = explode(' ', $this->collation());
        foreach(array_keys($sorted) as $key) $sorted[$key] = SafeFN::encode($sorted[$key]);
        $keys = array_keys($sorted);
        shuffle($keys);
        foreach($keys as $key) $random[$key] = $sorted[$key];
        intl_asortFN($random);
        $this->assertEquals(array_values($random), array_values($sorted));
        $this->assertEquals(array_keys($random), array_keys($sorted));
    }

    /**
     * @depends test_intl_extension
     */
    public function test_intl_asortFN_utf8() {
        global $conf;
        $conf['fnencode'] = 'utf-8';

        $sorted = explode(' ', $this->collation());
        $keys = array_keys($sorted);
        shuffle($keys);
        foreach($keys as $key) $random[$key] = $sorted[$key];
        intl_asortFN($random);
        $this->assertEquals(array_values($random), array_values($sorted));
        $this->assertEquals(array_keys($random), array_keys($sorted));
    }
}
