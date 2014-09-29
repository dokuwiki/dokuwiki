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

    /** @var EmailAddressValidator */
    protected $validator = null;
    protected $allowhtml = true;

    /**
     * Constructor
     *
     * Initializes the boundary strings and part counters
     */
    public function __construct() {
        global $conf;
        /* @var Input $INPUT */
        global $INPUT;

        $server = parse_url(DOKU_URL, PHP_URL_HOST);
        if(strpos($server,'.') === false) $server = $server.'.localhost';

        $this->partid   = substr(md5(uniqid(rand(), true)),0, 8).'@'.$server;
        $this->boundary = '__________'.md5(uniqid(rand(), true));

        $listid = join('.', array_reverse(explode('/', DOKU_BASE))).$server;
        $listid = strtolower(trim($listid, '.'));

        $this->allowhtml = (bool)$conf['htmlmail'];

        // add some default headers for mailfiltering FS#2247
        $this->setHeader('X-Mailer', 'DokuWiki');
        $this->setHeader('X-DokuWiki-User', $INPUT->server->str('REMOTE_USER'));
        $this->setHeader('X-DokuWiki-Title', $conf['title']);
        $this->setHeader('X-DokuWiki-Server', $server);
        $this->setHeader('X-Auto-Response-Suppress', 'OOF');
        $this->setHeader('List-Id', $conf['title'].' <'.$listid.'>');
        $this->setHeader('Date', date('r'), false);
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
            $name = utf8_basename($path);
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
     */
    protected function autoembed_cb($matches) {
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
     * @param string $value  the value of the header
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
     */
    public function setParameters($param) {
        $this->sendparam = $param;
    }

    /**
     * Set the text and HTML body and apply replacements
     *
     * This function applies a whole bunch of default replacements in addition
     * to the ones specidifed as parameters
     *
     * If you pass the HTML part or HTML replacements yourself you have to make
     * sure you encode all HTML special chars correctly
     *
     * @param string $text     plain text body
     * @param array  $textrep  replacements to apply on the text part
     * @param array  $htmlrep  replacements to apply on the HTML part, leave null to use $textrep
     * @param array  $html     the HTML body, leave null to create it from $text
     * @param bool   $wrap     wrap the HTML in the default header/Footer
     */
    public function setBody($text, $textrep = null, $htmlrep = null, $html = null, $wrap = true) {
        global $INFO;
        global $conf;
        /* @var Input $INPUT */
        global $INPUT;

        $htmlrep = (array)$htmlrep;
        $textrep = (array)$textrep;

        // create HTML from text if not given
        if(is_null($html)) {
            $html = $text;
            $html = hsc($html);
            $html = preg_replace('/^-----*$/m', '<hr >', $html);
            $html = nl2br($html);
        }
        if($wrap) {
            $wrap = rawLocale('mailwrap', 'html');
            $html = preg_replace('/\n-- <br \/>.*$/s', '', $html); //strip signature
            $html = str_replace('@HTMLBODY@', $html, $wrap);
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
            array($this, 'autoembed_cb'), $html
        );

        // prepare default replacements
        $ip   = clientIP();
        $cip  = gethostsbyaddrs($ip);
        $trep = array(
            'DATE'        => dformat(),
            'BROWSER'     => $INPUT->server->str('HTTP_USER_AGENT'),
            'IPADDRESS'   => $ip,
            'HOSTNAME'    => $cip,
            'TITLE'       => $conf['title'],
            'DOKUWIKIURL' => DOKU_URL,
            'USER'        => $INPUT->server->str('REMOTE_USER'),
            'NAME'        => $INFO['userinfo']['name'],
            'MAIL'        => $INFO['userinfo']['mail'],
        );
        $trep = array_merge($trep, (array)$textrep);
        $hrep = array(
            'DATE'        => '<i>'.hsc(dformat()).'</i>',
            'BROWSER'     => hsc($INPUT->server->str('HTTP_USER_AGENT')),
            'IPADDRESS'   => '<code>'.hsc($ip).'</code>',
            'HOSTNAME'    => '<code>'.hsc($cip).'</code>',
            'TITLE'       => hsc($conf['title']),
            'DOKUWIKIURL' => '<a href="'.DOKU_URL.'">'.DOKU_URL.'</a>',
            'USER'        => hsc($INPUT->server->str('REMOTE_USER')),
            'NAME'        => hsc($INFO['userinfo']['name']),
            'MAIL'        => '<a href="mailto:"'.hsc($INFO['userinfo']['mail']).'">'.
                hsc($INFO['userinfo']['mail']).'</a>',
        );
        $hrep = array_merge($hrep, (array)$htmlrep);

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
     */
    public function setHTML($html) {
        $this->html = $html;
    }

    /**
     * Set the plain text part of the mail
     *
     * You probably want to use setBody() instead
     */
    public function setText($text) {
        $this->text = $text;
    }

    /**
     * Add the To: recipients
     *
     * @see cleanAddress
     * @param string|array  $address Multiple adresses separated by commas or as array
     */
    public function to($address) {
        $this->setHeader('To', $address, false);
    }

    /**
     * Add the Cc: recipients
     *
     * @see cleanAddress
     * @param string|array  $address Multiple adresses separated by commas or as array
     */
    public function cc($address) {
        $this->setHeader('Cc', $address, false);
    }

    /**
     * Add the Bcc: recipients
     *
     * @see cleanAddress
     * @param string|array  $address Multiple adresses separated by commas or as array
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
     * Sets an email address header with correct encoding
     *
     * Unicode characters will be deaccented and encoded base64
     * for headers. Addresses may not contain Non-ASCII data!
     *
     * Example:
     *   cc("föö <foo@bar.com>, me@somewhere.com","TBcc");
     *
     * @param string|array  $addresses Multiple adresses separated by commas or as array
     * @return bool|string  the prepared header (can contain multiple lines)
     */
    public function cleanAddress($addresses) {
        // No named recipients for To: in Windows (see FS#652)
        $names = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? false : true;

        $headers = '';
        if(!is_array($addresses)){
            $addresses = explode(',', $addresses);
        }

        foreach($addresses as $part) {
            $part = preg_replace('/[\r\n\0]+/', ' ', $part); // remove attack vectors
            $part = trim($part);

            // parse address
            if(preg_match('#(.*?)<(.*?)>#', $part, $matches)) {
                $text = trim($matches[1]);
                $addr = $matches[2];
            } else {
                $addr = $part;
            }
            // skip empty ones
            if(empty($addr)) {
                continue;
            }

            // FIXME: is there a way to encode the localpart of a emailaddress?
            if(!utf8_isASCII($addr)) {
                msg(htmlspecialchars("E-Mail address <$addr> is not ASCII"), -1);
                continue;
            }

            if(is_null($this->validator)) {
                $this->validator                      = new EmailAddressValidator();
                $this->validator->allowLocalAddresses = true;
            }
            if(!$this->validator->check_email_address($addr)) {
                msg(htmlspecialchars("E-Mail address <$addr> is not valid"), -1);
                continue;
            }

            // text was given
            if(!empty($text) && $names) {
                // add address quotes
                $addr = "<$addr>";

                if(defined('MAILHEADER_ASCIIONLY')) {
                    $text = utf8_deaccent($text);
                    $text = utf8_strip($text);
                }

                if(strpos($text, ',') !== false || !utf8_isASCII($text)) {
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
                if(utf8_strlen($conf['title']) < 20) {
                    $prefix = '['.$conf['title'].']';
                } else {
                    $prefix = '['.utf8_substr($conf['title'], 0, 20).'...]';
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
                $this->headers['Subject'] = utf8_deaccent($this->headers['Subject']);
                $this->headers['Subject'] = utf8_strip($this->headers['Subject']);
            }
            if(!utf8_isASCII($this->headers['Subject'])) {
                $this->headers['Subject'] = '=?UTF-8?B?'.base64_encode($this->headers['Subject']).'?=';
            }
        }

    }

    /**
     * Returns a complete, EOL terminated header line, wraps it if necessary
     *
     * @param $key
     * @param $val
     * @return string
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
            if ($val === '' || is_null($val)) continue;
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
     * Send the mail
     *
     * Call this after all data was set
     *
     * @triggers MAIL_MESSAGE_SEND
     * @return bool true if the mail was successfully passed to the MTA
     */
    public function send() {
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
        $evt = new Doku_Event('MAIL_MESSAGE_SEND', $data);
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

            // send the thing
            if(is_null($this->sendparam)) {
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
