<?php
/**
 * Mail functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

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
if (!defined('PREG_PATTERN_VALID_EMAIL')) define(
    'PREG_PATTERN_VALID_EMAIL',
    '['.RFC2822_ATEXT.']+(?:\.['.RFC2822_ATEXT.']+)*@(?i:[0-9a-z][0-9a-z-]*\.)+(?i:[a-z]{2,63})'
);

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
 * Check if a given mail address is valid
 *
 * @param   string $email the address to check
 * @return  bool          true if address is valid
 */
function mail_isvalid($email) {
    return EmailAddressValidator::checkEmailAddress($email, true);
}

/**
 * Quoted printable encoding
 *
 * @author umu <umuAThrz.tu-chemnitz.de>
 * @link   http://php.net/manual/en/function.imap-8bit.php#61216
 *
 * @param string $sText
 * @param int $maxlen
 * @param bool $bEmulate_imap_8bit
 *
 * @return string
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
            $iLastChar = ord($sLine[$iLength-1]);

            //              !!!!!!!!
            // imap_8_bit does not encode x20 at the very end of a text,
            // here is, where I don't agree with imap_8_bit,
            // please correct me, if I'm wrong,
            // or comment next line for RFC2045 conformance, if you like
            if (!($bEmulate_imap_8bit && ($i==count($aLines)-1))){
                if (($iLastChar==0x09)||($iLastChar==0x20)) {
                    $sLine[$iLength-1]='=';
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
