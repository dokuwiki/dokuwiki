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

    /**
     * MediaDiff Ui constructor
     *
     * @param string $id  media id
     */
    public function __construct($id)
    {
        $this->id = $id;

        $this->preference['fromAjax'] = false; // see doluwiki\Ajax::callMediadiff()
        $this->preference['showIntro'] = false;
        $this->preference['difftype'] = null;  // both, opacity or portions. see lib/scripts/media.js
    }

    /**
     * Shows difference between two revisions of media
     */
    public function show()
    {
        $ns = getNS($this->id);
        $auth = auth_quickaclcheck("$ns:*");
        media_diff($this->id, $ns, $auth, $this->preference['fromAjax']);
    }

}
