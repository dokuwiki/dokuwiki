<?php

namespace dokuwiki\test\mock;

class Doku_Renderer extends \Doku_Renderer {

    /** @inheritdoc */
    public function getFormat() {
        return 'none';
    }
}
