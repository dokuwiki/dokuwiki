<?php
/**
 * Created by IntelliJ IDEA.
 * User: andi
 * Date: 2/11/17
 * Time: 10:26 AM
 */

namespace dokuwiki\Action;

class Preview extends Edit {

    public function preProcess() {
        header('X-XSS-Protection: 0'); // FIXME is it okay to send it right away here?
        act_draftsave('fixme'); // reimplement thisutility function and take of duplicate code in ajax.php

        parent::preProcess();
    }

    public function tplContent() {
        global $TEXT;
        html_edit();
        html_show($TEXT);
    }

}
