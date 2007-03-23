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
 * Unicode aware replacement for explode
 *
 * @TODO   support third limit arg
 * @author Harry Fuecks <hfuecks@gmail.com>
 * @see    explode();
 */
function utf8_explode($sep, $str) {
  if ( $sep == '' ) {
    trigger_error('Empty delimiter',E_USER_WARNING);
    return false;
  }

  return preg_split('!'.preg_quote($sep,'!').'!u',$str);
}

/**
 * Unicode aware replacement for strrepalce()
 *
 * @todo   support PHP5 count (fourth arg)
 * @author Harry Fuecks <hfuecks@gmail.com>
 * @see    strreplace();
 */
function utf8_str_replace($s,$r,$str){
  if(!is_array($s)){
    $s = '!'.preg_quote($s,'!').'!u';
  }else{
    foreach ($s as $k => $v) {
      $s[$k] = '!'.preg_quote($v).'!u';
    }
  }
  return preg_replace($s,$r,$str);
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

  return utf8_ltrim(utf8_rtrim($str));
}


/**
 * This is a unicode aware replacement for strtolower()
 *
 * Uses mb_string extension if available
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see    strtolower()
 * @see    utf8_strtoupper()
 */
function utf8_strtolower($string){
  if(UTF8_MBSTRING) return mb_strtolower($string,'utf-8');

  global $UTF8_UPPER_TO_LOWER;
  $uni = utf8_to_unicode($string);
  $cnt = count($uni);
  for ($i=0; $i < $cnt; $i++){
    if($UTF8_UPPER_TO_LOWER[$uni[$i]]){
      $uni[$i] = $UTF8_UPPER_TO_LOWER[$uni[$i]];
    }
  }
  return unicode_to_utf8($uni);
}

/**
 * This is a unicode aware replacement for strtoupper()
 *
 * Uses mb_string extension if available
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see    strtoupper()
 * @see    utf8_strtoupper()
 */
function utf8_strtoupper($string){
  if(UTF8_MBSTRING) return mb_strtoupper($string,'utf-8');

  global $UTF8_LOWER_TO_UPPER;
  $uni = utf8_to_unicode($string);
  $cnt = count($uni);
  for ($i=0; $i < $cnt; $i++){
    if($UTF8_LOWER_TO_UPPER[$uni[$i]]){
      $uni[$i] = $UTF8_LOWER_TO_UPPER[$uni[$i]];
    }
  }
  return unicode_to_utf8($uni);
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
    $string = str_replace(array_keys($UTF8_LOWER_ACCENTS),array_values($UTF8_LOWER_ACCENTS),$string);
  }
  if($case >= 0){
    global $UTF8_UPPER_ACCENTS;
    $string = str_replace(array_keys($UTF8_UPPER_ACCENTS),array_values($UTF8_UPPER_ACCENTS),$string);
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
 * Uses mb_string extension if available
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 * @see    strpos()
 */
function utf8_strpos($haystack, $needle,$offset=0) {
  if(UTF8_MBSTRING) return mb_strpos($haystack,$needle,$offset,'utf-8');

  if(!$offset){
    $ar = utf8_explode($needle, $haystack);
    if ( count($ar) > 1 ) {
       return utf8_strlen($ar[0]);
    }
    return false;
  }else{
    if ( !is_int($offset) ) {
      trigger_error('Offset must be an integer',E_USER_WARNING);
      return false;
    }

    $haystack = utf8_substr($haystack, $offset);

    if ( false !== ($pos = utf8_strpos($haystack,$needle))){
       return $pos + $offset;
    }
    return false;
  }
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
    0x0061=>0x0041, 0x03C6=>0x03A6, 0x0163=>0x0162, 0x00E5=>0x00C5, 0x0062=>0x0042,
    0x013A=>0x0139, 0x00E1=>0x00C1, 0x0142=>0x0141, 0x03CD=>0x038E, 0x0101=>0x0100,
    0x0491=>0x0490, 0x03B4=>0x0394, 0x015B=>0x015A, 0x0064=>0x0044, 0x03B3=>0x0393,
    0x00F4=>0x00D4, 0x044A=>0x042A, 0x0439=>0x0419, 0x0113=>0x0112, 0x043C=>0x041C,
    0x015F=>0x015E, 0x0144=>0x0143, 0x00EE=>0x00CE, 0x045E=>0x040E, 0x044F=>0x042F,
    0x03BA=>0x039A, 0x0155=>0x0154, 0x0069=>0x0049, 0x0073=>0x0053, 0x1E1F=>0x1E1E,
    0x0135=>0x0134, 0x0447=>0x0427, 0x03C0=>0x03A0, 0x0438=>0x0418, 0x00F3=>0x00D3,
    0x0440=>0x0420, 0x0454=>0x0404, 0x0435=>0x0415, 0x0449=>0x0429, 0x014B=>0x014A,
    0x0431=>0x0411, 0x0459=>0x0409, 0x1E03=>0x1E02, 0x00F6=>0x00D6, 0x00F9=>0x00D9,
    0x006E=>0x004E, 0x0451=>0x0401, 0x03C4=>0x03A4, 0x0443=>0x0423, 0x015D=>0x015C,
    0x0453=>0x0403, 0x03C8=>0x03A8, 0x0159=>0x0158, 0x0067=>0x0047, 0x00E4=>0x00C4,
    0x03AC=>0x0386, 0x03AE=>0x0389, 0x0167=>0x0166, 0x03BE=>0x039E, 0x0165=>0x0164,
    0x0117=>0x0116, 0x0109=>0x0108, 0x0076=>0x0056, 0x00FE=>0x00DE, 0x0157=>0x0156,
    0x00FA=>0x00DA, 0x1E61=>0x1E60, 0x1E83=>0x1E82, 0x00E2=>0x00C2, 0x0119=>0x0118,
    0x0146=>0x0145, 0x0070=>0x0050, 0x0151=>0x0150, 0x044E=>0x042E, 0x0129=>0x0128,
    0x03C7=>0x03A7, 0x013E=>0x013D, 0x0442=>0x0422, 0x007A=>0x005A, 0x0448=>0x0428,
    0x03C1=>0x03A1, 0x1E81=>0x1E80, 0x016D=>0x016C, 0x00F5=>0x00D5, 0x0075=>0x0055,
    0x0177=>0x0176, 0x00FC=>0x00DC, 0x1E57=>0x1E56, 0x03C3=>0x03A3, 0x043A=>0x041A,
    0x006D=>0x004D, 0x016B=>0x016A, 0x0171=>0x0170, 0x0444=>0x0424, 0x00EC=>0x00CC,
    0x0169=>0x0168, 0x03BF=>0x039F, 0x006B=>0x004B, 0x00F2=>0x00D2, 0x00E0=>0x00C0,
    0x0434=>0x0414, 0x03C9=>0x03A9, 0x1E6B=>0x1E6A, 0x00E3=>0x00C3, 0x044D=>0x042D,
    0x0436=>0x0416, 0x01A1=>0x01A0, 0x010D=>0x010C, 0x011D=>0x011C, 0x00F0=>0x00D0,
    0x013C=>0x013B, 0x045F=>0x040F, 0x045A=>0x040A, 0x00E8=>0x00C8, 0x03C5=>0x03A5,
    0x0066=>0x0046, 0x00FD=>0x00DD, 0x0063=>0x0043, 0x021B=>0x021A, 0x00EA=>0x00CA,
    0x03B9=>0x0399, 0x017A=>0x0179, 0x00EF=>0x00CF, 0x01B0=>0x01AF, 0x0065=>0x0045,
    0x03BB=>0x039B, 0x03B8=>0x0398, 0x03BC=>0x039C, 0x045C=>0x040C, 0x043F=>0x041F,
    0x044C=>0x042C, 0x00FE=>0x00DE, 0x00F0=>0x00D0, 0x1EF3=>0x1EF2, 0x0068=>0x0048,
    0x00EB=>0x00CB, 0x0111=>0x0110, 0x0433=>0x0413, 0x012F=>0x012E, 0x00E6=>0x00C6,
    0x0078=>0x0058, 0x0161=>0x0160, 0x016F=>0x016E, 0x03B1=>0x0391, 0x0457=>0x0407,
    0x0173=>0x0172, 0x00FF=>0x0178, 0x006F=>0x004F, 0x043B=>0x041B, 0x03B5=>0x0395,
    0x0445=>0x0425, 0x0121=>0x0120, 0x017E=>0x017D, 0x017C=>0x017B, 0x03B6=>0x0396,
    0x03B2=>0x0392, 0x03AD=>0x0388, 0x1E85=>0x1E84, 0x0175=>0x0174, 0x0071=>0x0051,
    0x0437=>0x0417, 0x1E0B=>0x1E0A, 0x0148=>0x0147, 0x0105=>0x0104, 0x0458=>0x0408,
    0x014D=>0x014C, 0x00ED=>0x00CD, 0x0079=>0x0059, 0x010B=>0x010A, 0x03CE=>0x038F,
    0x0072=>0x0052, 0x0430=>0x0410, 0x0455=>0x0405, 0x0452=>0x0402, 0x0127=>0x0126,
    0x0137=>0x0136, 0x012B=>0x012A, 0x03AF=>0x038A, 0x044B=>0x042B, 0x006C=>0x004C,
    0x03B7=>0x0397, 0x0125=>0x0124, 0x0219=>0x0218, 0x00FB=>0x00DB, 0x011F=>0x011E,
    0x043E=>0x041E, 0x1E41=>0x1E40, 0x03BD=>0x039D, 0x0107=>0x0106, 0x03CB=>0x03AB,
    0x0446=>0x0426, 0x00FE=>0x00DE, 0x00E7=>0x00C7, 0x03CA=>0x03AA, 0x0441=>0x0421,
    0x0432=>0x0412, 0x010F=>0x010E, 0x00F8=>0x00D8, 0x0077=>0x0057, 0x011B=>0x011A,
    0x0074=>0x0054, 0x006A=>0x004A, 0x045B=>0x040B, 0x0456=>0x0406, 0x0103=>0x0102,
    0x03BB=>0x039B, 0x00F1=>0x00D1, 0x043D=>0x041D, 0x03CC=>0x038C, 0x00E9=>0x00C9,
    0x00F0=>0x00D0, 0x0457=>0x0407, 0x0123=>0x0122,
  );

  /**
   * UTF-8 Case lookup table
   *
   * This lookuptable defines the lower case letters to their correspponding
   * upper case letter in UTF-8 (it does so by flipping $UTF8_LOWER_TO_UPPER)
   *
   * @author Andreas Gohr <andi@splitbrain.org>
   */
  global $UTF8_UPPER_TO_LOWER;
  $UTF8_UPPER_TO_LOWER = @array_flip($UTF8_LOWER_TO_UPPER);

} // end of case lookup tables


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
  'あ'=>'a','え'=>'e','い'=>'i','お'=>'o','う'=>'u','ば'=>'ba','べ'=>'be',
  'び'=>'bi','ぼ'=>'bo','ぶ'=>'bu','し'=>'ci','だ'=>'da','で'=>'de','ぢ'=>'di',
  'ど'=>'do','づ'=>'du','ふぁ'=>'fa','ふぇ'=>'fe','ふぃ'=>'fi','ふぉ'=>'fo',
  'ふ'=>'fu','が'=>'ga','げ'=>'ge','ぎ'=>'gi','ご'=>'go','ぐ'=>'gu','は'=>'ha',
  'へ'=>'he','ひ'=>'hi','ほ'=>'ho','ふ'=>'hu','じゃ'=>'ja','じぇ'=>'je',
  'じ'=>'ji','じょ'=>'jo','じゅ'=>'ju','か'=>'ka','け'=>'ke','き'=>'ki',
  'こ'=>'ko','く'=>'ku','ら'=>'la','れ'=>'le','り'=>'li','ろ'=>'lo','る'=>'lu',
  'ま'=>'ma','め'=>'me','み'=>'mi','も'=>'mo','む'=>'mu','な'=>'na','ね'=>'ne',
  'に'=>'ni','の'=>'no','ぬ'=>'nu','ぱ'=>'pa','ぺ'=>'pe','ぴ'=>'pi','ぽ'=>'po',
  'ぷ'=>'pu','ら'=>'ra','れ'=>'re','り'=>'ri','ろ'=>'ro','る'=>'ru','さ'=>'sa',
  'せ'=>'se','し'=>'si','そ'=>'so','す'=>'su','た'=>'ta','て'=>'te','ち'=>'ti',
  'と'=>'to','つ'=>'tu','ヴぁ'=>'va','ヴぇ'=>'ve','ヴぃ'=>'vi','ヴぉ'=>'vo',
  'ヴ'=>'vu','わ'=>'wa','うぇ'=>'we','うぃ'=>'wi','を'=>'wo','や'=>'ya','いぇ'=>'ye',
  'い'=>'yi','よ'=>'yo','ゆ'=>'yu','ざ'=>'za','ぜ'=>'ze','じ'=>'zi','ぞ'=>'zo',
  'ず'=>'zu','びゃ'=>'bya','びぇ'=>'bye','びぃ'=>'byi','びょ'=>'byo','びゅ'=>'byu',
  'ちゃ'=>'cha','ちぇ'=>'che','ち'=>'chi','ちょ'=>'cho','ちゅ'=>'chu','ちゃ'=>'cya',
  'ちぇ'=>'cye','ちぃ'=>'cyi','ちょ'=>'cyo','ちゅ'=>'cyu','でゃ'=>'dha','でぇ'=>'dhe',
  'でぃ'=>'dhi','でょ'=>'dho','でゅ'=>'dhu','どぁ'=>'dwa','どぇ'=>'dwe','どぃ'=>'dwi',
  'どぉ'=>'dwo','どぅ'=>'dwu','ぢゃ'=>'dya','ぢぇ'=>'dye','ぢぃ'=>'dyi','ぢょ'=>'dyo',
  'ぢゅ'=>'dyu','ぢ'=>'dzi','ふぁ'=>'fwa','ふぇ'=>'fwe','ふぃ'=>'fwi','ふぉ'=>'fwo',
  'ふぅ'=>'fwu','ふゃ'=>'fya','ふぇ'=>'fye','ふぃ'=>'fyi','ふょ'=>'fyo','ふゅ'=>'fyu',
  'ぎゃ'=>'gya','ぎぇ'=>'gye','ぎぃ'=>'gyi','ぎょ'=>'gyo','ぎゅ'=>'gyu','ひゃ'=>'hya',
  'ひぇ'=>'hye','ひぃ'=>'hyi','ひょ'=>'hyo','ひゅ'=>'hyu','じゃ'=>'jya','じぇ'=>'jye',
  'じぃ'=>'jyi','じょ'=>'jyo','じゅ'=>'jyu','きゃ'=>'kya','きぇ'=>'kye','きぃ'=>'kyi',
  'きょ'=>'kyo','きゅ'=>'kyu','りゃ'=>'lya','りぇ'=>'lye','りぃ'=>'lyi','りょ'=>'lyo',
  'りゅ'=>'lyu','みゃ'=>'mya','みぇ'=>'mye','みぃ'=>'myi','みょ'=>'myo','みゅ'=>'myu',
  'ん'=>'n','にゃ'=>'nya','にぇ'=>'nye','にぃ'=>'nyi','にょ'=>'nyo','にゅ'=>'nyu',
  'ぴゃ'=>'pya','ぴぇ'=>'pye','ぴぃ'=>'pyi','ぴょ'=>'pyo','ぴゅ'=>'pyu','りゃ'=>'rya',
  'りぇ'=>'rye','りぃ'=>'ryi','りょ'=>'ryo','りゅ'=>'ryu','しゃ'=>'sha','しぇ'=>'she',
  'し'=>'shi','しょ'=>'sho','しゅ'=>'shu','すぁ'=>'swa','すぇ'=>'swe','すぃ'=>'swi',
  'すぉ'=>'swo','すぅ'=>'swu','しゃ'=>'sya','しぇ'=>'sye','しぃ'=>'syi','しょ'=>'syo',
  'しゅ'=>'syu','てゃ'=>'tha','てぇ'=>'the','てぃ'=>'thi','てょ'=>'tho','てゅ'=>'thu',
  'つゃ'=>'tsa','つぇ'=>'tse','つぃ'=>'tsi','つょ'=>'tso','つ'=>'tsu','とぁ'=>'twa',
  'とぇ'=>'twe','とぃ'=>'twi','とぉ'=>'two','とぅ'=>'twu','ちゃ'=>'tya','ちぇ'=>'tye',
  'ちぃ'=>'tyi','ちょ'=>'tyo','ちゅ'=>'tyu','ヴゃ'=>'vya','ヴぇ'=>'vye','ヴぃ'=>'vyi',
  'ヴょ'=>'vyo','ヴゅ'=>'vyu','うぁ'=>'wha','うぇ'=>'whe','うぃ'=>'whi','うぉ'=>'who',
  'うぅ'=>'whu','ゑ'=>'wye','ゐ'=>'wyi','じゃ'=>'zha','じぇ'=>'zhe','じぃ'=>'zhi',
  'じょ'=>'zho','じゅ'=>'zhu','じゃ'=>'zya','じぇ'=>'zye','じぃ'=>'zyi','じょ'=>'zyo',
  'じゅ'=>'zyu',
  // Japanese katakana
  'ア'=>'a','エ'=>'e','イ'=>'i','オ'=>'o','ウ'=>'u','バ'=>'ba','ベ'=>'be','ビ'=>'bi',
  'ボ'=>'bo','ブ'=>'bu','シ'=>'ci','ダ'=>'da','デ'=>'de','ヂ'=>'di','ド'=>'do',
  'ヅ'=>'du','ファ'=>'fa','フェ'=>'fe','フィ'=>'fi','フォ'=>'fo','フ'=>'fu','ガ'=>'ga',
  'ゲ'=>'ge','ギ'=>'gi','ゴ'=>'go','グ'=>'gu','ハ'=>'ha','ヘ'=>'he','ヒ'=>'hi','ホ'=>'ho',
  'フ'=>'hu','ジャ'=>'ja','ジェ'=>'je','ジ'=>'ji','ジョ'=>'jo','ジュ'=>'ju','カ'=>'ka',
  'ケ'=>'ke','キ'=>'ki','コ'=>'ko','ク'=>'ku','ラ'=>'la','レ'=>'le','リ'=>'li','ロ'=>'lo',
  'ル'=>'lu','マ'=>'ma','メ'=>'me','ミ'=>'mi','モ'=>'mo','ム'=>'mu','ナ'=>'na','ネ'=>'ne',
  'ニ'=>'ni','ノ'=>'no','ヌ'=>'nu','パ'=>'pa','ペ'=>'pe','ピ'=>'pi','ポ'=>'po','プ'=>'pu',
  'ラ'=>'ra','レ'=>'re','リ'=>'ri','ロ'=>'ro','ル'=>'ru','サ'=>'sa','セ'=>'se','シ'=>'si',
  'ソ'=>'so','ス'=>'su','タ'=>'ta','テ'=>'te','チ'=>'ti','ト'=>'to','ツ'=>'tu','ヴァ'=>'va',
  'ヴェ'=>'ve','ヴィ'=>'vi','ヴォ'=>'vo','ヴ'=>'vu','ワ'=>'wa','ウェ'=>'we','ウィ'=>'wi',
  'ヲ'=>'wo','ヤ'=>'ya','イェ'=>'ye','イ'=>'yi','ヨ'=>'yo','ユ'=>'yu','ザ'=>'za','ゼ'=>'ze',
  'ジ'=>'zi','ゾ'=>'zo','ズ'=>'zu','ビャ'=>'bya','ビェ'=>'bye','ビィ'=>'byi','ビョ'=>'byo',
  'ビュ'=>'byu','チャ'=>'cha','チェ'=>'che','チ'=>'chi','チョ'=>'cho','チュ'=>'chu',
  'チャ'=>'cya','チェ'=>'cye','チィ'=>'cyi','チョ'=>'cyo','チュ'=>'cyu','デャ'=>'dha',
  'デェ'=>'dhe','ディ'=>'dhi','デョ'=>'dho','デュ'=>'dhu','ドァ'=>'dwa','ドェ'=>'dwe',
  'ドィ'=>'dwi','ドォ'=>'dwo','ドゥ'=>'dwu','ヂャ'=>'dya','ヂェ'=>'dye','ヂィ'=>'dyi',
  'ヂョ'=>'dyo','ヂュ'=>'dyu','ヂ'=>'dzi','ファ'=>'fwa','フェ'=>'fwe','フィ'=>'fwi',
  'フォ'=>'fwo','フゥ'=>'fwu','フャ'=>'fya','フェ'=>'fye','フィ'=>'fyi','フョ'=>'fyo',
  'フュ'=>'fyu','ギャ'=>'gya','ギェ'=>'gye','ギィ'=>'gyi','ギョ'=>'gyo','ギュ'=>'gyu',
  'ヒャ'=>'hya','ヒェ'=>'hye','ヒィ'=>'hyi','ヒョ'=>'hyo','ヒュ'=>'hyu','ジャ'=>'jya',
  'ジェ'=>'jye','ジィ'=>'jyi','ジョ'=>'jyo','ジュ'=>'jyu','キャ'=>'kya','キェ'=>'kye',
  'キィ'=>'kyi','キョ'=>'kyo','キュ'=>'kyu','リャ'=>'lya','リェ'=>'lye','リィ'=>'lyi',
  'リョ'=>'lyo','リュ'=>'lyu','ミャ'=>'mya','ミェ'=>'mye','ミィ'=>'myi','ミョ'=>'myo',
  'ミュ'=>'myu','ン'=>'n','ニャ'=>'nya','ニェ'=>'nye','ニィ'=>'nyi','ニョ'=>'nyo',
  'ニュ'=>'nyu','ピャ'=>'pya','ピェ'=>'pye','ピィ'=>'pyi','ピョ'=>'pyo','ピュ'=>'pyu',
  'リャ'=>'rya','リェ'=>'rye','リィ'=>'ryi','リョ'=>'ryo','リュ'=>'ryu','シャ'=>'sha',
  'シェ'=>'she','シ'=>'shi','ショ'=>'sho','シュ'=>'shu','スァ'=>'swa','スェ'=>'swe',
  'スィ'=>'swi','スォ'=>'swo','スゥ'=>'swu','シャ'=>'sya','シェ'=>'sye','シィ'=>'syi',
  'ショ'=>'syo','シュ'=>'syu','テャ'=>'tha','テェ'=>'the','ティ'=>'thi','テョ'=>'tho',
  'テュ'=>'thu','ツャ'=>'tsa','ツェ'=>'tse','ツィ'=>'tsi','ツョ'=>'tso','ツ'=>'tsu',
  'トァ'=>'twa','トェ'=>'twe','トィ'=>'twi','トォ'=>'two','トゥ'=>'twu','チャ'=>'tya',
  'チェ'=>'tye','チィ'=>'tyi','チョ'=>'tyo','チュ'=>'tyu','ヴャ'=>'vya','ヴェ'=>'vye',
  'ヴィ'=>'vyi','ヴョ'=>'vyo','ヴュ'=>'vyu','ウァ'=>'wha','ウェ'=>'whe','ウィ'=>'whi',
  'ウォ'=>'who','ウゥ'=>'whu','ヱ'=>'wye','ヰ'=>'wyi','ジャ'=>'zha','ジェ'=>'zhe',
  'ジィ'=>'zhi','ジョ'=>'zho','ジュ'=>'zhu','ジャ'=>'zya','ジェ'=>'zye','ジィ'=>'zyi',
  'ジョ'=>'zyo','ジュ'=>'zyu',

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
  'ะ'=>'a','–ั'=>'a','รร'=>'a','า'=>'a','รร'=>'an','ำ'=>'am','–ิ'=>'i','–ี'=>'i',
  '–ึ'=>'ue','–ื'=>'ue','–ุ'=>'u','–ู'=>'u','เะ'=>'e','เ–็'=>'e','เ'=>'e','แะ'=>'ae',
  'แ'=>'ae','โะ'=>'o','โ'=>'o','เาะ'=>'o','อ'=>'o','เอะ'=>'oe','เ–ิ'=>'oe',
  'เอ'=>'oe','เ–ียะ'=>'ia','เ–ีย'=>'ia','เ–ือะ'=>'uea','เ–ือ'=>'uea','–ัวะ'=>'ua',
  '–ัว'=>'ua','ว'=>'ua','ใ'=>'ai','ไ'=>'ai','–ัย'=>'ai','ไย'=>'ai','าย'=>'ai',
  'เา'=>'ao','าว'=>'ao','–ุย'=>'ui','โย'=>'oi','อย'=>'oi','เย'=>'oei','เ–ือย'=>'ueai',
  'วย'=>'uai','–ิว'=>'io','เ–็ว'=>'eo','เว'=>'eo','แ–็ว'=>'aeo','แว'=>'aeo',
  'เ–ียว'=>'iao',

  // Korean
  'ㄱ'=>'k','ㅋ'=>'kh','ㄲ'=>'kk','ㄷ'=>'t','ㅌ'=>'th','ㄸ'=>'tt','ㅂ'=>'p',
  'ㅍ'=>'ph','ㅃ'=>'pp','ㅈ'=>'c','ㅊ'=>'ch','ㅉ'=>'cc','ㅅ'=>'s','ㅆ'=>'ss',
  'ㅎ'=>'h','ㅇ'=>'ng','ㄴ'=>'n','ㄹ'=>'l','ㅁ'=>'m', 'ㅏ'=>'a','ㅓ'=>'e','ㅗ'=>'o',
  'ㅜ'=>'wu','ㅡ'=>'u','ㅣ'=>'i','ㅐ'=>'ay','ㅔ'=>'ey','ㅚ'=>'oy','ㅘ'=>'wa','ㅝ'=>'we',
  'ㅟ'=>'wi','ㅙ'=>'way','ㅞ'=>'wey','ㅢ'=>'uy','ㅑ'=>'ya','ㅕ'=>'ye','ㅛ'=>'oy',
  'ㅠ'=>'yu','ㅒ'=>'yay','ㅖ'=>'yey',
);

//Setup VIM: ex: et ts=2 enc=utf-8 :

