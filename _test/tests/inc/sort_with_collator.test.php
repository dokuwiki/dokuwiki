<?php

use dokuwiki\Utf8\Sort;

/**
 * @author Moisés Braga Ribeiro <moisesbr@gmail.com>
 */
class sort_with_collator_test extends DokuWikiTest
{

    /*
     * Dependency for tests that need "intl" extension.
     */
    public function testIntlExtenstionAvailability()
    {
        if (!class_exists('Collator')) {
            $this->markTestSkipped('Skipping all sort tests with collator, as they need "intl" extension');
        }
        $this->assertTrue(true); // avoid being marked as risky for having no assertion
    }

    /**
     * @return  Generator|array
     * @see testStrcmp
     */
    public function provideWordPairs()
    {
        static $pairs = [
            // Esperanto
            'eo' => [
                // if sort by 1st letter fails, sort by 2nd letter gives the opposite result
                ['celo', 'ĉapo'],
                ['ĉokolado', 'dento'], // c ĉ d
                ['glacio', 'ĝirafo'],
                ['ĝojo', 'haro'],
                ['horo', 'ĥameleono'],
                ['ĥoro', 'iam'], // g ĝ h ĥ i
                ['jes', 'ĵaŭdo'],
                ['ĵurnalo', 'kapo'], // j ĵ k
                ['seka', 'ŝako'],
                ['ŝuo', 'tablo'], // s ŝ t
                ['urso', 'ŭaŭ'],
                ['ŭo', 'vino'], // u ŭ v

                // natural sort
                ['paĝo 2', 'paĝo 10'],
                ['paĝo 51', 'paĝo 100'],
            ],
        ];

        foreach ($pairs as $lc => $list) {
            foreach ($list as $pair) {
                yield [$lc, $pair[0], $pair[1]];
            }
        }
    }

    /**
     * Provide a list of correctly sorted "words"
     *
     * @return Generator|array
     * @see testSort
     * @see testKSort
     * @see testASort
     * @see testASortFnUrl
     * @see testASortFnSafe
     * @see testASortFnUtf8
     */
    public function provideSortedCharList()
    {
        static $data = [
            'eo' => 'a b ci ĉe d e f go ĝi ho ĥa i ju ĵe k l m n o p r se ŝa t us ŭo v z', // Esperanto
            'de' => 'a ä b c d e f g h i j k l m n o ö p q r s ß t u ü v w x y z', // German
        ];

        foreach ($data as $lang => $chars) {
            yield [$lang, $chars];
        }
    }

    /**
     * @depends      testIntlExtenstionAvailability
     * @dataProvider provideWordPairs
     * @param string $lang
     * @param string $str1
     * @param string $str2
     */
    public function testStrcmp($lang, $str1, $str2)
    {
        global $conf;
        $conf['lang'] = $lang;

        $this->assertLessThan(0, Sort::strcmp($str1, $str2));
    }

    /**
     * @dataProvider provideSortedCharList
     * @depends      testIntlExtenstionAvailability
     * @param string $lang
     * @param string $chars
     */
    public function testSort($lang, $chars)
    {
        global $conf;
        $conf['lang'] = $lang;

        $sorted = explode(' ', $chars);
        $random = explode(' ', $chars);
        shuffle($random);
        Sort::sort($random);
        $this->assertEquals(array_values($random), array_values($sorted));
    }

    /**
     * @dataProvider provideSortedCharList
     * @depends      testIntlExtenstionAvailability
     * @param string $lang
     * @param string $chars
     */
    public function testKSort($lang, $chars)
    {
        global $conf;
        $conf['lang'] = $lang;

        $sorted = array_flip(explode(' ', $chars));
        $random = explode(' ', $chars);
        shuffle($random);
        $random = array_flip($random);
        Sort::ksort($random);
        $this->assertEquals(array_keys($random), array_keys($sorted));
    }

    /**
     * @dataProvider provideSortedCharList
     * @depends      testIntlExtenstionAvailability
     * @param string $lang
     * @param string $chars
     */
    public function testASort($lang, $chars)
    {
        global $conf;
        $conf['lang'] = $lang;

        $sorted = explode(' ', $chars);
        $keys = array_keys($sorted);
        shuffle($keys);
        foreach ($keys as $key) {
            $random[$key] = $sorted[$key];
        }
        Sort::asort($random);
        $this->assertEquals(array_values($random), array_values($sorted));
        $this->assertEquals(array_keys($random), array_keys($sorted));
    }

    /**
     * @dataProvider provideSortedCharList
     * @depends      testIntlExtenstionAvailability
     * @param string $lang
     * @param string $chars
     */
    public function testASortFnUrl($lang, $chars)
    {
        global $conf;
        $conf['fnencode'] = 'url';
        $conf['lang'] = $lang;

        $sorted = explode('+', urlencode($chars));
        $keys = array_keys($sorted);
        shuffle($keys);
        foreach ($keys as $key) {
            $random[$key] = $sorted[$key];
        }
        Sort::asortFN($random);
        $this->assertEquals(array_values($random), array_values($sorted));
        $this->assertEquals(array_keys($random), array_keys($sorted));
    }

    /**
     * @dataProvider provideSortedCharList
     * @depends      testIntlExtenstionAvailability
     * @param string $lang
     * @param string $chars
     */
    public function testASortFnSafe($lang, $chars)
    {
        global $conf;
        $conf['fnencode'] = 'safe';
        $conf['lang'] = $lang;

        $sorted = explode(' ', $chars);
        foreach (array_keys($sorted) as $key) {
            $sorted[$key] = SafeFN::encode($sorted[$key]);
        }
        $keys = array_keys($sorted);
        shuffle($keys);
        foreach ($keys as $key) {
            $random[$key] = $sorted[$key];
        }
        Sort::asortFN($random);
        $this->assertEquals(array_values($random), array_values($sorted));
        $this->assertEquals(array_keys($random), array_keys($sorted));
    }

    /**
     * @dataProvider provideSortedCharList
     * @depends      testIntlExtenstionAvailability
     * @param string $lang
     * @param string $chars
     */
    public function testASortFnUtf8($lang, $chars)
    {
        global $conf;
        $conf['fnencode'] = 'utf-8';
        $conf['lang'] = $lang;

        $sorted = explode(' ', $chars);
        $keys = array_keys($sorted);
        shuffle($keys);
        foreach ($keys as $key) {
            $random[$key] = $sorted[$key];
        }
        Sort::asortFN($random);
        $this->assertEquals(array_values($random), array_values($sorted));
        $this->assertEquals(array_keys($random), array_keys($sorted));
    }
}
