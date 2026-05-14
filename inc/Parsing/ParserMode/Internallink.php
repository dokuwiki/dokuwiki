<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;
use dokuwiki\Parsing\Helpers\Link;

class Internallink extends AbstractMode
{
    /** @inheritdoc */
    public function getSort()
    {
        return 300;
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        // Word boundaries?
        $this->Lexer->addSpecialPattern("\[\[.*?\]\](?!\])", $mode, 'internallink');
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        // Strip the opening and closing markup
        $link = preg_replace(['/^\[\[/', '/\]\]$/u'], '', $match);

        // Split title from URL
        $link = sexplode('|', $link, 2);
        if ($link[1] !== null && preg_match('/^\{\{[^\}]+\}\}$/', $link[1])) {
            // If the title is an image, convert it to an array containing the image details
            $link[1] = Media::parseMedia($link[1]);
        }
        $link[0] = trim($link[0]);

        [$call, $args] = Link::classify($link[0], $link[1]);
        $handler->addCall($call, $args, $pos);
        return true;
    }
}
