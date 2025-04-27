<?php

namespace dokuwiki\Menu\Item;

use dokuwiki\File\StaticImage;

/**
 * Class Revert
 *
 * Quick revert to the currently shown page revision
 */
class Revert extends AbstractItem
{
    /** @inheritdoc */
    public function __construct()
    {
        global $REV;
        global $INFO;
        global $INPUT;
        parent::__construct();

        if (!$REV || !$INFO['writable'] || $INPUT->server->str('REMOTE_USER') === '') {
            throw new \RuntimeException('revert not available');
        }
        $this->params['rev'] = $REV;
        $this->params['sectok'] = getSecurityToken();
        $this->svg = StaticImage::path('menu/06-revert_replay.svg');
    }
}
