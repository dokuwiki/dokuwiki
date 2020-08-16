<?php

use dokuwiki\Utf8\Sort;

require_once __DIR__ . '/sort_with_collator.test.php';

/**
 * Based on sort_with_collator.test.php.
 * @author Moisés Braga Ribeiro <moisesbr@gmail.com>
 * @author Andreas Gohr <andi@splitbrain.org>
 */
class sort_without_collator_test extends sort_with_collator_test
{

    /**
     * Disable the use of the intl class
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        Sort::useIntl(false);
    }

    /**
     * Reenable the intl class usage
     */
    public static function tearDownAfterClass()
    {
        Sort::useIntl(true);
        parent::tearDownAfterClass();
    }

    /**
     * Since we always use the fallback here, we do not check for
     * the availability of the "intl" extension here at all, instead this
     * test always succeeds
     */
    public function testIntlExtensionAvailability()
    {
        $this->assertTrue(true);
    }

    /** @inheritDoc */
    public function provideWordPairs()
    {
        // ADVICE: craft word pairs that show what the fallback sort can or cannot do
        static $pairs = [
            // Esperanto
            'eo' => [
                // fallback sort works for c/ĉ, but not for ĉ/d (and so on)
                ['celo', 'ĉapo'], ['glacio', 'ĝirafo'], ['horo', 'ĥameleono'],
                ['jes', 'ĵaŭdo'], ['seka', 'ŝako'], ['urso', 'ŭaŭ'],
                // fallback sort puts ĉ/ĝ/ĥ/ĵ/ŝ/ŭ after z (WRONG!)
                ['zorio', 'ĉokolado'], ['ĉerizo', 'ĝojo'], ['ĝangalo', 'ĥoro'],
                ['ĥaoso', 'ĵurnalo'], ['ĵipo', 'ŝuo'], ['ŝafo', 'ŭo'],
                // natural sort works as usual
                ['paĝo 2', 'paĝo 10'], ['paĝo 51', 'paĝo 100']
            ],
        ];

        foreach ($pairs as $lc => $list) {
            foreach ($list as $pair) {
                yield [$lc, $pair[0], $pair[1]];
            }
        }
    }

    /** @inheritDoc */
    public function provideSortedCharList()
    {
        // these collations are WRONG in practice, but the fallback sort doesn't know any better
        static $data = [
            // Esperanto
            'eo' => 'a b c d e f g h i j k l m n o p r s t u v z ĉ ĝ ĥ ĵ ŝ ŭ',
            // German
            'de' => 'a b c d e f g h i j k l m n o p q r s t u v w x y z ß ä ö ü',
        ];

        foreach ($data as $lang => $chars) {
            yield [$lang, $chars];
        }
    }
}
