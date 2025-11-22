<?php

namespace easywiki\test\mock;

class Wiki_Renderer extends \Wiki_Renderer {

    /** @inheritdoc */
    public function getFormat() {
        return 'none';
    }
}
