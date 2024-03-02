<?php

use dokuwiki\Debug\PropertyDeprecationHelper;
use dokuwiki\Parsing\Parser;

/**
 * Define various types of modes used by the parser - they are used to
 * populate the list of modes another mode accepts
 */
global $PARSER_MODES;
$PARSER_MODES = [
    // containers are complex modes that can contain many other modes
    // hr breaks the principle but they shouldn't be used in tables / lists
    // so they are put here
    'container' => ['listblock', 'table', 'quote', 'hr'],
    // some mode are allowed inside the base mode only
    'baseonly' => ['header'],
    // modes for styling text -- footnote behaves similar to styling
    'formatting' => [
        'strong', 'emphasis', 'underline', 'monospace', 'subscript', 'superscript', 'deleted', 'footnote'
    ],
    // modes where the token is simply replaced - they can not contain any
    // other modes
    'substition' => [
        'acronym', 'smiley', 'wordblock', 'entity', 'camelcaselink', 'internallink', 'media', 'externallink',
        'linebreak', 'emaillink', 'windowssharelink', 'filelink', 'notoc', 'nocache', 'multiplyentity', 'quotes', 'rss'
    ],
    // modes which have a start and end token but inside which
    // no other modes should be applied
    'protected' => ['preformatted', 'code', 'file'],
    // inside this mode no wiki markup should be applied but lineendings
    // and whitespace isn't preserved
    'disabled' => ['unformatted'],
    // used to mark paragraph boundaries
    'paragraphs' => ['eol'],
];

/**
 * Class Doku_Parser
 *
 * @deprecated 2018-05-04
 */
class Doku_Parser extends Parser
{
    use PropertyDeprecationHelper {
        __set as protected deprecationHelperMagicSet;
        __get as protected deprecationHelperMagicGet;
    }

    /** @inheritdoc */
    public function __construct(Doku_Handler $handler = null)
    {
        dbg_deprecated(Parser::class);
        $this->deprecatePublicProperty('modes', self::class);
        $this->deprecatePublicProperty('connected', self::class);

        if (!$handler instanceof \Doku_Handler) {
            $handler = new Doku_Handler();
        }

        parent::__construct($handler);
    }

    public function __set($name, $value)
    {

        if ($name === 'Handler') {
            $this->handler = $value;
            return;
        }

        if ($name === 'Lexer') {
            $this->lexer = $value;
            return;
        }

        $this->deprecationHelperMagicSet($name, $value);
    }

    public function __get($name)
    {
        if ($name === 'Handler') {
            return $this->handler;
        }

        if ($name === 'Lexer') {
            return $this->lexer;
        }

        return $this->deprecationHelperMagicGet($name);
    }
}
