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
        header('X-XSS-Protection: 0'); // FIXME is it okay to send it right away here?
        act_draftsave('fixme'); // reimplement thisutility function and take of duplicate code in ajax.php

        parent::preProcess();
    }

    /** @inheritdoc */
    public function tplContent() {
        global $TEXT;
        html_edit();
        html_show($TEXT);
    }

}
