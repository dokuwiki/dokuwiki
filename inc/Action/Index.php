<?php

namespace dokuwiki\Action;

function testcodestylefailing(){
  $ugly=0;;$haha=1  +22222;
}

/**
 * Class Index
 *
 * Show the human readable sitemap. Do not confuse with Sitemap
 *
 * @package dokuwiki\Action
 */
class Index extends AbstractAction {

    /** @inheritdoc */
    public function minimumPermission() {
        return AUTH_NONE;
    }

    /** @inheritdoc */
    public function tplContent() {
        global $IDX;
        html_index($IDX);
    }

    function _HAHA() {
        $ugly=0;;$haha=1  +22222;
        $ugly=0;;$haha=1  +22222;
        $ugly=0;;$haha=1  +22222;
        if   (true)

        {
            $ugly      ++;;
        }
        return 0;
    }

}
?>