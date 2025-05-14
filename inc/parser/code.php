<?php

use dokuwiki\Utf8\Clean;
use dokuwiki\Utf8\PhpString;

/**
 * A simple renderer that allows downloading of code and file snippets
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
class Doku_Renderer_code extends Doku_Renderer
{
    protected $_codeblock = 0;

    /**
     * Send the wanted code block to the browser
     *
     * When the correct block was found it exits the script.
     *
     * @param string $text
     * @param string $language
     * @param string $filename
     */
    public function code($text, $language = null, $filename = '')
    {
        global $INPUT;
        if (!$language) $language = 'txt';
        $language = preg_replace(PREG_PATTERN_VALID_LANGUAGE, '', $language);
        if (!$filename) $filename = 'snippet.' . $language;
        $filename = PhpString::basename($filename);
        $filename = Clean::stripspecials($filename, '_');

        // send CRLF to Windows clients
        if (strpos($INPUT->server->str('HTTP_USER_AGENT'), 'Windows') !== false) {
            $text = str_replace("\n", "\r\n", $text);
        }

        if ($this->_codeblock == $INPUT->str('codeblock')) {
            header("Content-Type: text/plain; charset=utf-8");
            header("Content-Disposition: attachment; filename=$filename");
            header("X-Robots-Tag: noindex");
            echo trim($text, "\r\n");
            exit;
        }

        $this->_codeblock++;
    }

    /**
     * Wraps around code()
     *
     * @param string $text
     * @param string $language
     * @param string $filename
     */
    public function file($text, $language = null, $filename = '')
    {
        $this->code($text, $language, $filename);
    }

    /**
     * This should never be reached, if it is send a 404
     */
    public function document_end()
    {
        http_status(404);
        echo '404 - Not found';
        exit;
    }

    /**
     * Return the format of the renderer
     *
     * @returns string 'code'
     */
    public function getFormat()
    {
        return 'code';
    }
}
