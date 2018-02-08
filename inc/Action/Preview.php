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
        $this->savedraft();
        parent::preProcess();
    }

    /** @inheritdoc */
    public function tplContent() {
        global $TEXT;
        html_edit();
        html_show($TEXT);
    }

    /**
     * Saves a draft on preview
     */
    protected function savedraft() {
        global $INFO;
        global $ID;
        global $INPUT;
        global $conf;

        if(!$conf['usedraft']) return;
        if(!$INPUT->post->has('wikitext')) return;

        // ensure environment (safeguard when used via AJAX)
        assert(isset($INFO['client']), 'INFO.client should have been set');
        assert(isset($ID), 'ID should have been set');

        $draft = array(
            'id' => $ID,
            'prefix' => substr($INPUT->post->str('prefix'), 0, -1),
            'text' => $INPUT->post->str('wikitext'),
            'suffix' => $INPUT->post->str('suffix'),
            'date' => $INPUT->post->int('date'),
            'client' => $INFO['client'],
        );
        $cname = getCacheName($draft['client'] . $ID, '.draft');
        if(io_saveFile($cname, serialize($draft))) {
            $INFO['draft'] = $cname;
        }
    }

}
