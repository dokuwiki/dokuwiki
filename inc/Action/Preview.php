<?php

namespace dokuwiki\Action;

use dokuwiki\Ui\Editor;
use dokuwiki\Ui\PageView;
use dokuwiki\Draft;
use dokuwiki\Ui;

/**
 * Class Preview
 *
 * preview during editing
 *
 * @package dokuwiki\Action
 */
class Preview extends Edit
{
    /** @inheritdoc */
    public function preProcess()
    {
        header('X-XSS-Protection: 0');
        $this->savedraft();
        parent::preProcess();
    }

    /** @inheritdoc */
    public function tplContent()
    {
        global $TEXT;
        (new Editor())->show();
        (new PageView($TEXT))->show();
    }

    /**
     * Saves a draft on preview
     */
    protected function savedraft()
    {
        global $ID, $INFO;
        $draft = new Draft($ID, $INFO['client']);
        if (!$draft->saveDraft()) {
            $errors = $draft->getErrors();
            foreach ($errors as $error) {
                msg(hsc($error), -1);
            }
        }
    }
}
