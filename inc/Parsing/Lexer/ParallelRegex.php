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
    protected $patterns = [];
    /** @var string[] labels for above patterns */
    protected $labels = [];
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
     * Lists the registered patterns together with their labels, in
     * registration order.
     *
     * The label tells how the lexer treats a match: true for a plain
     * pattern consumed in place, Lexer::MODE_EXIT for an exit pattern,
     * a mode name prefixed with Lexer::MODE_SPECIAL_PREFIX for a special
     * pattern, and a bare mode name for an entry pattern into that mode.
     *
     * @return array[] list of ['pattern' => string, 'label' => bool|string]
     */
    public function getPatterns()
    {
        return array_map(
            static fn($pattern, $label) => ['pattern' => $pattern, 'label' => $label],
            $this->patterns,
            $this->labels
        );
    }

    /**
     * Attempts to split the string against all patterns at once.
     *
     * When `$offset` is non-zero, the match begins at that byte position in
     * `$subject`, but the full subject is still passed to PCRE so any
     * lookbehinds in the patterns can see characters before the offset.
     * This is essential for inline-formatting closers like
     * `(?<=[^\s])\*\*`, whose preceding non-whitespace character may have
     * been consumed as part of a previous token (e.g. a `[[link]]`).
     *
     * @param string $subject      String to match against.
     * @param array $split         The split result: array containing, pre-match, match & post-match strings
     * @param int $offset          Byte offset into `$subject` at which to start matching.
     * @return boolean             True on success.
     *
     * @author Christopher Smith <chris@jalakai.co.uk>
     */
    public function split($subject, &$split, $offset = 0)
    {
        if (count($this->patterns) == 0) {
            return false;
        }

        if (! preg_match($this->getCompoundedRegex(), $subject, $matches, PREG_OFFSET_CAPTURE, $offset)) {
            if (function_exists('preg_last_error')) {
                $err = preg_last_error();
                switch ($err) {
                    case PREG_BACKTRACK_LIMIT_ERROR:
                        msg('A PCRE backtrack error occured. Try to increase the pcre.backtrack_limit in php.ini', -1);
                        break;
                    case PREG_JIT_STACKLIMIT_ERROR:
                        msg('A PCRE JIT stacklimit error occured. Try to disable pcre.jit in php.ini', -1);
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

            $split = [substr($subject, $offset), "", ""];
            return false;
        }

        $idx = count($matches) - 2;
        $matchText = (string) $matches[0][0];
        // Byte offset from PREG_OFFSET_CAPTURE; cast makes the int type
        // obvious to static analysers that don't model the flag.
        $matchStart = (int) $matches[0][1];
        $pre = substr($subject, $offset, $matchStart - $offset);
        $post = substr($subject, $matchStart + strlen($matchText));
        $split = [$pre, $matchText, $post];

        return $this->labels[$idx] ?? true;
    }

    /**
     * Translates a pattern from the lexer convention into plain PCRE
     * syntax: bare ( and ) match literally — only the (?...) group forms
     * keep their regex meaning — and / needs no escaping despite the
     * /-delimited compound. Any fragment embedded into a /-delimited
     * regex alongside the registered patterns must go through this
     * translation to compose correctly.
     *
     * @param string $pattern pattern in the lexer convention
     * @return string plain PCRE pattern fragment
     */
    public static function escapePattern($pattern)
    {
        /*
         * decompose the input pattern into "(", "(?", ")",
         * "[...]", "[]..]", "[^]..]", "[...[:...:]..]", "\x"...
         * elements.
         */
        preg_match_all('/\\\\.|' .
                       '\(\?|' .
                       '[()]|' .
                       '\[\^?\]?(?:\\\\.|\[:[^]]*:\]|[^]\\\\])*\]|' .
                       '[^[()\\\\]+/', $pattern, $elts);

        $escaped = "";
        $level = 0;

        foreach ($elts[0] as $elt) {
            /*
             * for "(", ")" remember the nesting level, add "\"
             * only to the non-"(?" ones.
             */

            switch ($elt) {
                case '(':
                    $escaped .= '\(';
                    break;
                case ')':
                    if ($level > 0)
                        $level--; /* closing (? */
                    else $escaped .= '\\';
                    $escaped .= ')';
                    break;
                case '(?':
                    $level++;
                    $escaped .= '(?';
                    break;
                default:
                    if (str_starts_with($elt, '\\'))
                        $escaped .= $elt;
                    else $escaped .= str_replace('/', '\/', $elt);
            }
        }
        return $escaped;
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
            $groups = array_map(
                static fn($pattern) => '(' . self::escapePattern($pattern) . ')',
                $this->patterns
            );
            $this->regex = "/" . implode("|", $groups) . "/" . $this->getPerlMatchingFlags();
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
