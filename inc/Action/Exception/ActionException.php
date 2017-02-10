<?php

namespace dokuwiki\Action\Exception;

class ActionException extends \Exception {

    protected $newaction;

    public function __construct($newaction = 'show', $message='') {
        parent::__construct($message);
        $this->newaction = $newaction;
    }

    public function getNewAction() {
        return $this->newaction;
    }
}
