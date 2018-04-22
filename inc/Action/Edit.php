<?php

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionAbort;

/**
 * Class Edit
 *
 * Handle editing
 *
 * @package dokuwiki\Action
 */
class Edit extends AbstractAction {

    /** @inheritdoc */
    public function minimumPermission() {
        global $INFO;
        if($INFO['exists']) {
            return AUTH_READ; // we check again below
        } else {
            return AUTH_CREATE;
        }
    }

    /**
     * @inheritdoc falls back to 'source' if page not writable
     */
    public function checkPreconditions() {
        parent::checkPreconditions();
        global $INFO;

        // no edit permission? view source
        if($INFO['exists'] && !$INFO['writable']) {
            throw new ActionAbort('source');
        }
    }

    /** @inheritdoc */
    public function preProcess() {
        global $ID;
        global $INFO;

        global $TEXT;
        global $RANGE;
        global $PRE;
        global $SUF;
        global $REV;
        global $SUM;
        global $lang;
        global $DATE;

        if(!isset($TEXT)) {
            if($INFO['exists']) {
                if($RANGE) {
                    list($PRE, $TEXT, $SUF) = rawWikiSlices($RANGE, $ID, $REV);
                } else {
                    $TEXT = rawWiki($ID, $REV);
                }
            } else {
                $TEXT = pageTemplate($ID);
            }
        }

        //set summary default
        if(!$SUM) {
            if($REV) {
                $SUM = sprintf($lang['restored'], dformat($REV));
            } elseif(!$INFO['exists']) {
                $SUM = $lang['created'];
            }
        }

        // Use the date of the newest revision, not of the revision we edit
        // This is used for conflict detection
        if(!$DATE) $DATE = @filemtime(wikiFN($ID));

        //check if locked by anyone - if not lock for my self
        $lockedby = checklock($ID);
        if($lockedby) {
            throw new ActionAbort('locked');
        };
        lock($ID);
    }

    /** @inheritdoc */
    public function tplContent() {
        html_edit();
    }

}
