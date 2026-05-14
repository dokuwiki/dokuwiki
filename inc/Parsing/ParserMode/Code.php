<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;

class Code extends AbstractMode
{
    /** @var string The call type used in addCall ('code' or 'file') */
    protected $type = 'code';

    /** @inheritdoc */
    public function getSort()
    {
        return 200;
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addEntryPattern('<code\b(?=.*</code>)', $mode, 'code');
    }

    /** @inheritdoc */
    public function postConnect()
    {
        $this->Lexer->addExitPattern('</code>', 'code');
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        if ($state !== DOKU_LEXER_UNMATCHED) return true;

        // split "language filename [options]>content" at the first >
        [$attr, $content] = sexplode('>', $match, 2, '');

        // extract highlight options from [...]
        $hasOptions = preg_match('/\[.*\]/', $attr, $optMatch);
        if ($hasOptions) {
            $attr = str_replace($optMatch[0], '', $attr);
        }

        // split remaining attributes into language and filename
        $parts = preg_split('/\s+/', $attr, 2, PREG_SPLIT_NO_EMPTY);
        $language = $parts[0] ?? null;
        $filename = $parts[1] ?? null;

        // normalize language
        if ($language === 'html') $language = 'html4strict';
        if ($language === '-') $language = null;

        $param = [$content, $language, $filename];
        if ($hasOptions) {
            $param[] = $this->parseHighlightOptions($optMatch[0]);
        }
        $handler->addCall($this->type, $param, $pos);

        return true;
    }

    /**
     * Internal function for parsing highlight options.
     * $options is parsed for key value pairs separated by commas.
     * A value might also be missing in which case the value will simply
     * be set to true. Commas in strings are ignored, e.g. option="4,56"
     * will work as expected and will only create one entry.
     *
     * @param string $options space separated list of key-value pairs
     * @return array|null Array of key-value pairs or null if no entries found
     */
    protected function parseHighlightOptions($options)
    {
        $result = [];
        preg_match_all('/(\w+(?:="[^"]*"))|(\w+(?:=[^\s]*))|(\w+[^=\s\]])(?:\s*)/', $options, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $equal_sign = strpos($match[0], '=');
            if ($equal_sign === false) {
                $key = trim($match[0]);
                $result[$key] = 1;
            } else {
                $key = substr($match[0], 0, $equal_sign);
                $value = substr($match[0], $equal_sign + 1);
                $value = trim($value, '"');
                if ($value !== '') {
                    $result[$key] = $value;
                } else {
                    $result[$key] = 1;
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
            $result['highlight_lines_extra'] = array_map(intval(...), explode(',', $result['highlight_lines_extra']));
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
}
