<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;

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

        //decide which kind of link it is

        if (link_isinterwiki($link[0])) {
            // Interwiki
            $interwiki = sexplode('>', $link[0], 2, '');
            $handler->addCall(
                'interwikilink',
                [$link[0], $link[1], strtolower($interwiki[0]), $interwiki[1]],
                $pos
            );
        } elseif (preg_match('/^\\\\\\\\[^\\\\]+?\\\\/u', $link[0])) {
            // Windows Share
            $handler->addCall(
                'windowssharelink',
                [$link[0], $link[1]],
                $pos
            );
        } elseif (preg_match('#^([a-z0-9\-\.+]+?)://#i', $link[0])) {
            // external link (accepts all protocols)
            $handler->addCall(
                'externallink',
                [$link[0], $link[1]],
                $pos
            );
        } elseif (preg_match('<' . PREG_PATTERN_VALID_EMAIL . '>', $link[0])) {
            // E-Mail (pattern above is defined in inc/mail.php)
            $handler->addCall(
                'emaillink',
                [$link[0], $link[1]],
                $pos
            );
        } elseif (preg_match('!^#.+!', $link[0])) {
            // local link
            $handler->addCall(
                'locallink',
                [substr($link[0], 1), $link[1]],
                $pos
            );
        } else {
            // internal link
            $handler->addCall(
                'internallink',
                [$link[0], $link[1]],
                $pos
            );
        }

        return true;
    }
}
