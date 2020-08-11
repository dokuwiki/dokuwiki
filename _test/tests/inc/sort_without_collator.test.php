<?php

use dokuwiki\Utf8\Sort;

require_once __DIR__ . '/sort_with_collator.test.php';

/**
 * Based on sort_with_collator.test.php.
 * @author Moisés Braga Ribeiro <moisesbr@gmail.com>
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
     * Reeanble the intl class usage
     */
    public static function tearDownAfterClass()
    {
        Sort::useIntl(true);
        parent::tearDownAfterClass();
    }

    /**
     * Since we always use the fallback here, we do not check for
     * the availability of the intl extension here at all, instead this
     * test always succeeds
     */
    public function testIntlExtenstionAvailability()
    {
        $this->assertTrue(true);
    }

    /** @inheritDoc */
    public function provideWordPairs()
    {
        // these sorts are mostly wrong. but all our fallback can do
        static $pairs = [
            // Esperanto
            'eo' => [
                ['celo', 'ĉapo'],
                ['glacio', 'ĝirafo'],
                ['horo', 'ĥameleono'],
                ['jes', 'ĵaŭdo'],
                ['seka', 'ŝako'],
                ['urso', 'ŭaŭ'],
                ['zorio', 'ĉokolado'],
                ['ĉerizo', 'ĝojo'],
                ['ĝangalo', 'ĥoro'],
                ['ĥaoso', 'ĵurnalo'],
                ['ĵipo', 'ŝuo'],
                ['ŝafo', 'ŭo'],
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

    /** @inheritDoc */
    public function provideSortedCharList()
    {
        // these collations are WRONG in practice, but our fallback doesn't know any better
        static $data = [
            'eo' => 'a b c d e f g h i j k l m n o p r s t u v z ĉ ĝ ĥ ĵ ŝ ŭ', // Esperanto
            'de' => 'a b c d e f g h i j k l m n o p q r s t u v w x y z ß ä ö ü', // German
        ];

        foreach ($data as $lang => $chars) {
            yield [$lang, $chars];
        }
    }
}
