<?php

namespace dokuwiki\Search;

use dokuwiki\Utf8\Asian;
use dokuwiki\Utf8\Clean;
use dokuwiki\Utf8\PhpString;
use dokuwiki\Extension\Event;
use dokuwiki\Utf8;

// set the minimum token length to use in the index
// (note, this doesn't apply to numeric tokens)
const MINWORDLENGTH = 2;

/**
 * DokuWiki Tokenizer class
 */
class Tokenizer
{
    /** @var array $Stopwords Words that tokenizer ignores */
    protected static array $Stopwords;

    /** @var int $MinWordLength minimum token length */
    protected static int $MinWordLength;

    /**
     * Returns words that will be ignored
     *
     * @return array  list of stop words
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    public static function getStopwords(): array
    {
        if (!isset(static::$Stopwords)) {
            global $conf;
            $swFile = DOKU_INC . 'inc/lang/' . $conf['lang'] . '/stopwords.txt';
            if (file_exists($swFile)) {
                static::$Stopwords = file($swFile, FILE_IGNORE_NEW_LINES);
            } else {
                static::$Stopwords = [];
            }
        }
        return static::$Stopwords;
    }

    /**
     * Returns minimum word length to be used in the index
     *
     * @return int
     */
    public static function getMinWordLength(): int
    {
        if (!isset(static::$MinWordLength)) {
            // set the minimum token length to use in the index
            // (note, this doesn't apply to numeric tokens)
            static::$MinWordLength = (defined('IDX_MINWORDLENGTH'))
                ? IDX_MINWORDLENGTH
                : MINWORDLENGTH;
        }
        return static::$MinWordLength;
    }

    /**
     * Split the text into words for fulltext search
     *
     * @triggers INDEXER_TEXT_PREPARE
     * This event allows plugins to modify the text before it gets tokenized.
     * Plugins intercepting this event should also intercept INDEX_VERSION_GET
     *
     * @param string $text plain text
     * @param bool $wc are wildcards allowed?
     * @return array  list of words in the text
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    public static function getWords(string $text, bool $wc = false): array
    {
        $wc = ($wc) ? '' : '\*';

        // prepare the text to be tokenized
        $event = new Event('INDEXER_TEXT_PREPARE', $text);
        if ($event->advise_before()) {
            if (preg_match('/[^0-9A-Za-z ]/u', $text)) {
                $text = Asian::separateAsianWords($text);
            }
        }
        $event->advise_after();
        unset($event);

        $text = strtr($text, [
                "\r" => ' ',
                "\n" => ' ',
                "\t" => ' ',
                "\xC2\xAD" => '', //soft-hyphen
        ]);
        if (preg_match('/[^0-9A-Za-z ]/u', $text)) {
            $text = Clean::stripspecials($text, ' ', '\._\-:' . $wc);
        }

        $wordlist = explode(' ', $text);
        foreach ($wordlist as $i => $word) {
            $wordlist[$i] = (preg_match('/[^0-9A-Za-z]/u', $word)) ?
                PhpString::strtolower($word) : strtolower($word);
        }

        foreach ($wordlist as $i => $word) {
            if (
                (!is_numeric($word) && strlen($word) < static::getMinWordLength())
                || in_array($word, static::getStopwords(), true)
            ) {
                unset($wordlist[$i]);
            }
        }
        return array_values($wordlist);
    }

    /**
     * Check if a search term meets the minimum length requirement
     *
     * Strips wildcard characters, then checks the base against the minimum
     * word length. Numeric terms are always accepted.
     *
     * @param string $term the search term, may include * wildcards
     * @return bool true if the term is valid for searching
     */
    public static function isValidSearchTerm(string $term): bool
    {
        $base = trim($term, '*');
        if ($base === '') return false;
        if (is_numeric($base)) return true;
        return static::tokenLength($base) >= static::getMinWordLength();
    }

    /**
     * Measure the length of a string
     *
     * Differs from strlen in handling of asian characters, otherwise byte lengths are used
     *
     * @param string $token
     * @return int
     * @author Tom N Harris <tnharris@whoopdedo.org>
     *
     */
    public static function tokenLength(string $token): int
    {
        $length = strlen($token);
        // If left alone, all chinese "words" will have the same lenght of 3, so the "length" of a "word" is faked
        if (preg_match_all('/[\xE2-\xEF]/', $token, $leadbytes)) {
            foreach ($leadbytes[0] as $byte) {
                $length += ord($byte) - 0xE1;
            }
        }
        return $length;
    }
}
