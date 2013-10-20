<?php

include_once(DOKU_INC . "/inc/components/action.php");
include_once(DOKU_INC . "/inc/components/actions/diff/diff_lib.php");

/**
 * Handler for action diff
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Diff extends Doku_Action
{
    /**
     * Specifies the action name
     * 
     * @return string the action name
     */
    public function action() {
        return "diff";
    }

    /**
     * Specifies the required permission level to handle the diff action.
     * 
     * @return string the permission
     */
    public function permission_required() {
        return AUTH_READ;
    }

    /**
     * Handle the diff action
     * 
     * @global array $INPUT
     */
    public function handle() {
        global $INPUT;
        $difftype = $INPUT->str('difftype');
        if (!empty($difftype)) {
            set_doku_pref('difftype', $difftype);
        }
    }

    /**
     * display diffs between revisions.
     */
    public function html() {
        html_diff();
    }
}
