<?php
/**
 * @author Andreas Gohr <andi@splitbrain.org>
 */


// end of line for mail lines - RFC822 says CRLF but postfix (and other MTAs?)
// think different
if(!defined('MAILHEADER_EOL')) define('MAILHEADER_EOL',"\n");
#define('MAILHEADER_ASCIIONLY',1);


class Mailer {

    private $headers = array();
    private $attach  = array();
    private $html    = '';
    private $text    = '';

    private $boundary = '';
    private $partid   = '';
    private $sendparam= '';

    function __construct(){
        $this->partid = md5(uniqid(rand(),true)).'@'.$_SERVER['SERVER_NAME'];
        $this->boundary = '----------'.md5(uniqid(rand(),true));
    }

    /**
     * Attach a file
     *
     * @param $path  Path to the file to attach
     * @param $mime  Mimetype of the attached file
     * @param $name  The filename to use
     * @param $embed Unique key to reference this file from the HTML part
     */
    public function attachFile($path,$mime,$name='',$embed=''){
        if(!$name){
            $name = basename($path);
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
     * @param $path  The file contents to attach
     * @param $mime  Mimetype of the attached file
     * @param $name  The filename to use
     * @param $embed Unique key to reference this file from the HTML part
     */
    public function attachContent($data,$mime,$name='',$embed=''){
        if(!$name){
            list($junk,$ext) = split('/',$mime);
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
     * Set the HTML part of the mail
     *
     * Placeholders can be used to reference embedded attachments
     */
    public function setHTMLBody($html){
        $this->html = $html;
    }

    /**
     * Set the plain text part of the mail
     */
    public function setTextBody($text){
        $this->text = $text;
    }

    /**
     * Ses an email address header with correct encoding
     *
     * Unicode characters will be deaccented and encoded base64
     * for headers. Addresses may not contain Non-ASCII data!
     *
     * Example:
     *   setAddress("föö <foo@bar.com>, me@somewhere.com","TBcc");
     *
     * @param string  $address Multiple adresses separated by commas
     * @param string  $header  Name of the header (To,Bcc,Cc,...)
     */
    function mail_encode_address($address,$header){
        // No named recipients for To: in Windows (see FS#652)
        $names = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? false : true;

        $headers = '';
        $parts = explode(',',$address);
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
                    //FIXME
                    // put the quotes outside as in =?UTF-8?Q?"Elan Ruusam=C3=A4e"?= vs "=?UTF-8?Q?Elan Ruusam=C3=A4e?="
                    /*
                    if (preg_match('/^"(.+)"$/', $text, $matches)) {
                      $text = '"=?UTF-8?Q?'.mail_quotedprintable_encode($matches[1], 0).'?="';
                    } else {
                      $text = '=?UTF-8?Q?'.mail_quotedprintable_encode($text, 0).'?=';
                    }
                    */
                    $text = '=?UTF-8?B?'.base64_encode($text).'?=';
                }
            }else{
                $text = '';
            }

            // add to header comma seperated
            if($headers != ''){
                $headers .= ',';
                $headers .= MAILHEADER_EOL.' '; // avoid overlong mail headers
            }
            $headers .= $text.' '.$addr;
        }

        if(empty($headers)) return false;

        $this->headers[$header] = $headers;
        return $headers;
    }

    /**
     * Add the To: recipients
     *
     * @see setAddress
     * @param string  $address Multiple adresses separated by commas
     */
    public function to($address){
        $this->setAddress($address, 'To');
    }

    /**
     * Add the Cc: recipients
     *
     * @see setAddress
     * @param string  $address Multiple adresses separated by commas
     */
    public function cc($address){
        $this->setAddress($address, 'Cc');
    }

    /**
     * Add the Bcc: recipients
     *
     * @see setAddress
     * @param string  $address Multiple adresses separated by commas
     */
    public function bcc($address){
        $this->setAddress($address, 'Bcc');
    }

    /**
     * Add the mail's Subject: header
     *
     * @param string $subject the mail subject
     */
    public function subject($subject){
        if(!utf8_isASCII($subject)){
            $subject = '=?UTF-8?B?'.base64_encode($subject).'?=';
        }
        $this->headers['Subject'] = $subject;
    }

    /**
     * Prepare the mime multiparts for all attachments
     *
     * Replaces placeholders in the HTML with the correct CIDs
     */
    protected function prepareAttachments(){
        $mime = '';
        $part = 1;
        // embedded attachments
        foreach($this->attach as $media){
            // create content id
            $cid = 'part'.$part.'.'.$this->partid;

            // replace wildcards
            if($media['embed']){
                $this->html = str_replace('%%'.$media['embed'].'%%','cid:'.$cid,$this->html);
            }

            $mime .= '--'.$this->boundary.MAILHEADER_EOL;
            $mime .= 'Content-Type: '.$media['mime'].';'.MAILHEADER_EOL;
            $mime .= 'Content-Transfer-Encoding: base64'.MAILHEADER_EOL;
            $mime .= "Content-ID: <$cid>".MAILHEADER_EOL;
            if($media['embed']){
                $mime .= 'Content-Disposition: inline; filename="'.$media['name'].'"'.MAILHEADER_EOL;
            }else{
                $mime .= 'Content-Disposition: attachment; filename="'.$media['name'].'"'.MAILHEADER_EOL;
            }
            $mime .= MAILHEADER_EOL; //end of headers
            $mime .= chunk_split(base64_encode($media['data']),74,MAILHEADER_EOL);

            $part++;
        }
        return $mime;
    }

    protected function createBody(){
        // check for body
        if(!$this->text && !$this->html){
            return false;
        }

        // add general headers
        $this->headers['MIME-Version'] = '1.0';

        if(!$this->html && !count($this->attach)){ // we can send a simple single part message
            $this->headers['Content-Type'] = 'text/plain; charset=UTF-8';
            $this->headers['Content-Transfer-Encoding'] = 'base64';
            $body = chunk_split(base64_encode($this->text),74,MAILHEADER_EOL);
        }else{ // multi part it is

            // prepare the attachments
            $attachments = $this->prepareAttachments();

            // do we have alternative text content?
            if($this->text && $this->html){
                $this->headers['Content-Type'] = 'multipart/alternative; boundary="'.$this->boundary.'XX"';
                $body  = "This is a multi-part message in MIME format.".MAILHEADER_EOL;
                $body .= '--'.$this->boundary.'XX'.MAILHEADER_EOL;
                $body .= MAILHEADER_EOL;
                $body .= 'Content-Type: text/plain; charset=UTF-8';
                $body .= 'Content-Transfer-Encoding: base64';
                $body .= chunk_split(base64_encode($this->text),74,MAILHEADER_EOL);
                $body .= '--'.$this->boundary.'XX'.MAILHEADER_EOL;
                $body .= 'Content-Type: multipart/related; boundary="'.$this->boundary.'"'.MAILHEADER_EOL;
                $body .= MAILHEADER_EOL;
            }else{
                $this->headers['Content-Type'] = 'multipart/related; boundary="'.$this->boundary.'"';
                $body  = "This is a multi-part message in MIME format.".MAILHEADER_EOL;
            }

            $body .= '--'.$this->boundary."\n";
            $body .= "Content-Type: text/html; charset=UTF-8\n";
            $body .= "Content-Transfer-Encoding: base64\n";
            $body .= MAILHEADER_EOL;
            $body = chunk_split(base64_encode($this->html),74,MAILHEADER_EOL);
            $body .= MAILHEADER_EOL;
            $body .= $attachments;
            $body .= '--'.$this->boundary.'--'.MAILHEADER_EOL;

            // close open multipart/alternative boundary
            if($this->text && $this->html){
                $body .= '--'.$this->boundary.'XX--'.MAILHEADER_EOL;
            }
        }

        return $body;
    }

    /**
     * Create a string from the headers array
     */
    protected function prepareHeaders(){
        $headers = '';
        foreach($this->headers as $key => $val){
            $headers .= "$key: $val".MAILHEADER_EOL;
        }
        return $headers;
    }

    /**
     * return a full email with all headers
     *
     * This is mainly for debugging and testing
     */
    public function dump(){
        $headers = $this->prepareHeaders();
        $body    = $this->prepareBody();

        return $headers.MAILHEADER_EOL.$body;
    }
}
