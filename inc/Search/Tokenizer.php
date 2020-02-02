<?php

namespace dokuwiki\Search;

use dokuwiki\Extension\Event;
use dokuwiki\Utf8;

// set the minimum token length to use in the index
// (note, this doesn't apply to numeric tokens)
const MINWORDLENGTH = 2;

/**
 * DokuWuki Tokenizer class (Singleton)
 */
class Tokenizer
{
    /** @var Tokenizer */
    protected static $instance = null;

    /** @var array $Stopwords Words that tokenizer ignores */
    protected $Stopwords;

    /** @var int $MinWordLength  minimum token length */
    protected $MinWordLength;

    /**
     * Tokenizer constructor. Singleton, thus protected!
     */
    protected function __construct()
    {
        // set the minimum token length to use in the index
        // (note, this doesn't apply to numeric tokens)
        $this->MinWordLength = (defined('IDX_MINWORDLENGTH'))
            ? IDX_MINWORDLENGTH
            : MINWORDLENGTH;

        $this->Stopwords = $this->getStopwords();
    }

    /**
     * Get new or existing singleton instance of the Tokenizer
     *
     * @return Tokenizer
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Returns words that will be ignored
     *
     * @return array                list of stop words
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    public function getStopwords()
    {
        if (!isset($this->Stopwords)) {
            global $conf;
            $swFile = DOKU_INC.'inc/lang/'.$conf['lang'].'/stopwords.txt';
            if (file_exists($swFile)) {
                $this->Stopwords = file($swFile, FILE_IGNORE_NEW_LINES);
            } else {
                $this->Stopwords = array();
           }
        }
        return $this->Stopwords;
    }

    /**
     * Returns minimum word length to be used in the index
     */
    public function getMinWordLength()
    {
        return $this->MinWordLength;
    }

    /**
     * Split the text into words for fulltext search
     *
     * @triggers INDEXER_TEXT_PREPARE
     * This event allows plugins to modify the text before it gets tokenized.
     * Plugins intercepting this event should also intercept INDEX_VERSION_GET
     *
     * @param string    $text   plain text
     * @param bool      $wc     are wildcards allowed?
     * @return array            list of words in the text
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    public function getWords($text, $wc=false)
    {
        $wc = ($wc) ? '' : '\*';

        // prepare the text to be tokenized
        $event = new Event('INDEXER_TEXT_PREPARE', $text);
        if ($event->advise_before(true)) {
            if (preg_match('/[^0-9A-Za-z ]/u', $text)) {
                $text = Utf8\Asian::separateAsianWords($text);
            }
        }
        $event->advise_after();
        unset($event);

        $text = strtr($text,
                       array(
                           "\r" => ' ',
                           "\n" => ' ',
                           "\t" => ' ',
                           "\xC2\xAD" => '', //soft-hyphen
                       )
                     );
        if (preg_match('/[^0-9A-Za-z ]/u', $text)) {
            $text = Utf8\Clean::stripspecials($text, ' ', '\._\-:'.$wc);
        }

        $wordlist = explode(' ', $text);
        foreach ($wordlist as $i => $word) {
            $wordlist[$i] = (preg_match('/[^0-9A-Za-z]/u', $word)) ?
                Utf8\PhpString::strtolower($word) : strtolower($word);
        }

        foreach ($wordlist as $i => $word) {
            if ((!is_numeric($word) && strlen($word) < $this->MinWordLength)
              || array_search($word, $this->getStopwords(), true) !== false) {
                unset($wordlist[$i]);
            }
        }
        return array_values($wordlist);
    }
}
