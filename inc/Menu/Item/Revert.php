<?php

namespace dokuwiki\Menu\Item;

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

        // if we are comparing with the current version then
        // allow reverting to the non current version
        $comparedWithCurrent = in_array('current', $INPUT->ref('rev2')) || in_array('', $INPUT->ref('rev2'));

        if (
            (!$INFO['ismanager'] || (!$REV && !$comparedWithCurrent) || !$INFO['writable'])
        ) {
            throw new \RuntimeException('revert not available');
        }

        $this->params['rev'] = $REV;
        $this->params['sectok'] = getSecurityToken();
        $this->svg = DOKU_INC . 'lib/images/menu/06-revert_replay.svg';
    }
}
