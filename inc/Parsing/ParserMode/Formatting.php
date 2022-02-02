<?php

namespace dokuwiki\Parsing\ParserMode;

/**
 * This class sets the markup for bold (=strong),
 * italic (=emphasis), underline etc.
 */
class Formatting extends AbstractMode
{
    protected $type;

    protected $formatting = array(
        'strong' => array(
            'entry' => '\*\*(?=.*\*\*)',
            'exit' => '\*\*',
            'sort' => 70
        ),

        'emphasis' => array(
            'entry' => '//(?=[^\x00]*[^:])', //hack for bugs #384 #763 #1468
            'exit' => '//',
            'sort' => 80
        ),

        'underline' => array(
            'entry' => '__(?=.*__)',
            'exit' => '__',
            'sort' => 90
        ),

        'monospace' => array(
            'entry' => '\x27\x27(?=.*\x27\x27)',
            'exit' => '\x27\x27',
            'sort' => 100
        ),

        'subscript' => array(
            'entry' => '<sub>(?=.*</sub>)',
            'exit' => '</sub>',
            'sort' => 110
        ),

        'superscript' => array(
            'entry' => '<sup>(?=.*</sup>)',
            'exit' => '</sup>',
            'sort' => 120
        ),

        'deleted' => array(
            'entry' => '<del>(?=.*</del>)',
            'exit' => '</del>',
            'sort' => 130
        ),
    );

    /**
     * @param string $type
     */
    public function __construct($type)
    {
        global $PARSER_MODES;

        if (!array_key_exists($type, $this->formatting)) {
            trigger_error('Invalid formatting type ' . $type, E_USER_WARNING);
        }

        $this->type = $type;

        // formatting may contain other formatting but not it self
        $modes = $PARSER_MODES['formatting'];
        $key = array_search($type, $modes);
        if (is_int($key)) {
            unset($modes[$key]);
        }

        $this->allowedModes = array_merge(
            $modes,
            $PARSER_MODES['substition'],
            $PARSER_MODES['disabled']
        );
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {

        // Can't nest formatting in itself
        if ($mode == $this->type) {
            return;
        }

        $this->Lexer->addEntryPattern(
            $this->formatting[$this->type]['entry'],
            $mode,
            $this->type
        );
    }

    /** @inheritdoc */
    public function postConnect()
    {

        $this->Lexer->addExitPattern(
            $this->formatting[$this->type]['exit'],
            $this->type
        );
    }

    /** @inheritdoc */
    public function getSort()
    {
        return $this->formatting[$this->type]['sort'];
    }
}
