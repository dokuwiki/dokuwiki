<?php

use dokuwiki\Extension\Event;
use dokuwiki\Extension\SyntaxPlugin;
use dokuwiki\Parsing\Handler\Block;
use dokuwiki\Parsing\Handler\CallWriter;
use dokuwiki\Parsing\Handler\CallWriterInterface;
use dokuwiki\Parsing\Handler\Lists;
use dokuwiki\Parsing\Handler\Nest;
use dokuwiki\Parsing\Handler\Preformatted;
use dokuwiki\Parsing\Handler\Quote;
use dokuwiki\Parsing\Handler\Table;

/**
 * Class Doku_Handler
 */
class Doku_Handler
{
    /** @var CallWriterInterface */
    protected $callWriter;

    /** @var array The current CallWriter will write directly to this list of calls, Parser reads it */
    public $calls = [];

    /** @var array internal status holders for some modes */
    protected $status = [
        'section' => false,
        'doublequote' => 0
    ];

    /** @var bool should blocks be rewritten? FIXME seems to always be true */
    protected $rewriteBlocks = true;

    /**
     * @var bool are we in a footnote already?
     */
    protected $footnote;

    /**
     * Doku_Handler constructor.
     */
    public function __construct()
    {
        $this->callWriter = new CallWriter($this);
    }

    /**
     * Add a new call by passing it to the current CallWriter
     *
     * @param string $handler handler method name (see mode handlers below)
     * @param mixed $args arguments for this call
     * @param int $pos byte position in the original source file
     */
    public function addCall($handler, $args, $pos)
    {
        $call = [$handler, $args, $pos];
        $this->callWriter->writeCall($call);
    }

    /**
     * Accessor for the current CallWriter
     *
     * @return CallWriterInterface
     */
    public function getCallWriter()
    {
        return $this->callWriter;
    }

    /**
     * Set a new CallWriter
     *
     * @param CallWriterInterface $callWriter
     */
    public function setCallWriter($callWriter)
    {
        $this->callWriter = $callWriter;
    }

    /**
     * Return the current internal status of the given name
     *
     * @param string $status
     * @return mixed|null
     */
    public function getStatus($status)
    {
        if (!isset($this->status[$status])) return null;
        return $this->status[$status];
    }

    /**
     * Set a new internal status
     *
     * @param string $status
     * @param mixed $value
     */
    public function setStatus($status, $value)
    {
        $this->status[$status] = $value;
    }

    /** @deprecated 2019-10-31 use addCall() instead */
    public function _addCall($handler, $args, $pos)
    {
        dbg_deprecated('addCall');
        $this->addCall($handler, $args, $pos);
    }

    /**
     * Similar to addCall, but adds a plugin call
     *
     * @param string $plugin name of the plugin
     * @param mixed $args arguments for this call
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @param string $match matched syntax
     */
    public function addPluginCall($plugin, $args, $state, $pos, $match)
    {
        $call = ['plugin', [$plugin, $args, $state, $match], $pos];
        $this->callWriter->writeCall($call);
    }

    /**
     * Finishes handling
     *
     * Called from the parser. Calls finalise() on the call writer, closes open
     * sections, rewrites blocks and adds document_start and document_end calls.
     *
     * @triggers PARSER_HANDLER_DONE
     */
    public function finalize()
    {
        $this->callWriter->finalise();

        if ($this->status['section']) {
            $last_call = end($this->calls);
            $this->calls[] = ['section_close', [], $last_call[2]];
        }

        if ($this->rewriteBlocks) {
            $B = new Block();
            $this->calls = $B->process($this->calls);
        }

        Event::createAndTrigger('PARSER_HANDLER_DONE', $this);

        array_unshift($this->calls, ['document_start', [], 0]);
        $last_call = end($this->calls);
        $this->calls[] = ['document_end', [], $last_call[2]];
    }

    /**
     * fetch the current call and advance the pointer to the next one
     *
     * @fixme seems to be unused?
     * @return bool|mixed
     */
    public function fetch()
    {
        $call = current($this->calls);
        if ($call !== false) {
            next($this->calls); //advance the pointer
            return $call;
        }
        return false;
    }


    /**
     * Internal function for parsing highlight options.
     * $options is parsed for key value pairs separated by commas.
     * A value might also be missing in which case the value will simple
     * be set to true. Commas in strings are ignored, e.g. option="4,56"
     * will work as expected and will only create one entry.
     *
     * @param string $options space separated list of key-value pairs,
     *                        e.g. option1=123, option2="456"
     * @return array|null     Array of key-value pairs $array['key'] = 'value';
     *                        or null if no entries found
     */
    protected function parse_highlight_options($options)
    {
        $result = [];
        preg_match_all('/(\w+(?:="[^"]*"))|(\w+(?:=[^\s]*))|(\w+[^=\s\]])(?:\s*)/', $options, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $equal_sign = strpos($match [0], '=');
            if ($equal_sign === false) {
                $key = trim($match[0]);
                $result [$key] = 1;
            } else {
                $key = substr($match[0], 0, $equal_sign);
                $value = substr($match[0], $equal_sign + 1);
                $value = trim($value, '"');
                if (strlen($value) > 0) {
                    $result [$key] = $value;
                } else {
                    $result [$key] = 1;
                }
            }
        }

        // Check for supported options
        $result = array_intersect_key(
            $result,
            array_flip([
                'enable_line_numbers',
                'start_line_numbers_at',
                'highlight_lines_extra',
                'enable_keyword_links'
            ])
        );

        // Sanitize values
        if (isset($result['enable_line_numbers'])) {
            if ($result['enable_line_numbers'] === 'false') {
                $result['enable_line_numbers'] = false;
            }
            $result['enable_line_numbers'] = (bool)$result['enable_line_numbers'];
        }
        if (isset($result['highlight_lines_extra'])) {
            $result['highlight_lines_extra'] = array_map('intval', explode(',', $result['highlight_lines_extra']));
            $result['highlight_lines_extra'] = array_filter($result['highlight_lines_extra']);
            $result['highlight_lines_extra'] = array_unique($result['highlight_lines_extra']);
        }
        if (isset($result['start_line_numbers_at'])) {
            $result['start_line_numbers_at'] = (int)$result['start_line_numbers_at'];
        }
        if (isset($result['enable_keyword_links'])) {
            if ($result['enable_keyword_links'] === 'false') {
                $result['enable_keyword_links'] = false;
            }
            $result['enable_keyword_links'] = (bool)$result['enable_keyword_links'];
        }
        if (count($result) == 0) {
            return null;
        }

        return $result;
    }

    /**
     * Simplifies handling for the formatting tags which all behave the same
     *
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @param string $name actual mode name
     */
    protected function nestingTag($match, $state, $pos, $name)
    {
        switch ($state) {
            case DOKU_LEXER_ENTER:
                $this->addCall($name . '_open', [], $pos);
                break;
            case DOKU_LEXER_EXIT:
                $this->addCall($name . '_close', [], $pos);
                break;
            case DOKU_LEXER_UNMATCHED:
                $this->addCall('cdata', [$match], $pos);
                break;
        }
    }


    /**
     * The following methods define the handlers for the different Syntax modes
     *
     * The handlers are called from dokuwiki\Parsing\Lexer\Lexer\invokeParser()
     *
     * @todo it might make sense to move these into their own class or merge them with the
     *       ParserMode classes some time.
     */
    // region mode handlers

    /**
     * Special plugin handler
     *
     * This handler is called for all modes starting with 'plugin_'.
     * An additional parameter with the plugin name is passed. The plugin's handle()
     * method is called here
     *
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @param string $pluginname name of the plugin
     * @return bool mode handled?
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     */
    public function plugin($match, $state, $pos, $pluginname)
    {
        $data = [$match];
        /** @var SyntaxPlugin $plugin */
        $plugin = plugin_load('syntax', $pluginname);
        if ($plugin != null) {
            $data = $plugin->handle($match, $state, $pos, $this);
        }
        if ($data !== false) {
            $this->addPluginCall($pluginname, $data, $state, $pos, $match);
        }
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function base($match, $state, $pos)
    {
        if ($state === DOKU_LEXER_UNMATCHED) {
            $this->addCall('cdata', [$match], $pos);
            return true;
        }
        return false;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function header($match, $state, $pos)
    {
        // get level and title
        $title = trim($match);
        $level = 7 - strspn($title, '=');
        if ($level < 1) $level = 1;
        $title = trim($title, '=');
        $title = trim($title);

        if ($this->status['section']) $this->addCall('section_close', [], $pos);

        $this->addCall('header', [$title, $level, $pos], $pos);

        $this->addCall('section_open', [$level], $pos);
        $this->status['section'] = true;
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function notoc($match, $state, $pos)
    {
        $this->addCall('notoc', [], $pos);
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function nocache($match, $state, $pos)
    {
        $this->addCall('nocache', [], $pos);
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function linebreak($match, $state, $pos)
    {
        $this->addCall('linebreak', [], $pos);
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function eol($match, $state, $pos)
    {
        $this->addCall('eol', [], $pos);
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function hr($match, $state, $pos)
    {
        $this->addCall('hr', [], $pos);
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function strong($match, $state, $pos)
    {
        $this->nestingTag($match, $state, $pos, 'strong');
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function emphasis($match, $state, $pos)
    {
        $this->nestingTag($match, $state, $pos, 'emphasis');
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function underline($match, $state, $pos)
    {
        $this->nestingTag($match, $state, $pos, 'underline');
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function monospace($match, $state, $pos)
    {
        $this->nestingTag($match, $state, $pos, 'monospace');
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function subscript($match, $state, $pos)
    {
        $this->nestingTag($match, $state, $pos, 'subscript');
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function superscript($match, $state, $pos)
    {
        $this->nestingTag($match, $state, $pos, 'superscript');
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function deleted($match, $state, $pos)
    {
        $this->nestingTag($match, $state, $pos, 'deleted');
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function footnote($match, $state, $pos)
    {
        if (!isset($this->footnote)) $this->footnote = false;

        switch ($state) {
            case DOKU_LEXER_ENTER:
                // footnotes can not be nested - however due to limitations in lexer it can't be prevented
                // we will still enter a new footnote mode, we just do nothing
                if ($this->footnote) {
                    $this->addCall('cdata', [$match], $pos);
                    break;
                }
                $this->footnote = true;

                $this->callWriter = new Nest($this->callWriter, 'footnote_close');
                $this->addCall('footnote_open', [], $pos);
                break;
            case DOKU_LEXER_EXIT:
                // check whether we have already exitted the footnote mode, can happen if the modes were nested
                if (!$this->footnote) {
                    $this->addCall('cdata', [$match], $pos);
                    break;
                }

                $this->footnote = false;
                $this->addCall('footnote_close', [], $pos);

                /** @var Nest $reWriter */
                $reWriter = $this->callWriter;
                $this->callWriter = $reWriter->process();
                break;
            case DOKU_LEXER_UNMATCHED:
                $this->addCall('cdata', [$match], $pos);
                break;
        }
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function listblock($match, $state, $pos)
    {
        switch ($state) {
            case DOKU_LEXER_ENTER:
                $this->callWriter = new Lists($this->callWriter);
                $this->addCall('list_open', [$match], $pos);
                break;
            case DOKU_LEXER_EXIT:
                $this->addCall('list_close', [], $pos);
                /** @var Lists $reWriter */
                $reWriter = $this->callWriter;
                $this->callWriter = $reWriter->process();
                break;
            case DOKU_LEXER_MATCHED:
                $this->addCall('list_item', [$match], $pos);
                break;
            case DOKU_LEXER_UNMATCHED:
                $this->addCall('cdata', [$match], $pos);
                break;
        }
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function unformatted($match, $state, $pos)
    {
        if ($state == DOKU_LEXER_UNMATCHED) {
            $this->addCall('unformatted', [$match], $pos);
        }
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function preformatted($match, $state, $pos)
    {
        switch ($state) {
            case DOKU_LEXER_ENTER:
                $this->callWriter = new Preformatted($this->callWriter);
                $this->addCall('preformatted_start', [], $pos);
                break;
            case DOKU_LEXER_EXIT:
                $this->addCall('preformatted_end', [], $pos);
                /** @var Preformatted $reWriter */
                $reWriter = $this->callWriter;
                $this->callWriter = $reWriter->process();
                break;
            case DOKU_LEXER_MATCHED:
                $this->addCall('preformatted_newline', [], $pos);
                break;
            case DOKU_LEXER_UNMATCHED:
                $this->addCall('preformatted_content', [$match], $pos);
                break;
        }

        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function quote($match, $state, $pos)
    {

        switch ($state) {
            case DOKU_LEXER_ENTER:
                $this->callWriter = new Quote($this->callWriter);
                $this->addCall('quote_start', [$match], $pos);
                break;

            case DOKU_LEXER_EXIT:
                $this->addCall('quote_end', [], $pos);
                /** @var Lists $reWriter */
                $reWriter = $this->callWriter;
                $this->callWriter = $reWriter->process();
                break;

            case DOKU_LEXER_MATCHED:
                $this->addCall('quote_newline', [$match], $pos);
                break;

            case DOKU_LEXER_UNMATCHED:
                $this->addCall('cdata', [$match], $pos);
                break;
        }

        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function file($match, $state, $pos)
    {
        return $this->code($match, $state, $pos, 'file');
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @param string $type either 'code' or 'file'
     * @return bool mode handled?
     */
    public function code($match, $state, $pos, $type = 'code')
    {
        if ($state == DOKU_LEXER_UNMATCHED) {
            $matches = sexplode('>', $match, 2, '');
            // Cut out variable options enclosed in []
            preg_match('/\[.*\]/', $matches[0], $options);
            if (!empty($options[0])) {
                $matches[0] = str_replace($options[0], '', $matches[0]);
            }
            $param = preg_split('/\s+/', $matches[0], 2, PREG_SPLIT_NO_EMPTY);
            while (count($param) < 2) $param[] = null;
            // We shortcut html here.
            if ($param[0] == 'html') $param[0] = 'html4strict';
            if ($param[0] == '-') $param[0] = null;
            array_unshift($param, $matches[1]);
            if (!empty($options[0])) {
                $param [] = $this->parse_highlight_options($options[0]);
            }
            $this->addCall($type, $param, $pos);
        }
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function acronym($match, $state, $pos)
    {
        $this->addCall('acronym', [$match], $pos);
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function smiley($match, $state, $pos)
    {
        $this->addCall('smiley', [$match], $pos);
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function wordblock($match, $state, $pos)
    {
        $this->addCall('wordblock', [$match], $pos);
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function entity($match, $state, $pos)
    {
        $this->addCall('entity', [$match], $pos);
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function multiplyentity($match, $state, $pos)
    {
        preg_match_all('/\d+/', $match, $matches);
        $this->addCall('multiplyentity', [$matches[0][0], $matches[0][1]], $pos);
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function singlequoteopening($match, $state, $pos)
    {
        $this->addCall('singlequoteopening', [], $pos);
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function singlequoteclosing($match, $state, $pos)
    {
        $this->addCall('singlequoteclosing', [], $pos);
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function apostrophe($match, $state, $pos)
    {
        $this->addCall('apostrophe', [], $pos);
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function doublequoteopening($match, $state, $pos)
    {
        $this->addCall('doublequoteopening', [], $pos);
        $this->status['doublequote']++;
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function doublequoteclosing($match, $state, $pos)
    {
        if ($this->status['doublequote'] <= 0) {
            $this->doublequoteopening($match, $state, $pos);
        } else {
            $this->addCall('doublequoteclosing', [], $pos);
            $this->status['doublequote'] = max(0, --$this->status['doublequote']);
        }
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function camelcaselink($match, $state, $pos)
    {
        $this->addCall('camelcaselink', [$match], $pos);
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function internallink($match, $state, $pos)
    {
        // Strip the opening and closing markup
        $link = preg_replace(['/^\[\[/', '/\]\]$/u'], '', $match);

        // Split title from URL
        $link = sexplode('|', $link, 2);
        if ($link[1] === null) {
            $link[1] = null;
        } elseif (preg_match('/^\{\{[^\}]+\}\}$/', $link[1])) {
            // If the title is an image, convert it to an array containing the image details
            $link[1] = Doku_Handler_Parse_Media($link[1]);
        }
        $link[0] = trim($link[0]);

        //decide which kind of link it is

        if (link_isinterwiki($link[0])) {
            // Interwiki
            $interwiki = sexplode('>', $link[0], 2, '');
            $this->addCall(
                'interwikilink',
                [$link[0], $link[1], strtolower($interwiki[0]), $interwiki[1]],
                $pos
            );
        } elseif (preg_match('/^\\\\\\\\[^\\\\]+?\\\\/u', $link[0])) {
            // Windows Share
            $this->addCall(
                'windowssharelink',
                [$link[0], $link[1]],
                $pos
            );
        } elseif (preg_match('#^([a-z0-9\-\.+]+?)://#i', $link[0])) {
            // external link (accepts all protocols)
            $this->addCall(
                'externallink',
                [$link[0], $link[1]],
                $pos
            );
        } elseif (preg_match('<' . PREG_PATTERN_VALID_EMAIL . '>', $link[0])) {
            // E-Mail (pattern above is defined in inc/mail.php)
            $this->addCall(
                'emaillink',
                [$link[0], $link[1]],
                $pos
            );
        } elseif (preg_match('!^#.+!', $link[0])) {
            // local link
            $this->addCall(
                'locallink',
                [substr($link[0], 1), $link[1]],
                $pos
            );
        } else {
            // internal link
            $this->addCall(
                'internallink',
                [$link[0], $link[1]],
                $pos
            );
        }

        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function filelink($match, $state, $pos)
    {
        $this->addCall('filelink', [$match, null], $pos);
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function windowssharelink($match, $state, $pos)
    {
        $this->addCall('windowssharelink', [$match, null], $pos);
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function media($match, $state, $pos)
    {
        $p = Doku_Handler_Parse_Media($match);

        $this->addCall(
            $p['type'],
            [$p['src'], $p['title'], $p['align'], $p['width'], $p['height'], $p['cache'], $p['linking']],
            $pos
        );
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function rss($match, $state, $pos)
    {
        $link = preg_replace(['/^\{\{rss>/', '/\}\}$/'], '', $match);

        // get params
        [$link, $params] = sexplode(' ', $link, 2, '');

        $p = [];
        if (preg_match('/\b(\d+)\b/', $params, $match)) {
            $p['max'] = $match[1];
        } else {
            $p['max'] = 8;
        }
        $p['reverse'] = (preg_match('/rev/', $params));
        $p['author'] = (preg_match('/\b(by|author)/', $params));
        $p['date'] = (preg_match('/\b(date)/', $params));
        $p['details'] = (preg_match('/\b(desc|detail)/', $params));
        $p['nosort'] = (preg_match('/\b(nosort)\b/', $params));

        if (preg_match('/\b(\d+)([dhm])\b/', $params, $match)) {
            $period = ['d' => 86400, 'h' => 3600, 'm' => 60];
            $p['refresh'] = max(600, $match[1] * $period[$match[2]]);  // n * period in seconds, minimum 10 minutes
        } else {
            $p['refresh'] = 14400;   // default to 4 hours
        }

        $this->addCall('rss', [$link, $p], $pos);
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function externallink($match, $state, $pos)
    {
        $url = $match;
        $title = null;

        // add protocol on simple short URLs
        if (str_starts_with($url, 'ftp') && !str_starts_with($url, 'ftp://')) {
            $title = $url;
            $url = 'ftp://' . $url;
        }
        if (str_starts_with($url, 'www')) {
            $title = $url;
            $url = 'http://' . $url;
        }

        $this->addCall('externallink', [$url, $title], $pos);
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function emaillink($match, $state, $pos)
    {
        $email = preg_replace(['/^</', '/>$/'], '', $match);
        $this->addCall('emaillink', [$email, null], $pos);
        return true;
    }

    /**
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @return bool mode handled?
     */
    public function table($match, $state, $pos)
    {
        switch ($state) {
            case DOKU_LEXER_ENTER:
                $this->callWriter = new Table($this->callWriter);

                $this->addCall('table_start', [$pos + 1], $pos);
                if (trim($match) == '^') {
                    $this->addCall('tableheader', [], $pos);
                } else {
                    $this->addCall('tablecell', [], $pos);
                }
                break;

            case DOKU_LEXER_EXIT:
                $this->addCall('table_end', [$pos], $pos);
                /** @var Table $reWriter */
                $reWriter = $this->callWriter;
                $this->callWriter = $reWriter->process();
                break;

            case DOKU_LEXER_UNMATCHED:
                if (trim($match) != '') {
                    $this->addCall('cdata', [$match], $pos);
                }
                break;

            case DOKU_LEXER_MATCHED:
                if ($match == ' ') {
                    $this->addCall('cdata', [$match], $pos);
                } elseif (preg_match('/:::/', $match)) {
                    $this->addCall('rowspan', [$match], $pos);
                } elseif (preg_match('/\t+/', $match)) {
                    $this->addCall('table_align', [$match], $pos);
                } elseif (preg_match('/ {2,}/', $match)) {
                    $this->addCall('table_align', [$match], $pos);
                } elseif ($match == "\n|") {
                    $this->addCall('table_row', [], $pos);
                    $this->addCall('tablecell', [], $pos);
                } elseif ($match == "\n^") {
                    $this->addCall('table_row', [], $pos);
                    $this->addCall('tableheader', [], $pos);
                } elseif ($match == '|') {
                    $this->addCall('tablecell', [], $pos);
                } elseif ($match == '^') {
                    $this->addCall('tableheader', [], $pos);
                }
                break;
        }
        return true;
    }

    // endregion modes
}

//------------------------------------------------------------------------
function Doku_Handler_Parse_Media($match)
{

    // Strip the opening and closing markup
    $link = preg_replace(['/^\{\{/', '/\}\}$/u'], '', $match);

    // Split title from URL
    $link = sexplode('|', $link, 2);

    // Check alignment
    $ralign = (bool)preg_match('/^ /', $link[0]);
    $lalign = (bool)preg_match('/ $/', $link[0]);

    // Logic = what's that ;)...
    if ($lalign & $ralign) {
        $align = 'center';
    } elseif ($ralign) {
        $align = 'right';
    } elseif ($lalign) {
        $align = 'left';
    } else {
        $align = null;
    }

    // The title...
    if (!isset($link[1])) {
        $link[1] = null;
    }

    //remove aligning spaces
    $link[0] = trim($link[0]);

    //split into src and parameters (using the very last questionmark)
    $pos = strrpos($link[0], '?');
    if ($pos !== false) {
        $src = substr($link[0], 0, $pos);
        $param = substr($link[0], $pos + 1);
    } else {
        $src = $link[0];
        $param = '';
    }

    //parse width and height
    if (preg_match('#(\d+)(x(\d+))?#i', $param, $size)) {
        $w = empty($size[1]) ? null : $size[1];
        $h = empty($size[3]) ? null : $size[3];
    } else {
        $w = null;
        $h = null;
    }

    //get linking command
    if (preg_match('/nolink/i', $param)) {
        $linking = 'nolink';
    } elseif (preg_match('/direct/i', $param)) {
        $linking = 'direct';
    } elseif (preg_match('/linkonly/i', $param)) {
        $linking = 'linkonly';
    } else {
        $linking = 'details';
    }

    //get caching command
    if (preg_match('/(nocache|recache)/i', $param, $cachemode)) {
        $cache = $cachemode[1];
    } else {
        $cache = 'cache';
    }

    // Check whether this is a local or remote image or interwiki
    if (media_isexternal($src) || link_isinterwiki($src)) {
        $call = 'externalmedia';
    } else {
        $call = 'internalmedia';
    }

    $params = [
        'type' => $call,
        'src' => $src,
        'title' => $link[1],
        'align' => $align,
        'width' => $w,
        'height' => $h,
        'cache' => $cache,
        'linking' => $linking
    ];

    return $params;
}
