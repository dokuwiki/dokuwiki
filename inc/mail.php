<?php
/**
 * Mail functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

  if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');
  require_once(DOKU_INC.'inc/utf8.php');

  define('MAILHEADER_EOL',"\n"); //end of line for mail headers
  #define('MAILHEADER_ASCIIONLY',1);

/**
 * UTF-8 autoencoding replacement for PHPs mail function
 *
 * Email address fields (To, From, Cc, Bcc can contain a textpart and an address
 * like this: 'Andreas Gohr <andi@splitbrain.org>' - the text part is encoded
 * automatically. You can seperate receivers by commas.
 *
 * @param string $to      Receiver of the mail (multiple seperated by commas)
 * @param string $subject Mailsubject
 * @param string $body    Messagebody
 * @param string $from    Sender address 
 * @param string $cc      CarbonCopy receiver (multiple seperated by commas)
 * @param string $bcc     BlindCarbonCopy receiver (multiple seperated by commas)
 * @param string $headers Additional Headers (seperated by MAILHEADER_EOL
 * @param string $params  Additonal Sendmail params (passed to mail())
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see    mail()
 */
function mail_send($to, $subject, $body, $from='', $cc='', $bcc='', $headers=null, $params=null){
  if(defined('MAILHEADER_ASCIIONLY')){
    $subject = utf8_deaccent($subject);
    $subject = utf8_strip($subject);
  }

  if(!utf8_isASCII($subject))
    $subject = '=?UTF-8?Q?'.mail_quotedprintable_encode($subject).'?=';

  $header  = '';

  // use PHP mail's to field if pure ASCII-7 is available
  $to = mail_encode_address($to,'To');
  if(preg_match('#=?UTF-8?=#',$to)){
    $header .= $to;
    $to = null;
  }else{
    $to = preg_replace('#^To: #','',$to);
  }

  $header .= mail_encode_address($from,'From');
  $header .= mail_encode_address($cc,'Cc');
  $header .= mail_encode_address($bcc,'Bcc');
  $header .= 'MIME-Version: 1.0'.MAILHEADER_EOL;
  $header .= 'Content-Type: text/plain; charset=UTF-8'.MAILHEADER_EOL;
  $header .= 'Content-Transfer-Encoding: quoted-printable'.MAILHEADER_EOL;
  $header .= $headers;
  $header  = trim($header);

  $body = mail_quotedprintable_encode($body);

  return @mail($to,$subject,$body,$header,$params);
}

/**
 * Encodes an email address header
 *
 * Unicode characters will be deaccented and encoded
 * quoted_printable for headers.
 * Addresses may not contain Non-ASCII data!
 *
 * Example:
 *   mail_encode_address("föö <foo@bar.com>, me@somewhere.com","To");
 *
 * @param string $string Multiple headers seperated by commas
 */
function mail_encode_address($string,$header='To'){
  $headers = '';
  $parts = split(',',$string);
  foreach ($parts as $part){
    $part = trim($part);

    // parse address
    if(preg_match('#(.*?)<(.*?)>#',$part,$matches)){
      $text = trim($matches[1]);
      $addr = $matches[2];
    }else{
      $addr = $part;
    }

    // skip empty ones
    if(empty($addr)){
      continue;
    }

    // FIXME: is there a way to encode the localpart of a emailaddress?
    if(!utf8_isASCII($addr)){
      msg(htmlspecialchars("E-Mail address <$addr> is not ASCII"),-1);
      continue;
    }

    if(!mail_isvalid($addr)){
      msg(htmlspecialchars("E-Mail address <$addr> is not valid"),-1);
      continue;
    }

    // no text was given
    if(empty($text)){
      $headers .= $header.': <'.$addr.'>'.MAILHEADER_EOL;
      continue;
    }

    if(defined('MAILHEADER_ASCIIONLY')){
      $text = utf8_deaccent($text);
      $text = utf8_strip($text);
    }


    // FIME: can make problems with long headers?
    if(!utf8_isASCII($text)){
      $text = '=?UTF-8?Q?'.mail_quotedprintable_encode($text).'?=';
    }
    
    //construct header
    $headers .= $header.': '.$text.' <'.$addr.'>'.MAILHEADER_EOL;
  }

  return $headers;
}

/**
 * Uses a regular expresion to check if a given mail address is valid
 *
 * May not be completly RFC conform!
 * 
 * @link    http://www.webmasterworld.com/forum88/135.htm
 *
 * @param   string $email the address to check
 * @return  bool          true if address is valid
 */
function mail_isvalid($email){
  return eregi("^[0-9a-z]([-_.]?[0-9a-z])*@[0-9a-z]([-.]?[0-9a-z])*\\.[a-z]{2,4}$", $email);
}

/**
 * Quoted printable encoding
 *
 * @author <pob@medienrecht.org>
 * @author <tamas.tompa@kirowski.com>
 * @link   http://www.php.net/manual/en/function.quoted-printable-decode.php
 */
function mail_quotedprintable_encode($input='',$line_max=74,$space_conv=false){
  $hex = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F');
  $lines = preg_split("/(?:\r\n|\r|\n)/", $input);
  $eol = "\n";
  $escape = "=";
  $output = "";
  while( list(, $line) = each($lines) ) {
    //$line = rtrim($line); // remove trailing white space -> no =20\r\n necessary
    $linlen = strlen($line);
    $newline = "";
    for($i = 0; $i < $linlen; $i++) {
      $c = substr( $line, $i, 1 );
      $dec = ord( $c );
      if ( ( $i == 0 ) && ( $dec == 46 ) ) { // convert first point in the line into =2E
        $c = "=2E";
      }
      if ( $dec == 32 ) {
        if ( $i == ( $linlen - 1 ) ) { // convert space at eol only
          $c = "=20";
        } else if ( $space_conv ) {
          $c = "=20";
        }
      } elseif ( ($dec == 61) || ($dec < 32 ) || ($dec > 126) ) { // always encode "\t", which is *not* required
        $h2 = floor($dec/16);
        $h1 = floor($dec%16);
        $c = $escape.$hex["$h2"].$hex["$h1"];
      }
      if ( (strlen($newline) + strlen($c)) >= $line_max ) { // CRLF is not counted
         $output .= $newline.$escape.$eol; // soft line break; " =\r\n" is okay
         $newline = "";
         // check if newline first character will be point or not
         if ( $dec == 46 ) {
            $c = "=2E";
         }
      }
      $newline .= $c;
    } // end of for
    $output .= $newline.$eol;
  } // end of while
  return trim($output);
}



//Setup VIM: ex: et ts=2 enc=utf-8 :
