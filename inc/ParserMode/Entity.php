<?php

namespace dokuwiki\ParserMode;

class Entity extends AbstractMode
{

    protected $entities = array();
    protected $pattern = '';

    /**
     * Entity constructor.
     * @param string[] $entities
     */
    public function __construct($entities)
    {
        $this->entities = $entities;
    }


    /** @inheritdoc */
    public function preConnect()
    {
        if (!count($this->entities) || $this->pattern != '') return;

        $sep = '';
        foreach ($this->entities as $entity) {
            $this->pattern .= $sep.Doku_Lexer_Escape($entity);
            $sep = '|';
        }
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        if (!count($this->entities)) return;

        if (strlen($this->pattern) > 0) {
            $this->Lexer->addSpecialPattern($this->pattern, $mode, 'entity');
        }
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 260;
    }
}
