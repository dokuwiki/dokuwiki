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
    /** @var string[] patterns to match */
    protected $patterns;
    /** @var string[] labels for above patterns */
    protected $labels;
    /** @var string the compound regex matching all patterns */
    protected $regex;
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
        $this->regex = null;
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
     */
    public function addPattern($pattern, $label = true)
    {
        $count = count($this->patterns);
        $this->patterns[$count] = $pattern;
        $this->labels[$count] = $label;
        $this->regex = null;
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
        if (count($this->patterns) == 0) {
            return false;
        }
        if (! preg_match($this->getCompoundedRegex(), $subject, $matches)) {
            $match = "";
            return false;
        }

        $match = $matches[0];
        $size = count($matches);
        // FIXME this could be made faster by storing the labels as keys in a hashmap
        for ($i = 1; $i < $size; $i++) {
            if ($matches[$i] && isset($this->labels[$i - 1])) {
                return $this->labels[$i - 1];
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
        if (count($this->patterns) == 0) {
            return false;
        }

        if (! preg_match($this->getCompoundedRegex(), $subject, $matches)) {
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
        list($pre, $post) = preg_split($this->patterns[$idx].$this->getPerlMatchingFlags(), $subject, 2);
        $split = array($pre, $matches[0], $post);

        return isset($this->labels[$idx]) ? $this->labels[$idx] : true;
    }

    /**
     * Compounds the patterns into a single
     * regular expression separated with the
     * "or" operator. Caches the regex.
     * Will automatically escape (, ) and / tokens.
     *
     * @return null|string
     */
    protected function getCompoundedRegex()
    {
        if ($this->regex == null) {
            $cnt = count($this->patterns);
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
                               '[^[()\\\\]+/', $this->patterns[$i], $elts);

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
                $this->patterns[$i] = "($pattern)";
            }
            $this->regex = "/" . implode("|", $this->patterns) . "/" . $this->getPerlMatchingFlags();
        }
        return $this->regex;
    }

    /**
     * Accessor for perl regex mode flags to use.
     * @return string       Perl regex flags.
     */
    protected function getPerlMatchingFlags()
    {
        return ($this->case ? "msS" : "msSi");
    }
}
