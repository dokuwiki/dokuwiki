<?php

namespace dokuwiki\Ui;

use dokuwiki\ChangeLog\MediaChangeLog;
use dokuwiki\Extension\Event;
use dokuwiki\Form\Form;

/**
 * DokuWiki MediaDiff Interface
 * 
 * @package dokuwiki\Ui
 */
class MediaDiff extends Diff
{
    /* @var string */
    protected $id;
    /* @var bool */
    protected $fromajax;

    /** 
     * MediaDiff Ui constructor
     *
     * @param string $id  id of media
     * @param bool $fromajax
     */
    public function __construct($id, $fromajax = false)
    {
        $this->id = $id;
        $this->fromajax = $fromajax;
    }

    /**
     * Shows difference between two revisions of media
     *
     * @param bool $fromajax
     */
    public function show()
    {
        $ns = getNS($this->id);
        $auth = auth_quickaclcheck("$ns:*");
        media_diff($this->id, $ns, $auth, $this->fromajax);
    }

}
