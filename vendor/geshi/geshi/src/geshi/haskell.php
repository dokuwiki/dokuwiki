<?php
/*************************************************************************************
 * haskell.php
 * ----------
 * Author: Daniel Mlot (duplode_1 at yahoo dot com dot br)
 *         Based on haskell.php by Jason Dagit (dagit@codersbase.com), which was
 *         based on ocaml.php by Flaie (fireflaie@gmail.com).
 * Copyright: (c) 2005 Flaie, Nigel McNie (http://qbnz.com/highlighter)
 * Release Version: 1.0.9.1
 * Date Started: 2014/05/12
 *
 * Haskell language file for GeSHi.
 *
 *************************************************************************************
 *
 *   This file is part of GeSHi.
 *
 *   GeSHi is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   GeSHi is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with GeSHi; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 ************************************************************************************/

$language_data = array (
    'LANG_NAME' => 'Haskell',
    'COMMENT_SINGLE' => array( 1 => '--'),
    'COMMENT_MULTI' => array('{-' => '-}'),
    'COMMENT_REGEXP' => array(
        2 => "/-->/",
        3 => "/{-(?:(?R)|.)-}/s", //Nested Comments
        ),
    'CASE_KEYWORDS' => 0,
    'QUOTEMARKS' => array('"'),
    'ESCAPE_CHAR' => '\\',
    'KEYWORDS' => array(
        /* main haskell keywords */
        1 => array(
            'as',
            'case', 'of', 'class', 'data', 'default',
            'deriving', 'do', 'forall', 'hiding', 'if', 'then',
            'else', 'import', 'infix', 'infixl', 'infixr',
            'instance', 'let', 'in', 'module', 'newtype',
            'qualified', 'type', 'where'
            ),
        /* define names of main libraries, so we can link to it */
        2 => array(
            'Foreign', 'Numeric', 'Prelude'
            ),
        /* just link to Prelude functions, cause it's the default opened library when starting Haskell */
        3 => array(
            'not', 'otherwise', 'maybe',
            'either', 'fst', 'snd', 'curry', 'uncurry',
            'compare',
            'max', 'min', 'succ', 'pred', 'toEnum', 'fromEnum',
            'enumFrom', 'enumFromThen', 'enumFromTo',
            'enumFromThenTo', 'minBound', 'maxBound',
            'negate', 'abs', 'signum',
            'fromInteger', 'toRational', 'quot', 'rem',
            'div', 'mod', 'quotRem', 'divMod', 'toInteger',
            'recip', 'fromRational', 'pi', 'exp',
            'log', 'sqrt', 'logBase', 'sin', 'cos',
            'tan', 'asin', 'acos', 'atan', 'sinh', 'cosh',
            'tanh', 'asinh', 'acosh', 'atanh',
            'properFraction', 'truncate', 'round', 'ceiling',
            'floor', 'floatRadix', 'floatDigits', 'floatRange',
            'decodeFloat', 'encodeFloat', 'exponent',
            'significand', 'scaleFloat', 'isNaN', 'isInfinite',
            'isDenomalized', 'isNegativeZero', 'isIEEE',
            'atan2', 'subtract', 'even', 'odd', 'gcd',
            'lcm', 'fromIntegral', 'realToFrac',
            'return', 'fail', 'fmap',
            'mapM', 'mapM_', 'sequence', 'sequence_',
            'id', 'const','flip',
            'until', 'asTypeOf', 'error', 'undefined',
            'seq','map','filter', 'head',
            'last', 'tail', 'init', 'null', 'length',
            'reverse', 'foldl', 'foldl1', 'foldr',
            'foldr1', 'and', 'or', 'any', 'all', 'sum',
            'product', 'concat', 'concatMap', 'maximum',
            'minimum', 'scanl', 'scanl1', 'scanr', 'scanr1',
            'iterate', 'repeat', 'cycle', 'take', 'drop',
            'splitAt', 'takeWhile', 'dropWhile', 'span',
            'break', 'elem', 'notElem', 'lookup', 'zip',
            'zip3', 'zipWith', 'zipWith3', 'unzip', 'unzip3',
            'lines', 'words', 'unlines',
            'unwords', 'showPrec', 'show', 'showList',
            'shows', 'showChar', 'showString', 'showParen',
            'readsPrec', 'readList', 'reads', 'readParen',
            'read', 'lex', 'putChar', 'putStr', 'putStrLn',
            'print', 'getChar', 'getLine', 'getContents',
            'interact', 'readFile', 'writeFile', 'appendFile',
            'readIO', 'readLn', 'ioError', 'userError', 'catch'
            ),
        /* Prelude types */
        4 => array (
            'Bool', 'Maybe', 'Either', 'Ordering',
            'Char', 'String',
            'Int', 'Integer', 'Float', 'Double', 'Rational', 'Word',
            'ShowS', 'ReadS',
            'IO', 'IOError', 'IOException'
            ),
        /* Prelude classes */
        5 => array (
            'Ord', 'Eq', 'Enum', 'Bounded',
            'Num', 'Real', 'Integral', 'Fractional',
            'Floating', 'RealFrac', 'RealFloat',
            'Semigroup', 'Monoid',
            'Monad', 'Applicative', 'Functor',
            'Foldable', 'Traversable',
            'Show', 'Read'
            )
        ),
    /* Most symbol combinations can be valid Haskell operators */
    'SYMBOLS' => array(
        '!', '@', '#', '$', '%', '&', '*', '-', '+', '=',
        '^', '~', '|', '\\', '>', '<', ':', '?', '/'
        ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => true, /* Haskell is a case sensitive language */
        2 => true,
        3 => true,
        4 => true,
        5 => true
        ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #06c; font-weight: bold;', /* nice blue */
            2 => 'color: #06c; font-weight: bold;', /* blue as well */
            3 => 'font-weight: bold;', /* make the preduled functions bold */
            4 => 'color: #cccc00; font-weight: bold;', /* give types a different bg */
            5 => 'color: maroon; font-weight: bold;' /* similarly for classes */
            ),
        'COMMENTS' => array(
            1 => 'color: #5d478b; font-style: italic;',
            2 => 'color: #339933; font-weight: bold;',
            3 => 'color: #5d478b; font-style: italic;', /* light purple */
            'MULTI' => 'color: #5d478b; font-style: italic;' /* light purple */
            ),
        'ESCAPE_CHAR' => array(
            0 => 'background-color: #3cb371; font-weight: bold;'
            ),
        'BRACKETS' => array(
            0 => 'color: green;'
            ),
        'STRINGS' => array(
            0 => 'color: #3cb371;' /* nice green */
            ),
        'NUMBERS' => array(
            0 => 'color: red;' /* pink */
            ),
        'METHODS' => array(
            1 => 'color: #060;' /* dark green */
            ),
        'REGEXPS' => array(
            ),
        'SYMBOLS' => array(
            0 => 'color: #339933; font-weight: bold;'
            ),
        'SCRIPT' => array(
            )
        ),
    'URLS' => array(
        /* some of keywords are Prelude functions */
        1 => '',
        /* link to the wanted library */
        2 => 'http://hackage.haskell.org/package/base/docs/{FNAME}.html',
        /* link to Prelude functions */
        3 => 'http://hackage.haskell.org/package/base/docs/Prelude.html#v:{FNAME}',
        /* link to Prelude types */
        4 => 'http://hackage.haskell.org/package/base/docs/Prelude.html#t:{FNAME}',
        /* link to Prelude exceptions */
        5 => 'http://hackage.haskell.org/package/base/docs/Prelude.html#t:{FNAME}'
        ),
    'OOLANG' => false,
    'OBJECT_SPLITTERS' => array(
        ),
    'REGEXPS' => array(
        ),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(
        ),
    'HIGHLIGHT_STRICT_BLOCK' => array(
        )
);
