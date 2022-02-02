<?php
/**
 * A class to build and send multi part mails (with HTML content and embedded
 * attachments). All mails are assumed to be in UTF-8 encoding.
 *
 * Attachments are handled in memory so this shouldn't be used to send huge
 * files, but then again mail shouldn't be used to send huge files either.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */

use dokuwiki\Extension\Event;

// end of line for mail lines - RFC822 says CRLF but postfix (and other MTAs?)
// think different
if(!defined('MAILHEADER_EOL')) define('MAILHEADER_EOL', "\n");
#define('MAILHEADER_ASCIIONLY',1);

/**
 * Mail Handling
 */
class Mailer {

    protected $headers   = array();
    protected $attach    = array();
    protected $html      = '';
    protected $text      = '';

    protected $boundary  = '';
    protected $partid    = '';
    protected $sendparam = null;

    protected $allowhtml = true;

    protected $replacements = array('text'=> array(), 'html' => array());

    /**
     * Constructor
     *
     * Initializes the boundary strings, part counters and token replacements
     */
    public function __construct() {
        global $conf;
        /* @var Input $INPUT */
        global $INPUT;

        $server = parse_url(DOKU_URL, PHP_URL_HOST);
        if(strpos($server,'.') === false) $server .= '.localhost';

        $this->partid   = substr(md5(uniqid(mt_rand(), true)),0, 8).'@'.$server;
        $this->boundary = '__________'.md5(uniqid(mt_rand(), true));

        $listid = implode('.', array_reverse(explode('/', DOKU_BASE))).$server;
        $listid = strtolower(trim($listid, '.'));

        $this->allowhtml = (bool)$conf['htmlmail'];

        // add some default headers for mailfiltering FS#2247
        if(!empty($conf['mailreturnpath'])) {
            $this->setHeader('Return-Path', $conf['mailreturnpath']);
        }
        $this->setHeader('X-Mailer', 'DokuWiki');
        $this->setHeader('X-DokuWiki-User', $INPUT->server->str('REMOTE_USER'));
        $this->setHeader('X-DokuWiki-Title', $conf['title']);
        $this->setHeader('X-DokuWiki-Server', $server);
        $this->setHeader('X-Auto-Response-Suppress', 'OOF');
        $this->setHeader('List-Id', $conf['title'].' <'.$listid.'>');
        $this->setHeader('Date', date('r'), false);

        $this->prepareTokenReplacements();
    }

    /**
     * Attach a file
     *
     * @param string $path  Path to the file to attach
     * @param string $mime  Mimetype of the attached file
     * @param string $name The filename to use
     * @param string $embed Unique key to reference this file from the HTML part
     */
    public function attachFile($path, $mime, $name = '', $embed = '') {
        if(!$name) {
            $name = \dokuwiki\Utf8\PhpString::basename($path);
        }

        $this->attach[] = array(
            'data'  => file_get_contents($path),
            'mime'  => $mime,
            'name'  => $name,
            'embed' => $embed
        );
    }

    /**
     * Attach a file
     *
     * @param string $data  The file contents to attach
     * @param string $mime  Mimetype of the attached file
     * @param string $name  The filename to use
     * @param string $embed Unique key to reference this file from the HTML part
     */
    public function attachContent($data, $mime, $name = '', $embed = '') {
        if(!$name) {
            list(, $ext) = explode('/', $mime);
            $name = count($this->attach).".$ext";
        }

        $this->attach[] = array(
            'data'  => $data,
            'mime'  => $mime,
            'name'  => $name,
            'embed' => $embed
        );
    }

    /**
     * Callback function to automatically embed images referenced in HTML templates
     *
     * @param array $matches
     * @return string placeholder
     */
    protected function autoEmbedCallBack($matches) {
        static $embeds = 0;
        $embeds++;

        // get file and mime type
        $media = cleanID($matches[1]);
        list(, $mime) = mimetype($media);
        $file = mediaFN($media);
        if(!file_exists($file)) return $matches[0]; //bad reference, keep as is

        // attach it and set placeholder
        $this->attachFile($file, $mime, '', 'autoembed'.$embeds);
        return '%%autoembed'.$embeds.'%%';
    }

    /**
     * Add an arbitrary header to the mail
     *
     * If an empy value is passed, the header is removed
     *
     * @param string $header the header name (no trailing colon!)
     * @param string|string[] $value  the value of the header
     * @param bool   $clean  remove all non-ASCII chars and line feeds?
     */
    public function setHeader($header, $value, $clean = true) {
        $header = str_replace(' ', '-', ucwords(strtolower(str_replace('-', ' ', $header)))); // streamline casing
        if($clean) {
            $header = preg_replace('/[^a-zA-Z0-9_ \-\.\+\@]+/', '', $header);
            $value  = preg_replace('/[^a-zA-Z0-9_ \-\.\+\@<>]+/', '', $value);
        }

        // empty value deletes
        if(is_array($value)){
            $value = array_map('trim', $value);
            $value = array_filter($value);
            if(!$value) $value = '';
        }else{
            $value = trim($value);
        }
        if($value === '') {
            if(isset($this->headers[$header])) unset($this->headers[$header]);
        } else {
            $this->headers[$header] = $value;
        }
    }

    /**
     * Set additional parameters to be passed to sendmail
     *
     * Whatever is set here is directly passed to PHP's mail() command as last
     * parameter. Depending on the PHP setup this might break mailing alltogether
     *
     * @param string $param
     */
    public function setParameters($param) {
        $this->sendparam = $param;
    }

    /**
     * Set the text and HTML body and apply replacements
     *
     * This function applies a whole bunch of default replacements in addition
     * to the ones specified as parameters
     *
     * If you pass the HTML part or HTML replacements yourself you have to make
     * sure you encode all HTML special chars correctly
     *
     * @param string $text     plain text body
     * @param array  $textrep  replacements to apply on the text part
     * @param array  $htmlrep  replacements to apply on the HTML part, null to use $textrep (urls wrapped in <a> tags)
     * @param string $html     the HTML body, leave null to create it from $text
     * @param bool   $wrap     wrap the HTML in the default header/Footer
     */
    public function setBody($text, $textrep = null, $htmlrep = null, $html = null, $wrap = true) {

        $htmlrep = (array)$htmlrep;
        $textrep = (array)$textrep;

        // create HTML from text if not given
        if($html === null) {
            $html = $text;
            $html = hsc($html);
            $html = preg_replace('/^----+$/m', '<hr >', $html);
            $html = nl2br($html);
        }
        if($wrap) {
            $wrapper = rawLocale('mailwrap', 'html');
            $html = preg_replace('/\n-- <br \/>.*$/s', '', $html); //strip signature
            $html = str_replace('@EMAILSIGNATURE@', '', $html); //strip @EMAILSIGNATURE@
            $html = str_replace('@HTMLBODY@', $html, $wrapper);
        }

        if(strpos($text, '@EMAILSIGNATURE@') === false) {
            $text .= '@EMAILSIGNATURE@';
        }

        // copy over all replacements missing for HTML (autolink URLs)
        foreach($textrep as $key => $value) {
            if(isset($htmlrep[$key])) continue;
            if(media_isexternal($value)) {
                $htmlrep[$key] = '<a href="'.hsc($value).'">'.hsc($value).'</a>';
            } else {
                $htmlrep[$key] = hsc($value);
            }
        }

        // embed media from templates
        $html = preg_replace_callback(
            '/@MEDIA\(([^\)]+)\)@/',
            array($this, 'autoEmbedCallBack'), $html
        );

        // add default token replacements
        $trep = array_merge($this->replacements['text'], (array)$textrep);
        $hrep = array_merge($this->replacements['html'], (array)$htmlrep);

        // Apply replacements
        foreach($trep as $key => $substitution) {
            $text = str_replace('@'.strtoupper($key).'@', $substitution, $text);
        }
        foreach($hrep as $key => $substitution) {
            $html = str_replace('@'.strtoupper($key).'@', $substitution, $html);
        }

        $this->setHTML($html);
        $this->setText($text);
    }

    /**
     * Set the HTML part of the mail
     *
     * Placeholders can be used to reference embedded attachments
     *
     * You probably want to use setBody() instead
     *
     * @param string $html
     */
    public function setHTML($html) {
        $this->html = $html;
    }

    /**
     * Set the plain text part of the mail
     *
     * You probably want to use setBody() instead
     *
     * @param string $text
     */
    public function setText($text) {
        $this->text = $text;
    }

    /**
     * Add the To: recipients
     *
     * @see cleanAddress
     * @param string|string[]  $address Multiple adresses separated by commas or as array
     */
    public function to($address) {
        $this->setHeader('To', $address, false);
    }

    /**
     * Add the Cc: recipients
     *
     * @see cleanAddress
     * @param string|string[]  $address Multiple adresses separated by commas or as array
     */
    public function cc($address) {
        $this->setHeader('Cc', $address, false);
    }

    /**
     * Add the Bcc: recipients
     *
     * @see cleanAddress
     * @param string|string[]  $address Multiple adresses separated by commas or as array
     */
    public function bcc($address) {
        $this->setHeader('Bcc', $address, false);
    }

    /**
     * Add the From: address
     *
     * This is set to $conf['mailfrom'] when not specified so you shouldn't need
     * to call this function
     *
     * @see cleanAddress
     * @param string  $address from address
     */
    public function from($address) {
        $this->setHeader('From', $address, false);
    }

    /**
     * Add the mail's Subject: header
     *
     * @param string $subject the mail subject
     */
    public function subject($subject) {
        $this->headers['Subject'] = $subject;
    }

    /**
     * Return a clean name which can be safely used in mail address
     * fields. That means the name will be enclosed in '"' if it includes
     * a '"' or a ','. Also a '"' will be escaped as '\"'.
     *
     * @param string $name the name to clean-up
     * @see cleanAddress
     */
    public function getCleanName($name) {
        $name = trim($name, ' \t"');
        $name = str_replace('"', '\"', $name, $count);
        if ($count > 0 || strpos($name, ',') !== false) {
            $name = '"'.$name.'"';
        }
        return $name;
    }

    /**
     * Sets an email address header with correct encoding
     *
     * Unicode characters will be deaccented and encoded base64
     * for headers. Addresses may not contain Non-ASCII data!
     *
     * If @$addresses is a string then it will be split into multiple
     * addresses. Addresses must be separated by a comma. If the display
     * name includes a comma then it MUST be properly enclosed by '"' to
     * prevent spliting at the wrong point.
     *
     * Example:
     *   cc("föö <foo@bar.com>, me@somewhere.com","TBcc");
     *   to("foo, Dr." <foo@bar.com>, me@somewhere.com");
     *
     * @param string|string[]  $addresses Multiple adresses separated by commas or as array
     * @return false|string  the prepared header (can contain multiple lines)
     */
    public function cleanAddress($addresses) {
        $headers = '';
        if(!is_array($addresses)){
            $count = preg_match_all('/\s*(?:("[^"]*"[^,]+),*)|([^,]+)\s*,*/', $addresses, $matches, PREG_SET_ORDER);
            $addresses = array();
            if ($count !== false && is_array($matches)) {
                foreach ($matches as $match) {
                    array_push($addresses, rtrim($match[0], ','));
                }
            }
        }

        foreach($addresses as $part) {
            $part = preg_replace('/[\r\n\0]+/', ' ', $part); // remove attack vectors
            $part = trim($part);

            // parse address
            if(preg_match('#(.*?)<(.*?)>#', $part, $matches)) {
                $text = trim($matches[1]);
                $addr = $matches[2];
            } else {
                $text = '';
                $addr = $part;
            }
            // skip empty ones
            if(empty($addr)) {
                continue;
            }

            // FIXME: is there a way to encode the localpart of a emailaddress?
            if(!\dokuwiki\Utf8\Clean::isASCII($addr)) {
                msg(hsc("E-Mail address <$addr> is not ASCII"), -1, __LINE__, __FILE__, MSG_ADMINS_ONLY);
                continue;
            }

            if(!mail_isvalid($addr)) {
                msg(hsc("E-Mail address <$addr> is not valid"), -1, __LINE__, __FILE__, MSG_ADMINS_ONLY);
                continue;
            }

            // text was given
            if(!empty($text) && !isWindows()) { // No named recipients for To: in Windows (see FS#652)
                // add address quotes
                $addr = "<$addr>";

                if(defined('MAILHEADER_ASCIIONLY')) {
                    $text = \dokuwiki\Utf8\Clean::deaccent($text);
                    $text = \dokuwiki\Utf8\Clean::strip($text);
                }

                if(strpos($text, ',') !== false || !\dokuwiki\Utf8\Clean::isASCII($text)) {
                    $text = '=?UTF-8?B?'.base64_encode($text).'?=';
                }
            } else {
                $text = '';
            }

            // add to header comma seperated
            if($headers != '') {
                $headers .= ', ';
            }
            $headers .= $text.' '.$addr;
        }

        $headers = trim($headers);
        if(empty($headers)) return false;

        return $headers;
    }


    /**
     * Prepare the mime multiparts for all attachments
     *
     * Replaces placeholders in the HTML with the correct CIDs
     *
     * @return string mime multiparts
     */
    protected function prepareAttachments() {
        $mime = '';
        $part = 1;
        // embedded attachments
        foreach($this->attach as $media) {
            $media['name'] = str_replace(':', '_', cleanID($media['name'], true));

            // create content id
            $cid = 'part'.$part.'.'.$this->partid;

            // replace wildcards
            if($media['embed']) {
                $this->html = str_replace('%%'.$media['embed'].'%%', 'cid:'.$cid, $this->html);
            }

            $mime .= '--'.$this->boundary.MAILHEADER_EOL;
            $mime .= $this->wrappedHeaderLine('Content-Type', $media['mime'].'; id="'.$cid.'"');
            $mime .= $this->wrappedHeaderLine('Content-Transfer-Encoding', 'base64');
            $mime .= $this->wrappedHeaderLine('Content-ID',"<$cid>");
            if($media['embed']) {
                $mime .= $this->wrappedHeaderLine('Content-Disposition', 'inline; filename='.$media['name']);
            } else {
                $mime .= $this->wrappedHeaderLine('Content-Disposition', 'attachment; filename='.$media['name']);
            }
            $mime .= MAILHEADER_EOL; //end of headers
            $mime .= chunk_split(base64_encode($media['data']), 74, MAILHEADER_EOL);

            $part++;
        }
        return $mime;
    }

    /**
     * Build the body and handles multi part mails
     *
     * Needs to be called before prepareHeaders!
     *
     * @return string the prepared mail body, false on errors
     */
    protected function prepareBody() {

        // no HTML mails allowed? remove HTML body
        if(!$this->allowhtml) {
            $this->html = '';
        }

        // check for body
        if(!$this->text && !$this->html) {
            return false;
        }

        // add general headers
        $this->headers['MIME-Version'] = '1.0';

        $body = '';

        if(!$this->html && !count($this->attach)) { // we can send a simple single part message
            $this->headers['Content-Type']              = 'text/plain; charset=UTF-8';
            $this->headers['Content-Transfer-Encoding'] = 'base64';
            $body .= chunk_split(base64_encode($this->text), 72, MAILHEADER_EOL);
        } else { // multi part it is
            $body .= "This is a multi-part message in MIME format.".MAILHEADER_EOL;

            // prepare the attachments
            $attachments = $this->prepareAttachments();

            // do we have alternative text content?
            if($this->text && $this->html) {
                $this->headers['Content-Type'] = 'multipart/alternative;'.MAILHEADER_EOL.
                    '  boundary="'.$this->boundary.'XX"';
                $body .= '--'.$this->boundary.'XX'.MAILHEADER_EOL;
                $body .= 'Content-Type: text/plain; charset=UTF-8'.MAILHEADER_EOL;
                $body .= 'Content-Transfer-Encoding: base64'.MAILHEADER_EOL;
                $body .= MAILHEADER_EOL;
                $body .= chunk_split(base64_encode($this->text), 72, MAILHEADER_EOL);
                $body .= '--'.$this->boundary.'XX'.MAILHEADER_EOL;
                $body .= 'Content-Type: multipart/related;'.MAILHEADER_EOL.
                    '  boundary="'.$this->boundary.'";'.MAILHEADER_EOL.
                    '  type="text/html"'.MAILHEADER_EOL;
                $body .= MAILHEADER_EOL;
            }

            $body .= '--'.$this->boundary.MAILHEADER_EOL;
            $body .= 'Content-Type: text/html; charset=UTF-8'.MAILHEADER_EOL;
            $body .= 'Content-Transfer-Encoding: base64'.MAILHEADER_EOL;
            $body .= MAILHEADER_EOL;
            $body .= chunk_split(base64_encode($this->html), 72, MAILHEADER_EOL);
            $body .= MAILHEADER_EOL;
            $body .= $attachments;
            $body .= '--'.$this->boundary.'--'.MAILHEADER_EOL;

            // close open multipart/alternative boundary
            if($this->text && $this->html) {
                $body .= '--'.$this->boundary.'XX--'.MAILHEADER_EOL;
            }
        }

        return $body;
    }

    /**
     * Cleanup and encode the headers array
     */
    protected function cleanHeaders() {
        global $conf;

        // clean up addresses
        if(empty($this->headers['From'])) $this->from($conf['mailfrom']);
        $addrs = array('To', 'From', 'Cc', 'Bcc', 'Reply-To', 'Sender');
        foreach($addrs as $addr) {
            if(isset($this->headers[$addr])) {
                $this->headers[$addr] = $this->cleanAddress($this->headers[$addr]);
            }
        }

        if(isset($this->headers['Subject'])) {
            // add prefix to subject
            if(empty($conf['mailprefix'])) {
                if(\dokuwiki\Utf8\PhpString::strlen($conf['title']) < 20) {
                    $prefix = '['.$conf['title'].']';
                } else {
                    $prefix = '['.\dokuwiki\Utf8\PhpString::substr($conf['title'], 0, 20).'...]';
                }
            } else {
                $prefix = '['.$conf['mailprefix'].']';
            }
            $len = strlen($prefix);
            if(substr($this->headers['Subject'], 0, $len) != $prefix) {
                $this->headers['Subject'] = $prefix.' '.$this->headers['Subject'];
            }

            // encode subject
            if(defined('MAILHEADER_ASCIIONLY')) {
                $this->headers['Subject'] = \dokuwiki\Utf8\Clean::deaccent($this->headers['Subject']);
                $this->headers['Subject'] = \dokuwiki\Utf8\Clean::strip($this->headers['Subject']);
            }
            if(!\dokuwiki\Utf8\Clean::isASCII($this->headers['Subject'])) {
                $this->headers['Subject'] = '=?UTF-8?B?'.base64_encode($this->headers['Subject']).'?=';
            }
        }

    }

    /**
     * Returns a complete, EOL terminated header line, wraps it if necessary
     *
     * @param string $key
     * @param string $val
     * @return string line
     */
    protected function wrappedHeaderLine($key, $val){
        return wordwrap("$key: $val", 78, MAILHEADER_EOL.'  ').MAILHEADER_EOL;
    }

    /**
     * Create a string from the headers array
     *
     * @returns string the headers
     */
    protected function prepareHeaders() {
        $headers = '';
        foreach($this->headers as $key => $val) {
            if ($val === '' || $val === null) continue;
            $headers .= $this->wrappedHeaderLine($key, $val);
        }
        return $headers;
    }

    /**
     * return a full email with all headers
     *
     * This is mainly intended for debugging and testing but could also be
     * used for MHT exports
     *
     * @return string the mail, false on errors
     */
    public function dump() {
        $this->cleanHeaders();
        $body = $this->prepareBody();
        if($body === false) return false;
        $headers = $this->prepareHeaders();

        return $headers.MAILHEADER_EOL.$body;
    }

    /**
     * Prepare default token replacement strings
     *
     * Populates the '$replacements' property.
     * Should be called by the class constructor
     */
    protected function prepareTokenReplacements() {
        global $INFO;
        global $conf;
        /* @var Input $INPUT */
        global $INPUT;
        global $lang;

        $ip   = clientIP();
        $cip  = gethostsbyaddrs($ip);
        $name = isset($INFO) ? $INFO['userinfo']['name'] : '';
        $mail = isset($INFO) ? $INFO['userinfo']['mail'] : '';

        $this->replacements['text'] = array(
            'DATE' => dformat(),
            'BROWSER' => $INPUT->server->str('HTTP_USER_AGENT'),
            'IPADDRESS' => $ip,
            'HOSTNAME' => $cip,
            'TITLE' => $conf['title'],
            'DOKUWIKIURL' => DOKU_URL,
            'USER' => $INPUT->server->str('REMOTE_USER'),
            'NAME' => $name,
            'MAIL' => $mail
        );
        $signature = str_replace(
            '@DOKUWIKIURL@',
            $this->replacements['text']['DOKUWIKIURL'],
            $lang['email_signature_text']
        );
        $this->replacements['text']['EMAILSIGNATURE'] = "\n-- \n" . $signature . "\n";

        $this->replacements['html'] = array(
            'DATE' => '<i>' . hsc(dformat()) . '</i>',
            'BROWSER' => hsc($INPUT->server->str('HTTP_USER_AGENT')),
            'IPADDRESS' => '<code>' . hsc($ip) . '</code>',
            'HOSTNAME' => '<code>' . hsc($cip) . '</code>',
            'TITLE' => hsc($conf['title']),
            'DOKUWIKIURL' => '<a href="' . DOKU_URL . '">' . DOKU_URL . '</a>',
            'USER' => hsc($INPUT->server->str('REMOTE_USER')),
            'NAME' => hsc($name),
            'MAIL' => '<a href="mailto:"' . hsc($mail) . '">' .
                hsc($mail) . '</a>'
        );
        $signature = $lang['email_signature_text'];
        if(!empty($lang['email_signature_html'])) {
            $signature = $lang['email_signature_html'];
        }
        $signature = str_replace(
            array(
                '@DOKUWIKIURL@',
                "\n"
            ),
            array(
                $this->replacements['html']['DOKUWIKIURL'],
                '<br />'
            ),
            $signature
        );
        $this->replacements['html']['EMAILSIGNATURE'] = $signature;
    }

    /**
     * Send the mail
     *
     * Call this after all data was set
     *
     * @triggers MAIL_MESSAGE_SEND
     * @return bool true if the mail was successfully passed to the MTA
     */
    public function send() {
        global $lang;
        $success = false;

        // prepare hook data
        $data = array(
            // pass the whole mail class to plugin
            'mail'    => $this,
            // pass references for backward compatibility
            'to'      => &$this->headers['To'],
            'cc'      => &$this->headers['Cc'],
            'bcc'     => &$this->headers['Bcc'],
            'from'    => &$this->headers['From'],
            'subject' => &$this->headers['Subject'],
            'body'    => &$this->text,
            'params'  => &$this->sendparam,
            'headers' => '', // plugins shouldn't use this
            // signal if we mailed successfully to AFTER event
            'success' => &$success,
        );

        // do our thing if BEFORE hook approves
        $evt = new Event('MAIL_MESSAGE_SEND', $data);
        if($evt->advise_before(true)) {
            // clean up before using the headers
            $this->cleanHeaders();

            // any recipients?
            if(trim($this->headers['To']) === '' &&
                trim($this->headers['Cc']) === '' &&
                trim($this->headers['Bcc']) === ''
            ) return false;

            // The To: header is special
            if(array_key_exists('To', $this->headers)) {
                $to = (string)$this->headers['To'];
                unset($this->headers['To']);
            } else {
                $to = '';
            }

            // so is the subject
            if(array_key_exists('Subject', $this->headers)) {
                $subject = (string)$this->headers['Subject'];
                unset($this->headers['Subject']);
            } else {
                $subject = '';
            }

            // make the body
            $body = $this->prepareBody();
            if($body === false) return false;

            // cook the headers
            $headers = $this->prepareHeaders();
            // add any headers set by legacy plugins
            if(trim($data['headers'])) {
                $headers .= MAILHEADER_EOL.trim($data['headers']);
            }

            if(!function_exists('mail')){
                $emsg = $lang['email_fail'] . $subject;
                error_log($emsg);
                msg(hsc($emsg), -1, __LINE__, __FILE__, MSG_MANAGERS_ONLY);
                $evt->advise_after();
                return false;
            }

            // send the thing
            if($this->sendparam === null) {
                $success = @mail($to, $subject, $body, $headers);
            } else {
                $success = @mail($to, $subject, $body, $headers, $this->sendparam);
            }
        }
        // any AFTER actions?
        $evt->advise_after();
        return $success;
    }
}
