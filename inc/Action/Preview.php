<?php

namespace easywiki\Action;

use easywiki\Ui\Editor;
use easywiki\Ui\PageView;
use easywiki\Draft;
use easywiki\Ui;

/**
 * Class Preview
 *
 * preview during editing
 *
 * @package easywiki\Action
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
