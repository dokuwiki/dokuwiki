<?php
/**
 * Mail functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) die('meh.');

// end of line for mail lines - RFC822 says CRLF but postfix (and other MTAs?)
// think different
if(!defined('MAILHEADER_EOL')) define('MAILHEADER_EOL',"\n");
#define('MAILHEADER_ASCIIONLY',1);

/**
 * Patterns for use in email detection and validation
 *
 * NOTE: there is an unquoted '/' in RFC2822_ATEXT, it must remain unquoted to be used in the parser
 * the pattern uses non-capturing groups as captured groups aren't allowed in the parser
 * select pattern delimiters with care!
 *
 * May not be completly RFC conform!
 * @link http://www.faqs.org/rfcs/rfc2822.html (paras 3.4.1 & 3.2.4)
 *
 * @author Chris Smith <chris@jalakai.co.uk>
 * Check if a given mail address is valid
 */
if (!defined('RFC2822_ATEXT')) define('RFC2822_ATEXT',"0-9a-zA-Z!#$%&'*+/=?^_`{|}~-");
if (!defined('PREG_PATTERN_VALID_EMAIL')) define('PREG_PATTERN_VALID_EMAIL', '['.RFC2822_ATEXT.']+(?:\.['.RFC2822_ATEXT.']+)*@(?i:[0-9a-z][0-9a-z-]*\.)+(?i:[a-z]{2,63})');

/**
 * Prepare mailfrom replacement patterns
 *
 * Also prepares a mailfromnobody config that contains an autoconstructed address
 * if the mailfrom one is userdependent and this might not be wanted (subscriptions)
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function mail_setup(){
    global $conf;
    global $USERINFO;
    /** @var Input $INPUT */
    global $INPUT;

    // auto constructed address
    $host = @parse_url(DOKU_URL,PHP_URL_HOST);
    if(!$host) $host = 'example.com';
    $noreply = 'noreply@'.$host;

    $replace = array();
    if(!empty($USERINFO['mail'])){
        $replace['@MAIL@'] = $USERINFO['mail'];
    }else{
        $replace['@MAIL@'] = $noreply;
    }

    // use 'noreply' if no user
    $replace['@USER@'] = $INPUT->server->str('REMOTE_USER', 'noreply', true);

    if(!empty($USERINFO['name'])){
        $replace['@NAME@'] = $USERINFO['name'];
    }else{
        $replace['@NAME@'] = '';
    }

    // apply replacements
    $from = str_replace(array_keys($replace),
                        array_values($replace),
                        $conf['mailfrom']);

    // any replacements done? set different mailfromnone
    if($from != $conf['mailfrom']){
        $conf['mailfromnobody'] = $noreply;
    }else{
        $conf['mailfromnobody'] = $from;
    }
    $conf['mailfrom'] = $from;
}

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
 *
 * @deprecated User the Mailer:: class instead
 */
function mail_send($to, $subject, $body, $from='', $cc='', $bcc='', $headers=null, $params=null){
    dbg_deprecated('class Mailer::');
    $message = compact('to','subject','body','from','cc','bcc','headers','params');
    return trigger_event('MAIL_MESSAGE_SEND',$message,'_mail_send_action');
}

/**
 * @param $data
 * @return bool
 *
 * @deprecated User the Mailer:: class instead
 */
function _mail_send_action($data) {
    dbg_deprecated('class Mailer::');
    // retrieve parameters from event data, $to, $subject, $body, $from, $cc, $bcc, $headers, $params
    $to = $data['to'];
    $subject = $data['subject'];
    $body = $data['body'];

    // add robustness in case plugin removes any of these optional values
    $from = isset($data['from']) ? $data['from'] : '';
    $cc = isset($data['cc']) ? $data['cc'] : '';
    $bcc = isset($data['bcc']) ? $data['bcc'] : '';
    $headers = isset($data['headers']) ? $data['headers'] : null;
    $params = isset($data['params']) ? $data['params'] : null;

    // discard mail request if no recipients are available
    if(trim($to) === '' && trim($cc) === '' && trim($bcc) === '') return false;

    // end additional code to support event ... original mail_send() code from here

    if(defined('MAILHEADER_ASCIIONLY')){
        $subject = utf8_deaccent($subject);
        $subject = utf8_strip($subject);
    }

    if(!utf8_isASCII($subject)) {
        $enc_subj = '=?UTF-8?Q?'.mail_quotedprintable_encode($subject,0).'?=';
        // Spaces must be encoded according to rfc2047. Use the "_" shorthand
        $enc_subj = preg_replace('/ /', '_', $enc_subj);

        // quoted printable has length restriction, use base64 if needed
        if(strlen($subject) > 74){
            $enc_subj = '=?UTF-8?B?'.base64_encode($subject).'?=';
        }

        $subject = $enc_subj;
    }

    $header  = '';

    // No named recipients for To: in Windows (see FS#652)
    $usenames = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? false : true;

    $to = mail_encode_address($to,'',$usenames);
    $header .= mail_encode_address($from,'From');
    $header .= mail_encode_address($cc,'Cc');
    $header .= mail_encode_address($bcc,'Bcc');
    $header .= 'MIME-Version: 1.0'.MAILHEADER_EOL;
    $header .= 'Content-Type: text/plain; charset=UTF-8'.MAILHEADER_EOL;
    $header .= 'Content-Transfer-Encoding: quoted-printable'.MAILHEADER_EOL;
    $header .= $headers;
    $header  = trim($header);

    $body = mail_quotedprintable_encode($body);

    if($params == null){
        return @mail($to,$subject,$body,$header);
    }else{
        return @mail($to,$subject,$body,$header,$params);
    }
}

/**
 * Encodes an email address header
 *
 * Unicode characters will be deaccented and encoded
 * quoted_printable for headers.
 * Addresses may not contain Non-ASCII data!
 *
 * Example:
 *   mail_encode_address("föö <foo@bar.com>, me@somewhere.com","TBcc");
 *
 * @param string  $string Multiple adresses separated by commas
 * @param string  $header Name of the header (To,Bcc,Cc,...)
 * @param boolean $names  Allow named Recipients?
 *
 * @deprecated User the Mailer:: class instead
 */
function mail_encode_address($string,$header='',$names=true){
    dbg_deprecated('class Mailer::');
    $headers = '';
    $parts = explode(',',$string);
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

        // text was given
        if(!empty($text) && $names){
            // add address quotes
            $addr = "<$addr>";

            if(defined('MAILHEADER_ASCIIONLY')){
                $text = utf8_deaccent($text);
                $text = utf8_strip($text);
            }

            if(!utf8_isASCII($text)){
                // put the quotes outside as in =?UTF-8?Q?"Elan Ruusam=C3=A4e"?= vs "=?UTF-8?Q?Elan Ruusam=C3=A4e?="
                if (preg_match('/^"(.+)"$/', $text, $matches)) {
                    $text = '"=?UTF-8?Q?'.mail_quotedprintable_encode($matches[1], 0).'?="';
                } else {
                    $text = '=?UTF-8?Q?'.mail_quotedprintable_encode($text, 0).'?=';
                }
                // additionally the space character should be encoded as =20 (or each
                // word QP encoded separately).
                // however this is needed only in mail headers, not globally in mail_quotedprintable_encode().
                $text = str_replace(" ", "=20", $text);
            }
        }else{
            $text = '';
        }

        // add to header comma seperated
        if($headers != ''){
            $headers .= ',';
            if($header) $headers .= MAILHEADER_EOL.' '; // avoid overlong mail headers
        }
        $headers .= $text.' '.$addr;
    }

    if(empty($headers)) return null;

    //if headername was given add it and close correctly
    if($header) $headers = $header.': '.$headers.MAILHEADER_EOL;

    return $headers;
}

/**
 * Check if a given mail address is valid
 *
 * @param   string $email the address to check
 * @return  bool          true if address is valid
 */
function mail_isvalid($email){
    $validator = new EmailAddressValidator;
    $validator->allowLocalAddresses = true;
    return $validator->check_email_address($email);
}

/**
 * Quoted printable encoding
 *
 * @author umu <umuAThrz.tu-chemnitz.de>
 * @link   http://www.php.net/manual/en/function.imap-8bit.php#61216
 */
function mail_quotedprintable_encode($sText,$maxlen=74,$bEmulate_imap_8bit=true) {
    // split text into lines
    $aLines= preg_split("/(?:\r\n|\r|\n)/", $sText);
    $cnt = count($aLines);

    for ($i=0;$i<$cnt;$i++) {
        $sLine =& $aLines[$i];
        if (strlen($sLine)===0) continue; // do nothing, if empty

        $sRegExp = '/[^\x09\x20\x21-\x3C\x3E-\x7E]/e';

        // imap_8bit encodes x09 everywhere, not only at lineends,
        // for EBCDIC safeness encode !"#$@[\]^`{|}~,
        // for complete safeness encode every character :)
        if ($bEmulate_imap_8bit)
            $sRegExp = '/[^\x20\x21-\x3C\x3E-\x7E]/';

        $sLine = preg_replace_callback( $sRegExp, 'mail_quotedprintable_encode_callback', $sLine );

        // encode x09,x20 at lineends
        {
            $iLength = strlen($sLine);
            $iLastChar = ord($sLine{$iLength-1});

            //              !!!!!!!!
            // imap_8_bit does not encode x20 at the very end of a text,
            // here is, where I don't agree with imap_8_bit,
            // please correct me, if I'm wrong,
            // or comment next line for RFC2045 conformance, if you like
            if (!($bEmulate_imap_8bit && ($i==count($aLines)-1))){
                if (($iLastChar==0x09)||($iLastChar==0x20)) {
                    $sLine{$iLength-1}='=';
                    $sLine .= ($iLastChar==0x09)?'09':'20';
                }
            }
        }    // imap_8bit encodes x20 before chr(13), too
        // although IMHO not requested by RFC2045, why not do it safer :)
        // and why not encode any x20 around chr(10) or chr(13)
        if ($bEmulate_imap_8bit) {
            $sLine=str_replace(' =0D','=20=0D',$sLine);
            //$sLine=str_replace(' =0A','=20=0A',$sLine);
            //$sLine=str_replace('=0D ','=0D=20',$sLine);
            //$sLine=str_replace('=0A ','=0A=20',$sLine);
        }

        // finally split into softlines no longer than $maxlen chars,
        // for even more safeness one could encode x09,x20
        // at the very first character of the line
        // and after soft linebreaks, as well,
        // but this wouldn't be caught by such an easy RegExp
        if($maxlen){
            preg_match_all( '/.{1,'.($maxlen - 2).'}([^=]{0,2})?/', $sLine, $aMatch );
            $sLine = implode( '=' . MAILHEADER_EOL, $aMatch[0] ); // add soft crlf's
        }
    }

    // join lines into text
    return implode(MAILHEADER_EOL,$aLines);
}

function mail_quotedprintable_encode_callback($matches){
    return sprintf( "=%02X", ord ( $matches[0] ) ) ;
}
