<?php

use dokuwiki\Utf8\Sort;

/**
 * @author Moisés Braga Ribeiro <moisesbr@gmail.com>
 * @author Andreas Gohr <andi@splitbrain.org>
 */
class sort_with_collator_test extends DokuWikiTest
{
    /*
     * Dependency for tests that need "intl" extension.
     */
    public function testIntlExtensionAvailability()
    {
        if (!class_exists('\Collator')) {
            $this->markTestSkipped('Skipping all sort tests with collator, as they need "intl" extension');
        }
        $this->assertTrue(true); // avoid being marked as risky for having no assertion
    }

    /**
     * Provide real word pairs of the languages being tested (when possible).
     * Everything which is beyond the usual A-Z order should be checked,
     * including every character with an accent (diacritic) used in the language.
     *
     * CHECKING NON-EQUIVALENT CHARACTERS (X < Y)
     *
     * In this case, the words are always sorted according to the character pair.
     * Craft word pairs to double-check the collator, such that sort by the next
     * character yields the opposite result.
     *
     *   Esperanto example: ĉ < d
     *   ĉokolado, dento ==> ĉ < d ==> ĉokolado < dento
     *   (if ĉ < d would fail, o < e would also fail ==> collator failure)
     *
     * CHECKING EQUIVALENT CHARACTERS (X = Y)
     *
     * If the sole difference between the words is the character pair, the sort
     * will be as if X < Y. Otherwise the characters will be treated as the same.
     * Craft two word pairs to test both conditions.
     *
     *   German example: a = ä
     *   Sole diff.: Apfel, Äpfel ==> a < ä        ==> Apfel < Äpfel
     *   Otherwise:  Ämter, Arzt  ==> a = ä, m < r ==> Ämter < Arzt
     *
     * CHECKING MULTIPLE EQUIVALENT CHARACTERS (X = Y = Z = ...)
     *
     * An extension of the above case. If the sole difference between the words is
     * a character pair from the given set, the sort will be as if X < Y < Z < ...
     * Otherwise the characters will be treated as the same.
     * Craft at least one word pair to test the first case and as many as possible
     * to test the other case.
     *
     *   Portuguese example: e = é = ê
     *   Sole diff.: de, dê         ==> e < ê                  ==> de < dê
     *   Otherwise:  pé, pedra      ==> é = e, end of word < d ==> pé < pedra
     *               pêssego, peste ==> ê = e, s = s, s < t    ==> pêssego < peste
     *
     * @return Generator|array
     * @see testStrcmp
     */
    public function provideWordPairs()
    {
        static $pairs = [
            // Esperanto
            'eo' => [
                // c < ĉ < d
                ['celo', 'ĉapo'], ['ĉokolado', 'dento'],
                // g < ĝ < h < ĥ < i
                ['glacio', 'ĝirafo'], ['ĝojo', 'haro'], ['horo', 'ĥameleono'], ['ĥoro', 'iam'],
                // j < ĵ < k
                ['jes', 'ĵaŭdo'], ['ĵurnalo', 'kapo'],
                // s < ŝ < t
                ['seka', 'ŝako'], ['ŝuo', 'tablo'],
                // u < ŭ < v
                ['urso', 'ŭaŭ'], ['ŭo', 'vino'],
                // natural sort
                ['paĝo 2', 'paĝo 10'], ['paĝo 51', 'paĝo 100']
            ],

            // German
            'de' => [
                // a = ä
                ['Apfel', 'Äpfel'], ['Ämter', 'Arzt'],
                // o = ö
                ['Tochter', 'Töchter'], ['Öl', 'Orange'],
                // u = ü
                ['Mutter', 'Mütter'], ['Übersetzung', 'Uhrzeit'],
                // ß = ss
                ['weiss', 'weiß'], ['Fuchs', 'Fuß'], ['Fraß', 'Frau'],
                // natural sort
                ['Seite 2', 'Seite 10'], ['Seite 51', 'Seite 100']
            ],

            // Portuguese
            'pt' => [
                // a = á = à = â = ã
                ['a', 'à'], ['água', 'amor'], ['às', 'ato'], ['âmbar', 'arte'], ['lã', 'lata'],
                // e = é = ê
                ['de', 'dê'], ['pé', 'pedra'], ['pêssego', 'peste'],
                // i = í
                ['liquido', 'líquido'], ['índio', 'indireto'],
                // o = ó = ô = õ
                ['avó', 'avô'], ['ótimo', 'ovo'], ['ônibus', 'osso'], ['limões', 'limonada'],
                // u = ú = ü (ü appears in old texts)
                ['numero', 'número'], ['último', 'um'], ['tranqüila', 'tranquilamente'],
                // c = ç
                ['faca', 'faça'], ['taça', 'taco'],
                // natural sort
                ['página 2', 'página 10'], ['página 51', 'página 100']
            ],

            // Spanish
            'es' => [
                // n < ñ < o
                ['nube', 'ñoño'], ['ñu', 'ojo'],
                // a = á
                ['mas', 'más'], ['ácido', 'agua'],
                // e = é
                ['de', 'dé'], ['él', 'elefante'],
                // i = í
                ['mi', 'mí'], ['íntimo', 'isla'],
                // o = ó
                ['como', 'cómo'], ['óptimo', 'oreja'],
                // u = ú
                ['tu', 'tú'], ['último', 'uno'],
                // natural sort
                ['página 2', 'página 10'], ['página 51', 'página 100']
            ],
        ];

        foreach ($pairs as $lang => $list) {
            foreach ($list as $pair) {
                yield [$lang, $pair[0], $pair[1]];
            }
        }
    }

    /**
     * Provide the sorted sequences of all characters used in the languages being tested.
     * Everything which is beyond the usual A-Z order should be checked.
     *
     * CHECKING NON-EQUIVALENT CHARACTERS (X < Y)
     *
     * Add a 2nd character to double-check the collator, such that sort by the 2nd
     * character yields the opposite result.
     *
     *   Esperanto example: ĉ < d
     *   2nd character: ĉe, da ==> ĉ < d ==> ĉe < da
     *   (if ĉ < d would fail, e < a would also fail ==> collator failure)
     *
     * CHECKING EQUIVALENT CHARACTERS (X = Y = Z)
     *
     * Don't add a 2nd character, because it would break the test. The lone characters
     * will be sorted as words with a sole difference, that is, as if X < Y < Z.
     *
     *   German example: a = ä
     *   Sole difference: a, ä ==> a < ä
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
        static $lists = [
            // Esperanto
            // c < ĉ < d
            // g < ĝ < h < ĥ < i
            // j < ĵ < k
            // s < ŝ < t
            // u < ŭ < v
            'eo' => 'a b ci ĉe da e f gu ĝo hi ĥe ia ju ĵo ke l m n o p r so ŝi te us ŭo ve z',

            // German
            // a = ä
            // o = ö
            // u = ü
            // ß = ss
            'de' => 'a ä b c d e f g h i j k l m n o ö p q r s ss ß st t u ü v w x y z',

            // Portuguese
            // a = á = à = â = ã
            // e = é = ê
            // i = í
            // o = ó = ô = õ
            // u = ú = ü (ü appears in old texts)
            // c = ç
            'pt' => 'a á à â ã b c ç d e é ê f g h i í j k l m n o ó ô õ p q r s t u ú ü v w x y z',

            // Spanish
            // n < ñ < o
            // a = á
            // e = é
            // i = í
            // o = ó
            // u = ú
            'es' => 'a á b c d e é f g h i í j k l m nu ño oh óh p q r s t u ú v w x y z',
        ];

        foreach ($lists as $lang => $list) {
            yield [$lang, $list];
        }
    }

    /**
     * @depends      testIntlExtensionAvailability
     * @dataProvider provideWordPairs
     * @param string $lang
     * @param string $word1
     * @param string $word2
     */
    public function testStrcmp($lang, $word1, $word2)
    {
        global $conf;
        $conf['lang'] = $lang;

        $this->assertLessThan(0, Sort::strcmp($word1, $word2));
    }

    /**
     * @dataProvider provideSortedCharList
     * @depends      testIntlExtensionAvailability
     * @param string $lang
     * @param string $list
     */
    public function testSort($lang, $list)
    {
        global $conf;
        $conf['lang'] = $lang;

        $sorted = explode(' ', $list);
        $random = explode(' ', $list);
        shuffle($random);
        Sort::sort($random);
        $this->assertEquals(array_values($random), array_values($sorted));
    }

    /**
     * @dataProvider provideSortedCharList
     * @depends      testIntlExtensionAvailability
     * @param string $lang
     * @param string $list
     */
    public function testKSort($lang, $list)
    {
        global $conf;
        $conf['lang'] = $lang;

        $sorted = array_flip(explode(' ', $list));
        $random = explode(' ', $list);
        shuffle($random);
        $random = array_flip($random);
        Sort::ksort($random);
        $this->assertEquals(array_keys($random), array_keys($sorted));
    }

    /**
     * @dataProvider provideSortedCharList
     * @depends      testIntlExtensionAvailability
     * @param string $lang
     * @param string $list
     */
    public function testASort($lang, $list)
    {
        global $conf;
        $conf['lang'] = $lang;

        $sorted = explode(' ', $list);
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
     * @depends      testIntlExtensionAvailability
     * @param string $lang
     * @param string $list
     */
    public function testASortFnUrl($lang, $list)
    {
        global $conf;
        $conf['fnencode'] = 'url';
        $conf['lang'] = $lang;

        $sorted = explode('+', urlencode($list));
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
     * @depends      testIntlExtensionAvailability
     * @param string $lang
     * @param string $list
     */
    public function testASortFnSafe($lang, $list)
    {
        global $conf;
        $conf['fnencode'] = 'safe';
        $conf['lang'] = $lang;

        $sorted = explode(' ', $list);
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
     * @depends      testIntlExtensionAvailability
     * @param string $lang
     * @param string $list
     */
    public function testASortFnUtf8($lang, $list)
    {
        global $conf;
        $conf['fnencode'] = 'utf-8';
        $conf['lang'] = $lang;

        $sorted = explode(' ', $list);
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
