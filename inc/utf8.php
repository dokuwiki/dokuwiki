<?
/**
 * UTF8 helper functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

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
 * Tries to detect if a string is in Unicode encoding
 *
 * @author <bmorel@ssi.fr>
 * @link   http://www.php.net/manual/en/function.utf8-encode.php
 */
function utf8_check($Str) {
 for ($i=0; $i<strlen($Str); $i++) {
  if (ord($Str[$i]) < 0x80) continue; # 0bbbbbbb
  elseif ((ord($Str[$i]) & 0xE0) == 0xC0) $n=1; # 110bbbbb
  elseif ((ord($Str[$i]) & 0xF0) == 0xE0) $n=2; # 1110bbbb
  elseif ((ord($Str[$i]) & 0xF8) == 0xF0) $n=3; # 11110bbb
  elseif ((ord($Str[$i]) & 0xFC) == 0xF8) $n=4; # 111110bb
  elseif ((ord($Str[$i]) & 0xFE) == 0xFC) $n=5; # 1111110b
  else return false; # Does not match any model
  for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
   if ((++$i == strlen($Str)) || ((ord($Str[$i]) & 0xC0) != 0x80))
   return false;
  }
 }
 return true;
}

/**
 * This is a unicode aware replacement for strlen()
 *
 * Uses mb_string extension if available
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see    strlen()
 */
function utf8_strlen($string){
  if(!defined('UTF8_NOMBSTRING') && function_exists('mb_strlen'))
    return mb_strlen($string,'utf-8');

  $uni = utf8_to_unicode($string);
  return count($uni);
}

/**
 * This is a unicode aware replacement for substr()
 *
 * Uses mb_string extension if available
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see    substr()
 */
function utf8_substr($str, $start, $length=null){
  if(!defined('UTF8_NOMBSTRING') && function_exists('mb_substr'))
    return mb_substr($str,$start,$length,'utf-8');

  $uni = utf8_to_unicode($str);
  return unicode_to_utf8(array_slice($uni,$start,$length));
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
  if(!defined('UTF8_NOMBSTRING') && function_exists('mb_strtolower'))
    return mb_strtolower($string,'utf-8');

  global $UTF8_UPPER_TO_LOWER;
  $uni = utf8_to_unicode($string); 
  for ($i=0; $i < count($uni); $i++){
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
  if(!defined('UTF8_NOMBSTRING') && function_exists('mb_strtolower'))
    return mb_strtolower($string,'utf-8');

  global $UTF8_LOWER_TO_UPPER;
  $uni = utf8_to_unicode($string);
  for ($i=0; $i < count($uni); $i++){
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
 * This is an Unicode aware replacement for strpos
 *
 * Uses mb_string extension if available
 *
 * @author Scott Michael Reynen <scott@randomchaos.com>
 * @author Andreas Gohr <andi@splitbrain.org>
 * @link   http://www.randomchaos.com/document.php?source=php_and_unicode
 * @see    strpos()
 */
function utf8_strpos($haystack, $needle,$offset=0) {
  if(!defined('UTF8_NOMBSTRING') && function_exists('mb_strpos'))
    return mb_strpos($haystack,$needle,$offset,'utf-8');

  $haystack = utf8_to_unicode($haystack);
  $needle   = utf8_to_unicode($needle);
  $position = $offset;
  $found = false;
  
  while( (! $found ) && ( $position < count( $haystack ) ) ) {
    if ( $needle[0] == $haystack[$position] ) {
      for ($i = 1; $i < count( $needle ); $i++ ) {
        if ( $needle[$i] != $haystack[ $position + $i ] ) break;
      }
      if ( $i == count( $needle ) ) {
        $found = true;
        $position--;
      }
    }
    $position++;
  }
  return ( $found == true ) ? $position : false;
}

/**
 * This function will any UTF-8 encoded text and return it as
 * a list of Unicode values:
 *
 * @author Scott Michael Reynen <scott@randomchaos.com>
 * @link   http://www.randomchaos.com/document.php?source=php_and_unicode
 * @see    unicode_to_utf8()
 */
function utf8_to_unicode( $str ) {
  $unicode = array();  
  $values = array();
  $lookingFor = 1;
  
  for ($i = 0; $i < strlen( $str ); $i++ ) {
    $thisValue = ord( $str[ $i ] );
    if ( $thisValue < 128 ) $unicode[] = $thisValue;
    else {
      if ( count( $values ) == 0 ) $lookingFor = ( $thisValue < 224 ) ? 2 : 3;
      $values[] = $thisValue;
      if ( count( $values ) == $lookingFor ) {
  $number = ( $lookingFor == 3 ) ?
    ( ( $values[0] % 16 ) * 4096 ) + ( ( $values[1] % 64 ) * 64 ) + ( $values[2] % 64 ):
  	( ( $values[0] % 32 ) * 64 ) + ( $values[1] % 64 );
  $unicode[] = $number;
  $values = array();
  $lookingFor = 1;
      }
    }
  }
  return $unicode;
}

/**
 * This function will convert a Unicode array back to its UTF-8 representation
 *
 * @author Scott Michael Reynen <scott@randomchaos.com>
 * @link   http://www.randomchaos.com/document.php?source=php_and_unicode
 * @see    utf8_to_unicode()
 */
function unicode_to_utf8( $str ) {
  $utf8 = '';
  foreach( $str as $unicode ) {
    if ( $unicode < 128 ) {
      $utf8.= chr( $unicode );
    } elseif ( $unicode < 2048 ) {
      $utf8.= chr( 192 +  ( ( $unicode - ( $unicode % 64 ) ) / 64 ) );
      $utf8.= chr( 128 + ( $unicode % 64 ) );
    } else {
      $utf8.= chr( 224 + ( ( $unicode - ( $unicode % 4096 ) ) / 4096 ) );
      $utf8.= chr( 128 + ( ( ( $unicode % 4096 ) - ( $unicode % 64 ) ) / 64 ) );
      $utf8.= chr( 128 + ( $unicode % 64 ) );
    }
  }
  return $utf8;
}

/**
 * UTF-8 Case lookup table
 *
 * This lookuptable defines the upper case letters to their correspponding
 * lower case letter in UTF-8
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
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
$UTF8_UPPER_TO_LOWER = @array_flip($UTF8_LOWER_TO_UPPER);

/**
 * UTF-8 lookup table for lower case accented letters
 *
 * This lookuptable defines replacements for accented characters from the ASCII-7
 * range. This are lower case letters only.
 *
 * FIXME missing chars eg: æ
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see    utf8_deaccent()
 */
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
  'û' => 'u',
);

/**
 * UTF-8 lookup table for upper case accented letters
 *
 * This lookuptable defines replacements for accented characters from the ASCII-7
 * range. This are upper case letters only.
 *
 * FIXME missing chars eg: æ
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see    utf8_deaccent()
 */
$UTF8_UPPER_ACCENTS = array(
  'à' => 'A', 'ô' => 'O', 'ď' => 'D', 'ḟ' => 'F', 'ë' => 'E', 'š' => 'S', 'ơ' => 'O', 
  'ß' => 'Ss', 'ă' => 'A', 'ř' => 'R', 'ț' => 'T', 'ň' => 'N', 'ā' => 'A', 'ķ' => 'K', 
  'ŝ' => 'S', 'ỳ' => 'Y', 'ņ' => 'N', 'ĺ' => 'L', 'ħ' => 'H', 'ṗ' => 'P', 'ó' => 'O', 
  'ú' => 'U', 'ě' => 'E', 'é' => 'E', 'ç' => 'C', 'ẁ' => 'W', 'ċ' => 'C', 'õ' => 'O', 
  'ṡ' => 'S', 'ø' => 'O', 'ģ' => 'G', 'ŧ' => 'T', 'ș' => 'S', 'ė' => 'E', 'ĉ' => 'C', 
  'ś' => 'S', 'î' => 'I', 'ű' => 'U', 'ć' => 'C', 'ę' => 'E', 'ŵ' => 'W', 'ṫ' => 'T', 
  'ū' => 'U', 'č' => 'C', 'ö' => 'Oe', 'è' => 'E', 'ŷ' => 'Y', 'ą' => 'A', 'ł' => 'L', 
  'ų' => 'U', 'ů' => 'U', 'ş' => 'S', 'ğ' => 'G', 'ļ' => 'L', 'ƒ' => 'F', 'ž' => 'Z', 
  'ẃ' => 'W', 'ḃ' => 'B', 'å' => 'A', 'ì' => 'I', 'ï' => 'I', 'ḋ' => 'D', 'ť' => 'T', 
  'ŗ' => 'R', 'ä' => 'Ae', 'í' => 'I', 'ŕ' => 'R', 'ê' => 'E', 'ü' => 'Ue', 'ò' => 'O', 
  'ē' => 'E', 'ñ' => 'N', 'ń' => 'N', 'ĥ' => 'H', 'ĝ' => 'G', 'đ' => 'D', 'ĵ' => 'J', 
  'ÿ' => 'Y', 'ũ' => 'U', 'ŭ' => 'U', 'ư' => 'U', 'ţ' => 'T', 'ý' => 'Y', 'ő' => 'O', 
  'â' => 'A', 'ľ' => 'L', 'ẅ' => 'W', 'ż' => 'Z', 'ī' => 'I', 'ã' => 'A', 'ġ' => 'G', 
  'ṁ' => 'M', 'ō' => 'O', 'ĩ' => 'I', 'ù' => 'U', 'į' => 'I', 'ź' => 'Z', 'á' => 'A', 
  'û' => 'U',
);

?>
