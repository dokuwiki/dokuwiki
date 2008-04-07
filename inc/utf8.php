<?php
/**
 * UTF8 helper functions
 *
 * @license    LGPL (http://www.gnu.org/copyleft/lesser.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

/**
 * check for mb_string support
 */
if(!defined('UTF8_MBSTRING')){
  if(function_exists('mb_substr') && !defined('UTF8_NOMBSTRING')){
    define('UTF8_MBSTRING',1);
  }else{
    define('UTF8_MBSTRING',0);
  }
}

if(UTF8_MBSTRING){ mb_internal_encoding('UTF-8'); }


/**
 * URL-Encode a filename to allow unicodecharacters
 *
 * Slashes are not encoded
 *
 * When the second parameter is true the string will
 * be encoded only if non ASCII characters are detected -
 * This makes it safe to run it multiple times on the
 * same string (default is true)
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see    urlencode
 */
function utf8_encodeFN($file,$safe=true){
  if($safe && preg_match('#^[a-zA-Z0-9/_\-.%]+$#',$file)){
    return $file;
  }
  $file = urlencode($file);
  $file = str_replace('%2F','/',$file);
  return $file;
}

/**
 * URL-Decode a filename
 *
 * This is just a wrapper around urldecode
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see    urldecode
 */
function utf8_decodeFN($file){
  $file = urldecode($file);
  return $file;
}

/**
 * Checks if a string contains 7bit ASCII only
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function utf8_isASCII($str){
  for($i=0; $i<strlen($str); $i++){
    if(ord($str{$i}) >127) return false;
  }
  return true;
}

/**
 * Strips all highbyte chars
 *
 * Returns a pure ASCII7 string
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function utf8_strip($str){
  $ascii = '';
  for($i=0; $i<strlen($str); $i++){
    if(ord($str{$i}) <128){
      $ascii .= $str{$i};
    }
  }
  return $ascii;
}

/**
 * Tries to detect if a string is in Unicode encoding
 *
 * @author <bmorel@ssi.fr>
 * @link   http://www.php.net/manual/en/function.utf8-encode.php
 */
function utf8_check($Str) {
 for ($i=0; $i<strlen($Str); $i++) {
  $b = ord($Str[$i]);
  if ($b < 0x80) continue; # 0bbbbbbb
  elseif (($b & 0xE0) == 0xC0) $n=1; # 110bbbbb
  elseif (($b & 0xF0) == 0xE0) $n=2; # 1110bbbb
  elseif (($b & 0xF8) == 0xF0) $n=3; # 11110bbb
  elseif (($b & 0xFC) == 0xF8) $n=4; # 111110bb
  elseif (($b & 0xFE) == 0xFC) $n=5; # 1111110b
  else return false; # Does not match any model
  for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
   if ((++$i == strlen($Str)) || ((ord($Str[$i]) & 0xC0) != 0x80))
   return false;
  }
 }
 return true;
}

/**
 * Unicode aware replacement for strlen()
 *
 * utf8_decode() converts characters that are not in ISO-8859-1
 * to '?', which, for the purpose of counting, is alright - It's
 * even faster than mb_strlen.
 *
 * @author <chernyshevsky at hotmail dot com>
 * @see    strlen()
 * @see    utf8_decode()
 */
function utf8_strlen($string){
  return strlen(utf8_decode($string));
}

/**
 * UTF-8 aware alternative to substr
 *
 * Return part of a string given character offset (and optionally length)
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 * @author Chris Smith <chris@jalakai.co.uk>
 * @param string
 * @param integer number of UTF-8 characters offset (from left)
 * @param integer (optional) length in UTF-8 characters from offset
 * @return mixed string or false if failure
 */
function utf8_substr($str, $offset, $length = null) {
    if(UTF8_MBSTRING){
        if( $length === null ){
            return mb_substr($str, $offset);
        }else{
            return mb_substr($str, $offset, $length);
        }
    }

    /*
     * Notes:
     *
     * no mb string support, so we'll use pcre regex's with 'u' flag
     * pcre only supports repetitions of less than 65536, in order to accept up to MAXINT values for
     * offset and length, we'll repeat a group of 65535 characters when needed (ok, up to MAXINT-65536)
     *
     * substr documentation states false can be returned in some cases (e.g. offset > string length)
     * mb_substr never returns false, it will return an empty string instead.
     *
     * calculating the number of characters in the string is a relatively expensive operation, so
     * we only carry it out when necessary. It isn't necessary for +ve offsets and no specified length
     */

    // cast parameters to appropriate types to avoid multiple notices/warnings
    $str = (string)$str;                          // generates E_NOTICE for PHP4 objects, but not PHP5 objects
    $offset = (int)$offset;
    if (!is_null($length)) $length = (int)$length;

    // handle trivial cases
    if ($length === 0) return '';
    if ($offset < 0 && $length < 0 && $length < $offset) return '';

    $offset_pattern = '';
    $length_pattern = '';

    // normalise -ve offsets (we could use a tail anchored pattern, but they are horribly slow!)
    if ($offset < 0) {
      $strlen = strlen(utf8_decode($str));        // see notes
      $offset = $strlen + $offset;
      if ($offset < 0) $offset = 0;
    }

    // establish a pattern for offset, a non-captured group equal in length to offset
    if ($offset > 0) {
      $Ox = (int)($offset/65535);
      $Oy = $offset%65535;

      if ($Ox) $offset_pattern = '(?:.{65535}){'.$Ox.'}';
      $offset_pattern = '^(?:'.$offset_pattern.'.{'.$Oy.'})';
    } else {
      $offset_pattern = '^';                      // offset == 0; just anchor the pattern
    }

    // establish a pattern for length
    if (is_null($length)) {
      $length_pattern = '(.*)$';                  // the rest of the string
    } else {

      if (!isset($strlen)) $strlen = strlen(utf8_decode($str));    // see notes
      if ($offset > $strlen) return '';           // another trivial case

      if ($length > 0) {

        $length = min($strlen-$offset, $length);  // reduce any length that would go passed the end of the string

        $Lx = (int)($length/65535);
        $Ly = $length%65535;

        // +ve length requires ... a captured group of length characters
        if ($Lx) $length_pattern = '(?:.{65535}){'.$Lx.'}';
        $length_pattern = '('.$length_pattern.'.{'.$Ly.'})';

      } else if ($length < 0) {

        if ($length < ($offset - $strlen)) return '';

        $Lx = (int)((-$length)/65535);
        $Ly = (-$length)%65535;

        // -ve length requires ... capture everything except a group of -length characters
        //                         anchored at the tail-end of the string
        if ($Lx) $length_pattern = '(?:.{65535}){'.$Lx.'}';
        $length_pattern = '(.*)(?:'.$length_pattern.'.{'.$Ly.'})$';
      }
    }

    if (!preg_match('#'.$offset_pattern.$length_pattern.'#us',$str,$match)) return '';
    return $match[1];
}

/**
 * Unicode aware replacement for substr_replace()
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see    substr_replace()
 */
function utf8_substr_replace($string, $replacement, $start , $length=0 ){
  $ret = '';
  if($start>0) $ret .= utf8_substr($string, 0, $start);
  $ret .= $replacement;
  $ret .= utf8_substr($string, $start+$length);
  return $ret;
}

/**
 * Unicode aware replacement for ltrim()
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see    ltrim()
 * @return string
 */
function utf8_ltrim($str,$charlist=''){
  if($charlist == '') return ltrim($str);

  //quote charlist for use in a characterclass
  $charlist = preg_replace('!([\\\\\\-\\]\\[/])!','\\\${1}',$charlist);

  return preg_replace('/^['.$charlist.']+/u','',$str);
}

/**
 * Unicode aware replacement for rtrim()
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see    rtrim()
 * @return string
 */
function  utf8_rtrim($str,$charlist=''){
  if($charlist == '') return rtrim($str);

  //quote charlist for use in a characterclass
  $charlist = preg_replace('!([\\\\\\-\\]\\[/])!','\\\${1}',$charlist);

  return preg_replace('/['.$charlist.']+$/u','',$str);
}

/**
 * Unicode aware replacement for trim()
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see    trim()
 * @return string
 */
function  utf8_trim($str,$charlist='') {
  if($charlist == '') return trim($str);

  return utf8_ltrim(utf8_rtrim($str,$charlist),$charlist);
}


/**
 * This is a unicode aware replacement for strtolower()
 *
 * Uses mb_string extension if available
 *
 * @author Leo Feyer <leo@typolight.org>
 * @see    strtolower()
 * @see    utf8_strtoupper()
 */
function utf8_strtolower($string){
  if(UTF8_MBSTRING) return mb_strtolower($string,'utf-8');

  global $UTF8_UPPER_TO_LOWER;
  return strtr($string,$UTF8_UPPER_TO_LOWER);
}

/**
 * This is a unicode aware replacement for strtoupper()
 *
 * Uses mb_string extension if available
 *
 * @author Leo Feyer <leo@typolight.org>
 * @see    strtoupper()
 * @see    utf8_strtoupper()
 */
function utf8_strtoupper($string){
  if(UTF8_MBSTRING) return mb_strtoupper($string,'utf-8');

  global $UTF8_LOWER_TO_UPPER;
  return strtr($string,$UTF8_LOWER_TO_UPPER);
}

/**
 * Replace accented UTF-8 characters by unaccented ASCII-7 equivalents
 *
 * Use the optional parameter to just deaccent lower ($case = -1) or upper ($case = 1)
 * letters. Default is to deaccent both cases ($case = 0)
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function utf8_deaccent($string,$case=0){
  if($case <= 0){
    global $UTF8_LOWER_ACCENTS;
    $string = strtr($string,$UTF8_LOWER_ACCENTS);
  }
  if($case >= 0){
    global $UTF8_UPPER_ACCENTS;
    $string = strtr($string,$UTF8_UPPER_ACCENTS);
  }
  return $string;
}

/**
 * Romanize a non-latin string
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function utf8_romanize($string){
  if(utf8_isASCII($string)) return $string; //nothing to do

  global $UTF8_ROMANIZATION;
  return strtr($string,$UTF8_ROMANIZATION);
}

/**
 * Removes special characters (nonalphanumeric) from a UTF-8 string
 *
 * This function adds the controlchars 0x00 to 0x19 to the array of
 * stripped chars (they are not included in $UTF8_SPECIAL_CHARS)
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @param  string $string     The UTF8 string to strip of special chars
 * @param  string $repl       Replace special with this string
 * @param  string $additional Additional chars to strip (used in regexp char class)
 */
function utf8_stripspecials($string,$repl='',$additional=''){
  global $UTF8_SPECIAL_CHARS;
  global $UTF8_SPECIAL_CHARS2;

  static $specials = null;
  if(is_null($specials)){
#    $specials = preg_quote(unicode_to_utf8($UTF8_SPECIAL_CHARS), '/');
    $specials = preg_quote($UTF8_SPECIAL_CHARS2, '/');
  }

  return preg_replace('/['.$additional.'\x00-\x19'.$specials.']/u',$repl,$string);
}

/**
 * This is an Unicode aware replacement for strpos
 *
 * @author Leo Feyer <leo@typolight.org>
 * @see    strpos()
 * @param  string
 * @param  string
 * @param  integer
 * @return integer
 */
function utf8_strpos($haystack, $needle, $offset=0){
    $comp = 0;
    $length = null;

    while (is_null($length) || $length < $offset) {
        $pos = strpos($haystack, $needle, $offset + $comp);

        if ($pos === false)
            return false;

        $length = utf8_strlen(substr($haystack, 0, $pos));

        if ($length < $offset)
            $comp = $pos - $length;
    }

    return $length;
}


/**
 * Encodes UTF-8 characters to HTML entities
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 * @author <vpribish at shopping dot com>
 * @link   http://www.php.net/manual/en/function.utf8-decode.php
 */
function utf8_tohtml ($str) {
    $ret = '';
    foreach (utf8_to_unicode($str) as $cp) {
        if ($cp < 0x80)
            $ret .= chr($cp);
        elseif ($cp < 0x100)
            $ret .= "&#$cp;";
        else
            $ret .= '&#x'.dechex($cp).';';
    }
    return $ret;
}

/**
 * Decodes HTML entities to UTF-8 characters
 *
 * Convert any &#..; entity to a codepoint,
 * The entities flag defaults to only decoding numeric entities.
 * Pass HTML_ENTITIES and named entities, including &amp; &lt; etc.
 * are handled as well. Avoids the problem that would occur if you
 * had to decode "&amp;#38;&#38;amp;#38;"
 *
 * unhtmlspecialchars(utf8_unhtml($s)) -> "&#38;&#38;"
 * utf8_unhtml(unhtmlspecialchars($s)) -> "&&amp#38;"
 * what it should be                   -> "&#38;&amp#38;"
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 * @param  string  $str      UTF-8 encoded string
 * @param  boolean $entities Flag controlling decoding of named entities.
 * @return UTF-8 encoded string with numeric (and named) entities replaced.
 */
function utf8_unhtml($str, $entities=null) {
    static $decoder = null;
    if (is_null($decoder))
      $decoder = new utf8_entity_decoder();
    if (is_null($entities))
        return preg_replace_callback('/(&#([Xx])?([0-9A-Za-z]+);)/m',
                                     'utf8_decode_numeric', $str);
    else
        return preg_replace_callback('/&(#)?([Xx])?([0-9A-Za-z]+);/m',
                                     array(&$decoder, 'decode'), $str);
}
function utf8_decode_numeric($ent) {
    switch ($ent[2]) {
      case 'X':
      case 'x':
          $cp = hexdec($ent[3]);
          break;
      default:
          $cp = intval($ent[3]);
          break;
    }
    return unicode_to_utf8(array($cp));
}
class utf8_entity_decoder {
    var $table;
    function utf8_entity_decoder() {
        $table = get_html_translation_table(HTML_ENTITIES);
        $table = array_flip($table);
        $this->table = array_map(array(&$this,'makeutf8'), $table);
    }
    function makeutf8($c) {
        return unicode_to_utf8(array(ord($c)));
    }
    function decode($ent) {
        if ($ent[1] == '#') {
            return utf8_decode_numeric($ent);
        } elseif (array_key_exists($ent[0],$this->table)) {
            return $this->table[$ent[0]];
        } else {
            return $ent[0];
        }
    }
}

/**
 * Takes an UTF-8 string and returns an array of ints representing the
 * Unicode characters. Astral planes are supported ie. the ints in the
 * output can be > 0xFFFF. Occurrances of the BOM are ignored. Surrogates
 * are not allowed.
 *
 * If $strict is set to true the function returns false if the input
 * string isn't a valid UTF-8 octet sequence and raises a PHP error at
 * level E_USER_WARNING
 *
 * Note: this function has been modified slightly in this library to
 * trigger errors on encountering bad bytes
 *
 * @author <hsivonen@iki.fi>
 * @author Harry Fuecks <hfuecks@gmail.com>
 * @param  string  UTF-8 encoded string
 * @param  boolean Check for invalid sequences?
 * @return mixed array of unicode code points or false if UTF-8 invalid
 * @see    unicode_to_utf8
 * @link   http://hsivonen.iki.fi/php-utf8/
 * @link   http://sourceforge.net/projects/phputf8/
 */
function utf8_to_unicode($str,$strict=false) {
    $mState = 0;     // cached expected number of octets after the current octet
                     // until the beginning of the next UTF8 character sequence
    $mUcs4  = 0;     // cached Unicode character
    $mBytes = 1;     // cached expected number of octets in the current sequence

    $out = array();

    $len = strlen($str);

    for($i = 0; $i < $len; $i++) {

        $in = ord($str{$i});

        if ( $mState == 0) {

            // When mState is zero we expect either a US-ASCII character or a
            // multi-octet sequence.
            if (0 == (0x80 & ($in))) {
                // US-ASCII, pass straight through.
                $out[] = $in;
                $mBytes = 1;

            } else if (0xC0 == (0xE0 & ($in))) {
                // First octet of 2 octet sequence
                $mUcs4 = ($in);
                $mUcs4 = ($mUcs4 & 0x1F) << 6;
                $mState = 1;
                $mBytes = 2;

            } else if (0xE0 == (0xF0 & ($in))) {
                // First octet of 3 octet sequence
                $mUcs4 = ($in);
                $mUcs4 = ($mUcs4 & 0x0F) << 12;
                $mState = 2;
                $mBytes = 3;

            } else if (0xF0 == (0xF8 & ($in))) {
                // First octet of 4 octet sequence
                $mUcs4 = ($in);
                $mUcs4 = ($mUcs4 & 0x07) << 18;
                $mState = 3;
                $mBytes = 4;

            } else if (0xF8 == (0xFC & ($in))) {
                /* First octet of 5 octet sequence.
                 *
                 * This is illegal because the encoded codepoint must be either
                 * (a) not the shortest form or
                 * (b) outside the Unicode range of 0-0x10FFFF.
                 * Rather than trying to resynchronize, we will carry on until the end
                 * of the sequence and let the later error handling code catch it.
                 */
                $mUcs4 = ($in);
                $mUcs4 = ($mUcs4 & 0x03) << 24;
                $mState = 4;
                $mBytes = 5;

            } else if (0xFC == (0xFE & ($in))) {
                // First octet of 6 octet sequence, see comments for 5 octet sequence.
                $mUcs4 = ($in);
                $mUcs4 = ($mUcs4 & 1) << 30;
                $mState = 5;
                $mBytes = 6;

            } elseif($strict) {
                /* Current octet is neither in the US-ASCII range nor a legal first
                 * octet of a multi-octet sequence.
                 */
                trigger_error(
                        'utf8_to_unicode: Illegal sequence identifier '.
                            'in UTF-8 at byte '.$i,
                        E_USER_WARNING
                    );
                return false;

            }

        } else {

            // When mState is non-zero, we expect a continuation of the multi-octet
            // sequence
            if (0x80 == (0xC0 & ($in))) {

                // Legal continuation.
                $shift = ($mState - 1) * 6;
                $tmp = $in;
                $tmp = ($tmp & 0x0000003F) << $shift;
                $mUcs4 |= $tmp;

                /**
                 * End of the multi-octet sequence. mUcs4 now contains the final
                 * Unicode codepoint to be output
                 */
                if (0 == --$mState) {

                    /*
                     * Check for illegal sequences and codepoints.
                     */
                    // From Unicode 3.1, non-shortest form is illegal
                    if (((2 == $mBytes) && ($mUcs4 < 0x0080)) ||
                        ((3 == $mBytes) && ($mUcs4 < 0x0800)) ||
                        ((4 == $mBytes) && ($mUcs4 < 0x10000)) ||
                        (4 < $mBytes) ||
                        // From Unicode 3.2, surrogate characters are illegal
                        (($mUcs4 & 0xFFFFF800) == 0xD800) ||
                        // Codepoints outside the Unicode range are illegal
                        ($mUcs4 > 0x10FFFF)) {

                        if($strict){
                            trigger_error(
                                    'utf8_to_unicode: Illegal sequence or codepoint '.
                                        'in UTF-8 at byte '.$i,
                                    E_USER_WARNING
                                );

                            return false;
                        }

                    }

                    if (0xFEFF != $mUcs4) {
                        // BOM is legal but we don't want to output it
                        $out[] = $mUcs4;
                    }

                    //initialize UTF8 cache
                    $mState = 0;
                    $mUcs4  = 0;
                    $mBytes = 1;
                }

            } elseif($strict) {
                /**
                 *((0xC0 & (*in) != 0x80) && (mState != 0))
                 * Incomplete multi-octet sequence.
                 */
                trigger_error(
                        'utf8_to_unicode: Incomplete multi-octet '.
                        '   sequence in UTF-8 at byte '.$i,
                        E_USER_WARNING
                    );

                return false;
            }
        }
    }
    return $out;
}

/**
 * Takes an array of ints representing the Unicode characters and returns
 * a UTF-8 string. Astral planes are supported ie. the ints in the
 * input can be > 0xFFFF. Occurrances of the BOM are ignored. Surrogates
 * are not allowed.
 *
 * If $strict is set to true the function returns false if the input
 * array contains ints that represent surrogates or are outside the
 * Unicode range and raises a PHP error at level E_USER_WARNING
 *
 * Note: this function has been modified slightly in this library to use
 * output buffering to concatenate the UTF-8 string (faster) as well as
 * reference the array by it's keys
 *
 * @param  array of unicode code points representing a string
 * @param  boolean Check for invalid sequences?
 * @return mixed UTF-8 string or false if array contains invalid code points
 * @author <hsivonen@iki.fi>
 * @author Harry Fuecks <hfuecks@gmail.com>
 * @see    utf8_to_unicode
 * @link   http://hsivonen.iki.fi/php-utf8/
 * @link   http://sourceforge.net/projects/phputf8/
 */
function unicode_to_utf8($arr,$strict=false) {
    if (!is_array($arr)) return '';
    ob_start();

    foreach (array_keys($arr) as $k) {

        # ASCII range (including control chars)
        if ( ($arr[$k] >= 0) && ($arr[$k] <= 0x007f) ) {

            echo chr($arr[$k]);

        # 2 byte sequence
        } else if ($arr[$k] <= 0x07ff) {

            echo chr(0xc0 | ($arr[$k] >> 6));
            echo chr(0x80 | ($arr[$k] & 0x003f));

        # Byte order mark (skip)
        } else if($arr[$k] == 0xFEFF) {

            // nop -- zap the BOM

        # Test for illegal surrogates
        } else if ($arr[$k] >= 0xD800 && $arr[$k] <= 0xDFFF) {

            // found a surrogate
            if($strict){
                trigger_error(
                    'unicode_to_utf8: Illegal surrogate '.
                        'at index: '.$k.', value: '.$arr[$k],
                    E_USER_WARNING
                    );
                return false;
            }

        # 3 byte sequence
        } else if ($arr[$k] <= 0xffff) {

            echo chr(0xe0 | ($arr[$k] >> 12));
            echo chr(0x80 | (($arr[$k] >> 6) & 0x003f));
            echo chr(0x80 | ($arr[$k] & 0x003f));

        # 4 byte sequence
        } else if ($arr[$k] <= 0x10ffff) {

            echo chr(0xf0 | ($arr[$k] >> 18));
            echo chr(0x80 | (($arr[$k] >> 12) & 0x3f));
            echo chr(0x80 | (($arr[$k] >> 6) & 0x3f));
            echo chr(0x80 | ($arr[$k] & 0x3f));

        } elseif($strict) {

            trigger_error(
                'unicode_to_utf8: Codepoint out of Unicode range '.
                    'at index: '.$k.', value: '.$arr[$k],
                E_USER_WARNING
                );

            // out of range
            return false;
        }
    }

    $result = ob_get_contents();
    ob_end_clean();
    return $result;
}

/**
 * UTF-8 to UTF-16BE conversion.
 *
 * Maybe really UCS-2 without mb_string due to utf8_to_unicode limits
 */
function utf8_to_utf16be(&$str, $bom = false) {
  $out = $bom ? "\xFE\xFF" : '';
  if(UTF8_MBSTRING) return $out.mb_convert_encoding($str,'UTF-16BE','UTF-8');

  $uni = utf8_to_unicode($str);
  foreach($uni as $cp){
    $out .= pack('n',$cp);
  }
  return $out;
}

/**
 * UTF-8 to UTF-16BE conversion.
 *
 * Maybe really UCS-2 without mb_string due to utf8_to_unicode limits
 */
function utf16be_to_utf8(&$str) {
  $uni = unpack('n*',$str);
  return unicode_to_utf8($uni);
}

/**
 * Replace bad bytes with an alternative character
 *
 * ASCII character is recommended for replacement char
 *
 * PCRE Pattern to locate bad bytes in a UTF-8 string
 * Comes from W3 FAQ: Multilingual Forms
 * Note: modified to include full ASCII range including control chars
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 * @see http://www.w3.org/International/questions/qa-forms-utf-8
 * @param string to search
 * @param string to replace bad bytes with (defaults to '?') - use ASCII
 * @return string
 */
function utf8_bad_replace($str, $replace = '') {
    $UTF8_BAD =
     '([\x00-\x7F]'.                          # ASCII (including control chars)
     '|[\xC2-\xDF][\x80-\xBF]'.               # non-overlong 2-byte
     '|\xE0[\xA0-\xBF][\x80-\xBF]'.           # excluding overlongs
     '|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}'.    # straight 3-byte
     '|\xED[\x80-\x9F][\x80-\xBF]'.           # excluding surrogates
     '|\xF0[\x90-\xBF][\x80-\xBF]{2}'.        # planes 1-3
     '|[\xF1-\xF3][\x80-\xBF]{3}'.            # planes 4-15
     '|\xF4[\x80-\x8F][\x80-\xBF]{2}'.        # plane 16
     '|(.{1}))';                              # invalid byte
    ob_start();
    while (preg_match('/'.$UTF8_BAD.'/S', $str, $matches)) {
        if ( !isset($matches[2])) {
            echo $matches[0];
        } else {
            echo $replace;
        }
        $str = substr($str,strlen($matches[0]));
    }
    $result = ob_get_contents();
    ob_end_clean();
    return $result;
}

/**
 * adjust a byte index into a utf8 string to a utf8 character boundary
 *
 * @param $str   string   utf8 character string
 * @param $i     int      byte index into $str
 * @param $next  bool     direction to search for boundary,
 *                           false = up (current character)
 *                           true = down (next character)
 *
 * @return int            byte index into $str now pointing to a utf8 character boundary
 *
 * @author       chris smith <chris@jalakai.co.uk>
 */
function utf8_correctIdx(&$str,$i,$next=false) {

  if ($i <= 0) return 0;

  $limit = strlen($str);
  if ($i>=$limit) return $limit;

  if ($next) {
    while (($i<$limit) && ((ord($str[$i]) & 0xC0) == 0x80)) $i++;
  } else {
    while ($i && ((ord($str[$i]) & 0xC0) == 0x80)) $i--;
  }

  return $i;
}

// only needed if no mb_string available
if(!UTF8_MBSTRING){
  /**
   * UTF-8 Case lookup table
   *
   * This lookuptable defines the upper case letters to their correspponding
   * lower case letter in UTF-8
   *
   * @author Andreas Gohr <andi@splitbrain.org>
   */
  global $UTF8_LOWER_TO_UPPER;
  $UTF8_LOWER_TO_UPPER = array(
    "ｚ"=>"Ｚ","ｙ"=>"Ｙ","ｘ"=>"Ｘ","ｗ"=>"Ｗ","ｖ"=>"Ｖ","ｕ"=>"Ｕ","ｔ"=>"Ｔ","ｓ"=>"Ｓ","ｒ"=>"Ｒ","ｑ"=>"Ｑ",
    "ｐ"=>"Ｐ","ｏ"=>"Ｏ","ｎ"=>"Ｎ","ｍ"=>"Ｍ","ｌ"=>"Ｌ","ｋ"=>"Ｋ","ｊ"=>"Ｊ","ｉ"=>"Ｉ","ｈ"=>"Ｈ","ｇ"=>"Ｇ",
    "ｆ"=>"Ｆ","ｅ"=>"Ｅ","ｄ"=>"Ｄ","ｃ"=>"Ｃ","ｂ"=>"Ｂ","ａ"=>"Ａ","ῳ"=>"ῼ","ῥ"=>"Ῥ","ῡ"=>"Ῡ","ῑ"=>"Ῑ",
    "ῐ"=>"Ῐ","ῃ"=>"ῌ","ι"=>"Ι","ᾳ"=>"ᾼ","ᾱ"=>"Ᾱ","ᾰ"=>"Ᾰ","ᾧ"=>"ᾯ","ᾦ"=>"ᾮ","ᾥ"=>"ᾭ","ᾤ"=>"ᾬ",
    "ᾣ"=>"ᾫ","ᾢ"=>"ᾪ","ᾡ"=>"ᾩ","ᾗ"=>"ᾟ","ᾖ"=>"ᾞ","ᾕ"=>"ᾝ","ᾔ"=>"ᾜ","ᾓ"=>"ᾛ","ᾒ"=>"ᾚ","ᾑ"=>"ᾙ",
    "ᾐ"=>"ᾘ","ᾇ"=>"ᾏ","ᾆ"=>"ᾎ","ᾅ"=>"ᾍ","ᾄ"=>"ᾌ","ᾃ"=>"ᾋ","ᾂ"=>"ᾊ","ᾁ"=>"ᾉ","ᾀ"=>"ᾈ","ώ"=>"Ώ",
    "ὼ"=>"Ὼ","ύ"=>"Ύ","ὺ"=>"Ὺ","ό"=>"Ό","ὸ"=>"Ὸ","ί"=>"Ί","ὶ"=>"Ὶ","ή"=>"Ή","ὴ"=>"Ὴ","έ"=>"Έ",
    "ὲ"=>"Ὲ","ά"=>"Ά","ὰ"=>"Ὰ","ὧ"=>"Ὧ","ὦ"=>"Ὦ","ὥ"=>"Ὥ","ὤ"=>"Ὤ","ὣ"=>"Ὣ","ὢ"=>"Ὢ","ὡ"=>"Ὡ",
    "ὗ"=>"Ὗ","ὕ"=>"Ὕ","ὓ"=>"Ὓ","ὑ"=>"Ὑ","ὅ"=>"Ὅ","ὄ"=>"Ὄ","ὃ"=>"Ὃ","ὂ"=>"Ὂ","ὁ"=>"Ὁ","ὀ"=>"Ὀ",
    "ἷ"=>"Ἷ","ἶ"=>"Ἶ","ἵ"=>"Ἵ","ἴ"=>"Ἴ","ἳ"=>"Ἳ","ἲ"=>"Ἲ","ἱ"=>"Ἱ","ἰ"=>"Ἰ","ἧ"=>"Ἧ","ἦ"=>"Ἦ",
    "ἥ"=>"Ἥ","ἤ"=>"Ἤ","ἣ"=>"Ἣ","ἢ"=>"Ἢ","ἡ"=>"Ἡ","ἕ"=>"Ἕ","ἔ"=>"Ἔ","ἓ"=>"Ἓ","ἒ"=>"Ἒ","ἑ"=>"Ἑ",
    "ἐ"=>"Ἐ","ἇ"=>"Ἇ","ἆ"=>"Ἆ","ἅ"=>"Ἅ","ἄ"=>"Ἄ","ἃ"=>"Ἃ","ἂ"=>"Ἂ","ἁ"=>"Ἁ","ἀ"=>"Ἀ","ỹ"=>"Ỹ",
    "ỷ"=>"Ỷ","ỵ"=>"Ỵ","ỳ"=>"Ỳ","ự"=>"Ự","ữ"=>"Ữ","ử"=>"Ử","ừ"=>"Ừ","ứ"=>"Ứ","ủ"=>"Ủ","ụ"=>"Ụ",
    "ợ"=>"Ợ","ỡ"=>"Ỡ","ở"=>"Ở","ờ"=>"Ờ","ớ"=>"Ớ","ộ"=>"Ộ","ỗ"=>"Ỗ","ổ"=>"Ổ","ồ"=>"Ồ","ố"=>"Ố",
    "ỏ"=>"Ỏ","ọ"=>"Ọ","ị"=>"Ị","ỉ"=>"Ỉ","ệ"=>"Ệ","ễ"=>"Ễ","ể"=>"Ể","ề"=>"Ề","ế"=>"Ế","ẽ"=>"Ẽ",
    "ẻ"=>"Ẻ","ẹ"=>"Ẹ","ặ"=>"Ặ","ẵ"=>"Ẵ","ẳ"=>"Ẳ","ằ"=>"Ằ","ắ"=>"Ắ","ậ"=>"Ậ","ẫ"=>"Ẫ","ẩ"=>"Ẩ",
    "ầ"=>"Ầ","ấ"=>"Ấ","ả"=>"Ả","ạ"=>"Ạ","ẛ"=>"Ṡ","ẕ"=>"Ẕ","ẓ"=>"Ẓ","ẑ"=>"Ẑ","ẏ"=>"Ẏ","ẍ"=>"Ẍ",
    "ẋ"=>"Ẋ","ẉ"=>"Ẉ","ẇ"=>"Ẇ","ẅ"=>"Ẅ","ẃ"=>"Ẃ","ẁ"=>"Ẁ","ṿ"=>"Ṿ","ṽ"=>"Ṽ","ṻ"=>"Ṻ","ṹ"=>"Ṹ",
    "ṷ"=>"Ṷ","ṵ"=>"Ṵ","ṳ"=>"Ṳ","ṱ"=>"Ṱ","ṯ"=>"Ṯ","ṭ"=>"Ṭ","ṫ"=>"Ṫ","ṩ"=>"Ṩ","ṧ"=>"Ṧ","ṥ"=>"Ṥ",
    "ṣ"=>"Ṣ","ṡ"=>"Ṡ","ṟ"=>"Ṟ","ṝ"=>"Ṝ","ṛ"=>"Ṛ","ṙ"=>"Ṙ","ṗ"=>"Ṗ","ṕ"=>"Ṕ","ṓ"=>"Ṓ","ṑ"=>"Ṑ",
    "ṏ"=>"Ṏ","ṍ"=>"Ṍ","ṋ"=>"Ṋ","ṉ"=>"Ṉ","ṇ"=>"Ṇ","ṅ"=>"Ṅ","ṃ"=>"Ṃ","ṁ"=>"Ṁ","ḿ"=>"Ḿ","ḽ"=>"Ḽ",
    "ḻ"=>"Ḻ","ḹ"=>"Ḹ","ḷ"=>"Ḷ","ḵ"=>"Ḵ","ḳ"=>"Ḳ","ḱ"=>"Ḱ","ḯ"=>"Ḯ","ḭ"=>"Ḭ","ḫ"=>"Ḫ","ḩ"=>"Ḩ",
    "ḧ"=>"Ḧ","ḥ"=>"Ḥ","ḣ"=>"Ḣ","ḡ"=>"Ḡ","ḟ"=>"Ḟ","ḝ"=>"Ḝ","ḛ"=>"Ḛ","ḙ"=>"Ḙ","ḗ"=>"Ḗ","ḕ"=>"Ḕ",
    "ḓ"=>"Ḓ","ḑ"=>"Ḑ","ḏ"=>"Ḏ","ḍ"=>"Ḍ","ḋ"=>"Ḋ","ḉ"=>"Ḉ","ḇ"=>"Ḇ","ḅ"=>"Ḅ","ḃ"=>"Ḃ","ḁ"=>"Ḁ",
    "ֆ"=>"Ֆ","օ"=>"Օ","ք"=>"Ք","փ"=>"Փ","ւ"=>"Ւ","ց"=>"Ց","ր"=>"Ր","տ"=>"Տ","վ"=>"Վ","ս"=>"Ս",
    "ռ"=>"Ռ","ջ"=>"Ջ","պ"=>"Պ","չ"=>"Չ","ո"=>"Ո","շ"=>"Շ","ն"=>"Ն","յ"=>"Յ","մ"=>"Մ","ճ"=>"Ճ",
    "ղ"=>"Ղ","ձ"=>"Ձ","հ"=>"Հ","կ"=>"Կ","ծ"=>"Ծ","խ"=>"Խ","լ"=>"Լ","ի"=>"Ի","ժ"=>"Ժ","թ"=>"Թ",
    "ը"=>"Ը","է"=>"Է","զ"=>"Զ","ե"=>"Ե","դ"=>"Դ","գ"=>"Գ","բ"=>"Բ","ա"=>"Ա","ԏ"=>"Ԏ","ԍ"=>"Ԍ",
    "ԋ"=>"Ԋ","ԉ"=>"Ԉ","ԇ"=>"Ԇ","ԅ"=>"Ԅ","ԃ"=>"Ԃ","ԁ"=>"Ԁ","ӹ"=>"Ӹ","ӵ"=>"Ӵ","ӳ"=>"Ӳ","ӱ"=>"Ӱ",
    "ӯ"=>"Ӯ","ӭ"=>"Ӭ","ӫ"=>"Ӫ","ө"=>"Ө","ӧ"=>"Ӧ","ӥ"=>"Ӥ","ӣ"=>"Ӣ","ӡ"=>"Ӡ","ӟ"=>"Ӟ","ӝ"=>"Ӝ",
    "ӛ"=>"Ӛ","ә"=>"Ә","ӗ"=>"Ӗ","ӕ"=>"Ӕ","ӓ"=>"Ӓ","ӑ"=>"Ӑ","ӎ"=>"Ӎ","ӌ"=>"Ӌ","ӊ"=>"Ӊ","ӈ"=>"Ӈ",
    "ӆ"=>"Ӆ","ӄ"=>"Ӄ","ӂ"=>"Ӂ","ҿ"=>"Ҿ","ҽ"=>"Ҽ","һ"=>"Һ","ҹ"=>"Ҹ","ҷ"=>"Ҷ","ҵ"=>"Ҵ","ҳ"=>"Ҳ",
    "ұ"=>"Ұ","ү"=>"Ү","ҭ"=>"Ҭ","ҫ"=>"Ҫ","ҩ"=>"Ҩ","ҧ"=>"Ҧ","ҥ"=>"Ҥ","ң"=>"Ң","ҡ"=>"Ҡ","ҟ"=>"Ҟ",
    "ҝ"=>"Ҝ","қ"=>"Қ","ҙ"=>"Ҙ","җ"=>"Җ","ҕ"=>"Ҕ","ғ"=>"Ғ","ґ"=>"Ґ","ҏ"=>"Ҏ","ҍ"=>"Ҍ","ҋ"=>"Ҋ",
    "ҁ"=>"Ҁ","ѿ"=>"Ѿ","ѽ"=>"Ѽ","ѻ"=>"Ѻ","ѹ"=>"Ѹ","ѷ"=>"Ѷ","ѵ"=>"Ѵ","ѳ"=>"Ѳ","ѱ"=>"Ѱ","ѯ"=>"Ѯ",
    "ѭ"=>"Ѭ","ѫ"=>"Ѫ","ѩ"=>"Ѩ","ѧ"=>"Ѧ","ѥ"=>"Ѥ","ѣ"=>"Ѣ","ѡ"=>"Ѡ","џ"=>"Џ","ў"=>"Ў","ѝ"=>"Ѝ",
    "ќ"=>"Ќ","ћ"=>"Ћ","њ"=>"Њ","љ"=>"Љ","ј"=>"Ј","ї"=>"Ї","і"=>"І","ѕ"=>"Ѕ","є"=>"Є","ѓ"=>"Ѓ",
    "ђ"=>"Ђ","ё"=>"Ё","ѐ"=>"Ѐ","я"=>"Я","ю"=>"Ю","э"=>"Э","ь"=>"Ь","ы"=>"Ы","ъ"=>"Ъ","щ"=>"Щ",
    "ш"=>"Ш","ч"=>"Ч","ц"=>"Ц","х"=>"Х","ф"=>"Ф","у"=>"У","т"=>"Т","с"=>"С","р"=>"Р","п"=>"П",
    "о"=>"О","н"=>"Н","м"=>"М","л"=>"Л","к"=>"К","й"=>"Й","и"=>"И","з"=>"З","ж"=>"Ж","е"=>"Е",
    "д"=>"Д","г"=>"Г","в"=>"В","б"=>"Б","а"=>"А","ϵ"=>"Ε","ϲ"=>"Σ","ϱ"=>"Ρ","ϰ"=>"Κ","ϯ"=>"Ϯ",
    "ϭ"=>"Ϭ","ϫ"=>"Ϫ","ϩ"=>"Ϩ","ϧ"=>"Ϧ","ϥ"=>"Ϥ","ϣ"=>"Ϣ","ϡ"=>"Ϡ","ϟ"=>"Ϟ","ϝ"=>"Ϝ","ϛ"=>"Ϛ",
    "ϙ"=>"Ϙ","ϖ"=>"Π","ϕ"=>"Φ","ϑ"=>"Θ","ϐ"=>"Β","ώ"=>"Ώ","ύ"=>"Ύ","ό"=>"Ό","ϋ"=>"Ϋ","ϊ"=>"Ϊ",
    "ω"=>"Ω","ψ"=>"Ψ","χ"=>"Χ","φ"=>"Φ","υ"=>"Υ","τ"=>"Τ","σ"=>"Σ","ς"=>"Σ","ρ"=>"Ρ","π"=>"Π",
    "ο"=>"Ο","ξ"=>"Ξ","ν"=>"Ν","μ"=>"Μ","λ"=>"Λ","κ"=>"Κ","ι"=>"Ι","θ"=>"Θ","η"=>"Η","ζ"=>"Ζ",
    "ε"=>"Ε","δ"=>"Δ","γ"=>"Γ","β"=>"Β","α"=>"Α","ί"=>"Ί","ή"=>"Ή","έ"=>"Έ","ά"=>"Ά","ʒ"=>"Ʒ",
    "ʋ"=>"Ʋ","ʊ"=>"Ʊ","ʈ"=>"Ʈ","ʃ"=>"Ʃ","ʀ"=>"Ʀ","ɵ"=>"Ɵ","ɲ"=>"Ɲ","ɯ"=>"Ɯ","ɩ"=>"Ɩ","ɨ"=>"Ɨ",
    "ɣ"=>"Ɣ","ɛ"=>"Ɛ","ə"=>"Ə","ɗ"=>"Ɗ","ɖ"=>"Ɖ","ɔ"=>"Ɔ","ɓ"=>"Ɓ","ȳ"=>"Ȳ","ȱ"=>"Ȱ","ȯ"=>"Ȯ",
    "ȭ"=>"Ȭ","ȫ"=>"Ȫ","ȩ"=>"Ȩ","ȧ"=>"Ȧ","ȥ"=>"Ȥ","ȣ"=>"Ȣ","ȟ"=>"Ȟ","ȝ"=>"Ȝ","ț"=>"Ț","ș"=>"Ș",
    "ȗ"=>"Ȗ","ȕ"=>"Ȕ","ȓ"=>"Ȓ","ȑ"=>"Ȑ","ȏ"=>"Ȏ","ȍ"=>"Ȍ","ȋ"=>"Ȋ","ȉ"=>"Ȉ","ȇ"=>"Ȇ","ȅ"=>"Ȅ",
    "ȃ"=>"Ȃ","ȁ"=>"Ȁ","ǿ"=>"Ǿ","ǽ"=>"Ǽ","ǻ"=>"Ǻ","ǹ"=>"Ǹ","ǵ"=>"Ǵ","ǳ"=>"ǲ","ǯ"=>"Ǯ","ǭ"=>"Ǭ",
    "ǫ"=>"Ǫ","ǩ"=>"Ǩ","ǧ"=>"Ǧ","ǥ"=>"Ǥ","ǣ"=>"Ǣ","ǡ"=>"Ǡ","ǟ"=>"Ǟ","ǝ"=>"Ǝ","ǜ"=>"Ǜ","ǚ"=>"Ǚ",
    "ǘ"=>"Ǘ","ǖ"=>"Ǖ","ǔ"=>"Ǔ","ǒ"=>"Ǒ","ǐ"=>"Ǐ","ǎ"=>"Ǎ","ǌ"=>"ǋ","ǉ"=>"ǈ","ǆ"=>"ǅ","ƿ"=>"Ƿ",
    "ƽ"=>"Ƽ","ƹ"=>"Ƹ","ƶ"=>"Ƶ","ƴ"=>"Ƴ","ư"=>"Ư","ƭ"=>"Ƭ","ƨ"=>"Ƨ","ƥ"=>"Ƥ","ƣ"=>"Ƣ","ơ"=>"Ơ",
    "ƞ"=>"Ƞ","ƙ"=>"Ƙ","ƕ"=>"Ƕ","ƒ"=>"Ƒ","ƌ"=>"Ƌ","ƈ"=>"Ƈ","ƅ"=>"Ƅ","ƃ"=>"Ƃ","ſ"=>"S","ž"=>"Ž",
    "ż"=>"Ż","ź"=>"Ź","ŷ"=>"Ŷ","ŵ"=>"Ŵ","ų"=>"Ų","ű"=>"Ű","ů"=>"Ů","ŭ"=>"Ŭ","ū"=>"Ū","ũ"=>"Ũ",
    "ŧ"=>"Ŧ","ť"=>"Ť","ţ"=>"Ţ","š"=>"Š","ş"=>"Ş","ŝ"=>"Ŝ","ś"=>"Ś","ř"=>"Ř","ŗ"=>"Ŗ","ŕ"=>"Ŕ",
    "œ"=>"Œ","ő"=>"Ő","ŏ"=>"Ŏ","ō"=>"Ō","ŋ"=>"Ŋ","ň"=>"Ň","ņ"=>"Ņ","ń"=>"Ń","ł"=>"Ł","ŀ"=>"Ŀ",
    "ľ"=>"Ľ","ļ"=>"Ļ","ĺ"=>"Ĺ","ķ"=>"Ķ","ĵ"=>"Ĵ","ĳ"=>"Ĳ","ı"=>"I","į"=>"Į","ĭ"=>"Ĭ","ī"=>"Ī",
    "ĩ"=>"Ĩ","ħ"=>"Ħ","ĥ"=>"Ĥ","ģ"=>"Ģ","ġ"=>"Ġ","ğ"=>"Ğ","ĝ"=>"Ĝ","ě"=>"Ě","ę"=>"Ę","ė"=>"Ė",
    "ĕ"=>"Ĕ","ē"=>"Ē","đ"=>"Đ","ď"=>"Ď","č"=>"Č","ċ"=>"Ċ","ĉ"=>"Ĉ","ć"=>"Ć","ą"=>"Ą","ă"=>"Ă",
    "ā"=>"Ā","ÿ"=>"Ÿ","þ"=>"Þ","ý"=>"Ý","ü"=>"Ü","û"=>"Û","ú"=>"Ú","ù"=>"Ù","ø"=>"Ø","ö"=>"Ö",
    "õ"=>"Õ","ô"=>"Ô","ó"=>"Ó","ò"=>"Ò","ñ"=>"Ñ","ð"=>"Ð","ï"=>"Ï","î"=>"Î","í"=>"Í","ì"=>"Ì",
    "ë"=>"Ë","ê"=>"Ê","é"=>"É","è"=>"È","ç"=>"Ç","æ"=>"Æ","å"=>"Å","ä"=>"Ä","ã"=>"Ã","â"=>"Â",
    "á"=>"Á","à"=>"À","µ"=>"Μ","z"=>"Z","y"=>"Y","x"=>"X","w"=>"W","v"=>"V","u"=>"U","t"=>"T",
    "s"=>"S","r"=>"R","q"=>"Q","p"=>"P","o"=>"O","n"=>"N","m"=>"M","l"=>"L","k"=>"K","j"=>"J",
    "i"=>"I","h"=>"H","g"=>"G","f"=>"F","e"=>"E","d"=>"D","c"=>"C","b"=>"B","a"=>"A"
  );

  /**
   * UTF-8 Case lookup table
   *
   * This lookuptable defines the lower case letters to their correspponding
   * upper case letter in UTF-8
   *
   * @author Andreas Gohr <andi@splitbrain.org>
   */
  global $UTF8_UPPER_TO_LOWER;
  $UTF8_UPPER_TO_LOWER = array (
    "Ｚ"=>"ｚ","Ｙ"=>"ｙ","Ｘ"=>"ｘ","Ｗ"=>"ｗ","Ｖ"=>"ｖ","Ｕ"=>"ｕ","Ｔ"=>"ｔ","Ｓ"=>"ｓ","Ｒ"=>"ｒ","Ｑ"=>"ｑ",
    "Ｐ"=>"ｐ","Ｏ"=>"ｏ","Ｎ"=>"ｎ","Ｍ"=>"ｍ","Ｌ"=>"ｌ","Ｋ"=>"ｋ","Ｊ"=>"ｊ","Ｉ"=>"ｉ","Ｈ"=>"ｈ","Ｇ"=>"ｇ",
    "Ｆ"=>"ｆ","Ｅ"=>"ｅ","Ｄ"=>"ｄ","Ｃ"=>"ｃ","Ｂ"=>"ｂ","Ａ"=>"ａ","ῼ"=>"ῳ","Ῥ"=>"ῥ","Ῡ"=>"ῡ","Ῑ"=>"ῑ",
    "Ῐ"=>"ῐ","ῌ"=>"ῃ","Ι"=>"ι","ᾼ"=>"ᾳ","Ᾱ"=>"ᾱ","Ᾰ"=>"ᾰ","ᾯ"=>"ᾧ","ᾮ"=>"ᾦ","ᾭ"=>"ᾥ","ᾬ"=>"ᾤ",
    "ᾫ"=>"ᾣ","ᾪ"=>"ᾢ","ᾩ"=>"ᾡ","ᾟ"=>"ᾗ","ᾞ"=>"ᾖ","ᾝ"=>"ᾕ","ᾜ"=>"ᾔ","ᾛ"=>"ᾓ","ᾚ"=>"ᾒ","ᾙ"=>"ᾑ",
    "ᾘ"=>"ᾐ","ᾏ"=>"ᾇ","ᾎ"=>"ᾆ","ᾍ"=>"ᾅ","ᾌ"=>"ᾄ","ᾋ"=>"ᾃ","ᾊ"=>"ᾂ","ᾉ"=>"ᾁ","ᾈ"=>"ᾀ","Ώ"=>"ώ",
    "Ὼ"=>"ὼ","Ύ"=>"ύ","Ὺ"=>"ὺ","Ό"=>"ό","Ὸ"=>"ὸ","Ί"=>"ί","Ὶ"=>"ὶ","Ή"=>"ή","Ὴ"=>"ὴ","Έ"=>"έ",
    "Ὲ"=>"ὲ","Ά"=>"ά","Ὰ"=>"ὰ","Ὧ"=>"ὧ","Ὦ"=>"ὦ","Ὥ"=>"ὥ","Ὤ"=>"ὤ","Ὣ"=>"ὣ","Ὢ"=>"ὢ","Ὡ"=>"ὡ",
    "Ὗ"=>"ὗ","Ὕ"=>"ὕ","Ὓ"=>"ὓ","Ὑ"=>"ὑ","Ὅ"=>"ὅ","Ὄ"=>"ὄ","Ὃ"=>"ὃ","Ὂ"=>"ὂ","Ὁ"=>"ὁ","Ὀ"=>"ὀ",
    "Ἷ"=>"ἷ","Ἶ"=>"ἶ","Ἵ"=>"ἵ","Ἴ"=>"ἴ","Ἳ"=>"ἳ","Ἲ"=>"ἲ","Ἱ"=>"ἱ","Ἰ"=>"ἰ","Ἧ"=>"ἧ","Ἦ"=>"ἦ",
    "Ἥ"=>"ἥ","Ἤ"=>"ἤ","Ἣ"=>"ἣ","Ἢ"=>"ἢ","Ἡ"=>"ἡ","Ἕ"=>"ἕ","Ἔ"=>"ἔ","Ἓ"=>"ἓ","Ἒ"=>"ἒ","Ἑ"=>"ἑ",
    "Ἐ"=>"ἐ","Ἇ"=>"ἇ","Ἆ"=>"ἆ","Ἅ"=>"ἅ","Ἄ"=>"ἄ","Ἃ"=>"ἃ","Ἂ"=>"ἂ","Ἁ"=>"ἁ","Ἀ"=>"ἀ","Ỹ"=>"ỹ",
    "Ỷ"=>"ỷ","Ỵ"=>"ỵ","Ỳ"=>"ỳ","Ự"=>"ự","Ữ"=>"ữ","Ử"=>"ử","Ừ"=>"ừ","Ứ"=>"ứ","Ủ"=>"ủ","Ụ"=>"ụ",
    "Ợ"=>"ợ","Ỡ"=>"ỡ","Ở"=>"ở","Ờ"=>"ờ","Ớ"=>"ớ","Ộ"=>"ộ","Ỗ"=>"ỗ","Ổ"=>"ổ","Ồ"=>"ồ","Ố"=>"ố",
    "Ỏ"=>"ỏ","Ọ"=>"ọ","Ị"=>"ị","Ỉ"=>"ỉ","Ệ"=>"ệ","Ễ"=>"ễ","Ể"=>"ể","Ề"=>"ề","Ế"=>"ế","Ẽ"=>"ẽ",
    "Ẻ"=>"ẻ","Ẹ"=>"ẹ","Ặ"=>"ặ","Ẵ"=>"ẵ","Ẳ"=>"ẳ","Ằ"=>"ằ","Ắ"=>"ắ","Ậ"=>"ậ","Ẫ"=>"ẫ","Ẩ"=>"ẩ",
    "Ầ"=>"ầ","Ấ"=>"ấ","Ả"=>"ả","Ạ"=>"ạ","Ṡ"=>"ẛ","Ẕ"=>"ẕ","Ẓ"=>"ẓ","Ẑ"=>"ẑ","Ẏ"=>"ẏ","Ẍ"=>"ẍ",
    "Ẋ"=>"ẋ","Ẉ"=>"ẉ","Ẇ"=>"ẇ","Ẅ"=>"ẅ","Ẃ"=>"ẃ","Ẁ"=>"ẁ","Ṿ"=>"ṿ","Ṽ"=>"ṽ","Ṻ"=>"ṻ","Ṹ"=>"ṹ",
    "Ṷ"=>"ṷ","Ṵ"=>"ṵ","Ṳ"=>"ṳ","Ṱ"=>"ṱ","Ṯ"=>"ṯ","Ṭ"=>"ṭ","Ṫ"=>"ṫ","Ṩ"=>"ṩ","Ṧ"=>"ṧ","Ṥ"=>"ṥ",
    "Ṣ"=>"ṣ","Ṡ"=>"ṡ","Ṟ"=>"ṟ","Ṝ"=>"ṝ","Ṛ"=>"ṛ","Ṙ"=>"ṙ","Ṗ"=>"ṗ","Ṕ"=>"ṕ","Ṓ"=>"ṓ","Ṑ"=>"ṑ",
    "Ṏ"=>"ṏ","Ṍ"=>"ṍ","Ṋ"=>"ṋ","Ṉ"=>"ṉ","Ṇ"=>"ṇ","Ṅ"=>"ṅ","Ṃ"=>"ṃ","Ṁ"=>"ṁ","Ḿ"=>"ḿ","Ḽ"=>"ḽ",
    "Ḻ"=>"ḻ","Ḹ"=>"ḹ","Ḷ"=>"ḷ","Ḵ"=>"ḵ","Ḳ"=>"ḳ","Ḱ"=>"ḱ","Ḯ"=>"ḯ","Ḭ"=>"ḭ","Ḫ"=>"ḫ","Ḩ"=>"ḩ",
    "Ḧ"=>"ḧ","Ḥ"=>"ḥ","Ḣ"=>"ḣ","Ḡ"=>"ḡ","Ḟ"=>"ḟ","Ḝ"=>"ḝ","Ḛ"=>"ḛ","Ḙ"=>"ḙ","Ḗ"=>"ḗ","Ḕ"=>"ḕ",
    "Ḓ"=>"ḓ","Ḑ"=>"ḑ","Ḏ"=>"ḏ","Ḍ"=>"ḍ","Ḋ"=>"ḋ","Ḉ"=>"ḉ","Ḇ"=>"ḇ","Ḅ"=>"ḅ","Ḃ"=>"ḃ","Ḁ"=>"ḁ",
    "Ֆ"=>"ֆ","Օ"=>"օ","Ք"=>"ք","Փ"=>"փ","Ւ"=>"ւ","Ց"=>"ց","Ր"=>"ր","Տ"=>"տ","Վ"=>"վ","Ս"=>"ս",
    "Ռ"=>"ռ","Ջ"=>"ջ","Պ"=>"պ","Չ"=>"չ","Ո"=>"ո","Շ"=>"շ","Ն"=>"ն","Յ"=>"յ","Մ"=>"մ","Ճ"=>"ճ",
    "Ղ"=>"ղ","Ձ"=>"ձ","Հ"=>"հ","Կ"=>"կ","Ծ"=>"ծ","Խ"=>"խ","Լ"=>"լ","Ի"=>"ի","Ժ"=>"ժ","Թ"=>"թ",
    "Ը"=>"ը","Է"=>"է","Զ"=>"զ","Ե"=>"ե","Դ"=>"դ","Գ"=>"գ","Բ"=>"բ","Ա"=>"ա","Ԏ"=>"ԏ","Ԍ"=>"ԍ",
    "Ԋ"=>"ԋ","Ԉ"=>"ԉ","Ԇ"=>"ԇ","Ԅ"=>"ԅ","Ԃ"=>"ԃ","Ԁ"=>"ԁ","Ӹ"=>"ӹ","Ӵ"=>"ӵ","Ӳ"=>"ӳ","Ӱ"=>"ӱ",
    "Ӯ"=>"ӯ","Ӭ"=>"ӭ","Ӫ"=>"ӫ","Ө"=>"ө","Ӧ"=>"ӧ","Ӥ"=>"ӥ","Ӣ"=>"ӣ","Ӡ"=>"ӡ","Ӟ"=>"ӟ","Ӝ"=>"ӝ",
    "Ӛ"=>"ӛ","Ә"=>"ә","Ӗ"=>"ӗ","Ӕ"=>"ӕ","Ӓ"=>"ӓ","Ӑ"=>"ӑ","Ӎ"=>"ӎ","Ӌ"=>"ӌ","Ӊ"=>"ӊ","Ӈ"=>"ӈ",
    "Ӆ"=>"ӆ","Ӄ"=>"ӄ","Ӂ"=>"ӂ","Ҿ"=>"ҿ","Ҽ"=>"ҽ","Һ"=>"һ","Ҹ"=>"ҹ","Ҷ"=>"ҷ","Ҵ"=>"ҵ","Ҳ"=>"ҳ",
    "Ұ"=>"ұ","Ү"=>"ү","Ҭ"=>"ҭ","Ҫ"=>"ҫ","Ҩ"=>"ҩ","Ҧ"=>"ҧ","Ҥ"=>"ҥ","Ң"=>"ң","Ҡ"=>"ҡ","Ҟ"=>"ҟ",
    "Ҝ"=>"ҝ","Қ"=>"қ","Ҙ"=>"ҙ","Җ"=>"җ","Ҕ"=>"ҕ","Ғ"=>"ғ","Ґ"=>"ґ","Ҏ"=>"ҏ","Ҍ"=>"ҍ","Ҋ"=>"ҋ",
    "Ҁ"=>"ҁ","Ѿ"=>"ѿ","Ѽ"=>"ѽ","Ѻ"=>"ѻ","Ѹ"=>"ѹ","Ѷ"=>"ѷ","Ѵ"=>"ѵ","Ѳ"=>"ѳ","Ѱ"=>"ѱ","Ѯ"=>"ѯ",
    "Ѭ"=>"ѭ","Ѫ"=>"ѫ","Ѩ"=>"ѩ","Ѧ"=>"ѧ","Ѥ"=>"ѥ","Ѣ"=>"ѣ","Ѡ"=>"ѡ","Џ"=>"џ","Ў"=>"ў","Ѝ"=>"ѝ",
    "Ќ"=>"ќ","Ћ"=>"ћ","Њ"=>"њ","Љ"=>"љ","Ј"=>"ј","Ї"=>"ї","І"=>"і","Ѕ"=>"ѕ","Є"=>"є","Ѓ"=>"ѓ",
    "Ђ"=>"ђ","Ё"=>"ё","Ѐ"=>"ѐ","Я"=>"я","Ю"=>"ю","Э"=>"э","Ь"=>"ь","Ы"=>"ы","Ъ"=>"ъ","Щ"=>"щ",
    "Ш"=>"ш","Ч"=>"ч","Ц"=>"ц","Х"=>"х","Ф"=>"ф","У"=>"у","Т"=>"т","С"=>"с","Р"=>"р","П"=>"п",
    "О"=>"о","Н"=>"н","М"=>"м","Л"=>"л","К"=>"к","Й"=>"й","И"=>"и","З"=>"з","Ж"=>"ж","Е"=>"е",
    "Д"=>"д","Г"=>"г","В"=>"в","Б"=>"б","А"=>"а","Ε"=>"ϵ","Σ"=>"ϲ","Ρ"=>"ϱ","Κ"=>"ϰ","Ϯ"=>"ϯ",
    "Ϭ"=>"ϭ","Ϫ"=>"ϫ","Ϩ"=>"ϩ","Ϧ"=>"ϧ","Ϥ"=>"ϥ","Ϣ"=>"ϣ","Ϡ"=>"ϡ","Ϟ"=>"ϟ","Ϝ"=>"ϝ","Ϛ"=>"ϛ",
    "Ϙ"=>"ϙ","Π"=>"ϖ","Φ"=>"ϕ","Θ"=>"ϑ","Β"=>"ϐ","Ώ"=>"ώ","Ύ"=>"ύ","Ό"=>"ό","Ϋ"=>"ϋ","Ϊ"=>"ϊ",
    "Ω"=>"ω","Ψ"=>"ψ","Χ"=>"χ","Φ"=>"φ","Υ"=>"υ","Τ"=>"τ","Σ"=>"σ","Σ"=>"ς","Ρ"=>"ρ","Π"=>"π",
    "Ο"=>"ο","Ξ"=>"ξ","Ν"=>"ν","Μ"=>"μ","Λ"=>"λ","Κ"=>"κ","Ι"=>"ι","Θ"=>"θ","Η"=>"η","Ζ"=>"ζ",
    "Ε"=>"ε","Δ"=>"δ","Γ"=>"γ","Β"=>"β","Α"=>"α","Ί"=>"ί","Ή"=>"ή","Έ"=>"έ","Ά"=>"ά","Ʒ"=>"ʒ",
    "Ʋ"=>"ʋ","Ʊ"=>"ʊ","Ʈ"=>"ʈ","Ʃ"=>"ʃ","Ʀ"=>"ʀ","Ɵ"=>"ɵ","Ɲ"=>"ɲ","Ɯ"=>"ɯ","Ɩ"=>"ɩ","Ɨ"=>"ɨ",
    "Ɣ"=>"ɣ","Ɛ"=>"ɛ","Ə"=>"ə","Ɗ"=>"ɗ","Ɖ"=>"ɖ","Ɔ"=>"ɔ","Ɓ"=>"ɓ","Ȳ"=>"ȳ","Ȱ"=>"ȱ","Ȯ"=>"ȯ",
    "Ȭ"=>"ȭ","Ȫ"=>"ȫ","Ȩ"=>"ȩ","Ȧ"=>"ȧ","Ȥ"=>"ȥ","Ȣ"=>"ȣ","Ȟ"=>"ȟ","Ȝ"=>"ȝ","Ț"=>"ț","Ș"=>"ș",
    "Ȗ"=>"ȗ","Ȕ"=>"ȕ","Ȓ"=>"ȓ","Ȑ"=>"ȑ","Ȏ"=>"ȏ","Ȍ"=>"ȍ","Ȋ"=>"ȋ","Ȉ"=>"ȉ","Ȇ"=>"ȇ","Ȅ"=>"ȅ",
    "Ȃ"=>"ȃ","Ȁ"=>"ȁ","Ǿ"=>"ǿ","Ǽ"=>"ǽ","Ǻ"=>"ǻ","Ǹ"=>"ǹ","Ǵ"=>"ǵ","ǲ"=>"ǳ","Ǯ"=>"ǯ","Ǭ"=>"ǭ",
    "Ǫ"=>"ǫ","Ǩ"=>"ǩ","Ǧ"=>"ǧ","Ǥ"=>"ǥ","Ǣ"=>"ǣ","Ǡ"=>"ǡ","Ǟ"=>"ǟ","Ǝ"=>"ǝ","Ǜ"=>"ǜ","Ǚ"=>"ǚ",
    "Ǘ"=>"ǘ","Ǖ"=>"ǖ","Ǔ"=>"ǔ","Ǒ"=>"ǒ","Ǐ"=>"ǐ","Ǎ"=>"ǎ","ǋ"=>"ǌ","ǈ"=>"ǉ","ǅ"=>"ǆ","Ƿ"=>"ƿ",
    "Ƽ"=>"ƽ","Ƹ"=>"ƹ","Ƶ"=>"ƶ","Ƴ"=>"ƴ","Ư"=>"ư","Ƭ"=>"ƭ","Ƨ"=>"ƨ","Ƥ"=>"ƥ","Ƣ"=>"ƣ","Ơ"=>"ơ",
    "Ƞ"=>"ƞ","Ƙ"=>"ƙ","Ƕ"=>"ƕ","Ƒ"=>"ƒ","Ƌ"=>"ƌ","Ƈ"=>"ƈ","Ƅ"=>"ƅ","Ƃ"=>"ƃ","S"=>"ſ","Ž"=>"ž",
    "Ż"=>"ż","Ź"=>"ź","Ŷ"=>"ŷ","Ŵ"=>"ŵ","Ų"=>"ų","Ű"=>"ű","Ů"=>"ů","Ŭ"=>"ŭ","Ū"=>"ū","Ũ"=>"ũ",
    "Ŧ"=>"ŧ","Ť"=>"ť","Ţ"=>"ţ","Š"=>"š","Ş"=>"ş","Ŝ"=>"ŝ","Ś"=>"ś","Ř"=>"ř","Ŗ"=>"ŗ","Ŕ"=>"ŕ",
    "Œ"=>"œ","Ő"=>"ő","Ŏ"=>"ŏ","Ō"=>"ō","Ŋ"=>"ŋ","Ň"=>"ň","Ņ"=>"ņ","Ń"=>"ń","Ł"=>"ł","Ŀ"=>"ŀ",
    "Ľ"=>"ľ","Ļ"=>"ļ","Ĺ"=>"ĺ","Ķ"=>"ķ","Ĵ"=>"ĵ","Ĳ"=>"ĳ","I"=>"ı","Į"=>"į","Ĭ"=>"ĭ","Ī"=>"ī",
    "Ĩ"=>"ĩ","Ħ"=>"ħ","Ĥ"=>"ĥ","Ģ"=>"ģ","Ġ"=>"ġ","Ğ"=>"ğ","Ĝ"=>"ĝ","Ě"=>"ě","Ę"=>"ę","Ė"=>"ė",
    "Ĕ"=>"ĕ","Ē"=>"ē","Đ"=>"đ","Ď"=>"ď","Č"=>"č","Ċ"=>"ċ","Ĉ"=>"ĉ","Ć"=>"ć","Ą"=>"ą","Ă"=>"ă",
    "Ā"=>"ā","Ÿ"=>"ÿ","Þ"=>"þ","Ý"=>"ý","Ü"=>"ü","Û"=>"û","Ú"=>"ú","Ù"=>"ù","Ø"=>"ø","Ö"=>"ö",
    "Õ"=>"õ","Ô"=>"ô","Ó"=>"ó","Ò"=>"ò","Ñ"=>"ñ","Ð"=>"ð","Ï"=>"ï","Î"=>"î","Í"=>"í","Ì"=>"ì",
    "Ë"=>"ë","Ê"=>"ê","É"=>"é","È"=>"è","Ç"=>"ç","Æ"=>"æ","Å"=>"å","Ä"=>"ä","Ã"=>"ã","Â"=>"â",
    "Á"=>"á","À"=>"à","Μ"=>"µ","Z"=>"z","Y"=>"y","X"=>"x","W"=>"w","V"=>"v","U"=>"u","T"=>"t",
    "S"=>"s","R"=>"r","Q"=>"q","P"=>"p","O"=>"o","N"=>"n","M"=>"m","L"=>"l","K"=>"k","J"=>"j",
    "I"=>"i","H"=>"h","G"=>"g","F"=>"f","E"=>"e","D"=>"d","C"=>"c","B"=>"b","A"=>"a"
  );
}; // end of case lookup tables

/**
 * UTF-8 lookup table for lower case accented letters
 *
 * This lookuptable defines replacements for accented characters from the ASCII-7
 * range. This are lower case letters only.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see    utf8_deaccent()
 */
global $UTF8_LOWER_ACCENTS;
$UTF8_LOWER_ACCENTS = array(
  'à' => 'a', 'ô' => 'o', 'ď' => 'd', 'ḟ' => 'f', 'ë' => 'e', 'š' => 's', 'ơ' => 'o',
  'ß' => 'ss', 'ă' => 'a', 'ř' => 'r', 'ț' => 't', 'ň' => 'n', 'ā' => 'a', 'ķ' => 'k',
  'ŝ' => 's', 'ỳ' => 'y', 'ņ' => 'n', 'ĺ' => 'l', 'ħ' => 'h', 'ṗ' => 'p', 'ó' => 'o',
  'ú' => 'u', 'ě' => 'e', 'é' => 'e', 'ç' => 'c', 'ẁ' => 'w', 'ċ' => 'c', 'õ' => 'o',
  'ṡ' => 's', 'ø' => 'o', 'ģ' => 'g', 'ŧ' => 't', 'ș' => 's', 'ė' => 'e', 'ĉ' => 'c',
  'ś' => 's', 'î' => 'i', 'ű' => 'u', 'ć' => 'c', 'ę' => 'e', 'ŵ' => 'w', 'ṫ' => 't',
  'ū' => 'u', 'č' => 'c', 'ö' => 'oe', 'è' => 'e', 'ŷ' => 'y', 'ą' => 'a', 'ł' => 'l',
  'ų' => 'u', 'ů' => 'u', 'ş' => 's', 'ğ' => 'g', 'ļ' => 'l', 'ƒ' => 'f', 'ž' => 'z',
  'ẃ' => 'w', 'ḃ' => 'b', 'å' => 'a', 'ì' => 'i', 'ï' => 'i', 'ḋ' => 'd', 'ť' => 't',
  'ŗ' => 'r', 'ä' => 'ae', 'í' => 'i', 'ŕ' => 'r', 'ê' => 'e', 'ü' => 'ue', 'ò' => 'o',
  'ē' => 'e', 'ñ' => 'n', 'ń' => 'n', 'ĥ' => 'h', 'ĝ' => 'g', 'đ' => 'd', 'ĵ' => 'j',
  'ÿ' => 'y', 'ũ' => 'u', 'ŭ' => 'u', 'ư' => 'u', 'ţ' => 't', 'ý' => 'y', 'ő' => 'o',
  'â' => 'a', 'ľ' => 'l', 'ẅ' => 'w', 'ż' => 'z', 'ī' => 'i', 'ã' => 'a', 'ġ' => 'g',
  'ṁ' => 'm', 'ō' => 'o', 'ĩ' => 'i', 'ù' => 'u', 'į' => 'i', 'ź' => 'z', 'á' => 'a',
  'û' => 'u', 'þ' => 'th', 'ð' => 'dh', 'æ' => 'ae', 'µ' => 'u', 'ĕ' => 'e',
);

/**
 * UTF-8 lookup table for upper case accented letters
 *
 * This lookuptable defines replacements for accented characters from the ASCII-7
 * range. This are upper case letters only.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see    utf8_deaccent()
 */
global $UTF8_UPPER_ACCENTS;
$UTF8_UPPER_ACCENTS = array(
  'À' => 'A', 'Ô' => 'O', 'Ď' => 'D', 'Ḟ' => 'F', 'Ë' => 'E', 'Š' => 'S', 'Ơ' => 'O',
  'Ă' => 'A', 'Ř' => 'R', 'Ț' => 'T', 'Ň' => 'N', 'Ā' => 'A', 'Ķ' => 'K',
  'Ŝ' => 'S', 'Ỳ' => 'Y', 'Ņ' => 'N', 'Ĺ' => 'L', 'Ħ' => 'H', 'Ṗ' => 'P', 'Ó' => 'O',
  'Ú' => 'U', 'Ě' => 'E', 'É' => 'E', 'Ç' => 'C', 'Ẁ' => 'W', 'Ċ' => 'C', 'Õ' => 'O',
  'Ṡ' => 'S', 'Ø' => 'O', 'Ģ' => 'G', 'Ŧ' => 'T', 'Ș' => 'S', 'Ė' => 'E', 'Ĉ' => 'C',
  'Ś' => 'S', 'Î' => 'I', 'Ű' => 'U', 'Ć' => 'C', 'Ę' => 'E', 'Ŵ' => 'W', 'Ṫ' => 'T',
  'Ū' => 'U', 'Č' => 'C', 'Ö' => 'Oe', 'È' => 'E', 'Ŷ' => 'Y', 'Ą' => 'A', 'Ł' => 'L',
  'Ų' => 'U', 'Ů' => 'U', 'Ş' => 'S', 'Ğ' => 'G', 'Ļ' => 'L', 'Ƒ' => 'F', 'Ž' => 'Z',
  'Ẃ' => 'W', 'Ḃ' => 'B', 'Å' => 'A', 'Ì' => 'I', 'Ï' => 'I', 'Ḋ' => 'D', 'Ť' => 'T',
  'Ŗ' => 'R', 'Ä' => 'Ae', 'Í' => 'I', 'Ŕ' => 'R', 'Ê' => 'E', 'Ü' => 'Ue', 'Ò' => 'O',
  'Ē' => 'E', 'Ñ' => 'N', 'Ń' => 'N', 'Ĥ' => 'H', 'Ĝ' => 'G', 'Đ' => 'D', 'Ĵ' => 'J',
  'Ÿ' => 'Y', 'Ũ' => 'U', 'Ŭ' => 'U', 'Ư' => 'U', 'Ţ' => 'T', 'Ý' => 'Y', 'Ő' => 'O',
  'Â' => 'A', 'Ľ' => 'L', 'Ẅ' => 'W', 'Ż' => 'Z', 'Ī' => 'I', 'Ã' => 'A', 'Ġ' => 'G',
  'Ṁ' => 'M', 'Ō' => 'O', 'Ĩ' => 'I', 'Ù' => 'U', 'Į' => 'I', 'Ź' => 'Z', 'Á' => 'A',
  'Û' => 'U', 'Þ' => 'Th', 'Ð' => 'Dh', 'Æ' => 'Ae', 'Ĕ' => 'E',
);

/**
 * UTF-8 array of common special characters
 *
 * This array should contain all special characters (not a letter or digit)
 * defined in the various local charsets - it's not a complete list of non-alphanum
 * characters in UTF-8. It's not perfect but should match most cases of special
 * chars.
 *
 * The controlchars 0x00 to 0x19 are _not_ included in this array. The space 0x20 is!
 * These chars are _not_ in the array either:  _ (0x5f), : 0x3a, . 0x2e, - 0x2d, * 0x2a
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see    utf8_stripspecials()
 */
global $UTF8_SPECIAL_CHARS;
$UTF8_SPECIAL_CHARS = array(
  0x001a, 0x001b, 0x001c, 0x001d, 0x001e, 0x001f, 0x0020, 0x0021, 0x0022, 0x0023,
  0x0024, 0x0025, 0x0026, 0x0027, 0x0028, 0x0029,         0x002b, 0x002c,
          0x002f,         0x003b, 0x003c, 0x003d, 0x003e, 0x003f, 0x0040, 0x005b,
  0x005c, 0x005d, 0x005e,         0x0060, 0x007b, 0x007c, 0x007d, 0x007e,
  0x007f, 0x0080, 0x0081, 0x0082, 0x0083, 0x0084, 0x0085, 0x0086, 0x0087, 0x0088,
  0x0089, 0x008a, 0x008b, 0x008c, 0x008d, 0x008e, 0x008f, 0x0090, 0x0091, 0x0092,
  0x0093, 0x0094, 0x0095, 0x0096, 0x0097, 0x0098, 0x0099, 0x009a, 0x009b, 0x009c,
  0x009d, 0x009e, 0x009f, 0x00a0, 0x00a1, 0x00a2, 0x00a3, 0x00a4, 0x00a5, 0x00a6,
  0x00a7, 0x00a8, 0x00a9, 0x00aa, 0x00ab, 0x00ac, 0x00ad, 0x00ae, 0x00af, 0x00b0,
  0x00b1, 0x00b2, 0x00b3, 0x00b4, 0x00b5, 0x00b6, 0x00b7, 0x00b8, 0x00b9, 0x00ba,
  0x00bb, 0x00bc, 0x00bd, 0x00be, 0x00bf, 0x00d7, 0x00f7, 0x02c7, 0x02d8, 0x02d9,
  0x02da, 0x02db, 0x02dc, 0x02dd, 0x0300, 0x0301, 0x0303, 0x0309, 0x0323, 0x0384,
  0x0385, 0x0387, 0x03b2, 0x03c6, 0x03d1, 0x03d2, 0x03d5, 0x03d6, 0x05b0, 0x05b1,
  0x05b2, 0x05b3, 0x05b4, 0x05b5, 0x05b6, 0x05b7, 0x05b8, 0x05b9, 0x05bb, 0x05bc,
  0x05bd, 0x05be, 0x05bf, 0x05c0, 0x05c1, 0x05c2, 0x05c3, 0x05f3, 0x05f4, 0x060c,
  0x061b, 0x061f, 0x0640, 0x064b, 0x064c, 0x064d, 0x064e, 0x064f, 0x0650, 0x0651,
  0x0652, 0x066a, 0x0e3f, 0x200c, 0x200d, 0x200e, 0x200f, 0x2013, 0x2014, 0x2015,
  0x2017, 0x2018, 0x2019, 0x201a, 0x201c, 0x201d, 0x201e, 0x2020, 0x2021, 0x2022,
  0x2026, 0x2030, 0x2032, 0x2033, 0x2039, 0x203a, 0x2044, 0x20a7, 0x20aa, 0x20ab,
  0x20ac, 0x2116, 0x2118, 0x2122, 0x2126, 0x2135, 0x2190, 0x2191, 0x2192, 0x2193,
  0x2194, 0x2195, 0x21b5, 0x21d0, 0x21d1, 0x21d2, 0x21d3, 0x21d4, 0x2200, 0x2202,
  0x2203, 0x2205, 0x2206, 0x2207, 0x2208, 0x2209, 0x220b, 0x220f, 0x2211, 0x2212,
  0x2215, 0x2217, 0x2219, 0x221a, 0x221d, 0x221e, 0x2220, 0x2227, 0x2228, 0x2229,
  0x222a, 0x222b, 0x2234, 0x223c, 0x2245, 0x2248, 0x2260, 0x2261, 0x2264, 0x2265,
  0x2282, 0x2283, 0x2284, 0x2286, 0x2287, 0x2295, 0x2297, 0x22a5, 0x22c5, 0x2310,
  0x2320, 0x2321, 0x2329, 0x232a, 0x2469, 0x2500, 0x2502, 0x250c, 0x2510, 0x2514,
  0x2518, 0x251c, 0x2524, 0x252c, 0x2534, 0x253c, 0x2550, 0x2551, 0x2552, 0x2553,
  0x2554, 0x2555, 0x2556, 0x2557, 0x2558, 0x2559, 0x255a, 0x255b, 0x255c, 0x255d,
  0x255e, 0x255f, 0x2560, 0x2561, 0x2562, 0x2563, 0x2564, 0x2565, 0x2566, 0x2567,
  0x2568, 0x2569, 0x256a, 0x256b, 0x256c, 0x2580, 0x2584, 0x2588, 0x258c, 0x2590,
  0x2591, 0x2592, 0x2593, 0x25a0, 0x25b2, 0x25bc, 0x25c6, 0x25ca, 0x25cf, 0x25d7,
  0x2605, 0x260e, 0x261b, 0x261e, 0x2660, 0x2663, 0x2665, 0x2666, 0x2701, 0x2702,
  0x2703, 0x2704, 0x2706, 0x2707, 0x2708, 0x2709, 0x270c, 0x270d, 0x270e, 0x270f,
  0x2710, 0x2711, 0x2712, 0x2713, 0x2714, 0x2715, 0x2716, 0x2717, 0x2718, 0x2719,
  0x271a, 0x271b, 0x271c, 0x271d, 0x271e, 0x271f, 0x2720, 0x2721, 0x2722, 0x2723,
  0x2724, 0x2725, 0x2726, 0x2727, 0x2729, 0x272a, 0x272b, 0x272c, 0x272d, 0x272e,
  0x272f, 0x2730, 0x2731, 0x2732, 0x2733, 0x2734, 0x2735, 0x2736, 0x2737, 0x2738,
  0x2739, 0x273a, 0x273b, 0x273c, 0x273d, 0x273e, 0x273f, 0x2740, 0x2741, 0x2742,
  0x2743, 0x2744, 0x2745, 0x2746, 0x2747, 0x2748, 0x2749, 0x274a, 0x274b, 0x274d,
  0x274f, 0x2750, 0x2751, 0x2752, 0x2756, 0x2758, 0x2759, 0x275a, 0x275b, 0x275c,
  0x275d, 0x275e, 0x2761, 0x2762, 0x2763, 0x2764, 0x2765, 0x2766, 0x2767, 0x277f,
  0x2789, 0x2793, 0x2794, 0x2798, 0x2799, 0x279a, 0x279b, 0x279c, 0x279d, 0x279e,
  0x279f, 0x27a0, 0x27a1, 0x27a2, 0x27a3, 0x27a4, 0x27a5, 0x27a6, 0x27a7, 0x27a8,
  0x27a9, 0x27aa, 0x27ab, 0x27ac, 0x27ad, 0x27ae, 0x27af, 0x27b1, 0x27b2, 0x27b3,
  0x27b4, 0x27b5, 0x27b6, 0x27b7, 0x27b8, 0x27b9, 0x27ba, 0x27bb, 0x27bc, 0x27bd,
  0x27be, 0x3000, 0x3001, 0x3002, 0x3003, 0x3008, 0x3009, 0x300a, 0x300b, 0x300c,
  0x300d, 0x300e, 0x300f, 0x3010, 0x3011, 0x3012, 0x3014, 0x3015, 0x3016, 0x3017,
  0x3018, 0x3019, 0x301a, 0x301b, 0x3036,
  0xf6d9, 0xf6da, 0xf6db, 0xf8d7, 0xf8d8, 0xf8d9, 0xf8da, 0xf8db, 0xf8dc,
  0xf8dd, 0xf8de, 0xf8df, 0xf8e0, 0xf8e1, 0xf8e2, 0xf8e3, 0xf8e4, 0xf8e5, 0xf8e6,
  0xf8e7, 0xf8e8, 0xf8e9, 0xf8ea, 0xf8eb, 0xf8ec, 0xf8ed, 0xf8ee, 0xf8ef, 0xf8f0,
  0xf8f1, 0xf8f2, 0xf8f3, 0xf8f4, 0xf8f5, 0xf8f6, 0xf8f7, 0xf8f8, 0xf8f9, 0xf8fa,
  0xf8fb, 0xf8fc, 0xf8fd, 0xf8fe, 0xfe7c, 0xfe7d,
          0xff01, 0xff02, 0xff03, 0xff04, 0xff05, 0xff06, 0xff07, 0xff08, 0xff09,
  0xff09, 0xff0a, 0xff0b, 0xff0c, 0xff0d, 0xff0e, 0xff0f, 0xff1a, 0xff1b, 0xff1c,
  0xff1d, 0xff1e, 0xff1f, 0xff20, 0xff3b, 0xff3c, 0xff3d, 0xff3e, 0xff40, 0xff5b,
  0xff5c, 0xff5d, 0xff5e, 0xff5f, 0xff60, 0xff61, 0xff62, 0xff63, 0xff64, 0xff65,
  0xffe0, 0xffe1, 0xffe2, 0xffe3, 0xffe4, 0xffe5, 0xffe6, 0xffe8, 0xffe9, 0xffea,
  0xffeb, 0xffec, 0xffed, 0xffee,
);

// utf8 version of above data
global $UTF8_SPECIAL_CHARS2;
$UTF8_SPECIAL_CHARS2 =
    "\x1A".' !"#$%&\'()+,/;<=>?@[\]^`{|}~�'.
    '� ¡¢£¤¥¦§¨©ª«¬­®¯°±²³´µ¶·¸¹º»¼½�'.
    '�¿×÷ˇ˘˙˚˛˜˝̣̀́̃̉΄΅·βφϑϒϕϖְֱֲֳִֵֶַָֹֻּֽ־ֿ�'.
    '�ׁׂ׃׳״،؛؟ـًٌٍَُِّْ٪฿‌‍‎‏–—―‗‘’‚“”�'.
    '��†‡•…‰′″‹›⁄₧₪₫€№℘™Ωℵ←↑→↓↔↕↵'.
    '⇐⇑⇒⇓⇔∀∂∃∅∆∇∈∉∋∏∑−∕∗∙√∝∞∠∧∨�'.
    '�∪∫∴∼≅≈≠≡≤≥⊂⊃⊄⊆⊇⊕⊗⊥⋅⌐⌠⌡〈〉⑩─�'.
    '��┌┐└┘├┤┬┴┼═║╒╓╔╕╖╗╘╙╚╛╜╝╞╟╠'.
    '╡╢╣╤╥╦╧╨╩╪╫╬▀▄█▌▐░▒▓■▲▼◆◊●�'.
    '�★☎☛☞♠♣♥♦✁✂✃✄✆✇✈✉✌✍✎✏✐✑✒✓✔✕�'.
    '��✗✘✙✚✛✜✝✞✟✠✡✢✣✤✥✦✧✩✪✫✬✭✮✯✰✱'.
    '✲✳✴✵✶✷✸✹✺✻✼✽✾✿❀❁❂❃❄❅❆❇❈❉❊❋�'.
    '�❏❐❑❒❖❘❙❚❛❜❝❞❡❢❣❤❥❦❧❿➉➓➔➘➙➚�'.
    '��➜➝➞➟➠➡➢➣➤➥➦➧➨➩➪➫➬➭➮➯➱➲➳➴➵➶'.
    '➷➸➹➺➻➼➽➾'.
    '　、。〃〈〉《》「」『』【】〒〔〕〖〗〘〙〚〛〶'.
    '�'.
    '�ﹼﹽ'.
    '！＂＃＄％＆＇（）＊＋，－．／：；＜＝＞？＠［＼］＾｀｛｜｝～'.
    '｟｠｡｢｣､･￠￡￢￣￤￥￦￨￩￪￫￬￭￮';

/**
 * Romanization lookup table
 *
 * This lookup tables provides a way to transform strings written in a language
 * different from the ones based upon latin letters into plain ASCII.
 *
 * Please note: this is not a scientific transliteration table. It only works
 * oneway from nonlatin to ASCII and it works by simple character replacement
 * only. Specialities of each language are not supported.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Vitaly Blokhin <vitinfo@vitn.com>
 * @link   http://www.uconv.com/translit.htm
 * @author Bisqwit <bisqwit@iki.fi>
 * @link   http://kanjidict.stc.cx/hiragana.php?src=2
 * @link   http://www.translatum.gr/converter/greek-transliteration.htm
 * @link   http://en.wikipedia.org/wiki/Royal_Thai_General_System_of_Transcription
 * @link   http://www.btranslations.com/resources/romanization/korean.asp
 * @author Arthit Suriyawongkul <arthit@gmail.com>
 * @author Denis Scheither <amorphis@uni-bremen.de>
 */
global $UTF8_ROMANIZATION;
$UTF8_ROMANIZATION = array(
  //russian cyrillic
  'а'=>'a','А'=>'A','б'=>'b','Б'=>'B','в'=>'v','В'=>'V','г'=>'g','Г'=>'G',
  'д'=>'d','Д'=>'D','е'=>'e','Е'=>'E','ё'=>'jo','Ё'=>'Jo','ж'=>'zh','Ж'=>'Zh',
  'з'=>'z','З'=>'Z','и'=>'i','И'=>'I','й'=>'j','Й'=>'J','к'=>'k','К'=>'K',
  'л'=>'l','Л'=>'L','м'=>'m','М'=>'M','н'=>'n','Н'=>'N','о'=>'o','О'=>'O',
  'п'=>'p','П'=>'P','р'=>'r','Р'=>'R','с'=>'s','С'=>'S','т'=>'t','Т'=>'T',
  'у'=>'u','У'=>'U','ф'=>'f','Ф'=>'F','х'=>'x','Х'=>'X','ц'=>'c','Ц'=>'C',
  'ч'=>'ch','Ч'=>'Ch','ш'=>'sh','Ш'=>'Sh','щ'=>'sch','Щ'=>'Sch','ъ'=>'',
  'Ъ'=>'','ы'=>'y','Ы'=>'Y','ь'=>'','Ь'=>'','э'=>'eh','Э'=>'Eh','ю'=>'ju',
  'Ю'=>'Ju','я'=>'ja','Я'=>'Ja',
  // Ukrainian cyrillic
  'Ґ'=>'Gh','ґ'=>'gh','Є'=>'Je','є'=>'je','І'=>'I','і'=>'i','Ї'=>'Ji','ї'=>'ji',
  // Georgian
  'ა'=>'a','ბ'=>'b','გ'=>'g','დ'=>'d','ე'=>'e','ვ'=>'v','ზ'=>'z','თ'=>'th',
  'ი'=>'i','კ'=>'p','ლ'=>'l','მ'=>'m','ნ'=>'n','ო'=>'o','პ'=>'p','ჟ'=>'zh',
  'რ'=>'r','ს'=>'s','ტ'=>'t','უ'=>'u','ფ'=>'ph','ქ'=>'kh','ღ'=>'gh','ყ'=>'q',
  'შ'=>'sh','ჩ'=>'ch','ც'=>'c','ძ'=>'dh','წ'=>'w','ჭ'=>'j','ხ'=>'x','ჯ'=>'jh',
  'ჰ'=>'xh',
  //Sanskrit
  'अ'=>'a','आ'=>'ah','इ'=>'i','ई'=>'ih','उ'=>'u','ऊ'=>'uh','ऋ'=>'ry',
  'ॠ'=>'ryh','ऌ'=>'ly','ॡ'=>'lyh','ए'=>'e','ऐ'=>'ay','ओ'=>'o','औ'=>'aw',
  'अं'=>'amh','अः'=>'aq','क'=>'k','ख'=>'kh','ग'=>'g','घ'=>'gh','ङ'=>'nh',
  'च'=>'c','छ'=>'ch','ज'=>'j','झ'=>'jh','ञ'=>'ny','ट'=>'tq','ठ'=>'tqh',
  'ड'=>'dq','ढ'=>'dqh','ण'=>'nq','त'=>'t','थ'=>'th','द'=>'d','ध'=>'dh',
  'न'=>'n','प'=>'p','फ'=>'ph','ब'=>'b','भ'=>'bh','म'=>'m','य'=>'z','र'=>'r',
  'ल'=>'l','व'=>'v','श'=>'sh','ष'=>'sqh','स'=>'s','ह'=>'x',
  //Hebrew
  'א'=>'a', 'ב'=>'b','ג'=>'g','ד'=>'d','ה'=>'h','ו'=>'v','ז'=>'z','ח'=>'kh','ט'=>'th',
  'י'=>'y','ך'=>'h','כ'=>'k','ל'=>'l','ם'=>'m','מ'=>'m','ן'=>'n','נ'=>'n',
  'ס'=>'s','ע'=>'ah','ף'=>'f','פ'=>'p','ץ'=>'c','צ'=>'c','ק'=>'q','ר'=>'r',
  'ש'=>'sh','ת'=>'t',
  //Arabic
  'ا'=>'a','ب'=>'b','ت'=>'t','ث'=>'th','ج'=>'g','ح'=>'xh','خ'=>'x','د'=>'d',
  'ذ'=>'dh','ر'=>'r','ز'=>'z','س'=>'s','ش'=>'sh','ص'=>'s\'','ض'=>'d\'',
  'ط'=>'t\'','ظ'=>'z\'','ع'=>'y','غ'=>'gh','ف'=>'f','ق'=>'q','ك'=>'k',
  'ل'=>'l','م'=>'m','ن'=>'n','ه'=>'x\'','و'=>'u','ي'=>'i',

  // Japanese hiragana

  // 3 character syllables, っ doubles the consonant after
  'っちゃ'=>'ccha','っちぇ'=>'cche','っちょ'=>'ccho','っちゅ'=>'cchu',
  'っびゃ'=>'bya','っびぇ'=>'bye','っびぃ'=>'byi','っびょ'=>'byo','っびゅ'=>'byu',
  'っちゃ'=>'cha','っちぇ'=>'che','っち'=>'chi','っちょ'=>'cho','っちゅ'=>'chu',
  'っひゃ'=>'hya','っひぇ'=>'hye','っひぃ'=>'hyi','っひょ'=>'hyo','っひゅ'=>'hyu',
  'っきゃ'=>'kya','っきぇ'=>'kye','っきぃ'=>'kyi','っきょ'=>'kyo','っきゅ'=>'kyu',
  'っぎゃ'=>'gya','っぎぇ'=>'gye','っぎぃ'=>'gyi','っぎょ'=>'gyo','っぎゅ'=>'gyu',
  'っみゃ'=>'mya','っみぇ'=>'mye','っみぃ'=>'myi','っみょ'=>'myo','っみゅ'=>'myu',
  'っにゃ'=>'nya','っにぇ'=>'nye','っにぃ'=>'nyi','っにょ'=>'nyo','っにゅ'=>'nyu',
  'っりゃ'=>'rya','っりぇ'=>'rye','っりぃ'=>'ryi','っりょ'=>'ryo','っりゅ'=>'ryu',
  'っしゃ'=>'sha','っしぇ'=>'she','っし'=>'shi','っしょ'=>'sho','っしゅ'=>'shu',
  
   // 2 character syllables - normal
  'ふぁ'=>'fa','ふぇ'=>'fe','ふぃ'=>'fi','ふぉ'=>'fo','ふ'=>'fu',
  'ヴぁ'=>'va','ヴぇ'=>'ve','ヴぃ'=>'vi','ヴぉ'=>'vo','ヴ'=>'vu',
  'びゃ'=>'bya','びぇ'=>'bye','びぃ'=>'byi','びょ'=>'byo','びゅ'=>'byu',
  'ちゃ'=>'cha','ちぇ'=>'che','ち'=>'chi','ちょ'=>'cho','ちゅ'=>'chu',
  'ひゃ'=>'hya','ひぇ'=>'hye','ひぃ'=>'hyi','ひょ'=>'hyo','ひゅ'=>'hyu',
  'きゃ'=>'kya','きぇ'=>'kye','きぃ'=>'kyi','きょ'=>'kyo','きゅ'=>'kyu',
  'ぎゃ'=>'gya','ぎぇ'=>'gye','ぎぃ'=>'gyi','ぎょ'=>'gyo','ぎゅ'=>'gyu',
  'みゃ'=>'mya','みぇ'=>'mye','みぃ'=>'myi','みょ'=>'myo','みゅ'=>'myu',
  'にゃ'=>'nya','にぇ'=>'nye','にぃ'=>'nyi','にょ'=>'nyo','にゅ'=>'nyu',
  'りゃ'=>'rya','りぇ'=>'rye','りぃ'=>'ryi','りょ'=>'ryo','りゅ'=>'ryu',
  'しゃ'=>'sha','しぇ'=>'she','し'=>'shi','しょ'=>'sho','しゅ'=>'shu',
  'じゃ'=>'ja','じぇ'=>'je','じ'=>'ji','じょ'=>'jo','じゅ'=>'ju',

  // 2 character syllables, っ doubles the consonant after
  'っば'=>'bba','っべ'=>'bbe','っび'=>'bbi','っぼ'=>'bbo','っぶ'=>'bbu',
  'っぱ'=>'ppa','っぺ'=>'ppe','っぴ'=>'ppi','っぽ'=>'ppo','っぷ'=>'ppu',
  'った'=>'tta','って'=>'tte','っち'=>'cchi','っと'=>'tto','っつ'=>'ttsu',
  'っだ'=>'dda','っで'=>'dde','っぢ'=>'ddi','っど'=>'ddo','っづ'=>'ddu',
  'っが'=>'gga','っげ'=>'gge','っぎ'=>'ggi','っご'=>'ggo','っぐ'=>'ggu',
  'っか'=>'kka','っけ'=>'kke','っき'=>'kki','っこ'=>'kko','っく'=>'kku',
  'っま'=>'mma','っめ'=>'mme','っみ'=>'mmi','っも'=>'mmo','っむ'=>'mmu',
  'っな'=>'nna','っね'=>'nne','っに'=>'nni','っの'=>'nno','っぬ'=>'nnu',
  'っら'=>'rra','っれ'=>'rre','っり'=>'rri','っろ'=>'rro','っる'=>'rru',
  'っさ'=>'ssa','っせ'=>'sse','っし'=>'sshi','っそ'=>'sso','っす'=>'ssu',
  'っざ'=>'zza','っぜ'=>'zze','っじ'=>'zzi','っぞ'=>'zzo','っず'=>'zzu',
  
  // 1 character syllabels
  'あ'=>'a','え'=>'e','い'=>'i','お'=>'o','う'=>'u','ん'=>'n',
  'は'=>'ha','へ'=>'he','ひ'=>'hi','ほ'=>'ho','ふ'=>'hu',
  'ば'=>'ba','べ'=>'be','び'=>'bi','ぼ'=>'bo','ぶ'=>'bu',
  'ぱ'=>'pa','ぺ'=>'pe','ぴ'=>'pi','ぽ'=>'po','ぷ'=>'pu',
  'た'=>'ta','て'=>'te','ち'=>'ti','と'=>'to','つ'=>'tu',
  'だ'=>'da','で'=>'de','ぢ'=>'di','ど'=>'do','づ'=>'du',
  'が'=>'ga','げ'=>'ge','ぎ'=>'gi','ご'=>'go','ぐ'=>'gu',
  'か'=>'ka','け'=>'ke','き'=>'ki','こ'=>'ko','く'=>'ku',
  'ま'=>'ma','め'=>'me','み'=>'mi','も'=>'mo','む'=>'mu',
  'な'=>'na','ね'=>'ne','に'=>'ni','の'=>'no','ぬ'=>'nu',
  'ら'=>'ra','れ'=>'re','り'=>'ri','ろ'=>'ro','る'=>'ru',
  'さ'=>'sa','せ'=>'se','し'=>'shi','そ'=>'so','す'=>'su',
  'わ'=>'wa','うぇ'=>'we','うぃ'=>'wi','を'=>'wo',
  'ざ'=>'za','ぜ'=>'ze','じ'=>'zi','ぞ'=>'zo','ず'=>'zu',
  'や'=>'ya','いぇ'=>'ye','よ'=>'yo','ゆ'=>'yu',

  // never seen one of those, but better save than sorry
  'でゃ'=>'dha','でぇ'=>'dhe','でぃ'=>'dhi','でょ'=>'dho','でゅ'=>'dhu',
  'どぁ'=>'dwa','どぇ'=>'dwe','どぃ'=>'dwi','どぉ'=>'dwo','どぅ'=>'dwu',
  'ぢゃ'=>'dya','ぢぇ'=>'dye','ぢぃ'=>'dyi','ぢょ'=>'dyo','ぢゅ'=>'dyu',
  'ふぁ'=>'fwa','ふぇ'=>'fwe','ふぃ'=>'fwi','ふぉ'=>'fwo','ふぅ'=>'fwu',
  'ふゃ'=>'fya','ふぇ'=>'fye','ふぃ'=>'fyi','ふょ'=>'fyo','ふゅ'=>'fyu',
  'ぴゃ'=>'pya','ぴぇ'=>'pye','ぴぃ'=>'pyi','ぴょ'=>'pyo','ぴゅ'=>'pyu',
  'すぁ'=>'swa','すぇ'=>'swe','すぃ'=>'swi','すぉ'=>'swo','すぅ'=>'swu',
  'てゃ'=>'tha','てぇ'=>'the','てぃ'=>'thi','てょ'=>'tho','てゅ'=>'thu',
  'つゃ'=>'tsa','つぇ'=>'tse','つぃ'=>'tsi','つょ'=>'tso','つ'=>'tsu',
  'とぁ'=>'twa','とぇ'=>'twe','とぃ'=>'twi','とぉ'=>'two','とぅ'=>'twu',
  'ヴゃ'=>'vya','ヴぇ'=>'vye','ヴぃ'=>'vyi','ヴょ'=>'vyo','ヴゅ'=>'vyu',
  'うぁ'=>'wha','うぇ'=>'whe','うぃ'=>'whi','うぉ'=>'who','うぅ'=>'whu',
  'ゑ'=>'wye','ゐ'=>'wyi',
  'じゃ'=>'zha','じぇ'=>'zhe','じぃ'=>'zhi','じょ'=>'zho','じゅ'=>'zhu',
  'じゃ'=>'zya','じぇ'=>'zye','じぃ'=>'zyi','じょ'=>'zyo','じゅ'=>'zyu',

  //  convert what's left (probably only kicks in when something's missing above
  'ぁ'=>'a','ぇ'=>'e','ぃ'=>'i','ぉ'=>'o','ぅ'=>'u',
  'ゃ'=>'ya','ょ'=>'yo','ゅ'=>'yu',

  // 'spare' characters from other romanization systems
  // 'だ'=>'da','で'=>'de','ぢ'=>'di','ど'=>'do','づ'=>'du',
  // 'ら'=>'la','れ'=>'le','り'=>'li','ろ'=>'lo','る'=>'lu',
  // 'さ'=>'sa','せ'=>'se','し'=>'si','そ'=>'so','す'=>'su',
  // 'ちゃ'=>'cya','ちぇ'=>'cye','ちぃ'=>'cyi','ちょ'=>'cyo','ちゅ'=>'cyu',
  //'じゃ'=>'jya','じぇ'=>'jye','じぃ'=>'jyi','じょ'=>'jyo','じゅ'=>'jyu',
  //'りゃ'=>'lya','りぇ'=>'lye','りぃ'=>'lyi','りょ'=>'lyo','りゅ'=>'lyu',
  //'しゃ'=>'sya','しぇ'=>'sye','しぃ'=>'syi','しょ'=>'syo','しゅ'=>'syu',
  //'ちゃ'=>'tya','ちぇ'=>'tye','ちぃ'=>'tyi','ちょ'=>'tyo','ちゅ'=>'tyu',
  //'し'=>'ci',,い'=>'yi','ぢ'=>'dzi',
  //'っじゃ'=>'jja','っじぇ'=>'jje','っじ'=>'jji','っじょ'=>'jjo','っじゅ'=>'jju',


  // Japanese katakana

  // 4 character syllables: ッ doubles the consonant after, ー doubles the vowel before (usualy written with macron, but we don't want that in our URLs)
  'ッビャー'=>'bbyaa','ッビェー'=>'bbyee','ッビィー'=>'bbyii','ッビョー'=>'bbyoo','ッビュー'=>'bbyuu',
  'ッピャー'=>'ppyaa','ッピェー'=>'ppyee','ッピィー'=>'ppyii','ッピョー'=>'ppyoo','ッピュー'=>'ppyuu',
  'ッキャー'=>'kkyaa','ッキェー'=>'kkyee','ッキィー'=>'kkyii','ッキョー'=>'kkyoo','ッキュー'=>'kkyuu',
  'ッギャー'=>'ggyaa','ッギェー'=>'ggyee','ッギィー'=>'ggyii','ッギョー'=>'ggyoo','ッギュー'=>'ggyuu',
  'ッミャー'=>'mmyaa','ッミェー'=>'mmyee','ッミィー'=>'mmyii','ッミョー'=>'mmyoo','ッミュー'=>'mmyuu',
  'ッニャー'=>'nnyaa','ッニェー'=>'nnyee','ッニィー'=>'nnyii','ッニョー'=>'nnyoo','ッニュー'=>'nnyuu',
  'ッリャー'=>'rryaa','ッリェー'=>'rryee','ッリィー'=>'rryii','ッリョー'=>'rryoo','ッリュー'=>'rryuu',
  'ッシャー'=>'sshaa','ッシェー'=>'sshee','ッシー'=>'sshii','ッショー'=>'sshoo','ッシュー'=>'sshuu',
  'ッチャー'=>'cchaa','ッチェー'=>'cchee','ッチー'=>'cchii','ッチョー'=>'cchoo','ッチュー'=>'cchuu',

  // 3 character syllables - doubled vowels
  'ファー'=>'faa','フェー'=>'fee','フィー'=>'fii','フォー'=>'foo',
  'フャー'=>'fyaa','フェー'=>'fyee','フィー'=>'fyii','フョー'=>'fyoo','フュー'=>'fyuu',
  'ヒャー'=>'hyaa','ヒェー'=>'hyee','ヒィー'=>'hyii','ヒョー'=>'hyoo','ヒュー'=>'hyuu',
  'ビャー'=>'byaa','ビェー'=>'byee','ビィー'=>'byii','ビョー'=>'byoo','ビュー'=>'byuu',
  'ピャー'=>'pyaa','ピェー'=>'pyee','ピィー'=>'pyii','ピョー'=>'pyoo','ピュー'=>'pyuu',
  'キャー'=>'kyaa','キェー'=>'kyee','キィー'=>'kyii','キョー'=>'kyoo','キュー'=>'kyuu',
  'ギャー'=>'gyaa','ギェー'=>'gyee','ギィー'=>'gyii','ギョー'=>'gyoo','ギュー'=>'gyuu',
  'ミャー'=>'myaa','ミェー'=>'myee','ミィー'=>'myii','ミョー'=>'myoo','ミュー'=>'myuu',
  'ニャー'=>'nyaa','ニェー'=>'nyee','ニィー'=>'nyii','ニョー'=>'nyoo','ニュー'=>'nyuu',
  'リャー'=>'ryaa','リェー'=>'ryee','リィー'=>'ryii','リョー'=>'ryoo','リュー'=>'ryuu',
  'シャー'=>'shaa','シェー'=>'shee','シー'=>'shii','ショー'=>'shoo','シュー'=>'shuu',
  'ジャー'=>'jaa','ジェー'=>'jee','ジー'=>'jii','ジョー'=>'joo','ジュー'=>'juu',
  'スァー'=>'swaa','スェー'=>'swee','スィー'=>'swii','スォー'=>'swoo','スゥー'=>'swuu',
  'デァー'=>'daa','デェー'=>'dee','ディー'=>'dii','デォー'=>'doo','デゥー'=>'duu',
  'チャー'=>'chaa','チェー'=>'chee','チー'=>'chii','チョー'=>'choo','チュー'=>'chuu',
  'ヂャー'=>'dyaa','ヂェー'=>'dyee','ヂィー'=>'dyii','ヂョー'=>'dyoo','ヂュー'=>'dyuu',
  'ツャー'=>'tsaa','ツェー'=>'tsee','ツィー'=>'tsii','ツョー'=>'tsoo','ツー'=>'tsuu',
  'トァー'=>'twaa','トェー'=>'twee','トィー'=>'twii','トォー'=>'twoo','トゥー'=>'twuu',
  'ドァー'=>'dwaa','ドェー'=>'dwee','ドィー'=>'dwii','ドォー'=>'dwoo','ドゥー'=>'dwuu',
  'ウァー'=>'whaa','ウェー'=>'whee','ウィー'=>'whii','ウォー'=>'whoo','ウゥー'=>'whuu',
  'ヴャー'=>'vyaa','ヴェー'=>'vyee','ヴィー'=>'vyii','ヴョー'=>'vyoo','ヴュー'=>'vyuu',
  'ヴァー'=>'vaa','ヴェー'=>'vee','ヴィー'=>'vii','ヴォー'=>'voo','ヴー'=>'vuu',
  'ウェー'=>'wee','ウィー'=>'wii',
  'イェー'=>'yee',

  // 3 character syllables - doubled consonants
  'ッビャ'=>'bbya','ッビェ'=>'bbye','ッビィ'=>'bbyi','ッビョ'=>'bbyo','ッビュ'=>'bbyu',
  'ッピャ'=>'ppya','ッピェ'=>'ppye','ッピィ'=>'ppyi','ッピョ'=>'ppyo','ッピュ'=>'ppyu',
  'ッキャ'=>'kkya','ッキェ'=>'kkye','ッキィ'=>'kkyi','ッキョ'=>'kkyo','ッキュ'=>'kkyu',
  'ッギャ'=>'ggya','ッギェ'=>'ggye','ッギィ'=>'ggyi','ッギョ'=>'ggyo','ッギュ'=>'ggyu',
  'ッミャ'=>'mmya','ッミェ'=>'mmye','ッミィ'=>'mmyi','ッミョ'=>'mmyo','ッミュ'=>'mmyu',
  'ッニャ'=>'nnya','ッニェ'=>'nnye','ッニィ'=>'nnyi','ッニョ'=>'nnyo','ッニュ'=>'nnyu',
  'ッリャ'=>'rrya','ッリェ'=>'rrye','ッリィ'=>'rryi','ッリョ'=>'rryo','ッリュ'=>'rryu',
  'ッシャ'=>'ssha','ッシェ'=>'sshe','ッシ'=>'sshi','ッショ'=>'ssho','ッシュ'=>'sshu',
  'ッチャ'=>'ccha','ッチェ'=>'cche','ッチ'=>'cchi','ッチョ'=>'ccho','ッチュ'=>'cchu',

  // 3 character syllables - doubled vowel and consonants
  'ッバー'=>'bbaa','ッベー'=>'bbee','ッビー'=>'bbii','ッボー'=>'bboo','ッブー'=>'bbuu',
  'ッパー'=>'ppaa','ッペー'=>'ppee','ッピー'=>'ppii','ッポー'=>'ppoo','ップー'=>'ppuu',
  'ッケー'=>'kkee','ッキー'=>'kkii','ッコー'=>'kkoo','ックー'=>'kkuu','ッカー'=>'kkaa',
  'ッガー'=>'ggaa','ッゲー'=>'ggee','ッギー'=>'ggii','ッゴー'=>'ggoo','ッグー'=>'gguu',
  'ッマー'=>'maa','ッメー'=>'mee','ッミー'=>'mii','ッモー'=>'moo','ッムー'=>'muu',
  'ッナー'=>'nnaa','ッネー'=>'nnee','ッニー'=>'nnii','ッノー'=>'nnoo','ッヌー'=>'nnuu',
  'ッラー'=>'rraa','ッレー'=>'rree','ッリー'=>'rrii','ッロー'=>'rroo','ッルー'=>'rruu',
  'ッサー'=>'ssaa','ッセー'=>'ssee','ッシー'=>'sshii','ッソー'=>'ssoo','ッスー'=>'ssuu',
  'ッザー'=>'zzaa','ッゼー'=>'zzee','ッジー'=>'zzii','ッゾー'=>'zzoo','ッズー'=>'zzuu',
  'ッター'=>'ttaa','ッテー'=>'ttee','ッチー'=>'chii','ットー'=>'ttoo','ッツー'=>'ttssuu',
  'ッダー'=>'ddaa','ッデー'=>'ddee','ッヂー'=>'ddii','ッドー'=>'ddoo','ッヅー'=>'dduu',

  // 2 character syllables - normal
  'ファ'=>'fa','フェ'=>'fe','フィ'=>'fi','フォ'=>'fo',
  'フャ'=>'fya','フェ'=>'fye','フィ'=>'fyi','フョ'=>'fyo','フュ'=>'fyu',
  'ヒャ'=>'hya','ヒェ'=>'hye','ヒィ'=>'hyi','ヒョ'=>'hyo','ヒュ'=>'hyu',
  'ビャ'=>'bya','ビェ'=>'bye','ビィ'=>'byi','ビョ'=>'byo','ビュ'=>'byu',
  'ピャ'=>'pya','ピェ'=>'pye','ピィ'=>'pyi','ピョ'=>'pyo','ピュ'=>'pyu',
  'キャ'=>'kya','キェ'=>'kye','キィ'=>'kyi','キョ'=>'kyo','キュ'=>'kyu',
  'ギャ'=>'gya','ギェ'=>'gye','ギィ'=>'gyi','ギョ'=>'gyo','ギュ'=>'gyu',
  'ミャ'=>'mya','ミェ'=>'mye','ミィ'=>'myi','ミョ'=>'myo','ミュ'=>'myu',
  'ニャ'=>'nya','ニェ'=>'nye','ニィ'=>'nyi','ニョ'=>'nyo','ニュ'=>'nyu',
  'リャ'=>'rya','リェ'=>'rye','リィ'=>'ryi','リョ'=>'ryo','リュ'=>'ryu',
  'シャ'=>'sha','シェ'=>'she','シ'=>'shi','ショ'=>'sho','シュ'=>'shu',
  'ジャ'=>'ja','ジェ'=>'je','ジ'=>'ji','ジョ'=>'jo','ジュ'=>'ju',
  'スァ'=>'swa','スェ'=>'swe','スィ'=>'swi','スォ'=>'swo','スゥ'=>'swu',
  'デァ'=>'da','デェ'=>'de','ディ'=>'di','デォ'=>'do','デゥ'=>'du',
  'チャ'=>'cha','チェ'=>'che','チ'=>'chi','チョ'=>'cho','チュ'=>'chu',
  'ヂャ'=>'dya','ヂェ'=>'dye','ヂィ'=>'dyi','ヂョ'=>'dyo','ヂュ'=>'dyu',
  'ツャ'=>'tsa','ツェ'=>'tse','ツィ'=>'tsi','ツョ'=>'tso','ツ'=>'tsu',
  'トァ'=>'twa','トェ'=>'twe','トィ'=>'twi','トォ'=>'two','トゥ'=>'twu',
  'ドァ'=>'dwa','ドェ'=>'dwe','ドィ'=>'dwi','ドォ'=>'dwo','ドゥ'=>'dwu',
  'ウァ'=>'wha','ウェ'=>'whe','ウィ'=>'whi','ウォ'=>'who','ウゥ'=>'whu',
  'ヴャ'=>'vya','ヴェ'=>'vye','ヴィ'=>'vyi','ヴョ'=>'vyo','ヴュ'=>'vyu',
  'ヴァ'=>'va','ヴェ'=>'ve','ヴィ'=>'vi','ヴォ'=>'vo','ヴ'=>'vu',
  'ウェ'=>'we','ウィ'=>'wi',
  'イェ'=>'ye',

  // 2 character syllables - doubled vocal
  'アー'=>'aa','エー'=>'ee','イー'=>'ii','オー'=>'oo','ウー'=>'uu',
  'ダー'=>'daa','デー'=>'dee','ヂー'=>'dii','ドー'=>'doo','ヅー'=>'duu',
  'ハー'=>'haa','ヘー'=>'hee','ヒー'=>'hii','ホー'=>'hoo','フー'=>'fuu',
  'バー'=>'baa','ベー'=>'bee','ビー'=>'bii','ボー'=>'boo','ブー'=>'buu',
  'パー'=>'paa','ペー'=>'pee','ピー'=>'pii','ポー'=>'poo','プー'=>'puu',
  'ケー'=>'kee','キー'=>'kii','コー'=>'koo','クー'=>'kuu','カー'=>'kaa',
  'ガー'=>'gaa','ゲー'=>'gee','ギー'=>'gii','ゴー'=>'goo','グー'=>'guu',
  'マー'=>'maa','メー'=>'mee','ミー'=>'mii','モー'=>'moo','ムー'=>'muu',
  'ナー'=>'naa','ネー'=>'nee','ニー'=>'nii','ノー'=>'noo','ヌー'=>'nuu',
  'ラー'=>'raa','レー'=>'ree','リー'=>'rii','ロー'=>'roo','ルー'=>'ruu',
  'サー'=>'saa','セー'=>'see','シー'=>'shii','ソー'=>'soo','スー'=>'suu',
  'ザー'=>'zaa','ゼー'=>'zee','ジー'=>'zii','ゾー'=>'zoo','ズー'=>'zuu',
  'ター'=>'taa','テー'=>'tee','チー'=>'chii','トー'=>'too','ツー'=>'tsuu',
  'ワー'=>'waa','ヲー'=>'woo',
  'ヤー'=>'yaa','ヨー'=>'yoo','ユー'=>'yuu',
  'ヱー'=>'wyee','ヰー'=>'wyii',
  'ヵー'=>'kaa','ヶー'=>'kee',

  // 2 character syllables - doubled consonants
  'ッバ'=>'bba','ッベ'=>'bbe','ッビ'=>'bbi','ッボ'=>'bbo','ッブ'=>'bbu',
  'ッパ'=>'ppa','ッペ'=>'ppe','ッピ'=>'ppi','ッポ'=>'ppo','ップ'=>'ppu',
  'ッケ'=>'kke','ッキ'=>'kki','ッコ'=>'kko','ック'=>'kku','ッカ'=>'kka',
  'ッガ'=>'gga','ッゲ'=>'gge','ッギ'=>'ggi','ッゴ'=>'ggo','ッグ'=>'ggu',
  'ッマ'=>'ma','ッメ'=>'me','ッミ'=>'mi','ッモ'=>'mo','ッム'=>'mu',
  'ッナ'=>'nna','ッネ'=>'nne','ッニ'=>'nni','ッノ'=>'nno','ッヌ'=>'nnu',
  'ッラ'=>'rra','ッレ'=>'rre','ッリ'=>'rri','ッロ'=>'rro','ッル'=>'rru',
  'ッサ'=>'ssa','ッセ'=>'sse','ッシ'=>'sshi','ッソ'=>'sso','ッス'=>'ssu',
  'ッザ'=>'zza','ッゼ'=>'zze','ッジ'=>'zzi','ッゾ'=>'zzo','ッズ'=>'zzu',
  'ッタ'=>'tta','ッテ'=>'tte','ッチ'=>'chi','ット'=>'tto','ッツ'=>'ttssu',
  'ッダ'=>'dda','ッデ'=>'dde','ッヂ'=>'ddi','ッド'=>'ddo','ッヅ'=>'ddu',

  // 1 character syllables
  'ア'=>'a','エ'=>'e','イ'=>'i','オ'=>'o','ウ'=>'u','ン'=>'n',
  'ハ'=>'ha','ヘ'=>'he','ヒ'=>'hi','ホ'=>'ho','フ'=>'fu',
  'バ'=>'ba','ベ'=>'be','ビ'=>'bi','ボ'=>'bo','ブ'=>'bu',
  'パ'=>'pa','ペ'=>'pe','ピ'=>'pi','ポ'=>'po','プ'=>'pu',
  'ケ'=>'ke','キ'=>'ki','コ'=>'ko','ク'=>'ku','カ'=>'ka',
  'ガ'=>'ga','ゲ'=>'ge','ギ'=>'gi','ゴ'=>'go','グ'=>'gu',
  'マ'=>'ma','メ'=>'me','ミ'=>'mi','モ'=>'mo','ム'=>'mu',
  'ナ'=>'na','ネ'=>'ne','ニ'=>'ni','ノ'=>'no','ヌ'=>'nu',
  'ラ'=>'ra','レ'=>'re','リ'=>'ri','ロ'=>'ro','ル'=>'ru',
  'サ'=>'sa','セ'=>'se','シ'=>'shi','ソ'=>'so','ス'=>'su',
  'ザ'=>'za','ゼ'=>'ze','ジ'=>'zi','ゾ'=>'zo','ズ'=>'zu',
  'タ'=>'ta','テ'=>'te','チ'=>'chi','ト'=>'to','ツ'=>'tsu',
  'ダ'=>'da','デ'=>'de','ヂ'=>'di','ド'=>'do','ヅ'=>'du',
  'ワ'=>'wa','ヲ'=>'wo',
  'ヤ'=>'ya','ヨ'=>'yo','ユ'=>'yu',
  'ヱ'=>'wye','ヰ'=>'wyi',
  'ヵ'=>'ka','ヶ'=>'ke',

  //  convert what's left (probably only kicks in when something's missing above
  'ァ'=>'a','ェ'=>'e','ィ'=>'i','ォ'=>'o','ゥ'=>'u',
  'ャ'=>'ya','ョ'=>'yo','ュ'=>'yu',

  // 'ラ'=>'la','レ'=>'le','リ'=>'li','ロ'=>'lo','ル'=>'lu',
  // 'チャ'=>'cya','チェ'=>'cye','チィ'=>'cyi','チョ'=>'cyo','チュ'=>'cyu',
  //'デャ'=>'dha','デェ'=>'dhe','ディ'=>'dhi','デョ'=>'dho','デュ'=>'dhu',
  // 'リャ'=>'lya','リェ'=>'lye','リィ'=>'lyi','リョ'=>'lyo','リュ'=>'lyu',
  // 'テャ'=>'tha','テェ'=>'the','ティ'=>'thi','テョ'=>'tho','テュ'=>'thu',
  //'ファ'=>'fwa','フェ'=>'fwe','フィ'=>'fwi','フォ'=>'fwo','フゥ'=>'fwu',
  //'チャ'=>'tya','チェ'=>'tye','チィ'=>'tyi','チョ'=>'tyo','チュ'=>'tyu',
  // 'ジャ'=>'jya','ジェ'=>'jye','ジィ'=>'jyi','ジョ'=>'jyo','ジュ'=>'jyu',
  // 'ジャ'=>'zha','ジェ'=>'zhe','ジィ'=>'zhi','ジョ'=>'zho','ジュ'=>'zhu',
  //'ジャ'=>'zya','ジェ'=>'zye','ジィ'=>'zyi','ジョ'=>'zyo','ジュ'=>'zyu',
  //'シャ'=>'sya','シェ'=>'sye','シィ'=>'syi','ショ'=>'syo','シュ'=>'syu',
  //'シ'=>'ci','フ'=>'hu',シ'=>'si','チ'=>'ti','ツ'=>'tu','イ'=>'yi','ヂ'=>'dzi',

  // "Greeklish"
  'Γ'=>'G','Δ'=>'E','Θ'=>'Th','Λ'=>'L','Ξ'=>'X','Π'=>'P','Σ'=>'S','Φ'=>'F','Ψ'=>'Ps',
  'γ'=>'g','δ'=>'e','θ'=>'th','λ'=>'l','ξ'=>'x','π'=>'p','σ'=>'s','φ'=>'f','ψ'=>'ps',

  // Thai
  'ก'=>'k','ข'=>'kh','ฃ'=>'kh','ค'=>'kh','ฅ'=>'kh','ฆ'=>'kh','ง'=>'ng','จ'=>'ch',
  'ฉ'=>'ch','ช'=>'ch','ซ'=>'s','ฌ'=>'ch','ญ'=>'y','ฎ'=>'d','ฏ'=>'t','ฐ'=>'th',
  'ฑ'=>'d','ฒ'=>'th','ณ'=>'n','ด'=>'d','ต'=>'t','ถ'=>'th','ท'=>'th','ธ'=>'th',
  'น'=>'n','บ'=>'b','ป'=>'p','ผ'=>'ph','ฝ'=>'f','พ'=>'ph','ฟ'=>'f','ภ'=>'ph',
  'ม'=>'m','ย'=>'y','ร'=>'r','ฤ'=>'rue','ฤๅ'=>'rue','ล'=>'l','ฦ'=>'lue',
  'ฦๅ'=>'lue','ว'=>'w','ศ'=>'s','ษ'=>'s','ส'=>'s','ห'=>'h','ฬ'=>'l','ฮ'=>'h',
  'ะ'=>'a','ั'=>'a','รร'=>'a','า'=>'a','ๅ'=>'a','ำ'=>'am','ํา'=>'am',
  'ิ'=>'i','ี'=>'i','ึ'=>'ue','ี'=>'ue','ุ'=>'u','ู'=>'u',
  'เ'=>'e','แ'=>'ae','โ'=>'o','อ'=>'o',
  'ียะ'=>'ia','ีย'=>'ia','ือะ'=>'uea','ือ'=>'uea','ัวะ'=>'ua','ัว'=>'ua',
  'ใ'=>'ai','ไ'=>'ai','ัย'=>'ai','าย'=>'ai','าว'=>'ao',
  'ุย'=>'ui','อย'=>'oi','ือย'=>'ueai','วย'=>'uai',
  'ิว'=>'io','็ว'=>'eo','ียว'=>'iao',
  '่'=>'','้'=>'','๊'=>'','๋'=>'','็'=>'',
  '์'=>'','๎'=>'','ํ'=>'','ฺ'=>'',
  'ๆ'=>'2','๏'=>'o','ฯ'=>'-','๚'=>'-','๛'=>'-', 
	'๐'=>'0','๑'=>'1','๒'=>'2','๓'=>'3','๔'=>'4',
  '๕'=>'5','๖'=>'6','๗'=>'7','๘'=>'8','๙'=>'9',

  // Korean
  'ㄱ'=>'k','ㅋ'=>'kh','ㄲ'=>'kk','ㄷ'=>'t','ㅌ'=>'th','ㄸ'=>'tt','ㅂ'=>'p',
  'ㅍ'=>'ph','ㅃ'=>'pp','ㅈ'=>'c','ㅊ'=>'ch','ㅉ'=>'cc','ㅅ'=>'s','ㅆ'=>'ss',
  'ㅎ'=>'h','ㅇ'=>'ng','ㄴ'=>'n','ㄹ'=>'l','ㅁ'=>'m', 'ㅏ'=>'a','ㅓ'=>'e','ㅗ'=>'o',
  'ㅜ'=>'wu','ㅡ'=>'u','ㅣ'=>'i','ㅐ'=>'ay','ㅔ'=>'ey','ㅚ'=>'oy','ㅘ'=>'wa','ㅝ'=>'we',
  'ㅟ'=>'wi','ㅙ'=>'way','ㅞ'=>'wey','ㅢ'=>'uy','ㅑ'=>'ya','ㅕ'=>'ye','ㅛ'=>'oy',
  'ㅠ'=>'yu','ㅒ'=>'yay','ㅖ'=>'yey',
);

//Setup VIM: ex: et ts=2 enc=utf-8 :

