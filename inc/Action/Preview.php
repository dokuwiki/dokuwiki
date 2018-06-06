<?php

namespace dokuwiki\Action;

/**
 * Class Preview
 *
 * preview during editing
 *
 * @package dokuwiki\Action
 */
class Preview extends Edit {

    /** @inheritdoc */
    public function preProcess() {
        header('X-XSS-Protection: 0');
        saveDraft();
        parent::preProcess();
    }

    /** @inheritdoc */
    public function tplContent() {
        global $TEXT;
        html_edit();
        html_show($TEXT);
    }
}
