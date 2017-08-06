<?php

namespace dokuwiki\Mail;


class Message extends \Swift_Message
{

    protected $replacements = [];
    protected $allowHtml = true;

    public function initHeaders()
    {
        /* @var \Input $INPUT */
        global $conf;
        global $INPUT;
        $server = parse_url(DOKU_URL, PHP_URL_HOST);
        if (strpos($server, '.') === false) $server = $server . '.localhost';
        $listid = join('.', array_reverse(explode('/', DOKU_BASE))) . $server;
        $listid = strtolower(trim($listid, '.'));

        // add some default headers for mailfiltering FS#2247
        $headers = $this->getHeaders();
        $headers->addTextHeader('X-Mailer', 'DokuWiki');
        $headers->addTextHeader('X-DokuWiki-User', $INPUT->server->str('REMOTE_USER'));
        $headers->addTextHeader('X-DokuWiki-Title', $conf['title']);
        $headers->addTextHeader('X-DokuWiki-Server', $server);
        $headers->addTextHeader('X-Auto-Response-Suppress', 'OOF');
        $headers->addTextHeader('List-Id', $conf['title'] . ' <' . $listid . '>');
    }


    public function initReplacements()
    {
        global $INFO;
        global $conf;
        /* @var Input $INPUT */
        global $INPUT;
        global $lang;

        $ip = clientIP();
        $cip = gethostsbyaddrs($ip);

        $this->replacements['textrep'] = array(
            'DATE' => dformat(),
            'BROWSER' => $INPUT->server->str('HTTP_USER_AGENT'),
            'IPADDRESS' => $ip,
            'HOSTNAME' => $cip,
            'TITLE' => $conf['title'],
            'DOKUWIKIURL' => DOKU_URL,
            'USER' => $INPUT->server->str('REMOTE_USER'),
            'NAME' => $INFO['userinfo']['name'],
            'MAIL' => $INFO['userinfo']['mail']
        );
        $signature = str_replace('@DOKUWIKIURL@', $this->replacements['textrep']['DOKUWIKIURL'], $lang['email_signature_text']);
        $this->replacements['textrep']['EMAILSIGNATURE'] = "\n-- \n" . $signature . "\n";

        $this->replacements['htmlrep'] = array(
            'DATE' => '<i>' . hsc(dformat()) . '</i>',
            'BROWSER' => hsc($INPUT->server->str('HTTP_USER_AGENT')),
            'IPADDRESS' => '<code>' . hsc($ip) . '</code>',
            'HOSTNAME' => '<code>' . hsc($cip) . '</code>',
            'TITLE' => hsc($conf['title']),
            'DOKUWIKIURL' => '<a href="' . DOKU_URL . '">' . DOKU_URL . '</a>',
            'USER' => hsc($INPUT->server->str('REMOTE_USER')),
            'NAME' => hsc($INFO['userinfo']['name']),
            'MAIL' => '<a href="mailto:"' . hsc($INFO['userinfo']['mail']) . '">' .
                hsc($INFO['userinfo']['mail']) . '</a>'
        );
        $signature = $lang['email_signature_text'];
        if (!empty($lang['email_signature_html'])) {
            $signature = $lang['email_signature_html'];
        }
        $signature = str_replace(
            array(
                '@DOKUWIKIURL@',
                "\n"
            ),
            array(
                $this->replacements['htmlrep']['DOKUWIKIURL'],
                '<br />'
            ),
            $signature
        );
        $this->replacements['htmlrep']['EMAILSIGNATURE'] = $signature;

    }

    public function setBodyReplacements(array $replacements)
    {
        // known keys
        foreach (['textrep', 'htmlrep', 'html', 'wrap'] as $key) {
            if (empty($replacements[$key])) {
                continue;
            }
            if (is_array($replacements[$key])) {
                foreach ($replacements[$key] as $repkey => $repvalue) {
                    $this->replacements[$key][$repkey] = $repvalue;
                }
                continue;
            }
            // simple parameter otherwise
            $this->replacements[$key] = $replacements[$key];
        }

        return $this;
    }

    public function setAllowHtml($allow = true)
    {
        $this->allowHtml = (bool)$allow;

        return $this;
    }


    protected function prepareTextBody()
    {
        $text = $this->getBody();

        if (strpos($text, '@EMAILSIGNATURE@') === false) {
            $text .= '@EMAILSIGNATURE@';
        }

        foreach ($this->replacements['textrep'] as $key => $substitution) {
            $text = str_replace('@' . strtoupper($key) . '@', $substitution, $text);
        }

        return $text;
    }

    protected function prepareHtmlBody()
    {
        $text = $this->getBody();

        if (strpos($text, '@EMAILSIGNATURE@') === false) {
            $text .= '@EMAILSIGNATURE@';
        }

        $html = $this->replacements['html'];
        $wrap = $this->replacements['wrap'];
        $htmlrep = $this->replacements['htmlrep'];
        $textrep = $this->replacements['textrep'];

        if (empty($html)) { // create HTML from text if not given
            $html = $text;
            $html = hsc($html);
            $html = preg_replace('/^----+$/m', '<hr>', $html);
            $html = nl2br($html);
        }
        if ($wrap) {
            $wrap = rawLocale('mailwrap', 'html');
            $html = preg_replace('/\n-- <br \/>.*$/s', '', $html); //strip signature
            $html = str_replace('@EMAILSIGNATURE@', '', $html); //strip @EMAILSIGNATURE@
            $html = str_replace('@HTMLBODY@', $html, $wrap);
        }

        // copy over all replacements missing for HTML (autolink URLs)
        foreach ($textrep as $key => $value) {
            if (isset($htmlrep[$key])) continue;
            if (media_isexternal($value)) {
                $htmlrep[$key] = '<a href="' . hsc($value) . '">' . hsc($value) . '</a>';
            } else {
                $htmlrep[$key] = hsc($value);
            }
        }

        // embed media from templates TODO:!!!
        /*
        $html = preg_replace_callback(
            '/@MEDIA\(([^\)]+)\)@/',
            array($this, 'autoembed_cb'), $html
        );*/

        foreach ($htmlrep as $key => $substitution) {
            $html = str_replace('@' . strtoupper($key) . '@', $substitution, $html);
        }

        return $html;
    }

    public function prepareBody()
    {
        $text = $this->prepareTextBody();
        $html = $this->allowHtml ? $this->prepareHtmlBody() : null;

        $this->setBody($text, 'text/plain');

        if ($this->allowHtml) {
            $this->addPart($html, 'text/html');
        }

        return $this;
    }


    public function __construct($subject = null, $body = null, $contentType = null, $charset = null)
    {
        parent::__construct($subject, $body, $contentType, $charset);

        $this->initHeaders();
        $this->initReplacements();
    }
}