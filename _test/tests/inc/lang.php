<?php

/**
 * Language file tests inspired by the script by schplurtz
 * @link https://www.dokuwiki.org/teams:i18n:translation-check
 */
class lang_test extends DokuWikiTest
{
    /**
     * returen all languages except english
     *
     * @return string[]
     */
    protected function findLanguages()
    {
        $languages = glob(DOKU_INC . 'inc/lang/*', GLOB_ONLYDIR);
        $languages = array_map('basename', $languages);
        $languages = array_filter($languages, function ($in) {
            return $in !== 'en';
        });
        return $languages;
    }

    /**
     * Get all installed plugins
     *
     * This finds all things that might be a plugin and does not care for enabled or not.
     *
     * @return string[]
     */
    protected function findPlugins()
    {
        $plugins = glob(DOKU_INC . 'lib/plugins/*', GLOB_ONLYDIR);
        return $plugins;
    }

    /**
     * Get all installed templates
     *
     * This finds all things that might be a template and does not care for enabled or not.
     *
     * @return string[]
     */
    protected function findTemplates()
    {
        $templates = glob(DOKU_INC . 'lib/tpl/*', GLOB_ONLYDIR);
        return $templates;
    }

    /**
     * Load the strings for the given language
     *
     * @param string $lang
     * @return array
     */
    protected function loadLanguage($file)
    {
        $lang = [];
        if (file_exists($file)) {
            include $file;
        }
        return $lang;
    }

    /**
     * Provide all the language files to compare
     *
     * @return Generator
     */
    public function provideLanguageFiles()
    {
        $bases = array_merge(
            [DOKU_INC . 'inc'],
            $this->findPlugins(),
            $this->findTemplates()
        );

        foreach ($this->findLanguages() as $code) {
            foreach ($bases as $base) {
                foreach (['lang.php', 'settings.php'] as $file) {
                    $englishFile = "$base/lang/en/$file";
                    $foreignFile = "$base/lang/$code/$file";
                    $name = substr($foreignFile, strlen(DOKU_INC));
                    $name = '…'.substr($name, -35);

                    if (file_exists($foreignFile)) {
                        yield ([
                            $this->loadLanguage($englishFile),
                            $this->loadLanguage($foreignFile),
                            $code,
                            $name,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Check for obsolete language strings
     *
     * @param array $english key/value language pairs for English
     * @param array $foreign key/value language pairs for the foreign language
     * @param string $code language code of the foreign file
     * @param string $file the base file name the foreign keys came from
     * @param string $prefix sub key that is currently checked (used in recursion)
     * @dataProvider provideLanguageFiles
     */
    public function testObsolete($english, $foreign, $code, $file, $prefix = '')
    {
        $this->assertGreaterThan(0, count($foreign), "$file exists but has no translations");

        foreach ($foreign as $key => $value) {
            $name = $prefix ? $prefix . $key : $key;
            $this->assertArrayHasKey($key, $english, "$file: obsolete/unknown key '$name'");

            // sub arrays as for the js translations:
            if (is_array($value) && is_array($english[$key])) {
                $this->testObsolete($english[$key], $value, $code, $file, $key);
            }
        }
    }

    /**
     * Check for sprintf format placeholder equality
     *
     * @param array $english key/value language pairs for English
     * @param array $foreign key/value language pairs for the foreign language
     * @param string $code language code of the foreign file
     * @param string $file the base file name the foreign keys came from
     * @param string $prefix sub key that is currently checked (used in recursion)
     * @dataProvider provideLanguageFiles
     */
    public function testPlaceholders($english, $foreign, $code, $file, $prefix = '')
    {
        $this->assertGreaterThan(0, count($foreign), "$file exists but has no translations");

        foreach ($foreign as $key => $value) {
            // non existing in english is skipped here, that what testObsolete checks
            if (!isset($english[$key])) continue;

            // sub arrays as for the js translations:
            if (is_array($value) && is_array($english[$key])) {
                $this->testPlaceholders($english[$key], $value, $code, $file, $key);
                return;
            }

            $name = $prefix ? $prefix . $key : $key;

            $englishPlaceholders = $this->parsePlaceholders($english[$key]);
            $foreignPlaceholders = $this->parsePlaceholders($value);
            $countEnglish = count($englishPlaceholders);
            $countForeign = count($foreignPlaceholders);

            $this->assertEquals($countEnglish, $countForeign,
                join("\n",
                    [
                        "$file: unequal amount of sprintf format placeholders in '$name'",
                        "en: '" . $english[$key] . "'",
                        "$code: '$value'",
                    ]
                )
            );

            $this->assertEquals($englishPlaceholders, $foreignPlaceholders,
                join("\n",
                    [
                        "$file: sprintf format mismatch in '$name'",
                        "en: '" . $english[$key] . "'",
                        "$code: '$value'",
                    ]
                )
            );
        }
    }

    /**
     * Parses the placeholders from a string and brings them in the correct order
     *
     * This has its own test below.
     *
     * @param string $string
     */
    protected function parsePlaceholders($string)
    {
        if (!preg_match_all('/%(?:([0-9]+)\$)?([-.0-9hl]*?[%dufsc])/', $string, $matches, PREG_SET_ORDER)) {
            return [];
        }

        // Given this string : 'schproutch %2$s with %1$04d in %-20s plouf'
        // we have this in $matches:
        // [
        //     0 => ['%2$s', 2, 's'],
        //     1 => ['%1$04d', 1, '04d'],
        //     2 => ['%-20s', '', '-20s'],
        // ]

        // sort by the given sorting in key 1
        usort($matches, function ($a, $b) {
            if ($a[1] === $b[1]) return 0; // keep as is

            // sort empties towards the back
            if ($a[1] === '') $a[1] = 9999;
            if ($b[1] === '') $b[1] = 9999;

            // compare sort numbers
            if ((int)$a[1] < (int)$b[1]) return -1;
            if ((int)$a[1] > (int)$b[1]) return 1;
            return 0;
        });

        // return values in key 2
        return array_column($matches, 2);
    }

    /**
     * Dataprovider for the parsePlaceholder test
     * @return array[]
     */
    public function providePlaceholders()
    {
        return [
            ['schproutch %2$s with %1$04d in %-20s plouf', ['04d', 's', '-20s']],
        ];
    }

    /**
     * Test the parsePlaceholder utility function above
     *
     * @param string $input
     * @param array $expected
     * @dataProvider providePlaceholders
     */
    public function testParsePlaceholders($input, $expected)
    {
        $this->assertEquals($expected, $this->parsePlaceholders($input));
    }
}
