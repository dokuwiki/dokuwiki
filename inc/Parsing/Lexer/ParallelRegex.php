<?php
/**
 * Lexer adapted from Simple Test: http://sourceforge.net/projects/simpletest/
 * For an intro to the Lexer see:
 * https://web.archive.org/web/20120125041816/http://www.phppatterns.com/docs/develop/simple_test_lexer_notes
 *
 * @author Marcus Baker http://www.lastcraft.com
 */

namespace dokuwiki\Parsing\Lexer;

/**
 * Compounded regular expression.
 *
 * Any of the contained patterns could match and when one does it's label is returned.
 */
class ParallelRegex
{
    /** @var string[][] patterns to match */
    protected $patterns;
    /** @var string[][] labels for above patterns */
    protected $labels;
    /** @var string[] the compound regexes matching all patterns */
    protected $regexes;
    /** @var bool case sensitive matching? */
    protected $case;

    /**
     * Constructor. Starts with no patterns.
     *
     * @param boolean $case    True for case sensitive, false
     *                         for insensitive.
     */
    public function __construct($case)
    {
        $this->case = $case;
        $this->patterns = array();
        $this->labels = array();
        $this->regexes = array();
    }

    /**
     * Adds a pattern with an optional label.
     *
     * @param mixed       $pattern Perl style regex. Must be UTF-8
     *                             encoded. If its a string, the (, )
     *                             lose their meaning unless they
     *                             form part of a lookahead or
     *                             lookbehind assertation.
     * @param bool|string $label   Label of regex to be returned
     *                             on a match. Label must be ASCII
     * @param boolean $unicode     True for Unicode-aware, false for single-byte treatment.
     */
    public function addPattern($pattern, $label = true, $unicode = false)
    {
        if (! isset($this->patterns[$unicode])) {
            $this->patterns[$unicode] = array();
            $this->labels[$unicode] = array();
        }
        $count = count($this->patterns[$unicode]);
        $this->patterns[$unicode][$count] = $pattern;
        $this->labels[$unicode][$count] = $label;
        $this->regexes[$unicode] = null;
    }

    /**
     * Attempts to match all patterns at once against a string.
     *
     * @param string $subject      String to match against.
     * @param string $match        First matched portion of
     *                             subject.
     * @return bool|string         False if no match found, label if label exists, true if not
     */
    public function match($subject, &$match)
    {
        $trySingleByte = $this->partialMatch($subject, $match, false);
        if ($trySingleByte !== false) {
            return $trySingleByte;
        }
        return $this->partialMatch($subject, $match, true);
    }

    /**
     * Attempts to match all patterns at once against a string.
     *
     * @param string $subject      String to match against.
     * @param string $match        First matched portion of
     *                             subject.
     * @param boolean $unicode     True for Unicode-aware, false for single-byte treatment.
     * @return bool|string         False if no match found, label if label exists, true if not
     */
    protected function partialMatch($subject, &$match, $unicode)
    {
        if (! isset($this->patterns[$unicode]) || count($this->patterns[$unicode]) == 0) {
            return false;
        }
        if (! preg_match($this->getCompoundedRegex($unicode), $subject, $matches)) {
            $match = "";
            return false;
        }

        $match = $matches[0];
        $size = count($matches);
        // FIXME this could be made faster by storing the labels as keys in a hashmap
        for ($i = 1; $i < $size; $i++) {
            if ($matches[$i] && isset($this->labels[$unicode][$i - 1])) {
                return $this->labels[$unicode][$i - 1];
            }
        }
        return true;
    }

    /**
     * Attempts to split the string against all patterns at once
     *
     * @param string $subject      String to match against.
     * @param array $split         The split result: array containing, pre-match, match & post-match strings
     * @return boolean             True on success.
     *
     * @author Christopher Smith <chris@jalakai.co.uk>
     */
    public function split($subject, &$split)
    {
        $trySingleByte = $this->partialSplit($subject, $split, false);
        if ($trySingleByte !== false) {
            return $trySingleByte;
        }
        return $this->partialSplit($subject, $split, true);
    }

    /**
     * Attempts to split the string against all patterns at once
     *
     * @param string $subject      String to match against.
     * @param array $split         The split result: array containing, pre-match, match & post-match strings
     * @param boolean $unicode     True for Unicode-aware, false for single-byte treatment.
     * @return boolean             True on success.
     *
     * @author Christopher Smith <chris@jalakai.co.uk>
     */
    protected function partialSplit($subject, &$split, $unicode)
    {
        if (! isset($this->patterns[$unicode]) || count($this->patterns[$unicode]) == 0) {
            return false;
        }

        if (! preg_match($this->getCompoundedRegex($unicode), $subject, $matches)) {
            if (function_exists('preg_last_error')) {
                $err = preg_last_error();
                switch ($err) {
                    case PREG_BACKTRACK_LIMIT_ERROR:
                        msg('A PCRE backtrack error occured. Try to increase the pcre.backtrack_limit in php.ini', -1);
                        break;
                    case PREG_RECURSION_LIMIT_ERROR:
                        msg('A PCRE recursion error occured. Try to increase the pcre.recursion_limit in php.ini', -1);
                        break;
                    case PREG_BAD_UTF8_ERROR:
                        msg('A PCRE UTF-8 error occured. This might be caused by a faulty plugin', -1);
                        break;
                    case PREG_INTERNAL_ERROR:
                        msg('A PCRE internal error occured. This might be caused by a faulty plugin', -1);
                        break;
                }
            }

            $split = array($subject, "", "");
            return false;
        }

        $idx = count($matches)-2;
        list($pre, $post) = preg_split($this->patterns[$unicode][$idx].$this->getPerlMatchingFlags($unicode), $subject, 2);
        $split = array($pre, $matches[0], $post);

        return isset($this->labels[$unicode][$idx]) ? $this->labels[$unicode][$idx] : true;
    }

    /**
     * Compounds the patterns into a single
     * regular expression separated with the
     * "or" operator. Caches the regex.
     * Will automatically escape (, ) and / tokens.
     *
     * @param boolean $unicode     True for Unicode-aware, false for single-byte treatment.
     * @return null|string
     */
    protected function getCompoundedRegex($unicode)
    {
        if ($this->regexes[$unicode] == null) {
            $cnt = count($this->patterns[$unicode]);
            for ($i = 0; $i < $cnt; $i++) {
                /*
                 * decompose the input pattern into "(", "(?", ")",
                 * "[...]", "[]..]", "[^]..]", "[...[:...:]..]", "\x"...
                 * elements.
                 */
                preg_match_all('/\\\\.|' .
                               '\(\?|' .
                               '[()]|' .
                               '\[\^?\]?(?:\\\\.|\[:[^]]*:\]|[^]\\\\])*\]|' .
                               '[^[()\\\\]+/', $this->patterns[$unicode][$i], $elts);

                $pattern = "";
                $level = 0;

                foreach ($elts[0] as $elt) {
                    /*
                     * for "(", ")" remember the nesting level, add "\"
                     * only to the non-"(?" ones.
                     */

                    switch ($elt) {
                        case '(':
                            $pattern .= '\(';
                            break;
                        case ')':
                            if ($level > 0)
                                $level--; /* closing (? */
                            else $pattern .= '\\';
                            $pattern .= ')';
                            break;
                        case '(?':
                            $level++;
                            $pattern .= '(?';
                            break;
                        default:
                            if (substr($elt, 0, 1) == '\\')
                                $pattern .= $elt;
                            else $pattern .= str_replace('/', '\/', $elt);
                    }
                }
                $this->patterns[$unicode][$i] = "($pattern)";
            }
            $this->regexes[$unicode] = "/" . implode("|", $this->patterns[$unicode]) . "/" . $this->getPerlMatchingFlags($unicode);
        }
        return $this->regexes[$unicode];
    }

    /**
     * Accessor for perl regex mode flags to use.
     * @param boolean $unicode     True for Unicode-aware, false for single-byte treatment.
     * @return string              Perl regex flags.
     */
    protected function getPerlMatchingFlags($unicode)
    {
        $u = ($unicode ? "u" : "");
        return ($this->case ? $u . "msS" : $u . "msSi");
    }
}
