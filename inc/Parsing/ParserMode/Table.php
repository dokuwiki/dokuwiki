<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;
use dokuwiki\Parsing\Handler\Table as TableHandler;
use dokuwiki\Parsing\ModeRegistry;

class Table extends AbstractMode
{
    /**
     * Table constructor.
     */
    public function __construct()
    {
        $this->allowedModes = ModeRegistry::getInstance()->getModesForCategories([
            ModeRegistry::CATEGORY_FORMATTING,
            ModeRegistry::CATEGORY_SUBSTITION,
            ModeRegistry::CATEGORY_DISABLED,
            ModeRegistry::CATEGORY_PROTECTED,
        ]);
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 60;
    }

    /** @inheritdoc */
    public function preConnect()
    {
        ModeRegistry::getInstance()->registerBlockEolMode('table');
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addEntryPattern('[\t ]*\n\^', $mode, 'table');
        $this->Lexer->addEntryPattern('[\t ]*\n\|', $mode, 'table');
    }

    /** @inheritdoc */
    public function postConnect()
    {
        $this->Lexer->addPattern('\n\^', 'table');
        $this->Lexer->addPattern('\n\|', 'table');
        $this->Lexer->addPattern('[\t ]*:::[\t ]*(?=[\|\^])', 'table');
        $this->Lexer->addPattern('[\t ]+', 'table');
        $this->Lexer->addPattern('\^', 'table');
        $this->Lexer->addPattern('\|', 'table');
        $this->Lexer->addExitPattern('\n', 'table');
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        switch ($state) {
            case DOKU_LEXER_ENTER:
                $handler->setCallWriter(new TableHandler($handler->getCallWriter()));

                $handler->addCall('table_start', [$pos + 1], $pos);
                if (trim($match) == '^') {
                    $handler->addCall('tableheader', [], $pos);
                } else {
                    $handler->addCall('tablecell', [], $pos);
                }
                break;

            case DOKU_LEXER_EXIT:
                $handler->addCall('table_end', [$pos], $pos);
                /** @var TableHandler $reWriter */
                $reWriter = $handler->getCallWriter();
                $handler->setCallWriter($reWriter->process());
                break;

            case DOKU_LEXER_UNMATCHED:
                if (trim($match) != '') {
                    $handler->addCall('cdata', [$match], $pos);
                }
                break;

            case DOKU_LEXER_MATCHED:
                if ($match == ' ') {
                    $handler->addCall('cdata', [$match], $pos);
                } elseif (preg_match('/:::/', $match)) {
                    $handler->addCall('rowspan', [$match], $pos);
                } elseif (preg_match('/\t+/', $match)) {
                    $handler->addCall('table_align', [$match], $pos);
                } elseif (preg_match('/ {2,}/', $match)) {
                    $handler->addCall('table_align', [$match], $pos);
                } elseif ($match == "\n|") {
                    $handler->addCall('table_row', [], $pos);
                    $handler->addCall('tablecell', [], $pos);
                } elseif ($match == "\n^") {
                    $handler->addCall('table_row', [], $pos);
                    $handler->addCall('tableheader', [], $pos);
                } elseif ($match == '|') {
                    $handler->addCall('tablecell', [], $pos);
                } elseif ($match == '^') {
                    $handler->addCall('tableheader', [], $pos);
                }
                break;
        }
        return true;
    }
}
