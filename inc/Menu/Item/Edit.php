<?php

namespace dokuwiki\Menu\Item;

/**
 * Class Edit
 *
 * Most complex item. Shows the edit button but mutates to show, draft and create based on
 * current state.
 */
class Edit extends AbstractItem {

    /** @inheritdoc */
    public function __construct() {
        global $ACT;
        global $INFO;
        global $REV;

        parent::__construct();

        if($ACT === 'show') {
            $this->method = 'post';
            if($INFO['writable']) {
                $this->accesskey = 'e';
                if(!empty($INFO['draft'])) {
                    $this->type = 'draft';
                    $this->params['do'] = 'draft';
                } else {
                    $this->params['rev'] = $REV;
                    if(!$INFO['exists']) {
                        $this->type = 'create';
                    }
                }
            } else {
                if(!actionOK($this->type)) throw new \RuntimeException("action disabled: source");
                $params['rev'] = $REV;
                $this->type = 'source';
                $this->accesskey = 'v';
            }
        } else {
            $this->params = array('do' => '');
            $this->type = 'show';
            $this->accesskey = 'v';
        }

        $this->setIcon();
    }

    /**
     * change the icon according to what type the edit button has
     */
    protected function setIcon() {
        $icons = array(
            'edit' => '01-edit_pencil.svg',
            'create' => '02-create_pencil.svg',
            'draft' => '03-draft_android-studio.svg',
            'show' => '04-show_file-document.svg',
            'source' => '05-source_file-xml.svg',
        );
        if(isset($icons[$this->type])) {
            $this->svg = DOKU_INC . 'lib/images/menu/' . $icons[$this->type];
        }
    }

}
