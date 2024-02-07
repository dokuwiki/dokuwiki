<?php

namespace splitbrain\JSStrip;

/**
 * Strip comments and whitespaces from given JavaScript Code
 *
 * This is a port of Nick Galbreath's python tool jsstrip.py which is
 * released under BSD license. See link for original code.
 *
 * @author Nick Galbreath <nickg@modp.com>
 * @author Andreas Gohr <andi@splitbrain.org>
 * @link   http://code.google.com/p/jsstrip/
 */
class JSStrip
{

    const REGEX_STARTERS = [
        '(', '=', '<', '>', '?', '[', '{', ',', ';', ':', '!', '&', '|', '+', '-', '%', '~', '^',
        'return', 'yield', 'else', 'throw', 'await'
    ];
    const WHITESPACE_CHARS = [" ", "\t", "\n", "\r", "\0", "\x0B"];

    /** items that don't need spaces next to them */
    const CHARS = "^&|!+\-*\/%=\?:;,{}()<>% \t\n\r'\"`[]~^";

    /**
     * items which need a space if the sign before and after whitespace is equal.
     * E.g. '+ ++' may not be compressed to '+++' --> syntax error.
     */
    const OPS = "+-/";

    protected $source;
    protected $idx = 0;
    protected $line = 0;

    /**
     * Compress the given code
     *
     * @param string $source The JavaScript code to compress
     * @return string
     * @throws Exception if parsing fails
     */
    function compress($source)
    {
        $source = ltrim($source);     // strip all initial whitespace
        $source .= "\n";
        $idx = 0;             // char index for input string

        // track these as member variables
        $this->source = $source;
        $this->line = 1;
        $this->idx = &$idx;

        $j = 0;             // char forward index for input string
        $slen = strlen($source); // size of input string
        $lch = '';         // last char added
        $result = '';       // we store the final result here


        while ($idx < $slen) {
            // skip all "boring" characters.  This is either
            // reserved word (e.g. "for", "else", "if") or a
            // variable/object/method (e.g. "foo.color")
            while ($idx < $slen && (strpos(self::CHARS, $source[$idx]) === false)) {
                $result .= $source[$idx];
                $idx = $idx + 1;
            }

            $ch = $source[$idx];
            // multiline comments (keeping IE conditionals)
            if ($ch == '/' && $source[$idx + 1] == '*' && $source[$idx + 2] != '@') {
                $endC = strpos($source, '*/', $idx + 2);
                if ($endC === false) $this->fatal('Found invalid /*..*/ comment');

                // check if this is a NOCOMPRESS comment
                if (substr($source, $idx, $endC + 2 - $idx) == '/* BEGIN NOCOMPRESS */') {
                    // take nested NOCOMPRESS comments into account
                    $depth = 0;
                    $nextNC = $endC;
                    do {
                        $beginNC = strpos($source, '/* BEGIN NOCOMPRESS */', $nextNC + 2);
                        $endNC = strpos($source, '/* END NOCOMPRESS */', $nextNC + 2);

                        if ($endNC === false) $this->fatal('Found invalid NOCOMPRESS comment');
                        if ($beginNC !== false && $beginNC < $endNC) {
                            $depth++;
                            $nextNC = $beginNC;
                        } else {
                            $depth--;
                            $nextNC = $endNC;
                        }
                    } while ($depth >= 0);

                    // verbatim copy contents, trimming but putting it on its own line
                    $result .= "\n" . trim(substr($source, $idx + 22, $endNC - ($idx + 22))) . "\n"; // BEGIN comment = 22 chars
                    $idx = $endNC + 20; // END comment = 20 chars
                } else {
                    $idx = $endC + 2;
                }
                continue;
            }

            // singleline
            if ($ch == '/' && $source[$idx + 1] == '/') {
                $endC = strpos($source, "\n", $idx + 2);
                if ($endC === false) $this->fatal('Invalid comment'); // not sure this can happen
                $idx = $endC;
                continue;
            }

            // tricky.  might be an RE
            if ($ch == '/') {
                // rewind, skip white space
                $j = 1;
                while (in_array($source[$idx - $j], self::WHITESPACE_CHARS)) {
                    $j = $j + 1;
                }
                if (current(array_filter(
                    self::REGEX_STARTERS,
                    function ($e) use ($source, $idx, $j) {
                        $len = strlen($e);
                        $idx = $idx - $j + 1 - $len;
                        return substr($source, $idx, $len) === $e;
                    }
                ))) {
                    // yes, this is an re
                    // now move forward and find the end of it
                    $j = 1;
                    // we set this flag when inside a character class definition, enclosed by brackets [] where '/' does not terminate the re
                    $ccd = false;
                    while ($ccd || $source[$idx + $j] != '/') {
                        if ($source[$idx + $j] == '\\') $j = $j + 2;
                        else {
                            $j++;
                            // check if we entered/exited a character class definition and set flag accordingly
                            if ($source[$idx + $j - 1] == '[') $ccd = true;
                            else if ($source[$idx + $j - 1] == ']') $ccd = false;
                        }
                    }
                    $result .= substr($source, $idx, $j + 1);
                    $idx = $idx + $j + 1;
                    continue;
                }
            }

            // double quote strings
            if ($ch == '"') {
                $j = 1;
                while (($idx + $j < $slen) && $source[$idx + $j] != '"') {
                    if ($source[$idx + $j] == '\\' && ($source[$idx + $j + 1] == '"' || $source[$idx + $j + 1] == '\\')) {
                        $j += 2;
                    } else {
                        $j += 1;
                    }
                }
                $string = substr($source, $idx, $j + 1);
                // remove multiline markers:
                $string = str_replace("\\\n", '', $string);
                $result .= $string;
                $idx = $idx + $j + 1;
                continue;
            }

            // single quote strings
            if ($ch == "'") {
                $j = 1;
                while (($idx + $j < $slen) && $source[$idx + $j] != "'") {
                    if ($source[$idx + $j] == '\\' && ($source[$idx + $j + 1] == "'" || $source[$idx + $j + 1] == '\\')) {
                        $j += 2;
                    } else {
                        $j += 1;
                    }
                }
                $string = substr($source, $idx, $j + 1);
                // remove multiline markers:
                $string = str_replace("\\\n", '', $string);
                $result .= $string;
                $idx = $idx + $j + 1;
                continue;
            }

            // backtick strings
            if ($ch == "`") {
                $j = 1;
                while (($idx + $j < $slen) && $source[$idx + $j] != "`") {
                    if ($source[$idx + $j] == '\\' && ($source[$idx + $j + 1] == "`" || $source[$idx + $j + 1] == '\\')) {
                        $j += 2;
                    } else {
                        $j += 1;
                    }
                }
                $string = substr($source, $idx, $j + 1);
                // remove multiline markers:
                $string = str_replace("\\\n", '', $string);
                $result .= $string;
                $idx = $idx + $j + 1;
                continue;
            }

            // whitespaces
            if ($ch == ' ' || $ch == "\r" || $ch == "\n" || $ch == "\t") {
                $lch = substr($result, -1);
                if ($ch == "\n") $this->line++;

                // Only consider deleting whitespace if the signs before and after
                // are not equal and are not an operator which may not follow itself.
                if ($idx + 1 < $slen && ((!$lch || $source[$idx + 1] == ' ')
                        || $lch != $source[$idx + 1]
                        || strpos(self::OPS, $source[$idx + 1]) === false)) {
                    // leading spaces
                    if ($idx + 1 < $slen && (strpos(self::CHARS, $source[$idx + 1]) !== false)) {
                        $idx = $idx + 1;
                        continue;
                    }
                    // trailing spaces
                    //  if this ch is space AND the last char processed
                    //  is special, then skip the space
                    if ($lch && (strpos(self::CHARS, $lch) !== false)) {
                        $idx = $idx + 1;
                        continue;
                    }
                }

                // else after all of this convert the "whitespace" to
                // a single space.  It will get appended below
                $ch = ' ';
            }

            // other chars
            $result .= $ch;
            $idx = $idx + 1;
        }

        return trim($result);
    }

    /**
     * Helper to throw a fatal error
     *
     * Tries to give some context to locate the error
     *
     * @param string $msg
     * @throws Exception
     */
    protected function fatal($msg)
    {
        $before = substr($this->source, max(0, $this->idx - 15), $this->idx);
        $after = substr($this->source, $this->idx, 15);

        $msg = "$msg on line {$this->line}: '{$before}◎{$after}'";
        throw new Exception($msg);
    }
}
